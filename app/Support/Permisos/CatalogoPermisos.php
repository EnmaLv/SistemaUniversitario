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
                'permisos' => fn ($q) => $q->where('status_id', 1),
            ])
            ->where('status_id', 1)
            ->orderBy('orden_vis')
            ->orderBy('nombre_vis')
            ->get()
            ->filter(fn ($vista) => $vista->permisos->isNotEmpty())
            ->groupBy(fn ($vista) => $vista->modulo->nombre_mod ?? 'General')
            ->sortKeys();
    }
}
