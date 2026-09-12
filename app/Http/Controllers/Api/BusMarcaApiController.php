<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\BusMarca;

class BusMarcaApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = BusMarca::orderBy('nombre');

        if ($request->has('estado')) {
            $query->where('estado', $request->boolean('estado') ? 1 : 0);
        }

        $marcas = $query->get();

        return response()->json([
            'success' => true,
            'data'    => $marcas,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100|unique:marcas,nombre',
        ]);

        $marca = BusMarca::create([
            'nombre' => ucfirst(trim($validated['nombre'])),
            'estado' => 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Marca creada correctamente.',
            'data'    => $marca,
        ], 201);
    }

    public function show(BusMarca $marca): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => $marca,
        ]);
    }

    public function update(Request $request, BusMarca $marca): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => "required|string|max:100|unique:marcas,nombre,{$marca->id}",
        ]);

        $marca->update([
            'nombre' => ucfirst(trim($validated['nombre'])),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Marca actualizada correctamente.',
            'data'    => $marca->fresh(),
        ]);
    }

    public function toggle(BusMarca $marca): JsonResponse
    {
        if ($marca->modelos()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede inactivar: esta marca tiene modelos asociados.',
            ], 422);
        }

        $marca->update(['estado' => $marca->estado ? 0 : 1]);

        return response()->json([
            'success' => true,
            'message' => $marca->estado ? 'Marca activada.' : 'Marca desactivada.',
            'data'    => $marca->fresh(),
        ]);
    }
}
