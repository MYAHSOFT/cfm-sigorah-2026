<?php

namespace App\Http\Controllers\Groupement;

use DB;
use App\Models\Customer\Customer;
use App\Models\Association\Cycle;
use App\Models\Association\MeetingOperation;
use App\Models\Lending\ApplicationLoan;
use App\Models\Association\Group;
use Illuminate\Http\Request;
use App\Models\GPDemandePret;
use App\Models\Association\CycleCalendar;
use App\Models\Association\GroupLoanApplication;
use App\Models\Association\Member;
use App\Http\Controllers\Controller;
use App\Repositories\TiersRepository;
use App\Repositories\CFDossierRepository;
use Illuminate\Support\Facades\Validator;
use App\Repositories\DemandePretRepository;
use App\Repositories\GroupeSolideRepository;
use App\Repositories\CycleActiviteRepository;
use App\Repositories\DemandePretGroupeRepository;
use App\Repositories\GroupeSolideMembreRepository;

class DemandeCreditController extends Controller
{

    protected $cycle;

    protected $request;

    public function __construct(
        Request $request
    )
    {

        $this->request = $request;

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(
        DemandePretGroupeRepository $_demande,
        Request $request
    )
    {

        $employe = \Auth::user()->employe;

        $demandes = GroupLoanApplication::join('cf_dossiers','cf_demande_pret_groupes.dossier_id','cf_dossiers.id_dossier')
                        ->join('cd_demandes', 'cf_demande_pret_groupes.ref_dde','cd_demandes.id_demande')
                        ->join('cf_groupe_solidarites', 'cf_dossiers.groupe_id','cf_groupe_solidarites.id_groupe')
                        ->join('tiers', 'cf_groupe_solidarites.id_groupe','tiers.id_tiers')
                        ->whereIn('statut', ['O','P'])
                        ->where('animatrice', $employe->id_employe)
                        ->select('nom_tiers','id_groupe','num_caisse','id_dossier','debut_cycle','statut', DB::raw("SUM(mtt_capital) mtt_capital"))
                        ->groupBy('nom_tiers','id_groupe','num_caisse','id_dossier','debut_cycle','statut')
                        ->get();

        $nb_demande = 0;

        $sum_demande = 0;

        foreach ($demandes as $demande) {

            $nb_demande++;

            $sum_demande += $demande->mtt_capital;

        }

        return view('groupement.demandes.index', [
            'demandes' =>$demandes,
            'demandes' =>$demandes,
            'sum_demande' =>$sum_demande,
            'nb_demande' =>$nb_demande,
        ]);

    }

    public function search(Request $request)    {

        session()->put('demande',json_encode($request->all()));

        return redirect()->route('gp.dde.index');
    }

    public function new()
    {
        return redirect()->route('gp.search', ['objet'=>'credit']);
    }

    public function membre()
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

        $tiers = Customer::join('cf_membres','tiers.id_tiers','=','cf_membres.tiers_id')
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

    public function cart()
    {

        // session()->forget('cf_create_dde');

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

        if(count($cart)==0){
            return redirect()->route('gp.demande.membre');
            session()->forget('cf_create_dde');
        }

        $tiers = Customer::join('cf_membres','tiers.id_tiers','=','cf_membres.tiers_id')
                        ->where('groupe_id',session('id_groupe'))
                        ->whereIn('id_tiers', $cart)
                        ->get();

        $nb_tiers_rest = Customer::join('cf_membres','tiers.id_tiers','=','cf_membres.tiers_id')
                        ->where('groupe_id',session('id_groupe'))
                        ->where('status', 'A')
                        ->whereNotIn('id_tiers', $cart)
                        ->count();

        return view('groupement.demandes.cart', [
            'tiers'   =>$tiers,
            'carts'   =>$carts,
            'cart'  =>$cart,
            'sum_demande'  =>$sum_demande,
            'nb_tiers_rest'  =>$nb_tiers_rest,
        ]);


    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($id_tiers)
    {

        $membre = Customer::join('cf_membres','tiers.id_tiers','=','cf_membres.tiers_id')
                        ->where('id_tiers', $id_tiers)
                        ->first();

        if(empty($membre->id_tiers)){
            return abort(404);
        }

        $groupe = Group::join('tiers', 'cf_groupe_solidarites.id_groupe', 'tiers.id_tiers')
                        ->where('id_groupe', session('id_groupe'))
                        ->first();

        return view('groupement.demandes.create',[
            'groupe'    =>$groupe,
            'membre'    =>$membre,
            'url'   =>route('gp.demande.store'),
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

        $data = session()->get('cf_create_dde');

        $validator = Validator::make($request->all(), [
            'montant'   =>"required|numeric|gt:0",
            'objet'   =>"required",
        ], [
            'montant.required'=>"Montant est obligatoire",
            'montant.numeric'=>"Montant non valide",
            'montant.gt'=>"Le montant doit être suppérieur à Zéro",
            'objet.required'    =>"L'objet de prêt est obligatoire",
        ]);

        if($validator->fails()){
            return back()->withErrors($validator)->withInput();
        }



        $data[$request->folio] = $request->all();

        session()->put('cf_create_dde', $data);

        return redirect()->route('gp.demande.cart');

    }

    public function forget()
    {
        session()->forget('cf_create_dde');

        return redirect()->route('gp.demande.membre');

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(
        DemandePretGroupeRepository $_demande,
        $id_dossier)
    {

        $dossier = Cycle::where('id_dossier', $id_dossier)->first();

        if(empty($dossier->id_dossier)){
            return abort(404);
        }

        $groupe = Group::join('tiers', 'cf_groupe_solidarites.id_groupe', 'tiers.id_tiers')
                        ->where('id_groupe', session('id_groupe'))
                        ->first();

        $demandes = $_demande->get($id_dossier);

        $nb_demande = $_demande->count($id_dossier);

        $sum_demande = $_demande->sum($id_dossier);

        $nb_operation = MeetingOperation::where('dossier_id', $id_dossier)
                                    ->count();

        return view('groupement.demandes.show', [
            'groupe' =>$groupe,
            'dossier' =>$dossier,
            'demandes'   =>$demandes,
            'nb_demande'   =>$nb_demande,
            'sum_demande'   =>$sum_demande,
            'nb_operation'   =>$nb_operation,
        ]);

    }

    public function edit($id_demande)
    {

        $demande = ApplicationLoan::where('id_demande', $id_demande)->first();

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

        return view('groupement.demandes.create',[
            'groupe'    =>$groupe,
            'membre'    =>$membre,
            'demande'    =>$demande,
            'url'   =>route('gp.demande.update', $id_demande),
        ]);

    }

    public function update(Request $request, $id_demande)
    {

        $request->validate([
            'montant'   =>'required|numeric',
            'objet'   =>'required',
        ], [
            'montant.required'   =>"Le montant est obligatoire",
            'montant.numeric'   =>"Le montant n'est valide",
            'objet.required'    =>"L'objet de prêt est obligatoire"
        ]);

        $demande = [
            'mtt_capital'   =>$request->montant,
            'mtt_recommande'   =>$request->montant,
            'objet_pret'    =>$request->objet,
        ];

        $dossier = GroupLoanApplication::where('ref_dde', $id_demande)->first();

        ApplicationLoan::where('id_demande', $id_demande)->update($demande);

        return redirect()->route('gp.demande.show', $dossier->dossier_id);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id_tiers)
    {

        $demandes = session('cf_create_dde');

        if(key_exists($id_tiers, $demandes)){
            unset($demandes[$id_tiers]);

            session()->put('cf_create_dde', $demandes);
        }

        return back();

    }


}
