<?php
namespace App\Repositories;

use App\Models\Association\Group;
use Illuminate\Support\Facades\Auth;

class GroupeSolideRepository
{

    public function find($id_groupe)
    {

        return Group::join('tiers', 'cf_groupe_solidarites.id_groupe','=','tiers.id_tiers')
                        ->orWhere('id_groupe', $id_groupe)
                        ->orWhere('num_caisse', $id_groupe)
                        ->first();

    }

    public function paginate($per_page = 10)
    {

        return Group::join('tiers', 'cf_groupe_solidarites.id_groupe','=','tiers.id_tiers')
                        ->where(function($query){

                            $search = session()->has('groupement') ? json_decode(session('groupement')) : (object)[];


                            $id_groupe = !empty($search->groupeId) ? $search->groupeId : null;

                            $folio = !empty($search->folio) ? $search->folio : null;

                            if(!empty($caisse)){
                                $query->where('caisse_id', '=', $caisse);
                            }

                            if(!empty($id_groupe)){
                                $query->orWhere('id_groupe', '=', $id_groupe);
                                $query->orWhere('num_caisse', '=', $id_groupe);
                            }

                            $animatrices = session()->has('animatrices') ? session('animatrices') : [];



                            if(count($animatrices)>0){

                                $query->whereIn('animatrice_id', $animatrices);

                                $employe = !empty($search->employe) ? $search->employe : null;

                                if(!empty($employe)){
                                    $query->where('animatrice_id', '=', $employe);
                                }

                            }else{
                                $employe = (\Auth::user())->employe;
                                $query->where('animatrice_id', '=', $employe->id_employe);
                            }



                        })
                        ->where(function($query){

                            $search = session()->has('groupement') ? json_decode(session('groupement')) : (object)[];

                            $nom_cin = !empty($search->nom_cin) ? $search->nom_cin : "";

                            if(!empty($nom_cin)){

                                $query->orWhere('nom_tiers','like', '%'.$nom_cin.'%');
                                $query->orWhere('prenom_tiers','like', '%'.$nom_cin.'%');
                                $query->orWhere('cin','=', $nom_cin);
                                $query->orWhere('num_caisse','=', $nom_cin);


                            }
                        })
                        ->paginate($per_page);
    }

    public function get()
    {

        $user = \Auth::user();

        return Group::join('tiers', 'cf_groupe_solidarites.id_groupe','=','tiers.id_tiers')
                        ->where('agent_id', $user->name)
                        ->where(function($query){

                            $search = session()->has('groupement') ? json_decode(session('groupement')) : (object)[];


                            $id_groupe = !empty($search->groupeId) ? $search->groupeId : null;

                            if(!empty($id_groupe)){
                                $query->orWhere('id_groupe', '=', $id_groupe);
                                $query->orWhere('num_caisse', '=', $id_groupe);
                            }

                        })
                        ->orderBy('nom_tiers','asc')
                        ->get();
    }

    public function caisseByAnimatrice($animatrice_id)
    {

        return Group::join('tiers', 'cf_groupe_solidarites.id_groupe','=','tiers.id_tiers')
                        ->join('caisses', 'tiers.caisse_id','caisses.id_caisse')
                        ->where('animatrice_id', $animatrice_id)
                        ->select('caisses.id_caisse','caisses.agence_id')
                        ->groupBy('caisses.id_caisse','caisses.agence_id')
                        ->get();
    }

}
