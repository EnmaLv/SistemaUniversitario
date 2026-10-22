<?php

namespace Database\Seeders;

use App\Models\Unidad;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RecetaIngredienteSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['rct_id' => 1, 'producto' => 'CAR-CAR-006', 'cantidad_porcion_rci' => 40.00, 'unidad' => 'Unidad'],
            ['rct_id' => 1, 'producto' => 'CER-HAR-001', 'cantidad_porcion_rci' => 40.00, 'unidad' => 'Unidad'],
            ['rct_id' => 2, 'producto' => 'EMB-SAL-013', 'cantidad_porcion_rci' => 40.00, 'unidad' => 'Unidad'],
            ['rct_id' => 2, 'producto' => 'CER-HAR-001', 'cantidad_porcion_rci' => 40.00, 'unidad' => 'Unidad'],
            ['rct_id' => 3, 'producto' => 'EMB-MOR-002', 'cantidad_porcion_rci' => 40.00, 'unidad' => 'Unidad'],
            ['rct_id' => 3, 'producto' => 'CER-HAR-001', 'cantidad_porcion_rci' => 40.00, 'unidad' => 'Unidad'],
            ['rct_id' => 4, 'producto' => 'GRA-MAN-005-1', 'cantidad_porcion_rci' => 40.00, 'unidad' => 'Unidad'],
            ['rct_id' => 4, 'producto' => 'LAC-QUE-015', 'cantidad_porcion_rci' => 40.00, 'unidad' => 'Unidad'],
            ['rct_id' => 4, 'producto' => 'CER-HAR-001', 'cantidad_porcion_rci' => 40.00, 'unidad' => 'Unidad'],
            ['rct_id' => 5, 'producto' => 'CAR-POL-014', 'cantidad_porcion_rci' => 40.00, 'unidad' => 'Unidad'],
            ['rct_id' => 5, 'producto' => 'CER-HAR-001', 'cantidad_porcion_rci' => 40.00, 'unidad' => 'Unidad'],
        ];

        foreach ($data as $item) {
            $unidad = Unidad::findOrFail(ProductoSeeder::unidad($item['unidad']));

            DB::table('receta_ingrediente')->insert([
                'rct_id' => $item['rct_id'],
                'pdt_id' => ProductoSeeder::producto($item['producto']),
                'cantidad_porcion_rci' => $item['cantidad_porcion_rci'],
                'cantidad_convertida_rci' => $item['cantidad_porcion_rci'] * $unidad->factor_conversion_unm,
                'unm_id' => $unidad->id_unm,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
