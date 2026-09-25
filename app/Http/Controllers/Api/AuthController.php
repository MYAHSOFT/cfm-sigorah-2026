<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Resources\AuthUserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Authentifie une animatrice par code (name) + mot de passe et renvoie un jeton Sanctum.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('name', $request->input('name'))->first();

        if (! $user || ! Hash::check($request->input('password'), $user->password)) {
            throw ValidationException::withMessages([
                'name' => [trans('auth.failed')],
            ]);
        }

        // Un seul jeton "mobile" actif par appareil logique : on révoque l'ancien.
        $device = $request->input('device_name', 'mobile');
        $user->tokens()->where('name', $device)->delete();

        $token = $user->createToken($device)->plainTextToken;

        return response()->json([
            'token'      => $token,
            'token_type' => 'Bearer',
            'user'       => new AuthUserResource($user->loadMissing('employe')),
        ]);
    }

    /**
     * Profil de l'utilisateur authentifié (rôle, agence, fonction).
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => new AuthUserResource($request->user()->loadMissing('employe')),
        ]);
    }

    /**
     * Révoque le jeton courant.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Déconnecté.']);
    }

    /**
     * Révoque tous les jetons de l'utilisateur (tous appareils).
     */
    public function logoutAll(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Déconnecté de tous les appareils.']);
    }
}
