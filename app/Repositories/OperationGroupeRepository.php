<?php
namespace App\Repositories;

use DB;
use App\Models\Association\Cycle;
use App\Models\Lending\TransactionLoan;
use App\Models\Association\MeetingOperation;

class OperationGroupeRepository
{

    public function encaisseByGroupe($status,$per_page = 10)
    {
        return MeetingOperation::rightJoin('cf_dossiers', 'cf_operations.dossier_id','cf_dossiers.id_dossier')
                        ->join('cf_groupe_solidarites','cf_dossiers.groupe_id','cf_groupe_solidarites.id_groupe')
                        ->join('tiers','cf_groupe_solidarites.id_groupe','tiers.id_tiers')
                        ->where(function($query){

                            $search = session()->has('gp_operation') ? json_decode(session('gp_operation')) : (object)[];

                            $caisse = !empty($search->caisse) ? $search->caisse : "";

                            if(!empty($caisse)){
                                $query->where('tiers.caisse_id', $caisse);
                            }

                            $employe = \Auth::user()->employe;

                            $animatrices = [$employe->id_employe];

                            $query->whereIn('animatrice_id', $animatrices);

                        })
                        ->where(function($query){

                            $search = session()->has('gp_operation') ? json_decode(session('gp_operation')) : (object)[];

                            $groupe_id = !empty($search->groupeId) ? $search->groupeId : "";

                            if(!empty($groupe_id)){

                                $query->orWhere('nom_tiers','like', '%'.$groupe_id.'%');
                                $query->orWhere('prenom_tiers','like', '%'.$groupe_id.'%');
                                $query->orWhere('num_caisse','=', $groupe_id);
                                $query->orWhere('id_groupe','=', $groupe_id);
                                $query->orWhere('id_tiers','=', $groupe_id);


                            }
                        })
                        ->whereIn('statut', $status)
                        ->select('id_groupe','id_dossier','num_caisse','debut_cycle','cf_groupe_solidarites.id_groupe','nom_tiers','prenom_tiers','caisse_id',
                            DB::raw("(SUM(mtt_remb)+SUM(mtt_depot)-SUM(mtt_retrait)) AS encaisse, SUM(penalite) AS penalite"))
                        ->groupBy('id_groupe','id_dossier','num_caisse','debut_cycle','cf_groupe_solidarites.id_groupe','nom_tiers','prenom_tiers','caisse_id')
                        ->paginate($per_page);

    }

    public function operationByMembre($id_dossier,$per_page=10)
    {
        return MeetingOperation::join('tiers','cf_operations.tiers_id','tiers.id_tiers')
                        ->where('dossier_id', $id_dossier)
                        ->paginate($per_page);
    }

    public function operationByTiers($id_tiers, $id_dossier,$per_page=10)
    {
        return MeetingOperation::join('tiers','cf_operations.tiers_id','tiers.id_tiers')
                        ->where('tiers_id', $id_tiers)
                        ->where('dossier_id', $id_dossier)
                        ->paginate($per_page);
    }

    public function getOperationByTiers($id_tiers, $id_cycle,$per_page=10)
    {
        return MeetingOperation::join('tiers','cf_operations.tiers_id','tiers.id_tiers')
                        ->where('cycle_id', $id_cycle)
                        ->paginate($per_page);
    }

