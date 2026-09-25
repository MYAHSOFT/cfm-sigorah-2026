<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Équivalent API de CFAnimatrice : vérifie que l'utilisateur authentifié
 * est bien une animatrice (titre_id = ANIM). Aucune écriture en session ;
 * renvoie du JSON en cas de refus.
 */
class EnsureIsAnimatrice
{
    public function handle(Request $request, Closure $next): Response
    {
        $employe = optional($request->user())->employe;

        if (! $employe) {
            return response()->json([
                'message' => "Aucun profil employé n'est rattaché à ce compte.",
            ], 403);
        }

        if ($employe->titre_id !== 'ANIM') {
            return response()->json([
                'message' => "Accès réservé aux animatrices.",
            ], 403);
        }

        return $next($request);
    }
}
