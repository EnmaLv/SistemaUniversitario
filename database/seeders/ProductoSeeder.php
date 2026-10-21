<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('producto')->insert([
            ['cap_id' => 7, 'codigo_pdt' => 'CER-HAR-001', 'nombre_pdt' => 'Harina', 'descripcion_pdt' => null, 'imagen_pdt' => null, 'stock_minimo_pdt' => 5, 'stock_maximo_pdt' => 100, 'peso_contenido_pdt' => 1000, 'unm_id' => 2, 'enp_id' => null, 'status_id' => 1],
            ['cap_id' => 1, 'codigo_pdt' => 'EMB-MOR-002', 'nombre_pdt' => 'Mortadela', 'descripcion_pdt' => null, 'imagen_pdt' => null, 'stock_minimo_pdt' => 5, 'stock_maximo_pdt' => 100, 'peso_contenido_pdt' => 1000, 'unm_id' => 2, 'enp_id' => null, 'status_id' => 1],
            ['cap_id' => 9, 'codigo_pdt' => 'CAR-AZU-003', 'nombre_pdt' => 'Azucar', 'descripcion_pdt' => null, 'imagen_pdt' => null, 'stock_minimo_pdt' => 5, 'stock_maximo_pdt' => 100, 'peso_contenido_pdt' => 1000, 'unm_id' => 2,'enp_id' => null, 'status_id' => 1],
            ['cap_id' => 2, 'codigo_pdt' => 'FRU-PUL-004', 'nombre_pdt' => 'Pulpa de Mora', 'descripcion_pdt' => null, 'imagen_pdt' => null, 'stock_minimo_pdt' => 5, 'stock_maximo_pdt' => 100, 'peso_contenido_pdt' => 1000, 'unm_id' => 2, 'enp_id' => null,'status_id' => 1],
            ['cap_id' => 10, 'codigo_pdt' => 'GRA-MAN-005', 'nombre_pdt' => 'Mantequilla 1k', 'descripcion_pdt' => null, 'imagen_pdt' => null, 'stock_minimo_pdt' => 5, 'stock_maximo_pdt' => 100, 'peso_contenido_pdt' => 1000, 'unm_id' => 2, 'enp_id' => null,'status_id' => 1],
            ['cap_id' => 10, 'codigo_pdt' => 'GRA-MAN-005-1', 'nombre_pdt' => 'Mantequilla 500g', 'descripcion_pdt' => null, 'imagen_pdt' => null, 'stock_minimo_pdt' => 5, 'stock_maximo_pdt' => 100, 'peso_contenido_pdt' => 500, 'unm_id' => 1,'enp_id' => null, 'status_id' => 1],
            ['cap_id' => 5, 'codigo_pdt' => 'CAR-CAR-006', 'nombre_pdt' => 'Carne Molida', 'descripcion_pdt' => null, 'imagen_pdt' => null, 'stock_minimo_pdt' => 5, 'stock_maximo_pdt' => 100, 'peso_contenido_pdt' => 1000, 'unm_id' => 2, 'enp_id' => null,'status_id' => 1],
            ['cap_id' => 3, 'codigo_pdt' => 'VER-PAP-007', 'nombre_pdt' => 'Papa', 'descripcion_pdt' => null, 'imagen_pdt' => null, 'stock_minimo_pdt' => 5, 'stock_maximo_pdt' => 100, 'peso_contenido_pdt' => 1000, 'unm_id' => 2,'enp_id' => null, 'status_id' => 1],
            ['cap_id' => 3, 'codigo_pdt' => 'VER-ZAN-008', 'nombre_pdt' => 'Zanahoria', 'descripcion_pdt' => null, 'imagen_pdt' => null, 'stock_minimo_pdt' => 5, 'stock_maximo_pdt' => 100, 'peso_contenido_pdt' => 1000, 'unm_id' => 2, 'enp_id' => null,'status_id' => 1],
            ['cap_id' => 3, 'codigo_pdt' => 'VER-ALI-009', 'nombre_pdt' => 'Aliños Verdes', 'descripcion_pdt' => null, 'imagen_pdt' => null, 'stock_minimo_pdt' => 5, 'stock_maximo_pdt' => 100, 'peso_contenido_pdt' => 1000, 'unm_id' => 2, 'enp_id' => null,'status_id' => 1],
            ['cap_id' => 3, 'codigo_pdt' => 'VER-CEB-010', 'nombre_pdt' => 'Cebolla', 'descripcion_pdt' => null, 'imagen_pdt' => null, 'stock_minimo_pdt' => 5, 'stock_maximo_pdt' => 100, 'peso_contenido_pdt' => 1000, 'unm_id' => 2,'enp_id' => null, 'status_id' => 1],
            ['cap_id' => 2, 'codigo_pdt' => 'FRU-TOM-011', 'nombre_pdt' => 'Tomate', 'descripcion_pdt' => null, 'imagen_pdt' => null, 'stock_minimo_pdt' => 5, 'stock_maximo_pdt' => 100, 'peso_contenido_pdt' => 1000, 'unm_id' => 2, 'enp_id' => null,'status_id' => 1],
            ['cap_id' => 3, 'codigo_pdt' => 'VER-AJO-012', 'nombre_pdt' => 'Ajo', 'descripcion_pdt' => null, 'imagen_pdt' => null, 'stock_minimo_pdt' => 5, 'stock_maximo_pdt' => 100, 'peso_contenido_pdt' => 1000, 'unm_id' => 2,'enp_id' => null, 'status_id' => 1],
            ['cap_id' => 1, 'codigo_pdt' => 'EMB-SAL-013', 'nombre_pdt' => 'Salchicha', 'descripcion_pdt' => null, 'imagen_pdt' => null, 'stock_minimo_pdt' => 5, 'stock_maximo_pdt' => 100, 'peso_contenido_pdt' => 1000, 'unm_id' => 2, 'enp_id' => null,'status_id' => 1],
            ['cap_id' => 5, 'codigo_pdt' => 'CAR-POL-014', 'nombre_pdt' => 'Pollo', 'descripcion_pdt' => null, 'imagen_pdt' => null, 'stock_minimo_pdt' => 5, 'stock_maximo_pdt' => 100, 'peso_contenido_pdt' => 1000, 'unm_id' => 2,'enp_id' => null, 'status_id' => 1],
            ['cap_id' => 6, 'codigo_pdt' => 'LAC-QUE-015', 'nombre_pdt' => 'Queso Blanco', 'descripcion_pdt' => null, 'imagen_pdt' => null, 'stock_minimo_pdt' => 5, 'stock_maximo_pdt' => 100, 'peso_contenido_pdt' => 1000, 'unm_id' => 2,'enp_id' => null, 'status_id' => 1],
            ['cap_id' => 6, 'codigo_pdt' => 'LAC-QUE-015-1', 'nombre_pdt' => 'Queso Amarillo', 'descripcion_pdt' => null, 'imagen_pdt' => null, 'stock_minimo_pdt' => 5, 'stock_maximo_pdt' => 100, 'peso_contenido_pdt' => 300, 'unm_id' => 1,'enp_id' => null, 'status_id' => 1],
            ['cap_id' => 5, 'codigo_pdt' => 'CAR-RES-016', 'nombre_pdt' => 'Carne de Res', 'descripcion_pdt' => null, 'imagen_pdt' => null, 'stock_minimo_pdt' => 5, 'stock_maximo_pdt' => 80, 'peso_contenido_pdt' => 1000, 'unm_id' => 2,'enp_id' => null, 'status_id' => 1],
            ['cap_id' => 5, 'codigo_pdt' => 'CAR-CER-017', 'nombre_pdt' => 'Carne de Cerdo', 'descripcion_pdt' => null, 'imagen_pdt' => null, 'stock_minimo_pdt' => 5, 'stock_maximo_pdt' => 80, 'peso_contenido_pdt' => 1000, 'unm_id' => 2, 'enp_id' => null,'status_id' => 1],
            ['cap_id' => 2, 'codigo_pdt' => 'FRU-MAN-018', 'nombre_pdt' => 'Manzana', 'descripcion_pdt' => null, 'imagen_pdt' => null, 'stock_minimo_pdt' => 10, 'stock_maximo_pdt' => 150, 'peso_contenido_pdt' => 1000, 'unm_id' => 2, 'enp_id' => null,'status_id' => 1],
            ['cap_id' => 2, 'codigo_pdt' => 'FRU-BAN-019', 'nombre_pdt' => 'Cambur', 'descripcion_pdt' => null, 'imagen_pdt' => null, 'stock_minimo_pdt' => 10, 'stock_maximo_pdt' => 150, 'peso_contenido_pdt' => 1000, 'unm_id' => 2,'enp_id' => null, 'status_id' => 1],
            ['cap_id' => 3, 'codigo_pdt' => 'VER-LEC-020', 'nombre_pdt' => 'Lechuga', 'descripcion_pdt' => null, 'imagen_pdt' => null, 'stock_minimo_pdt' => 5, 'stock_maximo_pdt' => 60, 'peso_contenido_pdt' => 500, 'unm_id' => 1,'enp_id' => null, 'status_id' => 1],
            ['cap_id' => 3, 'codigo_pdt' => 'VER-PIM-021', 'nombre_pdt' => 'Pimentón', 'descripcion_pdt' => null, 'imagen_pdt' => null, 'stock_minimo_pdt' => 5, 'stock_maximo_pdt' => 60, 'peso_contenido_pdt' => 500, 'unm_id' => 1,'enp_id' => null, 'status_id' => 1],
            ['cap_id' => 7, 'codigo_pdt' => 'CER-ARR-022', 'nombre_pdt' => 'Arroz', 'descripcion_pdt' => null, 'imagen_pdt' => null, 'stock_minimo_pdt' => 10, 'stock_maximo_pdt' => 200, 'peso_contenido_pdt' => 1000, 'unm_id' => 2,'enp_id' => null, 'status_id' => 1],
            ['cap_id' => 9, 'codigo_pdt' => 'CAR-PAST-023', 'nombre_pdt' => 'Pasta', 'descripcion_pdt' => null, 'imagen_pdt' => null, 'stock_minimo_pdt' => 10, 'stock_maximo_pdt' => 200, 'peso_contenido_pdt' => 500, 'unm_id' => 1,'enp_id' => null, 'status_id' => 1],
            ['cap_id' => 6, 'codigo_pdt' => 'LAC-LEC-024', 'nombre_pdt' => 'Leche', 'descripcion_pdt' => null, 'imagen_pdt' => null, 'stock_minimo_pdt' => 10, 'stock_maximo_pdt' => 120, 'peso_contenido_pdt' => 1000, 'unm_id' => 3,'enp_id' => null, 'status_id' => 1],
            ['cap_id' => 6, 'codigo_pdt' => 'LAC-YOG-025', 'nombre_pdt' => 'Yogurt', 'descripcion_pdt' => null, 'imagen_pdt' => null, 'stock_minimo_pdt' => 10, 'stock_maximo_pdt' => 80, 'peso_contenido_pdt' => 500, 'unm_id' => 1, 'enp_id' => null,'status_id' => 1],
            ['cap_id' => 11, 'codigo_pdt' => 'BEB-AGU-026', 'nombre_pdt' => 'Agua Mineral', 'descripcion_pdt' => null, 'imagen_pdt' => null, 'stock_minimo_pdt' => 20, 'stock_maximo_pdt' => 300, 'peso_contenido_pdt' => 1500, 'unm_id' => 3,'enp_id' => null, 'status_id' => 1],
            ['cap_id' => 11, 'codigo_pdt' => 'BEB-REF-027', 'nombre_pdt' => 'Refresco', 'descripcion_pdt' => null, 'imagen_pdt' => null, 'stock_minimo_pdt' => 20, 'stock_maximo_pdt' => 300, 'peso_contenido_pdt' => 2000, 'unm_id' => 3,'enp_id' => null, 'status_id' => 1],
            ['cap_id' => 13, 'codigo_pdt' => 'CON-PIM-028', 'nombre_pdt' => 'Pimienta', 'descripcion_pdt' => null, 'imagen_pdt' => null, 'stock_minimo_pdt' => 5, 'stock_maximo_pdt' => 40, 'peso_contenido_pdt' => 100, 'unm_id' => 1, 'enp_id' => null,'status_id' => 1],
            ['cap_id' => 13, 'codigo_pdt' => 'CON-COM-029', 'nombre_pdt' => 'Comino', 'descripcion_pdt' => null, 'imagen_pdt' => null, 'stock_minimo_pdt' => 5, 'stock_maximo_pdt' => 40, 'peso_contenido_pdt' => 100, 'unm_id' => 1,'enp_id' => null, 'status_id' => 1],

        ]);
    }
}
