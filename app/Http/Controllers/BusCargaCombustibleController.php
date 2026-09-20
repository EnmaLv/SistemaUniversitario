<?php

namespace App\Http\Controllers;

use App\Models\BusCargaCombustible;
use App\Models\BusVehiculo;
use App\Models\BusViaje;
use App\Models\BusTipoCombustible;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BusCargaCombustibleController extends Controller
{
    private function rules(int $excludeId = null): array
    {
        return [
            'bus_vehiculo_id'         => 'required|exists:bus_vehiculos,id',
            'bus_viaje_id'            => 'required|exists:bus_viajes,id',
            'fecha'                   => 'required|date|before_or_equal:today',
            'litros'                  => 'required|numeric|min:0.1|max:1000',
            'precio_litros'           => 'required|numeric|min:0.01|max:999999',
            'km_al_cargar'            => 'required|numeric|min:0|max:9999999',
            'observaciones'           => 'nullable|string|max:2000',
        ];
    }

    public function index(Request $request)
    {
        $cargas    = BusCargaCombustible::listarCargas($request->buscar, $request->input('vehiculo_id'));
        $vehiculos = BusVehiculo::where('activo', 1)->orderBy('placa')->get();
        return view('admin.transporte.maestros.bus_carga_combustibles.index', compact('cargas', 'vehiculos'));
    }

    public function create()
    {
        $vehiculos = BusVehiculo::where('activo', 1)->orderBy('placa')->get();
        $viajes    = BusViaje::with(['vehiculo', 'ruta'])->orderBy('created_at', 'desc')->take(50)->get();
        $tipos     = BusTipoCombustible::where('estado', 1)->orderBy('nombre')->get();
        return view('admin.transporte.maestros.bus_carga_combustibles.create', compact('vehiculos', 'viajes', 'tipos'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());

        DB::transaction(function () use ($validated) {
            BusCargaCombustible::crearCarga($validated);

            $vehiculo = BusVehiculo::find($validated['bus_vehiculo_id']);
            if ($vehiculo && $validated['km_al_cargar'] > $vehiculo->km_actual) {
                $vehiculo->update(['km_actual' => $validated['km_al_cargar']]);
            }
        });

        return redirect()
            ->route('admin.transporte.maestros.bus_carga_combustibles.index')
            ->with('success', 'Carga de combustible registrada correctamente.');
    }

    public function edit(BusCargaCombustible $busCargaCombustible)
    {
        $vehiculos = BusVehiculo::where('activo', 1)->orderBy('placa')->get();
        $viajes    = BusViaje::with(['vehiculo', 'ruta'])->orderBy('created_at', 'desc')->take(50)->get();
        $tipos     = BusTipoCombustible::where('estado', 1)->orderBy('nombre')->get();
        return view('admin.transporte.maestros.bus_carga_combustibles.edit',
            compact('busCargaCombustible', 'vehiculos', 'viajes', 'tipos'));
    }

    public function update(Request $request, BusCargaCombustible $busCargaCombustible)
    {
        $validated = $request->validate($this->rules($busCargaCombustible->id));

        DB::transaction(function () use ($busCargaCombustible, $validated) {
            BusCargaCombustible::actualizarCarga($busCargaCombustible, $validated);

            $vehiculo = BusVehiculo::find($validated['bus_vehiculo_id']);
            if ($vehiculo && $validated['km_al_cargar'] > $vehiculo->km_actual) {
                $vehiculo->update(['km_actual' => $validated['km_al_cargar']]);
            }
        });

        return redirect()
            ->route('admin.transporte.maestros.bus_carga_combustibles.index')
            ->with('success', 'Carga de combustible actualizada correctamente.');
    }

    public function destroy(BusCargaCombustible $busCargaCombustible)
    {
        $busCargaCombustible->delete();
        return redirect()
            ->route('admin.transporte.maestros.bus_carga_combustibles.index')
            ->with('success', 'Registro de carga eliminado correctamente.');
    }
}
