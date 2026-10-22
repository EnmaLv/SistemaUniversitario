<?php

namespace Tests\Feature\Seguridad;

use App\Models\Compra;
use App\Models\Perfil;
use App\Models\Persona;
use App\Models\salud\Consultorio;
use App\Support\Sedes\SedeActiva;
use Illuminate\Support\Facades\DB;

class MultisedeTest extends SeguridadTestCase
{
    private function crearEstudiante(string $apellido, int $sede): Persona
    {
        return Persona::create([
            'nombres_per'   => 'ESTUDIANTE',
            'apellidos_per' => $apellido,
            'cedula_per'   => (string) random_int(10000000, 29999999),
            'telefono_per' => '04140000000',
            'genero_per'   => 'FEMENINO',
            'correo_per'    => strtolower($apellido) . '@prueba.test',
            'id_perfil'        => Perfil::idEstudiante(),
            'sed_id'          => $sede,
        ]);
    }

    private function crearCompra(int $sede): int
    {
        $proveedor = DB::table('proveedor')->insertGetId([
            'nombre_contacto_prv' => 'Proveedor ' . $sede,
            'empresa_prv'         => 'Empresa ' . $sede,
            'telefono_prv'        => '0424-0000000',
            'email_prv'           => 'proveedor' . random_int(1, 99999) . '@prueba.test',
            'direccion_prv'       => 'Dirección',
            'created_at'          => now(),
            'updated_at'          => now(),
        ], 'id_prv');

        return Compra::crearCompra([
            'proveedor_id' => $proveedor,
            'modulo_id'    => null,
            'sed_id'      => $sede,
            'fecha'        => now(),
        ]);
    }

    public function test_un_usuario_de_la_sede_a_no_ve_personas_de_la_sede_b(): void
    {
        $this->crearEstudiante('DESEDEA', $this->sedeA());
        $this->crearEstudiante('DESEDEB', $this->sedeB());

        $this->actingAs($this->crearUsuario('Obrero', sedeId: $this->sedeA()))
            ->get(route('admin.configuracion.persona.index'))
            ->assertOk()
            ->assertSee('DESEDEA')
            ->assertDontSee('DESEDEB');
    }

    public function test_un_superadmin_con_todas_las_sedes_ve_ambas_y_puede_elegir_una(): void
    {
        $this->crearEstudiante('DESEDEA', $this->sedeA());
        $this->crearEstudiante('DESEDEB', $this->sedeB());
        $admin = $this->crearUsuario('Administrador', sedeId: $this->sedeA());

        $this->actingAs($admin)
            ->get(route('admin.configuracion.persona.index'))
            ->assertOk()
            ->assertSee('DESEDEA')
            ->assertSee('DESEDEB');

        $this->post(route('sede.activa.cambiar'), ['sed_id' => $this->sedeB()])->assertRedirect();
        $this->assertSame($this->sedeB(), session(SedeActiva::CLAVE_SESION));

        $this->get(route('admin.configuracion.persona.index'))
            ->assertOk()
            ->assertSee('DESEDEB')
            ->assertDontSee('DESEDEA');

        $this->post(route('sede.activa.cambiar'), ['sed_id' => ''])->assertRedirect();
        $this->assertNull(session(SedeActiva::CLAVE_SESION));
    }

    public function test_un_usuario_normal_no_puede_cambiar_de_sede(): void
    {
        $usuario = $this->crearUsuario('Obrero', sedeId: $this->sedeA());

        $this->actingAs($usuario)
            ->withSession([SedeActiva::CLAVE_SESION => $this->sedeB()])
            ->post(route('sede.activa.cambiar'), ['sed_id' => $this->sedeB()])
            ->assertForbidden();

        $this->assertSame($this->sedeA(), SedeActiva::id());
    }

    public function test_los_registros_nuevos_toman_la_sede_activa_y_las_consultas_se_filtran(): void
    {
        $usuarioA = $this->crearUsuario('Obrero', sedeId: $this->sedeA());
        Consultorio::create(['nombre_csr' => 'CONSULTORIO B', 'sed_id' => $this->sedeB(), 'status_id' => 1]);

        $this->actingAs($usuarioA);

        $nuevo = Consultorio::create(['nombre_csr' => 'CONSULTORIO A', 'status_id' => 1]);

        $this->assertSame($this->sedeA(), (int) $nuevo->sed_id);
        $this->assertSame(['CONSULTORIO A'], Consultorio::pluck('nombre_csr')->all());
        $this->assertSame(2, Consultorio::todasLasSedes()->whereIn('nombre_csr', ['CONSULTORIO A', 'CONSULTORIO B'])->count());
    }

    public function test_un_usuario_de_la_sede_a_no_abre_compras_de_la_sede_b(): void
    {
        $compraA = $this->crearCompra($this->sedeA());
        $compraB = $this->crearCompra($this->sedeB());

        $this->actingAs($this->crearUsuario('Obrero', sedeId: $this->sedeA()));

        $this->assertNotNull(Compra::obtenerCompra($compraA));
        $this->assertNull(Compra::obtenerCompra($compraB));
        $this->assertSame([$compraA], collect(Compra::listarCompras()->items())->pluck('id_cmp')->intersect([$compraA, $compraB])->values()->all());
    }
}
