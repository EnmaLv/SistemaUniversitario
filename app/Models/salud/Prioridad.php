<?php

namespace App\Models\salud;

use Illuminate\Support\Facades\DB;

class Prioridad
{
    public const STATUS_ACTIVO = 1;
    public const STATUS_INACTIVO = 2;

    public static function obtenerParaPsicologo($psicologoId = null)
    {
        return DB::table('prioridad_atencion')
            ->where('status_id', self::STATUS_ACTIVO)
            ->whereNull('prioridad_atencion.deleted_at')
            ->where(function ($query) use ($psicologoId) {
                $query->whereNull('psicologo_use_id');
                if ($psicologoId) {
                    $query->orWhere('psicologo_use_id', $psicologoId);
                }
            })
            ->orderBy('nivel_gravedad_pat', 'desc')
            ->get();
    }

    public static function buscarYPaginar($busqueda = null, $psicologoId = null, $porPagina = 10)
    {
        $query = DB::table('prioridad_atencion')
            ->select('prioridad_atencion.*')
            ->selectSub(function ($sub) use ($psicologoId) {
                $sub->from('cita_psicologica')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('cita_psicologica.prioridad_texto_cps', 'prioridad_atencion.nombre_pat')
                    ->whereNull('cita_psicologica.deleted_at')
                    ->when($psicologoId, fn ($q) => $q->where('cita_psicologica.psicologo_use_id', $psicologoId));
            }, 'uso_count')
            ->where('prioridad_atencion.status_id', self::STATUS_ACTIVO)
            ->whereNull('prioridad_atencion.deleted_at')
            ->where(function ($query) use ($psicologoId) {
                $query->whereNull('prioridad_atencion.psicologo_use_id');
                if ($psicologoId) {
                    $query->orWhere('prioridad_atencion.psicologo_use_id', $psicologoId);
                }
            });

        if (!empty($busqueda)) {
            $query->where(function ($q) use ($busqueda) {
                $q->where('prioridad_atencion.nombre_pat', 'like', '%' . $busqueda . '%')
                  ->when(ctype_digit((string) $busqueda), fn ($s) => $s->orWhere('prioridad_atencion.nivel_gravedad_pat', (int) $busqueda));
            });
        }

        return $query->orderBy('prioridad_atencion.nivel_gravedad_pat', 'desc')->paginate($porPagina);
    }
}
