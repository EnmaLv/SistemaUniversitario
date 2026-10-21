<?php

namespace Tests\Feature\Seguridad;

use App\Models\InventarioTotal;
use App\Models\salud\Consulta;
use App\Services\Salud\RecetaService;
use App\Support\Inventario\ServicioInventario;
use Illuminate\Support\Facades\DB;

class FlujoSaludTest extends SeguridadTestCase
{
    private function prepararMedicamento(int $sede): array
    {
        $unidad = DB::table('unidad_medida')->insertGetId(['nombre_unm' => 'Tableta', 'abreviatura_unm' => 'Tab', 'factor_conversion_unm' => 1, 'created_at' => now(), 'updated_at' => now()], 'id_unm');
        $categoria = DB::table('categoria_producto')->insertGetId(['nombre_cap' => 'Analgésicos', 'tpr_id' => RecetaService::TIPO_PRODUCTO_SALUD, 'created_at' => now(), 'updated_at' => now()], 'id_cap');
        $producto = DB::table('producto')->insertGetId([
            'codigo_pdt' => 'MED-' . random_int(1000, 999999), 'nombre_pdt' => 'Acetaminofén de prueba', 'peso_contenido_pdt' => 1,
            'unm_id' => $unidad, 'cap_id' => $categoria, 'created_at' => now(), 'updated_at' => now(),
        ], 'id_pdt');
        $lote = DB::table('lote')->insertGetId([
            'codigo_lote_lot' => 'LM-' . random_int(1000, 999999), 'fecha_entrada_lot' => now()->toDateString(),
            'fecha_vencimiento_lot' => now()->addYear()->toDateString(), 'pdt_id' => $producto, 'created_at' => now(), 'updated_at' => now(),
        ], 'id_lot');

        app(ServicioInventario::class)->entrada($lote, $sede, 20, 20);

        return [$producto, $unidad];
    }

    public function test_consulta_receta_y_dispensacion_descuentan_inventario_de_la_sede(): void
    {
        $sede = $this->sedeA();
        $admin = $this->crearUsuario('Administrador');
        $paciente = $this->crearUsuario([], 'Secreta123', [], $sede)->persona;
        $medico = $this->crearUsuario([], 'Secreta123', [], $sede)->persona;
        [$producto, $unidad] = $this->prepararMedicamento($sede);

        $consultorio = DB::table('consultorio')->insertGetId(['nombre_csr' => 'Consultorio de prueba', 'sed_id' => $sede, 'created_at' => now(), 'updated_at' => now()], 'id_csr');
        $enfermedad = DB::table('enfermedad')->insertGetId(['codigo_efm' => 'PRB' . random_int(100, 99999), 'nombre_efm' => 'Cefalea de prueba', 'categoria_efm' => 'fisica', 'created_at' => now(), 'updated_at' => now()], 'id_efm');

        $this->actingAs($admin)->withSession(['master_key_validated' => true, 'ultima_actividad' => time(), 'sede_activa' => $sede]);

        $this->post(route('admin.salud.movimientos.consultas.store'), [
            'per_id'         => $paciente->id_per,
            'medico_id'      => $medico->id_per,
            'consultorio_id' => $consultorio,
            'fecha'          => now()->toDateString(),
            'motivo'         => 'Dolor de cabeza',
            'diagnostico'    => 'Cefalea tensional',
            'enfermedades'   => [$enfermedad],
        ])->assertSessionHasNoErrors();

        $consulta = Consulta::where('paciente_per_id', $paciente->id_per)->firstOrFail();
        $this->assertSame([$enfermedad], $consulta->enfermedades->pluck('id_efm')->all());

        $this->post(route('admin.salud.movimientos.consultas.receta.store', $consulta), [
            'fecha'       => now()->toDateString(),
            'vigencia'    => now()->addWeek()->toDateString(),
            'descripcion' => 'Tratamiento',
            'detalles'    => [[
                'producto_id' => $producto, 'cantidad' => 6, 'unidad_id' => $unidad, 'frecuencia' => 'Cada 8 horas',
            ]],
        ])->assertSessionHasNoErrors();

        $detalle = $consulta->fresh()->receta->detalles->first();
        $this->assertEquals(6, (float) $detalle->cantidad_prescrita_drm);

        $this->post(route('admin.salud.movimientos.consultas.dispensacion.store', $consulta), [
            'items' => [$detalle->id_drm => ['dispensar' => '1', 'cantidad' => 6]],
        ])->assertSessionHasNoErrors();

        $this->assertSame(1, DB::table('dispensacion_medicamento')->where('drm_id', $detalle->id_drm)->where('sed_id', $sede)->count());
        $this->assertEquals(14, (float) InventarioTotal::todasLasSedes()->where('pdt_id', $producto)->where('sed_id', $sede)->value('cantidad_total_int'));
        $this->assertSame(Consulta::RECETA_CERRADA, (int) $consulta->fresh()->receta->estado_rme);
    }
}
