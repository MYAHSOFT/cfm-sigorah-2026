<?php

namespace App\Http\Controllers\Api;

use App\Models\CalendrierCycle;
use App\Models\CFDossier;
use App\Models\ContratPretGroupe;
use App\Models\Echeancier;
use App\Models\Tiers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Miroir API de Groupement\CartVersementController.
 * Le panier session 'cf_versement' devient `lignes:[...]` dans le POST ;
 * les opérations sont écrites directement (comme le faisait OperationController::store).
 */
class VersementController extends ApiController
{
    /** Prêts actifs de l'animatrice (prêts groupe non soldés). */
    public function index()
    {
        $contrats = \App\Models\CDOperation::join('cf_contrat_pret_groupes', 'cd_operations.pret_id', 'cf_contrat_pret_groupes.pret_id')
            ->join('cf_groupe_solidarites', 'cf_contrat_pret_groupes.groupe_id', 'cf_groupe_solidarites.id_groupe')
            ->join('cf_dossiers', 'cf_contrat_pret_groupes.dossier_id', 'cf_dossiers.id_dossier')
            ->join('tiers', 'cf_groupe_solidarites.id_groupe', 'tiers.id_tiers')
            ->where('agent_id', $this->agentCode())
            ->select('id_groupe', 'id_dossier', 'debut_cycle', 'nom_tiers', 'num_caisse', DB::raw('MIN(date_oper) date_oper, SUM(debit) mtt_octroi'))
            ->groupBy('id_groupe', 'id_dossier', 'debut_cycle', 'nom_tiers', 'num_caisse')
            ->orderBy('nom_tiers')
            ->havingRaw('SUM(debit) > SUM(credit)')
            ->get();

        return $this->data($contrats);
    }

    /** Échéancier agrégé d'un dossier. */
    public function echeancier(CFDossier $dossier)
    {
        $this->authorize('access', $dossier);

        $echeanciers = Echeancier::join('cf_contrat_pret_groupes', 'cf_echeanciers.pret_id', 'cf_contrat_pret_groupes.pret_id')
            ->where('dossier_id', $dossier->id_dossier)
            ->select('dossier_id', 'date_oper', DB::raw('SUM(montant) montant'))
            ->groupBy('dossier_id', 'date_oper')
            ->get();

        return $this->data([
            'dossier'      => $dossier,
            'echeanciers'  => $echeanciers,
            'sum_echeance' => CalendrierCycle::where('dossier_id', $dossier->id_dossier)->sum('montant'),
        ]);
    }

    /** État d'une réunion : membres + montant d'échéance attendu par membre. ?reunion=YYYY-MM-DD */
    public function reunion(Request $request, CFDossier $dossier)
    {
        $this->authorize('access', $dossier);

        $reunion = $request->input('reunion');

        $membres = Tiers::join('cf_membres', 'tiers.id_tiers', '=', 'cf_membres.tiers_id')
            ->where('groupe_id', $dossier->groupe_id)
            ->where('status', 'A')
            ->orderBy('profil')
            ->orderBy('nom_tiers')
            ->get();

        $echMembres = Echeancier::join('cf_contrat_pret_groupes', 'cf_echeanciers.pret_id', 'cf_contrat_pret_groupes.pret_id')
            ->join('cd_contrats', 'cf_contrat_pret_groupes.pret_id', 'cd_contrats.id_pret')
            ->join('cd_demandes', 'cd_contrats.demande_id', 'cd_demandes.id_demande')
            ->where('date_oper', $reunion)
            ->where('dossier_id', $dossier->id_dossier)
            ->get();

        $echeanciers = [];
        foreach ($echMembres as $ech) {
            $echeanciers[$ech->tiers_id] = $ech->montant;
        }

        return $this->data([
            'dossier'     => $dossier,
            'reunion'     => $reunion,
            'membres'     => $membres,
            'echeanciers' => $echeanciers,
        ]);
    }

    /**
     * Enregistre les versements d'une réunion (remplace panier + OperationController::store).
     * Body: { date_oper, lignes:[{folio, remboursement?, depot?, retrait?, penalite?}] }
     */
    public function store(Request $request, CFDossier $dossier)
    {
        $this->authorize('access', $dossier);

        $data = $request->validate([
            'date_oper'              => ['required', 'date'],
            'lignes'                => ['required', 'array', 'min:1'],
            'lignes.*.folio'        => ['required'],
            'lignes.*.remboursement' => ['nullable', 'numeric', 'min:0'],
            'lignes.*.depot'        => ['nullable', 'numeric', 'min:0'],
            'lignes.*.retrait'      => ['nullable', 'numeric', 'min:0'],
            'lignes.*.penalite'     => ['nullable', 'numeric', 'min:0'],
        ]);

        $refOperation = 'CF' . \Illuminate\Support\Str::ulid();

        $rows = [];
        foreach ($data['lignes'] as $ligne) {
            $rows[] = [
                'dossier_id'    => $dossier->id_dossier,
                'tiers_id'      => $ligne['folio'],
                'date_oper'     => $data['date_oper'],
                'date_reunion'  => $data['date_oper'],
                'mtt_remb'      => (float) ($ligne['remboursement'] ?? 0),
                'mtt_depot'     => (float) ($ligne['depot'] ?? 0),
                'mtt_retrait'   => (float) ($ligne['retrait'] ?? 0),
                'penalite'      => (float) ($ligne['penalite'] ?? 0),
                'ref_operation' => $refOperation,
            ];
        }

        \App\Models\OperationGroupe::insert($rows);

        return $this->data(['ref_operation' => $refOperation, 'nb_lignes' => count($rows)], 201);
    }
}
