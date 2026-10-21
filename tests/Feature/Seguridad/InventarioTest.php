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

        $unidad = DB::table('unidades')->insertGetId(['nombre' => 'Kilo', 'abreviatura' => 'Kg', 'factor_a_gramo' => 1000, 'created_at' => now(), 'updated_at' => now()]);
        $tipo = DB::table('tipo_productos')->insertGetId(['nombre' => 'Alimentos', 'modulo_id' => DB::table('modulos')->value('id'), 'created_at' => now(), 'updated_at' => now()]);
        $categoria = DB::table('categorias')->insertGetId(['nombre' => 'Cereales', 'tipo_producto_id' => $tipo, 'created_at' => now(), 'updated_at' => now()]);

        $this->productoId = DB::table('productos')->insertGetId([
            'codigo'         => 'PRB-' . random_int(1000, 999999),
            'nombre'         => 'Harina de prueba',
            'stock_minimo'   => 5,
            'stock_maximo'   => 50,
            'peso_contenido' => 1000,
            'unidad_id'      => $unidad,
            'categoria_id'   => $categoria,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);
    }

    private function crearLote(): int
    {
        $proveedor = DB::table('proveedors')->insertGetId([
            'nombre' => 'Proveedor', 'telefono' => '0424-0000000', 'email' => 'p' . random_int(1, 99999) . '@prueba.test',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        return DB::table('lotes')->insertGetId([
            'codigo_lote'       => 'L-' . random_int(1000, 999999),
            'fecha_entrada'     => now()->toDateString(),
            'fecha_vencimiento' => now()->addMonths(3)->toDateString(),
            'producto_id'       => $this->productoId,
            'proveedor_id'      => $proveedor,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);
    }

    private function total(int $sede): ?InventarioTotal
    {
        return InventarioTotal::todasLasSedes()->where('producto_id', $this->productoId)->where('sed_id', $sede)->first();
    }

    public function test_la_entrada_actualiza_lote_sede_total_y_movimiento(): void
    {
        $lote = $this->crearLote();

        $movimiento = $this->servicio->entrada($lote, $this->sedeA(), 10);
        $this->servicio->entrada($lote, $this->sedeA(), 5);

        $existencia = DB::table('inventario_sede_lotes')->where('lote_id', $lote)->where('sed_id', $this->sedeA())->first();
        $this->assertEquals(15, $existencia->cantidad);
        $this->assertEquals(15000, $existencia->cantidad_convertida);
        $this->assertEquals(15000, DB::table('lotes')->where('id', $lote)->value('cantidad_actual'));
        $this->assertEquals(15, $this->total($this->sedeA())->cantidad_total);
        $this->assertSame('ENTRADA', $movimiento->tipo_movimiento);
        $this->assertEquals(0, $movimiento->cantidad_anterior);
        $this->assertEquals(10, $movimiento->cantidad_final);
        $this->assertNull($this->total($this->sedeB()));
    }

    public function test_la_salida_descuenta_solo_de_la_sede_indicada(): void
    {
        $lote = $this->crearLote();
        $this->servicio->entrada($lote, $this->sedeA(), 10);
        $this->servicio->entrada($lote, $this->sedeB(), 4);

        $movimiento = $this->servicio->salida($lote, $this->sedeA(), 3);

        $this->assertEquals(7, $this->total($this->sedeA())->cantidad_total);
        $this->assertEquals(4, $this->total($this->sedeB())->cantidad_total);
        $this->assertEquals(10, $movimiento->cantidad_anterior);
        $this->assertEquals(7, $movimiento->cantidad_final);
        $this->assertEquals(11000, DB::table('lotes')->where('id', $lote)->value('cantidad_actual'));
    }

    public function test_una_salida_sin_stock_suficiente_falla_sin_dejar_datos_inconsistentes(): void
    {
        $lote = $this->crearLote();
        $this->servicio->entrada($lote, $this->sedeA(), 10);
        $this->servicio->entrada($lote, $this->sedeB(), 20);
        $movimientos = DB::table('movimiento_inventarios')->where('lote_id', $lote)->count();

        try {
            DB::transaction(function () use ($lote) {
                $this->servicio->salida($lote, $this->sedeA(), 4);
                $this->servicio->salida($lote, $this->sedeA(), 7);
            });
            $this->fail('La salida debía fallar por falta de stock.');
        } catch (StockInsuficienteException $e) {
            $this->assertStringContainsString('Stock insuficiente', $e->getMessage());
        }

        $this->assertEquals(10, DB::table('inventario_sede_lotes')->where('lote_id', $lote)->where('sed_id', $this->sedeA())->value('cantidad'));
        $this->assertEquals(10, $this->total($this->sedeA())->cantidad_total);
        $this->assertSame($movimientos, DB::table('movimiento_inventarios')->where('lote_id', $lote)->count());

        $this->expectException(StockInsuficienteException::class);
        $this->servicio->salida($lote, $this->sedeA(), 15);
    }

    public function test_transferir_mueve_stock_entre_sedes(): void
    {
        $lote = $this->crearLote();
        $this->servicio->entrada($lote, $this->sedeA(), 10);

        [$salida, $entrada] = $this->servicio->transferir($lote, $this->sedeA(), $this->sedeB(), 4);

        $this->assertSame(ServicioInventario::TRANSFERENCIA_SALIDA, $salida->tipo_movimiento);
        $this->assertSame(ServicioInventario::TRANSFERENCIA_ENTRADA, $entrada->tipo_movimiento);
        $this->assertEquals(6, $this->total($this->sedeA())->cantidad_total);
        $this->assertEquals(4, $this->total($this->sedeB())->cantidad_total);
        $this->assertEquals(10000, DB::table('lotes')->where('id', $lote)->value('cantidad_actual'));
    }

    public function test_recalcular_reconstruye_los_totales_desde_las_existencias(): void
    {
        $loteUno = $this->crearLote();
        $loteDos = $this->crearLote();
        $this->servicio->entrada($loteUno, $this->sedeA(), 10);
        $this->servicio->entrada($loteDos, $this->sedeA(), 2.5);
        $this->servicio->entrada($loteDos, $this->sedeB(), 1);

        DB::table('inventario_totals')->where('producto_id', $this->productoId)->update(['cantidad_total' => 999]);

        $this->artisan('inventario:recalcular-totales', ['--producto' => $this->productoId])->assertSuccessful();

        $this->assertEquals(12.5, $this->total($this->sedeA())->cantidad_total);
        $this->assertEquals(1, $this->total($this->sedeB())->cantidad_total);
        $this->assertSame('normal', $this->total($this->sedeA())->estadoStock());
        $this->assertSame('bajo_minimo', $this->total($this->sedeB())->estadoStock());
    }

    public function test_un_usuario_solo_ve_el_total_de_su_sede(): void
    {
        $lote = $this->crearLote();
        $this->servicio->entrada($lote, $this->sedeA(), 10);
        $this->servicio->entrada($lote, $this->sedeB(), 4);

        $this->actingAs($this->crearUsuario('Obrero', sedeId: $this->sedeB()));

        $this->assertEquals(4, (float) InventarioTotal::where('producto_id', $this->productoId)->sum('cantidad_total'));
    }
}
