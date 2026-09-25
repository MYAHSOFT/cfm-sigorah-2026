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
        $groupes = Group::join('crm_customers', 'ass_groups.id_group', '=', 'crm_customers.folio_customer')
            ->select('ass_groups.*', 'crm_customers.last_name', 'crm_customers.first_name', 'crm_customers.teller_id')
            ->where('ass_groups.agent_id', $this->agentCode())
            ->when($request->filled('q'), function ($query) use ($request) {
                $q = $request->input('q');
                $query->where(function ($query) use ($q) {
                    $query->where('ass_groups.num_group', $q)
                        ->orWhere('ass_groups.id_group', $q)
                        ->orWhere('crm_customers.last_name', 'like', "%{$q}%");
                });
            })
            ->orderBy('crm_customers.last_name')
            ->paginate($request->integer('per_page', 20));

        return $this->paginated($groupes);
    }

    /** Détail d'un groupement + compte de base + animatrice. */
    public function show(Group $groupe)
    {
        $this->authorize('access', $groupe);

        $compte = AccountSaving::where('folio', $groupe->id_group)
            ->where('product_id', config('sigorah.produit_base'))
            ->first();

        return $this->data([
            'groupe'   => $groupe->load('tiers'),
            'compte'   => $compte,
            'employe'  => $groupe->employe,
            'fonctions' => MemberRole::orderBy('sort_order')->get(),
        ]);
    }
}
