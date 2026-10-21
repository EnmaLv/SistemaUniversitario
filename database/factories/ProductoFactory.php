<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Producto>
 */
class ProductoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cap_id' => $this->faker->numberBetween(1, 10),
            'codigo_pdt' => $this->faker->unique()->numerify('#####'),
            'nombre_pdt' => $this->faker->word(),
            'descripcion_pdt' => $this->faker->sentence(),
            'imagen_pdt' => $this->faker->imageUrl(640, 480, 'products'),
            'precio_compra_pdt' => $this->faker->randomFloat(2, 10, 100),
            'stock_minimo_pdt' => $this->faker->numberBetween(1, 5),
            'stock_maximo_pdt' => $this->faker->numberBetween(10, 20),
            'unm_id' => $this->faker->numberBetween(1, 4),
            'status_id' => $this->faker->boolean(80) ? 1 : 2,
        ];
    }
}
