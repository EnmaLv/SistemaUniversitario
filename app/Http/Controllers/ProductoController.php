<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use App\Models\TipoProducto;
use Illuminate\Http\Request;
use App\Models\ExchangeRates;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\ProductoRequest;
use App\Models\Modulo;
use App\Support\Inventario\FiltroTipoProducto;
use Illuminate\Support\Facades\Log;

class ProductoController extends Controller
{

    protected function tiposDelModulo(): array
    {
        $modulo = $this->moduloActual();

        if ($modulo !== null) {
            $tipos = TipoProducto::idsParaModulo((int) $modulo->id_mod);

            if ($tipos !== []) {
                return $tipos;
            }
        }

        return array_values(array_filter([
            TipoProducto::idPorNombre(TipoProducto::ALIMENTO),
            TipoProducto::idPorNombre(TipoProducto::MEDICAMENTO),
        ]));
    }

    protected function moduloActual(): ?Modulo
    {
        if ($id = session('modulo_actual_id')) {
            $modulo = Modulo::whereKey((int) $id)->first();
            if ($modulo) {
                return $modulo;
            }
        }

        $param = request()->route('modulo') ?? request()->input('modulo');
        if ($param !== null && $param !== '') {
            $modulo = is_numeric($param)
                ? Modulo::whereKey((int) $param)->first()
                : Modulo::where('key_mod', $param)->whereNull('deleted_at')->first();

            if ($modulo) {
                return $modulo;
            }
        }

        $routeName = (string) (request()->route()?->getName() ?? '');
        if ($routeName !== '') {
            $partes = explode('.', $routeName);
            if (($partes[0] ?? null) === 'admin' && ! empty($partes[1])) {
                $modulo = Modulo::where('key_mod', $partes[1])
                    ->whereNull('deleted_at')
                    ->first();

                if ($modulo) {
                    return $modulo;
                }
            }
        }

        return null;
    }

    private function tiposPermitidosParaIndex(): array
    {
        $porPermiso = $this->tiposPermitidos();
        $delModulo  = $this->tiposDelModulo();

        if ($porPermiso === null) {
            return $delModulo;
        }

        return array_values(array_intersect($porPermiso, $delModulo));
    }

    private const PANTALLA_POR_TIPO = [
        TipoProducto::IMPLEMENTO_DEPORTIVO => 'admin.deportes.implementos.edit',
        TipoProducto::MATERIAL_OFICINA     => 'admin.deportes.implementos.edit',
    ];

    public function index(Request $request)
    {
        $activo    = $request->input('activo', 1);
        $categoria = $request->input('categoria', null);

        $permitidos = $this->tiposPermitidosParaIndex();

        abort_if(
            $permitidos === [],
            404,
            'No hay tipos de producto disponibles para este módulo.'
        );

        $tipoProducto = FiltroTipoProducto::resolver($request);
        if (! in_array($tipoProducto, $permitidos, true)) {
            $tipoProducto = $permitidos[0];
        }

        $tiposProducto = FiltroTipoProducto::opciones()
            ->filter(fn ($tipo) => in_array((int) $tipo->id_tpr, $permitidos, true))
            ->values();

        $categorias = Categoria::select('id_cap', 'nombre_cap')
            ->where('status_id', Categoria::STATUS_ACTIVO)
            ->when($tipoProducto !== null, fn ($q) => $q->where('tpr_id', $tipoProducto))
            ->get();

        if ($categoria !== null && $categoria !== '' && ! $categorias->contains('id_cap', (int) $categoria)) {
            $categoria = null;
        }

        $productos = Producto::listarProductos(
                $request->buscar, $activo, $categoria, 10, null, null, $tipoProducto
            )
            ->appends(FiltroTipoProducto::PARAMETRO, FiltroTipoProducto::valorUrl($tipoProducto));

        return view('admin.maestros.productos.index',
            compact('productos', 'categorias', 'tipoProducto', 'tiposProducto'));
    }

