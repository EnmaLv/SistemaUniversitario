<?php

namespace App\Http\Middleware;

use App\Models\Usuario;
use Closure;
use Illuminate\Http\Request;

class VerificarPermisoApi
{
    public function handle(Request $request, Closure $next, string $vista)
    {
        if ($request->isMethodSafe()) {
            return $next($request);
        }

        $accion = match (true) {
            str_ends_with($request->path(), '/toggle') => 'activar',
            $request->isMethod('POST')                 => 'crear',
            $request->isMethod('DELETE')               => 'eliminar',
            default                                    => 'editar',
        };

        $usuario = $request->user();

        abort_unless($usuario instanceof Usuario && $usuario->puede($vista, $accion), 403, 'No tienes permiso para realizar esta acción.');

        return $next($request);
    }
}
