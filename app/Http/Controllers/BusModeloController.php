<?php

namespace App\Http\Controllers;

use App\Models\BusModelo;
use App\Models\BusMarca;
use Illuminate\Http\Request;

class BusModeloController extends Controller
{
    public function index(Request $request)
    {
        $modelos = BusModelo::listarModelos($request->buscar, $request->input('estado', 1));
        $marcas  = BusMarca::activos()->orderBy('nombre_mav')->get();

        return view('admin.transporte.maestros.bus_modelos.index', compact('modelos', 'marcas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'marca_id' => 'required|exists:marca_vehiculo,id_mav',
            'nombre'       => 'required|string|max:100|unique:modelo_vehiculo,nombre_mov',
            'descripcion'  => 'nullable|string|max:255',
        ]);

        $modelo = BusModelo::crearModelo($validated);
        $modelo->load('busMarca');

        return response()->json([
            'success' => true,
            'message' => 'Modelo registrado correctamente.',
            'modelo'  => [
                'id'          => $modelo->id_mov,
                'nombre'      => $modelo->nombre_mov,
                'descripcion' => $modelo->descripcion_mov,
                'marca_id'    => $modelo->mav_id,
                'marca_nombre' => $modelo->busMarca->nombre_mav,
                'estado'      => true,
            ],
        ]);
    }

    public function update(Request $request, BusModelo $busModelo)
    {
        $validated = $request->validate([
            'marca_id' => 'required|exists:marca_vehiculo,id_mav',
            'nombre'       => 'required|string|max:100|unique:modelo_vehiculo,nombre_mov,' . $busModelo->id_mov . ',id_mov',
            'descripcion'  => 'nullable|string|max:255',
        ]);

        BusModelo::actualizarModelo($busModelo, $validated);
        $busModelo->refresh()->load('busMarca');

        return response()->json([
            'success' => true,
            'message' => 'Modelo actualizado correctamente.',
            'modelo'  => [
                'id'          => $busModelo->id_mov,
                'nombre'      => $busModelo->nombre_mov,
                'descripcion' => $busModelo->descripcion_mov,
                'marca_id'    => $busModelo->mav_id,
                'marca_nombre' => $busModelo->busMarca->nombre_mav,
                'estado'      => $busModelo->estaActivo(),
            ],
        ]);
    }

    public function destroy(BusModelo $busModelo)
    {
        $busModelo->update(['status_id' => BusModelo::STATUS_INACTIVO]);
        return response()->json(['success' => true, 'message' => 'Modelo inactivado correctamente.']);
    }

    public function activar(BusModelo $busModelo)
    {
        $busModelo->update(['status_id' => BusModelo::STATUS_ACTIVO]);
        return response()->json(['success' => true, 'message' => 'Modelo activado correctamente.']);
    }
}
