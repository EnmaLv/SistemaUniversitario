<?php

namespace App\Http\Middleware;

use App\Support\Permisos\MapaRutas;
use App\Support\Permisos\ResolutorAccion;
use Closure;
use Illuminate\Http\Request;

class CheckMenuPermission
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        if ($user->esSuperAdmin()) {
            return $next($request);
        }

        $routeName   = $request->route()?->getName();
        $currentPath = ltrim($request->path(), '/');

        if (MapaRutas::esLibre($routeName, $currentPath)) {
            return $next($request);
        }

        $vistas = MapaRutas::vistasDeRuta($routeName, $currentPath);

        if (empty($vistas)) {
            abort(403, 'Esta sección no está registrada en el sistema de permisos.');
        }

        $accion = ResolutorAccion::desdeRuta($routeName, $request->method());

        if ($user->puedeAlguna($vistas, $accion)) {
            return $next($request);
        }

        abort(403, 'No tienes permiso para ' . mb_strtolower(ResolutorAccion::etiqueta($accion)) . ' en esta sección.');
    }
}
