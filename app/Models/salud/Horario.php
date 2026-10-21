<?php

namespace App\Models\salud;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Horario
{
    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 2;

    public static function obtenerUsuario($userId)
    {
        return DB::table('user')->where('id_use', $userId)->first();
    }

    public static function obtenerGrupoHorario($grupoHorarioId)
    {
        return DB::table('grupos_horarios')->where('id', $grupoHorarioId)->first();
    } 

    public static function obtenerPorId($id)
    {
        return DB::table('horarios')->where('id', $id)->first();
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
            $res = DB::table('horarios')->where('id', $id)->update([
                'activo' => self::STATUS_DELETED,
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
            $res = DB::table('horarios')
                ->whereIn('id', $horarioIds)
                ->update(['grupo_horario_id' => $grupoId, 'activo' => self::STATUS_ACTIVE]);
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
            $id = DB::table('horarios')->insertGetId(array_merge($data, [
                'created_at' => Carbon::now(),
                'updated_at' => null,
            ]));
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
            $res = DB::table('horarios')
                ->where('id', $id)
                ->update(array_merge($data, ['updated_at' => Carbon::now()]));
            DB::commit();
            return $res;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function hasPendingCitas($userId)
    {
        return DB::table('citas')
            ->where('psicologo_id', $userId)
            ->whereIn('estado', ['pendiente', 'confirmada'])
            ->exists();
    }

    public static function overlaps($userId, $dia, $inicio, $fin, $excludeId = null, $grupoId = null)
    {
        $query = DB::table('horarios')
            ->where('user_id', $userId)
            ->where('dia', $dia)
            ->whereIn('activo', [self::STATUS_ACTIVE, self::STATUS_INACTIVE]);

        if ($grupoId) {
            $query->where('grupo_horario_id', $grupoId);
        } else {
            $query->whereNull('grupo_horario_id');
        }

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $query->where(function ($q) use ($inicio, $fin) {
            $q->where('hora_inicio', '<', $fin)
            ->where('hora_fin', '>', $inicio);
        });

        $overlaps = $query->get();
        foreach ($overlaps as $match) {
            if ($match->hora_inicio == $fin || $match->hora_fin == $inicio) {
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
        return DB::table('horarios')
            ->where('grupo_horario_id', $grupoId)
            ->whereIn('activo', [self::STATUS_ACTIVE, self::STATUS_INACTIVE])
            ->orderByRaw("CASE dia WHEN 'Lunes' THEN 1 WHEN 'Martes' THEN 2 WHEN 'Miércoles' THEN 3 WHEN 'Jueves' THEN 4 WHEN 'Viernes' THEN 5 ELSE 6 END")
            ->orderBy('hora_inicio')
            ->get();
    }

    public static function obtenerPorFiltros($userId, $grupoId = null, $filtroDia = null)
    {
        $query = DB::table('horarios')
            ->where('user_id', $userId)
            ->whereIn('activo', [self::STATUS_ACTIVE, self::STATUS_INACTIVE]);

        if ($grupoId) {
            $query->where('grupo_horario_id', $grupoId);
        } else {
            $query->whereNull('grupo_horario_id');
        }

        if ($filtroDia) {
            $query->where('dia', $filtroDia);
        }

        return $query->orderByRaw("CASE dia WHEN 'Lunes' THEN 1 WHEN 'Martes' THEN 2 WHEN 'Miércoles' THEN 3 WHEN 'Jueves' THEN 4 WHEN 'Viernes' THEN 5 ELSE 6 END")
            ->orderBy('hora_inicio')
            ->get();
    }

    public static function hasBloques($userId)
    {
        return DB::table('horarios')
            ->where('user_id', $userId)
            ->whereIn('activo', [self::STATUS_ACTIVE, self::STATUS_INACTIVE])
            ->exists();
    }
}
