<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BusViaje;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Models\BusGpsLog;

class BusViajeApiController extends Controller
{

    public function registrarGps(Request $request, BusViaje $viaje): JsonResponse
    {
        if ($viaje->estado !== 'en_curso') {
            $this->eliminarBusDeFirebase((string)$viaje->id);

            return response()->json([
                'success' => false,
                'message' => 'El viaje ya no está en curso. Transmisión detenida.',
                'code'    => 'VIAJE_NO_ACTIVO'
            ], 422);
        }

        $validated = $request->validate([
            'lat'       => 'required|numeric',
            'lng'       => 'required|numeric',
            'velocidad' => 'nullable|numeric',
            'heading'   => 'nullable|numeric',
        ]);

        $viaje->update([
            'ultima_lat' => $validated['lat'],
            'ultima_lng' => $validated['lng'],
        ]);

        return response()->json(['success' => true]);
    }

    public function obtenerPosicion(BusViaje $viaje): JsonResponse
    {
        $ultimoLog = $viaje->gpsLogs()->latest('id')->first();

        return response()->json([
            'success'          => true,
            'latitud'          => $ultimoLog ? (float) $ultimoLog->lat : null,
            'longitud'         => $ultimoLog ? (float) $ultimoLog->lng : null,
            'velocidad'        => $ultimoLog ? (float) $ultimoLog->velocidad : 0,
            'pasajeros'        => $viaje->pasajeros,
            'distancia_km'     => $viaje->distancia_km,
            'litros_gastados'  => $viaje->litros_gastados,
            'estado'           => $viaje->estado,
            'fecha_registro'   => $ultimoLog ? $ultimoLog->created_at->toISOString() : null,
            'actualizado_hace' => $ultimoLog ? $ultimoLog->created_at->diffForHumans() : 'Sin registros',
        ]);
    }

    public function miViajeActivo(Request $request): JsonResponse
    {
        $usuario = $request->user();

        $viaje = BusViaje::delConductor($usuario->id_usuario)
            ->whereIn('estado', ['programado', 'en_curso'])
            ->with([
                'vehiculo.tipoCombustible',
                'busRuta.paradas' => fn($q) => $q->orderBy('orden', 'asc')
            ])
            ->first();

        if (!$viaje) {
            return response()->json([
                'success' => true,
                'message' => 'No tienes ningún viaje activo o programado en este momento.',
                'data'    => null,
            ]);
        }

        return response()->json([
            'success' => true,
            'data'    => $viaje,
        ]);
    }

