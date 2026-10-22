<?php

namespace App\Models\salud;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AvanceSesion
{
    /**
     * Obtener todos los avances de un psicólogo.
     */
    public static function obtenerPorPsicologo($psicologoId)
    {
        return DB::table('avance_sesion')
            ->where(function ($query) use ($psicologoId) {
                $query->where('psicologo_use_id', $psicologoId)
                    ->orWhere('es_sistema_avs', true);
            })
            ->whereNull('avance_sesion.deleted_at')
            ->orderBy('nombre_avs', 'asc')
            ->get();
    }

    /**
     * Obtener avances paginados y con búsqueda.
     */
    public static function obtenerPaginadoPorPsicologo($psicologoId, $search = null, $perPage = 10)
    {
        $query = DB::table('avance_sesion')
            ->where(function ($q) use ($psicologoId) {
                $q->where('psicologo_use_id', $psicologoId)
                    ->orWhere('es_sistema_avs', true);
            })
            ->whereNull('avance_sesion.deleted_at');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre_avs', 'like', '%' . $search . '%')
                    ->when(ctype_digit((string) $search), fn ($s) => $s->orWhere('valor_avs', (int) $search));
            });
        }
        return $query->orderBy('es_sistema_avs', 'desc')
            ->orderBy('nombre_avs', 'asc')
            ->paginate($perPage);
    }

    /**
     * Avance visible para el psicólogo: propio o del sistema, no eliminado.
     */
    public static function obtenerVisible($id, $psicologoId)
    {
        return DB::table('avance_sesion')
            ->where('id_avs', $id)
            ->where(function ($q) use ($psicologoId) {
                $q->where('psicologo_use_id', $psicologoId)
                    ->orWhere('es_sistema_avs', true);
            })
            ->whereNull('deleted_at')
            ->first();
    }

    /**
     * Crear un nuevo avance.
     */
    public static function crear($psicologoId, $datos)
    {
        $existe = DB::table('avance_sesion')
            ->where('psicologo_use_id', $psicologoId)
            ->where('nombre_avs', $datos['nombre'])
            ->whereNull('deleted_at')
            ->exists();

        if ($existe) {
            throw new \Exception('Ya existe un avance con este nombre.');
        }

        $existeValor = DB::table('avance_sesion')
            ->where(function ($q) use ($psicologoId) {
                $q->where('psicologo_use_id', $psicologoId)
                    ->orWhere('es_sistema_avs', true);
            })
            ->where('valor_avs', $datos['valor'])
            ->whereNull('deleted_at')
            ->exists();

        if ($existeValor) {
            throw new \Exception('Ya existe un avance con este valor numérico.');
        }

        return DB::table('avance_sesion')->insertGetId([
            'psicologo_use_id' => $psicologoId,
            'nombre_avs' => $datos['nombre'],
            'valor_avs' => $datos['valor'],
            'descripcion_avs' => $datos['descripcion'] ?? null,
            'activo_avs' => $datos['estado'] ?? true,
            'created_id' => Auth::id(),
            'created_at' => now(),
            'updated_at' => now(),
        ], 'id_avs');
    }

    /**
     * Actualizar un avance existente.
     */
    public static function actualizar($id, $psicologoId, $datos)
    {
        $avance = DB::table('avance_sesion')->where('id_avs', $id)->whereNull('deleted_at')->first();
        if (!$avance) throw new \Exception('Avance no encontrado.');
        if ($avance->es_sistema_avs) {
            throw new \Exception('No se puede modificar un avance del sistema.');
        }

        if (self::enUsoUltimaNota($id, $psicologoId)) {
            throw new \Exception('No se puede editar este avance de sesión porque está registrado en la última nota de evolución de un paciente.');
        }

        $existe = DB::table('avance_sesion')
            ->where(function ($q) use ($psicologoId) {
                $q->where('psicologo_use_id', $psicologoId)
                    ->orWhere('es_sistema_avs', true);
            })
            ->where('nombre_avs', $datos['nombre'])
            ->where('id_avs', '!=', $id)
            ->whereNull('deleted_at')
            ->exists();

        if ($existe) {
            throw new \Exception('Ya existe otro avance con este nombre.');
        }

        $existeValor = DB::table('avance_sesion')
            ->where(function ($q) use ($psicologoId) {
                $q->where('psicologo_use_id', $psicologoId)
                    ->orWhere('es_sistema_avs', true);
            })
            ->where('valor_avs', $datos['valor'])
            ->where('id_avs', '!=', $id)
            ->whereNull('deleted_at')
            ->exists();

        if ($existeValor) {
            throw new \Exception('Ya existe otro avance con este valor numérico.');
        }

        return DB::table('avance_sesion')
            ->where('id_avs', $id)
            ->where('psicologo_use_id', $psicologoId)
            ->whereNull('deleted_at')
            ->update([
                'nombre_avs' => $datos['nombre'],
                'valor_avs' => $datos['valor'],
                'descripcion_avs' => $datos['descripcion'] ?? null,
                'activo_avs' => $datos['estado'] ?? true,
                'updated_id' => Auth::id(),
                'updated_at' => now(),
            ]);
    }

    /**
     * Eliminar (soft delete) un avance.
     */
    public static function eliminar($id, $psicologoId)
    {
        $avance = DB::table('avance_sesion')->where('id_avs', $id)->whereNull('deleted_at')->first();
        if (!$avance) throw new \Exception('Avance no encontrado.');
        if ($avance->es_sistema_avs) {
            throw new \Exception('No se puede eliminar un avance del sistema.');
        }

        if (self::enUsoUltimaNota($id, $psicologoId)) {
            throw new \Exception('No se puede eliminar este avance de sesión porque está registrado en la última nota de evolución de un paciente.');
        }

        return DB::table('avance_sesion')
            ->where('id_avs', $id)
            ->where('psicologo_use_id', $psicologoId)
            ->whereNull('deleted_at')
            ->update([
                'deleted_at' => now(),
                'updated_id' => Auth::id(),
                'updated_at' => now(),
            ]);
    }

    public static function enUsoUltimaNota($id, $psicologoId = null)
    {
        $query = DB::table('cita_psicologica')
            ->where('estado_cita_cps', 'realizada')
            ->where('status_id', 1)
            ->whereNull('cita_psicologica.deleted_at')
            ->where('notas_cps', 'like', '%"avance_estado":"' . $id . '"%');

        if ($psicologoId) {
            $query->where('psicologo_use_id', $psicologoId);
        }

        $userIds = $query->pluck('paciente_use_id')->unique();

        foreach ($userIds as $userId) {
            $latestQuery = DB::table('cita_psicologica')
                ->where('paciente_use_id', $userId)
                ->where('estado_cita_cps', 'realizada')
                ->where('status_id', 1)
                ->whereNull('cita_psicologica.deleted_at')
                ->orderBy('fecha_cps', 'desc')
                ->orderBy('hora_cps', 'desc');

            if ($psicologoId) {
                $latestQuery->where('psicologo_use_id', $psicologoId);
            }

            $latestCita = $latestQuery->first();

            if ($latestCita && $latestCita->notas_cps) {
                $notas = json_decode($latestCita->notas_cps, true);
                if (isset($notas['avance_estado']) && $notas['avance_estado'] == $id) {
                    return true;
                }
            }
        }
        return false;
    }

    public static function obtenerNombrePorId($id)
    {
        return DB::table('avance_sesion')
            ->where('id_avs', $id)
            ->value('nombre_avs');
    }
}
