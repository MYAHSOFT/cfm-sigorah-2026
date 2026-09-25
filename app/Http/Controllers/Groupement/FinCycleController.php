<?php

namespace App\Http\Controllers\Groupement;

use App\Http\Controllers\Controller;
use App\Models\Calendrier;
use App\Models\CDOperation;
use App\Models\CFDossier;
use App\Models\CFOperation;
use App\Models\Journal;
use App\Repositories\OperationGroupeRepository;
use Illuminate\Http\Request;
use DB;
class FinCycleController extends Controller
{

    protected $remboursements;

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
    public function index(OperationGroupeRepository $_operation)
    {

        $encours = $_operation->encoursByGroupe();

        return view('groupement.finCycle.index', [
            'encours'   =>$encours,
        ]);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($id_dossier)
    {

        $dossier = CFDossier::where('id_dossier', $id_dossier)->first();

        $cd_operation = CDOperation::join('cf_contrat_pret_groupes', 'cd_operations.pret_id','cf_contrat_pret_groupes.pret_id')
                            ->select('dossier_id',DB::raw("SUM(debit) mtt_octroye"))
                            ->where('dossier_id', $id_dossier)
                            ->groupBy('dossier_id')
                            ->first();

        $echeancier = Calendrier::join('cf_contrat_pret_groupes', 'cd_calendriers.pret_id','cf_contrat_pret_groupes.pret_id')
                            ->where('dossier_id', $id_dossier)
                            ->select('dossier_id', DB::raw("SUM(capital) capital, SUM(interet) interet"))
                            ->groupBy('dossier_id')
                            ->first();

        if(empty($echeancier)){
            return redirect()->route('gp.calendrier.create', $id_dossier);
        }

        $cf_operation = CFOperation::where('dossier_id', $id_dossier)
                            ->select('dossier_id',DB::raw("SUM(mtt_remb) mtt_remb, SUM(mtt_depot) mtt_depot, SUM(mtt_retrait) mtt_retrait, SUM(penalite) penalite"))
                            ->groupBy('dossier_id')
                            ->first();

        return view('groupement.finCycle.create', [
            'dossier'   =>$dossier,
            'cd_operation'   =>$cd_operation,
            'echeancier'   =>$echeancier,
            'cf_operation'   =>$cf_operation,
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

        $test = 1;

        if($test == 1){

            // return back();
            
        }

        $dossier = CFDossier::find($request->dossierId);

        $octrois = CDOperation::join('cf_contrat_pret_groupes', 'cd_operations.pret_id','cf_contrat_pret_groupes.pret_id')
                            ->where('dossier_id', $request->id_dossier)
                            ->get();

        $echeanciers = Calendrier::join('cf_contrat_pret_groupes', 'cd_calendriers.pret_id','cf_contrat_pret_groupes.pret_id')
                            ->where('dossier_id', $request->id_dossier)
                            ->get();

        $remboursements = [];

        $interets = [];

        foreach ($echeanciers as $echeancier) {
            $interets[$echeancier->ref_pret] = $echeancier->interet;
        }

        $num_piece = strtoupper(uniqid());

        foreach ($octrois as $octroi) {

            $remboursements[] = [
                'journal_id'    =>$octroi->journal_id,
                'pret_id'    =>$octroi->pret_id,
                'num_piece'    =>$num_piece,
                'date_oper'    =>$this->request->dateOper,
                'debit'    =>0,
                'credit'    =>$octroi->debit,
                'interet'    =>key_exists($octroi->ref_pret,$interets) ? $interets[$octroi->ref_pret] : 0,
                'penalite'    =>0,
            ];

        }

        DB::transaction(function () use ($dossier, $remboursements, $request){

            $id_journal = strtoupper(uniqid(config('groupement.caisseId')));

            $dateOper = new \DateTime($request->dateOper);

            $journal['id_journal']  = $id_journal;
            $journal['exo_id']  = $dateOper->format('Y');
            $journal['caisse_id']  = config('groupement.caisseId');
            $journal['employe_id']  = $dossier->animatrice;
            $journal['date_oper']  = $request->dateOper;
            $journal['cloture']  = 'N';
            $journal['type_journal']  = 'JOD';
            $journal['module'] = 'G';
            $journal['libelle'] = "JOURNAL DCF ".$dossier->animatrice;

            Journal::create($journal);

            CDOperation::insert($remboursements);

            CFDossier::where('id_dossier', $request->id_dossier)
                    ->update(['statut'=>'C']);
        });

        return redirect()->route('gp.finc.show', $request->id_dossier);

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

        $calendriers = Calendrier::join('cf_contrat_pret_groupes', 'cd_calendriers.pret_id','cf_contrat_pret_groupes.pret_id')
                            ->where('dossier_id', $id_dossier)
                            ->get();

        $interets = [];

        $sum_interet = 0;

        foreach ($calendriers as $calendrier) {

            $interets[$calendrier->ref_pret] = $calendrier->interet;

            $sum_interet += $calendrier->interet;
        }

        $cd_operations = CDOperation::join('cf_contrat_pret_groupes', 'cd_operations.pret_id','cf_contrat_pret_groupes.pret_id')
                            ->join('cd_contrats', 'cf_contrat_pret_groupes.pret_id','cd_contrats.id_pret')
                            ->join('cd_demandes','cd_contrats.demande_id','cd_demandes.tiers_id')
                            ->join('tiers','cd_demandes.tiers_id','tiers.id_tiers')
                            ->select('id_tiers','cd_operations.pret_id','nom_tiers','prenom_tiers','cin',
                                    DB::raw("SUM(debit) mtt_octroye"))
                            ->where('dossier_id', $id_dossier)
                            ->groupBy('id_tiers','cd_operations.pret_id','nom_tiers','prenom_tiers','cin')
                            ->orderBy('nom_tiers')
                            ->get();

        $sum_octroi = CDOperation::join('cf_contrat_pret_groupes', 'cd_operations.pret_id','cf_contrat_pret_groupes.pret_id')
                            ->where('dossier_id', $id_dossier)
                            ->sum('debit');

        $cf_operations = CFOperation::where('dossier_id', $id_dossier)
                                ->select('dossier_id','tiers_id',
                                        DB::raw("SUM(mtt_remb) mtt_remb, SUM(mtt_depot) mtt_depot, SUM(mtt_retrait) mtt_retrait, SUM(penalite) penalite"))
                                ->groupBy('dossier_id','tiers_id')
                                ->get();

        $encaisses = [];

        $solde_operation = 0;

        foreach ($cf_operations as $operation) {

            $solde_mvt = $operation->mtt_remb + $operation->mtt_depot - $operation->mtt_retrait;

            $encaisses[$operation->tiers_id] = $solde_mvt;

            $solde_operation += $solde_mvt;
        }

        return view('groupement.finCycle.show', [
            'dossier'   =>$dossier,
            'cd_operations'   =>$cd_operations,
            'interets'   =>$interets,
            'solde_operation'   =>$solde_operation,
            'encaisses'   =>$encaisses,
            'mtt_rembourser'   =>$sum_octroi + $sum_interet,
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