    public function iniciar(Request $request, BusViaje $viaje): JsonResponse
    {
        if ($viaje->conductor_id !== $request->user()->id_usuario) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para iniciar este viaje.',
            ], 403);
        }

        if ($viaje->estado !== 'programado') {
            return response()->json([
                'success' => false,
                'message' => "No se puede iniciar el viaje porque su estado es '{$viaje->estado}'.",
            ], 422);
        }

        $kmInicio = $viaje->vehiculo->km_actual ?? 0;

        $viaje->update([
            'estado'       => 'en_curso',
            'fecha_inicio' => Carbon::now(),
            'km_inicio'    => $kmInicio,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'El viaje ha iniciado correctamente.',
            'data'    => $viaje->fresh(['vehiculo', 'busRuta.paradas']),
        ]);
    }

    private function eliminarBusDeFirebase(string $viajeId): void
    {
        try {
            if (class_exists('\Kreait\Laravel\Firebase\Facades\Firebase')) {
                \Kreait\Laravel\Firebase\Facades\Firebase::firestore()
                    ->database()
                    ->collection('buses_activos')
                    ->document($viajeId)
                    ->delete();
            }
        } catch (\Exception $e) {
            Log::error("Error eliminando bus $viajeId de Firestore: " . $e->getMessage());
        }
    }
    

    public function finalizar(Request $request, BusViaje $viaje): JsonResponse
    {
        if ($viaje->conductor_id !== $request->user()->id_usuario) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para finalizar este viaje.',
            ], 403);
        }

        if ($viaje->estado !== 'en_curso') {
            return response()->json([
                'success' => false,
                'message' => 'Solo se pueden finalizar viajes que estén en curso.',
            ], 422);
        }

        $validated = $request->validate([
            'km_fin'          => 'required|numeric|gte:' . $viaje->km_inicio,
            'pasajeros'       => 'required|integer|min:0',
            'litros_gastados' => 'nullable|numeric|min:0',
            'hubo_desvio'     => 'nullable|boolean',
            'motivo_desvio'   => 'nullable|required_if:hubo_desvio,true|string|max:255',
        ], [
            'km_fin.gte'                  => 'El kilometraje final no puede ser menor al de inicio (' . $viaje->km_inicio . ' km).',
            'motivo_desvio.required_if' => 'Debe indicar el motivo del desvío.',
        ]);

        $kmFin = $validated['km_fin'];
        $distanciaRecorrida = $kmFin - $viaje->km_inicio;

        $viaje->update([
            'estado'          => 'finalizado',
            'km_fin'          => $kmFin,
            'distancia_km'    => $distanciaRecorrida,
            'pasajeros'       => $validated['pasajeros'],
            'litros_gastados' => $validated['litros_gastados'] ?? 0,
            'hubo_desvio'     => $validated['hubo_desvio'] ?? false,
            'motivo_desvio'   => $validated['motivo_desvio'] ?? null,
        ]);

        if ($viaje->vehiculo) {
            $viaje->vehiculo->update([
                'km_actual' => $kmFin,
            ]);
        }

        $this->eliminarBusDeFirebase((string)$viaje->id);

        return response()->json([
            'success' => true,
            'message' => 'Viaje finalizado exitosamente.',
            'data'    => $viaje->fresh(),
        ]);
    }

    public function cancelar(Request $request, BusViaje $viaje): JsonResponse
    {
        if ($viaje->conductor_id !== $request->user()->id_usuario) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para cancelar este viaje.',
            ], 403);
        }

        if (!in_array($viaje->estado, ['programado', 'en_curso'])) {
            return response()->json([
                'success' => false,
                'message' => "No se puede cancelar el viaje porque su estado es '{$viaje->estado}'.",
            ], 422);
        }

        $validated = $request->validate([
            'motivo_cancelacion' => 'required|string|min:5|max:500',
        ], [
            'motivo_cancelacion.required' => 'Debe ingresar una razón para cancelar el viaje.',
            'motivo_cancelacion.min'      => 'La razón debe contener al menos 5 caracteres.',
        ]);

        $viaje->update([
            'estado'             => 'cancelado',
            'motivo_cancelacion' => $validated['motivo_cancelacion'],
        ]);

        $this->eliminarBusDeFirebase((string)$viaje->id);

        return response()->json([
            'success' => true,
            'message' => 'El viaje ha sido cancelado exitosamente.',
            'data'    => $viaje->fresh(),
        ]);
    }

    public function historial(Request $request): JsonResponse
    {
        $viajes = BusViaje::delConductor($request->user()->id_usuario)
            ->where('estado', 'finalizado')
            ->with(['vehiculo', 'busRuta'])
            ->orderBy('updated_at', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data'    => $viajes,
        ]);
    }

    public function cartelera(): JsonResponse
    {
        $hoy = Carbon::today();

        $viajes = BusViaje::whereIn('estado', ['en_curso', 'programado'])
            ->where(function ($q) use ($hoy) {
                $q->whereDate('created_at', $hoy)
                  ->orWhereDate('fecha_inicio', $hoy);
            })
            ->with(['vehiculo', 'busRuta', 'conductor'])
            ->orderByRaw("FIELD(estado, 'en_curso', 'programado')")
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $viajes,
        ]);
    }
}
