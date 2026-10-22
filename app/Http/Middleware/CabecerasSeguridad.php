<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CabecerasSeguridad
{
    public function handle(Request $request, Closure $next)
    {
        $respuesta = $next($request);

        if (! config('seguridad.cabeceras.habilitadas', true) || ! isset($respuesta->headers)) {
            return $respuesta;
        }

        $cabeceras = [
            'X-Frame-Options'        => 'SAMEORIGIN',
            'X-Content-Type-Options' => 'nosniff',
            'Referrer-Policy'        => 'strict-origin-when-cross-origin',
            'Permissions-Policy'     => 'camera=(), microphone=(), payment=(), usb=(), geolocation=(self)',
        ];

        if ($csp = config('seguridad.cabeceras.csp_report_only')) {
            $cabeceras['Content-Security-Policy-Report-Only'] = $csp;
        }

        foreach ($cabeceras as $nombre => $valor) {
            if (! $respuesta->headers->has($nombre)) {
                $respuesta->headers->set($nombre, $valor);
            }
        }

        return $respuesta;
    }
}
