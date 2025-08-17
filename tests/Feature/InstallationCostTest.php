<?php

namespace Tests\Feature;

use App\Models\PanelVariantPrice;
use App\Models\InverterVariantPrice;
use App\Models\StorageVariantPrice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstallationCostTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_calculates_string_installation_with_panels_only()
    {
        // Arrange: seed panel prices
        PanelVariantPrice::create([
            'model' => 'Tongwei 500W',
            'install_type' => 'string',
            'price_per_panel' => 500,
        ]);

        PanelVariantPrice::create([
            'model' => 'JASOLAR 500W FB',
            'install_type' => 'string',
            'price_per_panel' => 600,
        ]);

        // Act
        $response = $this->post(route('installation.calculate'), [
            'panelCount' => 8,
            'panelType' => 'tongwei',
            'installationType' => 'string',
        ]);

        // Assert
        $expected = 8 * 500; // 8 panels * 500
        $response->assertInertia(fn ($page) =>
        $page->where('total', $expected)
        );
    }

    /** @test */
    public function it_calculates_ground_installation_costs()
    {
        PanelVariantPrice::create([
            'model' => 'Tongwei 500W',
            'install_type' => 'string',
            'price_per_panel' => 500,
        ]);

        $response = $this->post(route('installation.calculate'), [
            'panelCount' => 8, // 8 panels = 4kW
            'panelType' => 'tongwei',
            'installationType' => 'string',
            'groundInstallation' => 'grunt',
        ]);

        $base = 8 * 500;
        $ground = 4 * 300; // 4 kW * 300
        $expected = $base + $ground;

        $response->assertInertia(fn ($page) =>
        $page->where('total', $expected)
        );
    }

    /** @test */
    public function it_calculates_storage_with_extra_capacity_and_backup()
    {
        PanelVariantPrice::create([
            'model' => 'JASOLAR 500W FB',
            'install_type' => 'with_storage',
            'price_per_panel' => 600,
        ]);

        InverterVariantPrice::create([
            'price' => 7000,
        ]);

        StorageVariantPrice::create([
            'capacity_kwh' => 15,
            'price' => 20000,
        ]);

        $response = $this->post(route('installation.calculate'), [
            'panelCount' => 10,
            'panelType' => 'jasolar',
            'installationType' => 'storage',
            'storageCapacity' => 15,
            'extraStorage' => 5, // triggers extra 4000 zł
            'hybridInverter' => true,
            'backup' => true,
            'proJoy' => true,
        ]);

        $basePanels = 10 * 600;
        $inverter = 7000;
        $storage = 20000;
        $extra = 4000; // extra 5kWh over 15
        $backup = 2000;
        $proJoy = 1250;

        $expected = $basePanels + $inverter + $storage + $extra + $backup + $proJoy;

        $response->assertInertia(fn ($page) =>
        $page->where('total', $expected)
        );
    }
}
