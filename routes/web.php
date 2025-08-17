<?php

use App\Http\Controllers\AccessController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InstallationCostController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

//Route::get('/', function () {
//    return view('welcome');
//});


// Password access routes
Route::get('/login-password', [AccessController::class, 'show'])->name('login-password');
Route::post('/login-password', [AccessController::class, 'check'])->name('check-password');

Route::middleware(['check-access'])->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');

    Route::post('/installation/calculate', [InstallationCostController::class, 'calculate'])
        ->name('installation.calculate');
});

