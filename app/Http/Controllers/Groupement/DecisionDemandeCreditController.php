<?php

namespace App\Http\Controllers\Groupement;

use App\Models\DemandePret;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\CFDossierRepository;
use App\Repositories\CycleActiviteRepository;
use App\Repositories\DemandePretGroupeRepository;
use DB;
class DecisionDemandeCreditController extends Controller
{

    public function store(
        CFDossierRepository $_dossier,
        DemandePretGroupeRepository $_demande,
        Request $request)
    {

        $demandes = $_demande->get($request->dossier);

        $dossier = $_dossier->find($request->dossier);

        $calendriers = (object)\App\Lib\CalendrierGroupement::getGroupe($dossier, $_demande);

        $cals = [];

        foreach ($calendriers as $calendrier) {

            $cals[] = [
                "dossier_id"  =>$request->dossier,
                "date_oper"  =>$calendrier->date_oper,
                "montant"  =>$calendrier->montant,
            ];
        }

        DB::table('cf_calendier_cycles')->insert($cals);

        foreach ($demandes as $demande) {

            DemandePret::where('ref_dde', $demande->ref_dde)->update([
                'decision'  =>'A'
            ]);

        }

        session()->flash("success",
        "Les demandes ont bien été accordé");

        return redirect()->route('gp.dde.show', $request->dossier);

    }
}
