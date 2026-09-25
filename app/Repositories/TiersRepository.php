<?php

namespace App\Repositories;

use App\Models\Tiers;

class TiersRepository
{

    public function find($id_tiers)
    {
        return Tiers::where('id_tiers', $id_tiers)
                ->join('caisses','tiers.caisse_id','=','caisses.id_caisse')
                ->first();
    }

    public function byIdentifiant($id_tiers, $agence = null)
    {

        $tiers = null;

        if($agence){

            $tiers = Tiers::where('id_tiers','=', $id_tiers)
                        ->where('caisse_id','like', $agence.'%')
                        ->first();

        }else{

            $tiers = Tiers::where('id_tiers','=', $id_tiers)
                        ->first();
        }

        return $tiers;

    }

    public function paginate($per_page)
    {

        return Tiers::where(function($query){

           $search = session()->has('tiers') ? json_decode(session('tiers')) : (object)[];

           $caisse = !empty($search->caisse) ? $search->caisse :  session('agence');

           $folio = !empty($search->folio) ? $search->folio : "";

           $query->where('caisse_id','like', $caisse.'%');

           if(!empty($folio)){
               $query->where('id_tiers', $folio);
           }

        })
        ->where(function($query){

            $search = session()->has('tiers') ? json_decode(session('tiers')) : (object)[];

            $nom_cin = !empty($search->nom_cin) ? $search->nom_cin : "";

            if(!empty($nom_cin)){

                $query->orWhere('nom_tiers','like', '%'.$nom_cin.'%');
                $query->orWhere('prenom_tiers','like', '%'.$nom_cin.'%');
                $query->orWhere('cin','=', $nom_cin);


            }
        })
        ->paginate($per_page);
    }

    public function get($status = null)
    {

        $status = empty($status) ? ['A','D'] : [$status];

        return Tiers::join('cf_membres','tiers.id_tiers','=','cf_membres.tiers_id')
                    ->where('groupe_id', session('id_groupe'))
                    ->whereIn('status', $status)
                    ->where(function($query){

                        $search = session()->has('tiers') ? json_decode(session('tiers')) : (object)[];

                        $name = !empty($search->name) ? $search->name : null;

                        if(!empty($name)){

                            $query->orWhere('nom_tiers','like',"%$name%");
                            $query->orWhere('prenom_tiers','like',"%$name%");

                        }

                    })
                    ->orderBy('nom_tiers','asc')
                    ->get();
    }

    protected function tiersWhere($query)
    {

        $search = session()->has('tiers') ? json_decode(session('tiers')) : (object)[];

        $name = !empty($search->name) ? $search->name : null;

        $query->where('groupe_id', session('id_groupe'));

        if(!empty($name)){

            $query->orWhere('nom_tiers','like',"%$name%");
            $query->orWhere('prenom_tiers','like',"%$name%");

        }

    }
}
