<?php
namespace App\Repositories;

use App\Models\DemandePretGroupe;
use DB;
class DemandePretGroupeRepository
{

    public function getMembres($id_dossier, $decision='E')
    {
        return DemandePretGroupe::join('cd_demandes','cf_demande_pret_groupes.ref_dde','cd_demandes.id_demande')
                    ->join('tiers','cd_demandes.tiers_id','=','tiers.id_tiers')
                    ->where('dossier_id', $id_dossier)
                    ->where('decision','=',$decision)
                    ->get();
    }

    public function get($id_dossier)
    {
        return DemandePretGroupe::join('cd_demandes','cf_demande_pret_groupes.ref_dde','cd_demandes.id_demande')
                    ->join('tiers','cd_demandes.tiers_id','=','tiers.id_tiers')
                    ->where('dossier_id', $id_dossier)
                    ->get();
    }

    public function countByDecision($id_dossier,$decision = 'E')
    {
        return DemandePretGroupe::join('cd_demandes','cf_demande_pret_groupes.ref_dde','cd_demandes.id_demande')
                    ->where([
                        ['dossier_id', $id_dossier],
                        ['decision', $decision],
                        ])
                    ->count();
    }

    public function count($id_dossier)
    {
        return DemandePretGroupe::join('cd_demandes','cf_demande_pret_groupes.ref_dde','cd_demandes.id_demande')
                    ->where('dossier_id', $id_dossier)
                    ->count();
    }

    public function sum($id_dossier)
    {
        return DemandePretGroupe::join('cd_demandes','cf_demande_pret_groupes.ref_dde','cd_demandes.id_demande')
                    ->where('dossier_id', $id_dossier)
                    ->sum('mtt_capital');
    }

    public function statutDemande($id_dossier)
    {

        $statut =  DemandePretGroupe::join('cd_demandes','cf_demande_pret_groupes.ref_dde','cd_demandes.id_demande')
                    ->where('dossier_id', $id_dossier)
                    ->select('decision')
                    ->groupBy('decision')
                    ->first();

        return !empty($statut->decision) ? $statut->decision : null;

    }

    public function find($ref_dde)
    {
        return DemandePretGroupe::join('cd_demandes','cf_demande_pret_groupes.ref_dde','cd_demandes.id_demande')
                    ->join('tiers','cd_demandes.tiers_id','=','tiers.id_tiers')
                    ->where('cd_demandes.id_demande', $ref_dde)
                    ->first();
    }

    public function getGroupes($id_groupe)
    {
        return DemandePretGroupe::join('cd_demandes','cf_demande_pret_groupes.ref_dde','cd_demandes.id_demande')
                    ->join('tiers','cd_demandes','cd_demandes.tiers_id','tiers.id_tiers')
                    ->where('groupe_id', $id_groupe)
                    ->get();
    }

    public function getGroupesByDossier($id_dossier)
    {
        return DemandePretGroupe::join('cd_demandes','cf_demande_pret_groupes.ref_dde','cd_demandes.id_demande')
                    ->where('dossier_id', $id_dossier)
                    ->get();
    }

