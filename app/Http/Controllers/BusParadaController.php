<?php

namespace App\Http\Controllers;

use App\Models\BusParada;
use Illuminate\Http\Request;

class BusParadaController extends Controller
{
    private function rules(int $excludeId = null): array
    {
        return [
            'nombre'    => 'required|string|max:100|unique:bus_paradas,nombre,' . $excludeId,
            'lat'       => 'required|numeric|between:-90,90',
            'lng'       => 'required|numeric|between:-180,180',
            'direccion' => 'nullable|string|max:255',
        ];
    }

    public function index(Request $request)
    {
        $paradas = BusParada::listarParadas($request->buscar, $request->input('estado', 1));
        return view('admin.transporte.maestros.bus_paradas.index', compact('paradas'));
    }

    public function create()
    {
        return view('admin.transporte.maestros.bus_paradas.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());
        BusParada::crearParada($validated);

        return redirect()->route('admin.transporte.maestros.bus_paradas.index')
            ->with('success', 'Parada registrada correctamente.');
    }

    public function edit(BusParada $busParada)
    {
        return view('admin.transporte.maestros.bus_paradas.edit', compact('busParada'));
    }

    public function update(Request $request, BusParada $busParada)
    {
        $validated = $request->validate($this->rules($busParada->id));
        BusParada::actualizarParada($busParada, $validated);

        return redirect()->route('admin.transporte.maestros.bus_paradas.index')
            ->with('success', 'Parada actualizada correctamente.');
    }

    public function destroy(BusParada $busParada)
    {
        $busParada->update(['estado' => 0]);
        return redirect()
            ->route('admin.transporte.maestros.bus_paradas.index')
            ->with('success', 'Parada inactivada correctamente.');
    }

    public function activar(BusParada $busParada)
    {
        $busParada->update(['estado' => 1]);
        return redirect()
            ->route('admin.transporte.maestros.bus_paradas.index')
            ->with('success', 'Parada activada correctamente.');
    }

    public function verificarNombre(Request $request)
    {
        $query = BusParada::where('nombre', trim($request->nombre));
        if ($request->exclude) {
            $query->where('id', '!=', $request->exclude);
        }
        return response()->json(['existe' => $query->exists()]);
    }
}
