<?php

namespace App\Support\Cuentas;

use App\Models\Persona;
use App\Models\Rol;
use App\Models\Usuario;
use App\Support\Auditoria\Bitacora;
use App\Support\Permisos\VersionPermisos;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class CuentasComunidad
{
    public const ROLES_POR_DEFECTO = ['becario', 'paciente'];

    public const TAMANO_LOTE = 200;

    public static function idsRolesPorDefecto(): array
    {
        $ids = Rol::whereIn('slug_rol', self::ROLES_POR_DEFECTO)->pluck('id_rol')->all();

        if (count($ids) !== count(self::ROLES_POR_DEFECTO)) {
            throw new RuntimeException('No existen los roles Becario y Paciente; ejecuta las migraciones de roles.');
        }

        return $ids;
    }

    public static function asegurarCuenta(Persona $persona, ?Usuario $actor = null): ?Usuario
    {
        self::procesarLote([$persona->id_per], 'registro_manual', $actor);

        return Usuario::where('per_id', $persona->id_per)->orderBy('id_use')->first();
    }

    public static function procesarLote(array $idsPersona, string $origen, ?Usuario $actor = null): array
    {
        $resumen = [
            'usuarios_creados'    => 0,
            'usuarios_existentes' => 0,
            'roles_agregados'     => 0,
            'errores'             => [],
        ];

        $idsPersona = array_values(array_unique(array_filter(array_map('intval', $idsPersona))));

        if ($idsPersona === []) {
            return $resumen;
        }

        $roles = self::idsRolesPorDefecto();

        foreach (array_chunk($idsPersona, self::TAMANO_LOTE) as $lote) {
            DB::transaction(function () use ($lote, $roles, &$resumen) {
                $personas = DB::table('persona')
                    ->whereIn('id_per', $lote)
                    ->get(['id_per', 'cedula_per', 'id_perfil']);

                $conCuenta = DB::table('user')
                    ->whereIn('per_id', $lote)
                    ->pluck('per_id')
                    ->flip();

                $cedulas = $personas
                    ->map(fn ($persona) => Cedula::normalizar($persona->cedula_per))
                    ->filter()
                    ->values()
                    ->all();

                $ocupados = DB::table('user')
                    ->whereIn('username_use', $cedulas)
                    ->pluck('username_use')
                    ->flip()
                    ->all();

                $nuevos = [];
                $ahora = now();

                foreach ($personas as $persona) {
                    if (isset($conCuenta[$persona->id_per])) {
                        $resumen['usuarios_existentes']++;
                        continue;
                    }

                    $cedula = Cedula::normalizar($persona->cedula_per);

                    if (! $cedula) {
                        $resumen['errores'][] = ['per_id' => $persona->id_per, 'motivo' => 'Cédula vacía o mal formada'];
                        continue;
                    }

                    if (isset($ocupados[$cedula])) {
                        $resumen['errores'][] = ['per_id' => $persona->id_per, 'motivo' => 'La cédula ya es el usuario de otra cuenta'];
                        continue;
                    }

                    $ocupados[$cedula] = true;
                    $nuevos[] = [
                        'per_id'            => $persona->id_per,
                        'id_perfil'             => $persona->id_perfil,
                        'username_use'          => $cedula,
                        'password_use'          => Hash::make($cedula),
                        'debe_cambiar_password_use' => true,
                        'created_at'            => $ahora,
                        'updated_at'            => $ahora,
                    ];
                }

                if ($nuevos !== []) {
                    DB::table('user')->insert($nuevos);
                    $resumen['usuarios_creados'] += count($nuevos);
                }

                $usuarios = DB::table('user')->whereIn('per_id', $lote)->pluck('id_use')->all();
                $creados = array_flip(array_column($nuevos, 'username_use'));

                $asignados = DB::table('usuario_rol')
                    ->whereIn('use_id', $usuarios)
                    ->whereIn('rol_id', $roles)
                    ->get(['use_id', 'rol_id'])
                    ->mapWithKeys(fn ($fila) => [$fila->use_id . '-' . $fila->rol_id => true])
                    ->all();

                $usernames = DB::table('user')->whereIn('id_use', $usuarios)->pluck('username_use', 'id_use');

                $filas = [];
                foreach ($usuarios as $idUsuario) {
                    foreach ($roles as $idRol) {
                        if (isset($asignados[$idUsuario . '-' . $idRol])) {
                            continue;
                        }

                        $filas[] = ['rol_id' => $idRol, 'use_id' => $idUsuario, 'created_at' => $ahora, 'updated_at' => $ahora];

                        if (! isset($creados[$usernames[$idUsuario] ?? ''])) {
                            $resumen['roles_agregados']++;
                        }
                    }
                }

                if ($filas !== []) {
                    DB::table('usuario_rol')->insert($filas);
                }
            });
        }

        if ($resumen['usuarios_creados'] === 0 && $resumen['roles_agregados'] === 0 && $resumen['errores'] === []) {
            return $resumen;
        }

        VersionPermisos::invalidar();

        Bitacora::registrar(
            accion: 'cuentas_comunidad_generadas',
            modulo: 'administracion',
            tablaAfectada: 'user',
            datosModificados: [
                'origen'              => $origen,
                'personas'            => count($idsPersona),
                'usuarios_creados'    => $resumen['usuarios_creados'],
                'usuarios_existentes' => $resumen['usuarios_existentes'],
                'roles_agregados'     => $resumen['roles_agregados'],
                'errores'             => count($resumen['errores']),
            ],
            observacion: 'Cuentas de la comunidad universitaria (roles Becario y Paciente)',
            usuario: $actor,
        );

        return $resumen;
    }
}
