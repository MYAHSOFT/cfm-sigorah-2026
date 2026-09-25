<?php

namespace App\Http\Controllers\Groupement;

use App\Http\Controllers\Controller;
use App\Models\CFMembre;
use App\Models\Tiers;
use Illuminate\Http\Request;

class MembreBureauController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $tiers = Tiers::join('cf_membres', 'tiers.id_tiers', 'cf_membres.tiers_id')
                        ->where('groupe_id', session('id_groupe'))
                        ->where('fonction_id', '<>', 'MBR')
                        ->get();

        $membres = [];

        foreach($tiers as $t){

            $membres[$t->fonction_id] = (object)[
                'name'   =>$t->nom_tiers.' '.$t->prenom_tiers,
                'photo' =>$t->photo,
            ];

        }

        $fonctions[0] = (object)[
            'code'  => 'PRD',
            'label' =>'Président(e)',
        ];
        $fonctions[1] = (object)[
            'code'  => 'SCE',
            'label' =>'Secrétaire(e)',
        ];
        $fonctions[2] = (object)[
            'code'  => 'TRE',
            'label' =>'Tresorier(ère)',
        ];
        $fonctions[3] = (object)[
            'code'  => 'COM',
            'label' =>'Commissaire au compte',
        ];
        $fonctions[4] = (object)[
            'code'  => 'SAG',
            'label' =>'Sage',
        ];

        return view('groupement.bureau.index', [
            'fonctions'   =>$fonctions,
            'membres'   =>$membres,
        ]);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($id_fonction)
    {

        $tiers = Tiers::join('cf_membres', 'tiers.id_tiers', 'cf_membres.tiers_id')
                        ->where('groupe_id', session('id_groupe'))
                        ->where('status', 'A')
                        ->get();

        return view('groupement.bureau.create', [
            'tiers' =>$tiers,
            'id_fonction'   =>$id_fonction,
        ]);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($id_tiers, $id_fonction)
    {

        CFMembre::where('groupe_id', session('id_groupe'))
                ->where('fonction_id', $id_fonction)
                ->update([
                    'fonction_id'   =>'MBR'
                ]);

        CFMembre::where('tiers_id', $id_tiers)
                ->where('groupe_id', session('id_groupe'))
                ->update([
                    'fonction_id'   =>$id_fonction
                ]);


        return redirect()->route('gp.bureau.index');

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
