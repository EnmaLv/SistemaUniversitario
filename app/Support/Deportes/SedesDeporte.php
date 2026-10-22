<?php

namespace App\Support\Deportes;

use App\Support\Sedes\SedeActiva;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SedesDeporte
{
    public static function opciones(): Collection
    {
        $consulta = DB::table('sede')->where('status_id', 1)->whereNull('deleted_at')->orderBy('nombre_sed');

        if (! SedeActiva::puedeElegir()) {
            $consulta->where('id_sed', SedeActiva::idParaCrear() ?? 0);
        }

        return $consulta->get(['id_sed', 'nombre_sed']);
    }

    public static function resolver($solicitada): ?int
    {
        if (SedeActiva::puedeElegir() && $solicitada && DB::table('sede')->where('id_sed', (int) $solicitada)->whereNull('deleted_at')->exists()) {
            return (int) $solicitada;
        }

        return SedeActiva::idParaCrear();
    }
}
