<?php

namespace App\Http\Controllers\Groupement;

use DB;
use App\Models\Association\Cycle;
use App\Models\Association\Group;
use Illuminate\Http\Request;
use App\Models\Association\CycleCalendar;
use App\Models\Association\MeetingOperation;
use App\Models\Association\GroupLoanContract;
use App\Models\EmployeResponsable;
use App\Http\Controllers\Controller;
use App\Repositories\CFDossierRepository;
use App\Repositories\ContratPretGroupeRepository;
use App\Repositories\GroupeSolideMembreRepository;

class ContratPretController extends Controller
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


        $contrats = GroupLoanContract::join('cd_contrats', 'cf_contrat_pret_groupes.pret_id', 'cd_contrats.id_pret')
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

        $nb_contrat_all = GroupLoanContract::where('groupe_id', session('id_groupe'))
                                    ->whereNotIn('dossier_id', $docs)
                                    ->select('dossier_id')
                                    ->groupBy('dossier_id')
                                    ->count();

        return view('groupement.contrats.index',[
            'contrats'  =>$contrats,
            'nb_contrat_all'  =>$nb_contrat_all,
            'archive'   =>$this->request->archive != 'all' ? $this->request->archive : '',
        ]);

    }

    public function search()
    {

        $archive = !empty($this->request->archive) ? $this->request->archive : 'all';

        return redirect()->route('gp.contrat.index',['archive'=>$archive]);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id_dossier)
    {

        $dossier = Cycle::where('id_dossier', $id_dossier)->first();

        if(empty($dossier->id_dossier)){
            return abort(404);
        }

        $groupe = Group::join('tiers', 'cf_groupe_solidarites.id_groupe', 'tiers.id_tiers')
                        ->where('id_groupe', session('id_groupe'))
                        ->first();

        $contrats = GroupLoanContract::join('cd_contrats', 'cf_contrat_pret_groupes.pret_id', 'cd_contrats.id_pret')
                        ->join('cd_demandes','cd_contrats.demande_id','cd_demandes.id_demande')
                        ->join('tiers', 'cd_demandes.tiers_id', 'tiers.id_tiers')
                        ->where('dossier_id', $id_dossier)
                        ->get();


        $sum_contrat = GroupLoanContract::join('cd_contrats', 'cf_contrat_pret_groupes.pret_id', 'cd_contrats.id_pret')
                        ->where('dossier_id', $id_dossier)
                        ->sum('mtt_capital');

        $nb_contrat = $contrats->count() > 0 ? $contrats->count() : 0;

        return view('groupement.contrats.show', [
            'groupe' =>$groupe,
            'dossier' =>$dossier,
            'contrats'   =>$contrats,
            'nb_demande'   =>$nb_contrat,
            'sum_demande'   =>$sum_contrat,
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
