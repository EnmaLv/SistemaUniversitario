<?php

namespace App\Http\Middleware;

use App\Models\Usuario;
use App\Support\Auditoria\Bitacora;
use App\Support\Auditoria\FiltroPeticiones;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CerrarSesionPorInactividad
{
    public const MENSAJE = 'Tu sesión se cerró por inactividad. Inicia sesión de nuevo.';

    public function handle(Request $request, Closure $next)
    {
        $usuario = $request->user();

        if (! $usuario instanceof Usuario || ! $request->hasSession()) {
            return $next($request);
        }

        $limite = (int) config('seguridad.minutos_inactividad', 15) * 60;
        $ultima = (int) $request->session()->get('ultima_actividad', 0);
        $ahora = now()->getTimestamp();

        if ($limite > 0 && $ultima > 0 && $ahora - $ultima > $limite) {
            Bitacora::registrar(
                accion: 'sesion_expirada',
                modulo: 'autenticacion',
                observacion: sprintf('Inactividad de %d minutos (límite %d)', intdiv($ahora - $ultima, 60), $limite / 60),
                usuario: $usuario,
            );

            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json(['message' => self::MENSAJE, 'redirect' => route('login')], 401);
            }

            return redirect()->route('login')->withErrors(['email' => self::MENSAJE]);
        }

        if ($ultima === 0 || ! FiltroPeticiones::esRuido($request)) {
            $request->session()->put('ultima_actividad', $ahora);
        }

        return $next($request);
    }

    public static function segundosRestantes(Request $request): int
    {
        $limite = (int) config('seguridad.minutos_inactividad', 15) * 60;
        $ultima = (int) $request->session()->get('ultima_actividad', now()->getTimestamp());

        return max(0, $limite - (now()->getTimestamp() - $ultima));
    }
}
