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
        $invoiceItems = [];

        // 1️⃣ Koszt paneli
        $panelVariantPrice = PanelVariantPrice::where('model', $this->panelName($panelType))
            ->where('install_type', $installationType === 'string' ? 'string' : 'with_storage')
            ->whereRelation('variant', 'panel_count', $panelCount)
            ->first();

        $panelPrice = $panelVariantPrice->price_per_panel ?? 0;
        $panelVariant = $panelVariantPrice->variant ?? null;
        $panelTotal = $panelCount * $panelPrice;
        $total += $panelTotal;

        $invoiceItems[] = [
            'name' => "Panele $panelVariantPrice->model",
            'quantity' => $panelCount,
            'unit_price' => $panelPrice,
            'total' => $panelTotal,
        ];

        // 2️⃣ Koszt montażu grunt/dach
        if ($groundInstallation) {
            $kw = round($panelCount / 2, 2); // 2 panele = 1 kW
            $groundCosts = [
                'grunt' => 300,
                'ekierka' => 300,
                'gont' => 300,
                'dachowka' => 250,
                'rąbek' => 250,
            ];
            $groundTotal = $kw * ($groundCosts[$groundInstallation] ?? 0);
            $total += $groundTotal;

            $invoiceItems[] = [
                'name' => "Montaż ($groundInstallation)",
                'quantity' => $kw . " kW",
                'unit_price' => $groundCosts[$groundInstallation] ?? 0,
                'total' => $groundTotal,
            ];
        }

        // 3️⃣ Dodatkowy inwerter
        if ($installationType === 'storage' || ($installationType === 'string' && ($data['inverterSelected'] ?? false))) {
            $additionalInverterTotal = $additionalInverterKW * 200;
            $total += $additionalInverterTotal;

            if ($additionalInverterKW > 0) {
                $invoiceItems[] = [
                    'name' => "Dodatkowa moc inwertera",
                    'quantity' => $additionalInverterKW . " kW",
                    'unit_price' => 200,
                    'total' => $additionalInverterTotal,
                ];
            }
        }

        // 4️⃣ Hybrydowy inwerter
        if ($hybridInverter || $installationType === 'storage') {
            $inverterPrice = (int) InverterVariantPrice::first()->price ?? 0;
            $total += $inverterPrice;

            $invoiceItems[] = [
                'name' => "Hybrydowy inwerter",
                'quantity' => 1,
                'unit_price' => $inverterPrice,
                'total' => $inverterPrice,
            ];
        }

        // 5️⃣ Magazyn
        if ($installationType === 'storage' && $storageCapacity > 0) {
            $storageVariant = StorageVariantPrice::whereRelation('variant', 'panel_count', $panelCount)
                ->where('capacity_kwh', '<=', $storageCapacity)
                ->orderByDesc('capacity_kwh')
                ->first();

            $baseCapacity = $storageVariant->capacity_kwh ?? 0;
            $storagePrice = $storageVariant->price ?? 0;
            $total += $storagePrice;

            $invoiceItems[] = [
                'name' => "Magazyn $storageCapacity kWh",
                'quantity' => 1,
                'unit_price' => $storagePrice,
                'total' => $storagePrice,
            ];

            // Dopłata za nadwyżkę powyżej największego wariantu
            $extraKw = max(0, $storageCapacity - $baseCapacity);
            if ($extraKw > 0) {
                $extraStorageTotal = 4000 * ceil($extraKw / 5);
                $total += $extraStorageTotal;

                $invoiceItems[] = [
                    'name' => "Dodatkowa pojemność magazynu",
                    'quantity' => $extraKw . " kWh",
                    'unit_price' => 4000,
                    'total' => $extraStorageTotal,
                ];
            }
        }

        // 6️⃣ Backup
        if ($backup && ($installationType === 'storage' || $installationType === 'string')) {
            $total += 2000;
            $invoiceItems[] = [
                'name' => "Backup",
                'quantity' => 1,
                'unit_price' => 2000,
                'total' => 2000,
            ];
        }

        // 7️⃣ Pro Joy
        if ($proJoy) {
            $total += 1250;
            $invoiceItems[] = [
                'name' => "Pro Joy",
                'quantity' => 1,
                'unit_price' => 1250,
                'total' => 1250,
            ];
        }

        return Inertia::render('Home', [
            'total' => $total,
            'invoiceItems' => $invoiceItems, // return detailed invoice
        ]);
    }

    private function panelName($type)
    {
        return $type === 'tongwei' ? 'Tongwei 500W' : 'JASOLAR 500W FB';
    }
}
