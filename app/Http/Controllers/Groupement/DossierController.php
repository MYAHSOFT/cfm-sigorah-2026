<?php

namespace App\Http\Controllers\Groupement;

use DB;
use App\Models\Association\Cycle;
use App\Models\Lending\ApplicationLoan;
use App\Models\Association\MemberApplication;
use App\Models\Association\Group;
use Illuminate\Http\Request;
use App\Models\Association\GroupLoanContract;
use App\Models\Association\GroupLoanApplication;
use App\Http\Controllers\Controller;
use App\Http\Requests\DossierRequest;
use App\Repositories\CFDossierRepository;
use App\Repositories\DemandePretTmpGroupeRepository;

class DossierController extends Controller
{

    protected $dossier;
    protected $request;

    public function __construct(Request $request)
    {

        $this->request = $request;

    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $user = \Auth::user();


        $groupes = Group::join('tiers', 'cf_groupe_solidarites.id_groupe','=','tiers.id_tiers')
                        ->where('agent_id', $user->name)
                        ->orderBy('nom_tiers')
                        ->get();

        $dossiers = Cycle::join('cf_demande_pret_groupes', 'cf_dossiers.id_dossier','cf_demande_pret_groupes.dossier_id')
                        ->join('cd_demandes', 'cf_demande_pret_groupes.ref_dde','cd_demandes.id_demande')
                        ->where('cf_dossiers.groupe_id', session('id_groupe'))
                        ->where(function($query){

                            $year = $this->nbDossierByYear(date('Y')) > 0 ? date('Y') : date('Y') -1;

                            $archive = !empty($this->request->archive) ? $this->request->archive : $year;

                            if($archive == 'all'){
                                $query->whereYear('debut_cycle', '<=' ,date('Y'));
                            }else{
                                $query->whereYear('debut_cycle', $archive);
                            }

                        })
                        ->select('id_dossier','debut_cycle', DB::raw("SUM(mtt_capital) mtt_capital, SUM(mtt_recommande) mtt_recommande"))
                        ->groupBy('id_dossier')
                        ->orderBy('debut_cycle','desc')
                        ->get();


        $docs = [];

        // dd($dossiers);

        foreach ($dossiers as $dossier) {

            $docs[] = $dossier->id_dossier;

        }

        $nb_contrat_all = GroupLoanContract::where('groupe_id', session('id_groupe'))
                                        ->whereNotIn('dossier_id', $docs)
                                        ->select('dossier_id')
                                        ->groupBy('dossier_id')
                                        ->count();

        return view('groupement.dossiers.index', [
            'groupes'    =>$groupes,
            'dossiers'    =>$dossiers,
            'nb_contrat_all'    =>$nb_contrat_all,
            'archive'   =>$this->request->archive != 'all' ? $this->request->archive : '',
        ]);

    }

    protected function nbDossierByYear($year)
    {

        return Cycle::where('cf_dossiers.groupe_id', session('id_groupe'))
                            ->whereYear('debut_cycle', '=' ,$year)
                            ->count();

    }

    public function search(Request $request)
    {

        $archive = !empty($this->request->archive) ? $this->request->archive : 'all';

        return redirect()->route('gp.doc.index',['archive'=>$archive]);

    }

