<?php

namespace App\Exceptions\Salud;

use RuntimeException;

/**
 * Error de negocio controlado durante la dispensación.
 * Su mensaje es seguro para mostrarse al usuario.
 */
class StockInsuficienteException extends RuntimeException
{
    public static function sinStock(string $producto, int $sedeId): self
    {
        return new self("No hay stock en tu sede (ID: {$sedeId}) para: {$producto}.");
    }

    public static function faltante(string $producto, float $faltante): self
    {
        return new self("Stock insuficiente en tu sede para: {$producto}. Faltaron {$faltante} unidades.");
    }
}
