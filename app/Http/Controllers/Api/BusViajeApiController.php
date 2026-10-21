<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BusViaje;
use App\Services\Transporte\CalculoCombustibleService;
use App\Support\Transporte\FormatoApiTransporte;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        if ((int) $viaje->conductor_use_id !== (int) $request->user()->id_use) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para registrar GPS en este viaje.',
            ], 403);
        }

        if ($viaje->estado_viaje_vjt !== 'en_curso') {
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

        $existente = DB::table('log_gps_transporte')
            ->where('local_id_lgt', $validated['local_id'])
            ->exists();

        if ($existente) {
            return response()->json([
                'success' => true,
                'duplicate' => true,
            ]);
        }

        $viaje->gpsLogs()->create([
            'local_id_lgt' => $validated['local_id'],
            'lat_lgt' => $validated['lat'],
            'lng_lgt' => $validated['lng'],
            'velocidad_lgt' => $validated['velocidad'] ?? 0,
            'heading_lgt' => $validated['heading'] ?? null,
            'registrado_en_lgt' => Carbon::parse($validated['timestamp']),
            'origen_lgt' => 'app_conductor',
        ]);

        return response()->json([
            'success' => true,
        ]);
    }

    public function obtenerPosicion(BusViaje $viaje): JsonResponse
    {
        $ultimoLog = $viaje->gpsLogs()
            ->latest('id_lgt')
            ->first();

        return response()->json([
            'success' => true,
            'latitud' => $ultimoLog ? (float) $ultimoLog->lat_lgt : null,
            'longitud' => $ultimoLog ? (float) $ultimoLog->lng_lgt : null,
            'velocidad' => $ultimoLog ? (float) $ultimoLog->velocidad_lgt : 0,
            'pasajeros' => (int) $viaje->total_pasajeros_vjt,
            'distancia_km' => FormatoApiTransporte::decimal($viaje->getAttributes()['distancia_km_vjt'] ?? null, 2),
            'litros_gastados' => FormatoApiTransporte::decimal($viaje->getAttributes()['litros_gastados_vjt'] ?? null, 2),
            'estado' => $viaje->estado_viaje_vjt,
            'fecha_registro' => $ultimoLog?->created_at?->toISOString(),
            'actualizado_hace' => $ultimoLog
                ? $ultimoLog->created_at->diffForHumans()
                : 'Sin registros',
        ]);
    }

    public function miViajeActivo(Request $request): JsonResponse
    {
        $usuario = $request->user();

        $viaje = BusViaje::where('conductor_use_id', $usuario->id_use)
            ->whereIn('estado_viaje_vjt', ['programado', 'en_curso'])
            ->with([
                'vehiculo.tipoCombustible',
                'busRuta.paradas' => fn ($q) => $q->orderBy('ruta_parada_transporte.orden_rpt', 'asc'),
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
            'data' => FormatoApiTransporte::viaje($viaje),
        ]);
    }

    public function iniciar(Request $request, BusViaje $viaje): JsonResponse
    {
        if ((int) $viaje->conductor_use_id !== (int) $request->user()->id_use) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para iniciar este viaje.',
            ], 403);
        }

        if ($viaje->estado_viaje_vjt !== 'programado') {
            return response()->json([
                'success' => false,
                'message' => "No se puede iniciar el viaje porque su estado es '{$viaje->estado_viaje_vjt}'.",
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

        $kmInicio = (float) ($viaje->vehiculo->km_actual_vtr ?? 0);

        $viaje->update([
            'estado_viaje_vjt' => 'en_curso',
            'fecha_inicio_vjt' => now(),
            'km_inicio_vjt' => $kmInicio,
            'total_pasajeros_vjt' => $validated['pasajeros'] ?? $viaje->total_pasajeros_vjt,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'El viaje ha iniciado correctamente.',
            'data' => FormatoApiTransporte::viaje($viaje->fresh([
                'vehiculo',
                'busRuta.paradas',
            ])),
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
        if ((int) $viaje->conductor_use_id !== (int) $request->user()->id_use) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para finalizar este viaje.',
            ], 403);
        }

        if ($viaje->estado_viaje_vjt === 'finalizado') {
            return response()->json([
                'success' => true,
                'duplicate' => true,
                'message' => 'El viaje ya estaba finalizado.',
                'data' => FormatoApiTransporte::viaje($viaje),
            ]);
        }

        if ($viaje->estado_viaje_vjt !== 'en_curso') {
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

        $resultado = $this->calculoCombustible->calcularParaViaje($viaje);

        $this->calculoCombustible->aplicarYGuardar(
            $viaje,
            $resultado
        );

        $viaje->update([
            'estado_viaje_vjt' => 'finalizado',
        ]);

        $viaje->vehiculo->update([
            'estado_operativo_vtr' => 'disponible',
        ]);

        $this->eliminarBusDeFirebase((string) $viaje->id_vjt);

        return response()->json([
            'success' => true,
            'message' => 'Viaje finalizado y combustible calculado.',
            'data' => array_merge($resultado, [
                'viaje' => FormatoApiTransporte::viaje($viaje->fresh([
                    'vehiculo',
                    'busRuta.paradas',
                ])),
                'vehiculo' => FormatoApiTransporte::vehiculo($viaje->vehiculo->fresh()),
            ]),
        ]);
    }

    public function cancelar(Request $request, BusViaje $viaje): JsonResponse
    {
        if ((int) $viaje->conductor_use_id !== (int) $request->user()->id_use) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para cancelar este viaje.',
            ], 403);
        }

        if (!in_array($viaje->estado_viaje_vjt, ['programado', 'en_curso'], true)) {
            return response()->json([
                'success' => false,
                'message' => "No se puede cancelar el viaje porque su estado es '{$viaje->estado_viaje_vjt}'.",
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
            'estado_viaje_vjt' => 'cancelado',
        ]);

        $this->eliminarBusDeFirebase((string) $viaje->id_vjt);

        if ($viaje->wasChanged() && $viaje->vehiculo) {
            $viaje->vehiculo->update(['estado_operativo_vtr' => 'disponible']);
        }

        return response()->json([
            'success' => true,
            'message' => 'El viaje ha sido cancelado exitosamente.',
            'data' => FormatoApiTransporte::viaje($viaje->fresh()),
        ]);
    }

    public function historial(Request $request): JsonResponse
    {
        $viajes = BusViaje::where('conductor_use_id', $request->user()->id_use)
            ->where('estado_viaje_vjt', 'finalizado')
            ->with([
                'vehiculo',
                'busRuta',
            ])
            ->orderBy('updated_at', 'desc')
            ->paginate(15)
            ->through(fn ($viaje) => FormatoApiTransporte::viaje($viaje));

        return response()->json([
            'success' => true,
            'data' => $viajes,
        ]);
    }

    public function cartelera(): JsonResponse
    {
        $hoy = Carbon::today();

        $viajes = BusViaje::whereIn('estado_viaje_vjt', ['en_curso', 'programado'])
            ->where(function ($q) use ($hoy) {
                $q->whereDate('created_at', $hoy)
                    ->orWhereDate('fecha_inicio_vjt', $hoy);
            })
            ->with([
                'vehiculo',
                'busRuta',
                'conductor',
            ])
            ->orderByRaw("CASE WHEN estado_viaje_vjt = 'en_curso' THEN 0 ELSE 1 END")
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn ($viaje) => FormatoApiTransporte::viaje($viaje))
            ->values();

        return response()->json([
            'success' => true,
            'data' => $viajes,
        ]);
    }
}