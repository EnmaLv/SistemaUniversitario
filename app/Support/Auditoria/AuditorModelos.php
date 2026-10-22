<?php

namespace App\Support\Auditoria;

use App\Models\Bitacora as BitacoraModelo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Event;

class AuditorModelos
{
    private const ACCIONES = [
        'created'  => 'crear',
        'updated'  => 'actualizar',
        'deleted'  => 'eliminar',
        'restored' => 'restaurar',
    ];

    private const COMANDOS_OMITIDOS = [
        'migrate',
        'migrate:fresh',
        'migrate:refresh',
        'migrate:reset',
        'migrate:rollback',
        'db:seed',
        'db:wipe',
    ];

    public static function escuchar(): void
    {
        Event::listen(
            ['eloquent.created: *', 'eloquent.updated: *', 'eloquent.deleted: *', 'eloquent.restored: *'],
            fn (string $evento, array $datos) => static::manejar($evento, $datos)
        );
    }

    public static function manejar(string $evento, array $datos): void
    {
        if (! config('seguridad.bitacora.habilitada', true) || ! config('seguridad.bitacora.registrar_modelos', true)) {
            return;
        }

        if (app()->runningConsoleCommand(...self::COMANDOS_OMITIDOS)) {
            return;
        }

        $modelo = $datos[0] ?? null;
        if (! $modelo instanceof Model || static::excluido($modelo)) {
            return;
        }

        $tipo = substr($evento, 9, strpos($evento, ':') - 9);
        $accion = self::ACCIONES[$tipo] ?? null;
        if (! $accion) {
            return;
        }

        $ignorados = array_flip(config('seguridad.campos_ignorados_modelos', []));
        $antes = null;
        $despues = null;
        $observacion = null;
        $nivel = BitacoraModelo::NIVEL_INFO;

        switch ($tipo) {
            case 'created':
            case 'restored':
                $despues = array_diff_key($modelo->getAttributes(), $ignorados);
                break;

            case 'updated':
                $cambios = array_diff_key($modelo->getChanges(), $ignorados);
                if (empty($cambios)) {
                    return;
                }
                $despues = $cambios;
                $antes = array_intersect_key($modelo->getOriginal(), $cambios);
                $sensibles = DatosSensibles::camposSensiblesEn(array_keys($cambios));
                if ($sensibles) {
                    $observacion = 'Campos sensibles modificados (valor omitido): ' . implode(', ', $sensibles);
                }
                break;

            case 'deleted':
                $antes = array_diff_key($modelo->getAttributes(), $ignorados);
                $nivel = BitacoraModelo::NIVEL_ADVERTENCIA;
                if (in_array(SoftDeletes::class, class_uses_recursive($modelo), true) && ! $modelo->isForceDeleting()) {
                    $observacion = 'Borrado lógico';
                }
                break;
        }

        Bitacora::registrar(
            accion: $accion,
            tablaAfectada: $modelo->getTable(),
            registroId: is_scalar($modelo->getKey()) ? $modelo->getKey() : null,
            datosAnteriores: $antes,
            datosModificados: $despues,
            nivel: $nivel,
            observacion: $observacion,
        );
    }

    protected static function excluido(Model $modelo): bool
    {
        foreach (config('seguridad.modelos_excluidos', []) as $clase) {
            if ($modelo instanceof $clase) {
                return true;
            }
        }

        return false;
    }
}
