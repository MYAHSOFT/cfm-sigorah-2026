<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthUserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $employe = $this->whenLoaded('employe');

        return [
            'id'            => $this->id_user,
            'code'          => $this->name,
            'employe'       => $employe ? [
                'id'          => $employe->id_employe,
                'nom'         => $employe->nom_employe ?? null,
                'prenom'      => $employe->prenom_employe ?? null,
                'fonction_id' => $employe->fonction_id ?? null,
                'agence_id'   => $employe->agence_id ?? null,
            ] : null,
            'is_animatrice' => optional($employe)->fonction_id === 'ANIM',
            'abilities'     => $request->user()?->currentAccessToken()?->abilities ?? [],
        ];
    }
}
