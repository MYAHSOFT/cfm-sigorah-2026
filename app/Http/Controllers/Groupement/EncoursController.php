<?php

namespace App\Http\Controllers\Groupement;

use DB;
use App\Models\CFDossier;
use App\Models\CDOperation;
use App\Models\CFOperation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class EncoursController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $user = \Auth::user();

        $encours = CDOperation::join('cf_contrat_pret_groupes', 'cd_operations.pret_id', 'cf_contrat_pret_groupes.pret_id')
                            ->join('cf_groupe_solidarites', 'cf_contrat_pret_groupes.groupe_id','cf_groupe_solidarites.id_groupe')
                            ->join('cf_dossiers','cf_contrat_pret_groupes.dossier_id','cf_dossiers.id_dossier')
                            ->join('tiers','cf_groupe_solidarites.id_groupe','tiers.id_tiers')
                            ->where('agent_id', $user->name)
                            ->select('id_groupe','id_dossier','debut_cycle','nom_tiers','num_caisse', DB::raw("SUM(debit) mtt_octroi"))
                            ->groupBy('id_groupe','id_dossier','debut_cycle','nom_tiers','num_caisse')
                            ->orderBy('nom_tiers')
                            ->havingRaw('SUM(debit)>SUM(credit)')
                            ->get();

        $nb_encours = $encours->count();

        $sum_encours = 0;

        foreach($encours as $enc){
            $sum_encours += $enc->mtt_octroi;
        }

       return view('groupement.encours.index', [
        'encours'   =>$encours,
        'nb_encours'   =>$nb_encours,
        'sum_encours'   =>$sum_encours,
       ]);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id_dossier)
    {

        $dossier = CFDossier::where('id_dossier', $id_dossier)->first();

        if(empty($dossier->id_dossier)){
            return abort(404);
        }

        session()->put('id_groupe', $dossier->groupe_id);

        return redirect()->route('gp.contrat.show', $dossier->id_dossier);

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
    public function destroy($id)
    {
        //
    }
}
