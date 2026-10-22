<?php

namespace Tests\Feature\Seguridad;

use App\Services\Salud\RecetaService;
use App\Support\Inventario\ServicioInventario;
use Illuminate\Support\Facades\DB;

class MedicamentosSoloLecturaTest extends SeguridadTestCase
{
    public function test_doctor_ve_el_stock_de_su_sede_sin_botones_de_edicion(): void
    {
        $sede = $this->sedeA();
        $doctor = $this->crearUsuario('Doctor(a)', 'Secreta123', [], $sede);

        $this->assertTrue($doctor->puede('medicamentos', 'ver'));
        $this->assertFalse($doctor->puede('medicamentos', 'crear'));
        $this->assertFalse($doctor->puede('medicamentos', 'editar'));

        $unidad = DB::table('unidad_medida')->where('abreviatura_unm', 'U')->value('id_unm')
            ?? DB::table('unidad_medida')->insertGetId(['nombre_unm' => 'Unidad', 'abreviatura_unm' => 'U', 'factor_conversion_unm' => 1, 'created_at' => now(), 'updated_at' => now()], 'id_unm');
        $categoria = DB::table('categoria_producto')->insertGetId(['nombre_cap' => 'Analgésicos stock', 'tpr_id' => RecetaService::TIPO_PRODUCTO_SALUD, 'created_at' => now(), 'updated_at' => now()], 'id_cap');
        $nombre = 'STOCK VISIBLE ' . random_int(1000, 999999);
        $producto = DB::table('producto')->insertGetId([
            'codigo_pdt' => 'STK-' . random_int(1000, 999999), 'nombre_pdt' => $nombre, 'peso_contenido_pdt' => 1,
            'stock_minimo_pdt' => 25, 'stock_maximo_pdt' => 100,
            'unm_id' => $unidad, 'cap_id' => $categoria, 'created_at' => now(), 'updated_at' => now(),
        ], 'id_pdt');
        $lote = DB::table('lote')->insertGetId([
            'codigo_lote_lot' => 'LS-' . random_int(1000, 999999), 'fecha_entrada_lot' => now()->toDateString(),
            'fecha_vencimiento_lot' => now()->addYear()->toDateString(), 'pdt_id' => $producto, 'created_at' => now(), 'updated_at' => now(),
        ], 'id_lot');
        app(ServicioInventario::class)->entrada($lote, $sede, 20, 20);
        app(ServicioInventario::class)->entrada($lote, $this->sedeB(), 70, 70);

        $this->actingAs($doctor)->withSession(['ultima_actividad' => time()]);

        $html = $this->get(route('admin.salud.maestros.medicamentos.index', ['buscar' => $nombre]))
            ->assertOk()
            ->assertSee($nombre)
            ->assertSee('Bajo el mínimo')
            ->getContent();

        $this->assertStringContainsString('data-stock="20"', $html);
        $this->assertStringNotContainsString('data-stock="90"', $html);
        $this->assertStringNotContainsString('Nuevo Medicamento', $html);
        $this->assertStringNotContainsString('medicamentos/create', $html);
        $this->assertStringNotContainsString('medicamentos/' . $producto . '/edit', $html);
        $this->assertStringNotContainsString('form-toggle-' . $producto, $html);

        $this->get(route('admin.salud.maestros.medicamentos.create'))->assertForbidden();
        $this->get(route('admin.salud.maestros.medicamentos.edit', $producto))->assertForbidden();
        $this->post(route('admin.salud.maestros.medicamentos.store'), ['nombre' => 'NO PERMITIDO'])->assertForbidden();
    }
}
