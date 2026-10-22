<?php

namespace App\Models\salud;

use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use App\Models\Usuario;
use Carbon\Carbon;
use Exception;

class HistoriaClinica extends Model
{
    use RegistraAutoria;
    use SoftDeletes;

    protected $table = 'historia_clinica';

    protected $primaryKey = 'id_hcl';

    protected $fillable = [
        'paciente_use_id',
        'psicologo_use_id',
        'status_id',
    ];

    public function paciente()
    {
        return $this->belongsTo(Usuario::class, 'paciente_use_id', 'id_use');
    }

    public function psicologo()
    {
        return $this->belongsTo(Usuario::class, 'psicologo_use_id', 'id_use');
    }

    public function secciones()
    {
        return $this->hasMany(SeccionPersonalizada::class, 'hcl_id', 'id_hcl');
    }

    public static function obtenerPaciente($userId)
    {
        return Usuario::with('persona')->where('id_use', $userId)->first();
    }

    public static function obtenerPsicologo($psicologoId)
    {
        return Usuario::with('persona')->where('id_use', $psicologoId)->first();
    }

    public static function obtenerSeccionesPersonalizadas($historiaId)
    {
        return DB::table('historia_seccion_personalizada')
            ->where('hcl_id', $historiaId)
            ->whereNull('deleted_at')
            ->orderBy('orden_hsp')
            ->get();
    }

    public static function obtenerPorPaciente($pacienteId, $psicologoId)
    {
        return self::where('paciente_use_id', $pacienteId)
            ->where('psicologo_use_id', $psicologoId)
            ->first();
    }

