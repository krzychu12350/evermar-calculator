<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Variant;
use Illuminate\Support\Facades\DB;
use App\Models\PanelVariantPrice;
use App\Models\StorageVariantPrice;
use App\Models\InverterVariantPrice;

class PriceController extends Controller
{
    /**
     * Show the price setting form for a single variant.
     */
    public function index(Request $request)
    {
        // Fetch all variants for dropdown
        $allVariants = Variant::select('id', 'panel_count')->get();

        // Optional selected variant
        $variantId = $request->get('variant_id');
        $selectedVariant = null;

        if ($variantId) {
            $selectedVariant = Variant::with([
                'panelPrices.panelModel',
                'storagePrices',
                'inverterPrice', // load inverter price
            ])->findOrFail($variantId);

            // Transform data for frontend
            $selectedVariant->models = $selectedVariant->panelPrices
                ->pluck('panelModel')
                ->unique('id')
                ->values()
                ->all();

            $selectedVariant->install_types = $selectedVariant->panelPrices
                ->pluck('install_type')
                ->unique()
                ->values()
                ->all();

            $selectedVariant->storages = $selectedVariant->storagePrices
                ->pluck('capacity_kwh')
                ->unique()
                ->values()
                ->all();

            // Get prices
            $selectedVariant->prices = [
                'panels' => $selectedVariant->panelPrices->mapWithKeys(fn($p) => [
                    $p->panel_model_id => [$p->install_type => $p->price_per_panel]
                ])->all(),
                'storages' => $selectedVariant->storagePrices->pluck('price', 'capacity_kwh')->all(),
                'inverter' => $selectedVariant->inverterPrice->price ?? 0, // inverter price
            ];
        }

        return Inertia::render('Prices/Index', [
            'variants' => $allVariants,
            'selectedVariant' => $selectedVariant,
        ]);
    }

    /**
     * Return single variant JSON with prices.
     */
    public function show(Request $request, Variant $price)
    {
        $variant = $price;

        $variant->load(['panelPrices.panelModel', 'storagePrices', 'inverterPrice']);

        $variant->models = $variant->panelPrices
            ->pluck('panelModel')
            ->unique('id')
            ->values()
            ->all();

        $variant->install_types = $variant->panelPrices
            ->pluck('install_type')
            ->unique()
            ->values()
            ->all();

        $variant->storages = $variant->storagePrices
            ->pluck('capacity_kwh')
            ->unique()
            ->values()
            ->all();

        // Get existing prices if any
        $variant->prices = [
            'panels' => $variant->panelPrices->mapWithKeys(fn($p) => [
                $p->panel_model_id => [$p->install_type => $p->price_per_panel]
            ])->all(),
            'storages' => $variant->storagePrices->pluck('price', 'capacity_kwh')->all(),
            'inverter' => $variant->inverterPrice->price ?? 0,
        ];

        return response()->json($variant);
    }

    public function update(Request $request, Variant $variant)
    {
        $validated = $request->validate([
            'prices' => ['required', 'array'],
            'prices.panels' => ['required', 'array'],
            'prices.storages' => ['required', 'array'],
            'prices.inverter' => ['nullable', 'numeric', 'min:0'],
        ]);

    //    dd($validated);

        $prices = $validated['prices'];

//        DB::transaction(function() use ($variant, $prices) {
            // 1️⃣ Save panel prices
            foreach ($prices['panels'] as $panelModelId => $types) {
                foreach ($types as $installType => $price) {
                    PanelVariantPrice::updateOrCreate(
                        [
                            'variant_id' => $variant->id,
                            'panel_model_id' => $panelModelId,
                            'install_type' => $installType,
                        ],
                        [
                            'price_per_panel' => $price,
                        ]
                    );
                }
            }

            // 2️⃣ Save storage prices
            foreach ($prices['storages'] as $capacity => $price) {
                StorageVariantPrice::updateOrCreate(
                    [
                        'variant_id' => $variant->id,
                        'capacity_kwh' => $capacity,
                    ],
                    [
                        'price' => $price,
                    ]
                );
            }

            // 3️⃣ Save inverter price
            if (isset($prices['inverter'])) {

                InverterVariantPrice::updateOrCreate(
                    [
                        'variant_id' => $variant->id,
                    ],
                    [
                        'price' => $prices['inverter'],
                    ]
                );
            }
//        });

        return back()->with('success', 'Ceny zostały zapisane pomyślnie.');
    }
}
