<?php

namespace Tests\Feature\Seguridad;

use App\Models\Deporte\PrestamoDeportivo;
use App\Models\Deporte\SancionDeportiva;
use App\Models\Persona;
use App\Models\TipoProducto;
use App\Models\Usuario;
use App\Services\Deporte\PrestamoService;
use App\Support\Inventario\ServicioInventario;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class DeportesPrestamosTest extends SeguridadTestCase
{
    private Usuario $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->crearUsuario('Administrador');
        $this->actingAs($this->admin);
        $this->prepararSesion();
    }

    private function prepararSesion(): void
    {
        $this->withSession([
            'master_key_validated' => true,
            'ultima_actividad'     => now()->getTimestamp(),
            'sede_activa'          => $this->sedeA(),
        ]);
    }

    private function tipo(string $nombre): int
    {
        $existente = DB::table('tipo_producto')->whereRaw('LOWER(nombre_tpr) = ?', [mb_strtolower($nombre)])->value('id_tpr');

        if ($existente) {
            return (int) $existente;
        }

        return DB::table('tipo_producto')->insertGetId([
            'nombre_tpr' => $nombre,
            'mod_id'     => PrestamoService::moduloDeporteId(),
            'created_at' => now(),
            'updated_at' => now(),
        ], 'id_tpr');
    }

    private function producto(string $tipo, string $nombre): int
    {
        $categoria = DB::table('categoria_producto')->insertGetId([
            'nombre_cap' => 'Cat ' . $nombre . ' ' . random_int(1, 999999),
            'tpr_id'     => $this->tipo($tipo),
            'created_at' => now(),
            'updated_at' => now(),
        ], 'id_cap');

        return DB::table('producto')->insertGetId([
            'codigo_pdt'                => 'DEP-' . random_int(1000, 999999),
            'nombre_pdt'                => $nombre,
            'peso_contenido_pdt'        => 1,
            'unidades_presentacion_pdt' => 1,
            'unm_id'                    => DB::table('unidad_medida')->orderBy('id_unm')->value('id_unm'),
            'cap_id'                    => $categoria,
            'created_at'                => now(),
            'updated_at'                => now(),
        ], 'id_pdt');
    }

    private function loteConExistencia(int $producto, float $cantidad, int $sede): int
    {
        $lote = DB::table('lote')->insertGetId([
            'codigo_lote_lot'   => 'LD-' . random_int(1000, 999999),
            'fecha_entrada_lot' => now()->toDateString(),
            'pdt_id'            => $producto,
            'created_at'        => now(),
            'updated_at'        => now(),
        ], 'id_lot');

        app(ServicioInventario::class)->entrada($lote, $sede, $cantidad);

        return $lote;
    }

    private function persona(): Persona
    {
        return Persona::create([
            'nombres_per'   => 'Atleta',
            'apellidos_per' => 'Pruebas',
            'cedula_per'    => (string) random_int(10000000, 99999999),
            'telefono_per'  => '0424-0000000',
            'genero_per'    => 'Otro',
            'correo_per'    => 'atleta' . random_int(1, 999999) . '@prueba.test',
            'id_perfil'     => DB::table('perfil')->orderBy('id_perfil')->value('id_perfil'),
            'sed_id'        => $this->sedeA(),
        ]);
    }

    private function datosPrestamo(Persona|string $persona, int $lote, array $extra = []): array
    {
        return array_merge([
            'cedula'                    => $persona instanceof Persona ? $persona->cedula_per : $persona,
            'lot_id'                    => $lote,
            'cantidad'                  => 1,
            'motivo'                    => 'Práctica de voleibol',
            'lugar'                     => 'Patio central',
            'fecha_devolucion_esperada' => now()->addDay()->format('Y-m-d H:i:s'),
        ], $extra);
    }

    private function prestar(Persona $persona, int $lote): PrestamoDeportivo
    {
        $this->post(route('admin.deportes.prestamos.store'), $this->datosPrestamo($persona, $lote))
            ->assertSessionHasNoErrors()
            ->assertSessionMissing('error');

        return PrestamoDeportivo::todasLasSedes()->where('per_id', $persona->id_per)->latest('id_prd')->firstOrFail();
    }

    private function existencia(int $lote, int $sede): float
    {
        return (float) DB::table('inventario_sede_lote')->where('lot_id', $lote)->where('sed_id', $sede)->value('cantidad_isl');
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

    private function vencerPrestamos(): void
    {
        $this->travel(3)->days();
        $this->prepararSesion();
        Artisan::call('deportes:marcar-vencidos');
    }

    public function test_prestamo_a_cedula_inexistente_es_rechazado(): void
    {
        $producto = $this->producto(TipoProducto::IMPLEMENTO_DEPORTIVO, 'Balón inexistente');
        $lote = $this->loteConExistencia($producto, 3, $this->sedeA());

        do {
            $cedula = (string) random_int(10000000, 99999999);
        } while (DB::table('persona')->where('cedula_per', $cedula)->exists());

        $this->post(route('admin.deportes.prestamos.store'), $this->datosPrestamo($cedula, $lote))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame(0, DB::table('prestamo_deportivo')->where('lot_id', $lote)->count());
        $this->assertEqualsWithDelta(3, $this->existencia($lote, $this->sedeA()), 0.01);
    }

    public function test_segundo_prestamo_con_uno_activo_es_rechazado(): void
    {
        $producto = $this->producto(TipoProducto::IMPLEMENTO_DEPORTIVO, 'Balón doble');
        $lote = $this->loteConExistencia($producto, 5, $this->sedeA());
        $persona = $this->persona();

        $this->prestar($persona, $lote);

        $this->post(route('admin.deportes.prestamos.store'), $this->datosPrestamo($persona, $lote))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame(1, DB::table('prestamo_deportivo')->where('per_id', $persona->id_per)->count());
        $this->assertEqualsWithDelta(4, $this->existencia($lote, $this->sedeA()), 0.01);
    }

    public function test_prestamo_vencido_genera_sancion_y_bloquea_nuevos_prestamos(): void
    {
        $producto = $this->producto(TipoProducto::IMPLEMENTO_DEPORTIVO, 'Balón vencido');
        $lote = $this->loteConExistencia($producto, 5, $this->sedeA());
        $persona = $this->persona();
        $prestamo = $this->prestar($persona, $lote);

        $this->vencerPrestamos();

        $prestamo->refresh();
        $this->assertSame(PrestamoDeportivo::ESTADO_VENCIDO, $prestamo->estado_prd);

        $sancion = SancionDeportiva::todasLasSedes()->where('prd_id', $prestamo->id_prd)->firstOrFail();
        $this->assertSame(SancionDeportiva::ESTADO_ACTIVA, $sancion->estado_snd);
        $this->assertNull($sancion->aplicada_por_use_id);
        $this->assertSame($persona->id_per, (int) $sancion->per_id);

        $this->post(route('admin.deportes.prestamos.store'), $this->datosPrestamo($persona, $lote))
            ->assertRedirect()
            ->assertSessionHas('error', fn ($mensaje) => str_contains($mensaje, 'sanción activa'));

        $this->assertSame(1, DB::table('prestamo_deportivo')->where('per_id', $persona->id_per)->count());
        $this->assertTrue(DB::table('bitacora_sistema')->where('tipo_evento_bis', 'prestamo_vencido')->where('registro_id_bis', $sancion->id_snd)->exists());
    }

    public function test_devolucion_en_buen_estado_levanta_la_sancion(): void
    {
        $producto = $this->producto(TipoProducto::IMPLEMENTO_DEPORTIVO, 'Balón devuelto tarde');
        $lote = $this->loteConExistencia($producto, 2, $this->sedeA());
        $persona = $this->persona();
        $prestamo = $this->prestar($persona, $lote);

        $this->vencerPrestamos();

        $this->put(route('admin.deportes.prestamos.update_devolucion', $prestamo->id_prd), ['estado_devolucion' => 'bueno'])
            ->assertRedirect(route('admin.deportes.prestamos.show', $prestamo->id_prd))
            ->assertSessionMissing('error');

        $prestamo->refresh();
        $this->assertSame(PrestamoDeportivo::ESTADO_DEVUELTO, $prestamo->estado_prd);
        $this->assertSame('bueno', $prestamo->estado_devolucion_prd);
        $this->assertSame($this->admin->id_use, (int) $prestamo->recibido_por_use_id);
        $this->assertNotNull($prestamo->fecha_devolucion_real_prd);

        $sancion = SancionDeportiva::todasLasSedes()->where('prd_id', $prestamo->id_prd)->firstOrFail();
        $this->assertSame(SancionDeportiva::ESTADO_LEVANTADA, $sancion->estado_snd);
        $this->assertSame(SancionDeportiva::FORMA_DEVOLUCION, $sancion->forma_levantamiento_snd);
        $this->assertSame($this->admin->id_use, (int) $sancion->levantada_por_use_id);
        $this->assertNotNull($sancion->fecha_levantamiento_snd);

        $this->assertEqualsWithDelta(2, $this->existencia($lote, $this->sedeA()), 0.01);
        $this->assertCuadra($producto, $this->sedeA());

        $this->post(route('admin.deportes.prestamos.store'), $this->datosPrestamo($persona, $lote))->assertSessionMissing('error');
        $this->assertSame(2, DB::table('prestamo_deportivo')->where('per_id', $persona->id_per)->count());
    }

    public function test_reposicion_levanta_la_sancion_y_entra_al_inventario(): void
    {
        $producto = $this->producto(TipoProducto::IMPLEMENTO_DEPORTIVO, 'Raqueta perdida');
        $lote = $this->loteConExistencia($producto, 3, $this->sedeA());
        $persona = $this->persona();
        $prestamo = $this->prestar($persona, $lote);

        $this->put(route('admin.deportes.prestamos.update_devolucion', $prestamo->id_prd), ['estado_devolucion' => 'perdido'])
            ->assertSessionMissing('error');

        $prestamo->refresh();
        $this->assertSame(PrestamoDeportivo::ESTADO_SANCIONADO, $prestamo->estado_prd);
        $this->assertEqualsWithDelta(2, $this->existencia($lote, $this->sedeA()), 0.01);

        $sancion = SancionDeportiva::todasLasSedes()->where('prd_id', $prestamo->id_prd)->activas()->firstOrFail();
        $this->assertSame($this->admin->id_use, (int) $sancion->aplicada_por_use_id);

        $this->put(route('admin.deportes.sanciones.update_reposicion', $sancion->id_snd), ['lote' => 'nuevo', 'codigo_lote' => 'REP-PRUEBA-' . $sancion->id_snd])
            ->assertRedirect(route('admin.deportes.sanciones.show', $sancion->id_snd))
            ->assertSessionMissing('error');

        $sancion->refresh();
        $prestamo->refresh();
        $this->assertSame(SancionDeportiva::ESTADO_LEVANTADA, $sancion->estado_snd);
        $this->assertSame(SancionDeportiva::FORMA_REPOSICION, $sancion->forma_levantamiento_snd);
        $this->assertSame($this->admin->id_use, (int) $sancion->levantada_por_use_id);
        $this->assertSame(PrestamoDeportivo::ESTADO_REPUESTO, $prestamo->estado_prd);

        $loteNuevo = (int) $sancion->lot_id;
        $this->assertNotSame($lote, $loteNuevo);
        $this->assertSame($producto, (int) DB::table('lote')->where('id_lot', $loteNuevo)->value('pdt_id'));
        $this->assertEqualsWithDelta(1, $this->existencia($loteNuevo, $this->sedeA()), 0.01);

        $movimiento = DB::table('movimiento_inventario')->where('lot_id', $loteNuevo)->orderByDesc('id_moi')->first();
        $this->assertSame(ServicioInventario::ENTRADA, $movimiento->tipo_movimiento_moi);
        $this->assertSame(PrestamoService::REFERENCIA_SANCION, $movimiento->referencia_type_moi);
        $this->assertSame($sancion->id_snd, (int) $movimiento->referencia_id_moi);
        $this->assertSame($this->sedeA(), (int) $movimiento->sed_id);

        $this->assertEqualsWithDelta(3, (float) DB::table('inventario_total')->where('pdt_id', $producto)->where('sed_id', $this->sedeA())->value('cantidad_total_int'), 0.01);
        $this->assertCuadra($producto, $this->sedeA());
    }

    public function test_solvencia_de_persona_sancionada_es_no_solvente_y_genera_pdf(): void
    {
        $producto = $this->producto(TipoProducto::IMPLEMENTO_DEPORTIVO, 'Malla dañada');
        $lote = $this->loteConExistencia($producto, 2, $this->sedeA());
        $persona = $this->persona();
        $prestamo = $this->prestar($persona, $lote);

        $this->put(route('admin.deportes.prestamos.update_devolucion', $prestamo->id_prd), ['estado_devolucion' => 'dañado'])
            ->assertSessionMissing('error');
        $this->assertEqualsWithDelta(1, $this->existencia($lote, $this->sedeA()), 0.01);

        $this->get(route('admin.deportes.solvencia.index', ['cedula' => $persona->cedula_per]))
            ->assertOk()
            ->assertSee('No solvente')
            ->assertSee('NO se puede firmar la solvencia para el título');

        $registro = DB::table('bitacora_sistema')->where('tipo_evento_bis', 'solvencia_consultada')->orderByDesc('id_bis')->first();
        $this->assertNotNull($registro);
        $this->assertSame($persona->id_per, (int) $registro->registro_id_bis);
        $this->assertStringNotContainsString($persona->cedula_per, (string) $registro->observacion_bis);

        $respuesta = $this->get(route('admin.deportes.solvencia.pdf', ['cedula' => $persona->cedula_per]));
        $respuesta->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $respuesta->headers->get('content-type'));

        $solvente = $this->persona();
        $this->get(route('admin.deportes.solvencia.index', ['cedula' => $solvente->cedula_per]))
            ->assertOk()
            ->assertSee('Solvente')
            ->assertDontSee('No solvente');
    }

    public function test_inventario_cuadra_despues_de_prestar_y_devolver(): void
    {
        $producto = $this->producto(TipoProducto::IMPLEMENTO_DEPORTIVO, 'Peto');
        $lote = $this->loteConExistencia($producto, 10, $this->sedeA());
        $otroLote = $this->loteConExistencia($producto, 4, $this->sedeB());
        $persona = $this->persona();

        $this->post(route('admin.deportes.prestamos.store'), $this->datosPrestamo($persona, $lote, ['cantidad' => 3]))
            ->assertSessionMissing('error');
        $prestamo = PrestamoDeportivo::todasLasSedes()->where('per_id', $persona->id_per)->firstOrFail();

        $this->assertSame($this->sedeA(), (int) $prestamo->sed_id);
        $this->assertEqualsWithDelta(7, $this->existencia($lote, $this->sedeA()), 0.01);
        $this->assertCuadra($producto, $this->sedeA());

        $salida = DB::table('movimiento_inventario')->where('referencia_type_moi', PrestamoService::REFERENCIA_PRESTAMO)->where('referencia_id_moi', $prestamo->id_prd)->first();
        $this->assertSame(ServicioInventario::SALIDA, $salida->tipo_movimiento_moi);
        $this->assertSame($lote, (int) $salida->lot_id);
        $this->assertEquals(10, (float) $salida->cantidad_anterior_moi);
        $this->assertEquals(7, (float) $salida->cantidad_final_moi);

        $this->put(route('admin.deportes.prestamos.update_devolucion', $prestamo->id_prd), ['estado_devolucion' => 'bueno'])
            ->assertSessionMissing('error');

        $movimientos = DB::table('movimiento_inventario')->where('referencia_type_moi', PrestamoService::REFERENCIA_PRESTAMO)->where('referencia_id_moi', $prestamo->id_prd)->orderBy('id_moi')->get();
        $this->assertSame([ServicioInventario::SALIDA, ServicioInventario::ENTRADA], $movimientos->pluck('tipo_movimiento_moi')->all());
        $this->assertSame($lote, (int) $movimientos[1]->lot_id);
        $this->assertSame($this->sedeA(), (int) $movimientos[1]->sed_id);
        $this->assertEquals(7, (float) $movimientos[1]->cantidad_anterior_moi);
        $this->assertEquals(10, (float) $movimientos[1]->cantidad_final_moi);

        $this->assertEqualsWithDelta(10, $this->existencia($lote, $this->sedeA()), 0.01);
        $this->assertEqualsWithDelta(4, $this->existencia($otroLote, $this->sedeB()), 0.01);
        $this->assertCuadra($producto, $this->sedeA());
        $this->assertCuadra($producto, $this->sedeB());
    }

    public function test_entrega_de_material_descuenta_inventario_y_rechaza_sin_existencia(): void
    {
        $producto = $this->producto(TipoProducto::MATERIAL_OFICINA, 'Resma carta');
        $primero = $this->loteConExistencia($producto, 3, $this->sedeA());
        $segundo = $this->loteConExistencia($producto, 4, $this->sedeA());
        $persona = $this->persona();

        $this->post(route('admin.deportes.entregas.store'), [
            'cedula' => $persona->cedula_per,
            'motivo' => 'Planillas del torneo',
            'items'  => [['pdt_id' => $producto, 'cantidad' => 5]],
        ])->assertSessionMissing('error')->assertSessionHasNoErrors();

        $entrega = DB::table('entrega_material')->where('per_id', $persona->id_per)->first();
        $this->assertNotNull($entrega);
        $this->assertSame($this->sedeA(), (int) $entrega->sed_id);
        $this->assertEquals(5, (float) DB::table('detalle_entrega_material')->where('ema_id', $entrega->id_ema)->sum('cantidad_dem'));
        $this->assertEqualsWithDelta(0, $this->existencia($primero, $this->sedeA()), 0.01);
        $this->assertEqualsWithDelta(2, $this->existencia($segundo, $this->sedeA()), 0.01);
        $this->assertSame(2, DB::table('movimiento_inventario')->where('referencia_type_moi', 'entrega_material')->where('referencia_id_moi', $entrega->id_ema)->where('tipo_movimiento_moi', ServicioInventario::SALIDA)->count());
        $this->assertCuadra($producto, $this->sedeA());

        $this->post(route('admin.deportes.entregas.store'), [
            'dependencia' => 'Coordinación de Deportes',
            'motivo'      => 'Archivo',
            'items'       => [['pdt_id' => $producto, 'cantidad' => 3]],
        ])->assertRedirect()->assertSessionHas('error', fn ($mensaje) => str_contains($mensaje, 'existencia suficiente'));

        $this->assertFalse(DB::table('entrega_material')->where('dependencia_ema', 'Coordinación de Deportes')->exists());
        $this->assertEqualsWithDelta(2, $this->existencia($segundo, $this->sedeA()), 0.01);
        $this->assertCuadra($producto, $this->sedeA());

        $this->get(route('admin.deportes.entregas.show', $entrega->id_ema))->assertOk()->assertSee('Resma carta');
        $this->get(route('admin.deportes.entregas.index'))->assertOk();
    }

    public function test_pantallas_de_prestamos_sanciones_y_entregas_responden(): void
    {
        $producto = $this->producto(TipoProducto::IMPLEMENTO_DEPORTIVO, 'Balón pantalla');
        $lote = $this->loteConExistencia($producto, 2, $this->sedeA());
        $persona = $this->persona();
        $prestamo = $this->prestar($persona, $lote);

        $this->get(route('admin.deportes.prestamos.index'))->assertOk()->assertSee('Balón pantalla');
        $this->get(route('admin.deportes.prestamos.create', ['cedula' => $persona->cedula_per]))->assertOk()->assertSee('solo se permite uno a la vez');
        $this->get(route('admin.deportes.prestamos.show', $prestamo->id_prd))->assertOk()->assertSee('Registrar devolución');
        $this->get(route('admin.deportes.sanciones.index'))->assertOk();
        $this->get(route('admin.deportes.entregas.create'))->assertOk();
    }
}
