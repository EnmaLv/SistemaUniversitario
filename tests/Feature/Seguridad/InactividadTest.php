<?php

namespace Tests\Feature\Seguridad;

use App\Models\Bitacora as BitacoraModelo;

class InactividadTest extends SeguridadTestCase
{
    public function test_inactividad_mayor_al_limite_cierra_la_sesion(): void
    {
        $usuario = $this->crearUsuario('Obrero');

        $this->actingAs($usuario)->get('/seguridad/sesion/estado')->assertOk();
        $this->post('/seguridad/sesion/mantener')->assertOk();

        $this->travel(16)->minutes();

        $this->get('/home')->assertRedirect(route('login'));
        $this->assertGuest();
        $this->assertTrue(BitacoraModelo::where('use_id', $usuario->id_use)->where('tipo_evento_bis', 'sesion_expirada')->exists());
    }

    public function test_actividad_real_renueva_la_sesion(): void
    {
        $usuario = $this->crearUsuario('Obrero');

        $this->actingAs($usuario)->post('/seguridad/sesion/mantener')->assertOk();

        $this->travel(10)->minutes();
        $this->post('/seguridad/sesion/mantener')->assertOk();

        $this->travel(10)->minutes();
        $this->getJson('/seguridad/sesion/estado')->assertOk()->assertJsonPath('segundos_restantes', 300);
        $this->assertAuthenticated();
    }

    public function test_las_rutas_de_polling_no_renuevan_la_actividad(): void
    {
        $usuario = $this->crearUsuario('Obrero');

        $this->actingAs($usuario)->post('/seguridad/sesion/mantener')->assertOk();

        $this->travel(10)->minutes();
        $this->getJson('/mensajes/contactos/lista');
        $this->postJson('/mensajes/ping');
        $this->getJson('/seguridad/sesion/estado')->assertOk()->assertJsonPath('segundos_restantes', 300);

        $this->travel(6)->minutes();

        $this->getJson('/mensajes/contactos/lista')->assertUnauthorized();
        $this->assertGuest();
    }
}
