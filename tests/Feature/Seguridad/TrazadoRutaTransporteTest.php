<?php

namespace Tests\Feature\Seguridad;

use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

class TrazadoRutaTransporteTest extends SeguridadTestCase
{
    private const LNG_RUTA = -69.2;

    private const LNG_DESVIO = -69.195;

    private Usuario $conductor;

    private int $vehiculo;

    private int $ruta;

    protected function setUp(): void
    {
        parent::setUp();

        $sede = $this->sedeA();
        $this->conductor = $this->crearUsuario();

        $marca = DB::table('marca_vehiculo')->insertGetId(['nombre_mav' => 'Marca trazado', 'created_at' => now(), 'updated_at' => now()], 'id_mav');
        $modelo = DB::table('modelo_vehiculo')->insertGetId(['nombre_mov' => 'Modelo trazado', 'mav_id' => $marca, 'created_at' => now(), 'updated_at' => now()], 'id_mov');
        $combustible = DB::table('tipo_combustible')->insertGetId(['nombre_tcb' => 'Gasoil', 'created_at' => now(), 'updated_at' => now()], 'id_tcb');
        $this->vehiculo = DB::table('vehiculo_transporte')->insertGetId([
            'placa_vtr' => 'TRZ' . random_int(1000, 9999), 'anio_vtr' => 2020, 'capacidad_pasajeros_vtr' => 30,
            'capacidad_tanque_litros_vtr' => 200, 'nivel_combustible_actual_vtr' => 150, 'consumo_urbano_vtr' => 0.3,
            'consumo_carretera_vtr' => 0.2, 'consumo_ralenti_vtr' => 2, 'km_actual_vtr' => 1000,
            'mov_id' => $modelo, 'tcb_id' => $combustible, 'sed_id' => $sede, 'created_at' => now(), 'updated_at' => now(),
        ], 'id_vtr');
        $this->ruta = DB::table('ruta_transporte')->insertGetId(['nombre_rtr' => 'Ruta trazado', 'distancia_km_rtr' => 12, 'sed_id' => $sede, 'created_at' => now(), 'updated_at' => now()], 'id_rtr');

        Sanctum::actingAs($this->conductor);
    }

    public function test_el_primer_viaje_finalizado_crea_la_referencia(): void
    {
        $viaje = $this->recorrer($this->puntosSobreRuta());

        $this->assertSame(11, DB::table('log_gps_transporte')->where('vjt_id', $viaje)->count());

        $referencia = DB::table('trazado_ruta_transporte')->where('rtr_id', $this->ruta)->orderBy('orden_trt')->get();

        $this->assertCount(2, $referencia);
        $this->assertTrue($referencia->every(fn ($p) => (int) $p->version_trt === 1 && (bool) $p->vigente_trt && (int) $p->vjt_id === $viaje));
        $this->assertEqualsWithDelta(9.0, (float) $referencia->first()->lat_trt, 0.000001);
        $this->assertEqualsWithDelta(9.01, (float) $referencia->last()->lat_trt, 0.000001);
    }

    public function test_un_viaje_sobre_la_referencia_no_guarda_puntos_pero_actualiza_la_posicion(): void
    {
        $this->recorrer($this->puntosSobreRuta());

        $viaje = $this->crearViaje();
        $this->postJson("/api/viajes/{$viaje}/iniciar", ['pasajeros' => 0])->assertOk();

        $ultimo = null;
        foreach ($this->puntosSobreRuta() as $punto) {
            $this->postJson("/api/viajes/{$viaje}/gps", $punto)->assertOk()->assertExactJson(['success' => true]);
            $ultimo = $punto;
        }

        $this->postJson("/api/viajes/{$viaje}/gps", $ultimo)->assertOk()->assertExactJson(['success' => true, 'duplicate' => true]);

        $this->assertSame(0, DB::table('log_gps_transporte')->where('vjt_id', $viaje)->count());

        $fila = DB::table('viaje_transporte')->where('id_vjt', $viaje)->first();
        $this->assertEqualsWithDelta(9.01, (float) $fila->ultima_lat_vjt, 0.000001);
        $this->assertEqualsWithDelta(self::LNG_RUTA, (float) $fila->ultima_lng_vjt, 0.000001);
        $this->assertSame(11, (int) $fila->puntos_recibidos_vjt);
        $this->assertEqualsWithDelta(30.0, (float) $fila->velocidad_max_vjt, 0.01);
        $this->assertFalse((bool) $fila->hubo_desvio_vjt);

        $this->getJson("/api/viajes/{$viaje}/posicion")
            ->assertOk()
            ->assertJsonPath('latitud', 9.01)
            ->assertJsonPath('longitud', self::LNG_RUTA)
            ->assertJsonStructure(['success', 'latitud', 'longitud', 'velocidad', 'pasajeros', 'distancia_km', 'litros_gastados', 'estado', 'fecha_registro', 'actualizado_hace']);

        $respuesta = $this->postJson("/api/viajes/{$viaje}/finalizar")
            ->assertOk()
            ->assertJsonPath('data.fuente', 'gps')
            ->assertJsonPath('data.puntos_gps_usados', 11);

        $this->assertEqualsWithDelta(1.11, (float) $respuesta->json('data.distancia_km'), 0.02);
        $this->assertGreaterThan(0, (float) $respuesta->json('data.litros_gastados'));
        $this->assertSame(1, (int) DB::table('trazado_ruta_transporte')->where('rtr_id', $this->ruta)->max('version_trt'));
    }

