<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GroupementController;
use App\Http\Controllers\Api\MembreController;
use App\Http\Controllers\Api\BureauController;
use App\Http\Controllers\Api\DemandeController;
use App\Http\Controllers\Api\DemandeCycleController;
use App\Http\Controllers\Api\DossierController;
use App\Http\Controllers\Api\DecisionController;
use App\Http\Controllers\Api\OctroiController;
use App\Http\Controllers\Api\ContratController;
use App\Http\Controllers\Api\CalendrierController;
use App\Http\Controllers\Api\EcheancierController;
use App\Http\Controllers\Api\VersementController;
use App\Http\Controllers\Api\OperationController;
use App\Http\Controllers\Api\OperationEditController;
use App\Http\Controllers\Api\FicheMembreController;
use App\Http\Controllers\Api\FinCycleController;
use App\Http\Controllers\Api\EncoursController;
use App\Http\Controllers\Api\PlanningController;

/*
|--------------------------------------------------------------------------
| API Routes — Fournisseur d'API mobile (sigorah-cfm)
|--------------------------------------------------------------------------
|
| Préfixe /api (RouteServiceProvider) puis /v1. Auth : jetons Sanctum
| (Authorization: Bearer <token>), aucune session. Réponses JSON ;
| erreurs mises en forme par App\Exceptions\Handler::renderApiException().
|
| Liaison de modèle implicite : {groupe} => GroupeSolide (id_groupe),
| {dossier} => CFDossier (id_dossier). L'appartenance est vérifiée dans
| chaque contrôleur via $this->authorize('access', ...).
|
*/

