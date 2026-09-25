<?php

namespace App\Http\Controllers\Groupement;

use App\Http\Controllers\Controller;
use App\Models\Association\MemberApplication;
use Illuminate\Http\Request;
use App\Repositories\DemandePretTmpGroupeRepository;
use App\Models\Customer\Customer;
use App\Models\Association\Cycle;
use App\Models\Association\Group;
use DB;

class DemandeTmpController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $employe = \Auth::user()->employe;

        $demandes = MemberApplication::join('cf_dossiers','cf_demandes.dossier_id','cf_dossiers.id_dossier')
                        ->join('cf_groupe_solidarites','cf_dossiers.groupe_id','cf_groupe_solidarites.id_groupe')
                        ->join('tiers', 'cf_groupe_solidarites.id_groupe','tiers.id_tiers')
                        ->where('agent_id', $employe->id_employe)   
                        ->select('nom_tiers','cf_dossiers.groupe_id','num_caisse','id_dossier','debut_cycle','statut', DB::raw("SUM(mtt_capital) mtt_capital"))
                        ->groupBy('nom_tiers','cf_dossiers.groupe_id','num_caisse','id_dossier','debut_cycle','statut')
                        ->get();

        $nb_demande = 0;

        $sum_demande = 0;

        foreach ($demandes as $demande) {

            $nb_demande++;

            $sum_demande += $demande->mtt_capital;

        }

        return view('groupement.demandes.tmp.index', [
            'demandes' =>$demandes,
            'sum_demande' =>$sum_demande,
            'nb_demande' =>$nb_demande,
        ]);
    }

    public function membre($id_dossier)
    {

        $dossier = Cycle::where('id_dossier', $id_dossier)->first();

        if(empty($dossier->id_dossier)){
            return back();
        }

        $demandes = MemberApplication::where('dossier_id', $id_dossier)->get();

        $membres = [];
        $cart = [];

        foreach ($demandes as $demande) {
            
            $membres[] = $demande->tiers_id;
        }

        $tiers = Customer::join('cf_membres','tiers.id_tiers','cf_membres.tiers_id')
                    ->leftJoin('cf_demandes','tiers.id_tiers','cf_demandes.tiers_id')
                    ->where('cf_membres.groupe_id', $dossier->groupe_id)
                    ->whereNotIn('id_tiers', $membres)
                    ->select('tiers.*')
                    ->get();

        return view('groupement.demandes.membre', [
            'tiers' =>$tiers,
            'cart'  =>$cart,
            'id_dossier'  =>$id_dossier,
        ]);

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($id_tiers, $id_dossier)
    {

        $membre = Customer::join('cf_membres','tiers.id_tiers','=','cf_membres.tiers_id')
                        ->where('id_tiers', $id_tiers)
                        ->first();

        if(empty($membre->id_tiers)){
            return back();
        }

        $dossier = Cycle::where('id_dossier', $id_dossier)->first();

        $groupe = Group::join('tiers', 'cf_groupe_solidarites.id_groupe', 'tiers.id_tiers')
                        ->where('id_groupe', session('id_groupe'))
                        ->first();

        return view('groupement.demandes.tmp.create',[
            'groupe'    =>$groupe,
            'membre'    =>$membre,
            'id_dossier'    =>$id_dossier,
            'url'   =>route('gp.demande.tmp.store'),
        ]);

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $request->validate([
            'montant'   =>'required|numeric|gt:0',
            'objet'   =>'required',
        ], [
            'montant.required'   =>"Le montant est obligatoire",
            'montant.numeric'   =>"Le montant n'est valide",
            'objet.required'    =>"L'objet de prêt est obligatoire"
        ]);

        //`dossier_id`, `tiers_id`, `groupe_id`, `mtt_capital`, `objet_pret`
        $data = [
            'dossier_id'    =>$request->dossierId,
            'tiers_id'    =>$request->folio,
            'groupe_id'    =>$request->groupeId,
            'mtt_capital'    =>$request->montant,
            'objet_pret'    =>$request->objet,
        ];

        MemberApplication::create($data);

        return redirect()->route('gp.demande.tmp.show', $request->dossierId);

    }

    /**
     * Display the specified resource.
     */
    public function show(
        DemandePretTmpGroupeRepository $_demande,
        $id_dossier)
    {

        $dossier = Cycle::where('id_dossier', $id_dossier)->first();

        if(empty($dossier->id_dossier)){
            return abort(404);
        }

        $groupe = MemberApplication::join('cf_dossiers', 'cf_demandes.dossier_id', 'cf_dossiers.id_dossier')
                        ->join('cf_groupe_solidarites','cf_dossiers.groupe_id','cf_groupe_solidarites.id_groupe')
                        ->where('id_dossier', session('id_groupe'))
                        ->first();

        $demandes = $_demande->get($id_dossier);

        $nb_demande = $_demande->count($id_dossier);

        $sum_demande = $_demande->sum($id_dossier);

        $nb_operation = 0;

        return view('groupement.demandes.tmp.show', [
            'groupe' =>$groupe,
            'dossier' =>$dossier,
            'demandes'   =>$demandes,
            'nb_demande'   =>$nb_demande,
            'sum_demande'   =>$sum_demande,
            'nb_operation'   =>$nb_operation,
        ]);

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id_demande)
    {

        $id_demande = \App\Lib\CryptId::decrypt($id_demande);

        $demande = MemberApplication::where('id_demande', $id_demande)->first();

        if(empty($demande->id_demande)){
            return back();
        }

        $membre = Customer::join('cf_membres','tiers.id_tiers','=','cf_membres.tiers_id')
                        ->where('id_tiers', $demande->tiers_id)
                        ->first();

        if(empty($membre->id_tiers)){
            return abort(404);
        }

        $groupe = Group::join('tiers', 'cf_groupe_solidarites.id_groupe', 'tiers.id_tiers')
                        ->where('id_groupe', session('id_groupe'))
                        ->first();

        return view('groupement.demandes.tmp.create',[
            'groupe'    =>$groupe,
            'membre'    =>$membre,
            'demande'    =>$demande,
            'id_dossier'    =>$demande->dossier_id,
            'url'   =>route('gp.demande.tmp.update', \App\Lib\CryptId::crypt($id_demande)),
        ]);

    }

    public function update(Request $request, $id_demande)
    {

        $id_demande = \App\Lib\CryptId::decrypt($id_demande);

        $demande = MemberApplication::where('id_demande', $id_demande)->first();

        $request->validate([
            'montant'   =>'required|numeric',
            'objet'   =>'required',
        ], [
            'montant.required'   =>"Le montant est obligatoire",
            'montant.numeric'   =>"Le montant n'est valide",
            'objet.required'    =>"L'objet de prêt est obligatoire"
        ]);

        $data = [
            'mtt_capital'   =>$request->montant,
            'objet_pret'    =>$request->objet,
        ];

        MemberApplication::where('id_demande', $id_demande)->update($data);

        return redirect()->route('gp.demande.tmp.show', $demande->dossier_id);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        
       DB::transaction(function() use($request) {

            MemberApplication::where('dossier_id', $request->dossierId)->delete();
        
            Cycle::where('id_dossier', $request->dossierId)->delete();

       });

       return redirect()->route('gp.demande.tmp.index');
       
    }

}
