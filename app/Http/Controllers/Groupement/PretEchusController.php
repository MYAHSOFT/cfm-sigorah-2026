<?php

namespace App\Http\Controllers\Groupement;

use App\Models\CFDossier;
use Illuminate\Http\Request;
use App\Models\ContratPretGroupe;
use App\Models\EmployeResponsable;
use App\Http\Controllers\Controller;
use App\Repositories\OperationGroupeRepository;
use DB;
class PretEchusController extends Controller
{
    public function index(
        Request $request,
        OperationGroupeRepository $_operation)
    {

        $pret_echus = [];

        $dossiers = [];

        $octrois = [];

        $remboursements = [];

        $search = session()->has('cf_contrat') ? json_decode(session('cf_contrat')) : (object)[];

        $employe = \Auth::user()->employe;



        $pret_echus_all = CFDossier::join('cf_date_fin_cycle','cf_dossiers.id_dossier','cf_date_fin_cycle.dossier_id')
                            ->where('statut', 'C')
                            ->where(function($query){

                                $year = date('Y');

                                $query->whereYear('last_oper', $year);

                            })
                            ->get();

        $employes = EmployeResponsable::join('employes','employe_responsables.id_employe','employes.id_employe')
                                ->where('id_responsable', $employe->id_employe)
                                ->get();

        if($request->list=='on'){

            foreach ($pret_echus_all as $pret) {
                $dossiers[]=$pret->id_dossier;
            }

            $contrat_groupes = ContratPretGroupe::join('cd_operations','cf_contrat_pret_groupes.pret_id','=','cd_operations.pret_id')
                            ->select('dossier_id','groupe_id', DB::raw("MIN(date_oper) AS date_octroi, SUM(debit) AS debit"))
                            ->groupBy('dossier_id','groupe_id')
                            ->whereIn('dossier_id', $dossiers)->get();

           foreach ($contrat_groupes as $contrat) {
            $octrois[$contrat->dossier_id] = $contrat;
           }

            $per_page = 10;

            $operations = DB::table('cf_operations')
                                ->whereIn('dossier_id', $dossiers)
                                ->select("dossier_id", DB::raw("MAX(date_oper) last_oper, SUM(mtt_remb) AS mtt_remb"))
                                ->groupBy('dossier_id')
                                ->get();

            foreach ($operations as $operation) {
                $remboursements[$operation->dossier_id] = $operation;
            }

            $pret_echus = $_operation->pretEchus($per_page);

        }

        return view('groupement.credits.encours.pret-echus',[
            'pret_echus'      =>$pret_echus,
            'octrois'           =>$octrois,
            'remboursements'    =>$remboursements,
            'search'            =>$search,
            'employes'          =>$employes,
        ]);

    }

    public function search(Request $request)
    {

        $data = $request->all();

        $user = \Auth::user();

        $employes = EmployeResponsable::where('id_responsable', $user->name)->get();

        $list_employe = [];

        foreach ($employes as $employe) {

            $list_employe["employe"][] = $employe->id_employe;

        }

        $data = array_merge($data, $list_employe);

        session()->put('cf_contrat', json_encode($data));

        return redirect()->route('gp.pretaechus.index',['list'=>'on']);

    }
}
