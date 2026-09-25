<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {

    Route::get('/', function () {
        return view('home');
    })->middleware(['groupement'])->name('home');

    Route::get('/abot', function () {
        return view('abot');
    })->name('abot');

    include 'groupement.php';
});

require __DIR__ . '/auth.php';
