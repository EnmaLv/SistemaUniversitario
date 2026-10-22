<?php

namespace App\Support\Sedes;

use App\Models\Sede;

class SedeCompradora
{
    public static function sede(): ?Sede
    {
        $principal = Sede::principal();

        return $principal && (int) $principal->status_id === Sede::STATUS_ACTIVO ? $principal : null;
    }

    public static function motivoParaNoComprar(): ?string
    {
        $principal = static::sede();

        if (! $principal) {
            return 'Todavía no hay una sede principal activa. Pide al administrador del sistema que indique cuál sede hace las compras para todas.';
        }

        $sedeActiva = SedeActiva::id();

        if ($sedeActiva === (int) $principal->id_sed || ($sedeActiva === null && SedeActiva::puedeElegir())) {
            return null;
        }

        if (SedeActiva::puedeElegir()) {
            return "Las compras se registran desde la sede principal ({$principal->nombre_sed}). Cambia la sede de trabajo a {$principal->nombre_sed} o a \"Todas\" para continuar.";
        }

        return "Solo el personal de la sede principal ({$principal->nombre_sed}) puede registrar compras. Tu sede recibe su parte automáticamente cuando se finaliza cada compra.";
    }

    public static function puedeComprar(): bool
    {
        return static::motivoParaNoComprar() === null;
    }
}
