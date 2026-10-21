<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InventarioInicialSeeder extends Seeder
{
    private const EXISTENCIAS = [
        'CER-HAR-001' => [1 => 40, 2 => 15],
        'CAR-AZU-003' => [1 => 25, 2 => 10],
        'CAR-POL-014' => [1 => 30, 2 => 8],
        'LAC-QUE-015' => [1 => 12, 2 => 20],
    ];

    public function run(): void
    {
        $proveedor = DB::table('proveedors')->orderBy('id')->value('id');

        if (! $proveedor) {
            return;
        }

        foreach (self::EXISTENCIAS as $codigo => $porSede) {
            $producto = DB::table('productos')->where('codigo', $codigo)->first();

            if (! $producto) {
                continue;
            }

            $total = array_sum($porSede);
            $loteId = DB::table('lotes')->insertGetId([
                'codigo_lote'       => 'INI-' . $codigo,
                'fecha_entrada'     => now()->toDateString(),
                'fecha_vencimiento' => now()->addMonths(6)->toDateString(),
                'cantidad_inicial'  => $total,
                'cantidad_actual'   => $total,
                'precio_compra'     => 1,
                'estado'            => 1,
                'producto_id'       => $producto->id,
                'proveedor_id'      => $proveedor,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

            foreach ($porSede as $sede => $cantidad) {
                $convertida = $cantidad * (float) ($producto->peso_contenido ?: 1);

                DB::table('inventario_sede_lotes')->insert([
                    'sede_id'             => $sede,
                    'lote_id'             => $loteId,
                    'cantidad'            => $cantidad,
                    'cantidad_convertida' => $convertida,
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ]);

                DB::table('movimiento_inventarios')->insert([
                    'producto_id'         => $producto->id,
                    'lote_id'             => $loteId,
                    'sede_id'             => $sede,
                    'tipo_movimiento'     => 'ENTRADA',
                    'cantidad'            => $cantidad,
                    'cantidad_convertida' => $convertida,
                    'cantidad_anterior'   => 0,
                    'cantidad_final'      => $cantidad,
                    'referencia_type'     => 'inventario_inicial',
                    'unidad_id'           => $producto->unidad_id,
                    'fecha'               => now(),
                    'observaciones'       => 'Inventario inicial de los seeders',
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ]);
            }
        }
    }
}
