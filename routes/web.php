<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\InstallationCostController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

//Route::get('/', function () {
//    return view('welcome');
//});


Route::get('/', [HomeController::class, 'index'])->name('home');

Route::post('/installation/calculate', [InstallationCostController::class, 'calculate'])
    ->name('installation.calculate');