<?php

namespace App\Http\Controllers\Groupement;

use DB;
use App\Models\Tiers;
use App\Models\CFDossier;
use App\Models\CFOperation;
use App\Models\ContratPret;
use Illuminate\Http\Request;
use App\Models\OperationGroupe;
use App\Models\ContratPretGroupe;
use App\Http\Controllers\Controller;
use App\Repositories\TiersRepository;
use App\Repositories\CFDossierRepository;
use Illuminate\Support\Facades\Validator;
use App\Repositories\CycleActiviteRepository;
use App\Repositories\OperationGroupeRepository;
use App\Repositories\GroupeSolideMembreRepository;

class OperationController extends Controller
{
    /**
     * Affichage de prêt actif.
     *
     * @return \Illuminate\Http\Response
     */

    public function index()
    {

        $operations = CFOperation::join('cf_dossiers', 'cf_operations.dossier_id', 'cf_dossiers.id_dossier')
                            ->select('id_dossier')
                            ->where('groupe_id', session('id_groupe'))
                            ->groupBy('id_dossier')
                            ->get();

        $dossiers = [];

        foreach ($operations as $operation) {
            $dossiers[] = $operation->id_dossier;
        }

        $contrats = CFDossier::join('cf_contrat_pret_groupes','cf_dossiers.id_dossier','cf_contrat_pret_groupes.dossier_id')
                        ->join('cd_operations','cf_contrat_pret_groupes.pret_id','=','cd_operations.pret_id')
                        ->whereIn('id_dossier', $dossiers)
                        ->select('id_dossier', DB::raw("MIN(date_oper) date_oper, SUM(debit) debit, SUM(credit) credit"))
                        ->groupBy('id_dossier')
                        ->get();

        return view('groupement.operations.index',[
            'contrats'  =>$contrats,
        ]);

    }

    public function search(Request $request)
    {

        session()->put('gp_operation', json_encode($request->all()));

        return redirect()->route('gp.operation.index', ['list'=>'on']);

    }

