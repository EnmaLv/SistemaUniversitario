<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class EnfermedadesSeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = database_path('seeders/data/cie10.json');

        if (!File::exists($jsonPath)) {
            $this->command->error("No se encontró el archivo cie10.json en: {$jsonPath}");
            return;
        }

        $json = File::get($jsonPath);
        $enfermedades = json_decode($json, true);

        if (empty($enfermedades)) {
            $this->command->error("El archivo JSON está vacío o mal formateado.");
            return;
        }

        Schema::disableForeignKeyConstraints();
        DB::table('enfermedad')->truncate();

        // Dividir los 12k registros en lotes de 1.000
        $chunks = array_chunk($enfermedades, 1000);

        foreach ($chunks as $chunk) {
            $insertData = [];

            foreach ($chunk as $item) {
                $codigo = trim($item['code'] ?? '');
                
                // Determinar si pertenece a salud mental (Capítulo V de CIE-10 empieza por F)
                $esMental = str_starts_with($codigo, 'F') || str_contains($codigo, 'F00') || str_contains($codigo, 'F99');

                $insertData[] = [
                    'codigo_efm'         => $codigo ?: null,
                    'nombre_efm'         => trim($item['description'] ?? ''),
                    'categoria_efm'      => $esMental ? 'mental' : 'fisica',
                    'nivel_gravedad_efm' => $item['level'] ?? 0,
                    'status_id'          => 1,
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ];
            }

            DB::table('enfermedad')->insert($insertData);
        }

        Schema::enableForeignKeyConstraints();
        $this->command->info('¡Tabla enfermedad poblada exitosamente con CIE-10!');
    }
}