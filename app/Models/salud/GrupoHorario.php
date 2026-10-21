<?php

namespace App\Models\salud;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GrupoHorario
{
    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 2;

    public static function obtenerUsuario($userId)
    {
        return DB::table('user')->where('id_use', $userId)->first();
    }

    public static function obtenerHorarios($grupoId)
    {
        return DB::table('horarios')->where('grupo_horario_id', $grupoId)->get();
    }

    public static function eliminar($id)
    {
        try {
            DB::beginTransaction();
            $now = Carbon::now();
            
            $updated = DB::table('grupos_horarios')
                ->where('id', $id)
                ->update([
                    'activo' => self::STATUS_DELETED,
                    'updated_at' => $now,
                ]);

            DB::table('horarios')
                ->where('grupo_horario_id', $id)
                ->update([
                    'activo' => Horario::STATUS_DELETED,
                    'updated_at' => $now,
                ]);

            DB::commit();
            return $updated;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function activate($id)
    {
        try {
            DB::beginTransaction();

            $userId = Auth::id();
            if (!$userId) {
                return [false, 'Error: Usuario no autenticado.'];
            }

            DB::table('grupos_horarios')
                ->where('user_id', $userId)
                ->where('activo', '!=', self::STATUS_DELETED)
                ->update(['activo' => self::STATUS_INACTIVE, 'updated_at' => Carbon::now()]);

            DB::table('horarios')
                ->where('user_id', $userId)
                ->where('activo', '!=', Horario::STATUS_DELETED)
                ->update(['activo' => Horario::STATUS_INACTIVE, 'updated_at' => Carbon::now()]);

            $updated = DB::table('grupos_horarios')->where('id', $id)->update([
                'activo' => self::STATUS_ACTIVE,
                'updated_at' => Carbon::now(),
            ]);

            DB::table('horarios')->where('grupo_horario_id', $id)
                ->where('activo', '!=', Horario::STATUS_DELETED)
                ->update([
                    'activo' => self::STATUS_ACTIVE,
                    'updated_at' => Carbon::now(),
                ]);

            DB::commit();
            return [true, 'El horario ha sido activado correctamente.'];

        } catch (\Exception $e) {
            DB::rollBack();
            return [false, 'Error al activar el horario: ' . \App\Support\Seguridad\MensajeError::de($e)];
        }
    }

    public static function deactivate($id)
    {
        try {
            DB::beginTransaction();

            $updated = DB::table('grupos_horarios')->where('id', $id)->update([
                'activo' => self::STATUS_INACTIVE,
                'updated_at' => Carbon::now(),
            ]);

            DB::table('horarios')->where('grupo_horario_id', $id)
                ->where('activo', '!=', Horario::STATUS_DELETED)
                ->update([
                    'activo' => self::STATUS_INACTIVE,
                    'updated_at' => Carbon::now(),
                ]);

            DB::commit();
            return [true, 'El horario ha sido desactivado correctamente.'];

        } catch (\Exception $e) {
            DB::rollBack();
            return [false, 'Error al desactivar el horario: ' . \App\Support\Seguridad\MensajeError::de($e)];
        }
    }

    public static function desactivarTodos($userId)
    {
        try {
            DB::beginTransaction();
            $res = DB::table('grupos_horarios')
                ->where('user_id', $userId)
                ->where('activo', '!=', self::STATUS_DELETED)
                ->update(['activo' => self::STATUS_INACTIVE]);
            DB::commit();
            return $res;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function obtenerActivoPorPsicologo($psicologoId)
    {
        return DB::table('grupos_horarios')
            ->where('user_id', $psicologoId)
            ->where('activo', self::STATUS_ACTIVE)
            ->first();
    }

    public static function obtenerPorIdYUsuario($id, $userId)
    {
        return DB::table('grupos_horarios')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->where('activo', '!=', self::STATUS_DELETED)
            ->first();
    }

    public static function obtenerPorId($id)
    {
        return DB::table('grupos_horarios')->where('id', $id)->first();
    }

    public static function obtenerConHorarios($userId)
    {
        return DB::table('grupos_horarios')
            ->where('user_id', $userId)
            ->whereIn('activo', [self::STATUS_ACTIVE, self::STATUS_INACTIVE])
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('horarios')
                    ->whereColumn('horarios.grupo_horario_id', 'grupos_horarios.id')
                    ->whereIn('horarios.activo', [Horario::STATUS_ACTIVE, Horario::STATUS_INACTIVE]);
            })
            ->get()
            ->map(function ($grupo) {
                $grupo->horarios = DB::table('horarios')
                    ->where('grupo_horario_id', $grupo->id)
                    ->whereIn('activo', [Horario::STATUS_ACTIVE, Horario::STATUS_INACTIVE])
                    ->orderByRaw("CASE dia WHEN 'Lunes' THEN 1 WHEN 'Martes' THEN 2 WHEN 'Miércoles' THEN 3 WHEN 'Jueves' THEN 4 WHEN 'Viernes' THEN 5 ELSE 6 END")
                    ->orderBy('hora_inicio')
                    ->get();
                return $grupo;
            });
    }

    public static function existeNombre($userId, $nombre, $excludeId = null)
    {
        $query = DB::table('grupos_horarios')
            ->where('user_id', $userId)
            ->where('nombre', $nombre)
            ->whereIn('activo', [self::STATUS_ACTIVE, self::STATUS_INACTIVE]);

        if ($excludeId) {
            $query->where('id', '<>', $excludeId);
        }

        return $query->exists();
    }

    public static function crear($data)
    {
        try {
            DB::beginTransaction();
            $id = DB::table('grupos_horarios')->insertGetId([
                'user_id' => $data['user_id'],
                'nombre' => $data['nombre'],
                'activo' => $data['activo'] ?? self::STATUS_INACTIVE,
                'created_at' => Carbon::now(),
                'updated_at' => null,
            ]);
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
            $res = DB::table('grupos_horarios')
                ->where('id', $id)
                ->update(array_merge($data, ['updated_at' => Carbon::now()]));
            DB::commit();
            return $res;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function obtenerNoEliminados($userId)
    {
        return DB::table('grupos_horarios')
            ->where('user_id', $userId)
            ->where('activo', '!=', self::STATUS_DELETED)
            ->get();
    }

    public static function obtenerHorariosHoy($grupoId, $dia)
    {
        return DB::table('horarios')
            ->where('grupo_horario_id', $grupoId)
            ->where('dia', $dia)
            ->where('activo', self::STATUS_ACTIVE)
            ->orderBy('hora_inicio')
            ->get();
    }

    public static function crearDesdeHorarios($userId, $action, $nombre)
    {
        $grupoActivo = self::obtenerActivoPorPsicologo($userId);

        $horarios = Horario::obtenerPorFiltros($userId, $grupoActivo ? $grupoActivo->id : null);

        if ($horarios->count() < 2) {
            throw new \Exception('Debe haber al menos dos bloques de horario configurados para guardar el grupo de horarios.');
        }

        $buildSignature = function ($bloques) {
            return $bloques->map(function ($h) {
                return trim($h->dia . '|' . $h->hora_inicio . '|' . $h->hora_fin . '|' . ($h->descripcion ?? ''));
            })->sort()->values()->implode(';');
        };

        $newSignature = $buildSignature($horarios);

        if ($action === 'create' && !empty($nombre)) {
            if (self::existeNombre($userId, $nombre)) {
                throw new \Exception('Ya existe un grupo con ese nombre. Usa otro nombre.');
            }
        }

        $oldGroup = self::obtenerNoEliminados($userId)
            ->first(function ($grupo) use ($newSignature, $action, $grupoActivo, $buildSignature) {
                if ($action === 'update' && $grupoActivo && $grupo->id === $grupoActivo->id) {
                    return false;
                }

                $signature = $buildSignature(
                    Horario::obtenerPorGrupo($grupo->id)
                );

                return $signature === $newSignature;
            });

        if ($oldGroup) {
            throw new \Exception('Ya existe un grupo con los mismos bloques. Cambia los bloques o el nombre del nuevo grupo.');
        }

        try {
            DB::beginTransaction();

            if ($action === 'create') {
                self::desactivarTodos($userId);

                $grupoId = self::crear([
                    'user_id' => $userId,
                    'nombre' => $nombre,
                    'activo' => self::STATUS_ACTIVE,
                ]);
            } else {
                if (!$grupoActivo) {
                    throw new \Exception('No hay un grupo activo para actualizar.');
                }

                self::desactivarTodos($userId);

                $grupoId = $grupoActivo->id;
                self::actualizar($grupoId, ['activo' => self::STATUS_ACTIVE]);
            }

            Horario::asignarGrupo($horarios->pluck('id'), $grupoId);

            DB::commit();
            return $action === 'create' ? 'Grupo nuevo creado y asignado.' : 'Grupo activo actualizado correctamente.';
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
