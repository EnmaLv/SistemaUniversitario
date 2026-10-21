<?php

namespace App\Support\Seguridad;

use App\Models\Bitacora as BitacoraModelo;
use App\Models\Usuario;
use App\Support\Auditoria\Bitacora;
use App\Support\Auditoria\FiltroPeticiones;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class AccesosDenegados
{
    private const ATRIBUTO = 'seguridad.acceso_denegado_registrado';

    public static function clave(Usuario $usuario): string
    {
        return 'accesos-denegados:' . $usuario->getKey();
    }

    public static function reiniciar(Usuario $usuario): void
    {
        Cache::forget(static::clave($usuario));
    }

    public static function yaRegistrado(Request $request): bool
    {
        return (bool) $request->attributes->get(self::ATRIBUTO, false);
    }

    public static function registrar(Request $request, ?string $motivo = null): array
    {
        $request->attributes->set(self::ATRIBUTO, true);

        $usuario = $request->user();
        $usuario = $usuario instanceof Usuario ? $usuario : null;

        [$vistas, $accion] = FiltroPeticiones::vistasYAccion($request);

        Bitacora::registrar(
            accion: 'acceso_denegado',
            nivel: BitacoraModelo::NIVEL_ADVERTENCIA,
            observacion: sprintf(
                'Vista(s): %s | Acción: %s | Motivo: %s',
                $vistas ? implode(', ', $vistas) : 'no registrada',
                $accion,
                $motivo ?: 'sin detalle'
            ),
            usuario: $usuario,
            codigoRespuesta: 403,
        );

        if (! $usuario || FiltroPeticiones::esRuido($request)) {
            return ['bloqueado' => false, 'restantes' => null];
        }

        return static::contar($usuario, $request);
    }

    public static function contar(Usuario $usuario, Request $request): array
    {
        $max = (int) config('seguridad.max_accesos_denegados', 10);
        $ventana = (int) config('seguridad.minutos_ventana_accesos', 10) * 60;
        $ahora = now()->getTimestamp();
        $clave = static::clave($usuario);

        $marcas = array_values(array_filter(
            (array) Cache::get($clave, []),
            fn ($marca) => (int) $marca > $ahora - $ventana
        ));
        $marcas[] = $ahora;

        if (count($marcas) < $max) {
            Cache::put($clave, $marcas, $ventana);

            return ['bloqueado' => false, 'restantes' => $max - count($marcas)];
        }

        Cache::forget($clave);

        Bloqueos::aplicarBloqueoPermanente(
            $usuario,
            Bloqueos::MOTIVO_ACCESOS,
            sprintf('%d accesos denegados en %d minutos', count($marcas), $ventana / 60)
        );

        if ($request->hasSession()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return ['bloqueado' => true, 'restantes' => 0];
    }

    public static function responder(HttpExceptionInterface $e, Request $request)
    {
        if ($e->getStatusCode() !== 403 || static::yaRegistrado($request)) {
            return null;
        }

        $resultado = static::registrar($request, $e->getMessage());

        if ($resultado['bloqueado']) {
            $mensaje = Bloqueos::mensaje(['tipo' => 'permanente']);

            return $request->expectsJson() || ! $request->hasSession()
                ? response()->json(['message' => $mensaje, 'redirect' => route('login')], 401)
                : redirect()->route('login')->withErrors(['email' => $mensaje]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message'            => $e->getMessage() ?: 'Acceso denegado.',
                'intentos_restantes' => $resultado['restantes'],
            ], 403, $e->getHeaders());
        }

        return response()->view('errors.403', [
            'exception'         => $e,
            'intentosRestantes' => $resultado['restantes'],
        ], 403, $e->getHeaders());
    }
}
