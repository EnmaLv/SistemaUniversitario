<?php

namespace Tests\Feature\Seguridad;

use App\Models\Deporte\DetallePrestamoDeportivo;
use App\Models\Deporte\PrestamoDeportivo;
use App\Models\Deporte\SancionDeportiva;
use App\Models\Perfil;
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

    private function producto(string $tipo, string $nombre, int $porCaja = 1): int
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
            'unidades_presentacion_pdt' => $porCaja,
            'unm_id'                    => DB::table('unidad_medida')->orderBy('id_unm')->value('id_unm'),
            'cap_id'                    => $categoria,
            'created_at'                => now(),
            'updated_at'                => now(),
        ], 'id_pdt');
    }

    private function loteConExistencia(int $producto, float $cantidad, int $sede, ?float $unidades = null): int
    {
        $lote = DB::table('lote')->insertGetId([
            'codigo_lote_lot'   => 'LD-' . random_int(1000, 999999),
            'fecha_entrada_lot' => now()->toDateString(),
            'pdt_id'            => $producto,
            'created_at'        => now(),
            'updated_at'        => now(),
        ], 'id_lot');

        app(ServicioInventario::class)->entrada($lote, $sede, $cantidad, $unidades);

        return $lote;
    }

    private function persona(?string $perfil = null): Persona
    {
        $idPerfil = $perfil
            ? DB::table('perfil')->where('nombre_perfil', $perfil)->value('id_perfil')
            : DB::table('perfil')->orderBy('id_perfil')->value('id_perfil');

        return Persona::create([
            'nombres_per'   => 'Atleta',
            'apellidos_per' => 'Pruebas',
            'cedula_per'    => (string) random_int(10000000, 99999999),
            'telefono_per'  => '0424-0000000',
            'genero_per'    => 'Otro',
            'correo_per'    => 'atleta' . random_int(1, 999999) . '@prueba.test',
            'id_perfil'     => $idPerfil,
            'sed_id'        => $this->sedeA(),
        ]);
    }

    private function datosPrestamo(Persona|string $persona, array $items, string $tipo = PrestamoDeportivo::TIPO_DEPORTIVO, array $extra = []): array
    {
        return array_merge([
            'cedula'                    => $persona instanceof Persona ? $persona->cedula_per : $persona,
            'tipo'                      => $tipo,
            'items'                     => collect($items)->map(fn ($cantidad, $producto) => ['pdt_id' => $producto, 'cantidad' => $cantidad])->values()->all(),
            'motivo'                    => 'Práctica de voleibol',
            'lugar'                     => 'Patio central',
            'fecha_devolucion_esperada' => now()->addDay()->format('Y-m-d H:i:s'),
        ], $extra);
    }

    private function prestar(Persona $persona, array $items, string $tipo = PrestamoDeportivo::TIPO_DEPORTIVO): PrestamoDeportivo
    {
        $this->post(route('admin.deportes.prestamos.store'), $this->datosPrestamo($persona, $items, $tipo))
            ->assertSessionHasNoErrors()
            ->assertSessionMissing('error');

        return PrestamoDeportivo::todasLasSedes()->with('detalles')->where('per_id', $persona->id_per)->where('tipo_prd', $tipo)->latest('id_prd')->firstOrFail();
    }

    private function lineas(PrestamoDeportivo $prestamo, array $porProducto): array
    {
        return $prestamo->detalles->mapWithKeys(fn ($detalle) => [$detalle->id_dpd => $porProducto[$detalle->pdt_id]])->all();
    }

    private function existencia(int $lote, int $sede): float
    {
        return (float) DB::table('inventario_sede_lote')->where('lot_id', $lote)->where('sed_id', $sede)->value('cantidad_isl');
    }

    private function unidades(int $lote, int $sede): float
    {
        return (float) DB::table('inventario_sede_lote')->where('lot_id', $lote)->where('sed_id', $sede)->value('cantidad_convertida_isl');
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

    private function assertMovimientosCuadran(int $producto, int $sede): void
    {
        $neto = DB::table('movimiento_inventario')->where('pdt_id', $producto)->where('sed_id', $sede)->get()
            ->sum(fn ($movimiento) => in_array($movimiento->tipo_movimiento_moi, [ServicioInventario::ENTRADA, ServicioInventario::TRANSFERENCIA_ENTRADA], true)
                ? (float) $movimiento->cantidad_convertida_moi
                : -(float) $movimiento->cantidad_convertida_moi);

        $existencia = (float) DB::table('inventario_sede_lote as e')->join('lote as l', 'l.id_lot', '=', 'e.lot_id')
            ->where('l.pdt_id', $producto)->where('e.sed_id', $sede)->sum('e.cantidad_convertida_isl');

        $this->assertEqualsWithDelta($existencia, $neto, 0.01);
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

        $this->post(route('admin.deportes.prestamos.store'), $this->datosPrestamo($cedula, [$producto => 1]))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame(0, DB::table('detalle_prestamo_deportivo')->where('lot_id', $lote)->count());
        $this->assertEqualsWithDelta(3, $this->existencia($lote, $this->sedeA()), 0.01);
    }

    public function test_prestamo_de_marcadores_a_un_docente(): void
    {
        $marcador = $this->producto(TipoProducto::MATERIAL_OFICINA, 'Marcador acrílico azul');
        $borrador = $this->producto(TipoProducto::MATERIAL_OFICINA, 'Borrador de pizarra');
        $loteMarcador = $this->loteConExistencia($marcador, 12, $this->sedeA());
        $loteBorrador = $this->loteConExistencia($borrador, 3, $this->sedeA());
        $docente = $this->persona(Perfil::DOCENTE);

        $this->post(route('admin.deportes.prestamos.store'), $this->datosPrestamo($docente, [$marcador => 3, $borrador => 1], PrestamoDeportivo::TIPO_OFICINA, [
            'motivo' => 'Clase de Matemática I',
            'lugar'  => 'Aula 12',
        ]))->assertSessionHasNoErrors()->assertSessionMissing('error');

        $prestamo = PrestamoDeportivo::todasLasSedes()->with('detalles')->where('per_id', $docente->id_per)->firstOrFail();

        $this->assertSame(PrestamoDeportivo::TIPO_OFICINA, $prestamo->tipo_prd);
        $this->assertSame(PrestamoDeportivo::ESTADO_ACTIVO, $prestamo->estado_prd);
        $this->assertSame('Aula 12', $prestamo->lugar_prd);
        $this->assertSame($this->admin->id_use, (int) $prestamo->registrado_por_use_id);
        $this->assertSame(4, (int) $prestamo->cantidad_prd);
        $this->assertCount(2, $prestamo->detalles);
        $this->assertEqualsWithDelta(3, $prestamo->detalles->firstWhere('pdt_id', $marcador)->cantidad_dpd, 0.01);
        $this->assertEqualsWithDelta(9, $this->existencia($loteMarcador, $this->sedeA()), 0.01);
        $this->assertEqualsWithDelta(2, $this->existencia($loteBorrador, $this->sedeA()), 0.01);
        $this->assertSame(2, DB::table('movimiento_inventario')->where('referencia_type_moi', PrestamoService::REFERENCIA_PRESTAMO)->where('referencia_id_moi', $prestamo->id_prd)->where('tipo_movimiento_moi', ServicioInventario::SALIDA)->count());
        $this->assertCuadra($marcador, $this->sedeA());

        $this->get(route('admin.deportes.prestamos.show', $prestamo->id_prd))
            ->assertOk()
            ->assertSee('Marcador acrílico azul')
            ->assertSee('Se gastó');

        $this->get(route('admin.deportes.prestamos.index', ['tipo' => 'oficina', 'q' => $docente->cedula_per]))
            ->assertOk()
            ->assertSee('Marcador acrílico azul x3');

        $this->get(route('admin.deportes.prestamos.index', ['tipo' => 'deportivo', 'q' => $docente->cedula_per]))
            ->assertOk()
            ->assertDontSee('Marcador acrílico azul');
    }

    public function test_devolucion_parcial_con_consumo_no_sanciona_y_cuadra(): void
    {
        $marcador = $this->producto(TipoProducto::MATERIAL_OFICINA, 'Marcador consumible');
        $lote = $this->loteConExistencia($marcador, 10, $this->sedeA());
        $docente = $this->persona(Perfil::DOCENTE);
        $prestamo = $this->prestar($docente, [$marcador => 5], PrestamoDeportivo::TIPO_OFICINA);

        $this->assertEqualsWithDelta(5, $this->existencia($lote, $this->sedeA()), 0.01);

        $this->put(route('admin.deportes.prestamos.update_devolucion', $prestamo->id_prd), [
            'lineas' => $this->lineas($prestamo, [$marcador => ['devuelta' => 2, 'danada' => 0, 'consumida' => 3]]),
        ])->assertSessionHasNoErrors()->assertSessionMissing('error');

        $prestamo->refresh();
        $detalle = DetallePrestamoDeportivo::where('prd_id', $prestamo->id_prd)->firstOrFail();

        $this->assertSame(PrestamoDeportivo::ESTADO_DEVUELTO, $prestamo->estado_prd);
        $this->assertSame(PrestamoDeportivo::DEVOLUCION_BUENO, $prestamo->estado_devolucion_prd);
        $this->assertSame($this->admin->id_use, (int) $prestamo->recibido_por_use_id);
        $this->assertEqualsWithDelta(2, $detalle->cantidad_devuelta_dpd, 0.01);
        $this->assertEqualsWithDelta(3, $detalle->cantidad_consumida_dpd, 0.01);
        $this->assertEqualsWithDelta(0, $detalle->cantidad_perdida_dpd, 0.01);
        $this->assertFalse(SancionDeportiva::todasLasSedes()->where('prd_id', $prestamo->id_prd)->exists());

        $this->assertEqualsWithDelta(7, $this->existencia($lote, $this->sedeA()), 0.01);
        $entrada = DB::table('movimiento_inventario')->where('referencia_type_moi', PrestamoService::REFERENCIA_PRESTAMO)->where('referencia_id_moi', $prestamo->id_prd)->where('tipo_movimiento_moi', ServicioInventario::ENTRADA)->first();
        $this->assertSame($lote, (int) $entrada->lot_id);
        $this->assertEquals(2, (float) $entrada->cantidad_convertida_moi);
        $this->assertCuadra($marcador, $this->sedeA());
        $this->assertMovimientosCuadran($marcador, $this->sedeA());

        $this->prestar($docente, [$marcador => 1], PrestamoDeportivo::TIPO_OFICINA);
    }

    public function test_devolucion_con_faltante_sanciona_y_la_reposicion_cuadra(): void
    {
        $marcador = $this->producto(TipoProducto::MATERIAL_OFICINA, 'Marcador faltante');
        $lote = $this->loteConExistencia($marcador, 10, $this->sedeA());
        $docente = $this->persona(Perfil::DOCENTE);
        $prestamo = $this->prestar($docente, [$marcador => 6], PrestamoDeportivo::TIPO_OFICINA);

        $this->put(route('admin.deportes.prestamos.update_devolucion', $prestamo->id_prd), [
            'lineas' => $this->lineas($prestamo, [$marcador => ['devuelta' => 2, 'danada' => 1, 'consumida' => 1]]),
        ])->assertSessionMissing('error');

        $prestamo->refresh();
        $detalle = DetallePrestamoDeportivo::where('prd_id', $prestamo->id_prd)->firstOrFail();
        $this->assertSame(PrestamoDeportivo::ESTADO_SANCIONADO, $prestamo->estado_prd);
        $this->assertSame(PrestamoDeportivo::DEVOLUCION_PERDIDO, $prestamo->estado_devolucion_prd);
        $this->assertEqualsWithDelta(2, $detalle->cantidad_perdida_dpd, 0.01);
        $this->assertEqualsWithDelta(3, $detalle->porReponer(), 0.01);
        $this->assertEqualsWithDelta(6, $this->existencia($lote, $this->sedeA()), 0.01);

        $sancion = SancionDeportiva::todasLasSedes()->where('prd_id', $prestamo->id_prd)->activas()->firstOrFail();
        $this->assertSame(PrestamoService::MOTIVO_DANADO_PERDIDO, $sancion->motivo_snd);

        $this->put(route('admin.deportes.sanciones.update_reposicion', $sancion->id_snd), ['lote' => 'original'])
            ->assertSessionMissing('error');

        $this->assertSame(SancionDeportiva::ESTADO_LEVANTADA, $sancion->fresh()->estado_snd);
        $this->assertSame(PrestamoDeportivo::ESTADO_REPUESTO, $prestamo->fresh()->estado_prd);
        $this->assertEqualsWithDelta(9, $this->existencia($lote, $this->sedeA()), 0.01);
        $this->assertCuadra($marcador, $this->sedeA());
        $this->assertMovimientosCuadran($marcador, $this->sedeA());
    }

    public function test_los_implementos_deportivos_no_admiten_consumo(): void
    {
        $balon = $this->producto(TipoProducto::IMPLEMENTO_DEPORTIVO, 'Balón sin consumo');
        $lote = $this->loteConExistencia($balon, 2, $this->sedeA());
        $persona = $this->persona();
        $prestamo = $this->prestar($persona, [$balon => 1]);

        $this->put(route('admin.deportes.prestamos.update_devolucion', $prestamo->id_prd), [
            'lineas' => $this->lineas($prestamo, [$balon => ['devuelta' => 0, 'danada' => 0, 'consumida' => 1]]),
        ])->assertSessionHas('error', fn ($mensaje) => str_contains($mensaje, 'no se gastan'));

        $this->assertSame(PrestamoDeportivo::ESTADO_ACTIVO, $prestamo->fresh()->estado_prd);
        $this->assertEqualsWithDelta(1, $this->existencia($lote, $this->sedeA()), 0.01);
    }

    public function test_un_deportivo_y_uno_de_oficina_a_la_vez_permitido_y_dos_deportivos_rechazado(): void
    {
        $balon = $this->producto(TipoProducto::IMPLEMENTO_DEPORTIVO, 'Balón doble');
        $marcador = $this->producto(TipoProducto::MATERIAL_OFICINA, 'Marcador doble');
        $loteBalon = $this->loteConExistencia($balon, 5, $this->sedeA());
        $this->loteConExistencia($marcador, 5, $this->sedeA());
        $persona = $this->persona(Perfil::DOCENTE);

        $this->prestar($persona, [$balon => 1]);
        $this->prestar($persona, [$marcador => 2], PrestamoDeportivo::TIPO_OFICINA);

        $this->post(route('admin.deportes.prestamos.store'), $this->datosPrestamo($persona, [$balon => 1]))
            ->assertRedirect()
            ->assertSessionHas('error', fn ($mensaje) => str_contains($mensaje, 'uno de cada tipo'));

        $this->post(route('admin.deportes.prestamos.store'), $this->datosPrestamo($persona, [$marcador => 1], PrestamoDeportivo::TIPO_OFICINA))
            ->assertRedirect()
            ->assertSessionHas('error', fn ($mensaje) => str_contains($mensaje, 'uno de cada tipo'));

        $this->assertSame(1, DB::table('prestamo_deportivo')->where('per_id', $persona->id_per)->where('tipo_prd', 'deportivo')->count());
        $this->assertSame(1, DB::table('prestamo_deportivo')->where('per_id', $persona->id_per)->where('tipo_prd', 'oficina')->count());
        $this->assertEqualsWithDelta(4, $this->existencia($loteBalon, $this->sedeA()), 0.01);

        $this->get(route('admin.deportes.prestamos.create', ['cedula' => $persona->cedula_per]))
            ->assertOk()
            ->assertSee('solo se permite uno de cada tipo a la vez');
    }

    public function test_no_se_mezclan_tipos_en_un_prestamo(): void
    {
        $balon = $this->producto(TipoProducto::IMPLEMENTO_DEPORTIVO, 'Balón mezclado');
        $marcador = $this->producto(TipoProducto::MATERIAL_OFICINA, 'Marcador mezclado');
        $this->loteConExistencia($balon, 5, $this->sedeA());
        $this->loteConExistencia($marcador, 5, $this->sedeA());
        $persona = $this->persona();

        $this->post(route('admin.deportes.prestamos.store'), $this->datosPrestamo($persona, [$balon => 1, $marcador => 1]))
            ->assertSessionHas('error', fn ($mensaje) => str_contains($mensaje, 'préstamo aparte'));

        $this->assertFalse(DB::table('prestamo_deportivo')->where('per_id', $persona->id_per)->exists());
    }

    public function test_prestamo_vencido_genera_sancion_y_bloquea_nuevos_prestamos(): void
    {
        $producto = $this->producto(TipoProducto::IMPLEMENTO_DEPORTIVO, 'Balón vencido');
        $marcador = $this->producto(TipoProducto::MATERIAL_OFICINA, 'Marcador tras vencido');
        $this->loteConExistencia($producto, 5, $this->sedeA());
        $this->loteConExistencia($marcador, 5, $this->sedeA());
        $persona = $this->persona();
        $prestamo = $this->prestar($persona, [$producto => 1]);

        $this->vencerPrestamos();

        $prestamo->refresh();
        $this->assertSame(PrestamoDeportivo::ESTADO_VENCIDO, $prestamo->estado_prd);

        $sancion = SancionDeportiva::todasLasSedes()->where('prd_id', $prestamo->id_prd)->firstOrFail();
        $this->assertSame(SancionDeportiva::ESTADO_ACTIVA, $sancion->estado_snd);
        $this->assertNull($sancion->aplicada_por_use_id);
        $this->assertSame($persona->id_per, (int) $sancion->per_id);

        $this->post(route('admin.deportes.prestamos.store'), $this->datosPrestamo($persona, [$marcador => 1], PrestamoDeportivo::TIPO_OFICINA))
            ->assertRedirect()
            ->assertSessionHas('error', fn ($mensaje) => str_contains($mensaje, 'sanción activa'));

        $this->assertSame(1, DB::table('prestamo_deportivo')->where('per_id', $persona->id_per)->count());
        $this->assertTrue(DB::table('bitacora_sistema')->where('tipo_evento_bis', 'prestamo_vencido')->where('registro_id_bis', $sancion->id_snd)->exists());
    }

    public function test_sancion_de_material_de_oficina_bloquea_prestamos_y_aparece_en_solvencia(): void
    {
        $marcador = $this->producto(TipoProducto::MATERIAL_OFICINA, 'Marcador vencido');
        $balon = $this->producto(TipoProducto::IMPLEMENTO_DEPORTIVO, 'Balón tras oficina');
        $this->loteConExistencia($marcador, 5, $this->sedeA());
        $this->loteConExistencia($balon, 5, $this->sedeA());
        $docente = $this->persona(Perfil::DOCENTE);
        $prestamo = $this->prestar($docente, [$marcador => 2], PrestamoDeportivo::TIPO_OFICINA);

        $this->vencerPrestamos();

        $this->assertSame(PrestamoDeportivo::ESTADO_VENCIDO, $prestamo->fresh()->estado_prd);
        $this->assertTrue(SancionDeportiva::todasLasSedes()->where('prd_id', $prestamo->id_prd)->activas()->exists());

        $this->post(route('admin.deportes.prestamos.store'), $this->datosPrestamo($docente, [$balon => 1]))
            ->assertSessionHas('error', fn ($mensaje) => str_contains($mensaje, 'sanción activa'));

        $this->get(route('admin.deportes.solvencia.index', ['cedula' => $docente->cedula_per]))
            ->assertOk()
            ->assertSee('No solvente')
            ->assertSee('material de oficina')
            ->assertSee('Marcador vencido x2');

        $this->put(route('admin.deportes.prestamos.update_devolucion', $prestamo->id_prd), [
            'lineas' => $this->lineas($prestamo, [$marcador => ['devuelta' => 1, 'consumida' => 1]]),
        ])->assertSessionMissing('error');

        $this->assertSame(SancionDeportiva::ESTADO_LEVANTADA, SancionDeportiva::todasLasSedes()->where('prd_id', $prestamo->id_prd)->firstOrFail()->estado_snd);
        $this->get(route('admin.deportes.solvencia.index', ['cedula' => $docente->cedula_per]))
            ->assertOk()
            ->assertDontSee('No solvente');
    }

    public function test_devolucion_en_buen_estado_levanta_la_sancion(): void
    {
        $producto = $this->producto(TipoProducto::IMPLEMENTO_DEPORTIVO, 'Balón devuelto tarde');
        $lote = $this->loteConExistencia($producto, 2, $this->sedeA());
        $persona = $this->persona();
        $prestamo = $this->prestar($persona, [$producto => 1]);

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

        $this->post(route('admin.deportes.prestamos.store'), $this->datosPrestamo($persona, [$producto => 1]))->assertSessionMissing('error');
        $this->assertSame(2, DB::table('prestamo_deportivo')->where('per_id', $persona->id_per)->count());
    }

    public function test_reposicion_levanta_la_sancion_y_entra_al_inventario(): void
    {
        $producto = $this->producto(TipoProducto::IMPLEMENTO_DEPORTIVO, 'Raqueta perdida');
        $lote = $this->loteConExistencia($producto, 3, $this->sedeA());
        $persona = $this->persona();
        $prestamo = $this->prestar($persona, [$producto => 1]);

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
        $prestamo = $this->prestar($persona, [$producto => 1]);

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

        $prestamo = $this->prestar($persona, [$producto => 3]);

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
        $this->assertMovimientosCuadran($producto, $this->sedeA());
    }

    public function test_prestamo_toma_varios_lotes_y_devuelve_cada_uno_a_su_lote(): void
    {
        $resma = $this->producto(TipoProducto::MATERIAL_OFICINA, 'Resma carta');
        $primero = $this->loteConExistencia($resma, 3, $this->sedeA());
        $segundo = $this->loteConExistencia($resma, 4, $this->sedeA());
        $persona = $this->persona();
        $prestamo = $this->prestar($persona, [$resma => 5], PrestamoDeportivo::TIPO_OFICINA);

        $this->assertCount(2, $prestamo->detalles);
        $this->assertEqualsWithDelta(0, $this->existencia($primero, $this->sedeA()), 0.01);
        $this->assertEqualsWithDelta(2, $this->existencia($segundo, $this->sedeA()), 0.01);

        $this->post(route('admin.deportes.prestamos.store'), $this->datosPrestamo($this->persona(), [$resma => 3], PrestamoDeportivo::TIPO_OFICINA))
            ->assertSessionHas('error', fn ($mensaje) => str_contains($mensaje, 'No hay suficiente'));

        $this->put(route('admin.deportes.prestamos.update_devolucion', $prestamo->id_prd), ['estado_devolucion' => 'bueno'])
            ->assertSessionMissing('error');

        $this->assertEqualsWithDelta(3, $this->existencia($primero, $this->sedeA()), 0.01);
        $this->assertEqualsWithDelta(4, $this->existencia($segundo, $this->sedeA()), 0.01);
        $this->assertCuadra($resma, $this->sedeA());
        $this->assertMovimientosCuadran($resma, $this->sedeA());
    }

    public function test_producto_por_caja_se_presta_por_unidades(): void
    {
        $lapiz = $this->producto(TipoProducto::MATERIAL_OFICINA, 'Lápiz en caja', 12);
        $lote = $this->loteConExistencia($lapiz, 2, $this->sedeA(), 24);
        $persona = $this->persona(Perfil::DOCENTE);
        $prestamo = $this->prestar($persona, [$lapiz => 5], PrestamoDeportivo::TIPO_OFICINA);

        $this->assertEqualsWithDelta(19, $this->unidades($lote, $this->sedeA()), 0.01);
        $this->assertLessThan(2, $this->existencia($lote, $this->sedeA()));
        $this->assertGreaterThan(1, $this->existencia($lote, $this->sedeA()));
        $this->assertCuadra($lapiz, $this->sedeA());

        $this->put(route('admin.deportes.prestamos.update_devolucion', $prestamo->id_prd), ['estado_devolucion' => 'bueno'])
            ->assertSessionMissing('error');

        $this->assertEqualsWithDelta(24, $this->unidades($lote, $this->sedeA()), 0.01);
        $this->assertEqualsWithDelta(2, $this->existencia($lote, $this->sedeA()), 0.01);
        $this->assertCuadra($lapiz, $this->sedeA());
        $this->assertMovimientosCuadran($lapiz, $this->sedeA());
    }

    public function test_el_inicio_de_deportes_muestra_prestamos_sanciones_y_stock_minimo(): void
    {
        $producto = $this->producto(TipoProducto::IMPLEMENTO_DEPORTIVO, 'Balón del inicio');
        DB::table('producto')->where('id_pdt', $producto)->update(['stock_minimo_pdt' => 5]);
        $marcador = $this->producto(TipoProducto::MATERIAL_OFICINA, 'Marcador del inicio');
        $this->loteConExistencia($producto, 3, $this->sedeA());
        $this->loteConExistencia($marcador, 10, $this->sedeA());
        $activo = $this->persona();
        $sancionada = $this->persona();
        $this->prestar($activo, [$producto => 1]);
        $this->prestar($activo, [$marcador => 2], PrestamoDeportivo::TIPO_OFICINA);
        $prestamo = $this->prestar($sancionada, [$producto => 1]);

        $this->put(route('admin.deportes.prestamos.update_devolucion', $prestamo->id_prd), ['estado_devolucion' => 'perdido'])
            ->assertSessionMissing('error');

        $this->withSession(['modulo_activo' => 'deporte', 'permisos_usuario_id' => $this->admin->id_use]);

        $this->get('/home')
            ->assertOk()
            ->assertSee('Inicio de Deportes')
            ->assertSee('Balón del inicio')
            ->assertSee('Marcador del inicio x2')
            ->assertSee('Reponer')
            ->assertSee('Préstamos por devolver')
            ->assertSee('Sanciones activas');
    }

    public function test_pantallas_de_prestamos_sanciones_y_redireccion_de_entregas(): void
    {
        $producto = $this->producto(TipoProducto::IMPLEMENTO_DEPORTIVO, 'Balón pantalla');
        $this->loteConExistencia($producto, 2, $this->sedeA());
        $persona = $this->persona();
        $prestamo = $this->prestar($persona, [$producto => 1]);

        $this->get(route('admin.deportes.prestamos.index'))->assertOk()->assertSee('Balón pantalla');
        $this->get(route('admin.deportes.prestamos.index', ['tipo' => 'deportivo', 'estado' => 'activo']))->assertOk()->assertSee('Balón pantalla');
        $this->get(route('admin.deportes.prestamos.create'))->assertOk();
        $this->get(route('admin.deportes.prestamos.create', ['cedula' => $persona->cedula_per]))->assertOk()->assertSee('Material de oficina');
        $this->get(route('admin.deportes.prestamos.show', $prestamo->id_prd))->assertOk()->assertSee('Registrar devolución');
        $this->get(route('admin.deportes.sanciones.index'))->assertOk();
        $this->get(route('admin.deportes.entregas.index'))->assertRedirect(route('admin.deportes.prestamos.index', ['tipo' => 'oficina']));
        $this->get('/admin/deportes/entregas/create')->assertRedirect(route('admin.deportes.prestamos.index', ['tipo' => 'oficina']));
    }
}
