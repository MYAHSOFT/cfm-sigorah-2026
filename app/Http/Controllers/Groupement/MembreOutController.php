<?php

namespace App\Http\Controllers\Groupement;

use App\Models\Compte;
use Illuminate\Http\Request;
use App\Models\CFFonctionGroupe;
use App\Http\Controllers\Controller;
use App\Models\GroupeSolide;
use App\Models\GroupeSolideMembre;
use App\Repositories\GroupeSolideRepository;
use App\Repositories\GroupeSolideMembreRepository;

class MembreOutController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $groupe = null;
        $tiers = [];
        $president = null;

        $groupe = GroupeSolide::where('id_groupe', session('id_groupe'))->first();

        $tiers = GroupeSolideMembre::join('tiers', 'cf_membres.tiers_id', 'tiers.id_tiers')
                            ->where('groupe_id', session('id_groupe'))
                            ->where('status', 'D')
                            ->orderBy('nom_tiers','asc')
                            ->get();


        return view('groupement.membres.out.index',[

            'groupe' =>$groupe,
            'tiers' =>$tiers,
            'president'    =>$president,

        ]);

    }

    public function search(Request $request)
    {
        session()->put('membre-out', json_encode($request->all()));

        return redirect()->route('gp.membre.out.create', ['groupe'=>$request->groupeId]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        $groupe = null;
        $tiers = [];
        $president = null;

        $groupe = GroupeSolide::where('id_groupe', session('id_groupe'))->first();

        $tiers = GroupeSolideMembre::join('tiers', 'cf_membres.tiers_id', 'tiers.id_tiers')
                            ->where('groupe_id', session('id_groupe'))
                            ->where('status', 'A')
                            ->orderBy('nom_tiers','asc')
                            ->get();


        return view('groupement.membres.out.create',[

            'groupe' =>$groupe,
            'tiers' =>$tiers,
            'president'    =>$president,

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

        if(empty($request->bloquer)){
            return back();
        }

        session()->put('membre_bloque', $request->bloquer);

        return redirect()->route('gp.mbre.out.cart', $request->groupeId);
    }

    public function cart()
    {

        $groupe = null;
        $tiers = [];
        $president = null;

        $membre_bloque = session()->has('membre_bloque') ? session('membre_bloque') : [];

        if(count($membre_bloque) == 0){
            return redirect()->route('gp.mbre.out.create');
        }

        $groupe = GroupeSolide::where('id_groupe', session('id_groupe'))->first();

        $tiers = GroupeSolideMembre::join('tiers', 'cf_membres.tiers_id', 'tiers.id_tiers')
                            ->whereIn('id_tiers', $membre_bloque)
                            ->orderBy('nom_tiers','asc')
                            ->get();


        return view('groupement.membres.out.cart',[

            'groupe' =>$groupe,
            'tiers' =>$tiers,
            'president'    =>$president,

        ]);

    }

    public function lockStore()
    {

        $membre_bloque = session()->has('membre_bloque') ? session('membre_bloque') : [];

        GroupeSolideMembre::where('groupe_id', session('id_groupe'))
                    ->whereIn('tiers_id', $membre_bloque)
                    ->update([
                        'status'=>'D',
                        'fonction_id'=>'MBR',
                    ]);

        session()->flash('success',"Les membres suivant ont été bloqués avec succès");

        return redirect()->route('gp.mbre.out.index');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy()
    {
        session()->forget('membre_bolque');

        return redirect()->route('gp.mbre.out.create');

    }
}
