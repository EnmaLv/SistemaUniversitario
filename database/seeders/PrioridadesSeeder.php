<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PrioridadesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $prioridades = [
            ['nombre_pat' => 'baja', 'nivel_gravedad_pat' => 1, 'psicologo_use_id' => null, 'status_id' => 1],
            ['nombre_pat' => 'media', 'nivel_gravedad_pat' => 5, 'psicologo_use_id' => null, 'status_id' => 1],
            ['nombre_pat' => 'alta', 'nivel_gravedad_pat' => 7, 'psicologo_use_id' => null, 'status_id' => 1],
            ['nombre_pat' => 'crítica', 'nivel_gravedad_pat' => 10, 'psicologo_use_id' => null, 'status_id' => 1],
        ];

        foreach ($prioridades as $prioridad) {
            $exists = DB::table('prioridad_atencion')
                ->where('nombre_pat', $prioridad['nombre_pat'])
                ->whereNull('psicologo_use_id')
                ->first();

            if ($exists) {
                DB::table('prioridad_atencion')
                    ->where('id_pat', $exists->id_pat)
                    ->update([
                        'nivel_gravedad_pat' => $prioridad['nivel_gravedad_pat'],
                        'status_id' => 1,
                        'deleted_at' => null,
                        'updated_at' => now(),
                    ]);
            } else {
                DB::table('prioridad_atencion')->insert(array_merge($prioridad, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }
    }
}
