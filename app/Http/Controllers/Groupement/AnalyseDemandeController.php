<?php

namespace App\Http\Controllers\Groupement;

use App\Models\Lending\ApplicationLoan;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Customer\Customer;
use App\Repositories\EmployeRepository;
use App\Repositories\CFDossierRepository;
use App\Repositories\DemandePretGroupeRepository;
use App\Repositories\GroupeSolideMembreRepository;

class AnalyseDemandeController extends Controller
{


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(
        DemandePretGroupeRepository $_demande,
        EmployeRepository $_employe,
        Request $request
    )
    {

        $per_page = 10;

        $decision = 'E';

        $animatrices = [];

        // session()->forget('cf_analyese');

        if($request->isMethod('post')){

            session()->put('cf_demande', json_encode($request->all()));

        }

        // dd(session()->all());

        $demandes = $_demande->paginateGroupes($decision,$per_page);

        $coach = (\Auth::user())->employe;

        $animatrices = $_employe->comboEmploye('ANIM', $coach->id_employe);

        return view('groupement.credits.analyses.index', [
            'demandes'  =>$demandes,
            'request'   =>$request,
            'animatrices'   =>$animatrices,
        ]);

    }


    public function create(
        DemandePretGroupeRepository $_demande,
        $ref_dde)
    {

        $demande = $_demande->find($ref_dde);

        $tiers = Customer::where('id_tiers', $demande->tiers_id)->first();

        return view('groupement.credits.analyses.create', [

            'demande'=>$demande,
            'tiers'=>$tiers,

        ]);

    }

    public function update(Request $request)
    {


        ApplicationLoan::where('ref_dde', $request->refDde)->update([
            'mtt_recommande'=>$request->mttCapital
        ]);

        return redirect()->route('gp.analyse.show', $request->dossierId);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(
        CFDossierRepository $_dossier,
        DemandePretGroupeRepository $_demande,
        GroupeSolideMembreRepository $_membre_groupe,
        $id_dossier)
    {

        $groupe = $_dossier->find($id_dossier);

        if(empty($groupe)){
            return abort(404);
        }

        $demandes = $_demande->getMembres($id_dossier);
        $decision = $_demande->statutDemande($id_dossier);

        if($decision == 'A'){
            session()->flash("info", "Cette demande a déjà été accordée.");
        }elseif($decision == 'R'){
            session()->flash("warning", "Cette demande a déjà été réfusé.");
        }

        $president = $_membre_groupe->getMembreBureauByFonction($groupe->id_groupe, 'PRD');

        $nb_accorde = $_demande->countMembres($id_dossier, 'A');

        $cart = session()->has('cf_cart_octroi') ? session('cf_cart_octroi') : [];


        return view('groupement.credits.analyses.show', [
            'groupe' =>$groupe,
            'president' =>$president,
            'demandes'   =>$demandes,
            'decision'   =>$decision,
            'nb_accorde'   =>$nb_accorde,
            'cart'  =>$cart,
            'action'    =>'analyse',
        ]);

    }
}
