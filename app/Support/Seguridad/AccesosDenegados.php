<?php

namespace App\Support\Seguridad;

use App\Models\Bitacora as BitacoraModelo;
use App\Models\Usuario;
use App\Support\Auditoria\Bitacora;
use App\Support\Auditoria\FiltroPeticiones;
use Illuminate\Http\Request;
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

        return ['bloqueado' => false, 'restantes' => null];
    }

    public static function responder(HttpExceptionInterface $e, Request $request)
    {
        if ($e->getStatusCode() !== 403 || static::yaRegistrado($request)) {
            return null;
        }

        $resultado = static::registrar($request, $e->getMessage());

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
