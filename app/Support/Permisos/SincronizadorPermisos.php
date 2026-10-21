<?php

namespace App\Support\Permisos;

use App\Models\Modulo;
use App\Models\Permiso;
use App\Models\Rol;
use App\Models\Vista;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class SincronizadorPermisos
{
    public static function sincronizar(array $clavesExtra = []): array
    {
        $catalogo     = static::catalogoVistas($clavesExtra);
        $rutas        = static::rutasRegistradas();
        $creadas      = 0;
        $permisosNew  = 0;

        DB::transaction(function () use ($catalogo, $rutas, &$creadas, &$permisosNew) {
            foreach ($catalogo as $clave => $datos) {
                $vista = Vista::firstOrNew(['key' => $clave]);

                if (! $vista->exists) {
                    $creadas++;
                }

                $vista->nombre = $datos['nombre'];
                $vista->orden  = $datos['orden'];
                if ($datos['modulo_id'] !== null || ! $vista->exists) {
                    $vista->modulo_id = $datos['modulo_id'];
                }
                $vista->save();

                foreach (static::accionesDeVista($clave, $rutas) as $accion) {
                    $permiso = Permiso::firstOrNew([
                        'vista_id' => $vista->id,
                        'accion'   => $accion,
                    ]);

                    if (! $permiso->exists) {
                        $permisosNew++;
                    }

                    $permiso->key       = "{$clave}.{$accion}";
                    $permiso->nombre    = ResolutorAccion::etiqueta($accion) . ' · ' . $vista->nombre;
                    $permiso->modulo_id = $vista->modulo_id;
                    $permiso->save();
                }
            }

            static::otorgarTodoASuperAdmins();
        });

        VersionPermisos::invalidar();

        return ['vistas_nuevas' => $creadas, 'permisos_nuevos' => $permisosNew];
    }

    public static function otorgarTodoASuperAdmins(): void
    {
        $todos = Permiso::pluck('id')->all();
        if (empty($todos)) {
            return;
        }

        $superAdmins = array_map('mb_strtolower', config('permisos.roles_superadmin', []));

        Rol::all()
            ->filter(fn ($rol) => in_array(mb_strtolower($rol->nombre ?? ''), $superAdmins, true)
                || in_array(mb_strtolower($rol->slug ?? ''), $superAdmins, true))
            ->each(fn ($rol) => $rol->permisos()->syncWithoutDetaching($todos));
    }

    protected static function catalogoVistas(array $clavesExtra): array
    {
        $grupoModulo = config('permisos.grupo_modulo', []);
        $modulos     = Modulo::pluck('id', 'key')->all();
        $obsoletas   = config('permisos.claves_obsoletas', []);
        $vistas      = [];
        $orden       = 0;

        foreach (config('menu_permissions', []) as $grupo => $definicion) {
            $moduloKey = $grupoModulo[$grupo] ?? null;
            foreach ($definicion['items'] ?? [] as $clave => $etiqueta) {
                if (isset($vistas[$clave])) {
                    continue;
                }
                $vistas[$clave] = [
                    'nombre'    => $etiqueta,
                    'modulo_id' => $moduloKey ? ($modulos[$moduloKey] ?? null) : null,
                    'orden'     => $orden++,
                ];
            }
        }

        foreach (array_keys(config('menu_routes', [])) as $clave) {
            if (! isset($vistas[$clave])) {
                $vistas[$clave] = [
                    'nombre'    => Str::headline($clave),
                    'modulo_id' => null,
                    'orden'     => $orden++,
                ];
            }
        }

        foreach ($clavesExtra as $clave) {
            if (! is_string($clave) || $clave === '' || isset($vistas[$clave]) || in_array($clave, $obsoletas, true)) {
                continue;
            }
            $vistas[$clave] = [
                'nombre'    => $clave,
                'modulo_id' => null,
                'orden'     => $orden++,
            ];
        }

        return $vistas;
    }

    protected static function rutasRegistradas(): Collection
    {
        return collect(Route::getRoutes()->getRoutes())
            ->map(fn ($ruta) => [
                'nombre' => $ruta->getName(),
                'uri'    => $ruta->uri(),
                'metodo' => collect($ruta->methods())->first(fn ($m) => $m !== 'HEAD') ?? 'GET',
            ])
            ->filter(fn ($ruta) => ! empty($ruta['nombre']))
            ->values();
    }

    protected static function accionesDeVista(string $clave, Collection $rutas): array
    {
        if ($rutas->isEmpty()) {
            return ResolutorAccion::acciones();
        }

        $patrones = (array) (config('menu_routes', [])[$clave] ?? [$clave]);
        $acciones = ['ver'];

        foreach ($rutas as $ruta) {
            if (Str::is($patrones, $ruta['nombre']) || Str::is($patrones, $ruta['uri'])) {
                $acciones[] = ResolutorAccion::desdeRuta($ruta['nombre'], $ruta['metodo']);
            }
        }

        return array_values(array_intersect(ResolutorAccion::acciones(), array_unique($acciones)));
    }
}
