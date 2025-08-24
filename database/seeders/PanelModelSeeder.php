<?php

namespace Database\Seeders;

use App\Models\PanelModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PanelModelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Generate random test data
        PanelModel::factory()->count(50)->create();
    }
}