    public function new(Request $request)
    {
        $objet = !empty($request->objet) ? $request->objet : 'credit';

        return redirect()->route('gp.search', ['objet'=>$objet]);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($type_versement)
    {

        $versements = ['depot','credit'];

        if(!in_array($type_versement, $versements)){
            return abort(404);
        }

        $groupe = Group::where('id_groupe', session('id_groupe'))
                        ->first();

        if(empty($groupe)){
            return abort(404);
        }

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

        $fields = \App\Lib\Forms::get('dossier');

        $url = route('gp.doc.store', $groupe->id_groupe);

        $fields = json_decode(json_encode((object)$fields));

        return view('groupement.dossiers.create', [
            'groupe'    =>$groupe,
            'fields'    =>$fields,
            'cart'       =>$cart,
            'nb_demande'       =>count($cart),
            'sum_demande'       =>$sum_demande,
            'url'       =>$url,
        ]);


    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(DossierRequest $request)
    {
        //`id_cycle`, `id_groupe`, `debut_cycle`, `fin_cycle`, `mode_reunion`

        $objet = $request->objet == 'depot' ? 'depot' : 'credit';

        $id_dossier = \App\Lib\Referentiel::dossierId($request->groupeId, $request->dateDebut);

        $nb_reunions = config('groupement')['nb_reunion'];

        $nb_reunion = current($nb_reunions);

        $mode_reunion = 1;

        if(key_exists($request->mode,$nb_reunions)){

            $nb_reunion = $nb_reunions[$request->mode];
            $mode_reunion = $request->mode;
        }

        $user =\Auth::user();

        $dossier = [
            'id_dossier'  =>$id_dossier,
            'groupe_id'  =>$request->groupeId,
            'debut_cycle'   =>$request->dateDebut,
            'fin_cycle'   =>$request->dateFin,
            'date_prev_octroi'   =>$request->dateOctroi,
            'date_prev_remb'   =>$request->dateFin,
            'mode_reunion'   =>$mode_reunion,
            'jour_reunion'   =>$request->jour,
            'nb_reunion'   =>$nb_reunion,
            'statut'    =>'O',
            'animatrice'    =>$user->name,
        ];

        // Cycle::create($this->dossier);

        //DEMANDE


        if($objet == 'credit'){

            if(session()->has('cf_create_dde') && count(session()->get('cf_create_dde'))>0){

                DB::transaction(function() use ($dossier){

                    $data = session()->get('cf_create_dde');

                    $demandes = [];

                    foreach ($data as $value) {

                        $demande["dossier_id"] = $dossier['id_dossier'];
                        $demande["groupe_id"] = $dossier['groupe_id'];
                        $demande["tiers_id"] = $value["folio"];
                        $demande["mtt_capital"] = $value["montant"];
                        $demande["objet_pret"] = $value["objet"];

                        $demandes[] = $demande;

                    }

                    Cycle::create($dossier);

                    MemberApplication::insert($demandes);

                });

                session()->forget("cf_create_dde");

                session()->flash("success", "Les demandes ont été bien enregistré avec succès.");

                return redirect()->route('gp.demande.tmp.show', $id_dossier);
                // return redirect()->route('gp.calendrier.create', $id_dossier);

            }

        }

        //END DEMANDE

        // session()->flash('success', "Un nouveau cycle a bien été crée.");

        // return redirect()->route('gp.doc.show', $id_dossier);
        return redirect()->route('gp.demande.membre');
    }

    public function storeDemande(DossierRequest $request)
    {
        //`id_cycle`, `id_groupe`, `debut_cycle`, `fin_cycle`, `mode_reunion`

        $objet = $request->objet == 'depot' ? 'depot' : 'credit';

        $id_dossier = \App\Lib\Referentiel::dossierId($request->groupeId, $request->dateDebut);

        $nb_reunions = config('groupement')['nb_reunion'];

        $nb_reunion = current($nb_reunions);

        $mode_reunion = 1;

        if(key_exists($request->mode,$nb_reunions)){

            $nb_reunion = $nb_reunions[$request->mode];
            $mode_reunion = $request->mode;
        }

        $user =\Auth::user();

        $dossier = [
            'id_dossier'  =>$id_dossier,
            'groupe_id'  =>$request->groupeId,
            'debut_cycle'   =>$request->dateDebut,
            'fin_cycle'   =>$request->dateFin,
            'date_prev_octroi'   =>$request->dateOctroi,
            'date_prev_remb'   =>$request->dateFin,
            'mode_reunion'   =>$mode_reunion,
            'jour_reunion'   =>$request->jour,
            'nb_reunion'   =>$nb_reunion,
            'statut'    =>'O',
            'animatrice'    =>$user->name,
        ];

        // Cycle::create($this->dossier);

        //DEMANDE


        if($objet == 'credit'){

            if(session()->has('cf_create_dde') && count(session()->get('cf_create_dde'))>0){

                DB::transaction(function() use ($dossier){

                    $data = session()->get('cf_create_dde');

                    $caisse_id = config('groupement.caisseId');

                    Cycle::create($dossier);

                    foreach ($data as $value) {

                        $id_demande = \App\Lib\Referentiel::demandeId($caisse_id, $dossier['debut_cycle']);

                        $demande["id_demande"] = $id_demande;
                        $demande["ref_demande"] = $id_demande;
                        $demande["tiers_id"] = $value["folio"];
                        $demande["caisse_id"] = $caisse_id;
                        $demande["date_demande"] = $dossier['debut_cycle'];
                        $demande["mtt_capital"] = $value["montant"];
                        $demande["mtt_recommande"] = $value["montant"];
                        $demande["decision"] = 'E';
                        $demande["genre_demande"] = 'CAE';
                        $demande["objet_pret"] = $value["objet"];
                        $demande["duree_mois"] = config('groupement.duree_pret');

                        ApplicationLoan::create($demande);
                        GroupLoanApplication::create([
                            'ref_dde'   =>$demande["id_demande"],
                            'groupe_id' =>$this->dossier['groupe_id'],
                            'dossier_id'  =>$this->dossier['id_dossier'],
                        ]);

                    }


                });

                session()->forget("cf_create_dde");

                session()->flash("success", "Les demandes ont été bien enregistré avec succès.");

                return redirect()->route('gp.demande.show', $id_dossier);
                // return redirect()->route('gp.calendrier.create', $id_dossier);

            }

        }

        //END DEMANDE

        // session()->flash('success', "Un nouveau cycle a bien été crée.");

        // return redirect()->route('gp.doc.show', $id_dossier);
        return redirect()->route('gp.demande.membre');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(
        CFDossierRepository $_dossier,
        $id_dossier)
    {

        $dossier = $_dossier->find($id_dossier);

        if(empty($dossier)){
            return abort(404);
        }

        $fields = \App\Lib\Forms::get('dossier');

        $dossier_data = [];

        foreach ($fields as $key => $value) {

            $field = $value['field'];
            $attribue['value'] = $dossier->$field;
            $attribue['label'] = $value['label'];
            $attribue['type'] = $value['type'];
            $attribue['options'] = key_exists('options',$value) ? $value['options'] : [];

            $dossier_data[$key] = (object)$attribue;

        }

        return view('groupement.demandes.show', [
            'groupe'    =>$dossier,
            'cycle' =>(object)$dossier_data,
        ]);

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(
        DemandePretTmpGroupeRepository $_demande,
        $id_dossier)
    {

        $dossier = Cycle::where('id_dossier', $id_dossier)->first();

        if(empty($dossier->id_dossier)){
            return back();
        }

        $groupe = $dossier->groupe;

        $dossier_data = [];

        $demandeGroupe = GroupLoanApplication::where('dossier_id', $id_dossier)->first();

        if(!empty($demandeGroupe->dossier_id)){

            $sum_demande = ApplicationLoan::join('cf_demande_pret_groupes','cd_demandes.id_demande','cf_demande_pret_groupes.ref_dde')
                                    ->where('dossier_id', $id_dossier)
                                    ->sum('mtt_capital');
    
            $nb_demande = ApplicationLoan::join('cf_demande_pret_groupes','cd_demandes.id_demande','cf_demande_pret_groupes.ref_dde')
                                    ->where('dossier_id', $id_dossier)
                                    ->count();

        }else{

            $sum_demande = $_demande->sum($id_dossier);

            $nb_demande = $_demande->count($id_dossier);

        }

        $fields = \App\Lib\Forms::show($dossier,'dossier');


        $url = route('gp.doc.update', $dossier->id_dossier);

        return view('groupement.dossiers.create', [
            'id_dossier'    =>$id_dossier,
            'groupe'    =>$groupe,
            'fields'    =>$fields,
            'nb_demande'       =>$nb_demande,
            'sum_demande'       =>$sum_demande,
            'url'       =>$url,
        ]);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(DossierRequest $request, $id_dossier)
    {

        $user =\Auth::user();

        $nb_reunions = config('groupement')['nb_reunion'];

        $nb_reunion = current($nb_reunions);

        $mode_reunion = 1;

        if(key_exists($request->mode,$nb_reunions)){

            $nb_reunion = $nb_reunions[$request->mode];
            $mode_reunion = $request->mode;

        }

        $dossier = [
            'debut_cycle'   =>$request->dateDebut,
            'fin_cycle'   =>$request->dateFin,
            'date_prev_octroi'   =>$request->dateOctroi,
            'date_prev_remb'   =>$request->dateFin,
            'mode_reunion'   =>$mode_reunion,
            'jour_reunion'   =>$request->jour,
            'nb_reunion'   =>$nb_reunion,
            'statut'    =>'O',
        ];

        Cycle::where('id_dossier', $id_dossier)
                ->update($dossier);

        $demandeGroupe = GroupLoanApplication::where('dossier_id', $id_dossier)->first();

        $url = route('gp.demande.tmp.show', $id_dossier);

        if(!empty($demandeGroupe->dossier_id)){
            $url = route('gp.demande.show', $id_dossier);
        }

        return redirect($url);


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
