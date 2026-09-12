<?php

namespace App\Services\becas;

use App\Models\Becas\SolicitudBeca;
use App\Models\Becas\JornadaBeca;
use App\Models\Becas\Beneficio;
use App\Models\Persona;
use Illuminate\Support\Facades\DB;
use Exception;

class SolicitudBecasService
{
    /**
     * Obtiene el listado de solicitudes paginado y con filtros para administración.
     */
    public function listarSolicitudes(array $filtros = [])
    {
        $query = SolicitudBeca::with(['persona.personaPnf.pnf', 'beneficio', 'jornada', 'lapso']);

        // Filtrar por búsqueda (Cédula, Nombre o Apellido)
        if (!empty($filtros['buscar'])) {
            $buscar = $filtros['buscar'];
            $query->whereHas('persona', function ($q) use ($buscar) {
                $q->where('cedula_persona', 'like', "%{$buscar}%")
                  ->orWhere('nombre_persona', 'like', "%{$buscar}%")
                  ->orWhere('apellido_persona', 'like', "%{$buscar}%");
            });
        }

        // Filtrar por Estado
        if (isset($filtros['estado']) && $filtros['estado'] !== '') {
            $query->where('estado', intval($filtros['estado']));
        }

        // Filtrar por Beneficio
        if (!empty($filtros['beneficio_id'])) {
            $query->where('id_beneficio', intval($filtros['beneficio_id']));
        }

        // Ordenar por fecha de creación desc
        return $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();
    }

    /**
     * Registra una nueva solicitud de beca.
     * @throws Exception
     */
    public function crearSolicitud(array $data): SolicitudBeca
    {
        return DB::transaction(function () use ($data) {
            $personaId = $data['id_persona'];
            $jornadaId = $data['jornada_id'];

            // 1. Validar que la jornada esté activa y exista
            $jornada = JornadaBeca::findOrFail($jornadaId);
            if (!$jornada->activa) {
                throw new Exception("La jornada de becas seleccionada no se encuentra activa.");
            }

            // 2. Validar que la fecha actual esté dentro de la jornada
            $hoy = now()->toDateString();
            if ($jornada->fecha_inicio_solicitud->toDateString() > $hoy || $jornada->fecha_fin_solicitud->toDateString() < $hoy) {
                throw new Exception("El período de solicitudes para esta jornada ya expiró o no ha iniciado.");
            }

            // 3. Validar duplicados para el estudiante en esta jornada
            $existeSolicitud = SolicitudBeca::where('id_persona', $personaId)
                ->where('jornada_id', $jornadaId)
                ->exists();
            if ($existeSolicitud) {
                throw new Exception("Ya existe una solicitud registrada para este estudiante en la jornada seleccionada.");
            }

            // 4. Validar cupos máximos de la jornada
            if ($jornada->cupos_maximos <= $jornada->cupos_asignados) {
                throw new Exception("No hay cupos disponibles en esta jornada.");
            }

            // Por defecto, se crea en estado Pendiente (0)
            $data['estado'] = 0;

            return SolicitudBeca::create($data);
        });
    }

    /**
     * Actualiza los datos de una solicitud.
     */
    public function actualizarSolicitud(int $id, array $data): SolicitudBeca
    {
        $solicitud = SolicitudBeca::findOrFail($id);
        $solicitud->update($data);
        return $solicitud;
    }

    /**
     * Verifica, aprueba o rechaza una solicitud actualizando cupos.
     * @throws Exception
     */
    public function verificarSolicitud(int $id, int $nuevoEstado, ?string $comentario, int $verificadorId): SolicitudBeca
    {
        return DB::transaction(function () use ($id, $nuevoEstado, $comentario, $verificadorId) {
            $solicitud = SolicitudBeca::findOrFail($id);
            $estadoAnterior = $solicitud->estado;

            if ($estadoAnterior === $nuevoEstado) {
                return $solicitud;
            }

            $jornada = JornadaBeca::findOrFail($solicitud->jornada_id);
            $beneficio = Beneficio::findOrFail($solicitud->id_beneficio);

            // Si pasa a ser APROBADA (1)
            if ($nuevoEstado === 1) {
                // Si antes estaba Aprobada, no sumamos (ya validado arriba que son diferentes).
                // Validar cupos en Jornada
                if ($jornada->cupos_asignados >= $jornada->cupos_maximos) {
                    throw new Exception("No hay cupos disponibles en la jornada para aprobar esta solicitud.");
                }
                // Validar cupones en el Beneficio
                if ($beneficio->cupones_disponibles <= 0) {
                    throw new Exception("No hay cupones disponibles en el beneficio configurado.");
                }

                // Incrementar cupos asignados en la jornada
                $jornada->increment('cupos_asignados');

                // Incrementar cupones ocupados y decrementar disponibles del beneficio
                $beneficio->increment('cupones_ocupados');
                $beneficio->decrement('cupones_disponibles');
            }

            // Si deja de ser APROBADA (pasa de Aprobada (1) a Rechazada (2) o Pendiente (0))
            if ($estadoAnterior === 1) {
                // Decrementar cupos asignados en la jornada
                if ($jornada->cupos_asignados > 0) {
                    $jornada->decrement('cupos_asignados');
                }

                // Decrementar cupones ocupados e incrementar disponibles en el beneficio
                if ($beneficio->cupones_ocupados > 0) {
                    $beneficio->decrement('cupones_ocupados');
                }
                $beneficio->increment('cupones_disponibles');
            }

            // Actualizar la solicitud
            $solicitud->update([
                'estado' => $nuevoEstado,
                'comentario_verificador' => $nuevoEstado === 2 ? $comentario : null, // Comentario solo en caso de rechazo
                'verificado_por' => $verificadorId,
                'fecha_verificacion' => now(),
            ]);

            return $solicitud;
        });
    }
}
