<?php

namespace App\Http\Controllers\Groupement;

use DB;
use App\Models\Saving\AccountSaving;
use App\Models\Accounting\JournalAccounting;
use App\Models\Association\Cycle;
use App\Models\Lending\ScheduleLoan;
use App\Models\Lending\TransactionLoan;
use App\Models\Lending\ContractLoan;
use App\Models\Association\GroupSchedule;
use App\Models\Association\Group;
use Illuminate\Http\Request;
use App\Models\Lending\ProductLoan;
use App\Models\Association\CycleCalendar;
use App\Models\Association\GroupLoanContract;
use App\Models\Association\GroupLoanApplication;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Repositories\CFDossierRepository;
use App\Repositories\DemandePretGroupeRepository;
use Illuminate\Support\Facades\Validator;

class OctroiPretController extends Controller
{

    protected $demande;

    protected $dossier;

    protected $request;

    public function __construct(
        DemandePretGroupeRepository $demande,
        CFDossierRepository $dossier,
        Request $request
    )
    {
        $this->demande = $demande;

        $this->dossier = $dossier;

        $this->request = $request;

    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(
        DemandePretGroupeRepository $_demande)
    {

        $search = session()->has('cf_octroi') ? json_decode(session('cf_octroi')) : (object)[];

        $user = \Auth::user();

        //Demandes qui ne sont pas encore octroyé mais déjà avoir l'approbation

        $demande_not_octroyes = Cycle::leftJoin('cf_contrat_pret_groupes','cf_dossiers.id_dossier','cf_contrat_pret_groupes.dossier_id')
                        // ->where('cf_dossiers.groupe_id', session('id_groupe'))
                        ->where('animatrice', $user->name)
                        ->whereNull('dossier_id')
                        ->get();

        $doc_octroyes = [];

        foreach ($demande_not_octroyes as $demande) {
            $doc_octroyes[] = $demande->id_dossier;
        }

        $demandes = GroupLoanApplication::join('cd_demandes','cf_demande_pret_groupes.ref_dde', 'cd_demandes.id_demande')
                                ->join('cf_dossiers', 'cf_demande_pret_groupes.dossier_id','cf_dossiers.id_dossier')
                                ->join('cf_groupe_solidarites','cf_dossiers.groupe_id','cf_groupe_solidarites.id_groupe')
                                ->where('animatrice', $user->name)
                                ->whereIn('dossier_id', $doc_octroyes)
                                ->select('date_demande','dossier_id','num_caisse', DB::raw("SUM(mtt_capital) mtt_capital"))
                                ->groupBy('date_demande','dossier_id','num_caisse')
                                ->get();


        return view('groupement.octroi.index', [
            'demandes'  =>$demandes,
            'search'    =>$search,
        ]);

    }

    public function search(Request $request)
    {

        session()->put('cf_octroi', json_encode($this->request->all()));

        return redirect()->route('gp.octroi.index');

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($id_dossier)
    {

        $dossier = Cycle::where('id_dossier', $id_dossier)->first();

        $carts = session()->has('cf_cart_octroi') ? session('cf_cart_octroi') : [];


        if(count($carts) == 0){
            return redirect()->route('gp.octroi.show', $id_dossier);
        }



        $demandes = GroupLoanApplication::join('cd_demandes','cf_demande_pret_groupes.ref_dde','cd_demandes.id_demande')
                                ->join('tiers','cd_demandes.tiers_id','=','tiers.id_tiers')
                                ->where('dossier_id', $id_dossier)
                                ->get();

        // foreach ($cart as $ref_dde=>$montant) {

        //     $demandes[] = $_demande->find($ref_dde);
        // }

        $nb_demande = count($carts);

        $sum_demande = 0;

        foreach ($carts as $mtt) {
            $sum_demande += $mtt;
        }

        return view('groupement.octroi.create',[
            'dossier'    =>$dossier,
            'demandes'    =>$demandes,
            'carts'    =>$carts,
            'nb_demande'    =>$nb_demande,
            'sum_demande'    =>$sum_demande,
        ]);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store()
    {

        //`ref_pret`, `tiers_id`, `caisse_id`, `ref_dde`, `compte_id_dav`, `date_contrat`, `premier_remb`, `duree_mois`, `frequence`, `echeance`,
        // `num_contrat`, `mtt_capital`, `mtt_interet`, `taux_interet`, `frais`, `produit_id`, `objet_pret`, `type_contrat`, `etat_pret`, `source_fonds`,
        // `employe_id` FROM `cd_contrat_prets`

        $dossier = $this->dossier->find($this->request->dossierId);

        $validator = Validator::make($this->request->all(), [
            'dateContrat'   => 'required|date',
        ]);

        if($validator->fails()){
            return back()->withErrors()->withInput();
        }

        Cycle::where('id_dossier', $dossier->id_dossier)->update(['date_octroi_effectif'=>$this->request->dateContrat]);

        $nbReunion = CycleCalendar::where('dossier_id', $this->request->dossierId)->count();

        if($nbReunion == 0){

            $calendriers = (object)\App\Lib\CalendrierGroupement::getGroupe($dossier, $this->demande,'A');

            $cals = [];

            foreach ($calendriers as $calendrier) {

                $cals[] = [
                    "dossier_id"  =>$dossier->id_dossier,
                    "date_oper"  =>$calendrier->date_oper,
                    "montant"  =>$calendrier->montant,
                ];
            }

            DB::table('cf_calendier_cycles')->insert($cals);

        }

        $first_remb = CycleCalendar::where('dossier_id', $this->request->dossierId)->min('date_oper');

        $produit = ProductLoan::where('id_produit', $this->request->produit)->first();
            
        $echeance = CycleCalendar::where('dossier_id', $this->request->dossierId)->max('date_oper');

        DB::transaction(function () use($dossier, $produit, $first_remb, $echeance){

            $cart =  session('cf_cart_octroi');

            $echeanciers_membres = \App\Lib\CalendrierGroupement::echeancierParMembre($dossier, $this->demande, 'A');

            $compte = AccountSaving::where('tiers_id', $dossier->id_tiers)
                            ->where('produit_id', config('groupement.produit_base'))
                            ->first();

            $num_piece = strtoupper(uniqid());

            foreach ($cart as $ref_dde=>$montant) {

                $demande = $this->demande->find($ref_dde);

                $contrat["id_pret"] =\App\Lib\Referentiel::contratPretId($demande->caisse_id, $this->request->dateContrat);
                $contrat["demande_id"] = $ref_dde;
                $contrat["compte_id"] = $compte->id_compte;
                $contrat["date_contrat"] = $this->request->dateContrat;
                $contrat["premier_remb"] = $first_remb;
                $contrat["duree_mois"] = $produit->duree_max;
                $contrat["frequence"] = 1;
                $contrat["echeance"] = $echeance;
                $contrat["mtt_capital"] = $montant;
                $contrat["mtt_interet"] = $montant * $produit->taux_interet;
                $contrat["taux_interet"] = $produit->taux_interet;
                $contrat["frais"] = $produit->frais;
                $contrat["produit_id"] = $produit->id_produit;

                $echeanciers = [];

                if(key_exists($demande->tiers_id,$echeanciers_membres)){

                    foreach ($echeanciers_membres[$demande->tiers_id] as $ech) {

                        $echeanciers[] = [
                            'pret_id'  =>$contrat["id_pret"],
                            'date_oper' =>$ech->date_oper,
                            'montant' =>$ech->montant,
                        ];

                    }

                    // GroupSchedule::insert($echeanciers);

                }

                $id_journal = strtoupper(uniqid(config('groupement.caisseId')));

                $journal['id_journal']  = $id_journal;
                $journal['exo_id']  = config('groupement.exo');
                $journal['caisse_id']  = config('groupement.caisseId');
                $journal['employe_id']  = $dossier->animatrice;
                $journal['date_oper']  = $this->request->dateContrat;
                $journal['cloture']  = 'N';
                $journal['type_journal']  = 'JOD';
                $journal['module'] = 'G';
                $journal['libelle'] = "JOURNAL DCF ".$dossier->animatrice;

                ContractLoan::create($contrat);

                GroupLoanContract::create([
                    'pret_id'   =>$contrat["id_pret"],
                    'groupe_id' =>$demande->groupe_id,
                    'dossier_id'  =>$this->request->dossierId,
                ]);

                JournalAccounting::create($journal);

                TransactionLoan::create([
                    'pret_id'  =>$contrat["id_pret"],
                    'journal_id'  =>$id_journal,
                    'num_piece'  =>$num_piece,
                    'date_oper'  =>$contrat["date_contrat"],
                    'debit'  =>$contrat["mtt_capital"],
                ]);

                ScheduleLoan::create([
                    'pret_id'  =>$contrat["id_pret"],
                    'date_oper'  =>$echeance,
                    'capital'  =>$contrat["mtt_capital"],
                    'interet'  =>$contrat["mtt_capital"]*0.18,
                ]);

                GroupSchedule::insert($echeanciers);

                Cycle::where('id_dossier', $this->request->dossierId)->update([
                    'statut' =>'A',
                    'statut_octroi' =>'1'
                ]);

            }

        });

        session()->forget('cf_cart_octroi');

        session()->flash("success", "Les contrats ont bien été enregistré avec succès");

        return redirect()->route('gp.contrat.show',  $this->request->dossierId);

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
                        ->where('id_groupe', $dossier->groupe_id)
                        ->first();
        
        session()->put('id_groupe', $groupe->id_groupe);

        $demandes = $_demande->get($id_dossier);

        $nb_demande = $_demande->count($id_dossier);

        $sum_demande = $_demande->sum($id_dossier);

        $carts = session()->has('cf_cart_octroi') ? session('cf_cart_octroi') : [];

        return view('groupement.octroi.show', [
            'groupe' =>$groupe,
            'dossier' =>$dossier,
            'demandes'   =>$demandes,
            'nb_demande'   =>$nb_demande,
            'sum_demande'   =>$sum_demande,
            'carts'   =>$carts,
        ]);
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
    public function destroy($id_groupe, $date_dde)
    {

        session()->forget('cf_cart_octroi');

        return redirect()->route('gp.dde.show', [
            'id_groupe' =>$id_groupe,
            'date_dde' =>$date_dde,
        ]);

    }

}
