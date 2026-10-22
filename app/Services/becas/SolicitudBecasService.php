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
use App\Models\Persona;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;
use Exception;

class SolicitudBecasService
{
    public const MENSAJE_SOLO_ESTUDIANTES = 'Solo las personas con perfil Estudiante pueden solicitar becas.';

    public static function esEstudiante(int|string|null $personaId): bool
    {
        return $personaId !== null && $personaId !== ''
            && Persona::whereKey((int) $personaId)->estudiantes()->exists();
    }

    private function asegurarEstudiante(int|string|null $personaId): void
    {
        if (! self::esEstudiante($personaId)) {
            throw new Exception(self::MENSAJE_SOLO_ESTUDIANTES);
        }
    }

    public function listarSolicitudes(array $filtros = [])
    {
        $query = SolicitudBeca::with(['persona.personaPnf.pnf', 'beneficio', 'jornada', 'lapso']);

        if (!empty($filtros['buscar'])) {
            $buscar = $filtros['buscar'];
            $query->whereHas('persona', function ($q) use ($buscar) {
                $q->where('cedula_per', 'like', "%{$buscar}%")
                  ->orWhere('nombres_per', 'like', "%{$buscar}%")
                  ->orWhere('apellidos_per', 'like', "%{$buscar}%");
            });
        }

        if (isset($filtros['estado']) && $filtros['estado'] !== '') {
            $query->where('estado_solicitud_sob', intval($filtros['estado']));
        }

        if (!empty($filtros['beneficio_id'])) {
            $query->where('bes_id', intval($filtros['beneficio_id']));
        }

        return $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();
    }

    public function crearSolicitud(array $data): SolicitudBeca
    {
        return DB::transaction(function () use ($data) {
            $personaId = $data['per_id'];
            $jornadaId = $data['jornada_id'];

            $this->asegurarEstudiante($personaId);

            $jornada = JornadaBeca::findOrFail($jornadaId);
            if ((int) $jornada->status_id !== 1) {
                throw new Exception("La jornada seleccionada no está activa.");
            }

            $hoy = now()->toDateString();
            if ($jornada->fecha_inicio_solicitud_jnb->toDateString() > $hoy
                || $jornada->fecha_fin_solicitud_jnb->toDateString() < $hoy) {
                throw new Exception("El período de solicitudes para esta jornada ya expiró o no ha iniciado.");
            }

            $existe = SolicitudBeca::where('per_id', $personaId)
                ->where('bes_id', $jornada->bes_id)
                ->where('laa_id', $jornada->laa_id)
                ->whereIn('estado_solicitud_sob', [0, 1])
                ->exists();
            if ($existe) {
                throw new Exception("Ya existe una solicitud en proceso o aprobada para este beneficio en el lapso actual.");
            }

            if ($jornada->cupos_maximos_jnb <= $jornada->cupos_asignados_jnb) {
                throw new Exception("No hay cupos disponibles en esta jornada.");
            }

            $respuestas = $data['respuestas'] ?? [];
            unset($data['respuestas']);

            $archivoNotas = $data['archivo_notas'] ?? null;
            unset($data['archivo_notas']);

            $solicitud = SolicitudBeca::create([
                'per_id'               => $personaId,
                'bes_id'               => $jornada->bes_id,
                'jnb_id'               => $jornada->id_jnb,
                'laa_id'               => $jornada->laa_id,
                'tipo_solicitud_sob'   => $data['tipo_solicitud'] ?? 'nueva',
                'estado_solicitud_sob' => 0,
            ]);

            // Procesamiento y guardado del documento adjunto (Notas)
            if ($archivoNotas instanceof UploadedFile) {
                // Obtener el código del lapso académico activo para agrupar los archivos en carpetas por período
                $lapsoActual = Lapso::where('es_actual_laa', true)->first();
                $codigoLapso = $lapsoActual ? $lapsoActual->codigo_laa : 'sin-lapso';

                // Generar un nombre único para el archivo que evita exponer información sensible del estudiante
                $extension = $archivoNotas->extension() ?: $archivoNotas->getClientOriginalExtension();
                $nombreArchivo = 'notas_solicitud_' . $solicitud->id_sob . '_' . uniqid() . '.' . $extension;
                
                // Guardar el archivo físicamente en el storage bajo: documentos_becas/{codigo_lapso}/{nombre_archivo}
                $ruta = $archivoNotas->storeAs('documentos_becas/' . $codigoLapso, $nombreArchivo, 'local');

                // Registrar la referencia del documento en la base de datos asociado a la solicitud
                SolicitudDocumento::create([
                    'sob_id'               => $solicitud->id_sob,
                    'nombre_documento_dsb' => 'Notas Certificadas',
                    'ruta_archivo_dsb'     => $ruta,
                    'tipo_archivo_dsb'     => $extension,
                ]);
            }

            $this->guardarRespuestas($solicitud, $jornada, $respuestas);

            return $solicitud;
        });
    }