    public function check(Request $request)
    {

        $validate = Validator::make($request->all(), [
            'dateOper'  => 'required|date'
        ]);

        if($validate->fails()){
            session()->flash('warning', "La date d'opération n'est pas valide. Veuillez respecter la format dd/mm/yyyy.");
            return back()->withErrors($validate)->withInput();
        }

        session()->put('cf_operation_date', $request->dateOper);

        return redirect()->route('gp.operation.create', $request->dossierId);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request, $id_dossier)
    {

        $dossier = CFDossier::where('id_dossier', $id_dossier)->first();

        if(empty($dossier->id_dossier)){
            return abort(404);
        }

        if($dossier->statut != 'A'){
            return back();
        }

        $reunion = $request->reunion;

        $carts = session()->has('cf_versement') ? session('cf_versement') : [];$versements = [];

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

            }

        }

        $sum = (object)[
            'remboursement' =>$sum_remb,
            'depot' =>$sum_depot,
            'retrait' =>$sum_retrait,
            'penalite' =>$sum_penalite,
        ];

        return view('groupement.operations.create',[
            'dossier'    =>$dossier,
            'carts'    =>$carts,
            'reunion'    =>$reunion,
            'sum'    =>$sum,
            'url'   =>route('gp.operation.store'),
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

        //`groupe_id`, `tiers_id`, `date_oper`, `mtt_remb`, `mtt_depot`, `mtt_retrait`, `penalite`

        $carts = session()->has('cf_versement') ? session('cf_versement') : [];$versements = [];

        $i = 0;

        $ref_operation = uniqid('CF');

        foreach ($carts as $cart) {

            $operations[$i] = [
                "dossier_id"=>$request->dossierId,
                "tiers_id"=>$cart["folio"],
                "date_oper"=>$request->dateOper,
                "date_reunion"=>$request->dateOper,//session('reunion'),
                "mtt_remb"=>key_exists('remboursement', $cart)?(double)$cart["remboursement"]:0,
                "mtt_depot"=>key_exists("depot",$cart)?(double)$cart["depot"]:0,
                "mtt_retrait"=>key_exists("retrait",$cart)?(double)$cart["retrait"]:0,
                "penalite"=>key_exists("penalite",$cart)?(double)$cart["penalite"]:0,
                'ref_operation' =>$ref_operation,
            ];

            $i++;

        }

        OperationGroupe::insert($operations);

        session()->forget("cf_versement");

        session()->forget("reunion");

        session()->flash("success", "Les opérations ont été bien enregistré avec succès.");

        return redirect()->route('gp.operation.show',$request->dossierId);


    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id_dossier)
    {

        //`mtt_remb`, `mtt_depot`, `mtt_retrait`, `penalite`

        $dossier = CFDossier::where('id_dossier', $id_dossier)->first();

        if(empty($dossier->id_dossier)){
            return abort(404);
        }

        $tri = $request->tri == 'desc' ? 'desc' : 'asc';

        $operations = CFOperation::where('dossier_id', $id_dossier)
                            ->select('dossier_id','ref_operation','date_oper', DB::raw("SUM(mtt_remb) mtt_remb,
                                    SUM(mtt_depot) mtt_depot,
                                    SUM(mtt_retrait) mtt_retrait,
                                    SUM(penalite) penalite"))
                            ->groupBy('dossier_id','date_oper', 'ref_operation')
                            ->orderBy('date_oper', $tri)
                            ->get();

        return view('groupement.operations.show', [
            'dossier' =>$dossier,
            'operations' =>$operations,
        ]);

    }

    public function showDetail($id_dossier, $ref_operation)
    {

        $dossier = CFDossier::where('id_dossier', $id_dossier)->first();

        if(empty($dossier->id_dossier)){
            return abort(404);
        }



        $operations = CFOperation::join('tiers', 'cf_operations.tiers_id','tiers.id_tiers')
                            ->join('cf_membres','tiers.id_tiers','cf_membres.tiers_id')
                            ->where('ref_operation', $ref_operation)
                            ->orderBy('profil')
                            ->orderBy('nom_tiers')
                            ->get();

        $sum_operation = CFOperation::where('ref_operation', $ref_operation)
                            ->where('dossier_id', $id_dossier)
                            ->select('dossier_id','ref_operation','date_oper', DB::raw("SUM(mtt_remb) mtt_remb,
                                    SUM(mtt_depot) mtt_depot,
                                    SUM(mtt_retrait) mtt_retrait,
                                    SUM(penalite) penalite"))
                            ->groupBy('dossier_id','date_oper', 'ref_operation')
                            ->first();


        $nb_contrat = $operations->count() > 0 ? $operations->count() : 0;

        //`mtt_remb`, `mtt_depot`, `mtt_retrait`, `penalite`

        $dossier = CFDossier::where('id_dossier', $id_dossier)->first();

        if(empty($dossier->id_dossier)){
            return abort(404);
        }

        return view('groupement.operations.showDetail', [
            'dossier' =>$dossier,
            'operations' =>$operations,
            'sum_operation' =>$sum_operation,
            'nb_contrat' =>$nb_contrat,
            'ref_operation' =>$ref_operation,
        ]);

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id_dossier, $ref_operation)
    {

        $dossier = CFDossier::where('id_dossier', $id_dossier)->first();

        if(empty($dossier->id_dossier)){
            return abort(404);
        }



        $operations = CFOperation::join('tiers', 'cf_operations.tiers_id','tiers.id_tiers')
                            ->join('cf_membres','tiers.id_tiers','cf_membres.tiers_id')
                            ->where('ref_operation', $ref_operation)
                            ->orderBy('profil')
                            ->orderBy('nom_tiers')
                            ->get();

        $sum_operation = CFOperation::where('ref_operation', $ref_operation)
                            ->where('dossier_id', $id_dossier)
                            ->select('dossier_id','ref_operation','date_oper', DB::raw("SUM(mtt_remb) mtt_remb,
                                    SUM(mtt_depot) mtt_depot,
                                    SUM(mtt_retrait) mtt_retrait,
                                    SUM(penalite) penalite"))
                            ->groupBy('dossier_id','date_oper', 'ref_operation')
                            ->first();


        $nb_contrat = $operations->count() > 0 ? $operations->count() : 0;

        //`mtt_remb`, `mtt_depot`, `mtt_retrait`, `penalite`

        $dossier = CFDossier::where('id_dossier', $id_dossier)->first();

        if(empty($dossier->id_dossier)){
            return abort(404);
        }

        return view('groupement.operations.edit-date-oper', [
            'dossier' =>$dossier,
            'operations' =>$operations,
            'sum_operation' =>$sum_operation,
            'nb_contrat' =>$nb_contrat,
            'ref_operation' =>$ref_operation,
        ]);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {

        $request->validate([
            'dateOper'  =>'required|date'
        ],[
            'dateOper.required' =>"La date d'opération est obligatoire",
            'dateOper.date' =>"Le format de date n'est pas valide"
        ]);

        CFOperation::where('dossier_id', $request->dossierId)
            ->where('ref_operation', $request->refOperation)
            ->update(['date_oper'  =>$request->dateOper]);

        return redirect()->route('gp.operation.show', $request->dossierId);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id_tiers)
    {

        $data = session()->get('cf_operation');

        if(key_exists($id_tiers,$data)){
            unset($data[$id_tiers]);
            session()->flash('info', "Une opération a été supprimer avec succès");
        }

        session()->put('cf_operation', $data);

        return back();

    }

    public function clear(Request $request)
    {
        if(!empty($request->dossierId)){

            session()->forget('cf_operation');

            session()->flash('success', "Les saisies en cours a été supprimer avec succès.");

        }

        return back();
    }

    public function getMembre($id_tiers, $ref_pret = null)
    {

        $membre = (new TiersRepository)->find($id_tiers);

        $contrat = ContratPret::where('ref_pret', $ref_pret)->first();


        return response()->json(['data'=>[
            "membre"    =>[
                'nom'   =>$membre->nom_tiers,
                'prenom'   =>!empty($membre->prenom_tiers) ? $membre->prenom_tiers : "",
                'cin'   =>$membre->cin,
            ],
        ]]);

    }
}
