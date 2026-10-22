<?php

namespace Tests\Feature\Seguridad;

use App\Livewire\Admin\Movimientos\Compras\ItemsCompra;
use App\Models\Compra;
use App\Models\TipoProducto;
use App\Models\Usuario;
use App\Models\salud\EnvasePrimario;
use App\Support\Inventario\RepartoCompra;
use App\Support\Inventario\UnidadCompra;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;

class PresentacionesAreaTest extends SeguridadTestCase
{
    private Usuario $admin;

    private string $sufijo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sufijo = Str::upper(Str::random(6));
        $this->admin = $this->crearUsuario('Administrador');
        $this->actingAs($this->admin)->withSession(['master_key_validated' => true, 'ultima_actividad' => time(), 'sede_activa' => $this->sedeA()]);
    }

    private function nombres(string $tipo): array
    {
        return EnvasePrimario::deTipos([TipoProducto::idPorNombre($tipo)])->pluck('nombre_enp')->map(fn ($n) => mb_strtoupper($n))->all();
    }

    private function envase(string $nombre): int
    {
        return (int) DB::table('envase_primario')->whereNull('deleted_at')->where(DB::raw('LOWER(nombre_enp)'), mb_strtolower($nombre))->value('id_enp');
    }

    private function categoria(string $tipo): int
    {
        return DB::table('categoria_producto')->insertGetId([
            'nombre_cap' => 'PRS ' . $tipo . ' ' . $this->sufijo,
            'tpr_id'     => TipoProducto::idPorNombre($tipo),
            'status_id'  => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ], 'id_cap');
    }

    private function datos(string $nombre, array $extra = []): array
    {
        return array_merge([
            'nombre'           => $nombre . ' ' . $this->sufijo,
            'categoria_id'     => $this->categoria(TipoProducto::IMPLEMENTO_DEPORTIVO),
            'costo_usd_ppr'    => 4,
            'stock_minimo_pdt' => 1,
            'stock_maximo_pdt' => 100,
        ], $extra);
    }

    private function producto(string $nombre)
    {
        return DB::table('producto')->where('nombre_pdt', mb_strtoupper($nombre . ' ' . $this->sufijo))->first();
    }

    private function unidadArea(): int
    {
        return (int) TipoProducto::idUnidadDelArea();
    }

    private function dejarDosSedes(): array
    {
        $ids = [$this->sedeA(), $this->sedeB()];
        DB::table('sede')->update(['es_principal_sed' => false]);
        DB::table('sede')->where('id_sed', $this->sedeA())->update(['es_principal_sed' => true]);
        DB::table('sede')->whereNotIn('id_sed', $ids)->update(['status_id' => 2]);
        DB::table('sede')->whereIn('id_sed', $ids)->update(['status_id' => 1]);

        return $ids;
    }

    public function test_cada_tipo_tiene_sus_presentaciones_sembradas(): void
    {
        $implementos = $this->nombres(TipoProducto::IMPLEMENTO_DEPORTIVO);
        $oficina = $this->nombres(TipoProducto::MATERIAL_OFICINA);
        $medicamentos = $this->nombres(TipoProducto::MEDICAMENTO);

        foreach (['CAJA', 'PAQUETE', 'BOLSA', 'DOCENA'] as $nombre) {
            $this->assertContains($nombre, $implementos);
            $this->assertContains($nombre, $oficina);
        }

        $this->assertContains('RESMA', $oficina);

        foreach (['BLISTER', 'AMPOLLA', 'FRASCO'] as $nombre) {
            $this->assertContains($nombre, $medicamentos);
            $this->assertNotContains($nombre, $implementos);
            $this->assertNotContains($nombre, $oficina);
        }

        foreach (['CAJA', 'DOCENA', 'RESMA', 'PAQUETE'] as $nombre) {
            $this->assertNotContains($nombre, $medicamentos);
        }
    }

    public function test_cada_formulario_muestra_solo_sus_presentaciones(): void
    {
        $area = $this->get(route('admin.deportes.implementos.create'))->assertOk();
        $nombresArea = collect($area->viewData('envases'))->pluck('nombre_enp')->map(fn ($n) => mb_strtoupper($n));
        $this->assertTrue($nombresArea->contains('CAJA'));
        $this->assertFalse($nombresArea->contains('BLISTER'));
        $this->assertStringNotContainsString('name="unidad_id"', $area->getContent());

        $salud = $this->get(route('admin.salud.maestros.medicamentos.create'))->assertOk();
        $nombresSalud = collect($salud->viewData('envases'))->pluck('nombre_enp')->map(fn ($n) => mb_strtoupper($n));
        $this->assertTrue($nombresSalud->contains('BLISTER'));
        $this->assertFalse($nombresSalud->contains('DOCENA'));
        $this->assertFalse($nombresSalud->contains('CAJA'));
    }

    public function test_la_unidad_del_area_siempre_es_unidad(): void
    {
        $kilo = (int) DB::table('unidad_medida')->where('nombre_unm', 'Kilo')->value('id_unm');

        $this->post(route('admin.deportes.implementos.store'), $this->datos('PRS BALON', ['unidad_id' => $kilo]))
            ->assertSessionHasNoErrors();

        $balon = $this->producto('PRS BALON');
        $this->assertNotNull($balon);
        $this->assertSame($this->unidadArea(), (int) $balon->unm_id);
        $this->assertNull($balon->enp_id);
        $this->assertEquals(1, (float) $balon->unidades_presentacion_pdt);

        $this->put(route('admin.deportes.implementos.update', $balon->id_pdt), $this->datos('PRS BALON', ['unidad_id' => $kilo]))
            ->assertSessionHasNoErrors();
        $this->assertSame($this->unidadArea(), (int) DB::table('producto')->where('id_pdt', $balon->id_pdt)->value('unm_id'));
    }

    public function test_el_backend_rechaza_presentaciones_de_otro_tipo(): void
    {
        $this->post(route('admin.deportes.implementos.store'), $this->datos('PRS PETO MAL', ['presentacion_id' => $this->envase('BLISTER'), 'unidades_presentacion_pdt' => 12]))
            ->assertSessionHasErrors('presentacion_id');
        $this->assertNull($this->producto('PRS PETO MAL'));

        $this->post(route('admin.deportes.implementos.store'), $this->datos('PRS PETO UNO', ['presentacion_id' => $this->envase('CAJA'), 'unidades_presentacion_pdt' => 1]))
            ->assertSessionHasErrors('unidades_presentacion_pdt');

        $this->post(route('admin.deportes.implementos.store'), $this->datos('PRS PETO', ['presentacion_id' => $this->envase('DOCENA'), 'unidades_presentacion_pdt' => 12]))
            ->assertSessionHasNoErrors();
        $peto = $this->producto('PRS PETO');
        $this->assertSame($this->envase('DOCENA'), (int) $peto->enp_id);

        $this->put(route('admin.deportes.implementos.update', $peto->id_pdt), $this->datos('PRS PETO', ['presentacion_id' => $this->envase('AMPOLLA'), 'unidades_presentacion_pdt' => 12]))
            ->assertSessionHasErrors('presentacion_id');
        $this->assertSame($this->envase('DOCENA'), (int) DB::table('producto')->where('id_pdt', $peto->id_pdt)->value('enp_id'));

        $this->post(route('admin.salud.maestros.medicamentos.store'), [
            'nombre'                    => 'PRS JARABE ' . $this->sufijo,
            'categoria_id'              => $this->categoria(TipoProducto::MEDICAMENTO),
            'unidad_id'                 => $this->unidadArea(),
            'presentacion_id'           => $this->envase('DOCENA'),
            'unidades_presentacion_pdt' => 12,
            'costo_usd_ppr'             => 2,
            'stock_minimo_pdt'          => 1,
            'stock_maximo_pdt'          => 10,
        ])->assertSessionHasErrors('presentacion_id');
        $this->assertNull($this->producto('PRS JARABE'));
    }

    public function test_tres_cajas_de_doce_entran_como_36_unidades_repartidas_en_enteros(): void
    {
        [$a, $b] = $this->dejarDosSedes();

        $this->post(route('admin.maestros.material_oficina.store'), $this->datos('PRS LAPIZ', [
            'categoria_id'              => $this->categoria(TipoProducto::MATERIAL_OFICINA),
            'presentacion_id'           => $this->envase('CAJA'),
            'unidades_presentacion_pdt' => 12,
        ]))->assertSessionHasNoErrors();
        $lapiz = (int) $this->producto('PRS LAPIZ')->id_pdt;

        $unidad = UnidadCompra::de($lapiz);
        $this->assertSame(UnidadCompra::MODO_ENVASE, $unidad->modo);
        $this->assertTrue($unidad->inventarioEnUnidades());
        $this->assertSame('3 cajas × 12 unidades = 36 unidades', $unidad->resumen(3));

        $proveedor = DB::table('proveedor')->insertGetId([
            'empresa_prv' => 'PROVEEDOR PRESENTACIONES', 'telefono_prv' => '0424', 'email_prv' => 'prs' . random_int(1, 99999) . '@x.test',
            'created_at' => now(), 'updated_at' => now(),
        ], 'id_prv');
        $compra = Compra::find(Compra::crearCompra([
            'proveedor_id' => $proveedor, 'fecha' => now(), 'sed_id' => $a,
            'modulo_id' => DB::table('modulo')->where('key_mod', 'administracion')->value('id_mod'),
        ]));

        Livewire::actingAs($this->admin)->test(ItemsCompra::class, ['compra' => $compra])
            ->set('productoId', $lapiz)
            ->set('cantidad', '2.5')
            ->call('agregarItems')
            ->assertHasErrors(['cantidad'])
            ->set('productoId', $lapiz)
            ->set('cantidad', 3)
            ->set('precioCompra', 10)
            ->call('agregarItems')
            ->assertHasNoErrors();

        $detalle = DB::table('detalle_compra')->where('cmp_id', $compra->id_cmp)->where('pdt_id', $lapiz)->first();
        $this->assertEquals(3, (float) $detalle->cantidad_dcm);
        $this->assertEquals(36, (float) $detalle->cantidad_convertida_dcm);

        $vistaPrevia = app(RepartoCompra::class)->calcular($compra->id_cmp);
        $this->assertSame(['cantidad' => 18.0, 'convertida' => 18.0], $vistaPrevia['lineas'][0]['partes'][$a]);
        $this->assertSame(['cantidad' => 18.0, 'convertida' => 18.0], $vistaPrevia['lineas'][0]['partes'][$b]);
        $this->assertSame('unidades', $vistaPrevia['lineas'][0]['unidad_plural']);

        Compra::finalizarCompraDistribuida($compra->fresh());

        foreach ([$a, $b] as $sede) {
            $fila = DB::table('inventario_sede_lote')->where('lot_id', $detalle->lot_id)->where('sed_id', $sede)->first();
            $this->assertEquals(18, (float) $fila->cantidad_isl);
            $this->assertEquals(18, (float) $fila->cantidad_convertida_isl);
        }

        $this->assertEquals(36, (float) DB::table('inventario_total')->where('pdt_id', $lapiz)->sum('cantidad_convertida_total_int'));
        $this->assertEquals(36, (float) DB::table('inventario_total')->where('pdt_id', $lapiz)->sum('cantidad_total_int'));
    }

    public function test_una_caja_impar_se_reparte_en_unidades_enteras(): void
    {
        [$a, $b] = $this->dejarDosSedes();

        $partes = app(RepartoCompra::class)->repartir(7, 7, [$a, $b], $a, false);

        $this->assertSame(['cantidad' => 3.0, 'convertida' => 3.0], $partes[$b]);
        $this->assertSame(['cantidad' => 4.0, 'convertida' => 4.0], $partes[$a]);
    }
}
