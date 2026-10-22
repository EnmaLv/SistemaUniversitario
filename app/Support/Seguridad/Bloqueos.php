<?php

namespace App\Support\Seguridad;

use App\Models\Bitacora as BitacoraModelo;
use App\Models\Usuario;
use App\Support\Auditoria\Bitacora;
use Illuminate\Http\Request;

class Bloqueos
{
    public const MOTIVO_CREDENCIALES = 'credenciales_incorrectas';
    public const MOTIVO_LLAVE = 'llave_maestra_incorrecta';
    public const MOTIVO_RECUPERACION = 'recuperacion_fallida';
    public const MOTIVO_TEMPORALES = 'bloqueos_temporales_repetidos';
    public const MOTIVO_ACCESOS = 'accesos_restringidos_repetidos';

    public const MENSAJE_GENERICO = 'Credenciales inválidas.';

    public static function estado(Usuario $usuario): ?array
    {
        if ($usuario->bloqueado_use) {
            return ['tipo' => 'permanente', 'motivo' => $usuario->motivo_bloqueo_use];
        }

        if ($usuario->bloqueado_hasta_use && $usuario->bloqueado_hasta_use->isFuture()) {
            $segundos = max(1, $usuario->bloqueado_hasta_use->getTimestamp() - now()->getTimestamp());

            return ['tipo' => 'temporal', 'segundos' => $segundos, 'motivo' => $usuario->motivo_bloqueo_use];
        }

        return null;
    }

    public static function estaBloqueado(Usuario $usuario): bool
    {
        return static::estado($usuario) !== null;
    }

    public static function mensaje(array $estado): string
    {
        if ($estado['tipo'] === 'permanente') {
            return 'Tu cuenta está bloqueada. Comunícate con un administrador para desbloquearla.';
        }

        $minutos = max(1, (int) ceil(($estado['segundos'] ?? 60) / 60));

        return "Demasiados intentos fallidos. Podrás intentarlo de nuevo en {$minutos} " . ($minutos === 1 ? 'minuto.' : 'minutos.');
    }

    public static function registrarFalloPassword(Usuario $usuario): ?array
    {
        $usuario->intentos_fallidos_use = (int) $usuario->intentos_fallidos_use + 1;

        if ($usuario->intentos_fallidos_use >= (int) config('seguridad.max_intentos_login', 3)) {
            return static::aplicarBloqueoTemporal($usuario, self::MOTIVO_CREDENCIALES);
        }

        $usuario->saveQuietly();

        return null;
    }

    public static function registrarFalloLlave(Usuario $usuario): ?array
    {
        $usuario->intentos_llave_fallidos_use = (int) $usuario->intentos_llave_fallidos_use + 1;

        if ($usuario->intentos_llave_fallidos_use >= (int) config('seguridad.max_intentos_llave', 3)) {
            return static::aplicarBloqueoTemporal($usuario, self::MOTIVO_LLAVE);
        }

        $usuario->saveQuietly();

        return null;
    }

    public static function aplicarBloqueoTemporal(Usuario $usuario, string $motivo): array
    {
        $ahora = now();
        $ventanaHoras = (int) config('seguridad.horas_ventana_bloqueos', 24);

        if (! $usuario->ventana_bloqueos_desde_use || $usuario->ventana_bloqueos_desde_use->lt($ahora->copy()->subHours($ventanaHoras))) {
            $usuario->ventana_bloqueos_desde_use = $ahora;
            $usuario->bloqueos_temporales_use = 0;
        }

        $usuario->bloqueos_temporales_use = (int) $usuario->bloqueos_temporales_use + 1;
        $usuario->intentos_fallidos_use = 0;
        $usuario->intentos_llave_fallidos_use = 0;

        if ($usuario->bloqueos_temporales_use >= (int) config('seguridad.bloqueos_temporales_para_permanente', 3)) {
            return static::aplicarBloqueoPermanente(
                $usuario,
                self::MOTIVO_TEMPORALES,
                "Acumuló {$usuario->bloqueos_temporales_use} bloqueos temporales en {$ventanaHoras} h. Último motivo: {$motivo}"
            );
        }

        $minutos = (int) config('seguridad.minutos_bloqueo_temporal', 5);
        $usuario->bloqueado_hasta_use = $ahora->copy()->addMinutes($minutos);
        $usuario->bloqueado_en_use = $ahora;
        $usuario->motivo_bloqueo_use = $motivo;
        $usuario->saveQuietly();

        Bitacora::registrar(
            accion: 'bloqueo_temporal',
            modulo: 'seguridad',
            tablaAfectada: 'user',
            registroId: $usuario->id_use,
            datosModificados: [
                'bloqueado_hasta_use'     => $usuario->bloqueado_hasta_use,
                'bloqueos_temporales_use' => $usuario->bloqueos_temporales_use,
                'motivo_bloqueo_use'      => $motivo,
            ],
            nivel: BitacoraModelo::NIVEL_ADVERTENCIA,
            observacion: "Bloqueo temporal de {$minutos} min ({$usuario->bloqueos_temporales_use} en la ventana de {$ventanaHoras} h). Motivo: {$motivo}",
            usuario: $usuario,
        );

        return ['tipo' => 'temporal', 'segundos' => $minutos * 60, 'motivo' => $motivo];
    }

