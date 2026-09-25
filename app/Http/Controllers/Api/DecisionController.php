<?php

namespace App\Http\Controllers\Api;

use App\Models\Association\Cycle;
use App\Models\Lending\ApplicationLoan;
use App\Repositories\DemandePretGroupeRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Miroir API de Groupement\DecisionDemandeCreditController::store — accord des demandes
 * d'un dossier et génération du calendrier-cycle.
 *
 * SHIM : App\Lib\CalendrierGroupement lit session('cf_calendrier_tmp') pour la date
 * d'octroi. On l'alimente ici depuis le corps de la requête, le temps que la logique
 * de calcul soit extraite dans un EcheancierCalculator pur (voir feuille de route, lot 4).
 */
class DecisionController extends ApiController
{
    public function store(Request $request, DemandePretGroupeRepository $repo, Cycle $dossier)
    {
        $this->authorize('access', $dossier);

        $request->validate(['date_octroi' => ['required', 'date']]);
        session()->put('cf_calendrier_tmp', json_encode(['date_octroi' => $request->input('date_octroi')]));

        $demandes = $repo->get($dossier->id_dossier);
        $calendriers = (object) \App\Lib\CalendrierGroupement::getGroupe($dossier, $repo);

        DB::transaction(function () use ($calendriers, $demandes, $dossier) {
            $cals = [];
            foreach ($calendriers as $calendrier) {
                $cals[] = [
                    'dossier_id' => $dossier->id_dossier,
                    'date_oper'  => $calendrier->date_oper,
                    'montant'    => $calendrier->montant,
                ];
            }
            DB::table('cf_calendier_cycles')->insert($cals);

            foreach ($demandes as $demande) {
                ApplicationLoan::where('ref_dde', $demande->ref_dde)->update(['decision' => 'A']);
            }
        });

        return $this->message('Demandes accordées.');
    }
}
