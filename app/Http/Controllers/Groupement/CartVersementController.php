<?php

namespace App\Http\Controllers\Groupement;

use App\Models\Tiers;
use App\Models\CFDossier;
use App\Models\GroupeSolide;
use Illuminate\Http\Request;
use App\Models\CalendrierCycle;
use App\Models\ContratPretGroupe;
use App\Http\Controllers\Controller;
use App\Models\Echeancier;
use App\Repositories\OperationGroupeRepository;
use DB;

class CartVersementController extends Controller
{
    /**
     * Affichage de prêt actif.
     *
     * @return \Illuminate\Http\Response
     */

    public function index(OperationGroupeRepository $_operation)
    {

        $contrats = $_operation->getPretActif();

        return view('groupement.versement.index',[
            'contrats'  =>$contrats,
        ]);

    }

    public function echeancier($id_dossier)
    {
        
        $dossier = CFDossier::where('id_dossier', $id_dossier)->first();

        if(empty($dossier->id_dossier)){
            return abort(404);
        }

        $echeanciers = Echeancier::join('cf_contrat_pret_groupes', 'cf_echeanciers.pret_id', 'cf_contrat_pret_groupes.pret_id')
                        ->where('dossier_id', $id_dossier)
                        ->select('dossier_id', 'date_oper', DB::raw("SUM(montant) montant"))
                        ->groupBy('dossier_id','date_oper')
                        ->get();

        $groupe = GroupeSolide::join('tiers', 'cf_groupe_solidarites.id_groupe', 'tiers.id_tiers')
                        ->where('id_groupe', session('id_groupe'))
                        ->first();

        $sum_echeance = CalendrierCycle::where('dossier_id', $id_dossier)
                        ->sum('montant');

        $nb_echeance = $echeanciers->count() > 0 ? $echeanciers->count() : 0;

        return view('groupement.versement.echeancier', [
            'groupe' =>$groupe,
            'dossier' =>$dossier,
            'echeanciers'   =>$echeanciers,
            'nb_echeance'   =>$nb_echeance,
            'sum_echeance'   =>$sum_echeance,
        ]);

    }

    public function create(
        Request $request,
        $id_tiers, 
        $id_dossier)
    {

        $membre = Tiers::join('cf_membres','tiers.id_tiers','=','cf_membres.tiers_id')
                        ->where('id_tiers', $id_tiers)
                        ->first();

        if(empty($membre->id_tiers)){
            return abort(404);
        }

        $reunion = $request->reunion;

        $contrat = ContratPretGroupe::join('cd_contrats', 'cf_contrat_pret_groupes.pret_id','cd_contrats.id_pret')
                            ->join('cd_demandes','cd_contrats.demande_id','cd_demandes.id_demande')
                            ->where('dossier_id', $id_dossier)
                            ->where('tiers_id', $id_tiers)
                            ->first();
        $id_pret = !empty($contrat->id_pret)? $contrat->id_pret : '';
        
        $operation = Echeancier::where('pret_id', $id_pret)
                        ->where('date_oper', session('reunion'))
                        ->select('cf_echeanciers.*', DB::raw("montant AS mtt_remb"))
                        ->first();

        return view('groupement.versement.create',[
            'membre'    =>$membre,
            'operation'    =>$operation,
            'contrat'    =>$contrat,
            'id_dossier'    =>$id_dossier,
            'reunion'    =>$reunion,
            'url'   =>route('gp.versement.store', ['reunion'=>$request->reunion]),
        ]);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $data = session()->get('cf_versement');

        $data[$request->folio] = $request->all();

        session()->put('cf_versement', $data);


        return redirect()->route('gp.versement.show', [
            'id_dossier'    =>$request->dossierId,
            'reunion'   =>'2024-02-13',
        ]);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id_dossier)
    {

        $dossier = CFDossier::where('id_dossier', $id_dossier)->first();

        if(empty($dossier->id_dossier)){
            return abort(404);
        }

        $reunion = $request->reunion;

        session()->put('reunion',$request->reunion);

        $membres = Tiers::join('cf_membres','tiers.id_tiers','=','cf_membres.tiers_id')
                        ->where('groupe_id',$dossier->groupe_id)
                        ->where('status', 'A')
                        ->orderBy('profil')
                        ->orderBy('nom_tiers')
                        ->get();

        $echMembres = Echeancier::join('cf_contrat_pret_groupes','cf_echeanciers.pret_id','cf_contrat_pret_groupes.pret_id')
                        ->join('cd_contrats','cf_contrat_pret_groupes.pret_id','cd_contrats.id_pret')
                        ->join('cd_demandes','cd_contrats.demande_id','cd_demandes.id_demande')
                        ->where('date_oper', $reunion)
                        ->where('dossier_id', $id_dossier)
                        ->get();

        $echeanciers = [];

        foreach ($echMembres as $ech) {
            
            $echeanciers[$ech->tiers_id] = $ech->montant;

        }
        

        $contrats = ContratPretGroupe::join('cd_contrats', 'cf_contrat_pret_groupes.pret_id','cd_contrats.id_pret')
                            ->where('dossier_id', $id_dossier)
                            ->get();
        

        $carts = session()->has('cf_versement') ? session('cf_versement') : [];

        $versements = [];

        $sum_remb = 0;
        $sum_depot = 0;
        $sum_retrait = 0;
        $sum_penalite = 0;

        foreach ($carts as $cart) {

            $cart = (object)$cart;

            if(!empty($cart->folio)){

                $remboursement = (double)$cart->remboursement;
                $depot = (double)$cart->depot;
                $retrait = (double)$cart->retrait;
                $penalite = (double)$cart->penalite;

                $sum_remb += $remboursement;
                $sum_depot += $depot;
                $sum_retrait += $retrait;
                $sum_penalite += $penalite;

                $versements[$cart->folio] = [
                    'versement' =>$remboursement + $depot + $penalite,
                    'retrait'   =>$retrait,
                ];
            }

        }

        $sum = (object)[
            'remboursement' =>$sum_remb,
            'depot' =>$sum_depot,
            'retrait' =>$sum_retrait,
            'penalite' =>$sum_penalite,
        ];

        return view('groupement.versement.show', [
            'membres' =>$membres,
            'dossier' =>$dossier,
            'echeanciers' =>$echeanciers,
            'versements' =>$versements,
            'reunion' =>$reunion,
            'sum' =>$sum,
            // 'date_dde'=>$date_dde,
        ]);

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy()
    {
        session()->forget('cf_versement');

        return back();
    }
}
