<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BusVehiculo;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BusVehiculoApiController extends Controller
{
    private function rules(int $excludeId = null): array
    {
        return [
            'placa'                    => 'required|string|max:20|unique:vehiculos,placa,' . $excludeId,
            'modelo_id'                => 'required|exists:modelos,id',
            'anio'                     => 'required|integer|min:1990|max:' . date('Y'),
            'color'                    => 'required|string|max:50',
            'cantidad_pasajeros'       => 'required|integer|min:1|max:150',
            'tipo_combustible_id'      => 'required|exists:tipo_combustibles,id',
            'cantidad_cilindros'       => 'required|integer|min:1|max:10',
            'capacidad_tanque_litros'  => 'required|numeric|min:1|max:1000',
            'consumo_litros_km'        => 'required|numeric|min:0.001|max:5',
            'km_actual'                => 'required|numeric|min:0|max:9999999',
            'km_proximo_mantenimiento' => 'required|numeric|min:0|max:9999999',
            'conductor_id'             => 'nullable|exists:usuarios,id_usuario',
            'sede_id'                  => 'required|exists:sede,id',
            'estado'                   => 'required|in:disponible,en_ruta,mantenimiento,inactivo',
        ];
    }

    public function index(Request $request): JsonResponse
    {
        $activo = $request->has('activo') ? ($request->boolean('activo') ? 1 : 0) : 1;
        $vehiculos = BusVehiculo::listarVehiculos($request->buscar, null, $activo);

        return response()->json([
            'success' => true,
            'data'    => $vehiculos,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate($this->rules());
        $validated['placa'] = strtoupper(trim($validated['placa']));
        $validated['activo'] = 1;

        $vehiculo = BusVehiculo::crearVehiculo($validated);

        $vehiculo->load(['modelo.busMarca', 'tipoCombustible', 'sede', 'conductor']);

        return response()->json([
            'success' => true,
            'message' => 'Vehículo registrado correctamente.',
            'data'    => $vehiculo,
        ], 201);
    }

    public function show(BusVehiculo $vehiculo): JsonResponse
    {
        $vehiculo->load(['modelo.busMarca', 'tipoCombustible', 'sede', 'conductor']);

        return response()->json([
            'success' => true,
            'data'    => $vehiculo,
        ]);
    }

    public function update(Request $request, BusVehiculo $vehiculo): JsonResponse
    {
        $validated = $request->validate($this->rules($vehiculo->id));

        $validated['placa'] = strtoupper(trim($validated['placa']));

        BusVehiculo::actualizarVehiculo($vehiculo, $validated);
        $vehiculo->load(['modelo.busMarca', 'tipoCombustible', 'sede', 'conductor']);

        return response()->json([
            'success' => true,
            'message' => 'Vehículo actualizado correctamente.',
            'data'    => $vehiculo->fresh(),
        ]);
    }

    public function toggle(BusVehiculo $vehiculo): JsonResponse
    {
        $nuevoEstadoActivo = $vehiculo->activo ? 0 : 1;
        $nuevoEstadoTexto = $nuevoEstadoActivo ? 'disponible' : 'inactivo';

        $vehiculo->update([
            'activo' => $nuevoEstadoActivo,
            'estado' => $nuevoEstadoTexto
        ]);

        return response()->json([
            'success' => true,
            'message' => $vehiculo->activo ? 'Vehículo activado correctamente.' : 'Vehículo inactivado correctamente.',
            'data'    => $vehiculo->fresh()->load(['modelo.busMarca', 'tipoCombustible', 'sede', 'conductor']),
        ]);
    }

    public function destroy(BusVehiculo $vehiculo): JsonResponse
    {
        if ($vehiculo->estado === 'en_ruta') {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar el vehículo mientras se encuentre En Ruta.',
            ], 422);
        }
        $vehiculo->delete();

        return response()->json([
            'success' => true,
            'message' => 'Vehículo eliminado del sistema permanentemente.',
        ]);
    }
}
