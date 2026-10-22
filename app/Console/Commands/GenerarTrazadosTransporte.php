<?php

namespace App\Console\Commands;

use App\Models\BusViaje;
use App\Services\Transporte\TrazadoRutaService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerarTrazadosTransporte extends Command
{
    protected $signature = 'transporte:generar-trazados {--ruta= : Solo esta ruta}';

    protected $description = 'Genera el trazado de referencia (versión 1) de cada ruta sin trazado a partir de su primer viaje finalizado con puntos GPS';

    public function handle(TrazadoRutaService $trazado): int
    {
        $rutas = DB::table('ruta_transporte')
            ->whereNull('deleted_at')
            ->when($this->option('ruta') !== null, fn ($q) => $q->where('id_rtr', (int) $this->option('ruta')))
            ->orderBy('id_rtr')
            ->pluck('id_rtr');

        $generadas = 0;

        foreach ($rutas as $rutaId) {
            if ($trazado->versionVigente((int) $rutaId) !== null) {
                continue;
            }

            $viajes = BusViaje::todasLasSedes()
                ->where('rtr_id', $rutaId)
                ->where('estado_viaje_vjt', 'finalizado')
                ->whereHas('gpsLogs', null, '>=', 2)
                ->orderBy('fecha_inicio_vjt')
                ->orderBy('id_vjt')
                ->get();

            foreach ($viajes as $viaje) {
                $version = $trazado->generarReferenciaSiFalta($viaje);

                if ($version !== null) {
                    $generadas++;
                    $this->line("Ruta {$rutaId}: referencia generada desde el viaje {$viaje->id_vjt}.");
                    break;
                }
            }
        }

        $this->info("Trazados generados: {$generadas}.");

        return self::SUCCESS;
    }
}
