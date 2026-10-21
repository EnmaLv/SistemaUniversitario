<?php

namespace Tests\Unit;

use App\Livewire\RegistroComida;
use PHPUnit\Framework\TestCase;

class RegistroComidaCantidadTest extends TestCase
{
    public function test_uses_recipe_quantity_in_grams(): void
    {
        $ingrediente = (object) [
            'cantidad_convertida_rci' => 40,
            'cantidad_porcion_rci' => 40,
        ];

        $this->assertSame(40.0, $this->resolverCantidad($ingrediente));
    }

    public function test_converts_portion_quantity_when_grams_are_missing(): void
    {
        $ingrediente = (object) [
            'cantidad_porcion_rci' => 2,
            'unidad' => (object) ['factor_conversion_unm' => 500],
        ];

        $this->assertSame(1000.0, $this->resolverCantidad($ingrediente));
    }

    private function resolverCantidad(object $ingrediente): float
    {
        $method = new \ReflectionMethod(RegistroComida::class, 'resolverCantidadPorUnidad');

        return $method->invoke(new RegistroComida, $ingrediente);
    }
}
