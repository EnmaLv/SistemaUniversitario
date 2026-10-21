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
        if ($usuario->bloqueado) {
            return ['tipo' => 'permanente', 'motivo' => $usuario->motivo_bloqueo];
        }

        if ($usuario->bloqueado_hasta && $usuario->bloqueado_hasta->isFuture()) {
            $segundos = max(1, $usuario->bloqueado_hasta->getTimestamp() - now()->getTimestamp());

            return ['tipo' => 'temporal', 'segundos' => $segundos, 'motivo' => $usuario->motivo_bloqueo];
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
        $usuario->intentos_fallidos = (int) $usuario->intentos_fallidos + 1;

        if ($usuario->intentos_fallidos >= (int) config('seguridad.max_intentos_login', 3)) {
            return static::aplicarBloqueoTemporal($usuario, self::MOTIVO_CREDENCIALES);
        }

        $usuario->saveQuietly();

        return null;
    }

    public static function registrarFalloLlave(Usuario $usuario): ?array
    {
        $usuario->intentos_llave_fallidos = (int) $usuario->intentos_llave_fallidos + 1;

        if ($usuario->intentos_llave_fallidos >= (int) config('seguridad.max_intentos_llave', 3)) {
            return static::aplicarBloqueoTemporal($usuario, self::MOTIVO_LLAVE);
        }

        $usuario->saveQuietly();

        return null;
    }

    public static function aplicarBloqueoTemporal(Usuario $usuario, string $motivo): array
    {
        $ahora = now();
        $ventanaHoras = (int) config('seguridad.horas_ventana_bloqueos', 24);

        if (! $usuario->ventana_bloqueos_desde || $usuario->ventana_bloqueos_desde->lt($ahora->copy()->subHours($ventanaHoras))) {
            $usuario->ventana_bloqueos_desde = $ahora;
            $usuario->bloqueos_temporales = 0;
        }

        $usuario->bloqueos_temporales = (int) $usuario->bloqueos_temporales + 1;
        $usuario->intentos_fallidos = 0;
        $usuario->intentos_llave_fallidos = 0;

        if ($usuario->bloqueos_temporales >= (int) config('seguridad.bloqueos_temporales_para_permanente', 3)) {
            return static::aplicarBloqueoPermanente(
                $usuario,
                self::MOTIVO_TEMPORALES,
                "Acumuló {$usuario->bloqueos_temporales} bloqueos temporales en {$ventanaHoras} h. Último motivo: {$motivo}"
            );
        }

        $minutos = (int) config('seguridad.minutos_bloqueo_temporal', 5);
        $usuario->bloqueado_hasta = $ahora->copy()->addMinutes($minutos);
        $usuario->bloqueado_en = $ahora;
        $usuario->motivo_bloqueo = $motivo;
        $usuario->saveQuietly();

        Bitacora::registrar(
            accion: 'bloqueo_temporal',
            modulo: 'seguridad',
            tablaAfectada: 'usuario',
            registroId: $usuario->id_usuario,
            datosModificados: [
                'bloqueado_hasta'     => $usuario->bloqueado_hasta,
                'bloqueos_temporales' => $usuario->bloqueos_temporales,
                'motivo_bloqueo'      => $motivo,
            ],
            nivel: BitacoraModelo::NIVEL_ADVERTENCIA,
            observacion: "Bloqueo temporal de {$minutos} min ({$usuario->bloqueos_temporales} en la ventana de {$ventanaHoras} h). Motivo: {$motivo}",
            usuario: $usuario,
        );

        return ['tipo' => 'temporal', 'segundos' => $minutos * 60, 'motivo' => $motivo];
    }

    public static function aplicarBloqueoPermanente(Usuario $usuario, string $motivo, ?string $detalle = null): array
    {
        $usuario->bloqueado = true;
        $usuario->bloqueado_en = now();
        $usuario->bloqueado_hasta = null;
        $usuario->motivo_bloqueo = $motivo;
        $usuario->intentos_fallidos = 0;
        $usuario->intentos_llave_fallidos = 0;
        $usuario->saveQuietly();

        Bitacora::registrar(
            accion: 'bloqueo_permanente',
            modulo: 'seguridad',
            tablaAfectada: 'usuario',
            registroId: $usuario->id_usuario,
            datosModificados: ['bloqueado' => true, 'motivo_bloqueo' => $motivo],
            nivel: BitacoraModelo::NIVEL_CRITICO,
            observacion: $detalle ?? "Bloqueo permanente. Motivo: {$motivo}",
            usuario: $usuario,
        );

        $usuario->tokens()->delete();

        return ['tipo' => 'permanente', 'motivo' => $motivo];
    }

    public static function registrarCredencialesValidas(Usuario $usuario): void
    {
        if ((int) $usuario->intentos_fallidos === 0) {
            return;
        }

        $usuario->intentos_fallidos = 0;
        $usuario->saveQuietly();
    }

    public static function registrarLoginExitoso(Usuario $usuario, Request $request): void
    {
        $usuario->intentos_fallidos = 0;
        $usuario->intentos_llave_fallidos = 0;
        $usuario->ultimo_login = now();
        $usuario->ultimo_login_ip = $request->ip();
        $usuario->saveQuietly();
    }

    public static function desbloquear(Usuario $usuario, ?Usuario $responsable, string $origen = 'web'): void
    {
        $anterior = [
            'bloqueado'           => (bool) $usuario->bloqueado,
            'bloqueado_hasta'     => $usuario->bloqueado_hasta,
            'motivo_bloqueo'      => $usuario->motivo_bloqueo,
            'bloqueos_temporales' => (int) $usuario->bloqueos_temporales,
            'intentos_fallidos'   => (int) $usuario->intentos_fallidos,
        ];

        $usuario->bloqueado = false;
        $usuario->bloqueado_en = null;
        $usuario->bloqueado_hasta = null;
        $usuario->motivo_bloqueo = null;
        $usuario->bloqueos_temporales = 0;
        $usuario->ventana_bloqueos_desde = null;
        $usuario->intentos_fallidos = 0;
        $usuario->intentos_llave_fallidos = 0;
        $usuario->saveQuietly();

        AccesosDenegados::reiniciar($usuario);

        Bitacora::registrar(
            accion: 'desbloqueo',
            modulo: 'seguridad',
            tablaAfectada: 'usuario',
            registroId: $usuario->id_usuario,
            datosAnteriores: $anterior,
            datosModificados: ['bloqueado' => false, 'bloqueado_hasta' => null, 'bloqueos_temporales' => 0],
            nivel: BitacoraModelo::NIVEL_ADVERTENCIA,
            observacion: $origen === 'consola'
                ? "consola: desbloqueo de emergencia de {$usuario->username}"
                : "Desbloqueado por {$responsable?->username}. Usuario: {$usuario->username}",
            usuario: $responsable,
            nombreUsuario: $responsable?->username,
        );
    }
}
