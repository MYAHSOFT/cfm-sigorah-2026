<?php

namespace App\Http\Controllers\Groupement;

use App\Models\Association\GroupSchedule;
use Illuminate\Http\Request;
use App\Models\Association\GroupLoanContract;
use App\Http\Controllers\Controller;
use App\Models\Customer\Customer;
use App\Repositories\CFDossierRepository;

class EcheancierMembreController extends Controller
{

    public function show(
        CFDossierRepository $_dossier,
        $ref_pret)
    {

        $per_page = 10;

        $tiers = Customer::join('cd_contrat_prets', 'tiers.id_tiers','=','cd_contrat_prets.tiers_id')
                    ->where('ref_pret', $ref_pret)
                    ->first();

        if(empty($tiers->id_tiers)){
            return abort(404);
        }

        $contrat_groupe = GroupLoanContract::where('ref_pret', $ref_pret)->first();

        $groupe = $_dossier->find($contrat_groupe->dossier_id);

        $calendriers = GroupSchedule::where('ref_pret', $ref_pret)->paginate($per_page);

        $sum_montant = GroupSchedule::where('ref_pret', $ref_pret)->sum('montant');

        return view('groupement.credits.calendriers.membre',[
            'groupe' =>$groupe,
            'tiers' =>$tiers,
            'calendriers'   =>$calendriers,
            'id_dossier'  =>$groupe->id_dossier,
            'sum_montant'  =>$sum_montant,
        ]);

    }
}
