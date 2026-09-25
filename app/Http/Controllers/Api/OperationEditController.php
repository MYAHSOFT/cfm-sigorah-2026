<?php

namespace App\Http\Controllers\Api;

use App\Models\Association\Cycle;
use App\Models\Association\MeetingOperation;
use App\Models\Association\GroupLoanContract;
use App\Models\Customer\Customer;
use Illuminate\Http\Request;

/**
 * Miroir API de Groupement\OperationEditController — édition d'une opération individuelle
 * (montants remboursement / dépôt / retrait / pénalité).
 */
class OperationEditController extends ApiController
{
    public function show(string $idOper)
    {
        $operation = MeetingOperation::where('id_oper', $idOper)->firstOrFail();
        $dossier = Cycle::findOrFail($operation->dossier_id);
        $this->authorize('access', $dossier);

        $membre = Customer::join('cf_membres', 'tiers.id_tiers', '=', 'cf_membres.tiers_id')
            ->where('id_tiers', $operation->tiers_id)
            ->first();

        $contrat = GroupLoanContract::join('cd_contrats', 'cf_contrat_pret_groupes.pret_id', 'cd_contrats.id_pret')
            ->join('cd_demandes', 'cd_contrats.demande_id', 'cd_demandes.id_demande')
            ->where('dossier_id', $operation->dossier_id)
            ->where('tiers_id', $operation->tiers_id)
            ->first();

        return $this->data([
            'operation' => $operation,
            'membre'    => $membre,
            'contrat'   => $contrat,
            'id_dossier' => $operation->dossier_id,
        ]);
    }

    public function update(Request $request, string $idOper)
    {
        $operation = MeetingOperation::where('id_oper', $idOper)->firstOrFail();
        $dossier = Cycle::findOrFail($operation->dossier_id);
        $this->authorize('access', $dossier);

        $data = $request->validate([
            'remboursement' => ['nullable', 'numeric', 'min:0'],
            'depot'         => ['nullable', 'numeric', 'min:0'],
            'retrait'       => ['nullable', 'numeric', 'min:0'],
            'penalite'      => ['nullable', 'numeric', 'min:0'],
        ]);

        $operation->update([
            'mtt_remb'    => $data['remboursement'] ?? 0,
            'mtt_depot'   => $data['depot'] ?? 0,
            'mtt_retrait' => $data['retrait'] ?? 0,
            'penalite'    => $data['penalite'] ?? 0,
        ]);

        return $this->data($operation->fresh());
    }
}
