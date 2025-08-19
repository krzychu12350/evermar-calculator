<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Variant;
use App\Models\PanelVariantPrice;
use App\Models\InverterVariantPrice;
use App\Models\StorageVariantPrice;
use App\Models\PanelModel;
use League\Csv\Reader;

class VariantPriceSeeder extends Seeder
{
    public function run(): void
    {
        $csv = Reader::createFromPath(database_path('seeders/cennik2.csv'), 'r');
        $csv->setHeaderOffset(0);
        $headers = $csv->getHeader();
        print_r($headers);

        // ✅ Create panel models only once
        $tongwei500 = PanelModel::firstOrCreate(
            ['name' => 'Tongwei 500W'],
            ['manufacturer' => 'Tongwei', 'power_watt' => 500]
        );

        $jasolar500 = PanelModel::firstOrCreate(
            ['name' => 'JASOLAR 500W FB'],
            ['manufacturer' => 'JA Solar', 'power_watt' => 500]
        );

        foreach ($csv as $row) {
            $panelCount = (int) $row['liczba paneli'];

            // Tworzenie wariantu
            $variant = Variant::create([
                'panel_count' => $panelCount,
            ]);

            // ===== PANELS =====
            if (!empty($row['Z MAGAZYNAMI i tongwei 500W'])) {
                PanelVariantPrice::create([
                    'variant_id' => $variant->id,
                    'panel_model_id' => $tongwei500->id,
                    'install_type' => 'with_storage',
                    'price_per_panel' => $row['Z MAGAZYNAMI i tongwei 500W'],
                ]);
            }

            if (!empty($row['STRING i tongwei 500W'])) {
                PanelVariantPrice::create([
                    'variant_id' => $variant->id,
                    'panel_model_id' => $tongwei500->id,
                    'install_type' => 'string',
                    'price_per_panel' => $row['STRING i tongwei 500W'],
                ]);
            }

            if (!empty($row['Z MAGAZYNAMI i JASOLAR 500W FB'])) {
                PanelVariantPrice::create([
                    'variant_id' => $variant->id,
                    'panel_model_id' => $jasolar500->id,
                    'install_type' => 'with_storage',
                    'price_per_panel' => $row['Z MAGAZYNAMI i JASOLAR 500W FB'],
                ]);
            }

            if (!empty($row['STRING i JASOLAR 500W FB'])) {
                PanelVariantPrice::create([
                    'variant_id' => $variant->id,
                    'panel_model_id' => $jasolar500->id,
                    'install_type' => 'string',
                    'price_per_panel' => $row['STRING i JASOLAR 500W FB'],
                ]);
            }

            // ===== INVERTER =====
            if (!empty($row['inwenter hybrydowy'])) {
                InverterVariantPrice::create([
                    'variant_id' => $variant->id,
                    'price' => $row['inwenter hybrydowy'],
                ]);
            }

            // ===== STORAGE =====
            if (!empty($row['magazyn 5 kw'])) {
                StorageVariantPrice::create([
                    'variant_id' => $variant->id,
                    'capacity_kwh' => 5,
                    'price' => $row['magazyn 5 kw'],
                ]);
            }

            if (!empty($row['magazyn 10 kw'])) {
                StorageVariantPrice::create([
                    'variant_id' => $variant->id,
                    'capacity_kwh' => 10,
                    'price' => $row['magazyn 10 kw'],
                ]);
            }

            if (!empty($row['magazyn 15 kw'])) {
                StorageVariantPrice::create([
                    'variant_id' => $variant->id,
                    'capacity_kwh' => 15,
                    'price' => $row['magazyn 15 kw'],
                ]);
            }
        }
    }
}
