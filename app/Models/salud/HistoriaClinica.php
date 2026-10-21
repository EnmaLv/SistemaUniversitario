<?php

namespace App\Models\salud;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use App\Models\Usuario;
use Carbon\Carbon;
use Exception;

class HistoriaClinica extends Model
{
    protected $table = 'historia_clinicas';

    protected $fillable = [
        'user_id',
        'psicologo_id'
    ];

    public function paciente()
    {
        return $this->belongsTo(Usuario::class, 'user_id', 'id_usuario');
    }

    public function psicologo()
    {
        return $this->belongsTo(Usuario::class, 'psicologo_id', 'id_usuario');
    }

    public function secciones()
    {
        return $this->hasMany(SeccionPersonalizada::class, 'historia_clinica_id');
    }

    public static function obtenerPaciente($userId)
    {
        return Usuario::with('persona')->where('id_usuario', $userId)->first();
    }

    public static function obtenerPsicologo($psicologoId)
    {
        return Usuario::with('persona')->where('id_usuario', $psicologoId)->first();
    }

    public static function obtenerSeccionesPersonalizadas($historiaId)
    {
        return DB::table('historia_secciones_personalizadas')
            ->where('historia_clinica_id', $historiaId)
            ->orderBy('orden')
            ->get();
    }

    public static function obtenerPorPaciente($pacienteId, $psicologoId)
    {
        return self::where('user_id', $pacienteId)
            ->where('psicologo_id', $psicologoId)
            ->first();
    }

