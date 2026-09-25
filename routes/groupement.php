<?php

use App\Models\GroupeSolide;
use App\Http\Controllers\Groupement\MembreController;
use App\Http\Controllers\Groupement\DossierController;
use App\Http\Controllers\Groupement\EncoursController;
use App\Http\Controllers\Groupement\FinCycleController;
use App\Http\Controllers\Groupement\MembreInController;
use App\Http\Controllers\Groupement\PlanningController;
use App\Http\Controllers\Groupement\MembreOutController;
use App\Http\Controllers\Groupement\OperationController;
use App\Http\Controllers\Groupement\GroupementController;
use App\Http\Controllers\Groupement\OctroiPretController;
use App\Http\Controllers\Groupement\ContratPretController;
use App\Http\Controllers\Groupement\FicheMembreController;
use App\Http\Controllers\Groupement\CartVersementController;
use App\Http\Controllers\Groupement\DemandeCreditController;
use App\Http\Controllers\Groupement\OperationEditController;
use App\Http\Controllers\Groupement\PanierPretCartController;
use App\Http\Controllers\Groupement\CalendrierGroupeController;
use App\Http\Controllers\Groupement\DecisionDemandeCreditController;
use App\Http\Controllers\Groupement\DemandeTmpController;
use App\Http\Controllers\Groupement\MembreBureauController;
use App\Http\Controllers\Groupement\EcheancierMembreController;
use App\Models\CFDossier;

