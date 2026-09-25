<?php

namespace App\Http\Controllers\Groupement;

use App\Models\DemandePret;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Repositories\DemandePretGroupeRepository;

class PanierPretCartController extends Controller
{



    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($id_tiers)
    {

        $demande = DemandePret::join('tiers', 'cd_demandes.tiers_id', 'tiers.id_tiers')
                            ->join('cf_demande_pret_groupes','cd_demandes.id_demande','cf_demande_pret_groupes.ref_dde')
                            ->where('id_tiers', $id_tiers)
                            ->first();

        if(empty($demande->id_tiers)){
            return abort(404);
        }


        return view('groupement.octroi.cart',[
            'demande'    =>$demande,
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

        $demande = DemandePret::where('cd_demandes.id_demande',$request->refDde)
                            ->first();

        $mtt_recommande = (double)$demande->mtt_recommande;

        $validator = Validator::make($request->all(), [
            'montant'   =>"required|numeric|lte:$mtt_recommande"
        ], [
            'montant.required'=>"Montant non valide",
            'montant.numeric'=>"Montant non valide",
            'montant.lte'=>"Le montant ne doit pas dépasser ".number_format($mtt_recommande,2,',',' '),
        ]);

        if($validator->fails()){
            return back()->withErrors($validator)->withInput();
        }

        $data = session()->get('cf_cart_octroi');

        $data[$request->refDde] = (double)$request->montant;

        session()->put('cf_cart_octroi', $data);

        return redirect()->route('gp.octroi.show',$request->dossierId);

    }

    public function storeAll(
        DemandePretGroupeRepository $_demande,
        $id_dossier)
    {

        $demandes = $_demande->getGroupesByDossier($id_dossier);

        $data = [];

        foreach ($demandes as $demande) {

            $data[$demande->ref_dde] = $demande->mtt_recommande;

        }

        session()->put('cf_cart_octroi', $data);

        session()->flash("success", "Une demande a bien été ajouter dans la liste pour octroi.");

        return redirect()->route('gp.octroi.show',$id_dossier);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

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

    public function forget()
    {

        session()->forget('cf_cart_octroi');

        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($ref_dde)
    {

        $carts = session('cf_cart_octroi');

        if(key_exists($ref_dde, $carts)){
            unset($carts[$ref_dde]);

            session()->put('cf_cart_octroi', $carts);
        }

        return back();
    }
}
