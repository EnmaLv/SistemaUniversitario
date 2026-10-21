<?php

namespace App\Console\Commands;

use App\Support\Permisos\SincronizadorPermisos;
use Illuminate\Console\Command;

class SincronizarPermisos extends Command
{
    protected $signature = 'permisos:sincronizar';

    protected $description = 'Crea o actualiza las vistas y acciones a partir de config/menu_permissions.php, config/menu_routes.php y las rutas registradas';

    public function handle(): int
    {
        $resultado = SincronizadorPermisos::sincronizar();

        $this->info("Vistas nuevas: {$resultado['vistas_nuevas']}");
        $this->info("Permisos nuevos: {$resultado['permisos_nuevos']}");

        return self::SUCCESS;
    }
}