    private function guardarRespuestas(SolicitudBeca $solicitud, JornadaBeca $jornada, array $respuestas): void
    {
        if (empty($respuestas)) return;

        $criterios = JornadaCriterio::where('jnb_id', $jornada->id_jnb)
            ->get()
            ->keyBy('pgb_id');

        $preguntas = BecaPregunta::where('bes_id', $jornada->bes_id)
            ->whereIn('id_pgb', collect($respuestas)->pluck('id_pregunta'))
            ->get()
            ->keyBy('id_pgb');

        $rows = [];

        foreach ($respuestas as $r) {
            $idPregunta = $r['id_pregunta'] ?? null;
            if (!$idPregunta || !isset($preguntas[$idPregunta])) continue;

            $pregunta  = $preguntas[$idPregunta];
            $criterio  = $criterios[$idPregunta] ?? null;
            $valor     = $r['valor'] ?? null;
            $valorJson = $r['valor_json'] ?? null;

            $cumple = null;
            if ($criterio && $criterio->operador_cjb) {
                $cumple = $this->evaluarCriterio($criterio, $valor, $valorJson);
            }

            $rows[] = [
                'sob_id'              => $solicitud->id_sob,
                'pgb_id'              => $idPregunta,
                'valor_rsb'           => $valor,
                'valor_json_rsb'      => $valorJson ? json_encode($valorJson) : null,
                'cumple_criterio_rsb' => $cumple,
                'status_id'           => 1,
                'created_id'          => auth()->id(),
                'created_at'          => now(),
                'updated_at'          => now(),
            ];
        }

        if (!empty($rows)) {
            SolicitudRespuesta::insert($rows);
        }
    }

