<?php

use App\Http\Controllers\AccessController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InstallationCostController;
use App\Http\Controllers\PanelModelController;
use App\Http\Controllers\PriceConfigurationController;
use App\Http\Controllers\PriceController;
use App\Http\Controllers\VariantController;
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

//    Route::get('/konfigurator-cen', [PriceConfigurationController::class, 'index']);
    Route::get('/konfigurator-cen', [PriceController::class, 'index'])
        ->name('prices.index');
    Route::resource('prices', PriceController::class)->except(['index', 'update']);
    Route::put('/prices/{variant}', [PriceController::class, 'update'])
        ->name('prices.update');


    Route::get('/panels', function (){
        return Inertia::render('Panels');
    });
    Route::get('/variants', function (){
        return Inertia::render('Variants');
    });

    Route::get('/form', function (){
        return Inertia::render('PriceForm');
    });

    Route::resource('panels', PanelModelController::class);
    Route::resource('variants', VariantController::class)->except(['show']);
    Route::get('/variants/options', [VariantController::class, 'availableOptions'])
        ->name('variants.options');


});

