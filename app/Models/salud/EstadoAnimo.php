<?php

namespace App\Models\salud;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EstadoAnimo
{
    public static function buscarYPaginar($busqueda = null, $porPagina = 10)
    {
        $query = DB::table('estado_animo')->whereNull('estado_animo.deleted_at');

        if (!empty($busqueda)) {
            $query->where(function ($q) use ($busqueda) {
                $q->where('nombre_esa', 'like', '%' . $busqueda . '%')
                  ->when(ctype_digit((string) $busqueda), fn ($s) => $s->orWhere('valor_esa', (int) $busqueda));
            });
        }

        return $query->orderBy('valor_esa', 'asc')->paginate($porPagina);
    }

    public static function valoresDisponibles($excluirId = null)
    {
        $query = DB::table('estado_animo')
            ->whereNull('estado_animo.deleted_at');
            
        if ($excluirId) {
            $query->where('id_esa', '!=', $excluirId);
        }
        $ocupados = $query->pluck('valor_esa')->toArray();
        $disponibles = [];
        for ($i = 1; $i <= 10; $i++) {
            if (!in_array($i, $ocupados)) {
                $disponibles[] = $i;
            }
        }
        return $disponibles;
    }

    public static function nombreExiste($nombre, $excluirId = null)
    {
        return DB::table('estado_animo')
            ->whereNull('estado_animo.deleted_at')
            ->when($excluirId, fn ($q) => $q->where('id_esa', '!=', $excluirId))
            ->whereRaw('LOWER(nombre_esa) = ?', [mb_strtolower($nombre)])
            ->exists();
    }

    public static function crear($datos)
    {
        return DB::table('estado_animo')->insert([
            'nombre_esa' => $datos['nombre'],
            'valor_esa' => $datos['valor'],
            'created_id' => Auth::id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public static function obtenerPorId($id)
    {
        return DB::table('estado_animo')
            ->whereNull('estado_animo.deleted_at')
            ->where('id_esa', $id)
            ->first();
    }

    public static function enUsoUltimaNota($id)
    {
        $userIds = DB::table('cita_psicologica')
            ->where('esa_id', $id)
            ->where('estado_cita_cps', 'realizada')
            ->where('status_id', 1)
            ->whereNull('cita_psicologica.deleted_at')
            ->pluck('paciente_use_id')
            ->unique();
            
        foreach ($userIds as $userId) {
            $latestCita = DB::table('cita_psicologica')
                ->where('paciente_use_id', $userId)
                ->where('estado_cita_cps', 'realizada')
                ->where('status_id', 1)
                ->whereNull('cita_psicologica.deleted_at')
                ->orderBy('fecha_cps', 'desc')
                ->orderBy('hora_cps', 'desc')
                ->first();
                
            if ($latestCita && $latestCita->esa_id == $id) {
                return true;
            }
        }
        
        return false;
    }

    public static function eliminar($id)
    {
        if (self::enUsoUltimaNota($id)) {
            throw new \Exception('No se puede eliminar este estado de ánimo porque está registrado en la última nota de evolución de un paciente.');
        }

        return DB::table('estado_animo')
            ->where('id_esa', $id)
            ->whereNull('deleted_at')
            ->update([
                'deleted_at' => Carbon::now(),
                'updated_id' => Auth::id(),
                'updated_at' => Carbon::now(),
            ]);
    }

    public static function actualizar($id, $datos)
    {
        if (self::enUsoUltimaNota($id)) {
            throw new \Exception('No se puede editar este estado de ánimo porque está registrado en la última nota de evolución de un paciente.');
        }

        return DB::table('estado_animo')
            ->where('id_esa', $id)
            ->whereNull('deleted_at')
            ->update([
                'nombre_esa' => $datos['nombre'],
                'valor_esa' => $datos['valor'],
                'updated_id' => Auth::id(),
                'updated_at' => Carbon::now(),
            ]);
    }

    public static function obtenerActivos()
    {
        return DB::table('estado_animo')->whereNull('estado_animo.deleted_at')->get();
    }

    public static function obtenerNombrePorId($id)
    {
        return DB::table('estado_animo')
            ->where('id_esa', $id)
            ->value('nombre_esa');
    }
}
