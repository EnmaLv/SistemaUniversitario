<?php

namespace App\Http\Middleware;

use App\Models\Usuario;
use Closure;
use Illuminate\Http\Request;

class ExigirCambioPassword
{
    public const RUTAS_PERMITIDAS = [
        'password.cambiar',
        'password.cambiar.guardar',
        'logout',
        'seguridad.sesion.*',
    ];

    public function handle(Request $request, Closure $next, ?string $guard = null)
    {
        $usuario = $request->user($guard);

        if (! $usuario instanceof Usuario || ! $usuario->debe_cambiar_password_use) {
            return $next($request);
        }

        if ($request->routeIs(...self::RUTAS_PERMITIDAS) || $request->is('api/auth/logout')) {
            return $next($request);
        }

        $mensaje = 'Debes cambiar tu contraseña antes de continuar.';

        if ($request->expectsJson() || $guard === 'sanctum') {
            return response()->json(['message' => $mensaje, 'debe_cambiar_password_use' => true], 428);
        }

        return redirect()->route('password.cambiar')->with('warning', $mensaje);
    }
}
