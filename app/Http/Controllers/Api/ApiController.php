<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employe;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Base des contrôleurs d'API v1.
 *
 * Chaque contrôleur reprend, à l'identique, les requêtes Eloquent du
 * contrôleur web correspondant, mais :
 *  - le périmètre `id_groupe` / `id_dossier` vient de l'URL (jamais de la session) ;
 *  - l'appartenance est vérifiée par policy (`access`) ;
 *  - la réponse est du JSON ({ data: ... }) au lieu d'une vue ;
 *  - les « paniers » de session deviennent un tableau `lignes` dans le corps.
 */
abstract class ApiController extends Controller
{
    /** Employé lié à l'utilisateur authentifié. */
    protected function employe(): Employe
    {
        return Auth::user()->employe;
    }

    /** Code animatrice (colonne `animatrice` des dossiers, `agent_id` des groupements). */
    protected function agentCode(): string
    {
        return Auth::user()->name;
    }

    /** Enveloppe standard pour une ressource ou une collection non paginée. */
    protected function data(mixed $payload, int $status = 200): JsonResponse
    {
        return response()->json(['data' => $payload], $status);
    }

    /** Enveloppe standard pour un résultat paginé (data + meta). */
    protected function paginated(LengthAwarePaginator $page): JsonResponse
    {
        return response()->json([
            'data' => $page->items(),
            'meta' => [
                'current_page' => $page->currentPage(),
                'last_page'    => $page->lastPage(),
                'per_page'     => $page->perPage(),
                'total'        => $page->total(),
            ],
        ]);
    }

    protected function message(string $message, int $status = 200): JsonResponse
    {
        return response()->json(['message' => $message], $status);
    }
}
