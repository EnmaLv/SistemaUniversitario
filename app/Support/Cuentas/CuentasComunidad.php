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
        $ids = Rol::whereIn('slug', self::ROLES_POR_DEFECTO)->pluck('id_rol')->all();

        if (count($ids) !== count(self::ROLES_POR_DEFECTO)) {
            throw new RuntimeException('No existen los roles Becario y Paciente; ejecuta las migraciones de roles.');
        }

        return $ids;
    }

    public static function asegurarCuenta(Persona $persona, ?Usuario $actor = null): ?Usuario
    {
        self::procesarLote([$persona->id_persona], 'registro_manual', $actor);

        return Usuario::where('id_persona', $persona->id_persona)->orderBy('id_usuario')->first();
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
                    ->whereIn('id_persona', $lote)
                    ->get(['id_persona', 'cedula_persona', 'id_perfil']);

                $conCuenta = DB::table('usuario')
                    ->whereIn('id_persona', $lote)
                    ->pluck('id_persona')
                    ->flip();

                $cedulas = $personas
                    ->map(fn ($persona) => Cedula::normalizar($persona->cedula_persona))
                    ->filter()
                    ->values()
                    ->all();

                $ocupados = DB::table('usuario')
                    ->whereIn('username', $cedulas)
                    ->pluck('username')
                    ->flip()
                    ->all();

                $nuevos = [];
                $ahora = now();

                foreach ($personas as $persona) {
                    if (isset($conCuenta[$persona->id_persona])) {
                        $resumen['usuarios_existentes']++;
                        continue;
                    }

                    $cedula = Cedula::normalizar($persona->cedula_persona);

                    if (! $cedula) {
                        $resumen['errores'][] = ['id_persona' => $persona->id_persona, 'motivo' => 'Cédula vacía o mal formada'];
                        continue;
                    }

                    if (isset($ocupados[$cedula])) {
                        $resumen['errores'][] = ['id_persona' => $persona->id_persona, 'motivo' => 'La cédula ya es el usuario de otra cuenta'];
                        continue;
                    }

                    $ocupados[$cedula] = true;
                    $nuevos[] = [
                        'id_persona'            => $persona->id_persona,
                        'id_perfil'             => $persona->id_perfil,
                        'username'              => $cedula,
                        'password'              => Hash::make($cedula),
                        'debe_cambiar_password' => true,
                        'created_at'            => $ahora,
                        'updated_at'            => $ahora,
                    ];
                }

                if ($nuevos !== []) {
                    DB::table('usuario')->insert($nuevos);
                    $resumen['usuarios_creados'] += count($nuevos);
                }

                $usuarios = DB::table('usuario')->whereIn('id_persona', $lote)->pluck('id_usuario')->all();
                $creados = array_flip(array_column($nuevos, 'username'));

                $asignados = DB::table('rol_usuario')
                    ->whereIn('id_usuario', $usuarios)
                    ->whereIn('id_rol', $roles)
                    ->get(['id_usuario', 'id_rol'])
                    ->mapWithKeys(fn ($fila) => [$fila->id_usuario . '-' . $fila->id_rol => true])
                    ->all();

                $usernames = DB::table('usuario')->whereIn('id_usuario', $usuarios)->pluck('username', 'id_usuario');

                $filas = [];
                foreach ($usuarios as $idUsuario) {
                    foreach ($roles as $idRol) {
                        if (isset($asignados[$idUsuario . '-' . $idRol])) {
                            continue;
                        }

                        $filas[] = ['id_rol' => $idRol, 'id_usuario' => $idUsuario, 'created_at' => $ahora, 'updated_at' => $ahora];

                        if (! isset($creados[$usernames[$idUsuario] ?? ''])) {
                            $resumen['roles_agregados']++;
                        }
                    }
                }

                if ($filas !== []) {
                    DB::table('rol_usuario')->insert($filas);
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
            tablaAfectada: 'usuario',
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
