<?php

namespace App\Http\Controllers\Api;

use App\Models\CalendrierCycle;
use Illuminate\Http\Request;

/**
 * Miroir API de Groupement\PlanningController — réunions du mois pour l'animatrice.
 */
class PlanningController extends ApiController
{
    /** Réunions programmées d'un mois (?month=1..12, ?year=YYYY). */
    public function index(Request $request)
    {
        $month = (int) $request->input('month', date('n'));
        if ($month > 12 || $month < 1) {
            $month = 1;
        }
        $year = (int) $request->input('year', date('Y'));

        $agent = $this->employe()->id_employe;

        $groupes = CalendrierCycle::join('cf_dossiers', 'cf_calendier_cycles.dossier_id', 'cf_dossiers.id_dossier')
            ->join('cf_groupe_solidarites', 'cf_dossiers.groupe_id', 'cf_groupe_solidarites.id_groupe')
            ->join('tiers', 'cf_groupe_solidarites.id_groupe', 'tiers.id_tiers')
            ->where('animatrice', $agent)
            ->whereYear('date_oper', $year)
            ->whereMonth('date_oper', $month)
            ->select('id_tiers', 'nom_tiers', 'id_groupe', 'num_caisse', 'jour_reunion', 'id_dossier')
            ->groupBy('id_tiers', 'nom_tiers', 'id_groupe', 'num_caisse', 'jour_reunion', 'id_dossier')
            ->get();

        $reunions = CalendrierCycle::join('cf_dossiers', 'cf_calendier_cycles.dossier_id', 'cf_dossiers.id_dossier')
            ->where('animatrice', $agent)
            ->whereYear('date_oper', $year)
            ->whereMonth('date_oper', $month)
            ->select('cf_calendier_cycles.*')
            ->orderBy('date_oper')
            ->get();

        return $this->data([
            'year'     => $year,
            'month'    => $month,
            'groupes'  => $groupes,
            'reunions' => $reunions,
        ]);
    }
}
