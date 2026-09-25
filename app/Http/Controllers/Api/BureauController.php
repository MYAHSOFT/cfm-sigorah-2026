<?php

namespace App\Http\Controllers\Api;

use App\Models\Association\Member;
use App\Models\Association\Group;
use App\Models\Customer\Customer;
use Illuminate\Http\Request;

/**
 * Miroir API de Groupement\MembreBureauController.
 */
class BureauController extends ApiController
{
    private const FONCTIONS = [
        'PRD' => 'Président(e)',
        'SCE' => 'Secrétaire',
        'TRE' => 'Trésorier(ère)',
        'COM' => 'Commissaire au compte',
        'SAG' => 'Sage',
    ];

    /** Composition actuelle du bureau. */
    public function index(Group $groupe)
    {
        $this->authorize('access', $groupe);

        $tiers = Customer::join('cf_membres', 'tiers.id_tiers', '=', 'cf_membres.tiers_id')
            ->where('groupe_id', $groupe->id_groupe)
            ->where('cf_membres.fonction_id', '<>', 'MBR')
            ->get();

        $membres = [];
        foreach ($tiers as $t) {
            $membres[$t->fonction_id] = [
                'tiers_id' => $t->tiers_id,
                'nom'      => trim($t->nom_tiers . ' ' . $t->prenom_tiers),
                'photo'    => $t->photo,
            ];
        }

        return $this->data([
            'fonctions' => self::FONCTIONS,
            'membres'   => $membres,
        ]);
    }

    /** Affecte un membre à une fonction du bureau. Body: { tiers_id, fonction }. */
    public function store(Request $request, Group $groupe)
    {
        $this->authorize('access', $groupe);

        $data = $request->validate([
            'tiers_id' => ['required'],
            'fonction' => ['required', 'in:' . implode(',', array_keys(self::FONCTIONS))],
        ]);

        // Libère la fonction si déjà occupée, puis l'attribue.
        Member::where('groupe_id', $groupe->id_groupe)
            ->where('fonction_id', $data['fonction'])
            ->update(['fonction_id' => 'MBR']);

        Member::where('groupe_id', $groupe->id_groupe)
            ->where('tiers_id', $data['tiers_id'])
            ->update(['fonction_id' => $data['fonction']]);

        return $this->message('Bureau mis à jour.');
    }
}
