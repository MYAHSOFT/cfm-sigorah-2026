<?php

namespace App\Http\Controllers\Groupement;

use App\Http\Controllers\Controller;
use App\Models\CalendrierCycle;
use Illuminate\Http\Request;

class PlanningController extends Controller
{


    public function home()
    {


        return view('groupement.planning.home', [
            'months'    =>\App\Lib\Combobox::month(),
        ]);


    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($month = 1)
    {

        if($month > 12 || $month == 0){
            $month = 1;
        }

        $year = date('Y');

        $dateMonth = [];

        $dates = [];

        $last_month = \App\Lib\Helper::lastMonth($year)[$month];

        for($i=1;$i<=$last_month;$i++){

            $date = new \DateTime($year.'/'.$month.'/'.$i);

            $dateMonth[$i] = $date;

        }

        $days = \App\Lib\Combobox::days();


        foreach ($days as $key => $day) {

            foreach ($dateMonth as $date) {

                if($date->format('N') == $key){

                    $dates[$key][] = $date->format('Y-m-d');

                }

            }

        }

        $employe = \Auth::user()->employe;

        $agent = $employe->id_employe;

        $calendriers = CalendrierCycle::join('cf_dossiers', 'cf_calendier_cycles.dossier_id','cf_dossiers.id_dossier')
                                ->join('cf_groupe_solidarites', 'cf_dossiers.groupe_id', 'cf_groupe_solidarites.id_groupe')
                                ->join('tiers', 'cf_groupe_solidarites.id_groupe', 'tiers.id_tiers')
                                ->where('animatrice', $agent)
                                ->whereYear('date_oper', $year)
                                ->whereMonth('date_oper', $month)
                                ->select('id_tiers','nom_tiers','id_groupe','num_caisse', 'jour_reunion', 'id_dossier')
                                ->groupBy('id_tiers','nom_tiers','id_groupe','num_caisse','jour_reunion', 'id_dossier')
                                ->get();

        $calendrier_cycles = CalendrierCycle::join('cf_dossiers', 'cf_calendier_cycles.dossier_id','cf_dossiers.id_dossier')
                                ->where('animatrice', $agent)
                                ->whereYear('date_oper', $year)
                                ->whereMonth('date_oper', $month)
                                ->select('cf_calendier_cycles.*')
                                ->get();

        $dossiers = CalendrierCycle::join('cf_dossiers', 'cf_calendier_cycles.dossier_id','cf_dossiers.id_dossier')
                                ->where('animatrice', $agent)
                                ->whereYear('date_oper', $year)
                                ->whereMonth('date_oper', $month)
                                ->select('id_dossier', 'jour_reunion')
                                ->groupBy('id_dossier', 'jour_reunion')
                                ->get();

        $echeanciers = [];

        foreach ($dossiers as $doc) {

            $item_date = $dates[$doc->jour_reunion];

            for($i = 0; $i < 5 ; $i++){

                $echeanciers[$doc->id_dossier][$i] = '';

            }

            foreach ($calendrier_cycles as $cal) {

                if($doc->id_dossier == $cal->dossier_id){

                    $key_date = array_search($cal->date_oper, $item_date);

                    $echeanciers[$doc->id_dossier][$key_date] = $cal->date_oper;

                }

            }
        }

        return view('groupement.planning.index', [
            'days'    =>$days,
            'dates'    =>$dates,
            'calendriers'   =>$calendriers,
            'dateMonth'   =>$dateMonth,
            'echeanciers'   =>$echeanciers,
            'year'  =>$year,
            'month'  =>$month,
            'months'    =>\App\Lib\Combobox::month(),
        ]);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
