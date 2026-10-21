<?php

namespace App\Support\Permisos;

use App\Models\Vista;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class MapaRutas
{
    protected static ?array $patronesCache = null;

    public static function patrones(): array
    {
        if (static::$patronesCache !== null) {
            return static::$patronesCache;
        }

        $mapa = [];
        foreach (config('menu_routes', []) as $vista => $patrones) {
            $mapa[$vista] = (array) $patrones;
        }

        if (Schema::hasTable('vista')) {
            foreach (Vista::where('activo', true)->pluck('key') as $clave) {
                if (! isset($mapa[$clave])) {
                    $mapa[$clave] = [$clave];
                }
            }
        }

        return static::$patronesCache = $mapa;
    }

    public static function patronesDe(string $vista): array
    {
        return static::patrones()[$vista] ?? [$vista];
    }

    public static function vistasDeRuta(?string $nombreRuta, string $path): array
    {
        $path = ltrim($path, '/');
        $vistas = [];

        foreach (static::patrones() as $vista => $patrones) {
            foreach ($patrones as $patron) {
                if (Str::is($patron, $path) || ($nombreRuta && Str::is($patron, $nombreRuta))) {
                    $vistas[] = $vista;
                    break;
                }
            }
        }

        return $vistas;
    }

    public static function esLibre(?string $nombreRuta, string $path): bool
    {
        $path = ltrim($path, '/');

        return in_array($path, config('permisos.rutas_libres.paths', []), true)
            || ($nombreRuta && in_array($nombreRuta, config('permisos.rutas_libres.nombres', []), true));
    }

    public static function limpiar(): void
    {
        static::$patronesCache = null;
    }
}
