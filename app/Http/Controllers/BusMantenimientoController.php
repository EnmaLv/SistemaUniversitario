<?php

namespace App\Http\Controllers;

use App\Models\BusMantenimiento;
use App\Models\BusVehiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BusMantenimientoController extends Controller
{
    private function rules(?int $excludeId = null): array
    {
        return [
            'bus_vehiculo_id' => 'required|exists:vehiculo_transporte,id_vtr',
            'tipo'            => 'required|in:preventivo,correctivo',
            'titulo'          => 'required|string|max:150',
            'descripcion'     => 'nullable|string|max:2000',
            'costo'           => 'nullable|numeric|min:0|max:9999999',
            'fecha'           => 'required|date|before_or_equal:today',
            'km_al_servicio'  => 'nullable|numeric|min:0|max:9999999',
            'proximo_km'      => 'nullable|numeric|min:0|max:9999999',
            'proxima_fecha'   => 'nullable|date|after:fecha',
            'estado'          => 'required|in:pendiente,en_proceso,completado',
        ];
    }

    public function index(Request $request)
    {
        $mantenimientos = BusMantenimiento::listarMantenimientos(
            $request->buscar,
            $request->input('estado')
        );
        return view('admin.transporte.maestros.bus_mantenimientos.index', compact('mantenimientos'));
    }

    public function create()
    {
        $vehiculos = BusVehiculo::activos()->with('modelo')->orderBy('placa_vtr')->get();
        return view('admin.transporte.maestros.bus_mantenimientos.create', compact('vehiculos'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());
        BusMantenimiento::crearMantenimiento($validated, Auth::id());
        return redirect()
            ->route('admin.transporte.maestros.bus_mantenimientos.index')
            ->with('success', 'Mantenimiento registrado correctamente.');
    }

    public function edit(BusMantenimiento $busMantenimiento)
    {
        $vehiculos = BusVehiculo::activos()->with('modelo')->orderBy('placa_vtr')->get();
        return view('admin.transporte.maestros.bus_mantenimientos.edit',
            compact('busMantenimiento', 'vehiculos'));
    }

    public function update(Request $request, BusMantenimiento $busMantenimiento)
    {
        $validated = $request->validate($this->rules($busMantenimiento->id_mnv));
        BusMantenimiento::actualizarMantenimiento($busMantenimiento, $validated);
        return redirect()
            ->route('admin.transporte.maestros.bus_mantenimientos.index')
            ->with('success', 'Mantenimiento actualizado correctamente.');
    }

    public function destroy(BusMantenimiento $busMantenimiento)
    {
        $busMantenimiento->delete();
        return redirect()
            ->route('admin.transporte.maestros.bus_mantenimientos.index')
            ->with('success', 'Mantenimiento eliminado correctamente.');
    }
}