    public function create(Request $request)
    {
        $tipoProductoId = ProductoRequest::tipoFormulario($request->input('tipo'));

        if (! in_array($tipoProductoId, ProductoRequest::tiposFormulario($request->user()), true)) {
            $tipoProductoId = TipoProducto::idPorNombre(TipoProducto::ALIMENTO);
        }

        $datos = Producto::getDatosFormulario($tipoProductoId);
        $datos['esMedicamento'] = $tipoProductoId === TipoProducto::idPorNombre(TipoProducto::MEDICAMENTO);

        return view('admin.maestros.productos.create', $datos);
    }

    public function store(ProductoRequest $request)
    {
        DB::beginTransaction();

        try {
            $validated = $request->validated();

            if ($request->hasFile('imagen')) {
                $validated['imagen'] = $request->file('imagen')
                    ->store('imagenes/productos', 'public');
            } else {
                $validated['imagen'] = 'imagenes/productos/product-defect.webp';
            }

            $productoId = Producto::crearProducto($validated);

            $this->procesarTasaYPrecios($productoId);

            DB::commit();

            $from = $request->input('from');

            if ($from) {
                return redirect($from . '?productoId=' . $productoId)
                    ->with('success', 'Producto creado exitosamente.');
            } else {
                return redirect()->route('admin.maestros.productos.index')
                    ->with('success', 'Producto creado y precio actualizado Exitosamente.');
            }
        } catch (\Exception $e) {

            DB::rollBack();
            Log::error('Error al crear producto', ['error' => $e->getMessage()]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error al crear el producto: ' . \App\Support\Seguridad\MensajeError::de($e));
        }
    }



    public function show($id)
    {
        $producto = Producto::with(['categoria', 'unidad'])->findOrFail($this->buscarPermitido($id)->id_pdt);

        return view('admin.maestros.productos.show', compact('producto'));
    }

    public function edit(Request $request, $id)
    {
        $producto = $this->buscarPermitido($id);
        $tipoProductoId = TipoProducto::idDeProducto((int) $producto->id_pdt);
        $nombreTipo = $tipoProductoId === null ? null : TipoProducto::whereKey($tipoProductoId)->value('nombre_tpr');

        if ($nombreTipo !== null && isset(self::PANTALLA_POR_TIPO[$nombreTipo])) {
            return redirect()->route(self::PANTALLA_POR_TIPO[$nombreTipo], $producto->id_pdt);
        }

        $datos = Producto::getDatosFormulario($tipoProductoId);
        $datos['esMedicamento'] = $nombreTipo === TipoProducto::MEDICAMENTO;

        return view('admin.maestros.productos.edit', array_merge($datos, [
            'producto' => $producto,
        ]));
    }

    public function update(ProductoRequest $request, $id)
    {
        $id = $this->buscarPermitido($id)->id_pdt;

        DB::beginTransaction();

        try {
            $validated = $request->validated();

            if ($request->hasFile('imagen')) {
                $validated['imagen'] = $request->file('imagen')
                    ->store('imagenes/productos', 'public');
            }

            Producto::actualizarProducto($id, $validated);

            $this->procesarTasaYPrecios($id);

            DB::commit();

            return redirect()
                ->route('admin.maestros.productos.index')
                ->with('success', 'Producto actualizado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar producto', ['error' => $e->getMessage()]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error al actualizar el producto: ' . \App\Support\Seguridad\MensajeError::de($e));
        }
    }

    public function destroy($id)
    {
        $id = $this->buscarPermitido($id)->id_pdt;

        if (Producto::tieneInventarioEnAcarigua($id)) {

            $cantidad = Producto::cantidadEnAcarigua($id);

            return redirect()
                ->route('admin.maestros.productos.index')
                ->with(
                    'error',
                    "No se puede eliminar el producto porque aún tiene " . round($cantidad) . " unidad(es) en el inventario."
                )
                ->with('icono', 'error');
        }

        Producto::eliminarProducto($id);

        return redirect()
            ->route('admin.maestros.productos.index')
            ->with('success', 'Producto inactivado exitosamente.');
    }


    public function activar($id)
    {
        Producto::activarProducto($this->buscarPermitido($id)->id_pdt);
        return redirect()->route('admin.maestros.productos.index')->with('success', 'Producto activado exitosamente.');
    }

    public function actualizarTasaDolar()
    {
        DB::beginTransaction();

        try {
            $response = Http::get('https://ve.dolarapi.com/v1/dolares/oficial');

            if (!$response->ok()) {
                return redirect()->back()->with('error', 'No se pudo obtener la tasa del dólar.');
            }

            $data = $response->json();

            $fechaVigencia = isset($data['fecha'])
                ? \Carbon\Carbon::parse($data['fecha'])->toDateString()
                : now()->toDateString();

            $tasa = ExchangeRates::updateOrCreate(
                [
                    'nombre_tca'         => 'Oficial',
                    'fecha_vigencia_tca' => $fechaVigencia
                ],
                [
                    'fuente_tca'   => $data['fuente'],
                    'promedio_tca' => $data['promedio'],
                ]
            );

            $productos = Producto::with('precioProducto')->get();

            foreach ($productos as $producto) {
                if (!$producto->precioProducto) {
                    continue;
                }

                $precioUSD = $producto->precioProducto->precio_usd_ppr
                    ?? $producto->precioProducto->costo_usd_ppr;

                if (!$precioUSD || $precioUSD <= 0) {
                    continue;
                }

                $margen = $producto->precioProducto->margen_ppr ?? 0;

                $precioBs = round(
                    $precioUSD * (1 + $margen / 100) * $tasa->promedio_tca,
                    2
                );

                DB::table('producto')
                    ->where('id_pdt', $producto->id_pdt)
                    ->update([
                        'precio_compra_pdt' => $precioBs,
                        'updated_at'    => now()
                    ]);
            }

            DB::commit();

            session()->forget('tasa_pendiente');

            return redirect()->route('home')->with(
                'success',
                'Tasa registrada y precios recalculados Exitosamente.'
            );
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error al actualizar la tasa', [
                'error' => \App\Support\Seguridad\MensajeError::de($e)
            ]);

            return redirect()->back()->with(
                'error',
                'Ocurrió un error al actualizar la tasa.'
            );
        }
    }


