<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\BusTipoCombustible;
use App\Models\BusVehiculo;
use App\Support\Transporte\FormatoApiTransporte;

class BusTipoCombustibleApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = BusTipoCombustible::orderBy('nombre_tcb');

        if ($request->has('estado')) {
            $query->where('status_id', $request->boolean('estado') ? 1 : 2);
        }

        return response()->json([
            'success' => true,
            'data'    => $query->get()->map(fn ($tipo) => FormatoApiTransporte::tipoCombustible($tipo))->values(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nombre'      => 'required|string|max:100|unique:tipo_combustible,nombre_tcb,NULL,id_tcb,deleted_at,NULL',
            'descripcion' => 'nullable|string|max:255',
        ]);

        $tipo = BusTipoCombustible::create([
            'nombre_tcb'      => ucfirst(trim($validated['nombre'])),
            'descripcion_tcb' => $validated['descripcion'] ?? null,
            'status_id'       => 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tipo de combustible creado correctamente.',
            'data'    => FormatoApiTransporte::tipoCombustible($tipo->refresh()),
        ], 201);
    }

    public function show(BusTipoCombustible $combustible): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => FormatoApiTransporte::tipoCombustible($combustible),
        ]);
    }

    public function update(Request $request, BusTipoCombustible $combustible): JsonResponse
    {
        $validated = $request->validate([
            'nombre'      => "required|string|max:100|unique:tipo_combustible,nombre_tcb,{$combustible->id_tcb},id_tcb,deleted_at,NULL",
            'descripcion' => 'nullable|string|max:255',
        ]);

        $combustible->update([
            'nombre_tcb'      => ucfirst(trim($validated['nombre'])),
            'descripcion_tcb' => $validated['descripcion'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tipo de combustible actualizado correctamente.',
            'data'    => FormatoApiTransporte::tipoCombustible($combustible->fresh()),
        ]);
    }

    public function toggle(BusTipoCombustible $combustible): JsonResponse
    {
        $combustible->update(['status_id' => (int) $combustible->status_id === 1 ? 2 : 1]);

        return response()->json([
            'success' => true,
            'message' => (int) $combustible->status_id === 1 ? 'Tipo de combustible activado.' : 'Tipo de combustible desactivado.',
            'data'    => FormatoApiTransporte::tipoCombustible($combustible->fresh()),
        ]);
    }

    public function destroy(BusTipoCombustible $combustible): JsonResponse
    {
        if (BusVehiculo::todasLasSedes()->where('tcb_id', $combustible->id_tcb)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar: tiene vehículos asociados.',
            ], 422);
        }

        $combustible->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tipo de combustible eliminado.',
        ]);
    }
}
