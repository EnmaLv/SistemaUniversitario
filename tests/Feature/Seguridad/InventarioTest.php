<?php

namespace Tests\Feature\Seguridad;

use App\Exceptions\Salud\StockInsuficienteException;
use App\Models\InventarioTotal;
use App\Support\Inventario\ServicioInventario;
use Illuminate\Support\Facades\DB;

class InventarioTest extends SeguridadTestCase
{
    private ServicioInventario $servicio;

    private int $productoId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->servicio = app(ServicioInventario::class);

        $unidad = DB::table('unidad_medida')->insertGetId(['nombre_unm' => 'Kilo', 'abreviatura_unm' => 'Kg', 'factor_conversion_unm' => 1000, 'created_at' => now(), 'updated_at' => now()], 'id_unm');
        $tipo = DB::table('tipo_producto')->insertGetId(['nombre_tpr' => 'Alimentos', 'mod_id' => DB::table('modulo')->value('id_mod'), 'created_at' => now(), 'updated_at' => now()], 'id_tpr');
        $categoria = DB::table('categoria_producto')->insertGetId(['nombre_cap' => 'Cereales', 'tpr_id' => $tipo, 'created_at' => now(), 'updated_at' => now()], 'id_cap');

        $this->productoId = DB::table('producto')->insertGetId([
            'codigo_pdt'         => 'PRB-' . random_int(1000, 999999),
            'nombre_pdt'         => 'Harina de prueba',
            'stock_minimo_pdt'   => 5,
            'stock_maximo_pdt'   => 50,
            'peso_contenido_pdt' => 1000,
            'unm_id'             => $unidad,
            'cap_id'             => $categoria,
            'created_at'         => now(),
            'updated_at'         => now(),
        ], 'id_pdt');
    }

    private function crearLote(): int
    {
        $proveedor = DB::table('proveedor')->insertGetId([
            'nombre_contacto_prv' => 'Proveedor', 'telefono_prv' => '0424-0000000', 'email_prv' => 'p' . random_int(1, 99999) . '@prueba.test',
            'created_at' => now(), 'updated_at' => now(),
        ], 'id_prv');

        return DB::table('lote')->insertGetId([
            'codigo_lote_lot'       => 'L-' . random_int(1000, 999999),
            'fecha_entrada_lot'     => now()->toDateString(),
            'fecha_vencimiento_lot' => now()->addMonths(3)->toDateString(),
            'pdt_id'                => $this->productoId,
            'prv_id'                => $proveedor,
            'created_at'            => now(),
            'updated_at'            => now(),
        ], 'id_lot');
    }

    private function total(int $sede): ?InventarioTotal
    {
        return InventarioTotal::todasLasSedes()->where('pdt_id', $this->productoId)->where('sed_id', $sede)->first();
    }

    public function test_la_entrada_actualiza_lote_sede_total_y_movimiento(): void
    {
        $lote = $this->crearLote();

        $movimiento = $this->servicio->entrada($lote, $this->sedeA(), 10);
        $this->servicio->entrada($lote, $this->sedeA(), 5);

        $existencia = DB::table('inventario_sede_lote')->where('lot_id', $lote)->where('sed_id', $this->sedeA())->first();
        $this->assertEquals(15, $existencia->cantidad_isl);
        $this->assertEquals(15000, $existencia->cantidad_convertida_isl);
        $this->assertEquals(15000, DB::table('lote')->where('id_lot', $lote)->value('cantidad_actual_lot'));
        $this->assertEquals(15, $this->total($this->sedeA())->cantidad_total_int);
        $this->assertSame('ENTRADA', $movimiento->tipo_movimiento_moi);
        $this->assertEquals(0, $movimiento->cantidad_anterior_moi);
        $this->assertEquals(10, $movimiento->cantidad_final_moi);
        $this->assertNull($this->total($this->sedeB()));
    }

    public function test_la_salida_descuenta_solo_de_la_sede_indicada(): void
    {
        $lote = $this->crearLote();
        $this->servicio->entrada($lote, $this->sedeA(), 10);
        $this->servicio->entrada($lote, $this->sedeB(), 4);

        $movimiento = $this->servicio->salida($lote, $this->sedeA(), 3);

        $this->assertEquals(7, $this->total($this->sedeA())->cantidad_total_int);
        $this->assertEquals(4, $this->total($this->sedeB())->cantidad_total_int);
        $this->assertEquals(10, $movimiento->cantidad_anterior_moi);
        $this->assertEquals(7, $movimiento->cantidad_final_moi);
        $this->assertEquals(11000, DB::table('lote')->where('id_lot', $lote)->value('cantidad_actual_lot'));
    }

    public function test_una_salida_sin_stock_suficiente_falla_sin_dejar_datos_inconsistentes(): void
    {
        $lote = $this->crearLote();
        $this->servicio->entrada($lote, $this->sedeA(), 10);
        $this->servicio->entrada($lote, $this->sedeB(), 20);
        $movimientos = DB::table('movimiento_inventario')->where('lot_id', $lote)->count();

        try {
            DB::transaction(function () use ($lote) {
                $this->servicio->salida($lote, $this->sedeA(), 4);
                $this->servicio->salida($lote, $this->sedeA(), 7);
            });
            $this->fail('La salida debía fallar por falta de stock.');
        } catch (StockInsuficienteException $e) {
            $this->assertStringContainsString('Stock insuficiente', $e->getMessage());
        }

        $this->assertEquals(10, DB::table('inventario_sede_lote')->where('lot_id', $lote)->where('sed_id', $this->sedeA())->value('cantidad_isl'));
        $this->assertEquals(10, $this->total($this->sedeA())->cantidad_total_int);
        $this->assertSame($movimientos, DB::table('movimiento_inventario')->where('lot_id', $lote)->count());

        $this->expectException(StockInsuficienteException::class);
        $this->servicio->salida($lote, $this->sedeA(), 15);
    }

    public function test_transferir_mueve_stock_entre_sedes(): void
    {
        $lote = $this->crearLote();
        $this->servicio->entrada($lote, $this->sedeA(), 10);

        [$salida, $entrada] = $this->servicio->transferir($lote, $this->sedeA(), $this->sedeB(), 4);

        $this->assertSame(ServicioInventario::TRANSFERENCIA_SALIDA, $salida->tipo_movimiento_moi);
        $this->assertSame(ServicioInventario::TRANSFERENCIA_ENTRADA, $entrada->tipo_movimiento_moi);
        $this->assertEquals(6, $this->total($this->sedeA())->cantidad_total_int);
        $this->assertEquals(4, $this->total($this->sedeB())->cantidad_total_int);
        $this->assertEquals(10000, DB::table('lote')->where('id_lot', $lote)->value('cantidad_actual_lot'));
    }

    public function test_recalcular_reconstruye_los_totales_desde_las_existencias(): void
    {
        $loteUno = $this->crearLote();
        $loteDos = $this->crearLote();
        $this->servicio->entrada($loteUno, $this->sedeA(), 10);
        $this->servicio->entrada($loteDos, $this->sedeA(), 2.5);
        $this->servicio->entrada($loteDos, $this->sedeB(), 1);

        DB::table('inventario_total')->where('pdt_id', $this->productoId)->update(['cantidad_total_int' => 999]);

        $this->artisan('inventario:recalcular-totales', ['--producto' => $this->productoId])->assertSuccessful();

        $this->assertEquals(12.5, $this->total($this->sedeA())->cantidad_total_int);
        $this->assertEquals(1, $this->total($this->sedeB())->cantidad_total_int);
        $this->assertSame('normal', $this->total($this->sedeA())->estadoStock());
        $this->assertSame('bajo_minimo', $this->total($this->sedeB())->estadoStock());
    }

    public function test_un_usuario_solo_ve_el_total_de_su_sede(): void
    {
        $lote = $this->crearLote();
        $this->servicio->entrada($lote, $this->sedeA(), 10);
        $this->servicio->entrada($lote, $this->sedeB(), 4);

        $this->actingAs($this->crearUsuario('Obrero', sedeId: $this->sedeB()));

        $this->assertEquals(4, (float) InventarioTotal::where('pdt_id', $this->productoId)->sum('cantidad_total_int'));
    }
}
