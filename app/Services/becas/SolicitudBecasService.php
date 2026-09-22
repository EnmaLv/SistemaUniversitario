<?php

namespace App\Services\becas;

use App\Models\Becas\BecaPregunta;
use App\Models\Becas\SolicitudBeca;
use App\Models\Becas\JornadaBeca;
use App\Models\Becas\Beneficio;
use App\Models\Becas\JornadaCriterio;
use App\Models\Becas\SolicitudRespuesta;
use App\Models\Becas\Lapso;
use App\Models\Becas\SolicitudDocumento;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;
use Exception;

class SolicitudBecasService
{

    public function listarSolicitudes(array $filtros = [])
    {
        $query = SolicitudBeca::with(['persona.personaPnf.pnf', 'beneficio', 'jornada', 'lapso']);

        if (!empty($filtros['buscar'])) {
            $buscar = $filtros['buscar'];
            $query->whereHas('persona', function ($q) use ($buscar) {
                $q->where('cedula_persona', 'like', "%{$buscar}%")
                  ->orWhere('nombre_persona', 'like', "%{$buscar}%")
                  ->orWhere('apellido_persona', 'like', "%{$buscar}%");
            });
        }

        if (isset($filtros['estado']) && $filtros['estado'] !== '') {
            $query->where('estado', intval($filtros['estado']));
        }

        if (!empty($filtros['beneficio_id'])) {
            $query->where('id_beneficio', intval($filtros['beneficio_id']));
        }

        return $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();
    }

    public function crearSolicitud(array $data): SolicitudBeca
    {
        return DB::transaction(function () use ($data) {
            $personaId = $data['id_persona'];
            $jornadaId = $data['jornada_id'];

            $jornada = JornadaBeca::findOrFail($jornadaId);
            if (!$jornada->activa) {
                throw new Exception("La jornada seleccionada no está activa.");
            }

            $hoy = now()->toDateString();
            if ($jornada->fecha_inicio_solicitud->toDateString() > $hoy
                || $jornada->fecha_fin_solicitud->toDateString() < $hoy) {
                throw new Exception("El período de solicitudes para esta jornada ya expiró o no ha iniciado.");
            }

            $existe = SolicitudBeca::where('id_persona', $personaId)
                ->where('id_beneficio', $jornada->beneficio_id)
                ->where('id_lapso', $jornada->lapsos_id)
                ->whereIn('estado', [0, 1])
                ->exists();
            if ($existe) {
                throw new Exception("Ya existe una solicitud en proceso o aprobada para este beneficio en el lapso actual.");
            }

            if ($jornada->cupos_maximos <= $jornada->cupos_asignados) {
                throw new Exception("No hay cupos disponibles en esta jornada.");
            }

            $respuestas = $data['respuestas'] ?? [];
            unset($data['respuestas']);

            $archivoNotas = $data['archivo_notas'] ?? null;
            unset($data['archivo_notas']);

            $data['estado'] = 0;
            $solicitud = SolicitudBeca::create($data);

            // Procesamiento y guardado del documento adjunto (Notas)
            if ($archivoNotas instanceof UploadedFile) {
                // Obtener el código del lapso académico activo para agrupar los archivos en carpetas por período
                $lapsoActual = Lapso::where('es_actual', true)->first();
                $codigoLapso = $lapsoActual ? $lapsoActual->codigo : 'sin-lapso';

                // Generar un nombre único para el archivo que evita exponer información sensible del estudiante
                $extension = $archivoNotas->extension() ?: $archivoNotas->getClientOriginalExtension();
                $nombreArchivo = 'notas_solicitud_' . $solicitud->id . '_' . uniqid() . '.' . $extension;
                
                // Guardar el archivo físicamente en el storage bajo: documentos_becas/{codigo_lapso}/{nombre_archivo}
                $ruta = $archivoNotas->storeAs('documentos_becas/' . $codigoLapso, $nombreArchivo, 'public');

                // Registrar la referencia del documento en la base de datos asociado a la solicitud
                SolicitudDocumento::create([
                    'id_solicitud'     => $solicitud->id,
                    'nombre_documento' => 'Notas Certificadas',
                    'ruta_archivo'     => $ruta,
                    'tipo_archivo'     => $extension,
                ]);
            }

            $this->guardarRespuestas($solicitud, $jornada, $respuestas);

            return $solicitud;
        });
    }

