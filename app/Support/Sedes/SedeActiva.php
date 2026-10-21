<?php

namespace App\Support\Sedes;

use App\Models\Usuario;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SedeActiva
{
    public const CLAVE_SESION = 'sede_activa';

    public const CLAVE_SESION_USUARIO = 'sede_usuario';

    private static array $sedePorUsuario = [];

    public static function usuario(): ?Usuario
    {
        $usuario = Auth::user();

        return $usuario instanceof Usuario ? $usuario : null;
    }

    public static function inicializar(Usuario $usuario): void
    {
        $propia = static::sedeDe($usuario);

        session([static::CLAVE_SESION_USUARIO => $propia]);

        if ($usuario->esSuperAdmin()) {
            $actual = session(static::CLAVE_SESION);
            session([static::CLAVE_SESION => static::existe($actual) ? (int) $actual : null]);

            return;
        }

        session([static::CLAVE_SESION => $propia]);
    }

    public static function id(): ?int
    {
        $usuario = static::usuario();

        if (! $usuario) {
            return null;
        }

        if (! $usuario->esSuperAdmin()) {
            return static::sedeDe($usuario);
        }

        if (! app()->bound('session') || ! session()->isStarted()) {
            return null;
        }

        $sede = session(static::CLAVE_SESION);

        return $sede === null || $sede === '' ? null : (int) $sede;
    }

    public static function esTodas(): bool
    {
        return static::usuario() !== null && static::id() === null;
    }

    public static function idParaCrear(): ?int
    {
        $usuario = static::usuario();

        return static::id()
            ?? ($usuario ? static::sedeDe($usuario) : null)
            ?? DB::table('sede')->orderBy('id_sed')->value('id_sed');
    }

    public static function fijar(?int $sedeId): void
    {
        session([static::CLAVE_SESION => $sedeId !== null && static::existe($sedeId) ? $sedeId : null]);
    }

    public static function puedeElegir(?Usuario $usuario = null): bool
    {
        $usuario ??= static::usuario();

        return $usuario !== null && $usuario->esSuperAdmin();
    }

    public static function filtrar($consulta, string $columna)
    {
        $sede = static::id();

        return $sede === null ? $consulta : $consulta->where($columna, $sede);
    }

    public static function sedeDe(Usuario $usuario): ?int
    {
        $clave = (int) $usuario->getKey();

        if (! array_key_exists($clave, static::$sedePorUsuario)) {
            $sede = DB::table('persona')->where('id_per', $usuario->per_id)->value('sed_id');
            static::$sedePorUsuario[$clave] = $sede === null ? null : (int) $sede;
        }

        return static::$sedePorUsuario[$clave];
    }

    public static function olvidar(): void
    {
        static::$sedePorUsuario = [];
    }

    private static function existe($sedeId): bool
    {
        return $sedeId !== null && $sedeId !== '' && DB::table('sede')->where('id_sed', (int) $sedeId)->exists();
    }
}
