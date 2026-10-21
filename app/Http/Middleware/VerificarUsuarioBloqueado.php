<?php

namespace App\Http\Middleware;

use App\Models\Bitacora as BitacoraModelo;
use App\Models\Usuario;
use App\Support\Auditoria\Bitacora;
use App\Support\Seguridad\Bloqueos;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerificarUsuarioBloqueado
{
    public function handle(Request $request, Closure $next, ?string $guard = null)
    {
        $usuario = $request->user($guard);

        if (! $usuario instanceof Usuario || ! ($estado = Bloqueos::estado($usuario))) {
            return $next($request);
        }

        Bitacora::registrar(
            accion: 'sesion_cerrada_por_bloqueo',
            modulo: 'seguridad',
            nivel: BitacoraModelo::NIVEL_ADVERTENCIA,
            observacion: "Sesión activa cerrada: cuenta con bloqueo {$estado['tipo']}",
            usuario: $usuario,
        );

        if ($request->hasSession()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        } else {
            $token = method_exists($usuario, 'currentAccessToken') ? $usuario->currentAccessToken() : null;
            if ($token && method_exists($token, 'delete')) {
                $token->delete();
            }
        }

        $mensaje = Bloqueos::mensaje($estado);

        if ($request->expectsJson() || ! $request->hasSession()) {
            return response()->json(['message' => $mensaje, 'redirect' => route('login')], 401);
        }

        return redirect()->route('login')->withErrors(['email' => $mensaje]);
    }
}