    public function test_un_viaje_con_desvio_guarda_solo_los_puntos_del_desvio(): void
    {
        $this->recorrer($this->puntosSobreRuta());

        $viaje = $this->recorrer($this->puntosConDesvio());

        $logs = DB::table('log_gps_transporte')->where('vjt_id', $viaje)->orderBy('registrado_en_lgt')->get();
        $this->assertCount(3, $logs);
        $this->assertTrue($logs->every(fn ($log) => (bool) $log->es_desvio_lgt && (int) $log->tramo_desvio_lgt === 1));
        $this->assertTrue($logs->every(fn ($log) => abs((float) $log->lng_lgt - self::LNG_DESVIO) < 0.000001));

        $fila = DB::table('viaje_transporte')->where('id_vjt', $viaje)->first();
        $this->assertTrue((bool) $fila->hubo_desvio_vjt);
        $this->assertFalse((bool) $fila->en_desvio_vjt);
        $this->assertSame(1, (int) $fila->tramos_desvio_vjt);
        $this->assertSame(11, (int) $fila->puntos_recibidos_vjt);
        $this->assertGreaterThan(1.11, (float) $fila->distancia_km_vjt);
    }

    public function test_un_administrador_marca_el_recorrido_de_un_viaje_como_nueva_referencia(): void
    {
        $this->recorrer($this->puntosSobreRuta());
        $viaje = $this->recorrer($this->puntosConDesvio());

        $admin = $this->crearUsuario('Administrador');

        $this->actingAs($admin)
            ->withSession(['master_key_validated' => true, 'ultima_actividad' => time(), 'sede_activa' => $this->sedeA()])
            ->post(route('admin.transporte.maestros.bus_viajes.marcar_referencia', $viaje))
            ->assertRedirect(route('admin.transporte.maestros.bus_viajes.show', $viaje))
            ->assertSessionHas('success');

        $this->assertSame(0, DB::table('trazado_ruta_transporte')->where('rtr_id', $this->ruta)->where('version_trt', 1)->where('vigente_trt', true)->count());

        $nueva = DB::table('trazado_ruta_transporte')->where('rtr_id', $this->ruta)->where('version_trt', 2)->orderBy('orden_trt')->get();
        $this->assertGreaterThan(2, $nueva->count());
        $this->assertTrue($nueva->every(fn ($p) => (bool) $p->vigente_trt && (int) $p->vjt_id === $viaje));
        $this->assertTrue($nueva->contains(fn ($p) => abs((float) $p->lng_trt - self::LNG_DESVIO) < 0.000001));
        $this->assertEqualsWithDelta(9.0, (float) $nueva->first()->lat_trt, 0.000001);
        $this->assertEqualsWithDelta(9.01, (float) $nueva->last()->lat_trt, 0.000001);

        $this->assertTrue(DB::table('bitacora_sistema')
            ->where('tipo_evento_bis', 'trazado_referencia_marcado')
            ->where('registro_id_bis', $this->ruta)
            ->exists());
    }

    private function crearViaje(): int
    {
        return DB::table('viaje_transporte')->insertGetId([
            'vtr_id' => $this->vehiculo, 'rtr_id' => $this->ruta, 'conductor_use_id' => $this->conductor->id_use,
            'estado_viaje_vjt' => 'programado', 'created_at' => now(), 'updated_at' => now(),
        ], 'id_vjt');
    }

    private function recorrer(array $puntos): int
    {
        Sanctum::actingAs($this->conductor);

        $viaje = $this->crearViaje();

        $this->postJson("/api/viajes/{$viaje}/iniciar", ['pasajeros' => 0])->assertOk();

        foreach ($puntos as $punto) {
            $this->postJson("/api/viajes/{$viaje}/gps", $punto)->assertOk()->assertExactJson(['success' => true]);
        }

        $this->postJson("/api/viajes/{$viaje}/finalizar")->assertOk()->assertJsonPath('data.viaje.estado', 'finalizado');

        return $viaje;
    }

    private function puntosSobreRuta(): array
    {
        return array_map(fn ($i) => $this->punto(9.0 + $i * 0.001, self::LNG_RUTA, $i), range(0, 10));
    }

    private function puntosConDesvio(): array
    {
        return array_map(
            fn ($i) => $this->punto(9.0 + $i * 0.001, in_array($i, [5, 6, 7], true) ? self::LNG_DESVIO : self::LNG_RUTA, $i),
            range(0, 10)
        );
    }

    private function punto(float $lat, float $lng, int $segundo): array
    {
        return [
            'local_id'  => (string) Str::uuid(),
            'lat'       => round($lat, 7),
            'lng'       => $lng,
            'velocidad' => 30,
            'heading'   => 0,
            'timestamp' => now()->addSeconds($segundo * 10)->toIso8601String(),
        ];
    }
}
