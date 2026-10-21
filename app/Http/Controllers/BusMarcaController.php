<?php

namespace App\Http\Controllers;

use App\Models\BusMarca;
use Illuminate\Http\Request;

class BusMarcaController extends Controller
{
    public function index(Request $request)
    {
        $marcas = BusMarca::listarMarcas($request->buscar, $request->input('estado', 1));
        return view('admin.transporte.maestros.bus_marcas.index', compact('marcas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre'      => 'required|string|max:100|unique:marca_vehiculo,nombre_mav',
            'descripcion' => 'nullable|string|max:255',
        ]);

        $marca = BusMarca::crearMarca($validated);

        return response()->json([
            'success' => true,
            'message' => 'Marca registrada correctamente.',
            'marca'   => [
                'id'          => $marca->id_mav,
                'nombre'      => $marca->nombre_mav,
                'descripcion' => $marca->descripcion_mav,
                'estado'      => true,
            ],
        ]);
    }

    public function update(Request $request, BusMarca $busMarca)
    {
        $validated = $request->validate([
            'nombre'      => 'required|string|max:100|unique:marca_vehiculo,nombre_mav,' . $busMarca->id_mav . ',id_mav',
            'descripcion' => 'nullable|string|max:255',
        ]);

        BusMarca::actualizarMarca($busMarca, $validated);
        $busMarca->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Marca actualizada correctamente.',
            'marca'   => [
                'id'          => $busMarca->id_mav,
                'nombre'      => $busMarca->nombre_mav,
                'descripcion' => $busMarca->descripcion_mav,
                'estado'      => $busMarca->estaActivo(),
            ],
        ]);
    }

    public function destroy(BusMarca $busMarca)
    {
        $busMarca->update(['status_id' => BusMarca::STATUS_INACTIVO]);
        return response()->json(['success' => true, 'message' => 'Marca inactivada correctamente.']);
    }

    public function activar(BusMarca $busMarca)
    {
        $busMarca->update(['status_id' => BusMarca::STATUS_ACTIVO]);
        return response()->json(['success' => true, 'message' => 'Marca activada correctamente.']);
    }
}