    public static function iniciarHistoria($pacienteId, $psicologoId)
    {
        try {
            DB::beginTransaction();
            $historia = self::obtenerPorPaciente($pacienteId, $psicologoId);

            if (!$historia) {
                $id = \Illuminate\Support\Facades\DB::table('historia_clinicas')->insertGetId([
                    'user_id' => $pacienteId,
                    'psicologo_id' => $psicologoId,
                    'created_at' => now(),
                    'updated_at' => null
                ]);
                $historia = self::obtenerPorPaciente($pacienteId, $psicologoId);
            }

            $seccionesActivas = DB::table('historia_secciones_personalizadas')
                ->where('historia_clinica_id', $historia->id)
                ->where('status', 1)
                ->count();

            if ($seccionesActivas === 0) {
                // Obtener la última plantilla global del psicólogo usando Query Builder
                $plantillaGlobal = DB::table('historia_plantillas_globales')
                    ->where('psicologo_id', $psicologoId)
                    ->orderBy('updated_at', 'desc')
                    ->first();

                if ($plantillaGlobal) {
                    $secciones = json_decode($plantillaGlobal->secciones, true) ?? [];
                    $maxOrden = 0;

                    foreach ($secciones as $seccionData) {
                        $maxOrden++;
                        $seccionId = DB::table('historia_secciones_personalizadas')->insertGetId([
                            'historia_clinica_id' => $historia->id,
                            'titulo' => $seccionData['titulo'],
                            'descripcion_general' => $seccionData['descripcion_general'] ?? null,
                            'orden' => $maxOrden,
                            'status' => 1,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        $segmentos = $seccionData['segmentos'] ?? [];
                        foreach ($segmentos as $indexSeg => $segmentoTitulo) {
                            if (!empty(trim($segmentoTitulo))) {
                                DB::table('historia_segmentos_personalizados')->insert([
                                    'seccion_id' => $seccionId,
                                    'titulo' => $segmentoTitulo,
                                    'contenido' => null,
                                    'orden' => $indexSeg + 1,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]);
                            }
                        }
                    }
                }
            }

            DB::commit();
            return $historia;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function buscarPacientes($query, $psicologoId)
    {
        return Usuario::whereHas('citas', function ($q) use ($psicologoId) {
            $q->where('psicologo_id', $psicologoId);
        })
        ->whereHas('persona', function ($q) use ($query) {
            $q->where(function ($q) use ($query) {
                $q->where('nombre_persona', 'like', '%' . $query . '%')
                  ->orWhere('apellido_persona', 'like', '%' . $query . '%')
                  ->orWhere('cedula_persona', 'like', '%' . $query . '%');
            });
        })
        ->with('persona')
        ->get()
        ->map(function ($usuario) {
            $persona = $usuario->persona;
            return (object) [
                'id' => $usuario->id_usuario,
                'name' => trim(($persona->nombre_persona ?? '') . ' ' . ($persona->apellido_persona ?? '')),
                'email' => $persona->email_persona ?? ''
            ];
        });
    }

    public static function obtenerListado($psicologoId, $search = null, $filters = [])
    {
        $query = DB::table('citas')
            ->join('usuario', 'citas.user_id', '=', 'usuario.id_usuario')
            ->join('persona', 'usuario.id_persona', '=', 'persona.id_persona')
            ->leftJoin('historia_clinicas', function ($join) {
                $join->on('usuario.id_usuario', '=', 'historia_clinicas.user_id')
                    ->on('historia_clinicas.psicologo_id', '=', 'citas.psicologo_id');
            })
            ->where('citas.psicologo_id', $psicologoId)
            ->where('citas.estado', 'realizada');

        if (!empty($search)) {
            $query->leftJoin('historia_enfermedad', 'historia_clinicas.id', '=', 'historia_enfermedad.historia_clinica_id')
                  ->leftJoin('enfermedades', 'historia_enfermedad.enfermedad_id', '=', 'enfermedades.id')
                  ->where(function ($q) use ($search) {
                      $q->where('persona.nombre_persona', 'like', "%{$search}%")
                        ->orWhere('persona.apellido_persona', 'like', "%{$search}%")
                        ->orWhere('persona.cedula_persona', 'like', "%{$search}%");
                  });
        }

        if (!empty($filters['pnf'])) {
            $query->join('persona_pnf', 'persona.id_persona', '=', 'persona_pnf.id_persona')
                  ->where('persona_pnf.id_pnf', $filters['pnf']);
        }

        if (!empty($filters['edad'])) {
            $query->whereYear('persona.fecha_nacimiento_persona', Carbon::now()->subYears($filters['edad'])->year);
        }

        $tipoFiltroFecha = $filters['tipo_filtro_fecha'] ?? 'rango';
        $fechaDesde = $filters['fecha_desde'] ?? null;
        $fechaHasta = $filters['fecha_hasta'] ?? null;

        if (!empty($fechaDesde) || !empty($fechaHasta)) {
            if ($tipoFiltroFecha === 'primera_cita') {
                $subquery = DB::table('citas as sub_citas')
                    ->select('sub_citas.user_id')
                    ->where('sub_citas.psicologo_id', $psicologoId)
                    ->where('sub_citas.estado', 'realizada')
                    ->groupBy('sub_citas.user_id');

                if (!empty($fechaDesde)) {
                    $subquery->havingRaw('MIN(sub_citas.fecha) >= ?', [$fechaDesde]);
                }
                if (!empty($fechaHasta)) {
                    $subquery->havingRaw('MIN(sub_citas.fecha) <= ?', [$fechaHasta]);
                }

                $query->whereIn('usuario.id_usuario', $subquery->pluck('user_id'));
            } elseif ($tipoFiltroFecha === 'ultima_cita') {
                $subquery = DB::table('citas as sub_citas')
                    ->select('sub_citas.user_id')
                    ->where('sub_citas.psicologo_id', $psicologoId)
                    ->where('sub_citas.estado', 'realizada')
                    ->groupBy('sub_citas.user_id');

                if (!empty($fechaDesde)) {
                    $subquery->havingRaw('MAX(sub_citas.fecha) >= ?', [$fechaDesde]);
                }
                if (!empty($fechaHasta)) {
                    $subquery->havingRaw('MAX(sub_citas.fecha) <= ?', [$fechaHasta]);
                }

                $query->whereIn('usuario.id_usuario', $subquery->pluck('user_id'));
            } else {
                if (!empty($fechaDesde)) {
                    $query->whereDate('citas.fecha', '>=', $fechaDesde);
                }
                if (!empty($fechaHasta)) {
                    $query->whereDate('citas.fecha', '<=', $fechaHasta);
                }
            }
        }

        if (!empty($filters['prioridad'])) {
            $query->where('citas.prioridad', $filters['prioridad']);
        }

        if (!empty($filters['estado_animo_id'])) {
            $query->where('citas.estado_animo_id', $filters['estado_animo_id']);
        }

        if (!empty($filters['enfermedad_id'])) {
            $query->leftJoin('historia_enfermedad as he_filter', 'historia_clinicas.id', '=', 'he_filter.historia_clinica_id')
                  ->where('he_filter.enfermedad_id', $filters['enfermedad_id'])
                  ->where('he_filter.status', 1);
        }

        $historiasBase = $query->select(
                'usuario.id_usuario as id',
                DB::raw("CONCAT(persona.nombre_persona, ' ', persona.apellido_persona) as patient_name"),
                'persona.email_persona as email',
                'citas.fecha as ultima_sesion',
                'citas.notas'
            )
            ->orderBy('citas.fecha', 'desc')
            ->get()
            ->unique('id');

        if (!empty($filters['avance_id'])) {
            $historiasBase = $historiasBase->filter(function ($item) use ($filters) {
                if (empty($item->notas)) return false;
                try {
                    $notasDecrypted = Crypt::decryptString($item->notas);
                    $notasArr = json_decode($notasDecrypted, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($notasArr)) {
                        return isset($notasArr['avance_estado']) && $notasArr['avance_estado'] == $filters['avance_id'];
                    }
                } catch (Exception $e) {}
                return false;
            });
        }

        return $historiasBase->map(function ($item) use ($psicologoId) {
            $h = self::obtenerPorPaciente($item->id, $psicologoId);
            $citasRealizadas = DB::table('citas')
                ->where('user_id', $item->id)
                ->where('psicologo_id', $psicologoId)
                ->where('estado', 'realizada')
                ->where('status', 1)
                ->get(['id', 'motivo']);

            $countCitas = $citasRealizadas->filter(function ($cita) {
                try {
                    return Crypt::decryptString($cita->motivo) !== 'Nota de Evolución (Manual)';
                } catch (Exception $e) {
                    return true;
                }
            })->count();

            $paciente = self::obtenerPaciente($item->id);
            if ($paciente && $paciente->persona) {
                $p = $paciente->persona;
                $paciente->name = trim(($p->nombre_persona ?? '') . ' ' . ($p->apellido_persona ?? ''));
                $paciente->avatar = strtoupper(
                    substr($p->nombre_persona ?? '', 0, 1) . substr($p->apellido_persona ?? '', 0, 1)
                );
            }

            $diagnosticoText = 'Sin diagnóstico';
            if ($h) {
                $diagSegment = DB::table('historia_secciones_personalizadas')
                    ->join('historia_segmentos_personalizados', 'historia_secciones_personalizadas.id', '=', 'historia_segmentos_personalizados.seccion_id')
                    ->where('historia_secciones_personalizadas.historia_clinica_id', $h->id)
                    ->where('historia_secciones_personalizadas.titulo', 'Diagnóstico')
                    ->where('historia_segmentos_personalizados.titulo', 'Diagnóstico Inicial (Resumen)')
                    ->first();

                if ($diagSegment) {
                    $segmento = DB::table('historia_secciones_personalizadas')
                        ->join('historia_segmentos_personalizados', 'historia_secciones_personalizadas.id', '=', 'historia_segmentos_personalizados.seccion_id')
                        ->where('historia_segmentos_personalizados.id', $diagSegment->id)
                        ->select('historia_segmentos_personalizados.*')
                        ->first();

                    if ($segmento && !empty($segmento->contenido)) {
                        try {
                            $diagnosticoText = Crypt::decryptString($segmento->contenido);
                        } catch (Exception $e) {
                        }
                    }
                }
            }

            return [
                'id'              => $item->id,
                'paciente_name'   => $item->patient_name,
                'email'           => $item->email,
                'ultima_sesion'   => $item->ultima_sesion,
                'notas'           => $item->notas,
                'citas_realizadas'=> $countCitas,
                'diagnostico'     => $diagnosticoText,
                'paciente'        => $paciente,
            ];
        });
    }

    public static function vincularEnfermedad($historiaId, $enfermedadId, $contexto)
    {
        try {
            return DB::transaction(function () use ($historiaId, $enfermedadId, $contexto) {
                $existe = null;
                if (str_starts_with($contexto, 'seg_')) {
                    $segmentoId = str_replace('seg_', '', $contexto);
                    $segmento = DB::table('historia_segmentos_personalizados')->where('id', $segmentoId)->first();
                    if ($segmento) {
                        $segmentosSeccion = DB::table('historia_segmentos_personalizados')->where('seccion_id', $segmento->seccion_id)->pluck('id')->map(fn($id) => 'seg_' . $id)->toArray();

                        $existe = DB::table('historia_enfermedad')
                            ->where('historia_clinica_id', $historiaId)
                            ->where('enfermedad_id', $enfermedadId)
                            ->whereIn('contexto', $segmentosSeccion)
                            ->first();
                    }
                } else {
                    $existe = DB::table('historia_enfermedad')
                        ->where('historia_clinica_id', $historiaId)
                        ->where('enfermedad_id', $enfermedadId)
                        ->where('contexto', $contexto)
                        ->first();
                }

                if (!$existe) {
                    $id = DB::table('historia_enfermedad')->insertGetId([
                        'historia_clinica_id' => $historiaId,
                        'enfermedad_id' => $enfermedadId,
                        'contexto' => $contexto,
                        'created_at' => now(),
                    ]);
                    
                    $enfermedad = DB::table('enfermedades')->where('id', $enfermedadId)->first();
                    return [
                        'success' => true,
                        'link_id' => $id,
                        'nombre' => $enfermedad ? $enfermedad->nombre : '',
                        'contexto' => $contexto
                    ];
                }
                
                return ['success' => false, 'message' => 'Ya está vinculada'];
            });
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error al vincular: ' . \App\Support\Seguridad\MensajeError::de($e)];
        }
    }

    public static function desvincularEnfermedad($linkId)
    {        
        try {
            DB::table('historia_enfermedad')
                ->where('id', $linkId)
                ->update([
                    'status' => 0,
                    'updated_at' => now()
                ]);

            return [true, 'Enfermedad desvinculada correctamente.'];
        } catch (Exception $e) {
            return [false, 'Error al desvincular: ' . \App\Support\Seguridad\MensajeError::de($e)];
        }
    }

    public static function obtenerEnfermedadesVinculadas($historiaId)
    {
        return DB::table('historia_enfermedad')
            ->join('enfermedades', 'historia_enfermedad.enfermedad_id', '=', 'enfermedades.id')
            ->where('historia_enfermedad.historia_clinica_id', $historiaId)
            ->where('historia_enfermedad.status', 1)
            ->select('enfermedades.*', 'historia_enfermedad.id as link_id', 'historia_enfermedad.contexto')
            ->get()
            ->groupBy('contexto');
    }

    public static function verificarAcceso($pacienteId, $psicologoId)
    {
        return self::where('user_id', $pacienteId)
            ->where('psicologo_id', $psicologoId)
            ->first();
    }

    public static function obtenerPorId($id)
    {
        return self::find($id);
    }

    public static function obtenerPorPacienteOrFail($pacienteId, $psicologoId)
    {
        $historia = self::obtenerPorPaciente($pacienteId, $psicologoId);

        if (!$historia) {
            abort(404);
        }

        return $historia;
    }

    public static function obtenerSeccionesConSegmentos($historiaId)
    {
        $secciones = DB::table('historia_secciones_personalizadas')
            ->where('historia_clinica_id', $historiaId)
            ->where('status', 1)
            ->orderBy('orden')
            ->get();

        foreach ($secciones as $seccion) {
            $seccion->segmentos = DB::table('historia_segmentos_personalizados')
                ->where('seccion_id', $seccion->id)
                ->get()
                ->map(function ($segmento) {
                    if (!empty($segmento->contenido)) {
                        try {
                            $segmento->contenido = Crypt::decryptString($segmento->contenido);
                        } catch (Exception $e) {
                        }
                    }
                    return $segmento;
                });
        }

        return $secciones;
    }
}