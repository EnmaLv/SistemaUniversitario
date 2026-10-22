<?php

namespace App\Http\Middleware;

use App\Models\Bitacora as BitacoraModelo;
use App\Models\Usuario;
use App\Support\Auditoria\Bitacora;
use App\Support\Auditoria\FiltroPeticiones;
use App\Support\Seguridad\AccesosDenegados;
use Closure;
use Illuminate\Http\Request;

class RegistrarActividad
{
    public function handle(Request $request, Closure $next)
    {
        return $next($request);
    }

    public function terminate(Request $request, $response): void
    {
        if (! config('seguridad.bitacora.habilitada', true)) {
            return;
        }

        try {
            $codigo = method_exists($response, 'getStatusCode') ? $response->getStatusCode() : 200;

            if ($codigo === 403) {
                if (! AccesosDenegados::yaRegistrado($request)) {
                    AccesosDenegados::registrar($request, 'Respuesta 403 sin excepción');
                }

                return;
            }

            if (! config('seguridad.bitacora.registrar_peticiones', true)) {
                return;
            }

            $usuario = $request->user();
            if (! $usuario instanceof Usuario || FiltroPeticiones::esRuido($request)) {
                return;
            }

            [, $accion] = FiltroPeticiones::vistasYAccion($request);

            Bitacora::registrar(
                accion: $accion,
                usuario: $usuario,
                codigoRespuesta: $codigo,
                nivel: match (true) {
                    $codigo >= 500 => BitacoraModelo::NIVEL_CRITICO,
                    $codigo >= 400 => BitacoraModelo::NIVEL_ADVERTENCIA,
                    default        => BitacoraModelo::NIVEL_INFO,
                },
            );
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
