<?php

namespace App\Support\Permisos;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PermisoUrl
{
    private static array $cache = [];

    public static function nombreRuta(string $url, string $metodo = 'GET'): ?string
    {
        $clave = strtoupper($metodo) . ' ' . $url;

        if (! array_key_exists($clave, self::$cache)) {
            try {
                $ruta = Route::getRoutes()->match(Request::create(url($url), strtoupper($metodo)));
                self::$cache[$clave] = $ruta->getName();
            } catch (HttpException) {
                self::$cache[$clave] = null;
            }
        }

        return self::$cache[$clave];
    }

    public static function puede(string $url, string $metodo = 'GET', ?Usuario $usuario = null): bool
    {
        $usuario ??= Auth::user();

        if (! $usuario instanceof Usuario) {
            return false;
        }

        $nombre = self::nombreRuta($url, $metodo);

        return $nombre !== null && $usuario->puedeRuta($nombre);
    }

    public static function limpiar(): void
    {
        self::$cache = [];
    }
}
