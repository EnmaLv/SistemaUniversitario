<?php

namespace App\Http\Middleware;

use App\Models\Usuario;
use Closure;
use Illuminate\Http\Request;

class ExigirRolPsicologo
{
    public function handle(Request $request, Closure $next)
    {
        $usuario = $request->user();

        abort_unless(
            $usuario instanceof Usuario && $usuario->esPsicologo(),
            403,
            'Por confidencialidad profesional, solo el personal con rol Psicólogo puede gestionar el módulo de psicología.'
        );

        return $next($request);
    }
}
