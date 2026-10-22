<?php

namespace App\Console\Commands;

use App\Services\Deporte\PrestamoService;
use Illuminate\Console\Command;

class MarcarPrestamosVencidos extends Command
{
    protected $signature = 'deportes:marcar-vencidos';

    protected $description = 'Marca como vencidos los préstamos deportivos activos cuya devolución esperada ya pasó y aplica la sanción correspondiente';

    public function handle(PrestamoService $servicio): int
    {
        $marcados = $servicio->marcarVencidos();

        $this->info("Préstamos marcados como vencidos: {$marcados}.");

        return self::SUCCESS;
    }
}
