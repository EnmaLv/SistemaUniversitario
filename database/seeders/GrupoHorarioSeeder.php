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
        $psicologos = \Illuminate\Support\Facades\DB::table('rol_usuario')
            ->join('rol', 'rol.id_rol', '=', 'rol_usuario.id_rol')
            ->where('rol.slug', 'psicologo')
            ->pluck('rol_usuario.id_usuario');

        foreach ($psicologos as $psicologoId) {
            \App\Models\salud\GrupoHorario::crear([
                'user_id' => $psicologoId,
                'nombre' => 'Horario Estándar',
                'activo' => \App\Models\salud\GrupoHorario::STATUS_ACTIVE,
            ]);
        }
    }
}
