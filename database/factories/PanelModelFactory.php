<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PanelModel>
 */
class PanelModelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement([
                'LONGi 450W',
                'JA Solar 400W',
                'Trina Solar 500W',
                'Canadian Solar 420W',
                'Jinko Solar 470W',
            ]),
            'manufacturer' => $this->faker->optional()->company(),
            'power_watt' => $this->faker->numberBetween(350, 600), // typical panel range
        ];
    }
}
