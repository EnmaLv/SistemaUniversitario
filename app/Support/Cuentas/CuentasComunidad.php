<?php

namespace App\Support\Cuentas;

use App\Models\Perfil;
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
    public const ROL_PACIENTE = 'paciente';

    public const ROL_BECARIO = 'becario';

    public const ROLES_POR_DEFECTO = [self::ROL_PACIENTE, self::ROL_BECARIO];

    public const TAMANO_LOTE = 200;

    private const MAX_IDS_BITACORA = 200;

    public static function idsRolesPorDefecto(): array
    {
        $ids = Rol::whereIn('slug_rol', self::ROLES_POR_DEFECTO)->pluck('id_rol', 'slug_rol')->all();

        if (count($ids) !== count(self::ROLES_POR_DEFECTO)) {
            throw new RuntimeException('No existen los roles Paciente y Becario; ejecuta las migraciones de roles.');
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
        $idEstudiante = Perfil::idEstudiante();

        foreach (array_chunk($idsPersona, self::TAMANO_LOTE) as $lote) {
            DB::transaction(function () use ($lote, $roles, $idEstudiante, &$resumen) {
                $personas = DB::table('persona')
                    ->whereIn('id_per', $lote)
                    ->get(['id_per', 'cedula_per', 'id_perfil']);

                $perfilPorPersona = $personas->pluck('id_perfil', 'id_per');

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
                        'per_id'                    => $persona->id_per,
                        'id_perfil'                 => $persona->id_perfil,
                        'username_use'              => $cedula,
                        'password_use'              => Hash::make($cedula),
                        'debe_cambiar_password_use' => true,
                        'created_at'                => $ahora,
                        'updated_at'                => $ahora,
                    ];
                }

                if ($nuevos !== []) {
                    DB::table('user')->insert($nuevos);
                    $resumen['usuarios_creados'] += count($nuevos);
                }

                $usuarios = DB::table('user')->whereIn('per_id', $lote)->get(['id_use', 'per_id', 'username_use']);
                $creados = array_flip(array_column($nuevos, 'username_use'));

                $asignados = DB::table('usuario_rol')
                    ->whereIn('use_id', $usuarios->pluck('id_use')->all())
                    ->whereIn('rol_id', array_values($roles))
                    ->get(['use_id', 'rol_id'])
                    ->mapWithKeys(fn ($fila) => [$fila->use_id . '-' . $fila->rol_id => true])
                    ->all();

                $filas = [];
                foreach ($usuarios as $usuario) {
                    $requeridos = [$roles[self::ROL_PACIENTE]];

                    if ($idEstudiante !== null && (int) ($perfilPorPersona[$usuario->per_id] ?? 0) === $idEstudiante) {
                        $requeridos[] = $roles[self::ROL_BECARIO];
                    }

                    foreach ($requeridos as $idRol) {
                        if (isset($asignados[$usuario->id_use . '-' . $idRol])) {
                            continue;
                        }

                        $filas[] = ['rol_id' => $idRol, 'use_id' => $usuario->id_use, 'created_at' => $ahora, 'updated_at' => $ahora];

                        if (! isset($creados[$usuario->username_use])) {
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
            observacion: 'Cuentas de la comunidad universitaria (rol Paciente para todas las personas y Becario solo para estudiantes)',
            usuario: $actor,
        );

        return $resumen;
    }

    public static function sincronizarRolBecario(array|int $idsPersona, string $origen = 'cambio_perfil', array $perfilesAnteriores = [], ?Usuario $actor = null): array
    {
        $resultado = ['agregados' => 0, 'retirados' => 0];

        $idsPersona = array_values(array_unique(array_filter(array_map('intval', (array) $idsPersona))));

        if ($idsPersona === []) {
            return $resultado;
        }

        $idBecario = Rol::where('slug_rol', self::ROL_BECARIO)->value('id_rol');

        if (! $idBecario) {
            throw new RuntimeException('No existe el rol Becario; ejecuta las migraciones de roles.');
        }

        $idEstudiante = Perfil::idEstudiante();
        $personasAgregadas = [];
        $personasRetiradas = [];

        foreach (array_chunk($idsPersona, self::TAMANO_LOTE) as $lote) {
            DB::transaction(function () use ($lote, $idBecario, $idEstudiante, $perfilesAnteriores, &$resultado, &$personasAgregadas, &$personasRetiradas) {
                $perfiles = DB::table('persona')->whereIn('id_per', $lote)->pluck('id_perfil', 'id_per');
                $usuarios = DB::table('user')->whereIn('per_id', $lote)->get(['id_use', 'per_id', 'id_perfil']);

                if ($usuarios->isEmpty()) {
                    return;
                }

                $conBecario = DB::table('usuario_rol')
                    ->where('rol_id', $idBecario)
                    ->whereIn('use_id', $usuarios->pluck('id_use')->all())
                    ->pluck('use_id')
                    ->flip();

                $ahora = now();
                $insertar = [];
                $retirar = [];
                $perfilCuenta = [];

                foreach ($usuarios as $usuario) {
                    $perfilActual = $perfiles[$usuario->per_id] ?? null;
                    $esEstudiante = $idEstudiante !== null && (int) $perfilActual === $idEstudiante;
                    $tieneBecario = isset($conBecario[$usuario->id_use]);

                    if ($esEstudiante && ! $tieneBecario) {
                        $insertar[] = ['rol_id' => $idBecario, 'use_id' => $usuario->id_use, 'created_at' => $ahora, 'updated_at' => $ahora];
                        $personasAgregadas[$usuario->per_id] = true;
                    } elseif (! $esEstudiante && $tieneBecario) {
                        $retirar[] = $usuario->id_use;
                        $personasRetiradas[$usuario->per_id] = true;
                    }

                    $anterior = $perfilesAnteriores[$usuario->per_id] ?? null;

                    if ($anterior !== null && $perfilActual !== null
                        && (int) $usuario->id_perfil === (int) $anterior
                        && (int) $perfilActual !== (int) $anterior) {
                        $perfilCuenta[(int) $perfilActual][] = $usuario->id_use;
                    }
                }

                if ($insertar !== []) {
                    DB::table('usuario_rol')->insert($insertar);
                    $resultado['agregados'] += count($insertar);
                }

                if ($retirar !== []) {
                    DB::table('usuario_rol')->where('rol_id', $idBecario)->whereIn('use_id', $retirar)->delete();
                    $resultado['retirados'] += count($retirar);
                }

                foreach ($perfilCuenta as $idPerfil => $idsUsuario) {
                    DB::table('user')->whereIn('id_use', $idsUsuario)->update(['id_perfil' => $idPerfil, 'updated_at' => $ahora]);
                }
            });
        }

        if ($resultado['agregados'] === 0 && $resultado['retirados'] === 0) {
            return $resultado;
        }

        VersionPermisos::invalidar();

        $unico = count($idsPersona) === 1 ? $idsPersona[0] : null;

        Bitacora::registrar(
            accion: 'rol_becario_por_perfil',
            modulo: 'administracion',
            tablaAfectada: 'usuario_rol',
            registroId: $unico,
            datosAnteriores: $unico !== null && isset($perfilesAnteriores[$unico]) ? ['id_perfil' => (int) $perfilesAnteriores[$unico]] : null,
            datosModificados: [
                'origen'                   => $origen,
                'rol'                      => self::ROL_BECARIO,
                'cuentas_con_rol_agregado' => $resultado['agregados'],
                'cuentas_con_rol_retirado' => $resultado['retirados'],
                'personas_agregadas'       => array_slice(array_keys($personasAgregadas), 0, self::MAX_IDS_BITACORA),
                'personas_retiradas'       => array_slice(array_keys($personasRetiradas), 0, self::MAX_IDS_BITACORA),
            ],
            observacion: 'Ajuste del rol Becario por cambio de perfil (solo las personas con perfil Estudiante lo conservan)',
            usuario: $actor,
        );

        return $resultado;
    }
}
