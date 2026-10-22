<?php

namespace App\Support\Auditoria;

use App\Support\Permisos\MapaRutas;
use App\Support\Permisos\ResolutorAccion;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FiltroPeticiones
{
    protected static ?array $modulosPorVista = null;

    public static function esRuido(Request $request): bool
    {
        if (in_array(strtoupper($request->method()), config('seguridad.ruido.metodos', []), true)) {
            return true;
        }

        $nombre = $request->route()?->getName();
        if ($nombre && Str::is(config('seguridad.ruido.rutas', []), $nombre)) {
            return true;
        }

        return Str::is(config('seguridad.ruido.paths', []), ltrim($request->path(), '/'));
    }

    public static function moduloDe(?Request $request): string
    {
        if (! $request) {
            return 'sistema';
        }

        $sesion = $request->hasSession() ? $request->session()->get('modulo_activo') : null;
        $candidatos = static::modulosInferidos($request);

        if ($sesion && in_array($sesion, $candidatos, true)) {
            return $sesion;
        }

        return $candidatos[0] ?? $sesion ?? 'general';
    }

    public static function modulosInferidos(Request $request): array
    {
        $ruta = $request->route();
        if (! $ruta) {
            return [];
        }

        try {
            $vistas = MapaRutas::vistasDeRuta($ruta->getName(), $request->path());
        } catch (\Throwable $e) {
            return [];
        }

        $mapa = static::modulosPorVista();
        $modulos = [];

        foreach ($vistas as $vista) {
            foreach ($mapa[$vista] ?? [] as $modulo) {
                $modulos[] = $modulo;
            }
        }

        return array_values(array_unique($modulos));
    }

    public static function vistasYAccion(Request $request): array
    {
        $ruta = $request->route();

        try {
            $vistas = $ruta ? MapaRutas::vistasDeRuta($ruta->getName(), $request->path()) : [];
        } catch (\Throwable $e) {
            $vistas = [];
        }

        return [
            $vistas,
            ResolutorAccion::desdeRuta($ruta?->getName(), $request->method()),
        ];
    }

    protected static function modulosPorVista(): array
    {
        if (static::$modulosPorVista !== null) {
            return static::$modulosPorVista;
        }

        $grupoModulo = config('permisos.grupo_modulo', []);
        $mapa = [];

        foreach (config('menu_permissions', []) as $grupo => $definicion) {
            $modulo = $grupoModulo[$grupo] ?? null;
            foreach (array_keys($definicion['items'] ?? []) as $vista) {
                $mapa[$vista][] = $modulo ?? 'general';
            }
        }

        return static::$modulosPorVista = $mapa;
    }
}
