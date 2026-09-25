<?php

namespace App\Http\Controllers\Api;

use App\Models\CFDemandePret;
use App\Models\CFDossier;
use App\Models\Tiers;
use App\Repositories\DemandePretTmpGroupeRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Miroir API de Groupement\DemandeTmpController — demandes du cycle en cours
 * (table cf_demandes), avant transformation en demandes définitives.
 */
class DemandeCycleController extends ApiController
{
    /** File des demandes-cycle de l'animatrice. */
    public function index()
    {
        $demandes = CFDemandePret::join('cf_dossiers', 'cf_demandes.dossier_id', 'cf_dossiers.id_dossier')
            ->join('cf_groupe_solidarites', 'cf_dossiers.groupe_id', 'cf_groupe_solidarites.id_groupe')
            ->join('tiers', 'cf_groupe_solidarites.id_groupe', 'tiers.id_tiers')
            ->where('agent_id', $this->employe()->id_employe)
            ->select('nom_tiers', 'cf_dossiers.groupe_id', 'num_caisse', 'id_dossier', 'debut_cycle', 'statut', DB::raw('SUM(mtt_capital) mtt_capital'))
            ->groupBy('nom_tiers', 'cf_dossiers.groupe_id', 'num_caisse', 'id_dossier', 'debut_cycle', 'statut')
            ->get();

        return $this->data([
            'demandes'    => $demandes,
            'nb_demande'  => $demandes->count(),
            'sum_demande' => $demandes->sum('mtt_capital'),
        ]);
    }

    /** Membres du groupe du dossier n'ayant pas encore de demande-cycle. */
    public function membresEligibles(CFDossier $dossier)
    {
        $this->authorize('access', $dossier);

        $deja = CFDemandePret::where('dossier_id', $dossier->id_dossier)->pluck('tiers_id')->all();

        $tiers = Tiers::join('cf_membres', 'tiers.id_tiers', 'cf_membres.tiers_id')
            ->where('cf_membres.groupe_id', $dossier->groupe_id)
            ->whereNotIn('id_tiers', $deja)
            ->select('tiers.*')
            ->get();

        return $this->data($tiers);
    }

    /** Détail des demandes-cycle d'un dossier. */
    public function showByDossier(DemandePretTmpGroupeRepository $repo, CFDossier $dossier)
    {
        $this->authorize('access', $dossier);

        return $this->data([
            'dossier'     => $dossier,
            'demandes'    => $repo->get($dossier->id_dossier),
            'nb_demande'  => $repo->count($dossier->id_dossier),
            'sum_demande' => $repo->sum($dossier->id_dossier),
        ]);
    }

    /** Ajoute une demande-cycle. Body: { folio, montant, objet }. */
    public function store(Request $request, CFDossier $dossier)
    {
        $this->authorize('access', $dossier);

        $data = $request->validate([
            'folio'   => ['required'],
            'montant' => ['required', 'numeric', 'gt:0'],
            'objet'   => ['required', 'string'],
        ]);

        $demande = CFDemandePret::create([
            'dossier_id'  => $dossier->id_dossier,
            'tiers_id'    => $data['folio'],
            'groupe_id'   => $dossier->groupe_id,
            'mtt_capital' => $data['montant'],
            'objet_pret'  => $data['objet'],
        ]);

        return $this->data($demande, 201);
    }

    /** Modifie une demande-cycle. */
    public function update(Request $request, string $demande)
    {
        $row = CFDemandePret::where('id_demande', $demande)->firstOrFail();
        $dossier = CFDossier::findOrFail($row->dossier_id);
        $this->authorize('access', $dossier);

        $data = $request->validate([
            'montant' => ['required', 'numeric', 'gt:0'],
            'objet'   => ['required', 'string'],
        ]);

        $row->update(['mtt_capital' => $data['montant'], 'objet_pret' => $data['objet']]);

        return $this->data($row->fresh());
    }

    /** Supprime toutes les demandes-cycle d'un dossier + le dossier (mirroir web). */
    public function destroy(CFDossier $dossier)
    {
        $this->authorize('access', $dossier);

        DB::transaction(function () use ($dossier) {
            CFDemandePret::where('dossier_id', $dossier->id_dossier)->delete();
            CFDossier::where('id_dossier', $dossier->id_dossier)->delete();
        });

        return $this->message('Dossier et demandes-cycle supprimés.');
    }
}
