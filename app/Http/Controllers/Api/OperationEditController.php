<?php

namespace App\Http\Controllers\Api;

use App\Models\CFDossier;
use App\Models\CFOperation;
use App\Models\ContratPretGroupe;
use App\Models\Tiers;
use Illuminate\Http\Request;

/**
 * Miroir API de Groupement\OperationEditController — édition d'une opération individuelle
 * (montants remboursement / dépôt / retrait / pénalité).
 */
class OperationEditController extends ApiController
{
    public function show(string $idOper)
    {
        $operation = CFOperation::where('id_oper', $idOper)->firstOrFail();
        $dossier = CFDossier::findOrFail($operation->dossier_id);
        $this->authorize('access', $dossier);

        $membre = Tiers::join('cf_membres', 'tiers.id_tiers', '=', 'cf_membres.tiers_id')
            ->where('id_tiers', $operation->tiers_id)
            ->first();

        $contrat = ContratPretGroupe::join('cd_contrats', 'cf_contrat_pret_groupes.pret_id', 'cd_contrats.id_pret')
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
        $operation = CFOperation::where('id_oper', $idOper)->firstOrFail();
        $dossier = CFDossier::findOrFail($operation->dossier_id);
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