    private function guardarRespuestas(SolicitudBeca $solicitud, JornadaBeca $jornada, array $respuestas): void
    {
        if (empty($respuestas)) return;

        $criterios = JornadaCriterio::where('id_jornada', $jornada->id)
            ->get()
            ->keyBy('id_pregunta');

        $preguntas = BecaPregunta::whereIn('id', collect($respuestas)->pluck('id_pregunta'))
            ->get()
            ->keyBy('id');

        $rows = [];

        foreach ($respuestas as $r) {
            $idPregunta = $r['id_pregunta'] ?? null;
            if (!$idPregunta || !isset($preguntas[$idPregunta])) continue;

            $pregunta  = $preguntas[$idPregunta];
            $criterio  = $criterios[$idPregunta] ?? null;
            $valor     = $r['valor'] ?? null;
            $valorJson = $r['valor_json'] ?? null;

            $cumple = null;
            if ($criterio && $criterio->operador) {
                $cumple = $this->evaluarCriterio($criterio, $valor, $valorJson);
            }

            $rows[] = [
                'id_solicitud'    => $solicitud->id,
                'id_pregunta'     => $idPregunta,
                'valor'           => $valor,
                'valor_json'      => $valorJson ? json_encode($valorJson) : null,
                'cumple_criterio' => $cumple,
                'created_at'      => now(),
                'updated_at'      => now(),
            ];
        }

        if (!empty($rows)) {
            SolicitudRespuesta::insert($rows);
        }
    }

    private function evaluarCriterio($criterio, $valor, $valorJson): bool
    {
        $operador = $criterio->operador;
        $esperado = $criterio->valor_esperado;

        if ($operador === 'in' || $operador === 'not_in') {
            $lista = array_map('trim', explode(',', (string) $esperado));
            $enLista = in_array((string) $valor, $lista, true);
            return $operador === 'in' ? $enLista : !$enLista;
        }

        if ($operador === 'between') {
            [$min, $max] = array_pad(explode(',', (string) $esperado), 2, null);
            if ($min === null || $max === null) return false;
            return (float) $valor >= (float) $min && (float) $valor <= (float) $max;
        }

        if (!is_numeric($valor) || !is_numeric($esperado)) return false;

        $v = (float) $valor;
        $e = (float) $esperado;

        return match ($operador) {
            '='  => $v === $e,
            '!=' => $v !== $e,
            '>'  => $v >  $e,
            '>=' => $v >= $e,
            '<'  => $v <  $e,
            '<=' => $v <= $e,
            default => false,
        };
    }

    public function actualizarSolicitud(int $id, array $data): SolicitudBeca
    {
        $solicitud = SolicitudBeca::findOrFail($id);
        $solicitud->update($data);
        return $solicitud;
    }

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

            if ($nuevoEstado === 1) {
                if ($jornada->cupos_asignados >= $jornada->cupos_maximos) {
                    throw new Exception("No hay cupos disponibles en la jornada para aprobar esta solicitud.");
                }
                if ($beneficio->cupones_disponibles <= 0) {
                    throw new Exception("No hay cupones disponibles en el beneficio configurado.");
                }

                $jornada->increment('cupos_asignados');

                $beneficio->increment('cupones_ocupados');
                $beneficio->decrement('cupones_disponibles');
            }

            if ($estadoAnterior === 1) {
                if ($jornada->cupos_asignados > 0) {
                    $jornada->decrement('cupos_asignados');
                }

                if ($beneficio->cupones_ocupados > 0) {
                    $beneficio->decrement('cupones_ocupados');
                }
                $beneficio->increment('cupones_disponibles');
            }

            $solicitud->update([
                'estado' => $nuevoEstado,
                'comentario_verificador' => $nuevoEstado === 2 ? $comentario : null, // Comentario solo en caso de rechazo
                'verificado_por' => $verificadorId,
                'fecha_verificacion' => now(),
            ]);

