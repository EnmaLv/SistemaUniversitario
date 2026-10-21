<?php

namespace App\Http\Controllers;

use App\Models\BusTipoCombustible;
use Illuminate\Http\Request;

class BusTipoCombustibleController extends Controller
{
    public function index(Request $request)
    {
        $tipos = BusTipoCombustible::listarTipos($request->buscar, $request->input('estado', 1));
        return view('admin.transporte.maestros.bus_tipo_combustibles.index', compact('tipos'));
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre'      => 'required|string|max:100|unique:tipo_combustible,nombre_tcb',
            'descripcion' => 'nullable|string|max:255',
        ]);

        $tipo = BusTipoCombustible::crearTipo($validated);

        $response = [
            'success' => true,
            'message' => 'Tipo de combustible registrado correctamente.',
            'tipo'    => [
                'id'          => $tipo->id_tcb,
                'nombre'      => $tipo->nombre_tcb,
                'descripcion' => $tipo->descripcion_tcb,
                'estado'      => true,
            ],
        ];

        if ($request->expectsJson()) {
            return response()->json($response);
        }

        return redirect()
            ->route('admin.transporte.maestros.bus_tipo_combustibles.index')
            ->with('success', $response['message']);
    }

    public function update(Request $request, BusTipoCombustible $busTipoCombustible)
    {
        $validated = $request->validate([
            'nombre'      => 'required|string|max:100|unique:tipo_combustible,nombre_tcb,' . $busTipoCombustible->id_tcb . ',id_tcb',
            'descripcion' => 'nullable|string|max:255',
        ]);

        BusTipoCombustible::actualizarTipo($busTipoCombustible, $validated);
        $busTipoCombustible->refresh();

        $response = [
            'success' => true,
            'message' => 'Tipo de combustible actualizado correctamente.',
            'tipo'    => [
                'id'          => $busTipoCombustible->id_tcb,
                'nombre'      => $busTipoCombustible->nombre_tcb,
                'descripcion' => $busTipoCombustible->descripcion_tcb,
                'estado'      => $busTipoCombustible->estaActivo(),
            ],
        ];

        if ($request->expectsJson()) {
            return response()->json($response);
        }

        return redirect()
            ->route('admin.transporte.maestros.bus_tipo_combustibles.index')
            ->with('success', $response['message']);
    }

    public function destroy(Request $request, BusTipoCombustible $busTipoCombustible)
    {
        $busTipoCombustible->update(['status_id' => BusTipoCombustible::STATUS_INACTIVO]);
        $message = 'Tipo de combustible inactivado correctamente.';

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return redirect()
            ->route('admin.transporte.maestros.bus_tipo_combustibles.index')
            ->with('success', $message);
    }

    public function activar(Request $request, BusTipoCombustible $busTipoCombustible)
    {
        $busTipoCombustible->update(['status_id' => BusTipoCombustible::STATUS_ACTIVO]);
        $message = 'Tipo de combustible activado correctamente.';

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return redirect()
            ->route('admin.transporte.maestros.bus_tipo_combustibles.index')
            ->with('success', $message);
    }

    private function isJsonRequest(Request $request): bool
    {
        return $request->expectsJson() || $request->ajax();
    }
}