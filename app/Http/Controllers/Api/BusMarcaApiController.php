<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\BusMarca;
use App\Models\BusModelo;
use App\Support\Transporte\FormatoApiTransporte;

class BusMarcaApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = BusMarca::orderBy('nombre_mav');

        if ($request->has('estado')) {
            $query->where('status_id', $request->boolean('estado') ? 1 : 2);
        }

        $marcas = $query->get()->map(fn ($marca) => FormatoApiTransporte::marca($marca))->values();

        return response()->json([
            'success' => true,
            'data'    => $marcas,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100|unique:marca_vehiculo,nombre_mav,NULL,id_mav,deleted_at,NULL',
        ]);

        $marca = BusMarca::create([
            'nombre_mav' => ucfirst(trim($validated['nombre'])),
            'status_id'  => 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Marca creada correctamente.',
            'data'    => FormatoApiTransporte::marca($marca->refresh()),
        ], 201);
    }

    public function show(BusMarca $marca): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => FormatoApiTransporte::marca($marca),
        ]);
    }

    public function update(Request $request, BusMarca $marca): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => "required|string|max:100|unique:marca_vehiculo,nombre_mav,{$marca->id_mav},id_mav,deleted_at,NULL",
        ]);

        $marca->update([
            'nombre_mav' => ucfirst(trim($validated['nombre'])),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Marca actualizada correctamente.',
            'data'    => FormatoApiTransporte::marca($marca->fresh()),
        ]);
    }

    public function toggle(BusMarca $marca): JsonResponse
    {
        if (BusModelo::where('mav_id', $marca->id_mav)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede inactivar: esta marca tiene modelos asociados.',
            ], 422);
        }

        $marca->update(['status_id' => (int) $marca->status_id === 1 ? 2 : 1]);

        return response()->json([
            'success' => true,
            'message' => (int) $marca->status_id === 1 ? 'Marca activada.' : 'Marca desactivada.',
            'data'    => FormatoApiTransporte::marca($marca->fresh()),
        ]);
    }
}
