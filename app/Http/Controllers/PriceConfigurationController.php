<?php

namespace App\Http\Controllers;

use App\Models\PanelModel;
use App\Models\Variant;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PriceConfigurationController extends Controller
{
    public function index()
    {
        $panelModels = PanelModel::all();

        $variants = Variant::with([
            'panelPrices.panelModel',
            'inverterPrice',
            'storagePrices'
        ])->get();

        return Inertia::render('PriceConfigurer', [
            'panelModels' => $panelModels,
            'variants' => $variants,
        ]);
    }
}