    private function evaluarCriterio($criterio, $valor, $valorJson): bool
    {
        $operador = $criterio->operador_cjb;
        $esperado = $criterio->valor_esperado_cjb;

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
            $estadoAnterior = (int) $solicitud->estado_solicitud_sob;

            if ($estadoAnterior === $nuevoEstado) {
                return $solicitud;
            }

            $jornada = JornadaBeca::findOrFail($solicitud->jnb_id);
            $beneficio = Beneficio::findOrFail($solicitud->bes_id);

            if ($nuevoEstado === 1) {
                if ($jornada->cupos_asignados_jnb >= $jornada->cupos_maximos_jnb) {
                    throw new Exception("No hay cupos disponibles en la jornada para aprobar esta solicitud.");
                }
                if ($beneficio->cupones_disponibles_bes <= 0) {
                    throw new Exception("No hay cupones disponibles en el beneficio configurado.");
                }

                $jornada->increment('cupos_asignados_jnb');

                $beneficio->increment('cupones_ocupados_bes');
                $beneficio->decrement('cupones_disponibles_bes');
            }

            if ($estadoAnterior === 1) {
                if ($jornada->cupos_asignados_jnb > 0) {
                    $jornada->decrement('cupos_asignados_jnb');
                }

                if ($beneficio->cupones_ocupados_bes > 0) {
                    $beneficio->decrement('cupones_ocupados_bes');
                }
                $beneficio->increment('cupones_disponibles_bes');
            }

            $solicitud->update([
                'estado_solicitud_sob'       => $nuevoEstado,
                'comentario_verificador_sob' => $nuevoEstado === 2 ? $comentario : null,
                'verificado_por_use_id'      => $verificadorId,
                'fecha_verificacion_sob'     => now(),
            ]);

            return $solicitud;
        });
    }

    public function obtenerPendientesPorRenovar(int $jornadaId)
    {
        $jornada = JornadaBeca::findOrFail($jornadaId);
        
        // Estudiantes aprobados en el mismo beneficio en cualquier lapso anterior
        $aprobadosAnteriores = SolicitudBeca::where('bes_id', $jornada->bes_id)
            ->where('estado_solicitud_sob', 1)
            ->where('laa_id', '!=', $jornada->laa_id)
            ->pluck('per_id');

        // Estudiantes que ya tienen solicitud (de cualquier estado) en la jornada actual
        $postuladosActuales = SolicitudBeca::where('jnb_id', $jornadaId)
            ->pluck('per_id');

        // Los que faltan por postularse en esta jornada
        $pendientes = $aprobadosAnteriores->diff($postuladosActuales);

        return $pendientes;
    }

    public function renovarSolicitudSimplificada(int $personaId, int $jornadaId, UploadedFile $archivoNotas): SolicitudBeca
    {
        return DB::transaction(function () use ($personaId, $jornadaId, $archivoNotas) {
            $this->asegurarEstudiante($personaId);

            $jornada = JornadaBeca::findOrFail($jornadaId);

            if ((int) $jornada->status_id !== 1) {
                throw new Exception("La jornada seleccionada no está activa.");
            }

            $hoy = now()->toDateString();
            if ($jornada->fecha_inicio_solicitud_jnb->toDateString() > $hoy
                || $jornada->fecha_fin_solicitud_jnb->toDateString() < $hoy) {
                throw new Exception("El período de solicitudes para esta jornada ya expiró o no ha iniciado.");
            }

            // Verificar si tiene una solicitud previa aprobada para este beneficio
            $solicitudPrevia = SolicitudBeca::where('per_id', $personaId)
                ->where('bes_id', $jornada->bes_id)
                ->where('estado_solicitud_sob', 1)
                ->where('laa_id', '!=', $jornada->laa_id)
                ->latest('created_at')
                ->first();

            if (!$solicitudPrevia) {
                throw new Exception("El estudiante no cumple los requisitos para renovación automática (no tiene una beca previa aprobada para este beneficio).");
            }

            // Verificar si ya tiene solicitud en este lapso
            $existe = SolicitudBeca::where('per_id', $personaId)
                ->where('bes_id', $jornada->bes_id)
                ->where('laa_id', $jornada->laa_id)
                ->exists();
            if ($existe) {
                throw new Exception("Ya existe una solicitud registrada para este beneficio en el lapso actual.");
            }

            if ($jornada->cupos_maximos_jnb <= $jornada->cupos_asignados_jnb) {
                throw new Exception("No hay cupos disponibles en esta jornada para renovar.");
            }

            // Crear la nueva solicitud
            $nuevaSolicitud = SolicitudBeca::create([
                'per_id'               => $personaId,
                'bes_id'               => $jornada->bes_id,
                'jnb_id'               => $jornada->id_jnb,
                'laa_id'               => $jornada->laa_id,
                'tipo_solicitud_sob'   => 'renovacion',
                'estado_solicitud_sob' => 0,
            ]);

            // Guardar el nuevo documento de notas
            $lapsoActual = Lapso::where('id_laa', $jornada->laa_id)->first();
            $codigoLapso = $lapsoActual ? $lapsoActual->codigo_laa : 'sin-lapso';
            $extension = $archivoNotas->extension() ?: $archivoNotas->getClientOriginalExtension();
            $nombreArchivo = 'notas_renovacion_' . $nuevaSolicitud->id_sob . '_' . uniqid() . '.' . $extension;
            
            $ruta = $archivoNotas->storeAs('documentos_becas/' . $codigoLapso, $nombreArchivo, 'local');

            SolicitudDocumento::create([
                'sob_id'               => $nuevaSolicitud->id_sob,
                'nombre_documento_dsb' => 'Notas Certificadas',
                'ruta_archivo_dsb'     => $ruta,
                'tipo_archivo_dsb'     => $extension,
            ]);

            // Clonar las respuestas anteriores
            $respuestasAnteriores = SolicitudRespuesta::where('sob_id', $solicitudPrevia->id_sob)->get();
            $nuevasRespuestas = [];

            foreach ($respuestasAnteriores as $respuesta) {
                $nuevasRespuestas[] = [
                    'sob_id'              => $nuevaSolicitud->id_sob,
                    'pgb_id'              => $respuesta->pgb_id,
                    'valor_rsb'           => $respuesta->valor_rsb,
                    'valor_json_rsb'      => $respuesta->valor_json_rsb !== null ? json_encode($respuesta->valor_json_rsb) : null,
                    'cumple_criterio_rsb' => $respuesta->cumple_criterio_rsb,
                    'status_id'           => 1,
                    'created_id'          => auth()->id(),
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ];
            }

            if (!empty($nuevasRespuestas)) {
                SolicitudRespuesta::insert($nuevasRespuestas);
            }

            return $nuevaSolicitud;
        });
    }
}
