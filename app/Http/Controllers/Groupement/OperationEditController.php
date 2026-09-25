<?php

namespace App\Http\Controllers\Groupement;

use App\Models\Customer\Customer;
use App\Models\Association\Cycle;
use App\Models\Association\MeetingOperation;
use Illuminate\Http\Request;
use App\Models\Association\GroupLoanContract;
use App\Http\Controllers\Controller;

class OperationEditController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
    public function show($id_oper)
    {

        $operation = MeetingOperation::where('id_oper', $id_oper)->first();


        if(empty($operation->id_oper)){
            return back();
        }

        $dossier = Cycle::where('id_dossier', $operation->dossier_id)->first();

        if($dossier->statut == 'C'){
            // return back();
        }

        $membre = Customer::join('cf_membres','tiers.id_tiers','=','cf_membres.tiers_id')
                        ->where('id_tiers', $operation->tiers_id)
                        ->first();

        $contrat = GroupLoanContract::join('cd_contrats', 'cf_contrat_pret_groupes.pret_id','cd_contrats.id_pret')
                            ->join('cd_demandes','cd_contrats.demande_id','cd_demandes.id_demande')
                            ->where('dossier_id', $operation->dossier_id)
                            ->where('tiers_id', $operation->tiers_id)
                            ->first();

        return view('groupement.operations.edit', [
            'membre' =>$membre,
            'contrat' =>$contrat,
            'operation' =>$operation,
            'id_dossier'    =>$operation->dossier_id,
        ]);

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id_oper)
    {

        $operation = MeetingOperation::where('id_oper', $id_oper)->first();


        if(empty($operation->id_oper)){
            return back();
        }

        $dossier = Cycle::where('id_dossier', $operation->dossier_id)->first();

        if($dossier->statut == 'C'){
            return back();
        }

        $membre = Customer::join('cf_membres','tiers.id_tiers','=','cf_membres.tiers_id')
                        ->where('id_tiers', $operation->tiers_id)
                        ->first();

        $contrat = GroupLoanContract::join('cd_contrats', 'cf_contrat_pret_groupes.pret_id','cd_contrats.id_pret')
                            ->join('cd_demandes','cd_contrats.demande_id','cd_demandes.id_demande')
                            ->where('dossier_id', $operation->dossier_id)
                            ->where('tiers_id', $operation->tiers_id)
                            ->first();

        return view('groupement.versement.create', [
            'membre' =>$membre,
            'contrat' =>$contrat,
            'operation' =>$operation,
            'id_dossier'    =>$operation->dossier_id,
            'url'   =>route('gp.operation-edit.update', $operation->id_oper),
        ]);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id_oper)
    {

        MeetingOperation::where('id_oper', $id_oper)->update([
            'mtt_remb'  =>$request->remboursement,
            'mtt_depot' =>$request->depot,
            'mtt_retrait'   =>$request->retrait,
            'penalite'  =>$request->penalite,
        ]);

        return redirect()->route('gp.operation-edit.show', $id_oper);

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
