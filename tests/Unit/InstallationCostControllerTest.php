<?php

namespace Tests\Unit;

use App\Http\Controllers\InstallationCostController;
use App\Models\PanelVariantPrice;
use App\Models\InverterVariantPrice;
use App\Models\StorageVariantPrice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class InstallationCostControllerTest extends TestCase
{
    use RefreshDatabase;

    private function callCalculate(array $data)
    {
        $controller = new InstallationCostController();

        $request = Request::create('/fake', 'POST', $data);

        // Bypass validation, call method directly
        $response = $controller->calculate($request);

        // Inertia response has props, so we extract them
        return $response->getData()->props->total;
    }

    /** @test */
    public function it_calculates_string_installation_panels_only()
    {
        PanelVariantPrice::create([
            'model' => 'Tongwei 500W',
            'install_type' => 'string',
            'price_per_panel' => 500,
        ]);

        $total = $this->callCalculate([
            'panelCount' => 8,
            'panelType' => 'tongwei',
            'installationType' => 'string',
        ]);

        $this->assertEquals(8 * 500, $total);
    }

    /** @test */
    public function it_adds_ground_installation_costs()
    {
        PanelVariantPrice::create([
            'model' => 'Tongwei 500W',
            'install_type' => 'string',
            'price_per_panel' => 500,
        ]);

        $total = $this->callCalculate([
            'panelCount' => 8, // 8 panels = 4 kW
            'panelType' => 'tongwei',
            'installationType' => 'string',
            'groundInstallation' => 'grunt',
        ]);

        $expected = (8 * 500) + (4 * 300);
        $this->assertEquals($expected, $total);
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

        $total = $this->callCalculate([
            'panelCount' => 10,
            'panelType' => 'jasolar',
            'installationType' => 'storage',
            'storageCapacity' => 15,
            'extraStorage' => 5,
            'hybridInverter' => true,
            'backup' => true,
            'proJoy' => true,
        ]);

        $expected =
            (10 * 600) +  // panels
            7000 +        // inverter
            20000 +       // storage
            4000 +        // extra storage (5kWh > 15kWh)
            2000 +        // backup
            1250;         // proJoy

        $this->assertEquals($expected, $total);
    }
}
