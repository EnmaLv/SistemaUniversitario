<?php

namespace App\Support\Permisos;

use Illuminate\Support\Facades\Cache;

class VersionPermisos
{
    private const CLAVE = 'permisos.version_global';

    public static function actual(): int
    {
        return (int) Cache::get(self::CLAVE, 1);
    }

    public static function invalidar(): void
    {
        Cache::forever(self::CLAVE, static::actual() + 1);
        MapaRutas::limpiar();
    }
}
