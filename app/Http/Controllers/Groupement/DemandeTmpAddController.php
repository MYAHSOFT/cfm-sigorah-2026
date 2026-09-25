<?php

namespace App\Http\Controllers\Groupement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DemandeTmpAddController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        
        
        $carts = session()->has('cf_create_dde') ? session('cf_create_dde') : [];

        $cart = [];

        $sum_demande = 0;

        foreach ($carts as $value) {

            $value = (object)$value;

            if(!empty($value->folio)){

                $cart[] = $value->folio;

                $sum_demande  += (double) $value->montant;

            }

        }

        $tiers = Tiers::join('cf_membres','tiers.id_tiers','=','cf_membres.tiers_id')
                        ->where('groupe_id',session('id_groupe'))
                        ->where('status', 'A')
                        ->whereNotIn('id_tiers', $cart)
                        ->where(function($query){

                            $name = !empty($this->request->name) ? $this->request->name : null;

                            if(!empty($name)){
                                $query->orWhere('nom_tiers','like', "%$name%");
                                $query->orWhere('prenom_tiers','like', "%$name%");
                            }
                        })
                        ->get();

        return view('groupement.demandes.membre', [
            'tiers' =>$tiers,
            'cart'  =>$cart,
        ]);
        
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
