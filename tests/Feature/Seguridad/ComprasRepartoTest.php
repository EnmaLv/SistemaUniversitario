<?php

namespace Tests\Feature\Seguridad;

use App\Livewire\Admin\Movimientos\Compras\ItemsCompra;
use App\Models\Compra;
use App\Models\Lote;
use App\Models\Rol;
use App\Models\TipoProducto;
use App\Models\Usuario;
use App\Support\Inventario\RepartoCompra;
use App\Support\Inventario\ServicioInventario;
use App\Support\Inventario\UnidadCompra;
use App\Support\Permisos\VersionPermisos;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;

class ComprasRepartoTest extends SeguridadTestCase
{
    private Usuario $admin;

    private ?int $sedeC = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->crearUsuario('Administrador');
        $this->actingAs($this->admin)->withSession(['master_key_validated' => true, 'ultima_actividad' => time(), 'sede_activa' => $this->sedeA()]);
        DB::table('sede')->update(['es_principal_sed' => false]);
        DB::table('sede')->where('id_sed', $this->sedeA())->update(['es_principal_sed' => true, 'status_id' => 1]);
    }

    private function dejarActivas(int $cantidad): array
    {
        $ids = [$this->sedeA(), $this->sedeB()];

        if ($cantidad === 3) {
            $this->sedeC ??= DB::table('sede')->insertGetId([
                'nombre_sed' => 'Sede C reparto ' . Str::random(4), 'direccion_sed' => 'Dirección de pruebas',
                'status_id' => 1, 'created_at' => now(), 'updated_at' => now(),
            ], 'id_sed');
            $ids[] = $this->sedeC;
        }

        DB::table('sede')->whereNotIn('id_sed', $ids)->update(['status_id' => 2]);
        DB::table('sede')->whereIn('id_sed', $ids)->update(['status_id' => 1]);

        $this->assertSame($cantidad, DB::table('sede')->where('status_id', 1)->whereNull('deleted_at')->count());

        return $ids;
    }

    private function unidad(string $abreviatura, string $nombre, float $factor): int
    {
        return (int) (DB::table('unidad_medida')->where('abreviatura_unm', $abreviatura)->value('id_unm')
            ?? DB::table('unidad_medida')->insertGetId(['nombre_unm' => $nombre, 'abreviatura_unm' => $abreviatura, 'factor_conversion_unm' => $factor, 'created_at' => now(), 'updated_at' => now()], 'id_unm'));
    }

    private function producto(string $tipo, string $nombre, int $unidad, array $extra = [], array $costos = []): int
    {
        $categoria = DB::table('categoria_producto')->insertGetId([
            'nombre_cap' => 'Cat ' . $nombre, 'tpr_id' => TipoProducto::idPorNombre($tipo), 'created_at' => now(), 'updated_at' => now(),
        ], 'id_cap');

        $id = DB::table('producto')->insertGetId(array_merge([
            'codigo_pdt' => 'CMP-' . random_int(1000, 999999), 'nombre_pdt' => $nombre . ' ' . Str::random(5), 'peso_contenido_pdt' => 0,
            'unidades_presentacion_pdt' => 1, 'unm_id' => $unidad, 'cap_id' => $categoria, 'precio_compra_pdt' => 0,
            'created_at' => now(), 'updated_at' => now(),
        ], $extra), 'id_pdt');

        foreach ($costos as $costo) {
            DB::table('precio_producto')->insert(['pdt_id' => $id, 'costo_usd_ppr' => $costo, 'margen_ppr' => 20, 'created_at' => now(), 'updated_at' => now()]);
        }

        return $id;
    }

    private function proveedor(): int
    {
        return DB::table('proveedor')->insertGetId([
            'empresa_prv' => 'PROVEEDOR REPARTO', 'telefono_prv' => '0424', 'email_prv' => 'reparto' . random_int(1, 99999) . '@x.test',
            'created_at' => now(), 'updated_at' => now(),
        ], 'id_prv');
    }

    private function compraCon(int $producto, float $cantidad, float $convertida): array
    {
        $proveedor = $this->proveedor();
        $compra = Compra::find(Compra::crearCompra(['proveedor_id' => $proveedor, 'fecha' => now(), 'sed_id' => $this->sedeA()]));
        $lote = Lote::create(['codigo_lote_lot' => 'REP-' . random_int(1000, 999999), 'fecha_entrada_lot' => now(), 'fecha_vencimiento_lot' => now()->addMonths(6), 'pdt_id' => $producto, 'prv_id' => $proveedor, 'status_id' => 1]);
        $compra->detalleCompras()->create([
            'pdt_id' => $producto, 'lot_id' => $lote->id_lot, 'cantidad_dcm' => $cantidad, 'cantidad_convertida_dcm' => $convertida,
            'unm_id' => DB::table('producto')->where('id_pdt', $producto)->value('unm_id'), 'precio_unitario_dcm' => 1, 'subtotal_dcm' => $cantidad,
        ]);

        return [$compra, $lote->id_lot];
    }

    private function existencia(int $lote, int $sede): array
    {
        $fila = DB::table('inventario_sede_lote')->where('lot_id', $lote)->where('sed_id', $sede)->first();

        return [(float) ($fila->cantidad_isl ?? 0), (float) ($fila->cantidad_convertida_isl ?? 0)];
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
    }

    public function test_diez_unidades_entre_tres_sedes_dejan_el_sobrante_en_la_principal(): void
    {
        [$a, $b, $c] = $this->dejarActivas(3);
        $producto = $this->producto(TipoProducto::IMPLEMENTO_DEPORTIVO, 'Balon reparto', $this->unidad('U', 'Unidad', 1));
        [$compra, $lote] = $this->compraCon($producto, 10, 10);

        $vistaPrevia = app(RepartoCompra::class)->calcular($compra);
        $this->assertSame(['cantidad' => 4.0, 'convertida' => 4.0], $vistaPrevia['lineas'][0]['partes'][$a]);
        $this->assertSame(3.0, $vistaPrevia['lineas'][0]['partes'][$b]['cantidad']);
        $this->assertSame(3.0, $vistaPrevia['lineas'][0]['partes'][$c]['cantidad']);
        $this->assertSame(0, DB::table('inventario_sede_lote')->where('lot_id', $lote)->count());

        Compra::finalizarCompraDistribuida($compra);

        $this->assertSame([4.0, 4.0], $this->existencia($lote, $a));
        $this->assertSame([3.0, 3.0], $this->existencia($lote, $b));
        $this->assertSame([3.0, 3.0], $this->existencia($lote, $c));
        $this->assertEqualsWithDelta(10, (float) DB::table('lote')->where('id_lot', $lote)->value('cantidad_actual_lot'), 0.01);
        $this->assertSame(3, DB::table('movimiento_inventario')->where('referencia_type_moi', 'compra')->where('referencia_id_moi', $compra->id_cmp)->where('tipo_movimiento_moi', ServicioInventario::ENTRADA)->count());
        $this->assertSame(Compra::ESTADO_FINALIZADA, DB::table('compra')->where('id_cmp', $compra->id_cmp)->value('estado_compra_cmp'));

        foreach ([$a, $b, $c] as $sede) {
            $this->assertCuadra($producto, $sede);
        }

        $aplicado = Compra::obtenerRepartoAplicado($compra->id_cmp);
        $this->assertSame(4.0, $aplicado['lineas'][0]['partes'][$a]['cantidad']);
        $this->assertSame($a, $aplicado['principal']);
    }

    public function test_una_compra_no_se_recibe_dos_veces(): void
    {
        $this->dejarActivas(2);
        $producto = $this->producto(TipoProducto::IMPLEMENTO_DEPORTIVO, 'Raqueta doble', $this->unidad('U', 'Unidad', 1));
        [$compra, $lote] = $this->compraCon($producto, 5, 5);

        Compra::finalizarCompraDistribuida($compra);

        try {
            Compra::finalizarCompraDistribuida($compra->fresh());
            $this->fail('La segunda recepción debió rechazarse.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('ya fue recibida', $e->getMessage());
        }

        $this->assertEqualsWithDelta(5, (float) DB::table('inventario_sede_lote')->where('lot_id', $lote)->sum('cantidad_isl'), 0.01);
    }

    public function test_siete_kilos_y_medio_entre_dos_sedes_se_reparten_con_decimales(): void
    {
        [$a, $b] = $this->dejarActivas(2);
        $producto = $this->producto(TipoProducto::ALIMENTO, 'Arroz reparto', $this->unidad('Kg', 'Kilo', 1000));
        [$compra, $lote] = $this->compraCon($producto, 7.5, 7500);

        Compra::finalizarCompraDistribuida($compra);

        $this->assertSame([3.75, 3750.0], $this->existencia($lote, $a));
        $this->assertSame([3.75, 3750.0], $this->existencia($lote, $b));
        $this->assertCuadra($producto, $a);
        $this->assertCuadra($producto, $b);

        $partes = app(RepartoCompra::class)->repartir(10, 10000, [$a, $b, 999999], $a, true);
        $this->assertSame(['cantidad' => 3.34, 'convertida' => 3340.0], $partes[$a]);
        $this->assertSame(['cantidad' => 3.33, 'convertida' => 3330.0], $partes[$b]);
        $this->assertEqualsWithDelta(10, array_sum(array_column($partes, 'cantidad')), 0.001);
    }

    public function test_una_sede_no_principal_no_puede_crear_compras(): void
    {
        $this->dejarActivas(2);
        $vista = DB::table('vista')->where('key_vis', 'compras')->value('id_vis');
        $rol = Rol::create(['nombre_rol' => 'Compras ' . Str::random(6), 'slug_rol' => 'compras-' . Str::lower(Str::random(6))]);
        $rol->permisos()->sync(DB::table('permiso')->where('vis_id', $vista)->pluck('id_pmi'));
        $rol->modulos()->sync(DB::table('modulo')->whereIn('key_mod', ['administracion'])->pluck('id_mod')->all());
        VersionPermisos::invalidar();

        $usuario = $this->crearUsuario([], 'Secreta123', [], $this->sedeB());
        $usuario->roles()->sync([$rol->id_rol]);
        $usuario = $usuario->fresh();
        $modulo = DB::table('modulo')->where('key_mod', 'administracion')->value('id_mod');
        $datos = ['proveedor_id' => $this->proveedor(), 'modulo_id' => $modulo, 'fecha' => now()->format('Y-m-d H:i:s')];
        $antes = DB::table('compra')->count();

        $this->actingAs($usuario)->withSession(['ultima_actividad' => time()])
            ->get(route('admin.movimientos.compras.create'))
            ->assertRedirect(route('admin.movimientos.compras.index'))
            ->assertSessionHas('error', fn ($mensaje) => str_contains($mensaje, 'sede principal'));

        $this->actingAs($usuario)->withSession(['ultima_actividad' => time()])
            ->post(route('admin.movimientos.compras.store'), $datos)
            ->assertRedirect(route('admin.movimientos.compras.index'))
            ->assertSessionHas('error');
        $this->assertSame($antes, DB::table('compra')->count());

        $this->actingAs($this->admin)->withSession(['master_key_validated' => true, 'ultima_actividad' => time(), 'sede_activa' => $this->sedeB()])
            ->post(route('admin.movimientos.compras.store'), $datos)
            ->assertSessionHas('error');
        $this->assertSame($antes, DB::table('compra')->count());

        $this->actingAs($this->admin)->withSession(['master_key_validated' => true, 'ultima_actividad' => time(), 'sede_activa' => $this->sedeA()])
            ->post(route('admin.movimientos.compras.store'), $datos)
            ->assertSessionHasNoErrors();
        $this->assertSame($antes + 1, DB::table('compra')->count());
        $this->assertSame($this->sedeA(), (int) DB::table('compra')->orderByDesc('id_cmp')->value('sed_id'));
    }

    public function test_el_formulario_trae_precio_y_unidad_segun_el_tipo(): void
    {
        $this->dejarActivas(2);
        DB::table('tasa_cambio')->insert(['nombre_tca' => 'Oficial', 'fuente_tca' => 'BCV', 'promedio_tca' => 40, 'fecha_vigencia_tca' => now()->addYears(5)->toDateString(), 'created_at' => now(), 'updated_at' => now()]);
        $kilo = $this->unidad('Kg', 'Kilo', 1000);
        $pieza = $this->unidad('U', 'Unidad', 1);
        $envase = (int) DB::table('envase_primario')->orderBy('id_enp')->value('id_enp');

        $alimento = $this->producto(TipoProducto::ALIMENTO, 'Harina form', $kilo, [], [1.00, 2.50]);
        $medicamento = $this->producto(TipoProducto::MEDICAMENTO, 'Acetaminofen form', $pieza, ['enp_id' => $envase, 'unidades_presentacion_pdt' => 10, 'presentacion_dispensacion_pdt' => 'tableta'], [3.00]);
        $implemento = $this->producto(TipoProducto::IMPLEMENTO_DEPORTIVO, 'Balon form', $pieza, [], [15.00]);
        $oficina = $this->producto(TipoProducto::MATERIAL_OFICINA, 'Lapices form', $pieza, ['enp_id' => $envase, 'unidades_presentacion_pdt' => 12], [4.00]);

        $compra = Compra::find(Compra::crearCompra([
            'proveedor_id' => $this->proveedor(), 'fecha' => now(), 'sed_id' => $this->sedeA(),
            'modulo_id' => DB::table('modulo')->where('key_mod', 'deporte')->value('id_mod'),
        ]));

        $casos = [
            [$alimento, UnidadCompra::MODO_PESO, 'kg', 'por kg', 'g', 100.0],
            [$medicamento, UnidadCompra::MODO_ENVASE, null, null, 'tabletas', 120.0],
            [$implemento, UnidadCompra::MODO_UNIDAD, 'unidad', 'por unidad', 'unidades', 600.0],
            [$oficina, UnidadCompra::MODO_ENVASE, null, null, 'unidades', 160.0],
        ];

        $componente = Livewire::actingAs($this->admin)->test(ItemsCompra::class, ['compra' => $compra]);

        foreach ($casos as [$producto, $modo, $unidad, $precioPor, $base, $precioBs]) {
            $componente->set('productoId', $producto)
                ->assertSet('unidad.modo', $modo)
                ->assertSet('unidad.unidad_base', $base)
                ->assertSet('precioCompra', $precioBs);

            if ($unidad !== null) {
                $componente->assertSet('unidad.unidad', $unidad)->assertSet('unidad.precio_por', $precioPor);
            }
        }

        $componente->set('productoId', $alimento)->assertSet('precio.usd', 2.5)->assertSet('unidad.etiqueta', 'Cantidad (kg)');
        $componente->set('productoId', $medicamento)->assertSet('unidad.factor', 10.0);
        $this->assertSame('5 ' . UnidadCompra::de($medicamento)->unidadPlural . ' × 10 tabletas = 50 tabletas', UnidadCompra::de($medicamento)->resumen(5));

        $componente->set('productoId', $medicamento)->set('cantidad', 5)->call('agregarItems')->assertHasNoErrors();
        $componente->set('productoId', $alimento)->set('cantidad', '7.5')->call('agregarItems')->assertHasNoErrors();
        $componente->set('productoId', $implemento)->set('cantidad', '2.5')->call('agregarItems')->assertHasErrors(['cantidad']);

        $med = DB::table('detalle_compra')->where('cmp_id', $compra->id_cmp)->where('pdt_id', $medicamento)->first();
        $this->assertEquals(5, (float) $med->cantidad_dcm);
        $this->assertEquals(50, (float) $med->cantidad_convertida_dcm);
        $this->assertEquals(120, (float) $med->precio_unitario_dcm);
        $this->assertEquals(600, (float) $med->subtotal_dcm);

        $ali = DB::table('detalle_compra')->where('cmp_id', $compra->id_cmp)->where('pdt_id', $alimento)->first();
        $this->assertEquals(7.5, (float) $ali->cantidad_dcm);
        $this->assertEquals(7500, (float) $ali->cantidad_convertida_dcm);
        $this->assertFalse(DB::table('detalle_compra')->where('cmp_id', $compra->id_cmp)->where('pdt_id', $implemento)->exists());
    }
}
