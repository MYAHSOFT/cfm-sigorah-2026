<?php

namespace App\Http\Controllers\Api;

use App\Models\Lending\TransactionLoan;
use App\Models\Association\Cycle;
use App\Repositories\CFCalendierCyclesRepository;
use App\Repositories\DemandePretGroupeRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Miroir API de Groupement\CalendrierGroupeController.
 * SHIM session('cf_calendrier_tmp') : voir DecisionController.
 */
class CalendrierController extends ApiController
{
    /** Encours (prêts groupe non soldés) de l'animatrice. */
    public function index()
    {
        $encours = TransactionLoan::join('cf_contrat_pret_groupes', 'cd_operations.pret_id', 'cf_contrat_pret_groupes.pret_id')
            ->join('cf_groupe_solidarites', 'cf_contrat_pret_groupes.groupe_id', 'cf_groupe_solidarites.id_groupe')
            ->join('cf_dossiers', 'cf_contrat_pret_groupes.dossier_id', 'cf_dossiers.id_dossier')
            ->join('tiers', 'cf_groupe_solidarites.id_groupe', 'tiers.id_tiers')
            ->where('agent_id', $this->agentCode())
            ->select('id_groupe', 'id_dossier', 'debut_cycle', 'nom_tiers', 'num_caisse', DB::raw('SUM(debit) mtt_octroi'))
            ->groupBy('id_groupe', 'id_dossier', 'debut_cycle', 'nom_tiers', 'num_caisse')
            ->orderBy('nom_tiers')
            ->havingRaw('SUM(debit) > SUM(credit)')
            ->get();

        return $this->data($encours);
    }

    /** Calendrier-cycle d'un dossier. */
    public function showByDossier(CFCalendierCyclesRepository $repo, Cycle $dossier)
    {
        $this->authorize('access', $dossier);

        $calendriers = $repo->get($dossier->id_dossier);

        $octrois = TransactionLoan::join('cf_contrat_pret_groupes', 'cd_operations.pret_id', 'cf_contrat_pret_groupes.pret_id')
            ->where('dossier_id', $dossier->id_dossier)
            ->where('debit', '>', 0)
            ->select('dossier_id', DB::raw('SUM(debit) debit, COUNT(*) nb_contrat'))
            ->groupBy('dossier_id')
            ->first();

        return $this->data([
            'dossier'     => $dossier,
            'calendriers' => $calendriers,
            'octrois'     => $octrois,
        ]);
    }

    /**
     * Génère le calendrier-cycle (miroir de store()).
     * Body: { date_octroi }
     */
    public function store(Request $request, DemandePretGroupeRepository $repo, Cycle $dossier)
    {
        $this->authorize('access', $dossier);

        $request->validate(['date_octroi' => ['required', 'date']]);
        session()->put('cf_calendrier_tmp', json_encode(['date_octroi' => $request->input('date_octroi')]));

        $octrois = TransactionLoan::join('cf_contrat_pret_groupes', 'cd_operations.pret_id', '=', 'cf_contrat_pret_groupes.pret_id')
            ->where('dossier_id', $dossier->id_dossier)
            ->where('debit', '>', 0)
            ->get();

        $calendriers = (object) \App\Lib\CalendrierGroupement::getGroupe($dossier, $repo, 'A');

        $cals = [];
        $echeance = $dossier->fin_cycle;
        foreach ($calendriers as $calendrier) {
            $cals[] = ['dossier_id' => $dossier->id_dossier, 'date_oper' => $calendrier->date_oper, 'montant' => $calendrier->montant];
            $echeance = $calendrier->date_oper;
        }

        $echeanciers = [];
        foreach ($octrois as $octroi) {
            $echeanciers[] = [
                'ref_pret' => $octroi->ref_pret,
                'date_oper' => $echeance,
                'capital'  => $octroi->debit,
                'interet'  => $octroi->debit * config('groupement')['taux_interet'],
            ];
        }

        DB::transaction(function () use ($cals, $echeanciers, $dossier, $echeance) {
            DB::table('cf_calendier_cycles')->insert($cals);
            DB::table('cd_calendriers')->insert($echeanciers);
            Cycle::where('id_dossier', $dossier->id_dossier)
                ->update(['fin_cycle' => $echeance, 'date_prev_remb' => $echeance]);
        });

        return $this->message('Calendrier-cycle généré.', 201);
    }

    /**
     * Recalcule / réécrit le calendrier à partir des paramètres de réunion (miroir de update()).
     * Body: { date_premier_remb, mode, jour }
     */
    public function update(Request $request, DemandePretGroupeRepository $repo, Cycle $dossier)
    {
        $this->authorize('access', $dossier);

        $data = $request->validate([
            'date_premier_remb' => ['required', 'date'],
            'mode'              => ['required'],
            'jour'              => ['required'],
        ]);

        // App\Lib\CalendrierGroupement::getGroupeByOctroi lit $request->dateFirstRemb/mode/jour.
        $request->merge(['dateFirstRemb' => $data['date_premier_remb'], 'mode' => $data['mode'], 'jour' => $data['jour']]);

        $contrats = \App\Models\ContratPretGroupe::join('cd_contrats', 'cf_contrat_pret_groupes.pret_id', '=', 'cd_contrats.id_pret')
            ->where('dossier_id', $dossier->id_dossier)
            ->get();

        $calendriers = (object) \App\Lib\CalendrierGroupement::getGroupeByOctroi($dossier, $repo, $request);

        $cals = [];
        foreach ($calendriers as $c) {
            $cals[] = ['dossier_id' => $dossier->id_dossier, 'date_oper' => $c->date_oper, 'montant' => $c->montant];
        }

        $cdCalendrier = [];
        $echeancier = [];
        $pretIds = [];
        foreach ($contrats as $contrat) {
            foreach ($cals as $cal) {
                $cdCalendrier[] = ['pret_id' => $contrat->id_pret, 'date_oper' => $cal['date_oper'], 'capital' => $contrat->mtt_capital, 'interet' => $contrat->mtt_capital * 0.18];
                $echeancier[]   = ['pret_id' => $contrat->id_pret, 'date_oper' => $cal['date_oper'], 'montant' => $contrat->mtt_capital * 1.18];
            }
            $pretIds[] = $contrat->id_pret;
        }

        DB::transaction(function () use ($dossier, $cals, $cdCalendrier, $echeancier, $pretIds, $data) {
            DB::table('cf_calendier_cycles')->where('dossier_id', $dossier->id_dossier)->delete();
            DB::table('cf_calendier_cycles')->insert($cals);
            \App\Models\Calendrier::whereIn('pret_id', $pretIds)->delete();
            \App\Models\Calendrier::insert($cdCalendrier);
            \App\Models\CFEcheancier::whereIn('pret_id', $pretIds)->delete();
            \App\Models\CFEcheancier::insert($echeancier);
            Cycle::where('id_dossier', $dossier->id_dossier)->update([
                'mode_reunion' => $data['mode'],
                'jour_reunion' => $data['jour'],
                'nb_reunion'   => config('groupement.nb_reunion')[$data['mode']] ?? null,
            ]);
        });

        return $this->message('Calendrier recalculé.');
    }
}
