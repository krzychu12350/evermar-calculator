<?php

namespace App\Http\Controllers;

use App\Models\PanelModel;
use App\Models\Variant;
use App\Models\PanelVariantPrice;
use App\Models\InverterVariantPrice;
use App\Models\StorageVariantPrice;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class VariantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Variant::query()->with([
            'panelPrices.panelModel',
            'inverterPrice',
            'storagePrices',
        ]);

        // Filters
        if ($request->filled('install_type')) {
            $installTypes = is_array($request->install_type)
                ? $request->install_type
                : [$request->install_type];
            $query->whereHas('panelPrices', function ($q) use ($installTypes) {
                $q->whereIn('install_type', $installTypes);
            });
        }

        if ($request->filled('panel_models')) {
            $panelModelIds = is_array($request->panel_models)
                ? $request->panel_models
                : [$request->panel_models];
            $query->whereHas('panelPrices', function ($q) use ($panelModelIds) {
                $q->whereIn('panel_model_id', array_map('intval', $panelModelIds));
            });
        }

        if ($request->filled('storage_variants')) {
            $storageIds = is_array($request->storage_variants)
                ? $request->storage_variants
                : [$request->storage_variants];
            $query->whereHas('storagePrices', function ($q) use ($storageIds) {
                $q->whereIn('capacity_kwh', array_map('intval', $storageIds));
            });
        }

        // Panel count range filter
        $panelCountFrom = $request->input('panel_count_from');
        $panelCountTo   = $request->input('panel_count_to');
        if ($panelCountFrom !== null) {
            $query->where('panel_count', '>=', (int)$panelCountFrom);
        }
        if ($panelCountTo !== null) {
            $query->where('panel_count', '<=', (int)$panelCountTo);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'panel_count');
        $sortDirection = $request->get('sort_direction', 'asc');
        $query->orderBy($sortBy, $sortDirection);

        // Pagination
        $perPage = $request->get('per_page', 10);
        $variants = $query->paginate($perPage)->withQueryString();

        // Transform data
        $variants->getCollection()->transform(function ($variant) {
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

            unset($variant->panelPrices, $variant->storagePrices, $variant->inverterPrice);

            return $variant;
        });

        // Filters for frontend
        $filters = [
            'install_types' => [
                ['label' => 'Standard', 'value' => 'string', 'checked' => false],
                ['label' => 'With Storage', 'value' => 'with_storage', 'checked' => false],
            ],
            'panel_models' => PanelModel::select('id', 'name')->get()->map(fn($m) => [
                'label' => $m->name,
                'value' => $m->id,
                'checked' => false,
            ])->toArray(),
            'storage_variants' => StorageVariantPrice::select('capacity_kwh as value')->distinct()->get()->map(fn($s) => [
                'label' => "{$s->value} kWh",
                'value' => $s->value,
                'checked' => false,
            ])->toArray(),
        ];

        return Inertia::render('Variants/Index', [
            'variants' => $variants,
            'filters' => $filters,
        ]);
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'panel_count' => ['required', 'integer', 'min:1'],
            'panel_models' => ['required', 'array', 'min:1'],
            'panel_models.*' => ['required', 'exists:panel_models,id'],
            'install_types' => ['required', 'array', 'min:1'],
            'install_types.*' => ['required', 'in:string,with_storage'],
            'storage_variants' => ['nullable', 'array'],
            'storage_variants.*' => ['required', 'integer', 'min:0'],
        ]);

        // Create variant
        $variant = Variant::create([
            'panel_count' => $validated['panel_count'],
        ]);

        // Generate panel prices for each combination of panel_model and install_type
        foreach ($validated['panel_models'] as $panelModelId) {
            foreach ($validated['install_types'] as $installType) {
                PanelVariantPrice::create([
                    'variant_id' => $variant->id,
                    'panel_model_id' => $panelModelId,
                    'install_type' => $installType,
                    'price_per_panel' => 0, // default price, can be updated later
                ]);
            }
        }

        // Generate storage prices if any
        if (!empty($validated['storage_variants'])) {
            foreach ($validated['storage_variants'] as $capacity) {
                StorageVariantPrice::create([
                    'variant_id' => $variant->id,
                    'capacity_kwh' => $capacity,
                    'price' => 0, // default price
                ]);
            }
        }