    public static function iniciarHistoria($pacienteId, $psicologoId)
    {
        try {
            DB::beginTransaction();
            $historia = self::obtenerPorPaciente($pacienteId, $psicologoId);

            if (!$historia) {
                DB::table('historia_clinica')->insertGetId([
                    'paciente_use_id' => $pacienteId,
                    'psicologo_use_id' => $psicologoId,
                    'created_id' => Auth::id(),
                    'created_at' => now(),
                    'updated_at' => null
                ], 'id_hcl');
                $historia = self::obtenerPorPaciente($pacienteId, $psicologoId);
            }

            $seccionesActivas = DB::table('historia_seccion_personalizada')
                ->where('hcl_id', $historia->id_hcl)
                ->whereNull('deleted_at')
                ->count();

            if ($seccionesActivas === 0) {
                $plantillaGlobal = DB::table('plantilla_historia_clinica')
                    ->where('psicologo_use_id', $psicologoId)
                    ->whereNull('deleted_at')
                    ->orderBy('updated_at', 'desc')
                    ->first();

                if ($plantillaGlobal) {
                    $secciones = json_decode($plantillaGlobal->secciones_phc, true) ?? [];
                    $maxOrden = 0;

                    foreach ($secciones as $seccionData) {
                        $maxOrden++;
                        $seccionId = DB::table('historia_seccion_personalizada')->insertGetId([
                            'hcl_id' => $historia->id_hcl,
                            'titulo_hsp' => $seccionData['titulo'],
                            'descripcion_general_hsp' => $seccionData['descripcion_general'] ?? null,
                            'orden_hsp' => $maxOrden,
                            'created_id' => Auth::id(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ], 'id_hsp');

                        $segmentos = $seccionData['segmentos'] ?? [];
                        foreach ($segmentos as $indexSeg => $segmentoTitulo) {
                            if (!empty(trim($segmentoTitulo))) {
                                DB::table('historia_segmento_personalizado')->insert([
                                    'hsp_id' => $seccionId,
                                    'titulo_hsm' => $segmentoTitulo,
                                    'contenido_hsm' => null,
                                    'orden_hsm' => $indexSeg + 1,
                                    'created_id' => Auth::id(),
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
            $q->where('psicologo_use_id', $psicologoId);
        })
        ->whereHas('persona', function ($q) use ($query) {
            $q->where(function ($q) use ($query) {
                $q->where('nombres_per', 'like', '%' . $query . '%')
                  ->orWhere('apellidos_per', 'like', '%' . $query . '%')
                  ->orWhere('cedula_per', 'like', '%' . $query . '%');
            });
        })
        ->with('persona')
        ->get()
        ->map(function ($usuario) {
            $persona = $usuario->persona;
            return (object) [
                'id' => $usuario->id_use,
                'name' => trim(($persona->nombres_per ?? '') . ' ' . ($persona->apellidos_per ?? '')),
                'email' => $persona->correo_per ?? ''
            ];
        });
    }

    public static function obtenerListado($psicologoId, $search = null, $filters = [])
    {
        $query = DB::table('cita_psicologica')
            ->join('user', 'cita_psicologica.paciente_use_id', '=', 'user.id_use')
            ->join('persona', 'user.per_id', '=', 'persona.id_per')
            ->leftJoin('historia_clinica', function ($join) {
                $join->on('user.id_use', '=', 'historia_clinica.paciente_use_id')
                    ->on('historia_clinica.psicologo_use_id', '=', 'cita_psicologica.psicologo_use_id');
            })
            ->where('cita_psicologica.psicologo_use_id', $psicologoId)
            ->where('cita_psicologica.estado_cita_cps', 'realizada')
            ->whereNull('cita_psicologica.deleted_at');

        if (!empty($search)) {
            $query->leftJoin('historia_enfermedad', 'historia_clinica.id_hcl', '=', 'historia_enfermedad.hcl_id')
                  ->leftJoin('enfermedad', 'historia_enfermedad.efm_id', '=', 'enfermedad.id_efm')
                  ->where(function ($q) use ($search) {
                      $q->where('persona.nombres_per', 'like', "%{$search}%")
                        ->orWhere('persona.apellidos_per', 'like', "%{$search}%")
                        ->orWhere('persona.cedula_per', 'like', "%{$search}%");
                  });
        }

        if (!empty($filters['pnf'])) {
            $query->join('persona_programa', 'persona.id_per', '=', 'persona_programa.per_id')
                  ->where('persona_programa.prg_id', $filters['pnf']);
        }

        if (!empty($filters['edad'])) {
            $query->whereYear('persona.fecha_nacimiento_per', Carbon::now()->subYears($filters['edad'])->year);
        }

        $tipoFiltroFecha = $filters['tipo_filtro_fecha'] ?? 'rango';
        $fechaDesde = $filters['fecha_desde'] ?? null;
        $fechaHasta = $filters['fecha_hasta'] ?? null;

        if (!empty($fechaDesde) || !empty($fechaHasta)) {
            if ($tipoFiltroFecha === 'primera_cita') {
                $subquery = DB::table('cita_psicologica as sub_citas')
                    ->select('sub_citas.paciente_use_id')
                    ->where('sub_citas.psicologo_use_id', $psicologoId)
                    ->where('sub_citas.estado_cita_cps', 'realizada')
                    ->whereNull('sub_citas.deleted_at')
                    ->groupBy('sub_citas.paciente_use_id');

                if (!empty($fechaDesde)) {
                    $subquery->havingRaw('MIN(sub_citas.fecha_cps) >= ?', [$fechaDesde]);
                }
                if (!empty($fechaHasta)) {
                    $subquery->havingRaw('MIN(sub_citas.fecha_cps) <= ?', [$fechaHasta]);
                }

                $query->whereIn('user.id_use', $subquery->pluck('paciente_use_id'));
            } elseif ($tipoFiltroFecha === 'ultima_cita') {
                $subquery = DB::table('cita_psicologica as sub_citas')
                    ->select('sub_citas.paciente_use_id')
                    ->where('sub_citas.psicologo_use_id', $psicologoId)
                    ->where('sub_citas.estado_cita_cps', 'realizada')
                    ->whereNull('sub_citas.deleted_at')
                    ->groupBy('sub_citas.paciente_use_id');

                if (!empty($fechaDesde)) {
                    $subquery->havingRaw('MAX(sub_citas.fecha_cps) >= ?', [$fechaDesde]);
                }
                if (!empty($fechaHasta)) {
                    $subquery->havingRaw('MAX(sub_citas.fecha_cps) <= ?', [$fechaHasta]);
                }

                $query->whereIn('user.id_use', $subquery->pluck('paciente_use_id'));
            } else {
                if (!empty($fechaDesde)) {
                    $query->whereDate('cita_psicologica.fecha_cps', '>=', $fechaDesde);
                }
                if (!empty($fechaHasta)) {
                    $query->whereDate('cita_psicologica.fecha_cps', '<=', $fechaHasta);
                }
            }
        }

        if (!empty($filters['prioridad'])) {
            $query->where('cita_psicologica.prioridad_texto_cps', $filters['prioridad']);
        }

        if (!empty($filters['esa_id'])) {
            $query->where('cita_psicologica.esa_id', $filters['esa_id']);
        }

        if (!empty($filters['enfermedad_id'])) {
            $query->leftJoin('historia_enfermedad as he_filter', 'historia_clinica.id_hcl', '=', 'he_filter.hcl_id')
                  ->where('he_filter.efm_id', $filters['enfermedad_id'])
                  ->whereNull('he_filter.deleted_at');
        }

        $historiasBase = $query->select(
                'user.id_use as id',
                DB::raw("CONCAT(persona.nombres_per, ' ', persona.apellidos_per) as patient_name"),
                'persona.correo_per as email',
                'cita_psicologica.fecha_cps as ultima_sesion',
                'cita_psicologica.notas_cps'
            )
            ->orderBy('cita_psicologica.fecha_cps', 'desc')
            ->get()
            ->unique('id');

        if (!empty($filters['avance_id'])) {
            $historiasBase = $historiasBase->filter(function ($item) use ($filters) {
                if (empty($item->notas_cps)) return false;
                try {
                    $notasDecrypted = Crypt::decryptString($item->notas_cps);
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
            $citasRealizadas = DB::table('cita_psicologica')
                ->where('paciente_use_id', $item->id)
                ->where('psicologo_use_id', $psicologoId)
                ->where('estado_cita_cps', 'realizada')
                ->where('status_id', 1)
                ->whereNull('deleted_at')
                ->get(['id_cps', 'motivo_cps']);

            $countCitas = $citasRealizadas->filter(function ($cita) {
                try {
                    return Crypt::decryptString($cita->motivo_cps) !== 'Nota de Evolución (Manual)';
                } catch (Exception $e) {
                    return true;
                }
            })->count();

            $paciente = self::obtenerPaciente($item->id);
            if ($paciente && $paciente->persona) {
                $p = $paciente->persona;
                $paciente->name = trim(($p->nombres_per ?? '') . ' ' . ($p->apellidos_per ?? ''));
                $paciente->avatar = strtoupper(
                    substr($p->nombres_per ?? '', 0, 1) . substr($p->apellidos_per ?? '', 0, 1)
                );
            }

            $diagnosticoText = 'Sin diagnóstico';
            if ($h) {
                $segmento = DB::table('historia_seccion_personalizada')
                    ->join('historia_segmento_personalizado', 'historia_seccion_personalizada.id_hsp', '=', 'historia_segmento_personalizado.hsp_id')
                    ->where('historia_seccion_personalizada.hcl_id', $h->id_hcl)
                    ->whereNull('historia_seccion_personalizada.deleted_at')
                    ->whereNull('historia_segmento_personalizado.deleted_at')
                    ->where('historia_seccion_personalizada.titulo_hsp', 'Diagnóstico')
                    ->where('historia_segmento_personalizado.titulo_hsm', 'Diagnóstico Inicial (Resumen)')
                    ->select('historia_segmento_personalizado.*')
                    ->first();

                if ($segmento && !empty($segmento->contenido_hsm)) {
                    try {
                        $diagnosticoText = Crypt::decryptString($segmento->contenido_hsm);
                    } catch (Exception $e) {
                    }
                }
            }

            return [
                'id'              => $item->id,
                'paciente_name'   => $item->patient_name,
                'email'           => $item->email,
                'ultima_sesion'   => $item->ultima_sesion,
                'notas'           => $item->notas_cps,
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
                    $segmento = DB::table('historia_segmento_personalizado')->where('id_hsm', $segmentoId)->first();
                    if ($segmento) {
                        $segmentosSeccion = DB::table('historia_segmento_personalizado')
                            ->where('hsp_id', $segmento->hsp_id)
                            ->pluck('id_hsm')
                            ->map(fn($id) => 'seg_' . $id)
                            ->toArray();

                        $existe = DB::table('historia_enfermedad')
                            ->where('hcl_id', $historiaId)
                            ->where('efm_id', $enfermedadId)
                            ->whereIn('contexto_hie', $segmentosSeccion)
                            ->whereNull('deleted_at')
                            ->first();
                    }
                } else {
                    $existe = DB::table('historia_enfermedad')
                        ->where('hcl_id', $historiaId)
                        ->where('efm_id', $enfermedadId)
                        ->where('contexto_hie', $contexto)
                        ->whereNull('deleted_at')
                        ->first();
                }

                if (!$existe) {
                    $id = DB::table('historia_enfermedad')->insertGetId([
                        'hcl_id' => $historiaId,
                        'efm_id' => $enfermedadId,
                        'contexto_hie' => $contexto,
                        'created_id' => Auth::id(),
                        'created_at' => now(),
                    ], 'id_hie');

                    $enfermedad = DB::table('enfermedad')->where('id_efm', $enfermedadId)->first();
                    return [
                        'success' => true,
                        'link_id' => $id,
                        'nombre' => $enfermedad ? $enfermedad->nombre_efm : '',
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
                ->where('id_hie', $linkId)
                ->update([
                    'deleted_at' => now(),
                    'updated_id' => Auth::id(),
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
            ->join('enfermedad', 'historia_enfermedad.efm_id', '=', 'enfermedad.id_efm')
            ->where('historia_enfermedad.hcl_id', $historiaId)
            ->whereNull('historia_enfermedad.deleted_at')
            ->whereNull('enfermedad.deleted_at')
            ->select('enfermedad.*', 'historia_enfermedad.id_hie as link_id', 'historia_enfermedad.contexto_hie')
            ->get()
            ->groupBy('contexto_hie');
    }

    public static function verificarAcceso($pacienteId, $psicologoId)
    {
        return self::where('paciente_use_id', $pacienteId)
            ->where('psicologo_use_id', $psicologoId)
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
        $secciones = DB::table('historia_seccion_personalizada')
            ->where('hcl_id', $historiaId)
            ->whereNull('deleted_at')
            ->orderBy('orden_hsp')
            ->get();

        foreach ($secciones as $seccion) {
            $seccion->segmentos = DB::table('historia_segmento_personalizado')
                ->where('hsp_id', $seccion->id_hsp)
                ->whereNull('deleted_at')
                ->orderBy('orden_hsm')
                ->orderBy('id_hsm')
                ->get()
                ->map(function ($segmento) {
                    if (!empty($segmento->contenido_hsm)) {
                        try {
                            $segmento->contenido_hsm = Crypt::decryptString($segmento->contenido_hsm);
                        } catch (Exception $e) {
                        }
                    }
                    return $segmento;
                });
        }

        return $secciones;
    }
}
