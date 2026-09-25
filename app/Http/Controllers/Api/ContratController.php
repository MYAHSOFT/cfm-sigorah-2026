<?php

namespace App\Http\Controllers\Api;

use App\Models\CFDossier;
use App\Models\ContratPretGroupe;
use App\Models\GroupeSolide;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Miroir API de Groupement\ContratPretController.
 */
class ContratController extends ApiController
{
    /** Contrats d'un groupement (option ?annee=YYYY|all). */
    public function index(Request $request, GroupeSolide $groupe)
    {
        $this->authorize('access', $groupe);

        $annee = $request->input('annee', date('Y'));

        $contrats = ContratPretGroupe::join('cd_contrats', 'cf_contrat_pret_groupes.pret_id', 'cd_contrats.id_pret')
            ->join('cf_dossiers', 'cf_contrat_pret_groupes.dossier_id', 'cf_dossiers.id_dossier')
            ->where('cf_dossiers.groupe_id', $groupe->id_groupe)
            ->when($annee !== 'all', fn ($q) => $q->whereYear('echeance', $annee))
            ->when($annee === 'all', fn ($q) => $q->whereYear('echeance', '<=', date('Y')))
            ->select('cf_dossiers.groupe_id', 'id_dossier', 'date_contrat', 'statut', 'statut_octroi', DB::raw('SUM(mtt_capital) mtt_capital'))
            ->groupBy('cf_dossiers.groupe_id', 'id_dossier', 'date_contrat', 'statut', 'statut_octroi')
            ->orderBy('date_contrat', 'desc')
            ->get();

        return $this->data($contrats);
    }

    /** Contrats détaillés d'un dossier. */
    public function showByDossier(CFDossier $dossier)
    {
        $this->authorize('access', $dossier);

        $contrats = ContratPretGroupe::join('cd_contrats', 'cf_contrat_pret_groupes.pret_id', 'cd_contrats.id_pret')
            ->join('cd_demandes', 'cd_contrats.demande_id', 'cd_demandes.id_demande')
            ->join('tiers', 'cd_demandes.tiers_id', 'tiers.id_tiers')
            ->where('dossier_id', $dossier->id_dossier)
            ->get();

        return $this->data([
            'dossier'     => $dossier,
            'contrats'    => $contrats,
            'nb_contrat'  => $contrats->count(),
            'sum_contrat' => ContratPretGroupe::join('cd_contrats', 'cf_contrat_pret_groupes.pret_id', 'cd_contrats.id_pret')
                ->where('dossier_id', $dossier->id_dossier)->sum('mtt_capital'),
        ]);
    }
}
