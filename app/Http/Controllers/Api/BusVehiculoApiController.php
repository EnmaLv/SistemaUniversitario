<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BusVehiculo;
use App\Support\Transporte\FormatoApiTransporte;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BusVehiculoApiController extends Controller
{
    private const RELACIONES = ['modelo.busMarca', 'tipoCombustible', 'sede'];

    private function rules(?int $excludeId = null): array
    {
        return [
            'placa'                    => 'required|string|max:20|unique:vehiculo_transporte,placa_vtr,' . ($excludeId ?? 'NULL') . ',id_vtr',
            'modelo_id'                => 'required|exists:modelo_vehiculo,id_mov,deleted_at,NULL',
            'anio'                     => 'required|integer|min:1990|max:' . date('Y'),
            'color'                    => 'required|string|max:50',
            'cantidad_pasajeros'       => 'required|integer|min:1|max:150',
            'tipo_combustible_id'      => 'required|exists:tipo_combustible,id_tcb,deleted_at,NULL',
            'cantidad_cilindros'       => 'required|integer|min:1|max:10',
            'capacidad_tanque_litros'  => 'required|numeric|min:1|max:1000',
            'consumo_litros_km'        => 'required|numeric|min:0.001|max:5',
            'km_actual'                => 'required|numeric|min:0|max:9999999',
            'km_proximo_mantenimiento' => 'required|numeric|min:0|max:9999999',
            'conductor_id'             => 'nullable|exists:user,id_use',
            'sed_id'                  => 'required|exists:sede,id_sed',
            'estado'                   => 'required|in:disponible,en_ruta,mantenimiento,inactivo',
        ];
    }

    private function columnas(array $validated): array
    {
        return [
            'placa_vtr'                    => strtoupper(trim($validated['placa'])),
            'mov_id'                       => $validated['modelo_id'],
            'anio_vtr'                     => $validated['anio'],
            'color_vtr'                    => $validated['color'],
            'capacidad_pasajeros_vtr'      => $validated['cantidad_pasajeros'],
            'tcb_id'                       => $validated['tipo_combustible_id'],
            'cantidad_cilindros_vtr'       => $validated['cantidad_cilindros'],
            'capacidad_tanque_litros_vtr'  => $validated['capacidad_tanque_litros'],
            'km_actual_vtr'                => $validated['km_actual'],
            'km_proximo_mantenimiento_vtr' => $validated['km_proximo_mantenimiento'],
            'sed_id'                       => $validated['sed_id'],
            'estado_operativo_vtr'         => $validated['estado'],
        ];
    }

    public function index(Request $request): JsonResponse
    {
        $status = $request->has('activo') ? ($request->boolean('activo') ? 1 : 2) : 1;
        $buscar = trim((string) $request->buscar);

        $vehiculos = BusVehiculo::query()
            ->with(self::RELACIONES)
            ->when($buscar !== '', function ($q) use ($buscar) {
                $patron = '%' . mb_strtolower($buscar) . '%';
                $q->where(fn ($s) => $s->whereRaw('LOWER(placa_vtr) LIKE ?', [$patron])
                    ->orWhereRaw('LOWER(color_vtr) LIKE ?', [$patron]));
            })
            ->where('status_id', $status)
            ->orderBy('placa_vtr')
            ->paginate(10)
            ->withQueryString()
            ->through(fn ($vehiculo) => FormatoApiTransporte::vehiculo($vehiculo));

        return response()->json([
            'success' => true,
            'data'    => $vehiculos,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate($this->rules());

        $vehiculo = BusVehiculo::create($this->columnas($validated) + ['status_id' => 1]);

        $vehiculo->refresh()->load(self::RELACIONES);

        return response()->json([
            'success' => true,
            'message' => 'Vehículo registrado correctamente.',
            'data'    => FormatoApiTransporte::vehiculo($vehiculo, true),
        ], 201);
    }

    public function show(BusVehiculo $vehiculo): JsonResponse
    {
        $vehiculo->load(self::RELACIONES);

        return response()->json([
            'success' => true,
            'data'    => FormatoApiTransporte::vehiculo($vehiculo, true),
        ]);
    }

    public function update(Request $request, BusVehiculo $vehiculo): JsonResponse
    {
        $validated = $request->validate($this->rules($vehiculo->id_vtr));

        $vehiculo->update($this->columnas($validated));

        return response()->json([
            'success' => true,
            'message' => 'Vehículo actualizado correctamente.',
            'data'    => FormatoApiTransporte::vehiculo($vehiculo->fresh()),
        ]);
    }

    public function toggle(BusVehiculo $vehiculo): JsonResponse
    {
        $activar = (int) $vehiculo->status_id !== 1;

        $vehiculo->update([
            'status_id'            => $activar ? 1 : 2,
            'estado_operativo_vtr' => $activar ? 'disponible' : 'inactivo',
        ]);

        return response()->json([
            'success' => true,
            'message' => $activar ? 'Vehículo activado correctamente.' : 'Vehículo inactivado correctamente.',
            'data'    => FormatoApiTransporte::vehiculo($vehiculo->fresh()->load(self::RELACIONES), true),
        ]);
    }

    public function destroy(BusVehiculo $vehiculo): JsonResponse
    {
        if ($vehiculo->estado_operativo_vtr === 'en_ruta') {
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
