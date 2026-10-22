<?php

namespace Database\Seeders;

use App\Support\Inventario\ServicioInventario;
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
        (new CategoriaSeeder())->sembrarComplementarias();

        $proveedor = DB::table('proveedor')->orderBy('id_prv')->value('id_prv');

        if (! $proveedor) {
            return;
        }

        foreach (self::EXISTENCIAS as $codigo => $porSede) {
            $producto = DB::table('producto')->where('codigo_pdt', $codigo)->first();

            if (! $producto) {
                continue;
            }

            $total = array_sum($porSede);
            $loteId = DB::table('lote')->insertGetId([
                'codigo_lote_lot'       => 'INI-' . $codigo,
                'fecha_entrada_lot'     => now()->toDateString(),
                'fecha_vencimiento_lot' => now()->addMonths(6)->toDateString(),
                'cantidad_inicial_lot'  => $total * (float) ($producto->peso_contenido_pdt ?: 1),
                'cantidad_actual_lot'   => 0,
                'precio_compra_lot'     => 1,
                'status_id'             => 1,
                'pdt_id'                => $producto->id_pdt,
                'prv_id'                => $proveedor,
                'created_at'            => now(),
                'updated_at'            => now(),
            ], 'id_lot');

            foreach ($porSede as $sede => $cantidad) {
                app(ServicioInventario::class)->entrada($loteId, $sede, $cantidad, $cantidad * (float) ($producto->peso_contenido_pdt ?: 1), [
                    'unm_id'              => $producto->unm_id,
                    'referencia_type_moi' => 'inventario_inicial',
                    'observacion_moi'     => 'Inventario inicial de los seeders',
                ]);
            }
        }
    }
}
