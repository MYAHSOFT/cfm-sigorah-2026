<?php

namespace App\Policies;

use App\Models\Association\Cycle;
use App\Models\Association\Group;
use App\Models\User;

/**
 * Un cycle est accessible à l'animatrice qui le porte
 * (ass_cycles.facilitator_id == user.name) ou, à défaut, à l'agent
 * du groupement rattaché (ass_groups.agent_id == user.name).
 */
class CFDossierPolicy
{
    public function access(User $user, Cycle $dossier): bool
    {
        if ((string) $dossier->facilitator_id === (string) $user->name) {
            return true;
        }

        return Group::where('id_group', $dossier->group_id)
            ->where('agent_id', $user->name)
            ->exists();
    }
}
