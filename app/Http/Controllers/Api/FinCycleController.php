<?php

namespace App\Http\Controllers\Api;

use App\Models\Lending\ScheduleLoan;
use App\Models\Lending\TransactionLoan;
use App\Models\Association\Cycle;
use App\Models\Association\MeetingOperation;
use App\Models\Association\Group;
use App\Models\Accounting\JournalAccounting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Miroir API de Groupement\FinCycleController — clôture d'un cycle de crédit.
 */
class FinCycleController extends ApiController
{
    /** Encours d'un groupement (candidats à la clôture). */
    public function index(Group $groupe)
    {
        $this->authorize('access', $groupe);

        $encours = TransactionLoan::join('cf_contrat_pret_groupes', 'cd_operations.pret_id', 'cf_contrat_pret_groupes.pret_id')
            ->join('cf_dossiers', 'cf_contrat_pret_groupes.dossier_id', 'cf_dossiers.id_dossier')
            ->where('cf_dossiers.groupe_id', $groupe->id_groupe)
            ->select('id_dossier', 'debut_cycle', DB::raw('MIN(date_oper) date_oper, SUM(debit) mtt_octroi'))
            ->groupBy('id_dossier', 'debut_cycle')
            ->orderBy('debut_cycle', 'desc')
            ->havingRaw('SUM(debit) > SUM(credit)')
            ->get();

        return $this->data($encours);
    }

    /** Récapitulatif de clôture d'un dossier (octrois, échéancier, opérations). */
    public function show(Cycle $dossier)
    {
        $this->authorize('access', $dossier);

        $cdOperation = TransactionLoan::join('cf_contrat_pret_groupes', 'cd_operations.pret_id', 'cf_contrat_pret_groupes.pret_id')
            ->select('dossier_id', DB::raw('SUM(debit) mtt_octroye'))
            ->where('dossier_id', $dossier->id_dossier)
            ->groupBy('dossier_id')
            ->first();

        $echeancier = ScheduleLoan::join('cf_contrat_pret_groupes', 'cd_calendriers.pret_id', 'cf_contrat_pret_groupes.pret_id')
            ->where('dossier_id', $dossier->id_dossier)
            ->select('dossier_id', DB::raw('SUM(capital) capital, SUM(interet) interet'))
            ->groupBy('dossier_id')
            ->first();

        $cfOperation = MeetingOperation::where('dossier_id', $dossier->id_dossier)
            ->select('dossier_id', DB::raw('SUM(mtt_remb) mtt_remb, SUM(mtt_depot) mtt_depot, SUM(mtt_retrait) mtt_retrait, SUM(penalite) penalite'))
            ->groupBy('dossier_id')
            ->first();

        return $this->data([
            'dossier'      => $dossier,
            'cd_operation' => $cdOperation,
            'echeancier'   => $echeancier,
            'cf_operation' => $cfOperation,
        ]);
    }

    /**
     * Clôture le cycle (miroir de store()).
     * Body: { date_oper }
     */
    public function store(Request $request, Cycle $dossier)
    {
        $this->authorize('access', $dossier);

        $request->validate(['date_oper' => ['required', 'date']]);
        $dateOper = $request->input('date_oper');

        $octrois = TransactionLoan::join('cf_contrat_pret_groupes', 'cd_operations.pret_id', 'cf_contrat_pret_groupes.pret_id')
            ->where('dossier_id', $dossier->id_dossier)
            ->get();

        $echeanciers = ScheduleLoan::join('cf_contrat_pret_groupes', 'cd_calendriers.pret_id', 'cf_contrat_pret_groupes.pret_id')
            ->where('dossier_id', $dossier->id_dossier)
            ->get();

        $interets = [];
        foreach ($echeanciers as $e) {
            $interets[$e->ref_pret] = $e->interet;
        }

        $numPiece = strtoupper(Str::ulid());
        $remboursements = [];
        foreach ($octrois as $octroi) {
            $remboursements[] = [
                'journal_id' => $octroi->journal_id,
                'pret_id'    => $octroi->pret_id,
                'num_piece'  => $numPiece,
                'date_oper'  => $dateOper,
                'debit'      => 0,
                'credit'     => $octroi->debit,
                'interet'    => $interets[$octroi->ref_pret] ?? 0,
                'penalite'   => 0,
            ];
        }

        DB::transaction(function () use ($dossier, $remboursements, $dateOper) {
            $idJournal = strtoupper(Str::ulid());
            $exo = (new \DateTime($dateOper))->format('Y');

            JournalAccounting::create([
                'id_journal'   => $idJournal,
                'exo_id'       => $exo,
                'caisse_id'    => config('groupement.caisseId'),
                'employe_id'   => $dossier->animatrice,
                'date_oper'    => $dateOper,
                'cloture'      => 'N',
                'type_journal' => 'JOD',
                'module'       => 'G',
                'libelle'      => 'JOURNAL DCF ' . $dossier->animatrice,
            ]);

            if ($remboursements) {
                TransactionLoan::insert($remboursements);
            }

            Cycle::where('id_dossier', $dossier->id_dossier)->update(['statut' => 'C']);
        });

        return $this->message('Cycle clôturé.');
    }
}
