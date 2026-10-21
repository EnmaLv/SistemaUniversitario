<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GrupoHorarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $psicologos = \Illuminate\Support\Facades\DB::table('usuario_rol')
            ->join('rol', 'rol.id_rol', '=', 'usuario_rol.rol_id')
            ->where('rol.slug_rol', 'psicologo')
            ->pluck('usuario_rol.use_id');

        foreach ($psicologos as $psicologoId) {
            \App\Models\salud\GrupoHorario::crear([
                'psicologo_use_id' => $psicologoId,
                'nombre_gha' => 'Horario Estándar',
                'status_id' => \App\Models\salud\GrupoHorario::STATUS_ACTIVE,
            ]);
        }
    }
}