    private function tiposPermitidos(): ?array
    {
        $permitidos = TipoProducto::idsVisiblesEnProductos(auth()->user());

        abort_if($permitidos === [], 404, 'El tipo de producto "' . TipoProducto::ALIMENTO . '" no está registrado.');

        return $permitidos;
    }

    private function buscarPermitido($id): Producto
    {
        $producto = Producto::findOrFail((int) $id);
        $permitidos = $this->tiposPermitidos();

        abort_if($permitidos !== null && ! in_array(TipoProducto::idDeProducto((int) $producto->id_pdt), $permitidos, true), 404);

        return $producto;
    }

    private function procesarTasaYPrecios(?int $productoId = null)
    {
        $tasa = ExchangeRates::orderByDesc('fecha_vigencia_tca')->first();

        if (!$tasa) {
            return;
        }

        $query = Producto::with('precioProducto');

        if ($productoId) {
            $query->where('id_pdt', $productoId);
        }

        $productos = $query->get();

        foreach ($productos as $producto) {

            if (!$producto->precioProducto) {
                continue;
            }

            $precioUSD =
                $producto->precioProducto->costo_usd_ppr ??
                $producto->precioProducto->precio_usd_ppr ??
                0;

            if ($precioUSD <= 0) {
                continue;
            }

            $margen = $producto->precioProducto->margen_ppr ?? 0;

            $precioBs = round(
                $precioUSD * (1 + $margen / 100) * $tasa->promedio_tca,
                2
            );

            DB::table('producto')
                ->where('id_pdt', $producto->id_pdt)
                ->update([
                    'precio_compra_pdt' => $precioBs,
                    'updated_at'    => now()
                ]);
        }
    }
}
