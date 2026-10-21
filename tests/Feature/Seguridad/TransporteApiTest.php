<?php

namespace Tests\Feature\Seguridad;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

class TransporteApiTest extends SeguridadTestCase
{
    public function test_el_conductor_inicia_registra_gps_y_pasajeros_y_finaliza_el_viaje(): void
    {
        $sede = $this->sedeA();
        $conductor = $this->crearUsuario();
        $pasajero = $this->crearUsuario([], 'Secreta123', [], $sede)->persona;

        $marca = DB::table('marca_vehiculo')->insertGetId(['nombre_mav' => 'Marca prueba', 'created_at' => now(), 'updated_at' => now()], 'id_mav');
        $modelo = DB::table('modelo_vehiculo')->insertGetId(['nombre_mov' => 'Modelo prueba', 'mav_id' => $marca, 'created_at' => now(), 'updated_at' => now()], 'id_mov');
        $combustible = DB::table('tipo_combustible')->insertGetId(['nombre_tcb' => 'Gasoil', 'created_at' => now(), 'updated_at' => now()], 'id_tcb');
        $vehiculo = DB::table('vehiculo_transporte')->insertGetId([
            'placa_vtr' => 'PRB' . random_int(1000, 9999), 'anio_vtr' => 2020, 'capacidad_pasajeros_vtr' => 30,
            'capacidad_tanque_litros_vtr' => 200, 'nivel_combustible_actual_vtr' => 150, 'consumo_urbano_vtr' => 0.3,
            'consumo_carretera_vtr' => 0.2, 'consumo_ralenti_vtr' => 2, 'km_actual_vtr' => 1000,
            'mov_id' => $modelo, 'tcb_id' => $combustible, 'sed_id' => $sede, 'created_at' => now(), 'updated_at' => now(),
        ], 'id_vtr');
        $ruta = DB::table('ruta_transporte')->insertGetId(['nombre_rtr' => 'Ruta prueba', 'distancia_km_rtr' => 12, 'sed_id' => $sede, 'created_at' => now(), 'updated_at' => now()], 'id_rtr');
        $parada = DB::table('parada_transporte')->insertGetId(['nombre_ptr' => 'Parada prueba', 'lat_ptr' => 9.0, 'lng_ptr' => -69.2, 'created_at' => now(), 'updated_at' => now()], 'id_ptr');
        DB::table('ruta_parada_transporte')->insert(['rtr_id' => $ruta, 'ptr_id' => $parada, 'orden_rpt' => 1, 'created_at' => now(), 'updated_at' => now()]);
        $viaje = DB::table('viaje_transporte')->insertGetId([
            'vtr_id' => $vehiculo, 'rtr_id' => $ruta, 'conductor_use_id' => $conductor->id_use,
            'fecha_inicio_vjt' => now(), 'estado_viaje_vjt' => 'programado', 'created_at' => now(), 'updated_at' => now(),
        ], 'id_vjt');

        Sanctum::actingAs($conductor);

        $this->getJson('/api/mi-viaje-activo')
            ->assertOk()
            ->assertJsonPath('data.id', $viaje)
            ->assertJsonPath('data.bus_ruta.paradas.0.pivot.orden', 1)
            ->assertJsonStructure(['data' => ['vehiculo_id', 'bus_ruta_id', 'conductor_id', 'km_inicio', 'estado', 'vehiculo' => ['placa', 'tipo_combustible']]]);

        $this->postJson("/api/viajes/{$viaje}/iniciar", ['pasajeros' => 0])
            ->assertOk()
            ->assertJsonPath('data.estado', 'en_curso');

        $local = (string) Str::uuid();
        $gps = ['local_id' => $local, 'lat' => 9.01, 'lng' => -69.21, 'velocidad' => 30, 'heading' => 90, 'timestamp' => now()->toIso8601String()];
        $this->postJson("/api/viajes/{$viaje}/gps", $gps)->assertOk()->assertJsonPath('success', true);
        $this->postJson("/api/viajes/{$viaje}/gps", $gps)->assertOk()->assertJsonPath('duplicate', true);
        $this->postJson("/api/viajes/{$viaje}/gps", array_merge($gps, ['local_id' => (string) Str::uuid(), 'lat' => 9.05, 'timestamp' => now()->addMinute()->toIso8601String()]))->assertOk();

        $this->postJson("/api/viajes/{$viaje}/pasajeros", ['cedula' => $pasajero->cedula_per, 'bus_parada_id' => $parada, 'metodo' => 'manual'])
            ->assertOk()
            ->assertJsonPath('data.persona_id', $pasajero->id_per)
            ->assertJsonPath('data.pasajeros_total', 1);

        $this->getJson("/api/viajes/{$viaje}/posicion")
            ->assertOk()
            ->assertJsonStructure(['success', 'latitud', 'longitud', 'velocidad', 'pasajeros', 'estado']);

        $this->postJson("/api/viajes/{$viaje}/finalizar")
            ->assertOk()
            ->assertJsonStructure(['data' => ['distancia_km', 'litros_gastados', 'viaje' => ['estado'], 'vehiculo' => ['km_actual', 'nivel_combustible_actual']]]);

        $this->assertSame('finalizado', DB::table('viaje_transporte')->where('id_vjt', $viaje)->value('estado_viaje_vjt'));
        $this->assertSame(2, DB::table('log_gps_transporte')->where('vjt_id', $viaje)->count());
        $this->assertSame(1, DB::table('pasajero_viaje_transporte')->where('vjt_id', $viaje)->count());
    }
}
