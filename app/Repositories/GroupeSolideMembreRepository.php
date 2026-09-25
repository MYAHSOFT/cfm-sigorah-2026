<?php
namespace App\Repositories;

use App\Models\Association\Member;

class GroupeSolideMembreRepository
{

    public function paginate($id_groupe, $per_page)
    {

        return Member::join('cf_groupe_solidarites','cf_membres.groupe_id','=','cf_groupe_solidarites.id_groupe')
                    ->join('tiers', 'cf_membres.tiers_id','=','tiers.id_tiers')
                    ->join('cf_fonction_membre_groupement', 'cf_membres.fonction_id','=','cf_fonction_membre_groupement.id_fonction')
                    ->where('id_groupe', $id_groupe)
                    ->orderBy('rang','asc')
                    ->orderBy('id_tiers','asc')
                    ->paginate($per_page);

    }

    public function getMembre($id_groupe)
    {

        return Member::join('cf_groupe_solidarites','cf_membres.groupe_id','=','cf_groupe_solidarites.id_groupe')
                    ->join('tiers', 'cf_membres.tiers_id','=','tiers.id_tiers')
                    ->where('id_groupe', $id_groupe)
                    ->get();

    }

    public function getMembreNotInList($id_groupe)
    {

        return Member::join('cf_groupe_solidarites','cf_membres.groupe_id','=','cf_groupe_solidarites.id_groupe')
                    ->join('tiers', 'cf_membres.tiers_id','=','tiers.id_tiers')
                    ->where('id_groupe', $id_groupe)
                    ->where(function($query){

                        $data = session()->has('cf_create_dde') ? session('cf_create_dde') : [];

                        $list = [];

                        foreach ($data as $key => $value) {

                            $list[] = $key;
                        }

                        if(count($data)>0){
                            $query->whereNotIn('id_tiers', $list);
                        }

                    })
                    ->get();

    }

    public function getMembreBureauByFonction($id_groupe, $id_fonction)
    {

        return Member::join('cf_groupe_solidarites','cf_membres.groupe_id','=','cf_groupe_solidarites.id_groupe')
                    ->join('tiers', 'cf_membres.tiers_id','=','tiers.id_tiers')
                    ->where('id_groupe', $id_groupe)
                    ->where('fonction_id', $id_fonction)
                    ->first();

    }

    public function getBureau($id_groupe)
    {

        return Member::join('cf_groupe_solidarites','cf_membres.groupe_id','=','cf_groupe_solidarites.id_groupe')
                    ->join('tiers', 'cf_membres.tiers_id','=','tiers.id_tiers')
                    ->where('id_groupe', $id_groupe)
                    ->where('fonction_id','<>', 'MBR')
                    ->get();

    }

    public function membreGroupeByStatus($id_groupe,$status, $per_page)
    {

        return Member::join('cf_groupe_solidarites','cf_membres.groupe_id','=','cf_groupe_solidarites.id_groupe')
                    ->join('tiers', 'cf_membres.tiers_id','=','tiers.id_tiers')
                    ->join('cf_fonction_membre_groupement', 'cf_membres.fonction_id','=','cf_fonction_membre_groupement.id_fonction')
                    ->where('id_groupe', $id_groupe)
                    ->where('status', $status)
                    ->orderBy('rang','asc')
                    ->orderBy('id_tiers','asc')
                    ->paginate($per_page);

    }

    public function membreByStatus($status, $per_page)
    {

        return Member::join('cf_groupe_solidarites','cf_membres.groupe_id','=','cf_groupe_solidarites.id_groupe')
                    ->join('tiers', 'cf_membres.tiers_id','=','tiers.id_tiers')
                    ->join('cf_fonction_membre_groupement', 'cf_membres.fonction_id','=','cf_fonction_membre_groupement.id_fonction')
                    ->where('status', $status)
                    ->orderBy('rang','asc')
                    ->orderBy('id_tiers','asc')
                    ->paginate($per_page);

    }
}
