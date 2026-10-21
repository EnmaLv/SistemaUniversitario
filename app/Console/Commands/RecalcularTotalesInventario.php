<?php

namespace App\Console\Commands;

use App\Support\Auditoria\Bitacora;
use App\Support\Inventario\ServicioInventario;
use Illuminate\Console\Command;

class RecalcularTotalesInventario extends Command
{
    protected $signature = 'inventario:recalcular-totales {--producto= : Solo este producto} {--sede= : Solo esta sede}';

    protected $description = 'Reconstruye inventario_total (consolidado por producto y sede) a partir de inventario_sede_lote';

    public function handle(ServicioInventario $servicio): int
    {
        $producto = $this->option('producto') !== null ? (int) $this->option('producto') : null;
        $sede = $this->option('sede') !== null ? (int) $this->option('sede') : null;

        $filas = $servicio->recalcular($producto, $sede);

        Bitacora::registrar(
            accion: 'inventario_totales_recalculados',
            modulo: 'administracion',
            tablaAfectada: 'inventario_totals',
            datosModificados: ['producto' => $producto, 'sede' => $sede, 'filas' => $filas],
            observacion: 'consola: inventario:recalcular-totales',
        );

        $this->info("Totales recalculados: {$filas} combinaciones de producto y sede.");

        return self::SUCCESS;
    }
}
