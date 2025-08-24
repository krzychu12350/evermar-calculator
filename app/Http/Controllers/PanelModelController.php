<?php

namespace App\Http\Controllers;

use App\Models\PanelModel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PanelModelController extends Controller
{
    /**
     * Wyświetl listę paneli.
     */
    public function index(Request $request)
    {

        $query = PanelModel::query();

        // ✅ Filtrowanie po producencie
        if ($request->has('manufacturer') && is_array($request->manufacturer)) {
            $query->whereIn('manufacturer', $request->manufacturer);
        }

        // ✅ Filtrowanie po zakresie mocy
//        if ($request->filled('power_range')) {
//            switch ($request->power_range) {
//                case 'under400':
//                    $query->where('power_watt', '<=', 400);
//                    break;
//                case '400to500':
//                    $query->whereBetween('power_watt', [401, 500]);
//                    break;
//                case 'above500':
//                    $query->where('power_watt', '>', 500);
//                    break;
//            }
//        }

        if ($request->filled('power_range')) {
            $min = $request->input('power_range.from', 100);
            $max = $request->input('power_range.to', 1000);
            $query->whereBetween('power_watt', [intval($min), intval($max)]);
        }
     //  dd($request->input('power_range.from', 100));



        // ✅ Sortowanie
        if ($request->filled('sortBy')) {
            $direction = $request->get('sortDirection', 'asc');
            $query->orderBy($request->sortBy, $direction);
        }

        // Liczba rekordów na stronę z requestu
        $itemsPerPage = $request->input('per_page', 10);

        $panels = $query->paginate($itemsPerPage)->withQueryString();

        // ✅ Producentów można użyć jako dane do filtrów
        $manufacturers = PanelModel::select('manufacturer')
            ->whereNotNull('manufacturer')
            ->distinct()
            ->pluck('manufacturer')
            ->map(fn ($m) => [
                'label' => $m,
                'value' => $m,
                'checked' => false,
            ])
            ->values();

        return Inertia::render('Panels/Index', [
            'panels' => $panels,
            'filters' => [
                'manufacturers' => $manufacturers,
            ],
        ]);
    }

    /**
     * Zapisz nowy panel.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'manufacturer' => ['string', 'max:255'],
            'power_watt' => ['required', 'integer', 'min:1'],
        ]);

        PanelModel::create($validated);

        return redirect()->route('panels.index')->with('success', 'Panel dodany poprawnie.');
    }

    /**
     * Zaktualizuj istniejący panel.
     */
    public function update(Request $request, PanelModel $panel)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'manufacturer' => ['required', 'string', 'max:255'],
            'power_watt' => ['required', 'integer', 'min:1'],
        ]);

        $panel->update($validated);

        return redirect()->route('panels.index')->with('success', 'Panel zaktualizowany.');
    }

    /**
     * Usuń panel.
     */
    public function destroy(PanelModel $panel)
    {
        $panel->delete();

        return redirect()->route('panels.index')->with('success', 'Panel usunięty.');
    }
}
