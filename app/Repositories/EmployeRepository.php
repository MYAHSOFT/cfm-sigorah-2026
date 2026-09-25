<?php
namespace App\Repositories;

use App\Models\Employe;

class EmployeRepository
{

    public function paginate($per_page)
    {

        return Employe::where(function($query){

                    $search = session()->has('params.employe') ? json_decode(session('params.employe')) : (object)[];

                    $agence = !empty($search->agence) ? $search->agence : null;

                    if(!empty($agence)){
                        $query->where('agence_id', $agence);
                    }

                })
                ->where(function($query){

                    $search = session()->has('params.employe') ? json_decode(session('params.employe')) : (object)[];

                    $name = !empty($search->name) ? $search->name : null;

                    if(!empty($name)){

                        $query->orWhere('id_employe', '=', $name);
                        $query->orWhere('nom_employe', 'like', "%$name%");
                        $query->orWhere('prenom_employe', 'like', "%$name%");
                    }

                })
                ->orderBy('agence_id','asc')
                ->orderBy('id_employe','asc')
                ->paginate($per_page);

    }

    public function getByFonction($id_fonction)
    {

        return Employe::where('fonction_id', $id_fonction)
                ->where(function($query){

                    $search = session()->has('cf_employe') ? json_decode(session('cf_employe')) : (object)[];

                    $name = !empty($search->name) ? $search->name : null;

                    if(!empty($name)){

                        $query->orWhere('id_employe', '=', $name);
                        $query->orWhere('nom_employe', 'like', "%$name%");
                        $query->orWhere('prenom_employe', 'like', "%$name%");
                    }

                })
                ->get();

    }

    public function paginateByFonction($id_fonction, $per_page=10)
    {

        return Employe::where('fonction_id', $id_fonction)
                ->where(function($query){

                    $search = session()->has('cf_employe') ? json_decode(session('cf_employe')) : (object)[];

                    $name = !empty($search->name) ? $search->name : null;

                    if(!empty($name)){

                        $query->orWhere('id_employe', '=', $name);
                        $query->orWhere('nom_employe', 'like', "%$name%");
                        $query->orWhere('prenom_employe', 'like', "%$name%");
                    }

                })
                ->paginate($per_page);

    }

    public function employeByResponsable($id_employe, $per_page=10)
    {

        return Employe::join('employe_collaborateurs','employes.id_employe','=','employe_collaborateurs.id_collaborateur')
                ->where('id_responsable', $id_employe)
                ->where(function($query){

                    $search = session()->has('cf_employe') ? json_decode(session('cf_employe')) : (object)[];

                    $name = !empty($search->name) ? $search->name : null;

                    if(!empty($name)){

                        $query->orWhere('id_employe', '=', $name);
                        $query->orWhere('nom_employe', 'like', "%$name%");
                        $query->orWhere('prenom_employe', 'like', "%$name%");
                    }

                })
                ->paginate($per_page);

    }

    public function getByAgence($id_agence)
    {

    }

    public function comboEmploye($id_fonction, $responsable = null)
    {

        $where = [['fonction_id', $id_fonction]];

        if(!empty($responsable)){
            $where = [
                ['fonction_id', $id_fonction],
                ['responsable', $responsable]
            ];
        };

        return Employe::where('fonction_id', $id_fonction)
                ->where($where)
                ->get();

    }

    public function comboByParams($params)
    {

        return Employe::leftJoin('employe_collaborateurs','employes.id_employe','=','employe_collaborateurs.id_collaborateur')
                ->where($params)
                ->get();

    }
}
