<?php

namespace App\Http\Controllers\Groupement;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\GroupeSolideRepository;
use App\Repositories\GroupeSolideMembreRepository;

class SearchGroupementController extends Controller
{

    public function search(
        GroupeSolideRepository $_groupe,
        GroupeSolideMembreRepository $_membre_groupe,
        Request $request
    )
    {

        $groupe = (object)[];


        $agent = session('agent');

        $president = null;

        if($request->isMethod('post')){

            $groupe = $_groupe->find($request->search,$agent);

            if(!empty($groupe->id_groupe)){
                $president = $_membre_groupe->getMembreBureauByFonction($groupe->id_groupe, 'PRD');
            }

        }

        return view('groupement.gp_solidarites.search',[
            'title' =>"Recherche de groupement",
            'request'=>$request,
            'url'   =>route('gp.search'),
            'tiers' =>$groupe,
            'president' =>$president,
        ]);

    }


}