            return $solicitud;
        });
    }

    public function obtenerPendientesPorRenovar(int $jornadaId)
    {
        $jornada = JornadaBeca::findOrFail($jornadaId);
        
        // Estudiantes aprobados en el mismo beneficio en cualquier lapso anterior
        $aprobadosAnteriores = SolicitudBeca::where('id_beneficio', $jornada->beneficio_id)
            ->where('estado', 1)
            ->where('id_lapso', '!=', $jornada->lapsos_id)
            ->pluck('id_persona');

        // Estudiantes que ya tienen solicitud (de cualquier estado) en la jornada actual
        $postuladosActuales = SolicitudBeca::where('jornada_id', $jornadaId)
            ->pluck('id_persona');

        // Los que faltan por postularse en esta jornada
        $pendientes = $aprobadosAnteriores->diff($postuladosActuales);

        return $pendientes;
    }

    public function renovarSolicitudSimplificada(int $personaId, int $jornadaId, UploadedFile $archivoNotas): SolicitudBeca
    {
        return DB::transaction(function () use ($personaId, $jornadaId, $archivoNotas) {
            $jornada = JornadaBeca::findOrFail($jornadaId);

            if (!$jornada->activa) {
                throw new Exception("La jornada seleccionada no está activa.");
            }

            $hoy = now()->toDateString();
            if ($jornada->fecha_inicio_solicitud->toDateString() > $hoy
                || $jornada->fecha_fin_solicitud->toDateString() < $hoy) {
                throw new Exception("El período de solicitudes para esta jornada ya expiró o no ha iniciado.");
            }

            // Verificar si tiene una solicitud previa aprobada para este beneficio
            $solicitudPrevia = SolicitudBeca::where('id_persona', $personaId)
                ->where('id_beneficio', $jornada->beneficio_id)
                ->where('estado', 1)
                ->where('id_lapso', '!=', $jornada->lapsos_id)
                ->latest('created_at')
                ->first();

            if (!$solicitudPrevia) {
                throw new Exception("El estudiante no cumple los requisitos para renovación automática (no tiene una beca previa aprobada para este beneficio).");
            }

            // Verificar si ya tiene solicitud en este lapso
            $existe = SolicitudBeca::where('id_persona', $personaId)
                ->where('id_beneficio', $jornada->beneficio_id)
                ->where('id_lapso', $jornada->lapsos_id)
                ->exists();
            if ($existe) {
                throw new Exception("Ya existe una solicitud registrada para este beneficio en el lapso actual.");
            }

            if ($jornada->cupos_maximos <= $jornada->cupos_asignados) {
                throw new Exception("No hay cupos disponibles en esta jornada para renovar.");
            }

            // Crear la nueva solicitud
            $nuevaSolicitud = SolicitudBeca::create([
                'id_persona' => $personaId,
                'id_beneficio' => $jornada->beneficio_id,
                'jornada_id' => $jornadaId,
                'id_lapso' => $jornada->lapsos_id,
                'tipo_solicitud' => 'renovacion',
                'estado' => 0, // Pendiente de revisión
            ]);

            // Guardar el nuevo documento de notas
            $lapsoActual = Lapso::where('id', $jornada->lapsos_id)->first();
            $codigoLapso = $lapsoActual ? $lapsoActual->codigo : 'sin-lapso';
            $extension = $archivoNotas->extension() ?: $archivoNotas->getClientOriginalExtension();
            $nombreArchivo = 'notas_renovacion_' . $nuevaSolicitud->id . '_' . uniqid() . '.' . $extension;
            
            $ruta = $archivoNotas->storeAs('documentos_becas/' . $codigoLapso, $nombreArchivo, 'public');

            SolicitudDocumento::create([
                'id_solicitud'     => $nuevaSolicitud->id,
                'nombre_documento' => 'Notas Certificadas',
                'ruta_archivo'     => $ruta,
                'tipo_archivo'     => $extension,
            ]);

            // Clonar las respuestas anteriores
            $respuestasAnteriores = SolicitudRespuesta::where('id_solicitud', $solicitudPrevia->id)->get();
            $nuevasRespuestas = [];

            foreach ($respuestasAnteriores as $respuesta) {
                $nuevasRespuestas[] = [
                    'id_solicitud'    => $nuevaSolicitud->id,
                    'id_pregunta'     => $respuesta->id_pregunta,
                    'valor'           => $respuesta->valor,
                    'valor_json'      => $respuesta->valor_json,
                    'cumple_criterio' => $respuesta->cumple_criterio,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ];
            }

            if (!empty($nuevasRespuestas)) {
                SolicitudRespuesta::insert($nuevasRespuestas);
            }

            return $nuevaSolicitud;
        });
    }
}
