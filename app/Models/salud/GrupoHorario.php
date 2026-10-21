<?php

namespace App\Models\salud;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GrupoHorario
{
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 2;

    public static function obtenerUsuario($userId)
    {
        return DB::table('user')->where('id_use', $userId)->first();
    }

    public static function obtenerHorarios($grupoId)
    {
        return DB::table('horario_profesional')
            ->where('gha_id', $grupoId)
            ->whereNull('horario_profesional.deleted_at')
            ->get();
    }

    public static function eliminar($id)
    {
        try {
            DB::beginTransaction();
            $now = Carbon::now();
            
            $updated = DB::table('grupo_horario_atencion')
                ->where('id_gha', $id)
                ->whereNull('deleted_at')
                ->update([
                    'deleted_at' => $now,
                    'updated_id' => Auth::id(),
                    'updated_at' => $now,
                ]);

            DB::table('horario_profesional')
                ->where('gha_id', $id)
                ->whereNull('deleted_at')
                ->update([
                    'deleted_at' => $now,
                    'updated_id' => Auth::id(),
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

            DB::table('grupo_horario_atencion')
                ->where('psicologo_use_id', $userId)
                ->whereNull('deleted_at')
                ->update(['status_id' => self::STATUS_INACTIVE, 'updated_id' => $userId, 'updated_at' => Carbon::now()]);

            DB::table('horario_profesional')
                ->where('psicologo_use_id', $userId)
                ->whereNull('deleted_at')
                ->update(['status_id' => Horario::STATUS_INACTIVE, 'updated_id' => $userId, 'updated_at' => Carbon::now()]);

            $updated = DB::table('grupo_horario_atencion')
                ->where('id_gha', $id)
                ->where('psicologo_use_id', $userId)
                ->whereNull('deleted_at')
                ->update([
                    'status_id' => self::STATUS_ACTIVE,
                    'updated_id' => $userId,
                    'updated_at' => Carbon::now(),
                ]);

            DB::table('horario_profesional')->where('gha_id', $id)
                ->where('psicologo_use_id', $userId)
                ->whereNull('deleted_at')
                ->update([
                    'status_id' => Horario::STATUS_ACTIVE,
                    'updated_id' => $userId,
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

            $updated = DB::table('grupo_horario_atencion')
                ->where('id_gha', $id)
                ->whereNull('deleted_at')
                ->update([
                    'status_id' => self::STATUS_INACTIVE,
                    'updated_id' => Auth::id(),
                    'updated_at' => Carbon::now(),
                ]);

            DB::table('horario_profesional')->where('gha_id', $id)
                ->whereNull('deleted_at')
                ->update([
                    'status_id' => Horario::STATUS_INACTIVE,
                    'updated_id' => Auth::id(),
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
            $res = DB::table('grupo_horario_atencion')
                ->where('psicologo_use_id', $userId)
                ->whereNull('deleted_at')
                ->update([
                    'status_id' => self::STATUS_INACTIVE,
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

    public static function obtenerActivoPorPsicologo($psicologoId)
    {
        return DB::table('grupo_horario_atencion')
            ->where('psicologo_use_id', $psicologoId)
            ->where('status_id', self::STATUS_ACTIVE)
            ->whereNull('deleted_at')
            ->first();
    }

    public static function obtenerPorIdYUsuario($id, $userId)
    {
        return DB::table('grupo_horario_atencion')
            ->where('id_gha', $id)
            ->where('psicologo_use_id', $userId)
            ->whereNull('deleted_at')
            ->first();
    }

    public static function obtenerPorId($id)
    {
        return DB::table('grupo_horario_atencion')->where('id_gha', $id)->first();
    }

    public static function obtenerConHorarios($userId)
    {
        return DB::table('grupo_horario_atencion')
            ->where('psicologo_use_id', $userId)
            ->whereNull('grupo_horario_atencion.deleted_at')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('horario_profesional')
                    ->whereColumn('horario_profesional.gha_id', 'grupo_horario_atencion.id_gha')
                    ->whereNull('horario_profesional.deleted_at');
            })
            ->get()
            ->map(function ($grupo) {
                $grupo->horarios = DB::table('horario_profesional')
                    ->where('gha_id', $grupo->id_gha)
                    ->whereNull('deleted_at')
                    ->orderByRaw(Horario::ORDEN_DIAS)
                    ->orderBy('hora_inicio_hpr')
                    ->get();
                return $grupo;
            });
    }

    public static function existeNombre($userId, $nombre, $excludeId = null)
    {
        $query = DB::table('grupo_horario_atencion')
            ->where('psicologo_use_id', $userId)
            ->where('nombre_gha', $nombre)
            ->whereNull('deleted_at');

        if ($excludeId) {
            $query->where('id_gha', '<>', $excludeId);
        }

        return $query->exists();
    }

    public static function crear($data)
    {
        try {
            DB::beginTransaction();
            $id = DB::table('grupo_horario_atencion')->insertGetId([
                'psicologo_use_id' => $data['psicologo_use_id'],
                'nombre_gha' => $data['nombre_gha'],
                'status_id' => $data['status_id'] ?? self::STATUS_INACTIVE,
                'created_id' => Auth::id(),
                'created_at' => Carbon::now(),
                'updated_at' => null,
            ], 'id_gha');
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
            $res = DB::table('grupo_horario_atencion')
                ->where('id_gha', $id)
                ->whereNull('deleted_at')
                ->update(array_merge($data, ['updated_id' => Auth::id(), 'updated_at' => Carbon::now()]));
            DB::commit();
            return $res;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function obtenerNoEliminados($userId)
    {
        return DB::table('grupo_horario_atencion')
            ->where('psicologo_use_id', $userId)
            ->whereNull('deleted_at')
            ->get();
    }

    public static function obtenerHorariosHoy($grupoId, $dia)
    {
        return DB::table('horario_profesional')
            ->where('gha_id', $grupoId)
            ->where('dia_hpr', $dia)
            ->where('status_id', Horario::STATUS_ACTIVE)
            ->whereNull('deleted_at')
            ->orderBy('hora_inicio_hpr')
            ->get();
    }

    public static function crearDesdeHorarios($userId, $action, $nombre)
    {
        $grupoActivo = self::obtenerActivoPorPsicologo($userId);

        $horarios = Horario::obtenerPorFiltros($userId, $grupoActivo ? $grupoActivo->id_gha : null);

        if ($horarios->count() < 2) {
            throw new \Exception('Debe haber al menos dos bloques de horario configurados para guardar el grupo de horarios.');
        }

        $buildSignature = function ($bloques) {
            return $bloques->map(function ($h) {
                return trim($h->dia_hpr . '|' . $h->hora_inicio_hpr . '|' . $h->hora_fin_hpr . '|' . ($h->descripcion_hpr ?? ''));
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
                if ($action === 'update' && $grupoActivo && (int) $grupo->id_gha === (int) $grupoActivo->id_gha) {
                    return false;
                }

                $signature = $buildSignature(
                    Horario::obtenerPorGrupo($grupo->id_gha)
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
                    'psicologo_use_id' => $userId,
                    'nombre_gha' => $nombre,
                    'status_id' => self::STATUS_ACTIVE,
                ]);
            } else {
                if (!$grupoActivo) {
                    throw new \Exception('No hay un grupo activo para actualizar.');
                }

                self::desactivarTodos($userId);

                $grupoId = $grupoActivo->id_gha;
                self::actualizar($grupoId, ['status_id' => self::STATUS_ACTIVE]);
            }

            Horario::asignarGrupo($horarios->pluck('id_hpr'), $grupoId);

            DB::commit();
            return $action === 'create' ? 'Grupo nuevo creado y asignado.' : 'Grupo activo actualizado correctamente.';
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
