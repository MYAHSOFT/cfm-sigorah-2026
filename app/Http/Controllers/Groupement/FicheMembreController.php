<?php

namespace App\Http\Controllers\Groupement;

use DB;
use App\Models\CFDossier;
use App\Models\CFOperation;
use Illuminate\Http\Request;
use App\Models\ContratPretGroupe;
use App\Http\Controllers\Controller;

class FicheMembreController extends Controller
{

    protected $request;

    public function __construct(Request $request)
    {

        $this->request = $request;

    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {


        $contrats = ContratPretGroupe::join('cd_contrats', 'cf_contrat_pret_groupes.pret_id', 'cd_contrats.id_pret')
                                ->join('cf_dossiers','cf_contrat_pret_groupes.dossier_id','cf_dossiers.id_dossier')
                                ->where('cf_dossiers.groupe_id', session('id_groupe'))
                                ->where(function($query){

                                    $archive = !empty($this->request->archive) ? $this->request->archive : date('Y');

                                    if($archive == 'all'){
                                        $query->whereYear('echeance', '<=' ,date('Y'));
                                    }else{
                                        $query->whereYear('echeance', $archive);
                                    }

                                })
                                ->select('cf_dossiers.groupe_id', 'id_dossier','date_contrat','statut','statut_octroi', DB::raw("SUM(`mtt_capital`) mtt_capital"))
                                ->groupBy('cf_dossiers.groupe_id', 'id_dossier', 'date_contrat','statut','statut_octroi')
                                ->orderBy('date_contrat','desc')
                                ->get();

        $docs = [];


        foreach ($contrats as $contrat) {

            $docs[] = $contrat->id_dossier;

        }

        $nb_contrat_all = ContratPretGroupe::where('groupe_id', session('id_groupe'))
                                    ->whereNotIn('dossier_id', $docs)
                                    ->select('dossier_id')
                                    ->groupBy('dossier_id')
                                    ->count();

        return view('groupement.fiche.index',[
            'contrats'  =>$contrats,
            'nb_contrat_all'  =>$nb_contrat_all,
            'archive'   =>$this->request->archive != 'all' ? $this->request->archive : '',
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
    public function show(Request $request, $id_tiers, $id_dossier)
    {

        $dossier = CFDossier::where('id_dossier', $id_dossier)->first();

        $tri = $request->tri == 'za' ? 'desc' : 'asc';

        $operations = CFOperation::where('tiers_id', $id_tiers)
                            ->where('dossier_id', $id_dossier)
                            ->orderBy('date_oper', $tri)
                            ->get();

        if($operations->count() == 0){
            return back();
        }

        $sum_operation = CFOperation::where('tiers_id', $id_tiers)
                            ->where('dossier_id', $id_dossier)
                            ->select('dossier_id','tiers_id',DB::raw("SUM(mtt_remb) mtt_remb, SUM(mtt_depot) mtt_depot, SUM(mtt_retrait) mtt_retrait, SUM(penalite) penalite"))
                            ->groupBy('dossier_id','tiers_id')
                            ->first();

        return view('groupement.fiche.show', [
            'operations'    =>$operations,
            'dossier'    =>$dossier,
            'sum_operation'    =>$sum_operation,
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
