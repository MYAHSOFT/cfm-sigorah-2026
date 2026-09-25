<?php

namespace App\Http\Controllers\Api;

use App\Models\Compte;
use App\Models\CFFonctionGroupe;
use App\Models\Employe;
use App\Models\GroupeSolide;
use Illuminate\Http\Request;

/**
 * Miroir API de Groupement\GroupementController.
 * Web : la sélection du groupe se faisait via session('id_groupe').
 * API : le groupe est un segment d'URL, contrôlé par GroupeSolidePolicy.
 */
class GroupementController extends ApiController
{
    /** Liste des groupements occupés par l'animatrice connectée. */
    public function index(Request $request)
    {
        $groupes = GroupeSolide::join('tiers', 'cf_groupe_solidarites.id_groupe', '=', 'tiers.id_tiers')
            ->where('agent_id', $this->agentCode())
            ->when($request->filled('q'), function ($query) use ($request) {
                $query->where('num_caisse', $request->input('q'));
            })
            ->orderBy('nom_tiers')
            ->paginate($request->integer('per_page', 20));

        return $this->paginated($groupes);
    }

    /** Détail d'un groupement + compte de base + animatrice. */
    public function show(GroupeSolide $groupe)
    {
        $this->authorize('access', $groupe);

        $compte = Compte::where('tiers_id', $groupe->tiers_id)
            ->where('produit_id', config('sigorah.produit_base'))
            ->first();

        return $this->data([
            'groupe'   => $groupe,
            'compte'   => $compte,
            'employe'  => Employe::where('id_employe', $groupe->animatrice_id)->first(),
            'fonctions' => CFFonctionGroupe::get(),
        ]);
    }
}
