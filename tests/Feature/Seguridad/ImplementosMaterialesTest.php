<?php

namespace Tests\Feature\Seguridad;

use App\Livewire\Admin\Movimientos\Compras\ItemsCompra;
use App\Models\Compra;
use App\Models\Rol;
use App\Models\TipoProducto;
use App\Models\Usuario;
use App\Support\Inventario\ServicioInventario;
use App\Support\Permisos\VersionPermisos;
use Database\Seeders\CategoriaSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;

class ImplementosMaterialesTest extends SeguridadTestCase
{
    private const CLAVE = 'implementos_materiales';

    private Usuario $admin;

    private string $sufijo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sufijo = Str::upper(Str::random(6));
        $this->admin = $this->crearUsuario('Administrador');
        $this->entrarComo($this->admin, $this->sedeA());
    }

    private function entrarComo(Usuario $usuario, int $sede): void
    {
        $this->actingAs($usuario)->withSession(['master_key_validated' => true, 'ultima_actividad' => time(), 'sede_activa' => $sede]);
    }

    private function categoria(string $tipo, ?string $nombre = null): int
    {
        return DB::table('categoria_producto')->insertGetId([
            'nombre_cap' => $nombre ?? ('IMM ' . $tipo . ' ' . $this->sufijo),
            'tpr_id'     => TipoProducto::idPorNombre($tipo),
            'status_id'  => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ], 'id_cap');
    }

    private function envase(string $nombre): int
    {
        return (int) DB::table('envase_primario')->whereNull('deleted_at')->where(DB::raw('LOWER(nombre_enp)'), mb_strtolower($nombre))->value('id_enp');
    }

    private function datos(string $tipo, int $categoria, string $nombre, array $extra = []): array
    {
        return array_merge([
            'tipo'             => $tipo,
            'nombre'           => $nombre . ' ' . $this->sufijo,
            'categoria_id'     => $categoria,
            'costo_usd_ppr'    => 3,
            'stock_minimo_pdt' => 1,
            'stock_maximo_pdt' => 50,
        ], $extra);
    }

    private function producto(string $nombre)
    {
        return DB::table('producto')->where('nombre_pdt', mb_strtoupper($nombre . ' ' . $this->sufijo))->first();
    }

    private function crearProducto(string $tipo, string $nombre): int
    {
        return DB::table('producto')->insertGetId([
            'codigo_pdt'                => 'IMM-' . random_int(100000, 999999),
            'nombre_pdt'                => $nombre . ' ' . $this->sufijo,
            'peso_contenido_pdt'        => 0,
            'unidades_presentacion_pdt' => 1,
            'stock_minimo_pdt'          => 1,
            'stock_maximo_pdt'          => 50,
            'unm_id'                    => TipoProducto::idUnidadDelArea(),
            'cap_id'                    => $this->categoria($tipo),
            'status_id'                 => 1,
            'created_at'                => now(),
            'updated_at'                => now(),
        ], 'id_pdt');
    }

    public function test_se_siembran_las_categorias_de_ambos_tipos(): void
    {
        (new CategoriaSeeder())->sembrarComplementarias();

        $nombres = fn (string $tipo) => DB::table('categoria_producto')
            ->where('tpr_id', TipoProducto::idPorNombre($tipo))
            ->pluck('nombre_cap')
            ->all();

        foreach (['Balones', 'Raquetas y mallas', 'Equipos'] as $nombre) {
            $this->assertContains($nombre, $nombres(TipoProducto::IMPLEMENTO_DEPORTIVO));
        }

        foreach (['Escritura', 'Papelería', 'Marcadores'] as $nombre) {
            $this->assertContains($nombre, $nombres(TipoProducto::MATERIAL_OFICINA));
        }
    }

    public function test_se_crea_un_producto_de_cada_tipo_desde_la_misma_vista(): void
    {
        $deportiva = $this->categoria(TipoProducto::IMPLEMENTO_DEPORTIVO);
        $oficina = $this->categoria(TipoProducto::MATERIAL_OFICINA);

        $formulario = $this->get(route('admin.deportes.implementos.create', ['tipo' => 'oficina']))->assertOk();
        $this->assertSame('oficina', $formulario->viewData('tipoInicial'));
        $formulario->assertSee('Implemento deportivo')->assertSee('Material de oficina');

        $this->post(route('admin.deportes.implementos.store'), $this->datos('deportivo', $deportiva, 'IMM CONO'))
            ->assertSessionHasNoErrors();
        $this->post(route('admin.deportes.implementos.store'), $this->datos('oficina', $oficina, 'IMM MARCADOR', [
            'presentacion_id' => $this->envase('CAJA'), 'unidades_presentacion_pdt' => 12,
        ]))->assertSessionHasNoErrors();

        $cono = $this->producto('IMM CONO');
        $marcador = $this->producto('IMM MARCADOR');
        $this->assertTrue(TipoProducto::productoEsDeTipo((int) $cono->id_pdt, TipoProducto::IMPLEMENTO_DEPORTIVO));
        $this->assertTrue(TipoProducto::productoEsDeTipo((int) $marcador->id_pdt, TipoProducto::MATERIAL_OFICINA));
        $this->assertSame(TipoProducto::idUnidadDelArea(), (int) $marcador->unm_id);

        $this->get(route('admin.deportes.implementos.edit', $marcador->id_pdt))
            ->assertOk()
            ->assertViewHas('tipoInicial', 'oficina');
    }

    public function test_el_tipo_es_obligatorio_y_la_categoria_y_la_caja_deben_ser_de_ese_tipo(): void
    {
        $deportiva = $this->categoria(TipoProducto::IMPLEMENTO_DEPORTIVO);
        $oficina = $this->categoria(TipoProducto::MATERIAL_OFICINA);

        $sinTipo = $this->datos('deportivo', $deportiva, 'IMM SIN TIPO');
        unset($sinTipo['tipo']);
        $this->post(route('admin.deportes.implementos.store'), $sinTipo)->assertSessionHasErrors('tipo');
        $this->post(route('admin.deportes.implementos.store'), $this->datos('medicamento', $deportiva, 'IMM RARO'))->assertSessionHasErrors('tipo');
        $this->post(route('admin.deportes.implementos.store'), $this->datos('oficina', $deportiva, 'IMM CRUZADO'))->assertSessionHasErrors('categoria_id');
        $this->post(route('admin.deportes.implementos.store'), $this->datos('deportivo', $deportiva, 'IMM RESMA MAL', [
            'presentacion_id' => $this->envase('RESMA'), 'unidades_presentacion_pdt' => 500,
        ]))->assertSessionHasErrors('presentacion_id');

        foreach (['IMM SIN TIPO', 'IMM RARO', 'IMM CRUZADO', 'IMM RESMA MAL'] as $nombre) {
            $this->assertNull($this->producto($nombre));
        }

        $this->post(route('admin.deportes.implementos.store'), $this->datos('oficina', $oficina, 'IMM PAPEL', [
            'presentacion_id' => $this->envase('RESMA'), 'unidades_presentacion_pdt' => 500,
        ]))->assertSessionHasNoErrors();
        $this->assertNotNull($this->producto('IMM PAPEL'));
    }

    public function test_el_listado_filtra_por_tipo_y_categoria_y_muestra_la_existencia_de_la_sede_activa(): void
    {
        $balon = $this->crearProducto(TipoProducto::IMPLEMENTO_DEPORTIVO, 'IMM BALON');
        $lapiz = $this->crearProducto(TipoProducto::MATERIAL_OFICINA, 'IMM LAPIZ');
        $categoriaLapiz = (int) DB::table('producto')->where('id_pdt', $lapiz)->value('cap_id');
        $lote = DB::table('lote')->insertGetId([
            'codigo_lote_lot' => 'IMM-' . $this->sufijo, 'fecha_entrada_lot' => now()->toDateString(),
            'pdt_id' => $lapiz, 'status_id' => 1, 'created_at' => now(), 'updated_at' => now(),
        ], 'id_lot');
        app(ServicioInventario::class)->entrada($lote, $this->sedeA(), 5, 5);
        app(ServicioInventario::class)->entrada($lote, $this->sedeB(), 7, 7);

        $this->get(route('admin.deportes.implementos.index', ['buscar' => $this->sufijo, 'tipo' => 'oficina', 'categoria' => $categoriaLapiz]))
            ->assertOk()
            ->assertSee('IMM LAPIZ ' . $this->sufijo)
            ->assertDontSee('IMM BALON ' . $this->sufijo);

        $this->get(route('admin.deportes.implementos.index', ['buscar' => $this->sufijo, 'tipo' => 'deportivo', 'categoria' => $categoriaLapiz]))
            ->assertOk()
            ->assertSee('IMM BALON ' . $this->sufijo)
            ->assertDontSee('IMM LAPIZ ' . $this->sufijo);

        $enA = $this->get(route('admin.deportes.implementos.index', ['buscar' => 'IMM LAPIZ ' . $this->sufijo]))->assertOk()->viewData('productos');
        $this->assertEquals(5, (float) $enA->first()->existencia_convertida);

        $this->entrarComo($this->admin, $this->sedeB());
        $enB = $this->get(route('admin.deportes.implementos.index', ['buscar' => 'IMM LAPIZ ' . $this->sufijo]))->assertOk()->viewData('productos');
        $this->assertEquals(7, (float) $enB->first()->existencia_convertida);
        $this->assertNotNull($balon);
    }

    public function test_las_rutas_viejas_de_material_de_oficina_redirigen_a_la_vista_unificada(): void
    {
        $lapiz = $this->crearProducto(TipoProducto::MATERIAL_OFICINA, 'IMM LAPIZ');

        $this->get(route('admin.maestros.material_oficina.index', ['buscar' => 'lapiz']))
            ->assertRedirect(route('admin.deportes.implementos.index', ['buscar' => 'lapiz', 'tipo' => 'oficina']));
        $this->get(route('admin.maestros.material_oficina.create'))
            ->assertRedirect(route('admin.deportes.implementos.create', ['tipo' => 'oficina']));
        $this->get(route('admin.maestros.material_oficina.edit', $lapiz))
            ->assertRedirect(route('admin.deportes.implementos.edit', $lapiz));
        $this->get(route('admin.maestros.productos.edit', $lapiz))
            ->assertRedirect(route('admin.deportes.implementos.edit', $lapiz));
    }

    public function test_compras_ofrece_ambos_tipos_en_administracion_y_deporte(): void
    {
        $balon = $this->crearProducto(TipoProducto::IMPLEMENTO_DEPORTIVO, 'IMM BALON');
        $lapiz = $this->crearProducto(TipoProducto::MATERIAL_OFICINA, 'IMM LAPIZ');
        $proveedor = DB::table('proveedor')->insertGetId([
            'empresa_prv' => 'PROVEEDOR IMM', 'telefono_prv' => '0424', 'email_prv' => 'imm' . random_int(1, 99999) . '@x.test',
            'created_at' => now(), 'updated_at' => now(),
        ], 'id_prv');

        foreach (['administracion', 'deporte'] as $modulo) {
            $compra = Compra::find(Compra::crearCompra([
                'proveedor_id' => $proveedor, 'fecha' => now(), 'sed_id' => $this->sedeA(),
                'modulo_id' => DB::table('modulo')->where('key_mod', $modulo)->value('id_mod'),
            ]));

            $ofrecidos = Livewire::actingAs($this->admin)->test(ItemsCompra::class, ['compra' => $compra])
                ->get('productos')
                ->pluck('id_pdt')
                ->map(fn ($id) => (int) $id);

            $this->assertTrue($ofrecidos->contains($balon), "Compras de {$modulo} no ofrece implementos deportivos.");
            $this->assertTrue($ofrecidos->contains($lapiz), "Compras de {$modulo} no ofrece material de oficina.");
        }
    }

    public function test_la_vista_de_permisos_renombrada_existe_y_las_viejas_no(): void
    {
        $this->assertTrue(DB::table('vista')->where('key_vis', self::CLAVE)->exists());
        $this->assertFalse(DB::table('vista')->where('key_vis', 'deportes_implementos')->exists());
        $this->assertFalse(DB::table('vista')->where('key_vis', 'material_oficina')->exists());

        $rol = Rol::create(['nombre_rol' => 'Material ' . Str::random(6), 'slug_rol' => 'material-' . Str::lower(Str::random(6))]);
        $rol->permisos()->sync(DB::table('permiso')->join('vista', 'vista.id_vis', '=', 'permiso.vis_id')->where('vista.key_vis', self::CLAVE)->where('permiso.accion_pmi', 'ver')->pluck('permiso.id_pmi'));
        VersionPermisos::invalidar();
        $usuario = $this->crearUsuario();
        $usuario->roles()->sync([$rol->id_rol]);
        $usuario = $usuario->fresh();

        $this->entrarComo($usuario, $this->sedeA());
        $this->get(route('admin.deportes.implementos.index'))->assertOk()->assertDontSee(route('admin.deportes.implementos.create'));
        $this->get(route('admin.maestros.material_oficina.index'))->assertRedirect();
        $this->get(route('admin.deportes.implementos.create'))->assertForbidden();
    }

    public function test_la_migracion_conserva_y_absorbe_los_permisos(): void
    {
        $nueva = DB::table('vista')->where('key_vis', self::CLAVE)->first();
        $this->assertNotNull($nueva);
        $verNueva = (int) DB::table('permiso')->where('vis_id', $nueva->id_vis)->where('accion_pmi', 'ver')->value('id_pmi');

        $rolDeportes = Rol::create(['nombre_rol' => 'Deportes ' . Str::random(6), 'slug_rol' => 'deportes-' . Str::lower(Str::random(6))]);
        $rolDeportes->permisos()->sync([$verNueva]);

        DB::table('vista')->where('id_vis', $nueva->id_vis)->update(['key_vis' => 'deportes_implementos', 'nombre_vis' => 'Implementos deportivos']);
        DB::table('permiso')->where('vis_id', $nueva->id_vis)->update(['nombre_pmi' => DB::raw("REPLACE(nombre_pmi, '" . self::CLAVE . "', 'deportes_implementos')")]);

        $oficina = DB::table('vista')->insertGetId([
            'key_vis' => 'material_oficina', 'nombre_vis' => 'Material de oficina', 'orden_vis' => 99,
            'mod_id' => DB::table('modulo')->where('key_mod', 'administracion')->value('id_mod'),
            'created_at' => now(), 'updated_at' => now(),
        ], 'id_vis');
        $permisosOficina = [];

        foreach (['ver', 'crear', 'exportar'] as $accion) {
            $permisosOficina[$accion] = DB::table('permiso')->insertGetId([
                'nombre_pmi' => 'material_oficina.' . $accion, 'etiqueta_pmi' => $accion, 'accion_pmi' => $accion,
                'vis_id' => $oficina, 'created_at' => now(), 'updated_at' => now(),
            ], 'id_pmi');
        }

        $rolOficina = Rol::create(['nombre_rol' => 'Oficina ' . Str::random(6), 'slug_rol' => 'oficina-' . Str::lower(Str::random(6))]);
        $rolOficina->permisos()->sync([$permisosOficina['ver'], $permisosOficina['crear'], $permisosOficina['exportar']]);
        $usuario = $this->crearUsuario();
        DB::table('usuario_permiso')->insert([
            'use_id' => $usuario->id_use, 'pmi_id' => $permisosOficina['crear'], 'tipo_usp' => 'grant',
            'fecha_fin_usp' => now()->addMonth()->toDateString(), 'created_at' => now(), 'updated_at' => now(),
        ]);

        (require database_path('migrations/2026_10_23_000300_unifica_vista_implementos_materiales.php'))->up();

        $vista = DB::table('vista')->where('key_vis', self::CLAVE)->first();
        $this->assertSame((int) $nueva->id_vis, (int) $vista->id_vis);
        $this->assertSame('Implementos y materiales', $vista->nombre_vis);
        $this->assertFalse(DB::table('vista')->whereIn('key_vis', ['deportes_implementos', 'material_oficina'])->exists());
        $this->assertSame(self::CLAVE . '.ver', DB::table('permiso')->where('id_pmi', $verNueva)->value('nombre_pmi'));
        $this->assertTrue($rolDeportes->fresh()->permisos()->where('permiso.id_pmi', $verNueva)->exists());

        $clavesOficina = $rolOficina->fresh()->permisos()->pluck('nombre_pmi')->sort()->values()->all();
        $this->assertSame([self::CLAVE . '.crear', self::CLAVE . '.exportar', self::CLAVE . '.ver'], $clavesOficina);

        $concedido = DB::table('usuario_permiso')
            ->join('permiso', 'permiso.id_pmi', '=', 'usuario_permiso.pmi_id')
            ->where('usuario_permiso.use_id', $usuario->id_use)
            ->first(['permiso.nombre_pmi', 'usuario_permiso.tipo_usp', 'usuario_permiso.fecha_fin_usp']);
        $this->assertSame(self::CLAVE . '.crear', $concedido->nombre_pmi);
        $this->assertSame('grant', $concedido->tipo_usp);
        $this->assertNotNull($concedido->fecha_fin_usp);
        $this->assertSame(1, DB::table('permiso')->where('vis_id', $vista->id_vis)->where('accion_pmi', 'exportar')->count());
    }
}
