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
            ['rct_id' => 1, 'producto_id' => 7,  'cantidad_porcion_rci' => 40.00, 'unidad_id' => 1],
            ['rct_id' => 1, 'producto_id' => 1,  'cantidad_porcion_rci' => 40.00, 'unidad_id' => 1],
            ['rct_id' => 2, 'producto_id' => 14, 'cantidad_porcion_rci' => 40.00, 'unidad_id' => 1],
            ['rct_id' => 2, 'producto_id' => 1,  'cantidad_porcion_rci' => 40.00, 'unidad_id' => 1],
            ['rct_id' => 3, 'producto_id' => 2,  'cantidad_porcion_rci' => 40.00, 'unidad_id' => 1],
            ['rct_id' => 3, 'producto_id' => 1,  'cantidad_porcion_rci' => 40.00, 'unidad_id' => 1],
            ['rct_id' => 4, 'producto_id' => 6,  'cantidad_porcion_rci' => 40.00, 'unidad_id' => 1],
            ['rct_id' => 4, 'producto_id' => 16,  'cantidad_porcion_rci' => 40.00, 'unidad_id' => 1],
            ['rct_id' => 4, 'producto_id' => 1,  'cantidad_porcion_rci' => 40.00, 'unidad_id' => 1],
            ['rct_id' => 5, 'producto_id' => 15, 'cantidad_porcion_rci' => 40.00, 'unidad_id' => 1],
            ['rct_id' => 5, 'producto_id' => 1,  'cantidad_porcion_rci' => 40.00, 'unidad_id' => 1],
        ];

        foreach ($data as $item) {
            $unidad = Unidad::find($item['unidad_id']);

            DB::table('receta_ingrediente')->insert([
                'rct_id' => $item['rct_id'],
                'pdt_id' => $item['producto_id'],
                'cantidad_porcion_rci' => $item['cantidad_porcion_rci'],
                'cantidad_convertida_rci' => $item['cantidad_porcion_rci'] * $unidad->factor_conversion_unm,
                'unm_id' => $item['unidad_id'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
