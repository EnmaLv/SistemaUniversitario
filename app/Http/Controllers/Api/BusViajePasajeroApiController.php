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
            if (empty($validated['cedula'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debe escanear el carnet del estudiante.',
                ], 422);
            }

            $persona = Persona::where('cedula_persona', $validated['cedula'])
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
            ->where('persona_id', $persona->id_persona)
            ->exists();

        if ($yaRegistrado) {
            return response()->json([
                'success' => false,
                'message' => "{$persona->nombre_persona} ya está registrado en este viaje.",
                'code'    => 'YA_REGISTRADO',
            ], 409);
        }

        try {
            DB::transaction(function () use ($viaje, $persona, $validated) {
                ViajePasajero::create([
                    'bus_viaje_id'  => $viaje->id,
                    'persona_id'    => $persona->id_persona,
                    'bus_parada_id' => $validated['bus_parada_id'] ?? null,
                    'escaneado_at'  => now(),
                ]);

                $viaje->increment('pasajeros');
            });
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                return response()->json([
                    'success' => false,
                    'message' => "{$persona->nombre_persona} ya está registrado en este viaje.",
                    'code'    => 'YA_REGISTRADO',
                ], 409);
            }
            throw $e;
        }

        return response()->json([
            'success' => true,
            'message' => "{$persona->nombre_persona} {$persona->apellido_persona} registrado exitosamente.",
            'data'    => [
                'persona_id'     => $persona->id_persona,
                'nombre'         => "{$persona->nombre_persona} {$persona->apellido_persona}",
                'pasajeros_total' => $viaje->fresh()->pasajeros,
            ],
        ]);
    }
}