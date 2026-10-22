<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PrecioProductoSeeder extends Seeder
{
    private const PRECIOS = [
        'CER-HAR-001' => [1.44, 20],
        'EMB-MOR-002' => [1.20, 15],
        'CAR-AZU-003' => [1.25, 25],
        'FRU-PUL-004' => [5.99, 18],
        'GRA-MAN-005' => [5.20, 10],
        'GRA-MAN-005-1' => [2.60, 12],
        'CAR-CAR-006' => [8.73, 20],
        'VER-PAP-007' => [1.82, 15],
        'VER-ZAN-008' => [1.50, 22],
        'VER-ALI-009' => [1.00, 18],
        'VER-CEB-010' => [1.80, 18],
        'FRU-TOM-011' => [2.01, 18],
        'VER-AJO-012' => [2.50, 18],
        'EMB-SAL-013' => [4.00, 18],
        'CAR-POL-014' => [6.08, 18],
        'LAC-QUE-015' => [6.18, 18],
        'LAC-QUE-015-1' => [7.60, 18],
        'CAR-RES-016' => [3.00, 20],
        'CAR-CER-017' => [5.06, 18],
        'FRU-MAN-018' => [1.43, 25],
        'FRU-BAN-019' => [1.32, 30],
        'VER-LEC-020' => [1.10, 25],
        'VER-PIM-021' => [1.47, 25],
        'CER-ARR-022' => [1.20, 20],
        'CAR-PAST-023' => [0.75, 22],
        'LAC-LEC-024' => [1.70, 18],
        'LAC-YOG-025' => [2.00, 20],
        'BEB-AGU-026' => [1.00, 30],
        'BEB-REF-027' => [2.82, 35],
        'CON-PIM-028' => [0.55, 40],
        'CON-COM-029' => [0.50, 40],
    ];

    public function run(): void
    {
        foreach (self::PRECIOS as $codigo => [$costo, $margen]) {
            DB::table('precio_producto')->insert([
                'pdt_id'         => ProductoSeeder::producto($codigo),
                'costo_usd_ppr'  => $costo,
                'margen_ppr'     => $margen,
                'precio_usd_ppr' => null,
            ]);
        }
    }
}
