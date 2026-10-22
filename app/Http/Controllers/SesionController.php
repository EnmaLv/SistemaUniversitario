<?php

namespace App\Http\Controllers;

use App\Http\Middleware\CerrarSesionPorInactividad;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SesionController extends Controller
{
    public function estado(Request $request): JsonResponse
    {
        return response()->json(['segundos_restantes' => CerrarSesionPorInactividad::segundosRestantes($request)]);
    }

    public function mantener(Request $request): JsonResponse
    {
        return response()->json(['segundos_restantes' => CerrarSesionPorInactividad::segundosRestantes($request)]);
    }
}
