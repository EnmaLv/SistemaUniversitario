<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Unidad; // importar la unidad

class RecetaIngredienteSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['recetas_id' => 1, 'producto_id' => 7,  'cantidad_porcion' => 40.00, 'unidad_id' => 1],
            ['recetas_id' => 1, 'producto_id' => 1,  'cantidad_porcion' => 40.00, 'unidad_id' => 1],
            ['recetas_id' => 2, 'producto_id' => 14, 'cantidad_porcion' => 40.00, 'unidad_id' => 1],
            ['recetas_id' => 2, 'producto_id' => 1,  'cantidad_porcion' => 40.00, 'unidad_id' => 1],
            ['recetas_id' => 3, 'producto_id' => 2,  'cantidad_porcion' => 40.00, 'unidad_id' => 1],
            ['recetas_id' => 3, 'producto_id' => 1,  'cantidad_porcion' => 40.00, 'unidad_id' => 1],
            ['recetas_id' => 4, 'producto_id' => 6,  'cantidad_porcion' => 40.00, 'unidad_id' => 1],
            ['recetas_id' => 4, 'producto_id' => 16,  'cantidad_porcion' => 40.00, 'unidad_id' => 1],
            ['recetas_id' => 4, 'producto_id' => 1,  'cantidad_porcion' => 40.00, 'unidad_id' => 1],
            ['recetas_id' => 5, 'producto_id' => 15, 'cantidad_porcion' => 40.00, 'unidad_id' => 1],
            ['recetas_id' => 5, 'producto_id' => 1,  'cantidad_porcion' => 40.00, 'unidad_id' => 1],
        ];

        foreach ($data as $item) {
            $unidad = Unidad::find($item['unidad_id']);

            DB::table('receta_ingredientes')->insert([
                'recetas_id' => $item['recetas_id'],
                'pdt_id' => $item['producto_id'],
                'cantidad_porcion' => $item['cantidad_porcion'],
                'cantidad_gramos' => $item['cantidad_porcion'] * $unidad->factor_conversion_unm,
                'unm_id' => $item['unidad_id'],
            ]);
        }
    }
}
