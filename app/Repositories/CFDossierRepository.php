<?php
namespace App\Repositories;

use App\Models\CFDossier;

class CFDossierRepository
{

    public function find($id_dossier)
    {

        return CFDossier::join('cf_groupe_solidarites','cf_dossiers.groupe_id','cf_groupe_solidarites.id_groupe')
                ->join('tiers','cf_groupe_solidarites.id_groupe','tiers.id_tiers')
                ->where('id_dossier', $id_dossier)
                ->first();

    }

    public function lastDocOpen($id_groupe)
    {

        $cycle = null;

        $lastCycle = CFDossier::where('statut', '=', 'O')
                        ->where('groupe_id', $id_groupe)
                        ->max('debut_cycle');

        if(!empty($lastCycle)){
            $cycle = CFDossier::join('cf_groupe_solidarites','cf_dossiers.groupe_id','cf_groupe_solidarites.id_groupe')
                        ->join('tiers','cf_groupe_solidarites.id_groupe','tiers.id_tiers')
                        ->where('id_groupe', $id_groupe)
                        ->where('debut_cycle', $lastCycle)
                        ->orderBy('debut_cycle', 'desc')
                        ->first();
        }

        return $cycle;

    }

    public function paginate($per_page = 10)
    {

        return CFDossier::join('cf_groupe_solidarites','cf_dossiers.groupe_id','cf_groupe_solidarites.id_groupe')
                ->join('tiers','cf_groupe_solidarites.id_groupe','tiers.id_tiers')
                ->where(function($query){

                    $search = session()->has('cf_dossier') ? json_decode(session('cf_dossier')) : (object)[];

                    $id_groupe = !empty($search->codeGroupe) ? $search->codeGroupe : null;

                    if(!empty($id_groupe)){
                        $query->where('id_groupe', $id_groupe);
                    }

                    $employes = ['ANNITH0413'];

                    $query->whereIn('animatrice_id', $employes);

                })
                ->where('statut','<>','A')
                ->orderBy('debut_cycle', 'DESC')
                ->paginate($per_page);

    }

    public function getByGroupe($id_groupe)
    {

        return CFDossier::join('cf_groupe_solidarites','cf_dossiers.groupe_id','cf_groupe_solidarites.id_groupe')
                ->join('tiers','cf_groupe_solidarites.id_groupe','tiers.id_tiers')
                ->where('id_groupe', $id_groupe)
                ->where('statut','<>','A')
                ->get();

    }

}
