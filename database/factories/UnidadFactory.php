<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Unidad>
 */
class UnidadFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre_unm' => $this->faker->word(),
            'abreviatura_unm' => $this->faker->lexify('???'),
            'factor_conversion_unm' => $this->faker->randomFloat(3, 0.001, 1000),
        ];
    }
}
