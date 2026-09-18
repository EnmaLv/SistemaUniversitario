<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BusGpsLog;
use App\Models\BusViaje;
use App\Services\Transporte\CalculoCombustibleService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BusViajeApiController extends Controller
{
    protected CalculoCombustibleService $calculoCombustible;

    public function __construct(CalculoCombustibleService $calculoCombustible)
    {
        $this->calculoCombustible = $calculoCombustible;
    }

    public function registrarGps(Request $request, BusViaje $viaje): JsonResponse
    {
        if ($viaje->conductor_id !== $request->user()->id_usuario) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para registrar GPS en este viaje.',
            ], 403);
        }

        if ($viaje->estado !== 'en_curso') {
            return response()->json([
                'success' => false,
                'message' => 'El viaje no está en curso; no se aceptan puntos GPS.',
            ], 409);
        }

        $validated = $request->validate([
            'local_id' => ['required', 'uuid'],
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'velocidad' => ['nullable', 'numeric', 'min:0'],
            'heading' => ['nullable', 'numeric'],
            'timestamp' => ['required', 'date'],
        ]);

        $existente = BusGpsLog::where('local_id', $validated['local_id'])->first();

        if ($existente) {
            return response()->json([
                'success' => true,
                'duplicate' => true,
            ]);
        }

        $viaje->gpsLogs()->create([
            'local_id' => $validated['local_id'],
            'lat' => $validated['lat'],
            'lng' => $validated['lng'],
            'velocidad' => $validated['velocidad'] ?? 0,
            'heading' => $validated['heading'] ?? null,
            'registrado_en' => Carbon::parse($validated['timestamp']),
            'origen' => 'app_conductor',
        ]);

        return response()->json([
            'success' => true,
        ]);
    }

    public function obtenerPosicion(BusViaje $viaje): JsonResponse
    {
        $ultimoLog = $viaje->gpsLogs()
            ->latest('id')
            ->first();

        return response()->json([
            'success' => true,
            'latitud' => $ultimoLog ? (float) $ultimoLog->lat : null,
            'longitud' => $ultimoLog ? (float) $ultimoLog->lng : null,
            'velocidad' => $ultimoLog ? (float) $ultimoLog->velocidad : 0,
            'pasajeros' => $viaje->pasajeros,
            'distancia_km' => $viaje->distancia_km,
            'litros_gastados' => $viaje->litros_gastados,
            'estado' => $viaje->estado,
            'fecha_registro' => $ultimoLog?->created_at?->toISOString(),
            'actualizado_hace' => $ultimoLog
                ? $ultimoLog->created_at->diffForHumans()
                : 'Sin registros',
        ]);
    }

    public function miViajeActivo(Request $request): JsonResponse
    {
        $usuario = $request->user();

        $viaje = BusViaje::delConductor($usuario->id_usuario)
            ->whereIn('estado', ['programado', 'en_curso'])
            ->with([
                'vehiculo.tipoCombustible',
                'busRuta.paradas' => fn ($q) => $q->orderBy('orden', 'asc'),
            ])
            ->first();

        if (!$viaje) {
            return response()->json([
                'success' => true,
                'message' => 'No tienes ningún viaje activo o programado en este momento.',
                'data' => null,
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $viaje,
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

        $validated = $request->validate([
            'pasajeros' => ['nullable', 'integer', 'min:0'],
        ]);

        $viaje->loadMissing('vehiculo');

        if (!$viaje->vehiculo) {
            return response()->json([
                'success' => false,
                'message' => 'El viaje no tiene un vehículo asociado.',
            ], 422);
        }

        $kmInicio = (float) ($viaje->vehiculo->km_actual ?? 0);

        $viaje->update([
            'estado' => 'en_curso',
            'fecha_inicio' => now(),
            'km_inicio' => $kmInicio,
            'pasajeros' => $validated['pasajeros'] ?? $viaje->pasajeros,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'El viaje ha iniciado correctamente.',
            'data' => $viaje->fresh([
                'vehiculo',
                'busRuta.paradas',
            ]),
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
        } catch (\Throwable $e) {
            Log::error(
                "Error eliminando bus {$viajeId} de Firestore: {$e->getMessage()}"
            );
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

        if ($viaje->estado === 'finalizado') {
            return response()->json([
                'success' => true,
                'duplicate' => true,
                'message' => 'El viaje ya estaba finalizado.',
                'data' => $viaje,
            ]);
        }

        if ($viaje->estado !== 'en_curso') {
            return response()->json([
                'success' => false,
                'message' => 'Solo se pueden finalizar viajes que estén en curso.',
            ], 422);
        }

        $viaje->loadMissing([
            'vehiculo',
            'busRuta.paradas',
        ]);

        if (!$viaje->vehiculo) {
            return response()->json([
                'success' => false,
                'message' => 'El viaje no tiene un vehículo asociado.',
            ], 422);
        }

        $kmFin = (float) ($viaje->vehiculo->km_actual ?? 0);

        if ($kmFin < (float) $viaje->km_inicio) {
            return response()->json([
                'success' => false,
                'message' => 'El kilometraje actual del vehículo no puede ser menor al kilometraje de inicio del viaje.',
            ], 422);
        }

        $distanciaRecorrida = $kmFin - (float) $viaje->km_inicio;

        $viaje->update([
            'km_fin' => $kmFin,
            'distancia_km' => $distanciaRecorrida,
        ]);

        $viaje->refresh();

        $resultado = $this->calculoCombustible->calcularParaViaje($viaje);

        $this->calculoCombustible->aplicarYGuardar(
            $viaje,
            $resultado
        );

        $viaje->update([
            'estado' => 'finalizado',
        ]);

        $viaje->vehiculo->update([
            'estado' => 'disponible',
            'km_actual' => $kmFin,
        ]);

        $this->eliminarBusDeFirebase((string) $viaje->id);

        return response()->json([
            'success' => true,
            'message' => 'Viaje finalizado y combustible calculado.',
            'data' => array_merge($resultado, [
                'viaje' => $viaje->fresh([
                    'vehiculo',
                    'busRuta.paradas',
                ]),
                'vehiculo' => $viaje->vehiculo->fresh(),
            ]),
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

        if (!in_array($viaje->estado, ['programado', 'en_curso'], true)) {
            return response()->json([
                'success' => false,
                'message' => "No se puede cancelar el viaje porque su estado es '{$viaje->estado}'.",
            ], 422);
        }

        $validated = $request->validate([
            'motivo_cancelacion' => [
                'required',
                'string',
                'min:5',
                'max:500',
            ],
        ]);

        $viaje->update([
            'estado' => 'cancelado',
            'motivo_cancelacion' => $validated['motivo_cancelacion'],
        ]);

        $this->eliminarBusDeFirebase((string) $viaje->id);

        return response()->json([
            'success' => true,
            'message' => 'El viaje ha sido cancelado exitosamente.',
            'data' => $viaje->fresh(),
        ]);
    }

    public function historial(Request $request): JsonResponse
    {
        $viajes = BusViaje::delConductor($request->user()->id_usuario)
            ->where('estado', 'finalizado')
            ->with([
                'vehiculo',
                'busRuta',
            ])
            ->orderBy('updated_at', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $viajes,
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
            ->with([
                'vehiculo',
                'busRuta',
                'conductor',
            ])
            ->orderByRaw("FIELD(estado, 'en_curso', 'programado')")
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $viajes,
        ]);
    }
}