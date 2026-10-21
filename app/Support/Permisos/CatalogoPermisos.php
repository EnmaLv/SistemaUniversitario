<?php

namespace App\Support\Permisos;

use App\Models\Vista;
use Illuminate\Support\Collection;

class CatalogoPermisos
{
    public static function agrupado(): Collection
    {
        return Vista::with([
                'modulo',
                'permisos' => fn ($q) => $q->where('activo', true),
            ])
            ->where('activo', true)
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get()
            ->filter(fn ($vista) => $vista->permisos->isNotEmpty())
            ->groupBy(fn ($vista) => $vista->modulo->nombre ?? 'General')
            ->sortKeys();
    }
}
