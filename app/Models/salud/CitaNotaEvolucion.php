<?php

namespace App\Models\salud;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CitaNotaEvolucion
{
    public static function obtenerPorCita($citaId)
    {
        return DB::table('cita_nota_evolucion')
            ->join('campo_nota_evolucion', 'cita_nota_evolucion.cne_id', '=', 'campo_nota_evolucion.id_cne')
            ->where('cita_nota_evolucion.cps_id', $citaId)
            ->whereNull('cita_nota_evolucion.deleted_at')
            ->whereNull('campo_nota_evolucion.deleted_at')
            ->select('cita_nota_evolucion.*', 'campo_nota_evolucion.titulo_cne')
            ->orderBy('campo_nota_evolucion.id_cne', 'asc')
            ->get();
    }

    public static function guardarCampo($citaId, $campoId, $contenido)
    {
        return DB::table('cita_nota_evolucion')->updateOrInsert(
            ['cps_id' => $citaId, 'cne_id' => $campoId],
            function (bool $existe) use ($contenido) {
                $datos = [
                    'contenido_cno' => $contenido,
                    'updated_at' => now(),
                    'updated_id' => Auth::id(),
                ];

                if (!$existe) {
                    $datos['created_at'] = now();
                    $datos['created_id'] = Auth::id();
                }

                return $datos;
            }
        );
    }
}
