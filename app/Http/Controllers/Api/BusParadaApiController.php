<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\BusParada;

class BusParadaApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = BusParada::orderBy('nombre');

        if ($request->has('estado')) {
            $query->where('estado', $request->boolean('estado') ? 1 : 0);
        } else {
            $query->where('estado', 1);
        }

        $paradas = $query->get(['id', 'nombre', 'lat', 'lng', 'direccion']);

        return response()->json([
            'success' => true,
            'data'    => $paradas,
        ]);
    }
}