<?php

namespace App\Http\Middleware;

use App\Models\Usuario;
use App\Support\Sedes\SedeActiva;
use Closure;
use Illuminate\Http\Request;

class EstablecerSedeActiva
{
    public function handle(Request $request, Closure $next)
    {
        $usuario = $request->user();

        if ($usuario instanceof Usuario && $request->hasSession() && (int) $request->session()->get('sede_inicializada_para') !== (int) $usuario->getKey()) {
            SedeActiva::inicializar($usuario);
            $request->session()->put('sede_inicializada_para', (int) $usuario->getKey());
        }

        return $next($request);
    }
}
