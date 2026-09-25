<?php

namespace App\Http\Controllers\Api;

use App\Models\CFEcheancier;
use App\Models\ContratPretGroupe;
use App\Models\Tiers;
use App\Repositories\CFDossierRepository;
use Illuminate\Http\Request;

/**
 * Miroir API de Groupement\EcheancierMembreController — échéancier d'un prêt individuel.
 */
class EcheancierController extends ApiController
{
    public function show(Request $request, CFDossierRepository $dossierRepo, string $refPret)
    {
        $tiers = Tiers::join('cd_contrat_prets', 'tiers.id_tiers', '=', 'cd_contrat_prets.tiers_id')
            ->where('ref_pret', $refPret)
            ->firstOrFail();

        $contratGroupe = ContratPretGroupe::where('ref_pret', $refPret)->firstOrFail();
        $dossier = $dossierRepo->find($contratGroupe->dossier_id);

        $page = CFEcheancier::where('ref_pret', $refPret)->paginate($request->integer('per_page', 20));

        return response()->json([
            'data' => [
                'tiers'       => $tiers,
                'dossier'     => $dossier,
                'sum_montant' => CFEcheancier::where('ref_pret', $refPret)->sum('montant'),
                'echeances'   => $page->items(),
            ],
            'meta' => [
                'current_page' => $page->currentPage(),
                'last_page'    => $page->lastPage(),
                'per_page'     => $page->perPage(),
                'total'        => $page->total(),
            ],
        ]);
    }
}
