<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Variant;
use App\Models\PanelVariantPrice;
use App\Models\InverterVariantPrice;
use App\Models\StorageVariantPrice;
use League\Csv\Reader;

class VariantPriceSeeder extends Seeder
{
    public function run(): void
    {
        $csv = Reader::createFromPath(database_path('seeders/cennik2.csv'), 'r');
        $csv->setHeaderOffset(0);
        $headers = $csv->getHeader(); // get header row
        print_r($headers);

        foreach ($csv as $row) {
            $panelCount = (int) $row['liczba paneli'];

            // Tworzenie wariantu
            $variant = Variant::create([
                'panel_count' => $panelCount,
            ]);

            // Ceny paneli
            if (!empty($row['Z MAGAZYNAMI i tongwei 500W'])) {
                PanelVariantPrice::create([
                    'variant_id' => $variant->id,
                    'model' => 'Tongwei 500W',
                    'install_type' => 'with_storage',
                    'price_per_panel' => $row['Z MAGAZYNAMI i tongwei 500W'],
                ]);
            }

            if (!empty($row['STRING i tongwei 500W'])) {
                PanelVariantPrice::create([
                    'variant_id' => $variant->id,
                    'model' => 'Tongwei 500W',
                    'install_type' => 'string',
                    'price_per_panel' => $row['STRING i tongwei 500W'],
                ]);
            }

            if (!empty($row['Z MAGAZYNAMI i JASOLAR 500W FB'])) {
                PanelVariantPrice::create([
                    'variant_id' => $variant->id,
                    'model' => 'JASOLAR 500W FB',
                    'install_type' => 'with_storage',
                    'price_per_panel' => $row['Z MAGAZYNAMI i JASOLAR 500W FB'],
                ]);
            }

            if (!empty($row['STRING i JASOLAR 500W FB'])) {
                PanelVariantPrice::create([
                    'variant_id' => $variant->id,
                    'model' => 'JASOLAR 500W FB',
                    'install_type' => 'string',
                    'price_per_panel' => $row['STRING i JASOLAR 500W FB'],
                ]);
            }

            // Cena inwertera
            if (!empty($row['inwenter hybrydowy'])) {
                InverterVariantPrice::create([
                    'variant_id' => $variant->id,
                    'price' => $row['inwenter hybrydowy'],
                ]);
            }

            // Ceny magazynów
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
