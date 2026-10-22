<?php

namespace Tests\Feature\Seguridad;

use App\Models\Rol;
use App\Models\Usuario;
use App\Support\Inventario\FiltroTipoProducto;
use App\Support\Inventario\ServicioInventario;
use App\Support\Permisos\VersionPermisos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FiltroTipoProductoTest extends SeguridadTestCase
{
    private Usuario $admin;

    private int $medicamento;

    private int $alimento;

    protected function setUp(): void
    {
        parent::setUp();

        $this->medicamento = $this->tipo('Medicamento', 'salud');
        $this->alimento = $this->tipo('Alimento', 'comedor');

        $this->admin = $this->crearUsuario('Administrador');
        $this->actingAs($this->admin)->withSession(['master_key_validated' => true, 'ultima_actividad' => time(), 'sede_activa' => $this->sedeA()]);
    }

    private function tipo(string $nombre, string $modulo): int
    {
        $id = DB::table('tipo_producto')->where('nombre_tpr', $nombre)->value('id_tpr');

        if ($id) {
            return (int) $id;
        }

        return DB::table('tipo_producto')->insertGetId([
            'nombre_tpr' => $nombre,
            'mod_id'     => DB::table('modulo')->where('key_mod', $modulo)->value('id_mod'),
            'created_at' => now(),
            'updated_at' => now(),
        ], 'id_tpr');
    }

    private function producto(int $tipo, string $nombre): int
    {
        $categoria = DB::table('categoria_producto')->insertGetId([
            'nombre_cap' => 'Cat ' . $nombre, 'tpr_id' => $tipo, 'created_at' => now(), 'updated_at' => now(),
        ], 'id_cap');

        return DB::table('producto')->insertGetId([
            'codigo_pdt'         => 'FTP-' . random_int(100000, 999999),
            'nombre_pdt'         => $nombre,
            'stock_minimo_pdt'   => 1,
            'stock_maximo_pdt'   => 100,
            'peso_contenido_pdt' => 1000,
            'unm_id'             => DB::table('unidad_medida')->orderBy('id_unm')->value('id_unm'),
            'cap_id'             => $categoria,
            'created_at'         => now(),
            'updated_at'         => now(),
        ], 'id_pdt');
    }

    private function lote(int $producto, string $codigo): int
    {
        return DB::table('lote')->insertGetId([
            'codigo_lote_lot'       => $codigo,
            'fecha_entrada_lot'     => now()->toDateString(),
            'fecha_vencimiento_lot' => now()->addMonths(6)->toDateString(),
            'pdt_id'                => $producto,
            'created_at'            => now(),
            'updated_at'            => now(),
        ], 'id_lot');
    }

    private function escenario(): array
    {
        $sufijo = Str::upper(Str::random(5));
        $medicina = $this->producto($this->medicamento, 'Ibuprofeno ' . $sufijo);
        $comida = $this->producto($this->alimento, 'Arroz ' . $sufijo);

        $loteMedicina = $this->lote($medicina, 'FTPMED-' . $sufijo);
        $loteComida = $this->lote($comida, 'FTPALI-' . $sufijo);

        $servicio = app(ServicioInventario::class);
        $servicio->entrada($loteMedicina, $this->sedeA(), 5);
        $servicio->entrada($loteComida, $this->sedeA(), 7);

        return [
            'sufijo'       => $sufijo,
            'medicina'     => 'Ibuprofeno ' . $sufijo,
            'comida'       => 'Arroz ' . $sufijo,
            'loteMedicina' => 'FTPMED-' . $sufijo,
            'loteComida'   => 'FTPALI-' . $sufijo,
        ];
    }

    private function usuarioConModulos(array $modulos): Usuario
    {
        $rol = Rol::create(['nombre_rol' => 'Filtro ' . Str::random(6), 'slug_rol' => 'filtro-' . Str::lower(Str::random(6))]);
        $rol->modulos()->sync(DB::table('modulo')->whereIn('key_mod', $modulos)->pluck('id_mod')->all());
        VersionPermisos::invalidar();

        $usuario = $this->crearUsuario();
        $usuario->roles()->sync([$rol->id_rol]);

        return $usuario->fresh();
    }

    private function resolverPara(Usuario $usuario, array $consulta = []): ?int
    {
        $this->actingAs($usuario);
        $request = Request::create('/admin/maestros/productos', 'GET', $consulta);
        $request->setUserResolver(fn () => $usuario);

        return FiltroTipoProducto::resolver($request);
    }

    public function test_productos_filtra_por_tipo(): void
    {
        $datos = $this->escenario();

        $this->get(route('admin.maestros.productos.index', ['buscar' => $datos['sufijo'], 'tipo' => $this->medicamento]))
            ->assertOk()
            ->assertSee($datos['medicina'])
            ->assertDontSee($datos['comida']);

        $this->get(route('admin.maestros.productos.index', ['buscar' => $datos['sufijo'], 'tipo' => FiltroTipoProducto::TODOS]))
            ->assertOk()
            ->assertSee($datos['medicina'])
            ->assertSee($datos['comida']);
    }

    public function test_lotes_filtra_por_tipo(): void
    {
        $datos = $this->escenario();

        $this->get(route('admin.movimientos.lotes.index', ['buscar' => 'FTP', 'tipo' => $this->alimento]))
            ->assertOk()
            ->assertSee($datos['loteComida'])
            ->assertDontSee($datos['loteMedicina']);
    }

    public function test_inventario_por_sede_filtra_lotes_y_totales_por_tipo(): void
    {
        $datos = $this->escenario();

        $this->get(route('admin.movimientos.sedes_lotes.show', [$this->sedeA(), 'buscar' => $datos['sufijo'], 'tipo' => $this->medicamento]))
            ->assertOk()
            ->assertSee($datos['loteMedicina'])
            ->assertDontSee($datos['loteComida'])
            ->assertSee($datos['medicina'])
            ->assertDontSee($datos['comida']);

        $respuesta = $this->get(route('admin.movimientos.sedes_lotes.show', [$this->sedeA(), 'tipo' => $this->medicamento]))->assertOk();
        $totales = $respuesta->viewData('totales');
        $this->assertTrue($totales->contains(fn ($total) => $total->producto?->nombre_pdt === $datos['medicina']));
        $this->assertFalse($totales->contains(fn ($total) => $total->producto?->nombre_pdt === $datos['comida']));
    }

    public function test_el_indice_por_sede_conserva_el_tipo_en_los_enlaces(): void
    {
        $this->get(route('admin.movimientos.sedes_lotes', ['tipo' => $this->medicamento]))
            ->assertOk()
            ->assertSee('sedes_lotes/show/' . $this->sedeA() . '?tipo=' . $this->medicamento, false);
    }

    public function test_movimientos_filtra_por_tipo_y_conserva_el_tipo_en_la_exportacion(): void
    {
        $datos = $this->escenario();

        $respuesta = $this->get(route('admin.movimientos.historial_movimientos.index', ['buscar' => 'FTP', 'tipo' => $this->medicamento]))
            ->assertOk()
            ->assertSee($datos['medicina'])
            ->assertDontSee($datos['comida']);

        $this->assertMatchesRegularExpression(
            '/data-url="[^"]*historial_movimientos\/export-pdf\?[^"]*tipo=' . $this->medicamento . '[^0-9]/',
            $respuesta->getContent()
        );

        $productosDelTipo = DB::table('producto')
            ->join('categoria_producto', 'categoria_producto.id_cap', '=', 'producto.cap_id')
            ->where('categoria_producto.tpr_id', $this->medicamento)
            ->pluck('producto.id_pdt')
            ->map(fn ($id) => (int) $id);

        $movimientos = $respuesta->viewData('movimiento');
        $this->assertNotEmpty($movimientos->items());
        $this->assertTrue($movimientos->getCollection()->every(fn ($movimiento) => $productosDelTipo->contains((int) $movimiento->pdt_id)));
    }

    public function test_la_paginacion_conserva_el_tipo(): void
    {
        $sufijo = Str::upper(Str::random(5));

        for ($i = 0; $i < 11; $i++) {
            $this->producto($this->medicamento, 'Pagina ' . $sufijo . ' ' . $i);
        }

        $productos = $this->get(route('admin.maestros.productos.index', ['buscar' => 'Pagina ' . $sufijo, 'tipo' => $this->medicamento]))
            ->assertOk()
            ->viewData('productos');

        $this->assertTrue($productos->hasPages());
        $this->assertStringContainsString('tipo=' . $this->medicamento, $productos->nextPageUrl());

        $lotes = $this->get(route('admin.movimientos.lotes.index'))
            ->assertOk()
            ->viewData('lotes');

        $this->assertStringContainsString('tipo=' . FiltroTipoProducto::TODOS, $lotes->url(2));
    }

    public function test_usuario_solo_de_salud_arranca_con_medicamento(): void
    {
        $medico = $this->usuarioConModulos(['salud']);

        $this->assertSame($this->medicamento, $this->resolverPara($medico));
        $this->assertNull($this->resolverPara($medico, ['tipo' => FiltroTipoProducto::TODOS]));
        $this->assertSame($this->alimento, $this->resolverPara($medico, ['tipo' => (string) $this->alimento]));
    }

    public function test_usuario_solo_de_comedor_arranca_con_alimento(): void
    {
        $this->assertSame($this->alimento, $this->resolverPara($this->usuarioConModulos(['comedor'])));
    }

    public function test_un_modulo_sin_tipo_no_cuenta_para_el_valor_inicial(): void
    {
        $this->assertSame($this->medicamento, $this->resolverPara($this->usuarioConModulos(['salud', 'psicologia'])));
    }

    public function test_usuario_con_varios_modulos_o_superadmin_arranca_en_todos(): void
    {
        $this->assertNull($this->resolverPara($this->usuarioConModulos(['salud', 'comedor'])));
        $this->assertNull($this->resolverPara($this->admin));
    }

    public function test_las_opciones_incluyen_los_tipos_sembrados(): void
    {
        $nombres = FiltroTipoProducto::opciones()->pluck('nombre_tpr')->all();

        foreach (['Alimento', 'Medicamento', 'Implemento deportivo', 'Material de oficina'] as $nombre) {
            $this->assertContains($nombre, $nombres);
        }
    }
}