    public static function aplicarBloqueoPermanente(Usuario $usuario, string $motivo, ?string $detalle = null): array
    {
        $usuario->bloqueado_use = true;
        $usuario->bloqueado_en_use = now();
        $usuario->bloqueado_hasta_use = null;
        $usuario->motivo_bloqueo_use = $motivo;
        $usuario->intentos_fallidos_use = 0;
        $usuario->intentos_llave_fallidos_use = 0;
        $usuario->saveQuietly();

        Bitacora::registrar(
            accion: 'bloqueo_permanente',
            modulo: 'seguridad',
            tablaAfectada: 'user',
            registroId: $usuario->id_use,
            datosModificados: ['bloqueado_use' => true, 'motivo_bloqueo_use' => $motivo],
            nivel: BitacoraModelo::NIVEL_CRITICO,
            observacion: $detalle ?? "Bloqueo permanente. Motivo: {$motivo}",
            usuario: $usuario,
        );

        $usuario->tokens()->delete();

        return ['tipo' => 'permanente', 'motivo' => $motivo];
    }

    public static function registrarCredencialesValidas(Usuario $usuario): void
    {
        if ((int) $usuario->intentos_fallidos_use === 0) {
            return;
        }

        $usuario->intentos_fallidos_use = 0;
        $usuario->saveQuietly();
    }

    public static function registrarLoginExitoso(Usuario $usuario, Request $request): void
    {
        $usuario->intentos_fallidos_use = 0;
        $usuario->intentos_llave_fallidos_use = 0;
        $usuario->ultimo_login_use = now();
        $usuario->ultimo_login_ip_use = $request->ip();
        $usuario->saveQuietly();
    }

    public static function desbloquear(Usuario $usuario, ?Usuario $responsable, string $origen = 'web'): void
    {
        $anterior = [
            'bloqueado_use'       => (bool) $usuario->bloqueado_use,
            'bloqueado_hasta_use'     => $usuario->bloqueado_hasta_use,
            'motivo_bloqueo_use'      => $usuario->motivo_bloqueo_use,
            'bloqueos_temporales_use' => (int) $usuario->bloqueos_temporales_use,
            'intentos_fallidos_use'   => (int) $usuario->intentos_fallidos_use,
        ];

        $usuario->bloqueado_use = false;
        $usuario->bloqueado_en_use = null;
        $usuario->bloqueado_hasta_use = null;
        $usuario->motivo_bloqueo_use = null;
        $usuario->bloqueos_temporales_use = 0;
        $usuario->ventana_bloqueos_desde_use = null;
        $usuario->intentos_fallidos_use = 0;
        $usuario->intentos_llave_fallidos_use = 0;
        $usuario->saveQuietly();

        AccesosDenegados::reiniciar($usuario);

        Bitacora::registrar(
            accion: 'desbloqueo',
            modulo: 'seguridad',
            tablaAfectada: 'user',
            registroId: $usuario->id_use,
            datosAnteriores: $anterior,
            datosModificados: ['bloqueado' => false, 'bloqueado_hasta_use' => null, 'bloqueos_temporales_use' => 0],
            nivel: BitacoraModelo::NIVEL_ADVERTENCIA,
            observacion: $origen === 'consola'
                ? "consola: desbloqueo de emergencia de {$usuario->username_use}"
                : "Desbloqueado por {$responsable?->username_use}. Usuario: {$usuario->username_use}",
            usuario: $responsable,
            nombreUsuario: $responsable?->username_use,
        );
    }
}
