<?php

use App\Support\Permisos\SincronizadorPermisos;
use App\Support\Permisos\VersionPermisos;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $rolTieneJson     = Schema::hasColumn('rol', 'menu_permissions');
        $usuarioTieneJson = Schema::hasColumn('user', 'extra_permissions');

        $roles    = $rolTieneJson ? DB::table('rol')->whereNull('deleted_at')->get(['id_rol', 'menu_permissions']) : collect();
        $usuarios = $usuarioTieneJson ? DB::table('user')->whereNotNull('extra_permissions')->get(['id_use', 'extra_permissions']) : collect();

        $clavesRol = [];
        foreach ($roles as $rol) {
            $clavesRol[$rol->id_rol] = $this->decodificar($rol->menu_permissions);
        }

        $extrasUsuario = [];
        foreach ($usuarios as $usuario) {
            $extra = $this->decodificar($usuario->extra_permissions);
            $extrasUsuario[$usuario->id_use] = [
                'allow'   => array_values(array_filter((array) ($extra['allow'] ?? []), 'is_string')),
                'deny'    => array_values(array_filter((array) ($extra['deny'] ?? []), 'is_string')),
                'modulos' => array_values(array_map('intval', (array) ($extra['modulos'] ?? []))),
            ];
        }

        $todasLasClaves = collect($clavesRol)->flatten()
            ->merge(collect($extrasUsuario)->pluck('allow')->flatten())
            ->merge(collect($extrasUsuario)->pluck('deny')->flatten())
            ->filter(fn ($c) => is_string($c) && $c !== '')
            ->unique()
            ->values()
            ->all();

        SincronizadorPermisos::sincronizar($todasLasClaves);

        $permisosPorVista = DB::table('permiso')
            ->join('vista', 'vista.id_vis', '=', 'permiso.vis_id')
            ->get(['vista.key_vis as vista_key', 'permiso.id_pmi'])
            ->groupBy('vista_key')
            ->map(fn ($filas) => $filas->pluck('id_pmi')->all())
            ->all();

        $ahora = now();

        DB::transaction(function () use ($clavesRol, $extrasUsuario, $permisosPorVista, $ahora) {
            foreach ($clavesRol as $rolId => $claves) {
                $filas = [];
                foreach ($claves as $clave) {
                    foreach ($permisosPorVista[$clave] ?? [] as $permisoId) {
                        $filas[$permisoId] = [
                            'rol_id'     => $rolId,
                            'pmi_id'     => $permisoId,
                            'created_at' => $ahora,
                            'updated_at' => $ahora,
                        ];
                    }
                }
                if ($filas) {
                    DB::table('rol_permiso')->insertOrIgnore(array_values($filas));
                }
            }

            $modulosValidos = DB::table('modulo')->pluck('id_mod')->all();

            foreach ($extrasUsuario as $usuarioId => $extra) {
                $filas = [];

                foreach ($extra['allow'] as $clave) {
                    foreach ($permisosPorVista[$clave] ?? [] as $permisoId) {
                        $filas[$permisoId] = 'grant';
                    }
                }

                foreach ($extra['deny'] as $clave) {
                    foreach ($permisosPorVista[$clave] ?? [] as $permisoId) {
                        $filas[$permisoId] = 'revoke';
                    }
                }

                $insertar = [];
                foreach ($filas as $permisoId => $tipo) {
                    $insertar[] = [
                        'use_id'     => $usuarioId,
                        'pmi_id'     => $permisoId,
                        'tipo_usp'   => $tipo,
                        'created_at' => $ahora,
                        'updated_at' => $ahora,
                    ];
                }
                if ($insertar) {
                    DB::table('usuario_permiso')->insertOrIgnore($insertar);
                }

                $modulos = array_values(array_intersect($extra['modulos'], $modulosValidos));
                if ($modulos) {
                    DB::table('usuario_modulo')->insertOrIgnore(array_map(fn ($moduloId) => [
                        'use_id'     => $usuarioId,
                        'mod_id'     => $moduloId,
                        'created_at' => $ahora,
                        'updated_at' => $ahora,
                    ], $modulos));
                }
            }
        });

        VersionPermisos::invalidar();
    }

    public function down(): void
    {
        DB::table('usuario_modulo')->delete();
        DB::table('usuario_permiso')->delete();
        DB::table('rol_permiso')->delete();
    }

    private function decodificar($valor): array
    {
        if (is_array($valor)) {
            return $valor;
        }

        if (! is_string($valor) || $valor === '') {
            return [];
        }

        $decodificado = json_decode($valor, true);

        if (is_string($decodificado)) {
            $decodificado = json_decode($decodificado, true);
        }

        return is_array($decodificado) ? $decodificado : [];
    }
};
