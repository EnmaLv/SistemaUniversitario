<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\BusParada;
use App\Support\Transporte\FormatoApiTransporte;

class BusParadaApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = BusParada::orderBy('nombre_ptr');

        if ($request->has('estado')) {
            $query->where('status_id', $request->boolean('estado') ? 1 : 2);
        } else {
            $query->where('status_id', 1);
        }

        $paradas = $query->get(['id_ptr', 'nombre_ptr', 'lat_ptr', 'lng_ptr', 'direccion_ptr'])
            ->map(fn ($parada) => FormatoApiTransporte::paradaBasica($parada))
            ->values();

        return response()->json([
            'success' => true,
            'data'    => $paradas,
        ]);
    }
}
