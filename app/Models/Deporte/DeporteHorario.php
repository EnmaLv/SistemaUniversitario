<?php

namespace App\Models\Deporte;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DeporteHorario extends Model
{
    protected $table = 'deporte_horario';
    protected $fillable = [
        'id_calendario', 'id_actividad', 'id_espacio', 'dia_semana', 'fecha_especifica', 'semana_inicio',
        'hora_inicio', 'hora_fin', 'cupo_maximo', 'observaciones', 'estado', 'motivo_cancelacion', 'creado_por'
    ];

    protected $casts = [
        'fecha_especifica' => 'date',
        'semana_inicio' => 'date',
    ];

    public function calendario()
    {
        return $this->belongsTo(DeporteCalendario::class, 'id_calendario');
    }

    public function actividad()
    {
        return $this->belongsTo(DeporteActividad::class, 'id_actividad');
    }

    public function espacio()
    {
        return $this->belongsTo(DeporteEspacio::class, 'id_espacio');
    }

    /**
     * Comprueba solapamiento según regla aprobada.
     * Devuelve true si existe conflicto.
     */
    public static function overlaps($idEspacio, $diaSem, $inicio, $fin, $excludeId = null, $fechaEspecifica = null, $semanaInicio = null)
    {
        $query = DB::table('deporte_horario')
            ->where('estado', 'activo')
            ->whereNotNull('semana_inicio');

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $semanaObjetivo = $semanaInicio
            ? Carbon::parse($semanaInicio)->startOfWeek(Carbon::MONDAY)->format('Y-m-d')
            : ($fechaEspecifica ? Carbon::parse($fechaEspecifica)->startOfWeek(Carbon::MONDAY)->format('Y-m-d') : null);

        if ($semanaObjetivo) {
            $query->whereDate('semana_inicio', $semanaObjetivo);
        }

        $horaInicio = Carbon::parse($inicio)->format('H:i:s');
        $horaFin = Carbon::parse($fin)->format('H:i:s');

        $rows = $query->get();

        foreach ($rows as $r) {
            if ($semanaObjetivo && $r->semana_inicio) {
                $rSemana = Carbon::parse($r->semana_inicio)->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
                if ($rSemana !== $semanaObjetivo) {
                    continue;
                }
            }

            $rHoraInicio = Carbon::parse($r->hora_inicio)->format('H:i:s');
            $rHoraFin = Carbon::parse($r->hora_fin)->format('H:i:s');

            $existingHasFecha = !is_null($r->fecha_especifica);
            $newHasFecha = !is_null($fechaEspecifica);

            $conflictDay = false;

            if ($newHasFecha && $existingHasFecha) {
                if (Carbon::parse($r->fecha_especifica)->toDateString() === Carbon::parse($fechaEspecifica)->toDateString()) {
                    $conflictDay = true;
                }
            } elseif (!$newHasFecha && !$existingHasFecha) {
                if ($r->dia_semana && $diaSem && strtolower((string) $r->dia_semana) === strtolower((string) $diaSem)) {
                    $conflictDay = true;
                }
            } elseif ($newHasFecha && !$existingHasFecha) {
                $dowNew = ['domingo','lunes','martes','miercoles','jueves','viernes','sabado'][Carbon::parse($fechaEspecifica)->dayOfWeek];
                if ($r->dia_semana && strtolower((string) $r->dia_semana) === strtolower((string) $dowNew)) {
                    $conflictDay = true;
                }
            } elseif (!$newHasFecha && $existingHasFecha) {
                if ($r->fecha_especifica && $diaSem) {
                    $dowExisting = ['domingo','lunes','martes','miercoles','jueves','viernes','sabado'][Carbon::parse($r->fecha_especifica)->dayOfWeek];
                    if (strtolower((string) $dowExisting) === strtolower((string) $diaSem)) {
                        $conflictDay = true;
                    }
                }
            }

            if (!$conflictDay) {
                continue;
            }

            if ($rHoraInicio < $horaFin && $rHoraFin > $horaInicio) {
                return true;
            }
        }

        return false;
    }
}
