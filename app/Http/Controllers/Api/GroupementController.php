<?php

namespace App\Http\Controllers\Api;

use App\Models\Saving\AccountSaving;
use App\Models\Association\MemberRole;
use App\Models\Bank\Employe;
use App\Models\Association\Group;
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
        $groupes = Group::join('tiers', 'cf_groupe_solidarites.id_groupe', '=', 'tiers.id_tiers')
            ->where('agent_id', $this->agentCode())
            ->when($request->filled('q'), function ($query) use ($request) {
                $query->where('num_caisse', $request->input('q'));
            })
            ->orderBy('nom_tiers')
            ->paginate($request->integer('per_page', 20));

        return $this->paginated($groupes);
    }

    /** Détail d'un groupement + compte de base + animatrice. */
    public function show(Group $groupe)
    {
        $this->authorize('access', $groupe);

        $compte = AccountSaving::where('tiers_id', $groupe->tiers_id)
            ->where('produit_id', config('sigorah.produit_base'))
            ->first();

        return $this->data([
            'groupe'   => $groupe,
            'compte'   => $compte,
            'employe'  => Employe::where('id_employe', $groupe->animatrice_id)->first(),
            'fonctions' => MemberRole::get(),
        ]);
    }
}
