<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TipoProducto;

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
            'CONTENEDOR PLÁSTICO',
        ];

        PresentacionAreaSeeder::sembrar([TipoProducto::MEDICAMENTO => $envases]);
        (new PresentacionAreaSeeder())->run();
    }
}
