<?php

namespace App\Http\Controllers;

use App\Models\BusVehiculo;
use App\Models\BusModelo;
use App\Models\BusMarca;
use App\Models\BusTipoCombustible;
use App\Models\Sede;
use Illuminate\Http\Request;

class BusVehiculoController extends Controller
{
    private function rules(?int $excludeId = null): array
    {
        return [
            'placa'                      => 'required|string|max:20|unique:vehiculo_transporte,placa_vtr,' . ($excludeId ?? 'NULL') . ',id_vtr',
            'modelo_id'                  => 'required|exists:modelo_vehiculo,id_mov',
            'anio'                       => 'required|integer|min:1990|max:' . date('Y'),
            'color'                      => 'required|string|max:50',
            'cantidad_pasajeros'         => 'required|integer|min:1|max:150',
            'peso'                       => 'required|string|max:50',
            'tipo_combustible_id'        => 'required|exists:tipo_combustible,id_tcb',
            'cantidad_cilindros'         => 'required|integer|min:1|max:10',
            'capacidad_tanque_litros'    => 'required|numeric|min:1|max:1000',
            'nivel_combustible_actual'   => 'required|numeric|min:0|lte:capacidad_tanque_litros',
            'consumo_urbano'             => 'required|numeric|min:0.001|max:5',
            'consumo_carretera'          => 'required|numeric|min:0.001|max:5',
            'consumo_relenti'            => 'required|numeric|min:0.001|max:50',
            'km_actual'                  => 'required|numeric|min:0|max:9999999',
            'km_proximo_mantenimiento'   => 'required|numeric|min:0|max:9999999',
            'estado'                     => 'required|in:disponible,en_ruta,mantenimiento,inactivo',
            'sed_id'                    => 'nullable|exists:sede,id_sed',
        ];
    }

    public function index(Request $request)
    {
        $vehiculos = BusVehiculo::listarVehiculos($request->buscar, $request->estado, $request->input('activo', 1));
        return view('admin.transporte.maestros.bus_vehiculos.index', compact('vehiculos'));
    }

    public function create()
    {
        $modelos = BusModelo::activos()->with('busMarca')->orderBy('nombre_mov')->get();
        $marcas  = BusMarca::activos()->orderBy('nombre_mav')->get();
        $tipos   = BusTipoCombustible::activos()->orderBy('nombre_tcb')->get();
        $sedes   = Sede::where('status_id', 1)->orderBy('nombre_sed')->get();
        return view('admin.transporte.maestros.bus_vehiculos.create', compact('modelos', 'marcas', 'tipos', 'sedes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());
        BusVehiculo::crearVehiculo($validated);
        return redirect()
            ->route('admin.transporte.maestros.bus_vehiculos.index')
            ->with('success', 'Vehículo registrado correctamente.');
    }

    public function edit(BusVehiculo $busVehiculo)
    {
        $modelos = BusModelo::activos()->with('busMarca')->orderBy('nombre_mov')->get();
        $marcas  = BusMarca::activos()->orderBy('nombre_mav')->get();
        $tipos   = BusTipoCombustible::activos()->orderBy('nombre_tcb')->get();
        $sedes   = Sede::where('status_id', 1)->orderBy('nombre_sed')->get();
        return view('admin.transporte.maestros.bus_vehiculos.edit', compact('busVehiculo', 'modelos', 'marcas', 'tipos', 'sedes'));
    }

    public function update(Request $request, BusVehiculo $busVehiculo)
    {
        $validated = $request->validate($this->rules($busVehiculo->id_vtr));
        BusVehiculo::actualizarVehiculo($busVehiculo, $validated);
        return redirect()
            ->route('admin.transporte.maestros.bus_vehiculos.index')
            ->with('success', 'Vehículo actualizado correctamente.');
    }

    public function destroy(BusVehiculo $busVehiculo)
    {
        $busVehiculo->update(['status_id' => BusVehiculo::STATUS_INACTIVO, 'estado_operativo_vtr' => 'inactivo']);
        return redirect()
            ->route('admin.transporte.maestros.bus_vehiculos.index')
            ->with('success', 'Vehículo inactivado correctamente.');
    }

    public function activar(BusVehiculo $busVehiculo)
    {
        $busVehiculo->update(['status_id' => BusVehiculo::STATUS_ACTIVO, 'estado_operativo_vtr' => 'disponible']);
        return redirect()
            ->route('admin.transporte.maestros.bus_vehiculos.index')
            ->with('success', 'Vehículo activado correctamente.');
    }

    public function verificarPlaca(Request $request)
    {
        $query = BusVehiculo::todasLasSedes()->withTrashed()->where('placa_vtr', strtoupper(trim($request->placa)));
        if ($request->exclude) {
            $query->where('id_vtr', '!=', $request->exclude);
        }
        return response()->json(['existe' => $query->exists()]);
    }
}