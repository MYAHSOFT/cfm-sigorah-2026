<?php

namespace App\Http\Controllers\Groupement;

use App\Http\Controllers\Controller;
use App\Models\CFEncours;
use App\Models\CycleActivite;
use App\Models\Tiers;
use Illuminate\Http\Request;
use DB;
class EncoursGroupement extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(
        Request $request
    )
    {

        $per_page = 10;

        $encours = [];
        $groupes = [];
        $cycles = [];
        $date_arret = session()->has('report_date_arret') ? session('report_date_arret') : date("Y-m-d");

        if($request->list=='on'){

            $employe = \Auth::user()->employe;

            $animatrices = [$employe->id_employe];

            $encours = CFEncours::join('cf_contrat_pret_groupes','cf_encours_at4.ref_pret','=','cf_contrat_pret_groupes.pret_id')
                                ->join('cf_groupe_solidarites','cf_contrat_pret_groupes.groupe_id','=','cf_groupe_solidarites.id_groupe')
                                ->whereIn('animatrice_id', $animatrices)
                                ->where('date_arret',$date_arret)
                                ->select('groupe_id','dossier_id','tiers_id','animatrice_id','date_octroi','echeance',
                                        DB::raw("SUM(debit) AS mtt_octroye,(SUM(debit)-SUM(credit)) AS encours, SUM(interet) interet, SUM(penalite) penalite, COUNT(dossier_id) AS nb_beneficiaire"))
                                ->groupBy('groupe_id','dossier_id','tiers_id','date_octroi','echeance','animatrice_id')
                                ->orderBy('date_octroi')
                                ->havingRaw("SUM(debit)-SUM(credit) > ?", [0])
                                ->paginate($per_page);

            $encours->appends(["list"=>"on"]);


            $tbl_groupes = [];
            $tbl_cycles = [];

            foreach ($encours as $enc) {
                $tbl_groupes[] = $enc->tiers_id;
                $tbl_cycles[] = $enc->cycle_id;
            }

            $tiers = Tiers::whereIn('id_tiers', $tbl_groupes)->get();
            $cycles_activites = CycleActivite::whereIn('id_cycle', $tbl_cycles)->get();

            foreach ($tiers as $groupe) {

                $groupes[$groupe->id_tiers] = (object)[
                    'nom_tiers' =>$groupe->nom_tiers
                ];
            }

            foreach ($cycles_activites as $cycle) {

                $cycles[$cycle->id_cycle] = (object)[
                    'nb_gs' =>$cycle->nb_gs
                ];

            }

        }

        return view('groupement.credits.encours.at4', [
            'encours'   =>$encours,
            'groupes'   =>$groupes,
            'cycles'   =>$cycles,
        ]);
    }

    public function search(Request $request)
    {

        session()->put("gp_report", json_encode($request->all()));

        return redirect()->route('gp.report.at4.index', ['list'=>'on']);

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

        $year = $request->year;
        $month = $request->month;

        $fev = $year%4 == 0 ? 29 : 28;

        $days = [
            1=>31,
            2=>$fev,
            3=>31,
            4=>30,
            5=>31,
            6=>30,
            7=>31,
            8=>31,
            9=>30,
            10=>31,
            11=>30,
            12=>31,
        ];

        $date_arret = date("Y-m-d");

        if(key_exists($month, $days)){

            $date_arret = $year.'-'.$month.'-'.$days[$month];
        }

        $query = "INSERT INTO cf_encours_at4( `ref_pret`,`date_octroi`,`echeance`,`debit`, `credit`, `interet`, `penalite`, `date_arret`)
                  SELECT ref_pret, MIN(date_oper) AS date_ctroi, `echeance`, SUM(`debit`) `debit`, SUM(`credit`) `credit`,
                        SUM(`interet`) `interet`, SUM(`penalite`) `penalite`, '$date_arret' AS `date_arret`
                  FROM
                    (SELECT op.*, c.echeance FROM cd_operations op INNER JOIN cd_contrat_prets c ON op.ref_pret = c.ref_pret WHERE date_oper <='$date_arret') AS o
                  GROUP BY ref_pret
                  HAVING SUM(debit)>SUM(credit)";

        try {

            DB::delete("DELETE FROM cf_encours_at4 WHERE date_arret = '$date_arret'");

            DB::insert($query);

            session()->put("report_date_arret", $date_arret);

            return response()->json(['params'=>[
                "day"  =>$date_arret,
                "mois"  =>$month,
                "annee" =>$year,
                "result" =>true
            ]]);

        } catch (\Throwable $th) {

            return response()->json(["err"=>$th]);
        }

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
