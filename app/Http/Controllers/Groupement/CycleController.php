<?php

namespace App\Http\Controllers\Groupement;

use Illuminate\Http\Request;
use App\Models\CycleActivite;
use App\Http\Requests\CycleRequest;
use App\Http\Controllers\Controller;
use App\Repositories\GroupeSolideRepository;
use App\Repositories\CycleActiviteRepository;
use App\Repositories\GroupeSolideMembreRepository;
use App\Repositories\RemboursementGroupeRepository;

class CycleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(
        GroupeSolideRepository $_groupe,
        CycleActiviteRepository $_cycle,
        RemboursementGroupeRepository $_remb,
        Request $request
    )
    {

        $groupe = null;

        $cycles = [];

        $rembs = [];

        $per_page = 10;

        $search = session()->has('cf_cycle') ? json_decode(session('cf_cycle')) : (object)[];


        if($request->list=='on'){


            $id_groupe = !empty($search->codeGroupe) ? $search->codeGroupe : null;

            $groupe = $_groupe->find($id_groupe);

            $cycles = $_cycle->paginate($per_page);

            $cycles->appends(['list'=>'on'])->links();

            $cycle_items = [];

            foreach ($cycles as $cycle) {
                $cycle_items[] = $cycle->id_cycle;
            }

            $remboursements = $_remb->getSumByCycle($cycle_items);

            foreach ($remboursements as $remb) {

                $rembs[$remb->id_cycle] = $remb->debit;
            }

        }

        return view('groupement.cycles.index', [
            'groupe'    =>$groupe,
            'cycles'    =>$cycles,
            'rembs'    =>$rembs,
            'request'   =>$request,
            'search'   =>$search,
        ]);

    }

    public function search(Request $request)
    {

        session()->put('cf_cycle', json_encode($request->all()));

        return redirect()->route('gp.cycle.index', ['list'=>'on']);

    }

    public function new()
    {
        return redirect()->route('gp.search', ['objet'=>'credit']);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(
        GroupeSolideRepository $_groupe,
        CycleActiviteRepository $_cycle,
        GroupeSolideMembreRepository $_membre_groupe,
        $id_groupe)
    {

        $groupe = $_groupe->find($id_groupe);

        if(empty($groupe)){
            return abort(404);
        }

        $membres = $_membre_groupe->getMembre($groupe->id_groupe);

        if($membres->count() == 0){

            session()->flash("warning", "Avertissement@Impossible de créer un cycle d'activité, car la liste des membres est vide.");

            return redirect()->route('gp.groupe.show', $groupe->id_groupe);
        }


        $cycle = $_cycle->lastCycleByOpen($groupe->id_groupe);



        if(!empty($cycle->id_cycle)){
            session()->flash("warning", "Vous avez encore un cycle en cours.");
            return redirect()->route('gp.cycle.show', $cycle->id_cycle);
        }

        $fields = json_decode(json_encode((object) \App\Lib\Forms::get('cycle')));

        return view('groupement.cycles.create', [
            'groupe'    =>$groupe,
            'fields'    =>$fields,
            'url'    =>route('gp.cycle.store', $groupe->id_groupe),
        ]);


    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(
        CycleRequest $request,
        $id_groupe)
    {
        //`id_cycle`, `id_groupe`, `debut_cycle`, `fin_cycle`, `mode_reunion`

        $id_cycle = \App\Lib\Referentiel::cycleId($id_groupe, $request->dateDebut);

        $nb_reunions = [
            1=>16,
            2=>8,
            4=>4,
        ];

        $nb_reunion = 16;
        $mode_reunion = 1;

        if(key_exists($request->mode,$nb_reunions)){

            $nb_reunion = $nb_reunions[$request->mode];
            $mode_reunion = $request->mode;
        }

        CycleActivite::create([
            'id_cycle'  =>$id_cycle,
            'groupe_id'  =>$id_groupe,
            'debut_cycle'   =>$request->dateDebut,
            'fin_cycle'   =>$request->dateFin,
            'date_prev_octroi'   =>$request->dateOctroi,
            'date_prev_remb'   =>$request->dateFin,
            'mode_reunion'   =>$mode_reunion,
            'jour_reunion'   =>$request->jour,
            'nb_reunion'   =>$nb_reunion,
            'statut'    =>'O',
            'animatrice'    =>session('agent'),
        ]);

        session()->flash('success', "Un nouveau cycle a bien été crée.");

        return redirect()->route('gp.cycle.show', $id_cycle);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(
        CycleActiviteRepository $_cycle,
        $id_cycle)
    {

        $cycle = $_cycle->find($id_cycle);

        if(empty($cycle)){
            return abort(404);
        }

        $fields = \App\Lib\Forms::get('cycle');

        $cycle_data = [];

        foreach ($fields as $key => $value) {

            $field = $value['field'];
            $attribue['value'] = $cycle->$field;
            $attribue['label'] = $value['label'];
            $attribue['type'] = $value['type'];
            $attribue['options'] = key_exists('options',$value) ? $value['options'] : [];

            $cycle_data[$key] = (object)$attribue;

        }

        return view('groupement.dossiers.show', [
            'groupe'    =>$cycle,
            'cycle' =>(object)$cycle_data,
        ]);

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(
        CycleActiviteRepository $_cycle,
        $id_cycle)
    {

        $cycle = $_cycle->find($id_cycle);

        if(empty($cycle)){
            return abort(404);
        }

        $fields = \App\Lib\Forms::get('cycle');

        $cycle_data = [];

        foreach ($fields as $key => $value) {

            $field = $value['field'];
            $attribue['value'] = $cycle->$field;
            $attribue['label'] = $value['label'];
            $attribue['type'] = $value['type'];
            $attribue['options'] = key_exists('options',$value) ? $value['options'] : [];

            $cycle_data[$key] = (object)$attribue;

        }


        return view('groupement.cycles.create', [
            'groupe'    =>$cycle,
            'fields' =>(object)$cycle_data,
            'url'    =>route('gp.cycle.update', $cycle->id_cycle),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(
        CycleRequest $request,
        $id_cycle)
    {
        //`id_cycle`, `id_groupe`, `debut_cycle`, `fin_cycle`, `mode_reunion`

        CycleActivite::where('id_cycle', $id_cycle)
                ->update([
            'debut_cycle'   =>$request->dateDebut,
            'fin_cycle'   =>$request->dateFin,
            'mode_reunion'   =>$request->mode,
        ]);

        session()->flash('success', "Les modifications ont bien été enregsitré.");

        return redirect()->route('gp.cycle.show', $id_cycle);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id_cycle)
    {

        CycleActivite::where('id_cycle', $id_cycle)
                ->update([
                    'statut'=>'A',
                ]);

        return redirect()->route('gp.cycle.index');
    }
}
