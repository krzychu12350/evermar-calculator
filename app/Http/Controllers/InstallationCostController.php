<?php

namespace App\Http\Controllers;

use App\Http\Requests\InstallationCostRequest;
use App\Models\PanelVariantPrice;
use App\Models\InverterVariantPrice;
use App\Models\StorageVariantPrice;
use Inertia\Inertia;

class InstallationCostController extends Controller
{
    public function calculate(InstallationCostRequest $request)
    {
        $data = $request->validated();

        $panelCount = $data['panelCount'];
        $panelType = $data['panelType'];
        $installationType = $data['installationType'];
        $storageCapacity = $data['storageCapacity'] ?? 0;
        $hybridInverter = $data['hybridInverter'] ?? false;
        $groundInstallation = $data['groundInstallation'] ?? null;
        $additionalInverterKW = $data['additionalInverterKW'] ?? 0;
        $backup = $data['backup'] ?? false;
        $proJoy = $data['proJoy'] ?? false;
        $extraStorage = $data['extraStorage'] ?? 0;

        $total = 0;

        // 1️⃣ Koszt paneli
        $panelPrice = PanelVariantPrice::where('model', $this->panelName($panelType))
            ->where('install_type', $installationType === 'string' ? 'string' : 'with_storage')
            ->first()
            ->price_per_panel ?? 0;

        $total += $panelCount * $panelPrice;

        // 2️⃣ Koszt montażu grunt/dach
        if ($groundInstallation) {
            $kw = round($panelCount / 2, 2); // 2 panele = 1kW
            $groundCosts = [
                'grunt' => 300,
                'ekierka' => 300,
                'gont' => 300,
                'dachowka' => 250,
                'rąbek' => 250,
            ];
            $total += $kw * ($groundCosts[$groundInstallation] ?? 0);
        }

        // 3️⃣ Dodatkowy inwerter
        $total += $additionalInverterKW * 200;

        // 4️⃣ Hybrydowy inwerter
        if ($hybridInverter) {
            $total += InverterVariantPrice::first()->price ?? 0;
        }

        // 5️⃣ Magazyn
        if ($installationType === 'storage' && $storageCapacity > 0) {
            $storagePrice = StorageVariantPrice::where('capacity_kwh', $storageCapacity)->first()->price ?? 0;
            $total += $storagePrice;

            // Dopłata za dodatkową pojemność >15kWh
            if ($storageCapacity + $extraStorage > 15) {
                $extraKw = max(0, $storageCapacity + $extraStorage - 15);
                $total += 4000 * ceil($extraKw / 5); // 4000 zł za każde dodatkowe 5kWh
            }
        }

        // 6️⃣ Backup (tylko dla magazynów, opcjonalnie dla stringów)
        if ($backup && ($installationType === 'storage' || $installationType === 'string')) {
            $total += 2000;
        }

        // 7️⃣ Pro Joy
        if ($proJoy) {
            $total += 1250;
        }

        return Inertia::render('Home', [
            'total' => $total,
        ]);
    }

    private function panelName($type)
    {
        return $type === 'tongwei' ? 'Tongwei 500W' : 'JASOLAR 500W FB';
    }
}
