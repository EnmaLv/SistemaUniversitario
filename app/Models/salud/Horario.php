<?php

namespace App\Models\salud;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Horario
{
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 2;

    public const ORDEN_DIAS = "CASE dia_hpr WHEN 'Lunes' THEN 1 WHEN 'Martes' THEN 2 WHEN 'Miércoles' THEN 3 WHEN 'Jueves' THEN 4 WHEN 'Viernes' THEN 5 ELSE 6 END";

    public static function obtenerUsuario($userId)
    {
        return DB::table('user')->where('id_use', $userId)->first();
    }

    public static function obtenerGrupoHorario($grupoHorarioId)
    {
        return DB::table('grupo_horario_atencion')->where('id_gha', $grupoHorarioId)->first();
    } 

    public static function obtenerPorId($id)
    {
        return DB::table('horario_profesional')->where('id_hpr', $id)->first();
    }

    public static function diasSemana(): array
    {
        return [
            'Lunes',
            'Martes',
            'Miércoles',
            'Jueves',
            'Viernes',
        ];
    }

    public static function eliminar($id)
    {
        try {
            DB::beginTransaction();
            $res = DB::table('horario_profesional')
                ->where('id_hpr', $id)
                ->whereNull('deleted_at')
                ->update([
                    'deleted_at' => Carbon::now(),
                    'updated_id' => Auth::id(),
                    'updated_at' => Carbon::now(),
                ]);
            DB::commit();
            return $res;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function asignarGrupo($horarioIds, $grupoId)
    {
        try {
            DB::beginTransaction();
            $res = DB::table('horario_profesional')
                ->whereIn('id_hpr', $horarioIds)
                ->whereNull('deleted_at')
                ->update([
                    'gha_id' => $grupoId,
                    'status_id' => self::STATUS_ACTIVE,
                    'updated_id' => Auth::id(),
                    'updated_at' => Carbon::now(),
                ]);
            DB::commit();
            return $res;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function crear($data)
    {
        try {
            DB::beginTransaction();
            $id = DB::table('horario_profesional')->insertGetId(array_merge($data, [
                'created_id' => Auth::id(),
                'created_at' => Carbon::now(),
                'updated_at' => null,
            ]), 'id_hpr');
            DB::commit();
            return $id;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function actualizar($id, $data)
    {
        try {
            DB::beginTransaction();
            $res = DB::table('horario_profesional')
                ->where('id_hpr', $id)
                ->whereNull('deleted_at')
                ->update(array_merge($data, ['updated_id' => Auth::id(), 'updated_at' => Carbon::now()]));
            DB::commit();
            return $res;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function hasPendingCitas($userId)
    {
        return DB::table('cita_psicologica')
            ->where('psicologo_use_id', $userId)
            ->whereIn('estado_cita_cps', ['pendiente', 'confirmada'])
            ->whereNull('deleted_at')
            ->exists();
    }

    public static function overlaps($userId, $dia, $inicio, $fin, $excludeId = null, $grupoId = null)
    {
        $query = DB::table('horario_profesional')
            ->where('psicologo_use_id', $userId)
            ->where('dia_hpr', $dia)
            ->whereNull('deleted_at');

        if ($grupoId) {
            $query->where('gha_id', $grupoId);
        } else {
            $query->whereNull('gha_id');
        }

        if ($excludeId) {
            $query->where('id_hpr', '!=', $excludeId);
        }

        $query->where(function ($q) use ($inicio, $fin) {
            $q->where('hora_inicio_hpr', '<', $fin)
            ->where('hora_fin_hpr', '>', $inicio);
        });

        $overlaps = $query->get();
        foreach ($overlaps as $match) {
            if ($match->hora_inicio_hpr == $fin || $match->hora_fin_hpr == $inicio) {
                continue;
            }
            return true;
        }

        return false;
    }

    public static function normalizeTime($time)
    {
        if (empty($time)) return null;
        return Carbon::parse($time)->format('H:i');
    }

    public static function obtenerPorGrupo($grupoId)
    {
        return DB::table('horario_profesional')
            ->where('gha_id', $grupoId)
            ->whereNull('deleted_at')
            ->orderByRaw(self::ORDEN_DIAS)
            ->orderBy('hora_inicio_hpr')
            ->get();
    }

    public static function obtenerPorFiltros($userId, $grupoId = null, $filtroDia = null)
    {
        $query = DB::table('horario_profesional')
            ->where('psicologo_use_id', $userId)
            ->whereNull('deleted_at');

        if ($grupoId) {
            $query->where('gha_id', $grupoId);
        } else {
            $query->whereNull('gha_id');
        }

        if ($filtroDia) {
            $query->where('dia_hpr', $filtroDia);
        }

        return $query->orderByRaw(self::ORDEN_DIAS)
            ->orderBy('hora_inicio_hpr')
            ->get();
    }

    public static function hasBloques($userId)
    {
        return DB::table('horario_profesional')
            ->where('psicologo_use_id', $userId)
            ->whereNull('deleted_at')
            ->exists();
    }
}
