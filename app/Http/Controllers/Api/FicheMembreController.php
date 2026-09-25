<?php

namespace App\Http\Controllers\Api;

use App\Models\CFDossier;
use App\Models\CFOperation;
use App\Models\ContratPretGroupe;
use App\Models\GroupeSolide;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Miroir API de Groupement\FicheMembreController — historique d'opérations d'un membre.
 */
class FicheMembreController extends ApiController
{
    /** Contrats d'un groupement, par année (miroir de l'index). */
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

    /** Fiche d'un membre sur un dossier : opérations + totaux (?tri=az|za). */
    public function show(Request $request, CFDossier $dossier, string $tiers)
    {
        $this->authorize('access', $dossier);

        $tri = $request->input('tri') === 'za' ? 'desc' : 'asc';

        $operations = CFOperation::where('tiers_id', $tiers)
            ->where('dossier_id', $dossier->id_dossier)
            ->orderBy('date_oper', $tri)
            ->get();

        $sum = CFOperation::where('tiers_id', $tiers)
            ->where('dossier_id', $dossier->id_dossier)
            ->select('dossier_id', 'tiers_id', DB::raw('SUM(mtt_remb) mtt_remb, SUM(mtt_depot) mtt_depot, SUM(mtt_retrait) mtt_retrait, SUM(penalite) penalite'))
            ->groupBy('dossier_id', 'tiers_id')
            ->first();

        return $this->data([
            'dossier'    => $dossier,
            'operations' => $operations,
            'total'      => $sum,
        ]);
    }
}
