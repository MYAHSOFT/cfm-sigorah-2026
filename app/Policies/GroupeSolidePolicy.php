<?php

namespace App\Policies;

use App\Models\Association\Group;
use App\Models\User;

/**
 * Remplace en API la logique implicite du middleware `groupement` :
 * une animatrice n'accède qu'aux groupements dont elle est l'agent
 * (cf_groupe_solidarites.agent_id == user.name). À appliquer dès qu'un {groupe} figure
 * dans une URL, puisque l'identifiant y circule en clair.
 */
class GroupeSolidePolicy
{
    /**
     * Consulter / opérer sur un groupement précis.
     */
    public function access(User $user, Group $groupe): bool
    {
        return (string) $groupe->agent_id === (string) $user->name;
    }
}
