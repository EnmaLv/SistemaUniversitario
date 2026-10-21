<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EnvasePrimarioSeeder extends Seeder
{
    public function run(): void
    {
        $envases = [
            'BLISTER',
            'FRASCO',
            'FRASCO ÁMPULA',
            'AMPOLLA',
            'VIAL',
            'JERINGA PRELLENADA',
            'TUBO',
            'SOBRE',
            'GOTERO',
            'BOLSA',
            'BOLSA PARA INFUSIÓN',
            'CARTUCHO',
            'SPRAY',
            'INHALADOR',
            'SACHET',
            'FRASCO GOTERO',
            'FRASCO DOSIFICADOR',
            'FRASCO SPRAY',
            'TIRA REACTIVA',
            'CONTENEDOR PLÁSTICO'
        ];

        foreach ($envases as $envase) {
            DB::table('envase_primario')->insert([
                'nombre_enp' => $envase,
                'status_id' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}