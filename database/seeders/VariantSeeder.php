<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Variant;

class VariantSeeder extends Seeder
{
    public function run(): void
    {
        // Create variants from 8 to 100 panels
        for ($i = 8; $i <= 100; $i++) {
            Variant::create([
                'panel_count' => $i,
            ]);
        }
    }
}
