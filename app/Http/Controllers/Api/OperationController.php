<?php

namespace App\Http\Controllers\Api;

use App\Models\Association\Cycle;
use App\Models\Association\MeetingOperation;
use App\Models\Lending\ContractLoan;
use App\Repositories\TiersRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Miroir API de Groupement\OperationController (lecture + saisie des opérations de réunion).
 * La saisie « panier » est traitée par VersementController::store ; ici on garde
 * la consultation et l'édition de la date d'une opération.
 */
class OperationController extends ApiController
{
    /** Synthèse des opérations par dossier pour le groupement courant. */
    public function indexByGroupe(\App\Models\GroupeSolide $groupe)
    {
        $this->authorize('access', $groupe);

        $dossiers = MeetingOperation::join('cf_dossiers', 'cf_operations.dossier_id', 'cf_dossiers.id_dossier')
            ->where('groupe_id', $groupe->id_groupe)
            ->select('id_dossier')
            ->groupBy('id_dossier')
            ->pluck('id_dossier');

        $contrats = Cycle::join('cf_contrat_pret_groupes', 'cf_dossiers.id_dossier', 'cf_contrat_pret_groupes.dossier_id')
            ->join('cd_operations', 'cf_contrat_pret_groupes.pret_id', '=', 'cd_operations.pret_id')
            ->whereIn('id_dossier', $dossiers)
            ->select('id_dossier', DB::raw('MIN(date_oper) date_oper, SUM(debit) debit, SUM(credit) credit'))
            ->groupBy('id_dossier')
            ->get();

        return $this->data($contrats);
    }

    /** Opérations d'un dossier, groupées par réunion (?tri=asc|desc). */
    public function showByDossier(Request $request, Cycle $dossier)
    {
        $this->authorize('access', $dossier);

        $tri = $request->input('tri') === 'desc' ? 'desc' : 'asc';

        $operations = MeetingOperation::where('dossier_id', $dossier->id_dossier)
            ->select('dossier_id', 'ref_operation', 'date_oper', DB::raw('SUM(mtt_remb) mtt_remb, SUM(mtt_depot) mtt_depot, SUM(mtt_retrait) mtt_retrait, SUM(penalite) penalite'))
            ->groupBy('dossier_id', 'date_oper', 'ref_operation')
            ->orderBy('date_oper', $tri)
            ->get();

        return $this->data(['dossier' => $dossier, 'operations' => $operations]);
    }

    /** Détail d'une opération (par membre). */
    public function detail(Cycle $dossier, string $refOperation)
    {
        $this->authorize('access', $dossier);

        $operations = MeetingOperation::join('tiers', 'cf_operations.tiers_id', 'tiers.id_tiers')
            ->join('cf_membres', 'tiers.id_tiers', 'cf_membres.tiers_id')
            ->where('ref_operation', $refOperation)
            ->where('dossier_id', $dossier->id_dossier)
            ->orderBy('profil')
            ->orderBy('nom_tiers')
            ->get();

        $sum = MeetingOperation::where('ref_operation', $refOperation)
            ->where('dossier_id', $dossier->id_dossier)
            ->select('dossier_id', 'ref_operation', 'date_oper', DB::raw('SUM(mtt_remb) mtt_remb, SUM(mtt_depot) mtt_depot, SUM(mtt_retrait) mtt_retrait, SUM(penalite) penalite'))
            ->groupBy('dossier_id', 'date_oper', 'ref_operation')
            ->first();

        return $this->data([
            'ref_operation' => $refOperation,
            'operations'    => $operations,
            'total'         => $sum,
        ]);
    }

    /** Modifie la date d'une opération (miroir de update()). Body: { date_oper }. */
    public function updateDate(Request $request, Cycle $dossier, string $refOperation)
    {
        $this->authorize('access', $dossier);

        $request->validate(['date_oper' => ['required', 'date']]);

        MeetingOperation::where('dossier_id', $dossier->id_dossier)
            ->where('ref_operation', $refOperation)
            ->update(['date_oper' => $request->input('date_oper')]);

        return $this->message('Date de l\'opération mise à jour.');
    }

    /** Infos d'un membre + son contrat (miroir de getMembre, JSON déjà natif côté web). */
    public function membre(TiersRepository $repo, string $tiers, ?string $refPret = null)
    {
        $membre = $repo->find($tiers);
        $contrat = $refPret ? ContractLoan::where('ref_pret', $refPret)->first() : null;

        return $this->data([
            'membre' => [
                'nom'    => $membre->nom_tiers,
                'prenom' => $membre->prenom_tiers ?? '',
                'cin'    => $membre->cin,
            ],
            'contrat' => $contrat,
        ]);
    }
}
