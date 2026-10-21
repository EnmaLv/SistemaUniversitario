<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('categoria_producto')->insert([
            ['nombre_cap' => 'Embutidos', 'descripcion_cap' => null, 'tpr_id' => 1, 'status_id' => 1],
            ['nombre_cap' => 'Frutas', 'descripcion_cap' => null, 'tpr_id' => 1, 'status_id' => 1],
            ['nombre_cap' => 'Verduras', 'descripcion_cap' => null, 'tpr_id' => 1, 'status_id' => 1],
            ['nombre_cap' => 'Legumbres', 'descripcion_cap' => null, 'tpr_id' => 1, 'status_id' => 1],
            ['nombre_cap' => 'Carnes', 'descripcion_cap' => null, 'tpr_id' => 1, 'status_id' => 1],
            ['nombre_cap' => 'Lácteos', 'descripcion_cap' => null, 'tpr_id' => 1, 'status_id' => 1],
            ['nombre_cap' => 'Cereales', 'descripcion_cap' => null, 'tpr_id' => 1, 'status_id' => 1],
            ['nombre_cap' => 'Aceite', 'descripcion_cap' => null, 'tpr_id' => 1, 'status_id' => 1],
            ['nombre_cap' => 'Carbohidrato', 'descripcion_cap' => null, 'tpr_id' => 1, 'status_id' => 1],
            ['nombre_cap' => 'Grasas', 'descripcion_cap' => null, 'tpr_id' => 1, 'status_id' => 1],
            ['nombre_cap' => 'Bebidas', 'descripcion_cap' => null, 'tpr_id' => 1, 'status_id' => 1],
            ['nombre_cap' => 'Enlatados', 'descripcion_cap' => null, 'tpr_id' => 1, 'status_id' => 1],
            ['nombre_cap' => 'Condimentos', 'descripcion_cap' => null, 'tpr_id' => 1, 'status_id' => 1],
            
        ]);
    }
}