    public function paginateGroupes($decision,$per_page=10)
    {
        return DemandePretGroupe::join('cd_demandes','cf_demande_pret_groupes.ref_dde','cd_demandes.id_demande')
                            ->join('cf_groupe_solidarites', 'cf_demande_pret_groupes.groupe_id','=','cf_groupe_solidarites.id_groupe')
                            ->join('cf_dossiers', 'cf_demande_pret_groupes.dossier_id','=','cf_dossiers.id_dossier')
                            ->join('tiers','cf_groupe_solidarites.id_groupe','=','tiers.id_tiers')
                            ->select('id_groupe','id_tiers','nom_tiers','id_dossier','date_dde','animatrice',
                                    DB::raw("SUM(mtt_capital) AS mtt_capital, SUM(mtt_recommande) AS mtt_recommande"))
                            ->where('decision','=',$decision)
                            ->where(function($query){

                                // $animatrice = session()->has('agent') ? session('agent') : null;

                                $session = session()->has('cf_demande') ? json_decode(session('cf_demande')) : (object)[];

                                $animatrice = !empty($session->animatrice) ? $session->animatrice : null;

                                $id_groupe = !empty($session->codeGroupe) ? $session->codeGroupe : null;


                                if(session('agent')){

                                    $query->where('animatrice', session('agent'));

                                }else{

                                    if(!empty($animatrice)){
                                        $query->where('animatrice', $animatrice);
                                    }

                                }

                                if(!empty($id_groupe)){
                                    $query->where('id_groupe', $id_groupe);
                                }





                            })
                            ->orderBy('date_dde','desc')
                            ->groupBy('id_groupe','id_tiers','nom_tiers','id_dossier','date_dde','animatrice')
                            ->paginate($per_page);
    }

    public function paginateNotOctroye($per_page=10)
    {
        return DemandePretGroupe::join('cd_demandes','cf_demande_pret_groupes.ref_dde','cd_demandes.id_demande')
                            ->join('cf_groupe_solidarites', 'cf_demande_pret_groupes.groupe_id','=','cf_groupe_solidarites.id_groupe')
                            ->join('cf_dossiers', 'cf_demande_pret_groupes.dossier_id','=','cf_dossiers.id_dossier')
                            ->join('tiers','cf_groupe_solidarites.id_groupe','=','tiers.id_tiers')
                            ->select('id_groupe','id_tiers','nom_tiers','dossier_id','date_dde','animatrice',
                                    DB::raw("SUM(mtt_capital) AS mtt_capital, SUM(mtt_recommande) AS mtt_recommande"))
                            ->where('decision','=','A')
                            ->where('statut_octroi','=','0')
                            ->orderBy('date_dde','desc')
                            ->groupBy('id_groupe','id_tiers','nom_tiers','dossier_id','date_dde','animatrice')
                            ->paginate($per_page);
    }

    public function getNotOctroye()
    {
        return DemandePretGroupe::join('cd_demandes','cf_demande_pret_groupes.ref_dde','cd_demandes.id_demande')
                            ->join('cf_groupe_solidarites', 'cf_demande_pret_groupes.groupe_id','=','cf_groupe_solidarites.id_groupe')
                            ->join('cf_dossiers', 'cf_demande_pret_groupes.dossier_id','=','cf_dossiers.id_dossier')
                            ->join('tiers','cf_groupe_solidarites.id_groupe','=','tiers.id_tiers')
                            ->select('id_groupe','id_tiers','nom_tiers','dossier_id','date_dde','animatrice',
                                    DB::raw("SUM(mtt_capital) AS mtt_capital, SUM(mtt_recommande) AS mtt_recommande"))
                            ->where('decision','=','A')
                            ->where('statut_octroi','=','0')
                            ->orderBy('date_dde','desc')
                            ->groupBy('id_groupe','id_tiers','nom_tiers','dossier_id','date_dde','animatrice')
                            ->get();
    }

    public function sumDemandeByCycle($id_dossier)
    {

        return DemandePretGroupe::join('cd_demandes','cf_demande_pret_groupes.ref_dde','cd_demandes.id_demande')
                        ->join('cf_dossiers','cf_demande_pret_groupes.dossier_id','=','cf_dossiers.id_dossier')
                        ->where('id_dossier', $id_dossier)
                        ->select('id_dossier', 'cf_dossiers.groupe_id', 'debut_cycle', 'fin_cycle', 'mode_reunion', 'statut',
                            DB::raw("SUM(mtt_capital) AS mtt_capital, SUM(mtt_recommande) AS mtt_recommande, count(*) nb_demande"))
                        ->groupBy('id_dossier', 'cf_dossiers.groupe_id', 'debut_cycle', 'fin_cycle', 'mode_reunion', 'statut')
                        ->first();

    }

}
