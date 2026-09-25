<?php

namespace App\Http\Controllers\Groupement;

use App\Models\Association\Cycle;
use App\Models\Association\GroupSchedule;
use App\Models\Lending\ScheduleLoan;
use App\Models\Lending\TransactionLoan;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Association\GroupLoanContract;
use App\Repositories\CFDossierRepository;
use App\Repositories\CFCalendierCyclesRepository;
use App\Repositories\DemandePretGroupeRepository;
use App\Lib\Search;
use DB;
class CalendrierGroupeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $user = \Auth::user();

        $encours = TransactionLoan::join('cf_contrat_pret_groupes', 'cd_operations.pret_id', 'cf_contrat_pret_groupes.pret_id')
                            ->join('cf_groupe_solidarites', 'cf_contrat_pret_groupes.groupe_id','cf_groupe_solidarites.id_groupe')
                            ->join('cf_dossiers','cf_contrat_pret_groupes.dossier_id','cf_dossiers.id_dossier')
                            ->join('tiers','cf_groupe_solidarites.id_groupe','tiers.id_tiers')
                            ->where('agent_id', $user->name)
                            ->select('id_groupe','id_dossier','debut_cycle','nom_tiers','num_caisse', DB::raw("SUM(debit) mtt_octroi"))
                            ->groupBy('id_groupe','id_dossier','debut_cycle','nom_tiers','num_caisse')
                            ->orderBy('nom_tiers')
                            ->havingRaw('SUM(debit)>SUM(credit)')
                            ->get();

        $nb_encours = $encours->count();

        $sum_encours = 0;

        foreach($encours as $enc){
            $sum_encours += $enc->mtt_octroi;
        }

       return view('groupement.calendriers.index', [
        'encours'   =>$encours,
        'nb_encours'   =>$nb_encours,
        'sum_encours'   =>$sum_encours,
       ]);


    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(
        CFDossierRepository $_dossier,
        DemandePretGroupeRepository $_demande,
        $id_dossier)
    {

        $groupe = $_dossier->find($id_dossier);


        if(empty($groupe->id_groupe)){
            return abort(404);
        }

        $decision = 'A';

        $props = session()->has('cf_calendrier_tmp') ?
                    json_decode(session('cf_calendrier_tmp')) :
                    (object)["date_octroi"=>$groupe->date_prev_octroi];

        $calendriers = (object)\App\Lib\CalendrierGroupement::getGroupe($groupe, $_demande, $decision);

        $sum_demande = $_demande->sumDemandeByCycle($id_dossier);

        $remb = $sum_demande->mtt_recommande*1.18;

        $cart = session()->has('cf_cart_octroi') ? session('cf_cart_octroi') : [];

        return view('groupement.calendriers.create', [
            'groupe' =>$groupe,
            'sum_demande'   =>$sum_demande,
            'calendriers'   =>$calendriers,
            'remb'   =>$remb,
            'props'  =>$props,
            'cart'  =>$cart,
            'id_dossier'  =>$id_dossier,
        ]);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(
        CFDossierRepository $_dossier,
        DemandePretGroupeRepository $_demande,
        Request $request)
    {

        $groupe = $_dossier->find($request->dossier);

        $octrois = TransactionLoan::join('cf_contrat_pret_groupes','cd_operations.pret_id','=','cf_contrat_pret_groupes.pret_id')
                        ->where('dossier_id', $request->dossier)
                        ->where('debit','>',0)
                        ->get();

        $decision = 'A';

        $calendriers = (object)\App\Lib\CalendrierGroupement::getGroupe($groupe, $_demande, $decision);

        $cals = [];

        $echeance = $groupe->fin_cycle;

        foreach ($calendriers as $calendrier) {

            $cals[] = [
                "dossier_id"  =>$request->dossier,
                "date_oper"  =>$calendrier->date_oper,
                "montant"  =>$calendrier->montant,
            ];

            $echeance = $calendrier->date_oper;
        }

        $echeanciers = [];

        foreach ($octrois as $octroi) {

            $echeanciers[] =[
                'ref_pret'  =>$octroi->ref_pret,
                'date_oper'  =>$echeance,
                'capital'  =>$octroi->debit,
                'interet'  =>$octroi->debit*config('groupement')['taux_interet'],
            ];
        }

        DB::table('cf_calendier_cycles')->insert($cals);
        DB::table('cd_calendriers')->insert($echeanciers);
        Cycle::where('id_dossier', $request->dossier)
                ->update([
                    'fin_cycle' =>$echeance,
                    'date_prev_remb' =>$echeance,
                ]);

        return redirect()->route('gp.calendrier.show', $request->dossier);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(
        Request $request,
        CFDossierRepository $_dossier,
        CFCalendierCyclesRepository $_calendrier,
        $id_dossier)
    {

        $groupe = $_dossier->find($id_dossier);


        if(empty($groupe)){
            return abort(404);
        }

        if($request->isMethod('post')){
            session()->put('cf_calendrier_tmp', json_encode($request->all()));
        }

        $calendriers = $_calendrier->get($id_dossier);

        if($calendriers->count() == 0){
            return redirect()->route('gp.calendrier.create', $id_dossier);
        }

        $octrois = TransactionLoan::join('cf_contrat_pret_groupes','cd_operations.pret_id','=','cf_contrat_pret_groupes.pret_id')
                        ->where('dossier_id', $id_dossier)
                        ->where('debit','>',0)
                        ->select('dossier_id', DB::raw("SUM(debit) debit, COUNT(*) nb_contrat"))
                        ->groupBy('dossier_id')
                        ->first();


        return view('groupement.calendriers.show', [
            'groupe' =>$groupe,
            'calendriers'   =>$calendriers,
            'id_dossier'  =>$id_dossier,
            'octrois'  =>$octrois,
        ]);

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(
        CFDossierRepository $_dossier,
        DemandePretGroupeRepository $_demande,
        Request $request,
        $id_dossier)
    {

        $dossier = $_dossier->find($id_dossier);


        if(empty($dossier->id_dossier)){
            return abort(404);
        }

        $groupe = $dossier->groupe;

        $url = route('gp.calendrier.edit', $id_dossier);

        $calendriers = [];

        $doc = (object)[
            'date_first_remb'=>date('Y-m-d'),
            'mode_reunion'=>'1',
            'jour_reunion'=>'1',
        ];

        if($request->isMethod('post')){

            // $dossier = Cycle::where('id_dossier', $request->dossierId)->first();
            Search::set('cf_calendrier', $request->all());

            $_demande = new DemandePretGroupeRepository;
    
            $calendriers = \App\Lib\CalendrierGroupement::getGroupeByOctroi($dossier, $_demande, $request);

            $url = route('gp.calendrier.update');

            $doc = (object)[
                'date_first_remb'=>$request->dateFirstRemb,
                'mode_reunion'=>$request->mode,
                'jour_reunion'=>$request->jour,
            ];

        }

        $decision = 'A';

        $search = Search::get('cf_calendrier');

        $props = session()->has('cf_calendrier_tmp') ?
                    json_decode(session('cf_calendrier_tmp')) :
                    (object)["date_octroi"=>$groupe->date_prev_octroi];

        $sum_demande = $_demande->sumDemandeByCycle($id_dossier);

        $remb = $sum_demande->mtt_recommande*1.18;

        $fields = \App\Lib\Forms::show($doc, 'calendrier');

        // dd($fields);

        // $fields = json_decode(json_encode((object)\App\Lib\Forms::get('calendrier')));

        return view('groupement.calendriers.create', [
            'groupe' =>$groupe,
            'dossier' =>$dossier,
            'sum_demande'   =>$sum_demande,
            'calendriers'   =>$calendriers,
            'remb'   =>$remb,
            'fields'   =>$fields,
            'props'  =>$props,
            'search'  =>$search,
            'url'  =>$url,
            'id_dossier'  =>$id_dossier,
        ]);
    }

    public function cart(Request $request)
    {

        $dossier = Cycle::where('id_dossier', $request->dossierId)->first();

        $_demande = new DemandePretGroupeRepository;

        $calendriers = (object)\App\Lib\CalendrierGroupement::getGroupeByOctroi($dossier, $_demande, $request);

        return redirect()->route('gp.calendrier.edit', $request->dossierId);

    }

    public function edit1(
        Request $request,
        CFDossierRepository $_dossier,
        CFCalendierCyclesRepository $_calendrier,
        $id_dossier)
    {

        $groupe = $_dossier->find($id_dossier);


        if(empty($groupe)){
            return abort(404);
        }

        if($request->isMethod('post')){
            session()->put('cf_calendrier_tmp', json_encode($request->all()));
        }

        $calendriers = $_calendrier->get($id_dossier);

        if($calendriers->count() == 0){
            return redirect()->route('gp.calendrier.create', $id_dossier);
        }

        $octrois = TransactionLoan::join('cf_contrat_pret_groupes','cd_operations.pret_id','=','cf_contrat_pret_groupes.pret_id')
                        ->where('dossier_id', $id_dossier)
                        ->where('debit','>',0)
                        ->select('dossier_id', DB::raw("SUM(debit) debit, COUNT(*) nb_contrat"))
                        ->groupBy('dossier_id')
                        ->first();


        return view('groupement.calendriers.cart', [
            'groupe' =>$groupe,
            'calendriers'   =>$calendriers,
            'id_dossier'  =>$id_dossier,
            'octrois'  =>$octrois,
        ]);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        
        $dossier = Cycle::where('id_dossier', $request->dossierId)->first();

        $contrats = GroupLoanContract::join('cd_contrats','cf_contrat_pret_groupes.pret_id','=','cd_contrats.id_pret')
                            ->where('dossier_id', $dossier->id_dossier)
                            ->get();

        $_demande = new DemandePretGroupeRepository;

        $calendriers = (object)\App\Lib\CalendrierGroupement::getGroupeByOctroi($dossier, $_demande, $request);

        $data = [];

        $cals = [];

        $cdCalendrier = [];

        $echenciar = [];

        $collectPretId = [];

        foreach ($calendriers as $calendrier) {

            $cals[] = [
                "dossier_id"  =>$dossier->id_dossier,
                "date_oper"  =>$calendrier->date_oper,
                "montant"  =>$calendrier->montant,
            ];

        }

        foreach ($contrats as $contrat) { 

            foreach ($cals as $cal) {
            
                $cdCalendrier[] = [
                    'pret_id'  =>$contrat->id_pret,
                    'date_oper'  =>$cal["date_oper"],
                    'capital'  =>$contrat->mtt_capital,
                    'interet'  =>$contrat->mtt_capital*0.18,
                ];

                $echenciar[] = [
                    'pret_id'  =>$contrat->id_pret,
                    'date_oper'  =>$cal["date_oper"],
                    'montant'  =>$contrat->mtt_capital*1.18,
                ];          
                
            }

            $collectPretId[] = $contrat->id_pret;

        }

        $data['cycle'] = $cals;
        $data['calendrier'] = $cdCalendrier;
        $data['echeancier'] = $echenciar;

        DB::transaction(function() use ($dossier, $data, $collectPretId, $request) {

            DB::table('cf_calendier_cycles')->where('dossier_id', $dossier->id_dossier)->delete();

            DB::table('cf_calendier_cycles')->insert($data['cycle']);

            ScheduleLoan::whereIn('pret_id',$collectPretId)->delete();

            ScheduleLoan::insert($data['calendrier']);

            GroupSchedule::whereIn('pret_id',$collectPretId)->delete();

            GroupSchedule::insert($data['echeancier']);

            Cycle::where('id_dossier', $dossier->id_dossier)->update([
                'mode_reunion'  =>$request->mode,
                'jour_reunion'  =>$request->jour,
                'nb_reunion'  =>config('groupement.nb_reunion')[$request->mode],
            ]);

        });

        return redirect()->route('gp.calendrier.show', $dossier->id_dossier);

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
