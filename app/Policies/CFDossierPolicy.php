<?php

namespace App\Policies;

use App\Models\Association\Cycle;
use App\Models\Association\Group;
use App\Models\User;

/**
 * Un dossier est accessible à l'animatrice qui le porte
 * (cf_dossiers.animatrice == user.name) ou, à défaut, à l'agent
 * du groupement rattaché (cf_groupe_solidarites.agent_id == user.name).
 */
class CFDossierPolicy
{
    public function access(User $user, Cycle $dossier): bool
    {
        if ($dossier->animatrice === $user->name) {
            return true;
        }

        return Group::where('id_groupe', $dossier->groupe_id)
            ->where('agent_id', $user->name)
            ->exists();
    }
}
