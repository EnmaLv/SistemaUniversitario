<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PnfSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pnfs = [
            ['codigo_prg' => 'PNF-INFORMATICA', 'nombre_prg' => 'INFORMATICA', 'status_id' => 1],
            ['codigo_prg' => 'PNF-ELECTRICIDAD', 'nombre_prg' => 'ELECTRICIDAD', 'status_id' => 1],
            ['codigo_prg' => 'PNF-AGROALIMENTA', 'nombre_prg' => 'AGROALIMENTACION', 'status_id' => 1],
            ['codigo_prg' => 'PNF-ADMINISTRACI', 'nombre_prg' => 'ADMINISTRACION', 'status_id' => 1],
            ['codigo_prg' => 'PNF-VETERINARIA', 'nombre_prg' => 'VETERINARIA', 'status_id' => 1],
            ['codigo_prg' => 'PNF-MECANICA', 'nombre_prg' => 'MECANICA', 'status_id' => 1],
            ['codigo_prg' => 'PNF-MANTENIMIENT', 'nombre_prg' => 'MANTENIMIENTO', 'status_id' => 1],
            ['codigo_prg' => 'PNF-DISTRIBUCION', 'nombre_prg' => 'DISTRIBUCIÓN LOGÍSTICA', 'status_id' => 1],
            ['codigo_prg' => 'PNF-PROCYDISTDEA', 'nombre_prg' => 'PROC. Y DIST. DE ALIMENTOS', 'status_id' => 1],
            ['codigo_prg' => 'PNF-SEGURIDADALI', 'nombre_prg' => 'SEGURIDAD ALIMENTARIA', 'status_id' => 1],
        ];
        $this->command->info('Pnfs insertados Exitosamente.');
        DB::table('programa')->insert($pnfs);
    }
}