Route::prefix('v1')->group(function () {

    Route::post('login', [AuthController::class, 'login'])->name('api.login');

    Route::middleware('auth:sanctum')->group(function () {

        Route::get('me', [AuthController::class, 'me'])->name('api.me');
        Route::post('logout', [AuthController::class, 'logout'])->name('api.logout');
        Route::post('logout-all', [AuthController::class, 'logoutAll'])->name('api.logout-all');

        Route::middleware('animatrice.api')->group(function () {

            // --- Groupements ---
            Route::get('groupements', [GroupementController::class, 'index']);
            Route::get('groupements/{groupe}', [GroupementController::class, 'show']);

            // --- Membres ---
            Route::get('groupements/{groupe}/membres', [MembreController::class, 'index']);
            Route::post('groupements/{groupe}/membres', [MembreController::class, 'store']);
            Route::get('groupements/{groupe}/membres/{tiers}', [MembreController::class, 'show']);
            Route::put('groupements/{groupe}/membres/{tiers}', [MembreController::class, 'update']);
            Route::post('groupements/{groupe}/membres/{tiers}/photo', [MembreController::class, 'upload']);
            Route::post('groupements/{groupe}/membres/reintegrer', [MembreController::class, 'reintegrer']);
            Route::post('groupements/{groupe}/membres/bloquer', [MembreController::class, 'bloquer']);

            // --- Bureau ---
            Route::get('groupements/{groupe}/bureau', [BureauController::class, 'index']);
            Route::post('groupements/{groupe}/bureau', [BureauController::class, 'store']);

            // --- Dossiers (cycles) ---
            Route::get('groupements/{groupe}/dossiers', [DossierController::class, 'index']);
            Route::post('groupements/{groupe}/dossiers', [DossierController::class, 'store']);
            Route::post('groupements/{groupe}/dossiers/definitif', [DossierController::class, 'storeDefinitif']);
            Route::get('dossiers/{dossier}', [DossierController::class, 'show']);
            Route::put('dossiers/{dossier}', [DossierController::class, 'update']);

            // --- Demandes de crédit ---
            Route::get('demandes', [DemandeController::class, 'index']);
            Route::get('groupements/{groupe}/membres-eligibles', [DemandeController::class, 'membresEligibles']);
            Route::post('groupements/{groupe}/demandes', [DemandeController::class, 'store']);
            Route::get('dossiers/{dossier}/demandes', [DemandeController::class, 'showByDossier']);
            Route::put('demandes/{demande}', [DemandeController::class, 'update']);

            // --- Demandes du cycle courant (cf_demandes) ---
            Route::get('demandes-cycle', [DemandeCycleController::class, 'index']);
            Route::get('dossiers/{dossier}/demandes-cycle', [DemandeCycleController::class, 'showByDossier']);
            Route::get('dossiers/{dossier}/demandes-cycle/membres-eligibles', [DemandeCycleController::class, 'membresEligibles']);
            Route::post('dossiers/{dossier}/demandes-cycle', [DemandeCycleController::class, 'store']);
            Route::put('demandes-cycle/{demande}', [DemandeCycleController::class, 'update']);
            Route::delete('dossiers/{dossier}/demandes-cycle', [DemandeCycleController::class, 'destroy']);

            // --- Décision ---
            Route::post('dossiers/{dossier}/decision', [DecisionController::class, 'store']);

            // --- Octroi ---
            Route::get('octroi', [OctroiController::class, 'index']);
            Route::get('dossiers/{dossier}/octroi', [OctroiController::class, 'showByDossier']);
            Route::post('dossiers/{dossier}/octroi/simulation', [OctroiController::class, 'simulate']);
            Route::post('dossiers/{dossier}/octroi', [OctroiController::class, 'store']);

            // --- Contrats ---
            Route::get('groupements/{groupe}/contrats', [ContratController::class, 'index']);
            Route::get('dossiers/{dossier}/contrat', [ContratController::class, 'showByDossier']);

            // --- Calendrier / échéancier ---
            Route::get('calendriers', [CalendrierController::class, 'index']);
            Route::get('dossiers/{dossier}/calendrier', [CalendrierController::class, 'showByDossier']);
            Route::post('dossiers/{dossier}/calendrier', [CalendrierController::class, 'store']);
            Route::put('dossiers/{dossier}/calendrier', [CalendrierController::class, 'update']);
            Route::get('prets/{refPret}/echeancier', [EcheancierController::class, 'show']);

            // --- Versements ---
            Route::get('versements', [VersementController::class, 'index']);
            Route::get('dossiers/{dossier}/versements/echeancier', [VersementController::class, 'echeancier']);
            Route::get('dossiers/{dossier}/versements/reunion', [VersementController::class, 'reunion']);
            Route::post('dossiers/{dossier}/versements', [VersementController::class, 'store']);

            // --- Opérations ---
            Route::get('groupements/{groupe}/operations', [OperationController::class, 'indexByGroupe']);
            Route::get('dossiers/{dossier}/operations', [OperationController::class, 'showByDossier']);
            Route::get('dossiers/{dossier}/operations/{refOperation}', [OperationController::class, 'detail']);
            Route::put('dossiers/{dossier}/operations/{refOperation}/date', [OperationController::class, 'updateDate']);
            Route::get('membres/{tiers}/operation-infos/{refPret?}', [OperationController::class, 'membre']);
            Route::get('operations/{idOper}', [OperationEditController::class, 'show']);
            Route::put('operations/{idOper}', [OperationEditController::class, 'update']);

            // --- Fiche membre ---
            Route::get('groupements/{groupe}/fiches', [FicheMembreController::class, 'index']);
            Route::get('dossiers/{dossier}/membres/{tiers}/fiche', [FicheMembreController::class, 'show']);

            // --- Fin de cycle ---
            Route::get('groupements/{groupe}/fin-cycle', [FinCycleController::class, 'index']);
            Route::get('dossiers/{dossier}/fin-cycle', [FinCycleController::class, 'show']);
            Route::post('dossiers/{dossier}/fin-cycle', [FinCycleController::class, 'store']);

            // --- Encours ---
            Route::get('encours', [EncoursController::class, 'index']);
            Route::get('dossiers/{dossier}/encours', [EncoursController::class, 'show']);

            // --- Planning ---
            Route::get('planning', [PlanningController::class, 'index']);
        });
    });
});
