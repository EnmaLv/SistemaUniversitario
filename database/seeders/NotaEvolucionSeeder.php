<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NotaEvolucionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $campos = [
            'Motivo de Consulta',
            'Observaciones Clínicas',
            'Intervenciones / Resumen de Sesión',
            'Detalle del Avance',
            'Plan de Tratamiento',
            'Diagnósticos Oficiales',
            'Estado de Ánimo del Paciente',
            'Estado de Evolución',
            'Próxima Cita Recomendada'
        ];

        foreach ($campos as $campo) {
            if (!\Illuminate\Support\Facades\DB::table('campo_nota_evolucion')->where('titulo_cne', $campo)->whereNull('psicologo_use_id')->exists()) {
                \Illuminate\Support\Facades\DB::table('campo_nota_evolucion')->insert([
                    'psicologo_use_id' => null,
                    'titulo_cne' => $campo,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
