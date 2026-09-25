<?php

namespace App\Http\Controllers\Groupement;

use App\Models\Compte;
use Illuminate\Http\Request;
use App\Models\CFFonctionGroupe;
use App\Http\Controllers\Controller;
use App\Models\ProfilGroupement;
use App\Repositories\GroupeSolideRepository;
use App\Repositories\GroupeSolideMembreRepository;

class GroupementDeclasserController extends Controller
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

        return redirect()->route('gp.declasser.create', ['groupe'=>$request->codeGroupe]);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(
        Request $request,
        GroupeSolideMembreRepository $_membre_groupe,
        GroupeSolideRepository $_groupe,
        $id_groupe = null)
    {

        $groupe_id = $request->groupe;

        $groupe = $_groupe->find($request->groupe);

        // if(empty($groupe->id_groupe)){
        //     session()->flash("warning", "Le groupe sélectionné n'existe pas dans la base de données.");
        // }

        $per_page = 10;

        $tiers = $_membre_groupe->membreGroupeByStatus($groupe_id,'A', $per_page);

        $president = $_membre_groupe->getMembreBureauByFonction($groupe_id, 'PRD');

        $bureau = $_membre_groupe->getBureau($groupe_id);

        $compte = Compte::join('cf_groupe_solidarites','ep_comptes.tiers_id','=','cf_groupe_solidarites.id_groupe')
                        ->where('id_groupe', $id_groupe)
                        ->first();

        return view('groupement.gp_solidarites.out.create',[

            'groupe' =>$groupe,
            'tiers' =>$tiers,
            'compte' =>$compte,
            'bureau' =>$bureau,
            'president' =>$president,
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

        //`id_profil`, `groupe_id`, `motif`, `animatrice_id`

        $employe = (\Auth::user())->employe;

        $profil = [
            'groupe_id' =>$request->groupeId,
            'profil'    =>$request->profil,
            'motif' =>$request->motif,
            'animatrice_id' =>$employe->id_employe,
        ];

        ProfilGroupement::create($profil);

        session()->flash("success", "Le membre a été déclasser avec succès.");

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
