<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\BusModelo;
use App\Models\BusVehiculo;
use App\Support\Transporte\FormatoApiTransporte;

class BusModeloApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = BusModelo::with('busMarca')->orderBy('nombre_mov');

        if ($request->filled('marca_id')) {
            $query->where('mav_id', $request->integer('marca_id'));
        }

        if ($request->filled('estado')) {
            $query->where('status_id', $request->boolean('estado') ? 1 : 2);
        }

        $modelos = $query->get()->map(fn ($modelo) => FormatoApiTransporte::modelo($modelo))->values();

        return response()->json(['success' => true, 'data' => $modelos]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'marca_id' => 'required|integer|exists:marca_vehiculo,id_mav,deleted_at,NULL',
            'nombre'   => 'required|string|max:100',
        ]);

        $existe = BusModelo::where('mav_id', $validated['marca_id'])
            ->where('nombre_mov', ucfirst(trim($validated['nombre'])))
            ->exists();

        if ($existe) {
            return response()->json([
                'success' => false,
                'message' => 'Ya existe ese modelo para esta marca.',
            ], 422);
        }

        $modelo = BusModelo::create([
            'mav_id'     => $validated['marca_id'],
            'nombre_mov' => ucfirst(trim($validated['nombre'])),
            'status_id'  => 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Modelo creado correctamente.',
            'data'    => FormatoApiTransporte::modelo($modelo->refresh()->load('busMarca')),
        ], 201);
    }

    public function show(BusModelo $modelo): JsonResponse
    {
        return response()->json(['success' => true, 'data' => FormatoApiTransporte::modelo($modelo->load('busMarca'))]);
    }

    public function update(Request $request, BusModelo $modelo): JsonResponse
    {
        $validated = $request->validate([
            'marca_id' => 'required|integer|exists:marca_vehiculo,id_mav,deleted_at,NULL',
            'nombre'   => 'required|string|max:100',
        ]);

        $modelo->update([
            'mav_id'     => $validated['marca_id'],
            'nombre_mov' => ucfirst(trim($validated['nombre'])),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Modelo actualizado.',
            'data'    => FormatoApiTransporte::modelo($modelo->fresh()->load('busMarca')),
        ]);
    }

    public function toggle(BusModelo $modelo): JsonResponse
    {
        $modelo->update(['status_id' => (int) $modelo->status_id === 1 ? 2 : 1]);

        return response()->json([
            'success' => true,
            'message' => (int) $modelo->status_id === 1 ? 'Modelo activado.' : 'Modelo desactivado.',
            'data'    => FormatoApiTransporte::modelo($modelo->fresh()->load('busMarca')),
        ]);
    }

    public function destroy(BusModelo $modelo): JsonResponse
    {
        if (BusVehiculo::todasLasSedes()->where('mov_id', $modelo->id_mov)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar: tiene vehículos asociados.',
            ], 422);
        }

        $modelo->delete();

        return response()->json(['success' => true, 'message' => 'Modelo eliminado.']);
    }
}