    public function getGroupeByDate($id_dossier)
    {
        return MeetingOperation::where('dossier_id', $id_dossier)
                        ->select('date_oper',
                                DB::raw("SUM(mtt_remb) AS mtt_remb,
                                SUM(mtt_depot) AS mtt_depot,
                                SUM(mtt_retrait) AS mtt_retrait,
                                SUM(penalite) AS penalite"))
                        ->groupBy('date_oper')
                        ->get();
    }

    public function getMembreAllByDate($id_dossier, $date_oper)
    {
        return MeetingOperation::join('tiers','cf_operations.tiers_id','tiers.id_tiers')
                        ->where('dossier_id', $id_dossier)
                        ->where('date_oper', $date_oper)
                        ->select('id_tiers','nom_tiers','prenom_tiers','cin','dossier_id','date_oper','photo','signature',
                                DB::raw("SUM(mtt_remb) AS mtt_remb,
                                SUM(mtt_depot) AS mtt_depot,
                                SUM(mtt_retrait) AS mtt_retrait,
                                SUM(penalite) AS penalite"))
                        ->groupBy('id_tiers','nom_tiers','prenom_tiers','cin','dossier_id','date_oper','photo','signature')
                        ->get();
    }

    public function perpageGoupByCycle($per_page)
    {

        return MeetingOperation::join('cf_dossiers', 'cf_operations.dossier_id','cf_dossiers.id_dossier')
                        ->join('cf_groupe_solidarites','cf_dossiers.groupe_id','cf_groupe_solidarites.id_groupe')
                        ->join('tiers','cf_groupe_solidarites.id_groupe','tiers.id_tiers')
                        ->where(function($query){


                            $employe = \Auth::user()->employe;

                            $animatrices = [$employe->id_employe];

                            $query->whereIn('animatrice_id', $animatrices);

                        })
                        ->select('id_groupe','date_oper','id_dossier','cf_groupe_solidarites.id_groupe','nom_tiers','caisse_id',
                            DB::raw("SUM(mtt_remb) AS mtt_remb,
                                    SUM(mtt_depot) AS mtt_depot,
                                    SUM(mtt_retrait) AS mtt_retrait,
                                    SUM(penalite) AS penalite"))
                        ->orderBy('date_oper','desc')
                        ->groupBy('id_groupe','date_oper','id_dossier','cf_groupe_solidarites.id_groupe','nom_tiers','caisse_id')
                        ->paginate($per_page);
    }

    public function getPretActif()
    {


        $user = \Auth::user();

        $encours = TransactionLoan::join('cf_contrat_pret_groupes', 'cd_operations.pret_id', 'cf_contrat_pret_groupes.pret_id')
                            ->join('cf_groupe_solidarites', 'cf_contrat_pret_groupes.groupe_id','cf_groupe_solidarites.id_groupe')
                            ->join('cf_dossiers','cf_contrat_pret_groupes.dossier_id','cf_dossiers.id_dossier')
                            ->join('tiers','cf_groupe_solidarites.id_groupe','tiers.id_tiers')
                            ->where('agent_id', $user->name)
                            ->where('cf_contrat_pret_groupes.groupe_id', session('id_groupe'))
                            ->select('id_groupe','id_dossier','debut_cycle','nom_tiers','num_caisse', DB::raw("MIN(date_oper) date_oper,SUM(debit) mtt_octroi"))
                            ->groupBy('id_groupe','id_dossier','debut_cycle','nom_tiers','num_caisse')
                            ->orderBy('nom_tiers')
                            ->havingRaw('SUM(debit)>SUM(credit)')
                            ->get();

        return $encours;

    }

    public function encoursByGroupe()
    {

        $user = \Auth::user();

        $encours = TransactionLoan::join('cf_contrat_pret_groupes', 'cd_operations.pret_id', 'cf_contrat_pret_groupes.pret_id')
                            ->join('cf_groupe_solidarites', 'cf_contrat_pret_groupes.groupe_id','cf_groupe_solidarites.id_groupe')
                            ->join('cf_dossiers','cf_contrat_pret_groupes.dossier_id','cf_dossiers.id_dossier')
                            ->join('tiers','cf_groupe_solidarites.id_groupe','tiers.id_tiers')
                            ->where('cf_dossiers.groupe_id', session('id_groupe'))
                            ->select('id_groupe','id_dossier','debut_cycle','nom_tiers','num_caisse', DB::raw("MIN(date_oper) date_oper,SUM(debit) mtt_octroi"))
                            ->groupBy('id_groupe','id_dossier','debut_cycle','nom_tiers','num_caisse')
                            ->orderBy('nom_tiers')
                            ->havingRaw('SUM(debit)>SUM(credit)')
                            ->get();

        return $encours;

    }


    public function pretEchus($per_page = 10)
    {

        return Cycle::join('cf_groupe_solidarites','cf_dossiers.groupe_id','cf_groupe_solidarites.id_groupe')
                                ->join('cf_date_fin_cycle','cf_dossiers.id_dossier','cf_date_fin_cycle.dossier_id')
                                ->join('tiers','cf_groupe_solidarites.id_groupe','=','tiers.id_tiers')
                                ->where('statut', 'C')
                                ->where(function($query){

                                    $search = session()->has('cf_contrat') ? json_decode(session('cf_contrat')) : (object)[];

                                    $year = !empty($search->year) ? $search->year : date('Y');

                                    $id_groupe = !empty($search->codeGroupe) ? $search->codeGroupe : "";

                                    $nom = !empty($search->nom) ? $search->nom : "";

                                    $employe_id = !empty($search->employeId) ? $search->employeId : "";

                                    $employe = (\Auth::user())->employe;

                                    $employes = !empty($search->employe) ? $search->employe : [$employe->id_employe];


                                    $query->whereYear('last_oper', $year);

                                    if(!empty($id_groupe)){
                                        $query->where('id_groupe', $id_groupe);
                                    }

                                    if(!empty($nom)){
                                        $query->where('nom_tiers','like', "%$nom%");
                                    }

                                    if(!empty($employe_id)){

                                        $query->where('animatrice', $employe_id);

                                    }else{

                                        $query->whereIn('animatrice', $employes);

                                    }

                                })
                                ->paginate($per_page);

    }

    public function pretActifAll()
    {

        return DB::table("cf_pret_actif")
                    ->join('cf_dossiers','cf_pret_actif.dossier_id','=','cf_dossiers.id_dossier')
                    ->join('cf_groupe_solidarites','cf_pret_actif.groupe_id','=','cf_groupe_solidarites.id_groupe')
                    ->join('tiers','cf_groupe_solidarites.id_groupe','=','tiers.id_tiers')
                    ->where(function($query){

                        $search = session()->has('cf_contrat') ? json_decode(session('cf_contrat')) : (object)[];

                        $id_groupe = !empty($search->codeGroupe) ? $search->codeGroupe : "";

                        $nom = !empty($search->nom) ? $search->nom : "";

                        $employe_id = !empty($search->employeId) ? $search->employeId : "";

                        $employe = (\Auth::user())->employe;

                        $employes = !empty($search->employe) ? $search->employe : [$employe->id_employe];

                        if(!empty($id_groupe)){
                            $query->where('id_groupe', $id_groupe);
                        }

                        if(!empty($nom)){
                            $query->where('nom_tiers','like', "%$nom%");
                        }

                        if(!empty($employe_id)){

                            $query->where('animatrice', $employe_id);

                        }else{

                            $query->whereIn('animatrice', $employes);

                        }

                    })
                    ->get();

    }

}
