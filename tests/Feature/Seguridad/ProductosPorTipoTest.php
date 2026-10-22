<?php

namespace Tests\Feature\Seguridad;

use App\Models\Rol;
use App\Models\TipoProducto;
use App\Models\Usuario;
use App\Support\Permisos\VersionPermisos;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductosPorTipoTest extends SeguridadTestCase
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

    private function unidad(): int
    {
        return (int) DB::table('unidad_medida')->where('nombre_unm', 'Unidad')->value('id_unm');
    }

    private function categoria(string $tipo): int
    {
        return DB::table('categoria_producto')->insertGetId([
            'nombre_cap' => 'PTT ' . $tipo . ' ' . $this->sufijo,
            'tpr_id'     => TipoProducto::idPorNombre($tipo),
            'status_id'  => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ], 'id_cap');
    }

    private function producto(string $tipo, string $nombre): int
    {
        return DB::table('producto')->insertGetId([
            'codigo_pdt'                => 'PTT-' . random_int(100000, 999999),
            'nombre_pdt'                => $nombre . ' ' . $this->sufijo,
            'peso_contenido_pdt'        => 0,
            'unidades_presentacion_pdt' => 1,
            'stock_minimo_pdt'          => 1,
            'stock_maximo_pdt'          => 10,
            'unm_id'                    => $this->unidad(),
            'cap_id'                    => $this->categoria($tipo),
            'status_id'                 => 1,
            'created_at'                => now(),
            'updated_at'                => now(),
        ], 'id_pdt');
    }

    private function datosArea(int $categoria, string $nombre): array
    {
        return [
            'nombre'           => $nombre . ' ' . $this->sufijo,
            'categoria_id'     => $categoria,
            'unidad_id'        => $this->unidad(),
            'costo_usd_ppr'    => 5,
            'stock_minimo_pdt' => 1,
            'stock_maximo_pdt' => 10,
        ];
    }

    private function datosMedicamento(int $categoria, string $nombre): array
    {
        return $this->datosArea($categoria, $nombre) + ['unidades_presentacion_pdt' => 10];
    }

    private function datosAlimento(int $categoria, string $nombre): array
    {
        return $this->datosArea($categoria, $nombre) + ['peso_contenido_pdt' => 1];
    }

    private function existe(string $nombre): bool
    {
        return DB::table('producto')->where('nombre_pdt', mb_strtoupper($nombre . ' ' . $this->sufijo))->exists();
    }

    public function test_los_medicamentos_sembrados_estan_en_categorias_de_medicamento(): void
    {
        $codigos = ['ANT-ACE-001', 'ANT-LOR-006', 'ANT-IBU-008', 'ANA-ATA-001', 'ANT-BRU-002'];
        $productos = DB::table('producto')->whereIn('codigo_pdt', $codigos)->pluck('id_pdt', 'codigo_pdt');

        $this->assertCount(count($codigos), $productos);

        foreach ($productos as $codigo => $id) {
            $this->assertTrue(TipoProducto::productoEsDeTipo((int) $id, TipoProducto::MEDICAMENTO), "{$codigo} no está en una categoría de medicamentos.");
        }

        $this->get(route('admin.deportes.implementos.index'))
            ->assertOk()
            ->assertDontSee('ATAMEL 500 MG')
            ->assertDontSee('BRUGESIC 400 MG')
            ->assertDontSee('IBUPROFENO 800 MG');
    }

    public function test_los_productos_sembrados_del_comedor_quedan_en_categorias_de_alimento(): void
    {
        $alimentos = DB::table('producto')->where('codigo_pdt', 'like', 'CER-%')->orWhere('codigo_pdt', 'like', 'LAC-%')->pluck('id_pdt');

        $this->assertNotEmpty($alimentos);

        foreach ($alimentos as $id) {
            $this->assertTrue(TipoProducto::productoEsDeTipo((int) $id, TipoProducto::ALIMENTO));
        }

        $ingredientes = DB::table('receta_ingrediente')->pluck('pdt_id')->unique();

        foreach ($ingredientes as $id) {
            $this->assertTrue(TipoProducto::productoEsDeTipo((int) $id, TipoProducto::ALIMENTO), "El ingrediente {$id} no es un alimento.");
        }
    }

    public function test_cada_listado_muestra_solo_productos_de_su_tipo(): void
    {
        $this->producto(TipoProducto::MEDICAMENTO, 'PTT MEDICINA');
        $this->producto(TipoProducto::IMPLEMENTO_DEPORTIVO, 'PTT BALON');
        $this->producto(TipoProducto::MATERIAL_OFICINA, 'PTT LAPIZ');

        $this->get(route('admin.deportes.implementos.index', ['buscar' => $this->sufijo]))
            ->assertOk()
            ->assertSee('PTT BALON ' . $this->sufijo)
            ->assertSee('PTT LAPIZ ' . $this->sufijo)
            ->assertDontSee('PTT MEDICINA ' . $this->sufijo);

        $this->get(route('admin.deportes.implementos.index', ['buscar' => $this->sufijo, 'tipo' => 'deportivo']))
            ->assertOk()
            ->assertSee('PTT BALON ' . $this->sufijo)
            ->assertDontSee('PTT MEDICINA ' . $this->sufijo)
            ->assertDontSee('PTT LAPIZ ' . $this->sufijo);

        $this->get(route('admin.deportes.implementos.index', ['buscar' => $this->sufijo, 'tipo' => 'oficina']))
            ->assertOk()
            ->assertSee('PTT LAPIZ ' . $this->sufijo)
            ->assertDontSee('PTT MEDICINA ' . $this->sufijo)
            ->assertDontSee('PTT BALON ' . $this->sufijo);

        $this->get(route('admin.salud.maestros.medicamentos.index', ['buscar' => $this->sufijo]))
            ->assertOk()
            ->assertSee('PTT MEDICINA ' . $this->sufijo)
            ->assertDontSee('PTT BALON ' . $this->sufijo)
            ->assertDontSee('PTT LAPIZ ' . $this->sufijo);
    }

    public function test_al_crear_se_rechaza_una_categoria_de_otro_tipo(): void
    {
        $medicamento = $this->categoria(TipoProducto::MEDICAMENTO);
        $implemento = $this->categoria(TipoProducto::IMPLEMENTO_DEPORTIVO);
        $alimento = $this->categoria(TipoProducto::ALIMENTO);

        $this->post(route('admin.deportes.implementos.store'), $this->datosArea($medicamento, 'PTT IMPL MAL') + ['tipo' => 'deportivo'])
            ->assertSessionHasErrors('categoria_id');
        $this->assertFalse($this->existe('PTT IMPL MAL'));

        $this->post(route('admin.deportes.implementos.store'), $this->datosArea($implemento, 'PTT IMPL BIEN') + ['tipo' => 'deportivo'])
            ->assertSessionHasNoErrors();
        $this->assertTrue($this->existe('PTT IMPL BIEN'));

        $this->post(route('admin.salud.maestros.medicamentos.store'), $this->datosMedicamento($implemento, 'PTT MED MAL'))
            ->assertSessionHasErrors('categoria_id');
        $this->assertFalse($this->existe('PTT MED MAL'));

        $this->post(route('admin.salud.maestros.medicamentos.store'), $this->datosMedicamento($medicamento, 'PTT MED BIEN'))
            ->assertSessionHasNoErrors();
        $this->assertTrue($this->existe('PTT MED BIEN'));

        $this->post(route('admin.maestros.productos.store'), $this->datosAlimento($implemento, 'PTT ALI MAL') + ['tipo' => 'alimento'])
            ->assertSessionHasErrors('categoria_id');
        $this->post(route('admin.maestros.productos.store'), $this->datosAlimento($medicamento, 'PTT ALI MAL') + ['tipo' => 'alimento'])
            ->assertSessionHasErrors('categoria_id');
        $this->assertFalse($this->existe('PTT ALI MAL'));

        $this->post(route('admin.maestros.productos.store'), $this->datosAlimento($alimento, 'PTT ALI BIEN') + ['tipo' => 'alimento'])
            ->assertSessionHasNoErrors();
        $this->assertTrue($this->existe('PTT ALI BIEN'));
    }

    public function test_al_editar_se_rechaza_una_categoria_de_otro_tipo(): void
    {
        $medicamento = $this->categoria(TipoProducto::MEDICAMENTO);
        $implemento = $this->categoria(TipoProducto::IMPLEMENTO_DEPORTIVO);

        $balon = $this->producto(TipoProducto::IMPLEMENTO_DEPORTIVO, 'PTT BALON');
        $categoriaBalon = DB::table('producto')->where('id_pdt', $balon)->value('cap_id');
        $this->put(route('admin.deportes.implementos.update', $balon), $this->datosArea($medicamento, 'PTT BALON') + ['tipo' => 'deportivo'])
            ->assertSessionHasErrors('categoria_id');
        $this->assertSame((int) $categoriaBalon, (int) DB::table('producto')->where('id_pdt', $balon)->value('cap_id'));

        $medicina = $this->producto(TipoProducto::MEDICAMENTO, 'PTT MEDICINA');
        $this->put(route('admin.salud.maestros.medicamentos.update', $medicina), $this->datosMedicamento($implemento, 'PTT MEDICINA'))
            ->assertSessionHasErrors('categoria_id');
        $this->assertSame(TipoProducto::idPorNombre(TipoProducto::MEDICAMENTO), TipoProducto::idDeProducto($medicina));

        $arroz = $this->producto(TipoProducto::ALIMENTO, 'PTT ARROZ');
        $this->put(route('admin.maestros.productos.update', $arroz), $this->datosAlimento($implemento, 'PTT ARROZ'))
            ->assertSessionHasErrors('categoria_id');
        $this->assertSame(TipoProducto::idPorNombre(TipoProducto::ALIMENTO), TipoProducto::idDeProducto($arroz));
    }

    public function test_cada_pantalla_solo_abre_productos_de_su_tipo(): void
    {
        $balon = $this->producto(TipoProducto::IMPLEMENTO_DEPORTIVO, 'PTT BALON');
        $medicina = $this->producto(TipoProducto::MEDICAMENTO, 'PTT MEDICINA');

        $this->get(route('admin.salud.maestros.medicamentos.edit', $balon))->assertNotFound();
        $this->get(route('admin.salud.maestros.medicamentos.show', $balon))->assertNotFound();
        $this->get(route('admin.deportes.implementos.edit', $medicina))->assertNotFound();
        $this->get(route('admin.maestros.productos.edit', $balon))->assertRedirect(route('admin.deportes.implementos.edit', $balon));
        $this->get(route('admin.maestros.productos.edit', $medicina))->assertOk();
    }

    public function test_productos_del_comedor_solo_muestra_alimentos(): void
    {
        $vista = DB::table('vista')->where('key_vis', 'comedor_productos')->value('id_vis');
        $rol = Rol::create(['nombre_rol' => 'Comedor ' . Str::random(6), 'slug_rol' => 'comedor-' . Str::lower(Str::random(6))]);
        $rol->permisos()->sync(DB::table('permiso')->where('vis_id', $vista)->pluck('id_pmi'));
        $rol->modulos()->sync(DB::table('modulo')->where('key_mod', 'comedor')->pluck('id_mod')->all());
        VersionPermisos::invalidar();

        $usuario = $this->crearUsuario();
        $usuario->roles()->sync([$rol->id_rol]);
        $usuario = $usuario->fresh();

        $this->producto(TipoProducto::ALIMENTO, 'PTT ARROZ');
        $medicina = $this->producto(TipoProducto::MEDICAMENTO, 'PTT MEDICINA');

        $this->actingAs($usuario)->withSession(['ultima_actividad' => time(), 'sede_activa' => $this->sedeA()]);

        $this->get(route('admin.maestros.productos.index', ['buscar' => $this->sufijo, 'tipo' => TipoProducto::idPorNombre(TipoProducto::MEDICAMENTO)]))
            ->assertOk()
            ->assertSee('PTT ARROZ ' . $this->sufijo)
            ->assertDontSee('PTT MEDICINA ' . $this->sufijo);

        $this->get(route('admin.maestros.productos.edit', $medicina))->assertNotFound();
    }
}
