<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PrecioProductoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('precio_producto')->insert([
            ['pdt_id' => 1, 'costo_usd_ppr' => 1.44, 'margen_ppr' => 20, 'precio_usd_ppr' => null],
            ['pdt_id' => 2, 'costo_usd_ppr' => 1.20, 'margen_ppr' => 15, 'precio_usd_ppr' => null],
            ['pdt_id' => 3, 'costo_usd_ppr' => 1.25, 'margen_ppr' => 25, 'precio_usd_ppr' => null],
            ['pdt_id' => 4, 'costo_usd_ppr' => 5.99, 'margen_ppr' => 18, 'precio_usd_ppr' => null],
            ['pdt_id' => 5, 'costo_usd_ppr' => 5.20, 'margen_ppr' => 10, 'precio_usd_ppr' => null],
            ['pdt_id' => 6, 'costo_usd_ppr' => 2.60, 'margen_ppr' => 12, 'precio_usd_ppr' => null],
            ['pdt_id' => 7, 'costo_usd_ppr' => 8.73, 'margen_ppr' => 20, 'precio_usd_ppr' => null],
            ['pdt_id' => 8, 'costo_usd_ppr' => 1.82, 'margen_ppr' => 15, 'precio_usd_ppr' => null],
            ['pdt_id' => 9, 'costo_usd_ppr' => 1.50, 'margen_ppr' => 22, 'precio_usd_ppr' => null],
            ['pdt_id' => 10, 'costo_usd_ppr' => 1.00, 'margen_ppr' => 18, 'precio_usd_ppr' => null],
            ['pdt_id' => 11, 'costo_usd_ppr' => 1.80, 'margen_ppr' => 18, 'precio_usd_ppr' => null],
            ['pdt_id' => 12, 'costo_usd_ppr' => 2.01, 'margen_ppr' => 18, 'precio_usd_ppr' => null],
            ['pdt_id' => 13, 'costo_usd_ppr' => 2.50, 'margen_ppr' => 18, 'precio_usd_ppr' => null],
            ['pdt_id' => 14, 'costo_usd_ppr' => 4.00, 'margen_ppr' => 18, 'precio_usd_ppr' => null],
            ['pdt_id' => 15, 'costo_usd_ppr' => 6.08, 'margen_ppr' => 18, 'precio_usd_ppr' => null],
            ['pdt_id' => 16, 'costo_usd_ppr' => 6.18, 'margen_ppr' => 18, 'precio_usd_ppr' => null],
            ['pdt_id' => 17, 'costo_usd_ppr' => 7.60, 'margen_ppr' => 18, 'precio_usd_ppr' => null],
            ['pdt_id' => 18, 'costo_usd_ppr' => 3.00, 'margen_ppr' => 20, 'precio_usd_ppr' => null],
            ['pdt_id' => 19, 'costo_usd_ppr' => 5.06, 'margen_ppr' => 18, 'precio_usd_ppr' => null],
            ['pdt_id' => 20, 'costo_usd_ppr' => 1.43, 'margen_ppr' => 25, 'precio_usd_ppr' => null],
            ['pdt_id' => 21, 'costo_usd_ppr' => 1.32, 'margen_ppr' => 30, 'precio_usd_ppr' => null],
            ['pdt_id' => 22, 'costo_usd_ppr' => 1.10, 'margen_ppr' => 25, 'precio_usd_ppr' => null],
            ['pdt_id' => 23, 'costo_usd_ppr' => 1.47, 'margen_ppr' => 25, 'precio_usd_ppr' => null],
            ['pdt_id' => 24, 'costo_usd_ppr' => 1.20, 'margen_ppr' => 20, 'precio_usd_ppr' => null],
            ['pdt_id' => 25, 'costo_usd_ppr' => 0.75, 'margen_ppr' => 22, 'precio_usd_ppr' => null],
            ['pdt_id' => 26, 'costo_usd_ppr' => 1.70, 'margen_ppr' => 18, 'precio_usd_ppr' => null],
            ['pdt_id' => 27, 'costo_usd_ppr' => 2.00, 'margen_ppr' => 20, 'precio_usd_ppr' => null],
            ['pdt_id' => 28, 'costo_usd_ppr' => 1.00, 'margen_ppr' => 30, 'precio_usd_ppr' => null],
            ['pdt_id' => 29, 'costo_usd_ppr' => 2.82, 'margen_ppr' => 35, 'precio_usd_ppr' => null],
            ['pdt_id' => 30, 'costo_usd_ppr' => 0.55, 'margen_ppr' => 40, 'precio_usd_ppr' => null],
            ['pdt_id' => 31, 'costo_usd_ppr' => 0.50, 'margen_ppr' => 40, 'precio_usd_ppr' => null],
        ]);

    }
}
