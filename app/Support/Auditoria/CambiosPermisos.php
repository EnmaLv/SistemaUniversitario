<?php

namespace App\Support\Auditoria;

use App\Models\Bitacora as BitacoraModelo;
use App\Models\Modulo;
use App\Models\Permiso;
use App\Models\Rol;
use App\Models\Usuario;

class CambiosPermisos
{
    public static function rol(Rol $rol, string $accion, array $sincPermisos, array $sincModulos): void
    {
        $otorgados = static::permisos($sincPermisos['attached'] ?? []);
        $quitados = static::permisos($sincPermisos['detached'] ?? []);
        $modulosAgregados = static::modulos($sincModulos['attached'] ?? []);
        $modulosQuitados = static::modulos($sincModulos['detached'] ?? []);

        if (! $otorgados && ! $quitados && ! $modulosAgregados && ! $modulosQuitados) {
            return;
        }

        Bitacora::registrar(
            accion: $accion,
            modulo: 'administracion',
            tablaAfectada: 'rol_permiso',
            registroId: $rol->id_rol,
            datosAnteriores: ['permisos_quitados' => $quitados, 'modulos_quitados' => $modulosQuitados],
            datosModificados: ['permisos_otorgados' => $otorgados, 'modulos_agregados' => $modulosAgregados],
            nivel: BitacoraModelo::NIVEL_ADVERTENCIA,
            observacion: "Rol: {$rol->nombre_rol}",
        );
    }

    public static function permisosUsuario(Usuario $usuario, array $antes, array $despues): void
    {
        $cambios = [];
        foreach (['grant', 'revoke', 'modulos'] as $tipo) {
            $agregados = array_values(array_diff($despues[$tipo] ?? [], $antes[$tipo] ?? []));
            $quitados = array_values(array_diff($antes[$tipo] ?? [], $despues[$tipo] ?? []));
            $resolver = $tipo === 'modulos' ? 'modulos' : 'permisos';
            $cambios['agregados'][$tipo] = static::$resolver($agregados);
            $cambios['quitados'][$tipo] = static::$resolver($quitados);
        }

        $hayCambios = collect($cambios)->flatten()->isNotEmpty();
        $vigenciaCambio = ($antes['vigencia'] ?? null) !== ($despues['vigencia'] ?? null);

        if (! $hayCambios && ! $vigenciaCambio) {
            return;
        }

        Bitacora::registrar(
            accion: 'permisos_usuario_actualizados',
            modulo: 'administracion',
            tablaAfectada: 'usuario_permiso',
            registroId: $usuario->id_use,
            datosAnteriores: ['quitados' => $cambios['quitados'], 'vigencia' => $antes['vigencia'] ?? null],
            datosModificados: ['agregados' => $cambios['agregados'], 'vigencia' => $despues['vigencia'] ?? null],
            nivel: BitacoraModelo::NIVEL_ADVERTENCIA,
            observacion: "Usuario: {$usuario->username_use}",
        );
    }

    public static function rolesUsuario(Usuario $usuario, array $sinc, string $accion = 'roles_usuario_actualizados'): void
    {
        $agregados = Rol::withTrashed()->whereIn('id_rol', $sinc['attached'] ?? [])->pluck('nombre_rol')->all();
        $quitados = Rol::withTrashed()->whereIn('id_rol', $sinc['detached'] ?? [])->pluck('nombre_rol')->all();

        if (! $agregados && ! $quitados) {
            return;
        }

        Bitacora::registrar(
            accion: $accion,
            modulo: 'administracion',
            tablaAfectada: 'rol_usuario',
            registroId: $usuario->id_use,
            datosAnteriores: ['roles_quitados' => $quitados],
            datosModificados: ['roles_agregados' => $agregados],
            nivel: BitacoraModelo::NIVEL_ADVERTENCIA,
            observacion: "Usuario: {$usuario->username_use}",
        );
    }

    protected static function permisos(array $ids): array
    {
        return $ids ? Permiso::whereIn('id_pmi', $ids)->orderBy('nombre_pmi')->pluck('nombre_pmi')->all() : [];
    }

    protected static function modulos(array $ids): array
    {
        return $ids ? Modulo::whereIn('id_mod', $ids)->orderBy('key_mod')->pluck('key_mod')->all() : [];
    }
}