Route::middleware(['animatrice'])->group(function(){

    Route::prefix('groupement')->group(function(){

        Route::get('/mod1', [GroupementController::class, 'index'])->name('gp.index');
        Route::post('/mod1', [GroupementController::class, 'index']);

        Route::get('/select/{id_groupe}', [GroupementController::class, 'checked'])->name('gp.checked');

        Route::prefix('octroi')->group(function(){

            Route::get('/mod1', [OctroiPretController::class, 'index'])->name('gp.octroi.index');
            Route::post('/mod1', [OctroiPretController::class, 'search'])->name('gp.octroi.search');

            Route::get('/mod1/{id_dossier}', [OctroiPretController::class, 'show'])->name('gp.octroi.show');


        });

        Route::middleware(['groupement'])->group(function(){

            Route::prefix('membre')->group(function(){

                Route::get('/mod1', [MembreController::class, 'index'])->name('gp.mbre.index');
                Route::post('/mod1', [MembreController::class, 'search'])->name('gp.mbre.search');
                Route::get('/erase', [MembreController::class, 'forget'])->name('gp.mbre.forget');

                Route::get('/mod1/{id_tiers}', [MembreController::class, 'show'])->name('gp.mbre.show');
                Route::post('/upload/{id_tiers}', [MembreController::class, 'upload'])->name('gp.mbre.upload');

                Route::get('/mod2', [MembreController::class, 'create'])->name('gp.mbre.create');
                Route::post('/mod2', [MembreController::class, 'store'])->name('gp.mbre.store');

                Route::get('/mod3/{id_tiers}', [MembreController::class, 'edit'])->name('gp.mbre.edit');
                Route::post('/mod3/{id_tiers}', [MembreController::class, 'update'])->name('gp.mbre.update');

                Route::post('/in', [MembreInController::class, 'store'])->name('gp.mbre.in');

                Route::prefix('out')->group(function(){

                    Route::get('/mo1', [MembreOutController::class, 'index'])->name('gp.mbre.out.index');

                    Route::get('/mod2', [MembreOutController::class, 'create'])->name('gp.mbre.out.create');
                    Route::post('/mod2', [MembreOutController::class, 'store'])->name('gp.mbre.out.store');

                    Route::get('/list', [MembreOutController::class, 'cart'])->name('gp.mbre.out.cart');
                    Route::post('/lock', [MembreOutController::class, 'lockStore'])->name('gp.mbre.out.lock');

                    Route::get('/mod4', [MembreOutController::class, 'destroy'])->name('gp.mbre.out.destroy');

                });

            });

            Route::prefix('bureau')->group(function(){

                Route::get('/mod1', [MembreBureauController::class, 'index'])->name('gp.bureau.index');

                Route::get('/mod2/{id_fonction}', [MembreBureauController::class, 'create'])->name('gp.bureau.create');
                Route::get('/mod2/{id_tiers}/{id_fonction}', [MembreBureauController::class, 'store'])->name('gp.bureau.store');


            });

            Route::prefix('demande')->group(function(){

                // Route::get('/all', [DemandeCreditController::class, 'all'])->name('gp.demande.all');

                Route::get('/mbre', [DemandeCreditController::class, 'membre'])->name('gp.demande.membre');
                Route::post('/mbre', [DemandeCreditController::class, 'membre']);

                Route::get('/mod1/{id_dossier}', [DemandeCreditController::class, 'show'])->name('gp.demande.show');
                Route::get('/cart', [DemandeCreditController::class, 'cart'])->name('gp.demande.cart');
                Route::post('/forget', [DemandeCreditController::class, 'forget'])->name('gp.demande.forget');

                Route::get('/mod2/{id_tiers}', [DemandeCreditController::class, 'create'])->name('gp.demande.create');
                Route::post('/mod2', [DemandeCreditController::class, 'store'])->name('gp.demande.store');

                Route::get('/mod3/{id_demande}', [DemandeCreditController::class, 'edit'])->name('gp.demande.edit');
                Route::post('/mod3/{id_demande}', [DemandeCreditController::class, 'update'])->name('gp.demande.update');

                Route::get('/mod4/{id_tiers}', [DemandeCreditController::class, 'destroy'])->name('gp.demande.destroy');

                Route::prefix('tmp')->group(function(){
                    Route::get('/mod1', [DemandeTmpController::class, 'index'])->name('gp.demande.tmp.index');
                    Route::get('/mod1/{id_dossier}', [DemandeTmpController::class, 'show'])->name('gp.demande.tmp.show');

                    Route::get('/membre/{id_dossier}', [DemandeTmpController::class, 'membre'])->name('gp.demande.tmp.membre');

                    Route::get('/mod2/{id_tiers}/{id_dossier}', [DemandeTmpController::class, 'create'])->name('gp.demande.tmp.create');
                    Route::post('/mod2', [DemandeTmpController::class, 'store'])->name('gp.demande.tmp.store');

                    Route::get('/mod3/{id_demande}', [DemandeTmpController::class, 'edit'])->name('gp.demande.tmp.edit');
                    Route::post('/mod3/{id_demande}', [DemandeTmpController::class, 'update'])->name('gp.demande.tmp.update');

                    Route::post('/mod4/{id_dossier}', [DemandeTmpController::class, 'destroy'])->name('gp.demande.tmp.destroy');
                });

            });

            Route::prefix('doc')->group(function(){

                Route::get('/mod1', [DossierController::class, 'index'])->name('gp.doc.index');
                Route::post('/mod1', [DossierController::class, 'search'])->name('gp.doc.search');
                Route::get('/mod1/{id_dossier}', [DossierController::class, 'show'])->name('gp.doc.show');

                Route::get('/mod2/{type_versement}', [DossierController::class, 'create'])->name('gp.doc.create');
                Route::post('/mod2', [DossierController::class, 'store'])->name('gp.doc.store');

                Route::get('/mod3/{id_dossier}', [DossierController::class, 'edit'])->name('gp.doc.edit');
                Route::post('/mod3/{id_dossier}', [DossierController::class, 'update'])->name('gp.doc.update');

            });

            Route::prefix('octroi')->group(function(){

                Route::get('/mod2/{id_dossier}', [OctroiPretController::class, 'create'])->name('gp.octroi.create');
                Route::post('/mod2', [OctroiPretController::class, 'store'])->name('gp.octroi.store');

                Route::get('/mod4/{id_dossier}', [OctroiPretController::class, 'destroy'])->name('gp.octroi.destroy');

                Route::prefix('panier')->group(function(){

                    Route::get('/mod2/{id_demande}', [PanierPretCartController::class, 'create'])->name('gp.octroi.cart1');
                    Route::post('/mod2', [PanierPretCartController::class, 'store'])->name('gp.octroi.cart2');

                    Route::get('/all/{id_dossier}', [PanierPretCartController::class, 'storeAll'])->name('gp.octroi.storeAll');

                    Route::get('/mod4/{id_demande}', [PanierPretCartController::class, 'destroy'])->name('gp.octroi.destroy');
                    Route::get('/mod4', [PanierPretCartController::class, 'forget'])->name('gp.octroi.forget');

                });

            });

            Route::prefix('contrat')->group(function(){
                Route::get('/mod1', [ContratPretController::class, 'index'])->name('gp.contrat.index');
                Route::post('/mod1', [ContratPretController::class, 'search'])->name('gp.contrat.search');
                Route::get('/old', [ContratPretController::class, 'archive'])->name('gp.contrat.archive');
                Route::post('/mod1', [ContratPretController::class, 'search'])->name('gp.contrat.search');
                Route::get('/mod1/{id_dossier}', [ContratPretController::class, 'show'])->name('gp.contrat.show');
            });

            Route::prefix('calendrier')->group(function(){

                Route::get('/mod1', [CalendrierGroupeController::class, 'index'])->name('gp.calendrier.index');

                Route::get('/mod1/{id_dossier}', [CalendrierGroupeController::class, 'show'])->name('gp.calendrier.show');

                Route::get('/membre/{ref_pret}', [EcheancierMembreController::class, 'show'])->name('gp.echeancier.show');

                Route::middleware(['animatrice:ANIM'])->group(function(){

                    Route::get('/mod2/{id_dossier}', [CalendrierGroupeController::class, 'create'])->name('gp.calendrier.create');
                    Route::post('/mod2', [CalendrierGroupeController::class, 'store'])->name('gp.calendrier.store');

                    Route::post('/accord', [DecisionDemandeCreditController::class, 'store'])->name('gp.dde.accord.store');

                });

                Route::get('/cart/mod3/{id_dossier}', [CalendrierGroupeController::class, 'edit'])->name('gp.calendrier.edit');
                Route::post('/cart/mod3/{id_dossier}', [CalendrierGroupeController::class, 'edit']);
                Route::post('/cart/mod3', [CalendrierGroupeController::class, 'cart'])->name('gp.calendrier.cart');

                // Route::get('/mod3/{id_dossier}', [CalendrierGroupeController::class, 'edit'])->name('gp.calendrier.edit');
                Route::post('/mod3', [CalendrierGroupeController::class, 'update'])->name('gp.calendrier.update');

            });

            Route::prefix('versement')->group(function(){

                Route::get('/mod1', [CartVersementController::class, 'index'])->name('gp.versement.index');

                Route::get('/reunion/{id_dossier}', [CartVersementController::class, 'echeancier'])->name('gp.versement.ech');

                Route::get('/mod1/{id_dossier}', [CartVersementController::class, 'show'])->name('gp.versement.show');

                Route::get('/mod2/{id_tiers}/{id_dossier}', [CartVersementController::class, 'create'])->name('gp.versement.create');

                Route::post('/mod2', [CartVersementController::class, 'store'])->name('gp.versement.store');

                Route::get('/mod4', [CartVersementController::class, 'destroy'])->name('gp.versement.destroy');

            });

            Route::prefix('operation')->group(function(){

                Route::get('/mod1', [OperationController::class, 'index'])->name('gp.operation.index');

                Route::get('/mod1/{id_dossier}', [OperationController::class, 'show'])->name('gp.operation.show');
                Route::get('/mod1/{id_dossier}/{ref_operation}', [OperationController::class, 'showDetail'])->name('gp.operation.showDetail');

                Route::get('/mod2/{id_dossier}', [OperationController::class, 'create'])->name('gp.operation.create');
                Route::post('/mod2', [OperationController::class, 'store'])->name('gp.operation.store');

                Route::get('/mod3/{id_dossier}/{ref_operaton}', [OperationController::class, 'edit'])->name('gp.operation.edit');
                Route::post('/mod3', [OperationController::class, 'update'])->name('gp.operation.update');

                Route::prefix('membre')->group(function(){
                    Route::get('/mod1/{id_oper}', [OperationEditController::class, 'show'])->name('gp.operation-edit.show');
                    Route::get('/mod3/{id_oper}', [OperationEditController::class, 'edit'])->name('gp.operation-edit.edit');
                    Route::post('/mod3/{id_oper}', [OperationEditController::class, 'update'])->name('gp.operation-edit.update');
                });

                Route::get('/fiche/mod1', [FicheMembreController::class, 'index'])->name('gp.fiche.index');
                Route::get('/fiche/{id_tiers}/{id_dossier}', [FicheMembreController::class, 'show'])->name('gp.fiche.show');

            });

            Route::prefix('fin-cycle')->group(function(){

                Route::get('/mod1', [FinCycleController::class, 'index'])->name('gp.finc.index');

                Route::get('/mod1/{id_dossier}', [FinCycleController::class, 'show'])->name('gp.finc.show');

                Route::get('/mod2/{id_dossier}', [FinCycleController::class, 'create'])->name('gp.finc.create');
                Route::post('/mod2/{id_dossier}', [FinCycleController::class, 'store'])->name('gp.finc.store');

            });

        });

    });

    Route::prefix('planning')->group(function(){

        Route::get('/', [PlanningController::class, 'home'])->name('gp.planning.home');
        Route::get('/mod1/{month}', [PlanningController::class, 'index'])->name('gp.planning.index');

    });

    Route::get('demande/mod1', [DemandeCreditController::class, 'index'])->name('gp.demande.index');

    Route::get('demande/groupe/{id_dossier}', function($id_dossier){


        $dossier = CFDossier::where('id_dossier', $id_dossier)->first();

        // $groupe = GroupeSolide::where('id_groupe', $dossier->groupe_id)->first();


        if(empty($dossier->groupe_id)){
            return back();
        }

        session()->put('id_groupe', $dossier->groupe_id);

        return redirect()->route('gp.demande.show',$dossier->id_dossier);


    })->name('gp.demande.groupe');



    Route::prefix('encours')->group(function(){

        Route::get('mod1', [EncoursController::class, 'index'])->name('gp.encours.index');

        Route::get('mod1/{id_dossier}', [EncoursController::class, 'show'])->name('gp.encours.show');

    });

});