//        return redirect()->back()->with('success', 'Wariant został dodany.');
        return to_route('prices.index', ['selected_variant_id' => $variant->id]);
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Variant $variant)
    {
        $variant->load(['panelPrices.panelModel', 'storagePrices', 'inverterPrice']);
        return response()->json($variant); // Used by Inertia modal to populate form
    }


    /**
     * Update the specified resource in storage.
     */

    public function update(Request $request, Variant $variant)
    {
        $request->validate([
            'panel_models' => 'required|array',
            'panel_models.*' => 'required|exists:panel_models,id',
            'install_types' => 'required|array',
            'install_types.*' => 'required|in:string,with_storage',
            'storage_variants' => 'required|array',
            'storage_variants.*' => 'required|integer|min:0',
        ]);

        DB::transaction(function () use ($request, $variant) {

            // --- PANEL PRICES ---

            // Build a set of keys from request: panel_model_id + install_type
            $requestedPanelKeys = [];
            foreach ($request->panel_models as $panelId) {
                foreach ($request->install_types as $installType) {
                    $requestedPanelKeys[] = ['panel_model_id' => $panelId, 'install_type' => $installType];
                }
            }

            // Delete panelPrices not in request
            $variant->panelPrices()
                ->whereNot(function ($query) use ($requestedPanelKeys) {
                    foreach ($requestedPanelKeys as $key) {
                        $query->orWhere(function ($q) use ($key) {
                            $q->where('panel_model_id', $key['panel_model_id'])
                                ->where('install_type', $key['install_type']);
                        });
                    }
                })->delete();

            // Update or create panelPrices from request, preserving existing prices
            foreach ($requestedPanelKeys as $key) {
                $panelPrice = $variant->panelPrices()
                    ->where('panel_model_id', $key['panel_model_id'])
                    ->where('install_type', $key['install_type'])
                    ->first();

                if (!$panelPrice) {
                    // Create new record with price 0
                    $variant->panelPrices()->create([
                        'panel_model_id' => $key['panel_model_id'],
                        'install_type' => $key['install_type'],
                        'price_per_panel' => 0,
                    ]);
                }
                // Existing records remain unchanged
            }

            // --- STORAGE PRICES ---

            // Delete storagePrices not in request
            $variant->storagePrices()
                ->whereNotIn('capacity_kwh', $request->storage_variants)
                ->delete();

            // Update or create storagePrices, preserving existing prices
            foreach ($request->storage_variants as $capacity) {
                $storagePrice = $variant->storagePrices()
                    ->where('capacity_kwh', $capacity)
                    ->first();

                if (!$storagePrice) {
                    $variant->storagePrices()->create([
                        'capacity_kwh' => $capacity,
                        'price' => 0,
                    ]);
                }
                // Existing records remain unchanged
            }
        });

       // return redirect()->back()->with('success', 'Wariant został zaktualizowany.');

        return to_route('prices.index', ['selected_variant_id' => $variant->id]);
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Variant $variant)
    {
        $variant->delete();
        return redirect()->back()->with('success', 'Wariant został usunięty.');
    }

    /**
     * Return available panel models + restricted (unused) panel_count options.
     */
    public function availableOptions()
    {
        // Get all panel models
        $panelModels = PanelModel::select('id', 'name', 'manufacturer', 'power_watt')->get();

        // Restrict panel_count -> exclude already used counts
        $usedCounts = Variant::pluck('panel_count')->toArray();

        // Example: allow 1–100 panels, excluding already taken
        $allCounts = range(1, 1000);
        $availableCounts = array_values(array_diff($allCounts, $usedCounts));

        // Map to { label, value } objects
        $availableCounts = array_map(fn($n) => [
            'label' => (string) $n,
            'value' => $n,
        ], $availableCounts);

        return response()->json([
            'panel_models' => $panelModels,
            'available_counts' => $availableCounts,
            'install_types' => [
                ['label' => 'Standard', 'value' => 'string'],
                ['label' => 'With Storage', 'value' => 'with_storage'],
            ],
            'storage_variants' => collect([5, 10, 15])->map(fn($v) => [
                'label' => "{$v} kWh",
                'value' => $v,
            ]),
        ]);
    }
}
