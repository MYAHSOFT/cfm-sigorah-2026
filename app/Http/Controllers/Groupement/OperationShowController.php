<?php

namespace App\Http\Controllers\Groupement;

use Illuminate\Http\Request;
use App\Models\Association\CycleCalendar;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Repositories\TiersRepository;
use App\Repositories\CFDossierRepository;
use App\Repositories\GroupeSolideRepository;
use App\Repositories\OperationGroupeRepository;
use App\Repositories\GroupeSolideMembreRepository;

class OperationShowController extends Controller
{

    public function showGroupeByDate(
        OperationGroupeRepository $_operation,
        CFDossierRepository $_dossier,
        GroupeSolideMembreRepository $_membre_groupe,
        $id_dossier)
    {

        $groupe = $_dossier->find($id_dossier);

        $operations = $_operation->getGroupeByDate($id_dossier);

        $president = $_membre_groupe->getMembreBureauByFonction($groupe->id_groupe, 'PRD');

        $echeance = CycleCalendar::where('dossier_id', $id_dossier)->max('date_oper');

        return view('groupement.operations.showGroupByDate', [
            'groupe'    =>$groupe,
            'operations'    =>$operations,
            'president'    =>$president,
            'echeance'    =>$echeance,
        ]);

    }

    public function showMembresByDate(
        CFDossierRepository $_dossier,
        OperationGroupeRepository $_operation,
        GroupeSolideMembreRepository $_membre_groupe,
        $id_dossier,
        $date_oper)
    {

        $groupe = $_dossier->find($id_dossier);

        $operations = $_operation->getMembreAllByDate($id_dossier, $date_oper);

        $president = $_membre_groupe->getMembreBureauByFonction($groupe->id_groupe, 'PRD');

        $echeance = CycleCalendar::where('dossier_id', $id_dossier)->max('date_oper');

        return view('groupement.operations.show', [
            'groupe'    =>$groupe,
            'operations'    =>$operations,
            'president'    =>$president,
            'echeance'    =>$echeance,
        ]);

    }

    public function showMembreByCycle(
        CFDossierRepository $_dossier,
        OperationGroupeRepository $_operation,
        TiersRepository $_tiers,
        $id_tiers,
        $id_dossier)
    {

        $tiers = $_tiers->find($id_tiers);

        if(empty($tiers->id_tiers)){
            return abort(404);
        }

        $groupe = $_dossier->find($id_dossier);

        $operations = $_operation->operationByTiers($id_tiers, $id_dossier);

        return view('groupement.operations.carnet', [
            'tiers'    =>$tiers,
            'groupe'    =>$groupe,
            'operations'    =>$operations,
        ]);

    }

    public function showList(
        GroupeSolideRepository $_groupe,
        OperationGroupeRepository $_operation,
        Request $request
    )
    {

        $per_page = 10;

        $operations= [];

        $employe = (Auth::user())->employe;

        if($request->list== 'on'){
            $operations = $_operation->perpageGoupByCycle($per_page);
        }

        $caisses = $_groupe->caisseByAnimatrice($employe->id_employe);


        return view('groupement.operations.list_pardate',[
            'caisses'   =>$caisses,
            'operations'    =>$operations,
        ]);

    }

    public function search(Request $request)
    {

        session()->put('gp_operaiton', $request->all());

        return redirect()->route('gp.operation.showList', ['list'=>'on']);

    }

}
