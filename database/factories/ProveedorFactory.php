<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Proveedor>
 */
class ProveedorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'empresa_prv' => "Hiper Sol",
            'nombre_contacto_prv' => "Orianna",
            'direccion_prv' => "Prados del Sol Acarigua",
            'telefono_prv' => "0424-5345568",
            'email_prv' => "oriana@gmail.com",
            'status_id' => 1,
        ];
    }
}
