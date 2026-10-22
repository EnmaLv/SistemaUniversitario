<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AvanceSesionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $avancesPredeterminados = [
            ['nombre' => 'Estancado / Sin cambios', 'descripcion' => 'No se observan mejoras clínicas evidentes, el paciente se mantiene en el mismo estado.', 'valor' => 1],
            ['nombre' => 'En progreso / Mejora leve', 'descripcion' => 'Se observan mejoras leves y progresivas en el estado del paciente.', 'valor' => 3],
            ['nombre' => 'Logrado / Mejora significativa', 'descripcion' => 'El paciente ha alcanzado el objetivo terapéutico con mejoras significativas.', 'valor' => 5],
        ];

        foreach ($avancesPredeterminados as $avance) {
            $existe = DB::table('avance_sesion')
                ->where('nombre_avs', $avance['nombre'])
                ->where('es_sistema_avs', true)
                ->exists();

            if (!$existe) {
                DB::table('avance_sesion')->insert([
                    'psicologo_use_id' => null,
                    'nombre_avs' => $avance['nombre'],
                    'descripcion_avs' => $avance['descripcion'],
                    'valor_avs' => $avance['valor'],
                    'activo_avs' => true,
                    'es_sistema_avs' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
