<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlantillaGlobalSeeder extends Seeder
{
    public function run(): void
    {
        $psicologos = DB::table('user')
            ->join('usuario_rol', 'user.id_use', '=', 'usuario_rol.use_id')
            ->join('rol', 'usuario_rol.rol_id', '=', 'rol.id_rol')
            ->where('rol.slug_rol', 'psicologo')
            ->select('user.id_use')
            ->distinct()
            ->get();

        $seccionesPredefinidas = [
            [
                'titulo' => 'Antecedentes Personales',
                'descripcion_general' => 'En el ámbito de salud general',
                'segmentos' => ['Salud Mental', 'Salud General']
            ],
            [
                'titulo' => 'Antecedentes Familiares',
                'descripcion_general' => 'Recor de salud desde el lado familiar Paterno',
                'segmentos' => ['Salud Mental', 'Salud General']
            ],
            [
                'titulo' => 'Antecedentes',
                'descripcion_general' => 'Recor de salud desde el lado familiar Materno',
                'segmentos' => ['Salud Mental', 'Salud General']
            ],
            [
                'titulo' => 'Diagnostico General',
                'descripcion_general' => 'Este abarcará todo momento con el paciente',
                'segmentos' => ['Observaciones y Diagnosticos', 'Plan de Acción para la recuperación']
            ]
        ];

        foreach ($psicologos as $psicologo) {
            $userId = $psicologo->id_use;

            $plantilla = DB::table('plantilla_historia_clinica')
                ->where('psicologo_use_id', $userId)
                ->first();

            if (!$plantilla) {
                DB::table('plantilla_historia_clinica')->insert([
                    'psicologo_use_id' => $userId,
                    'titulo_phc' => 'Expediente General de Pacientes',
                    'descripcion_phc' => 'Especificaciones del record de salud del paciente',
                    'secciones_phc' => json_encode($seccionesPredefinidas),
                    'estado_phc' => 2,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
