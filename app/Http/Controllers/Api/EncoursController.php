<?php

namespace App\Http\Controllers\Api;

use App\Models\CDOperation;
use App\Models\CFDossier;
use Illuminate\Support\Facades\DB;

/**
 * Miroir API de Groupement\EncoursController.
 */
class EncoursController extends ApiController
{
    /** Encours de l'animatrice (prêts groupe non soldés). */
    public function index()
    {
        $encours = CDOperation::join('cf_contrat_pret_groupes', 'cd_operations.pret_id', 'cf_contrat_pret_groupes.pret_id')
            ->join('cf_groupe_solidarites', 'cf_contrat_pret_groupes.groupe_id', 'cf_groupe_solidarites.id_groupe')
            ->join('cf_dossiers', 'cf_contrat_pret_groupes.dossier_id', 'cf_dossiers.id_dossier')
            ->join('tiers', 'cf_groupe_solidarites.id_groupe', 'tiers.id_tiers')
            ->where('agent_id', $this->agentCode())
            ->select('id_groupe', 'id_dossier', 'debut_cycle', 'nom_tiers', 'num_caisse', DB::raw('SUM(debit) mtt_octroi'))
            ->groupBy('id_groupe', 'id_dossier', 'debut_cycle', 'nom_tiers', 'num_caisse')
            ->orderBy('nom_tiers')
            ->havingRaw('SUM(debit) > SUM(credit)')
            ->get();

        return $this->data([
            'encours'     => $encours,
            'nb_encours'  => $encours->count(),
            'sum_encours' => $encours->sum('mtt_octroi'),
        ]);
    }

    /** Détail d'un dossier en encours (renvoie le dossier + son groupe). */
    public function show(CFDossier $dossier)
    {
        $this->authorize('access', $dossier);

        return $this->data([
            'dossier' => $dossier,
            'groupe'  => $dossier->groupe,
        ]);
    }
}
