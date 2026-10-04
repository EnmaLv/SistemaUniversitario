<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlantillaGlobalSeeder extends Seeder
{
    public function run(): void
    {
        $psicologos = DB::table('usuario')
            ->join('rol_usuario', 'usuario.id_usuario', '=', 'rol_usuario.id_usuario')
            ->join('rol', 'rol_usuario.id_rol', '=', 'rol.id_rol')
            ->whereIn(DB::raw('LOWER(rol.nombre)'), ['psicologo'])
            ->select('usuario.id_usuario')
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
            $userId = $psicologo->id_usuario;

            $plantilla = DB::table('historia_plantillas_globales')
                ->where('psicologo_id', $userId)
                ->first();

            if (!$plantilla) {
                DB::table('historia_plantillas_globales')->insert([
                    'psicologo_id' => $userId,
                    'titulo' => 'Expediente General de Pacientes',
                    'descripcion' => 'Especificaciones del record de salud del paciente',
                    'secciones' => json_encode($seccionesPredefinidas),
                    'status' => 2,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
