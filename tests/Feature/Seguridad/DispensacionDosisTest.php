<?php

namespace Tests\Feature\Seguridad;

use App\Models\Usuario;
use App\Models\salud\Consulta;
use App\Models\salud\UnidadDosis;
use App\Services\Salud\RecetaService;
use App\Support\Inventario\ServicioInventario;
use Illuminate\Support\Facades\DB;

class DispensacionDosisTest extends SeguridadTestCase
{
    private Usuario $admin;

    private int $sede;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sede = $this->sedeA();
        $this->admin = $this->crearUsuario('Administrador');

        foreach ([['Tableta', 'tab'], ['Miligramo', 'mg'], ['Cucharada', 'cda']] as [$nombre, $abreviatura]) {
            UnidadDosis::firstOrCreate(['nombre_udo' => $nombre], ['abreviatura_udo' => $abreviatura]);
        }

        $this->actingAs($this->admin)->withSession(['master_key_validated' => true, 'ultima_actividad' => time(), 'sede_activa' => $this->sede]);
    }

    private function unidadMedida(string $nombre, string $abreviatura, float $factor): int
    {
        return (int) (DB::table('unidad_medida')->where('abreviatura_unm', $abreviatura)->value('id_unm')
            ?? DB::table('unidad_medida')->insertGetId([
                'nombre_unm' => $nombre, 'abreviatura_unm' => $abreviatura, 'factor_conversion_unm' => $factor,
                'created_at' => now(), 'updated_at' => now(),
            ], 'id_unm'));
    }

    private function medicamento(int $unidad, float $envases, float $porEnvase, float $unidadesPresentacion = 1): int
    {
        $categoria = DB::table('categoria_producto')->insertGetId(['nombre_cap' => 'Categoría dosis ' . random_int(1, 99999), 'tpr_id' => RecetaService::TIPO_PRODUCTO_SALUD, 'created_at' => now(), 'updated_at' => now()], 'id_cap');
        $producto = DB::table('producto')->insertGetId([
            'codigo_pdt' => 'DOS-' . random_int(1000, 999999), 'nombre_pdt' => 'Medicamento dosis ' . random_int(1000, 999999),
            'peso_contenido_pdt' => $porEnvase, 'unidades_presentacion_pdt' => $unidadesPresentacion,
            'unm_id' => $unidad, 'cap_id' => $categoria, 'created_at' => now(), 'updated_at' => now(),
        ], 'id_pdt');
        $lote = DB::table('lote')->insertGetId([
            'codigo_lote_lot' => 'LD-' . random_int(1000, 999999), 'fecha_entrada_lot' => now()->toDateString(),
            'fecha_vencimiento_lot' => now()->addYear()->toDateString(), 'pdt_id' => $producto, 'created_at' => now(), 'updated_at' => now(),
        ], 'id_lot');

        app(ServicioInventario::class)->entrada($lote, $this->sede, $envases, $envases * $porEnvase);

        return $producto;
    }

    private function recetar(int $producto, float $cantidad, string $unidadDosis): Consulta
    {
        $consultorio = DB::table('consultorio')->insertGetId(['nombre_csr' => 'Consultorio dosis', 'sed_id' => $this->sede, 'created_at' => now(), 'updated_at' => now()], 'id_csr');
        $consulta = Consulta::todasLasSedes()->create([
            'paciente_per_id' => $this->crearUsuario([], 'Secreta123', [], $this->sede)->persona->id_per,
            'medico_per_id'   => $this->crearUsuario([], 'Secreta123', [], $this->sede)->persona->id_per,
            'csr_id' => $consultorio, 'use_id' => $this->admin->id_use, 'fecha_cme' => now()->toDateString(),
            'motivo_cme' => 'Dolor', 'observaciones_cme' => '', 'diagnostico_cme' => 'Dolor leve',
        ]);

        $this->post(route('admin.salud.movimientos.consultas.receta.store', $consulta), [
            'fecha'       => now()->toDateString(),
            'descripcion' => 'Tratamiento',
            'detalles'    => [[
                'producto_id' => $producto, 'cantidad' => $cantidad, 'unidad_dosis' => $unidadDosis,
                'frecuencia' => 'Cada 8 horas', 'observaciones' => 'Tomar con agua',
            ]],
        ])->assertSessionHasNoErrors();

        return $consulta->fresh();
    }

    private function assertSaldoCuadra(int $producto): void
    {
        $existencias = DB::table('inventario_sede_lote')
            ->join('lote', 'lote.id_lot', '=', 'inventario_sede_lote.lot_id')
            ->where('lote.pdt_id', $producto)
            ->where('inventario_sede_lote.sed_id', $this->sede)
            ->selectRaw('COALESCE(SUM(inventario_sede_lote.cantidad_isl), 0) AS cantidad, COALESCE(SUM(inventario_sede_lote.cantidad_convertida_isl), 0) AS convertida')
            ->first();
        $total = DB::table('inventario_total')->where('pdt_id', $producto)->where('sed_id', $this->sede)->first();

        $this->assertEqualsWithDelta((float) $existencias->cantidad, (float) $total->cantidad_total_int, 0.001);
        $this->assertEqualsWithDelta((float) $existencias->convertida, (float) $total->cantidad_convertida_total_int, 0.001);
    }

    public function test_tabletas_se_convierten_a_unidades_y_descuentan_lo_correcto(): void
    {
        $producto = $this->medicamento($this->unidadMedida('Unidad', 'U', 1), 5, 10, 10);
        $consulta = $this->recetar($producto, 6, 'Tableta');
        $detalle = $consulta->receta->detalles->first();

        $this->assertSame('Tableta', $detalle->unidad_prescrita_drm);

        $this->post(route('admin.salud.movimientos.consultas.dispensacion.store', $consulta), [
            'items' => [$detalle->id_drm => ['dispensar' => '1', 'cantidad' => 6]],
        ])->assertSessionHasNoErrors();

        $this->assertEquals(6, (float) DB::table('dispensacion_medicamento')->where('drm_id', $detalle->id_drm)->sum('cantidad_dsm'));
        $this->assertEquals(6, (float) $detalle->fresh()->cantidad_drm);
        $this->assertEquals(44, (float) DB::table('inventario_total')->where('pdt_id', $producto)->where('sed_id', $this->sede)->value('cantidad_convertida_total_int'));
        $this->assertEquals(4.4, (float) DB::table('inventario_total')->where('pdt_id', $producto)->where('sed_id', $this->sede)->value('cantidad_total_int'));
        $this->assertSame(Consulta::RECETA_CERRADA, (int) $consulta->receta->fresh()->estado_rme);
        $this->assertSaldoCuadra($producto);

        $this->get(route('admin.salud.movimientos.consultas.recipe_pdf', $consulta))->assertOk();
    }

    public function test_miligramos_se_convierten_a_gramos_del_producto(): void
    {
        $this->unidadMedida('Miligramo', 'mg', 0.001);
        $producto = $this->medicamento($this->unidadMedida('Gramo', 'g', 1), 10, 10);
        $consulta = $this->recetar($producto, 2000, 'Miligramo');
        $detalle = $consulta->receta->detalles->first();

        $this->post(route('admin.salud.movimientos.consultas.dispensacion.store', $consulta), [
            'items' => [$detalle->id_drm => ['dispensar' => '1', 'cantidad' => 2000, 'cantidad_inventario' => 999]],
        ])->assertSessionHasNoErrors();

        $this->assertEquals(2, (float) DB::table('dispensacion_medicamento')->where('drm_id', $detalle->id_drm)->sum('cantidad_dsm'));
        $this->assertEquals(98, (float) DB::table('inventario_total')->where('pdt_id', $producto)->where('sed_id', $this->sede)->value('cantidad_convertida_total_int'));
        $this->assertEquals(9.8, (float) DB::table('inventario_total')->where('pdt_id', $producto)->where('sed_id', $this->sede)->value('cantidad_total_int'));
        $this->assertSaldoCuadra($producto);
    }

    public function test_unidad_no_convertible_exige_la_cantidad_a_descontar(): void
    {
        $producto = $this->medicamento($this->unidadMedida('Mililitro', 'Ml', 1), 3, 120);
        $consulta = $this->recetar($producto, 3, 'Cucharada');
        $detalle = $consulta->receta->detalles->first();

        $this->post(route('admin.salud.movimientos.consultas.dispensacion.store', $consulta), [
            'items' => [$detalle->id_drm => ['dispensar' => '1', 'cantidad' => 3]],
        ])->assertSessionHasErrors("items.{$detalle->id_drm}.cantidad_inventario");

        $this->assertSame(0, DB::table('dispensacion_medicamento')->where('drm_id', $detalle->id_drm)->count());
        $this->assertEquals(360, (float) DB::table('inventario_total')->where('pdt_id', $producto)->where('sed_id', $this->sede)->value('cantidad_convertida_total_int'));

        $this->post(route('admin.salud.movimientos.consultas.dispensacion.store', $consulta), [
            'items' => [$detalle->id_drm => ['dispensar' => '1', 'cantidad' => 3, 'cantidad_inventario' => 120]],
        ])->assertSessionHasNoErrors();

        $this->assertEquals(120, (float) DB::table('dispensacion_medicamento')->where('drm_id', $detalle->id_drm)->sum('cantidad_dsm'));
        $this->assertEquals(3, (float) $detalle->fresh()->cantidad_drm);
        $this->assertEquals(240, (float) DB::table('inventario_total')->where('pdt_id', $producto)->where('sed_id', $this->sede)->value('cantidad_convertida_total_int'));
        $this->assertEquals(2, (float) DB::table('inventario_total')->where('pdt_id', $producto)->where('sed_id', $this->sede)->value('cantidad_total_int'));
        $this->assertSaldoCuadra($producto);
    }

    public function test_receta_rechaza_unidad_de_dosis_fuera_del_catalogo(): void
    {
        $producto = $this->medicamento($this->unidadMedida('Unidad', 'U', 1), 1, 10, 10);
        $consultorio = DB::table('consultorio')->insertGetId(['nombre_csr' => 'Consultorio dosis', 'sed_id' => $this->sede, 'created_at' => now(), 'updated_at' => now()], 'id_csr');
        $consulta = Consulta::todasLasSedes()->create([
            'paciente_per_id' => $this->crearUsuario([], 'Secreta123', [], $this->sede)->persona->id_per,
            'medico_per_id'   => $this->crearUsuario([], 'Secreta123', [], $this->sede)->persona->id_per,
            'csr_id' => $consultorio, 'use_id' => $this->admin->id_use, 'fecha_cme' => now()->toDateString(),
            'motivo_cme' => 'Dolor', 'observaciones_cme' => '', 'diagnostico_cme' => 'Dolor leve',
        ]);

        $this->post(route('admin.salud.movimientos.consultas.receta.store', $consulta), [
            'fecha'    => now()->toDateString(),
            'detalles' => [['producto_id' => $producto, 'cantidad' => 1, 'unidad_dosis' => 'Barril', 'frecuencia' => 'Diaria']],
        ])->assertSessionHasErrors('detalles.0.unidad_dosis');
    }
}
