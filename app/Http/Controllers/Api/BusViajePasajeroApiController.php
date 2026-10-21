<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BusViaje;
use App\Models\Persona;
use App\Models\ViajePasajero;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class BusViajePasajeroApiController extends Controller
{
    public function registrar(Request $request, BusViaje $viaje): JsonResponse
    {
        if ($viaje->estado !== 'en_curso') {
            return response()->json([
                'success' => false,
                'message' => 'Este viaje no está en curso, no se puede registrar asistencia.',
            ], 422);
        }

        $validated = $request->validate([
            'cedula'        => 'nullable|string',
            'bus_parada_id' => 'nullable|integer|exists:bus_paradas,id',
            'metodo'        => 'required|in:proximidad,manual',
        ]);

        if ($validated['metodo'] === 'manual') {
            if ((int) $viaje->conductor_id !== (int) $request->user()->id_usuario) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo el conductor del viaje puede registrar pasajeros manualmente.',
                ], 403);
            }

            if (empty($validated['cedula'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debe escanear el carnet del estudiante.',
                ], 422);
            }

            $persona = Persona::where('cedula_per', $validated['cedula'])
                ->where('estado', true)
                ->first();

            if (!$persona) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró un registro para esta cédula.',
                ], 404);
            }
        } else {
            $persona = $request->user()->persona ?? null;

            if (!$persona) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tu usuario no tiene un perfil de persona asociado.',
                ], 422);
            }
        }

        $yaRegistrado = ViajePasajero::where('bus_viaje_id', $viaje->id)
            ->where('persona_id', $persona->id_per)
            ->exists();

        if ($yaRegistrado) {
            return response()->json([
                'success' => false,
                'message' => "{$persona->nombres_per} ya está registrado en este viaje.",
                'code'    => 'YA_REGISTRADO',
            ], 409);
        }

        try {
            DB::transaction(function () use ($viaje, $persona, $validated) {
                ViajePasajero::create([
                    'bus_viaje_id'  => $viaje->id,
                    'persona_id'    => $persona->id_per,
                    'bus_parada_id' => $validated['bus_parada_id'] ?? null,
                    'escaneado_at'  => now(),
                ]);

                $viaje->increment('pasajeros');
            });
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                return response()->json([
                    'success' => false,
                    'message' => "{$persona->nombres_per} ya está registrado en este viaje.",
                    'code'    => 'YA_REGISTRADO',
                ], 409);
            }
            throw $e;
        }

        return response()->json([
            'success' => true,
            'message' => "{$persona->nombres_per} {$persona->apellidos_per} registrado exitosamente.",
            'data'    => [
                'persona_id'     => $persona->id_per,
                'nombre'         => "{$persona->nombres_per} {$persona->apellidos_per}",
                'pasajeros_total' => $viaje->fresh()->pasajeros,
            ],
        ]);
    }
}