<?php

namespace Tests\Feature\Seguridad;

use App\Livewire\RegistroComida;
use App\Models\Compra;
use App\Models\Lote;
use App\Models\Usuario;
use App\Support\Inventario\ServicioInventario;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

class InventarioFlujosTest extends SeguridadTestCase
{
    private Usuario $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->crearUsuario('Administrador');
        $this->actingAs($this->admin)->withSession(['master_key_validated' => true, 'ultima_actividad' => time(), 'sede_activa' => $this->sedeA()]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function producto(int $tipoProducto, string $nombre, float $peso = 1000): int
    {
        $categoria = DB::table('categoria_producto')->insertGetId(['nombre_cap' => 'Cat ' . $nombre, 'tpr_id' => $tipoProducto, 'created_at' => now(), 'updated_at' => now()], 'id_cap');

        return DB::table('producto')->insertGetId([
            'codigo_pdt' => 'FLJ-' . random_int(1000, 999999), 'nombre_pdt' => $nombre, 'peso_contenido_pdt' => $peso,
            'unidades_presentacion_pdt' => 1, 'unm_id' => DB::table('unidad_medida')->where('abreviatura_unm', 'g')->value('id_unm') ?? 1,
            'cap_id' => $categoria, 'created_at' => now(), 'updated_at' => now(),
        ], 'id_pdt');
    }

    private function lote(int $producto, ?string $vencimiento): int
    {
        return DB::table('lote')->insertGetId([
            'codigo_lote_lot' => 'L-' . random_int(1000, 999999), 'fecha_entrada_lot' => now()->toDateString(),
            'fecha_vencimiento_lot' => $vencimiento, 'pdt_id' => $producto, 'created_at' => now(), 'updated_at' => now(),
        ], 'id_lot');
    }

    private function assertCuadra(int $producto, int $sede): void
    {
        $suma = DB::table('inventario_sede_lote as e')->join('lote as l', 'l.id_lot', '=', 'e.lot_id')
            ->where('l.pdt_id', $producto)->where('e.sed_id', $sede)
            ->selectRaw('COALESCE(SUM(e.cantidad_isl), 0) AS cantidad, COALESCE(SUM(e.cantidad_convertida_isl), 0) AS convertida')->first();
        $total = DB::table('inventario_total')->where('pdt_id', $producto)->where('sed_id', $sede)->first();

        $this->assertNotNull($total);
        $this->assertEqualsWithDelta((float) $suma->cantidad, (float) $total->cantidad_total_int, 0.01);
        $this->assertEqualsWithDelta((float) $suma->convertida, (float) $total->cantidad_convertida_total_int, 0.01);

        foreach (DB::table('lote')->where('pdt_id', $producto)->get() as $lote) {
            $this->assertEqualsWithDelta(
                (float) DB::table('inventario_sede_lote')->where('lot_id', $lote->id_lot)->sum('cantidad_convertida_isl'),
                (float) $lote->cantidad_actual_lot,
                0.01
            );
        }
    }

    private function ultimoMovimiento(int $producto): object
    {
        return DB::table('movimiento_inventario')->where('pdt_id', $producto)->orderByDesc('id_moi')->first();
    }

    public function test_compra_finalizada_se_reparte_entre_las_sedes_activas(): void
    {
        DB::table('sede')->update(['es_principal_sed' => false]);
        DB::table('sede')->where('id_sed', $this->sedeA())->update(['es_principal_sed' => true, 'status_id' => 1]);
        $sedes = DB::table('sede')->where('status_id', 1)->whereNull('deleted_at')->pluck('id_sed')->map(fn ($id) => (int) $id)->all();
        $otras = count($sedes) - 1;
        $porSede = intdiv(400, count($sedes)) / 100;

        $producto = $this->producto(1, 'Harina compra');
        $proveedor = DB::table('proveedor')->insertGetId(['telefono_prv' => '0424', 'email_prv' => 'p' . random_int(1, 99999) . '@x.test', 'created_at' => now(), 'updated_at' => now()], 'id_prv');
        $compra = Compra::find(Compra::crearCompra(['proveedor_id' => $proveedor, 'fecha' => now(), 'sed_id' => $this->sedeA()]));
        $lote = Lote::create(['codigo_lote_lot' => 'C-1', 'fecha_entrada_lot' => now(), 'fecha_vencimiento_lot' => now()->addMonths(6), 'pdt_id' => $producto, 'prv_id' => $proveedor, 'status_id' => 1]);
        $compra->detalleCompras()->create(['pdt_id' => $producto, 'lot_id' => $lote->id_lot, 'cantidad_dcm' => 4, 'cantidad_convertida_dcm' => 4000, 'unm_id' => 1, 'precio_unitario_dcm' => 2, 'subtotal_dcm' => 8]);

        Compra::finalizarCompraDistribuida($compra);

        $movimientos = DB::table('movimiento_inventario')->where('pdt_id', $producto)->get();
        $this->assertCount(count($sedes), $movimientos);
        $this->assertEqualsCanonicalizing($sedes, $movimientos->pluck('sed_id')->map(fn ($id) => (int) $id)->all());

        foreach ($movimientos as $mov) {
            $this->assertSame(ServicioInventario::ENTRADA, $mov->tipo_movimiento_moi);
            $this->assertSame('compra', $mov->referencia_type_moi);
            $this->assertSame($compra->id_cmp, (int) $mov->referencia_id_moi);
            $this->assertSame($this->admin->id_use, (int) $mov->use_id);
            $esperado = (int) $mov->sed_id === $this->sedeA() ? round(4 - $porSede * $otras, 2) : $porSede;
            $this->assertEqualsWithDelta($esperado, (float) $mov->cantidad_final_moi, 0.001);
            $this->assertCuadra($producto, (int) $mov->sed_id);
        }

        $this->assertEqualsWithDelta(4, (float) DB::table('inventario_sede_lote')->where('lot_id', $lote->id_lot)->sum('cantidad_isl'), 0.001);
        $this->assertEqualsWithDelta(4000, (float) DB::table('inventario_sede_lote')->where('lot_id', $lote->id_lot)->sum('cantidad_convertida_isl'), 0.01);
    }

    public function test_servir_comida_descuenta_ingredientes_por_fefo(): void
    {
        $producto = $this->producto(1, 'Harina comedor', 1000);
        $loteTardio = $this->lote($producto, now()->addMonths(5)->toDateString());
        $loteProximo = $this->lote($producto, now()->addDays(10)->toDateString());
        $servicio = app(ServicioInventario::class);
        $servicio->entrada($loteTardio, $this->sedeA(), 2, 2000);
        $servicio->entrada($loteProximo, $this->sedeA(), 1, 1000);

        $receta = DB::table('receta')->insertGetId(['nombre_rct' => 'Arepa FEFO', 'created_at' => now(), 'updated_at' => now()], 'id_rct');
        DB::table('receta_ingrediente')->insert(['rct_id' => $receta, 'pdt_id' => $producto, 'unm_id' => 1, 'cantidad_porcion_rci' => 300, 'cantidad_convertida_rci' => 300, 'created_at' => now(), 'updated_at' => now()]);

        Carbon::setTestNow(now()->setTime(12, 30));
        Livewire::actingAs($this->admin)->test(RegistroComida::class)
            ->set('desayunos_agregados', [['receta_id' => $receta, 'cantidad' => 4]])
            ->call('saveDesayuno');

        $this->assertEqualsWithDelta(0, (float) DB::table('inventario_sede_lote')->where('lot_id', $loteProximo)->value('cantidad_convertida_isl'), 0.01);
        $this->assertEqualsWithDelta(1800, (float) DB::table('inventario_sede_lote')->where('lot_id', $loteTardio)->value('cantidad_convertida_isl'), 0.01);
        $this->assertSame(2, DB::table('movimiento_inventario')->where('pdt_id', $producto)->where('referencia_type_moi', 'consumo_diario')->where('tipo_movimiento_moi', ServicioInventario::SALIDA)->count());
        $this->assertSame(1, DB::table('consumo_diario')->where('rct_id', $receta)->count());
        $this->assertCuadra($producto, $this->sedeA());
    }

    public function test_merma_de_vencidos_deja_el_lote_en_cero(): void
    {
        $producto = $this->producto(1, 'Leche vencida');
        $lote = $this->lote($producto, now()->subDay()->toDateString());
        app(ServicioInventario::class)->entrada($lote, $this->sedeA(), 3, 3000);

        $this->post(route('admin.movimientos.lotes.mermar'))->assertRedirect();

        $mov = $this->ultimoMovimiento($producto);
        $this->assertSame(ServicioInventario::MERMA, $mov->tipo_movimiento_moi);
        $this->assertEquals(3, (float) $mov->cantidad_anterior_moi);
        $this->assertEquals(0, (float) $mov->cantidad_final_moi);
        $this->assertSame(2, (int) DB::table('lote')->where('id_lot', $lote)->value('status_id'));
        $this->assertCuadra($producto, $this->sedeA());
    }

    public function test_recalcular_totales_en_base_sembrada_no_cambia_nada(): void
    {
        $antes = DB::table('inventario_total')->orderBy('pdt_id')->orderBy('sed_id')->get(['pdt_id', 'sed_id', 'cantidad_total_int', 'cantidad_convertida_total_int'])
            ->map(fn ($f) => [$f->pdt_id, $f->sed_id, round((float) $f->cantidad_total_int, 2), round((float) $f->cantidad_convertida_total_int, 2)])->all();

        Artisan::call('inventario:recalcular-totales');

        $despues = DB::table('inventario_total')->orderBy('pdt_id')->orderBy('sed_id')->get(['pdt_id', 'sed_id', 'cantidad_total_int', 'cantidad_convertida_total_int'])
            ->map(fn ($f) => [$f->pdt_id, $f->sed_id, round((float) $f->cantidad_total_int, 2), round((float) $f->cantidad_convertida_total_int, 2)])->all();

        $this->assertNotEmpty($antes);
        $this->assertSame($antes, $despues);
    }
}
