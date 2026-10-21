<?php

namespace App\Support\Seguridad;

use App\Models\Bitacora as BitacoraModelo;
use App\Models\Usuario;
use App\Support\Auditoria\Bitacora;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class ProteccionLogin
{
    private const HASH_FICTICIO = '$2y$12$A5H25pT96fYo8xDJB2rDs.DSVs7Cta5rQa/Aq9P956p4J0Fp4tF9q';

    public static function intentar(string $username, string $password, string $ip, string $canal = 'web'): array
    {
        $username = trim($username);
        $usuario = Usuario::buscarPorIdentificador($username);

        if (! $usuario) {
            return static::fallarInexistente($username, $password, $ip, $canal);
        }

        if ($estado = Bloqueos::estado($usuario)) {
            Bitacora::registrar(
                accion: 'login_bloqueado',
                modulo: 'autenticacion',
                nivel: BitacoraModelo::NIVEL_ADVERTENCIA,
                observacion: "Intento de acceso ({$canal}) a una cuenta bloqueada ({$estado['tipo']})",
                usuario: $usuario,
                nombreUsuario: $username,
            );

            return ['usuario' => null, 'mensaje' => Bloqueos::mensaje($estado)];
        }

        if (! static::passwordValida($usuario, $password)) {
            $max = (int) config('seguridad.max_intentos_login', 3);

            Bitacora::registrar(
                accion: 'login_fallido',
                modulo: 'autenticacion',
                nivel: BitacoraModelo::NIVEL_ADVERTENCIA,
                observacion: sprintf('Contraseña incorrecta (%s). Intento %d de %d', $canal, (int) $usuario->intentos_fallidos + 1, $max),
                usuario: $usuario,
                nombreUsuario: $username,
            );

            $estado = Bloqueos::registrarFalloPassword($usuario);

            return ['usuario' => null, 'mensaje' => $estado ? Bloqueos::mensaje($estado) : Bloqueos::MENSAJE_GENERICO];
        }

        Bloqueos::registrarCredencialesValidas($usuario);

        return ['usuario' => $usuario, 'mensaje' => null];
    }

    public static function clave(string $username, string $ip): string
    {
        return 'login:' . sha1(Usuario::claveIdentificador($username) . '|' . $ip);
    }

    protected static function fallarInexistente(string $username, string $password, string $ip, string $canal): array
    {
        $clave = static::clave($username, $ip);
        $max = (int) config('seguridad.max_intentos_login', 3);

        Hash::check($password, self::HASH_FICTICIO);

        if ($estado = static::estadoInexistente($clave, $max)) {
            Bitacora::registrar(
                accion: 'login_bloqueado',
                modulo: 'autenticacion',
                nivel: BitacoraModelo::NIVEL_ADVERTENCIA,
                observacion: "Intento ({$canal}) durante bloqueo por límite de intentos (usuario inexistente)",
                nombreUsuario: $username,
            );

            return ['usuario' => null, 'mensaje' => Bloqueos::mensaje($estado)];
        }

        RateLimiter::hit($clave, (int) config('seguridad.minutos_bloqueo_temporal', 5) * 60);

        Bitacora::registrar(
            accion: 'login_fallido',
            modulo: 'autenticacion',
            nivel: BitacoraModelo::NIVEL_ADVERTENCIA,
            observacion: sprintf('Usuario inexistente (%s). Intento %d de %d desde esta IP', $canal, RateLimiter::attempts($clave), $max),
            nombreUsuario: $username,
        );

        if (RateLimiter::attempts($clave) < $max) {
            return ['usuario' => null, 'mensaje' => Bloqueos::MENSAJE_GENERICO];
        }

        $horas = (int) config('seguridad.horas_ventana_bloqueos', 24);
        $contador = 'login-bloqueos:' . $clave;
        Cache::add($contador, 0, now()->addHours($horas));
        $bloqueos = (int) Cache::increment($contador);

        if ($bloqueos >= (int) config('seguridad.bloqueos_temporales_para_permanente', 3)) {
            Cache::put('login-permanente:' . $clave, true, now()->addDays(30));
            $estado = ['tipo' => 'permanente'];
        } else {
            $estado = ['tipo' => 'temporal', 'segundos' => RateLimiter::availableIn($clave)];
        }

        Bitacora::registrar(
            accion: 'login_limite_ip',
            modulo: 'autenticacion',
            nivel: BitacoraModelo::NIVEL_ADVERTENCIA,
            observacion: "Límite de intentos alcanzado para un usuario inexistente desde esta IP ({$estado['tipo']})",
            nombreUsuario: $username,
        );

        return ['usuario' => null, 'mensaje' => Bloqueos::mensaje($estado)];
    }

    protected static function estadoInexistente(string $clave, int $max): ?array
    {
        if (Cache::get('login-permanente:' . $clave)) {
            return ['tipo' => 'permanente'];
        }

        if (RateLimiter::tooManyAttempts($clave, $max)) {
            return ['tipo' => 'temporal', 'segundos' => RateLimiter::availableIn($clave)];
        }

        return null;
    }

    protected static function passwordValida(Usuario $usuario, string $password): bool
    {
        $almacenada = $usuario->getOriginal('password');

        if (! is_string($almacenada) || $almacenada === '') {
            return false;
        }

        try {
            return Hash::check($password, $almacenada);
        } catch (\RuntimeException $e) {
            return password_verify($password, $almacenada);
        }
    }
}
