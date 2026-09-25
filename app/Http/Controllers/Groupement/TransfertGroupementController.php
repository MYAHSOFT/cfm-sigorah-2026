<?php

namespace App\Http\Controllers\Groupement;

use App\Http\Controllers\Controller;
use App\Models\Bank\Employe;
use App\Models\Association\Group;
use App\Repositories\GroupeSolideMembreRepository;
use App\Repositories\GroupeSolideRepository;
use Illuminate\Http\Request;
use DB;

class TransfertGroupementController extends Controller
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


    public function search(Request $request)
    {

        // session()->put('transfert_groupe', json_encode($request->all()));

        return redirect()->route('gp.transfert.groupe.create', ['groupe'=>$request->codeGroupe]);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request,
    GroupeSolideRepository $_groupe,
    GroupeSolideMembreRepository $_membre_groupe)
    {

        $groupe = null;
        $animatrices = [];
        $president = null;
        $animatrice_current = null;

        $groupe = $_groupe->find($request->groupe);

        $per_page = 10;

        $nb_groupes = [];


        $user =(\Auth::user());

        if(!empty($groupe->id_groupe)){

            $animatrice_current = $groupe->employe;

            $animatrices = Employe::join('employe_responsables','employes.id_employe','employe_responsables.id_employe')
                                ->where('id_responsable', $user->name)
                                ->paginate($per_page);

            $animatrices->appends(['groupe'=>$groupe->id_groupe]);

            $president = $_membre_groupe->getMembreBureauByFonction($groupe->id_groupe, 'PRD');

            $count_groupes = Group::join('employe_responsables','cf_groupe_solidarites.animatrice_id','employe_responsables.id_employe')
                                ->where('id_responsable', $user->name)
                                ->select('id_employe', DB::raw("COUNT(id_groupe) AS nb_groupe"))
                                ->groupBy('id_employe')
                                ->get();

            foreach ($count_groupes as $count) {
                $nb_groupes[$count->id_employe] = $count->nb_groupe;
            }

        }else{

            if($request->groupeId){
                session()->flash("warning","Aucun groupement ne correspond à ce code.");
            }

        }

        return view('groupement.transfert_groupes.create', [
            'groupe'    =>$groupe,
            'animatrices'   =>$animatrices,
            'animatrice_current'   =>$animatrice_current,
            'nb_groupes'   =>$nb_groupes,
            'president'    =>$president,
            'request'    =>$request,
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

        Group::where('id_groupe', $request->groupeId)
                ->update([
                    'animatrice_id' =>$request->employeId
                ]);

        session()->flash("success", "L'animatrice a été modifié avec succès.");
        return redirect()->route('gp.groupe.show', $request->groupeId);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
