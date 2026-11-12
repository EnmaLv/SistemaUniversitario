<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\TipoProducto;
use App\Models\salud\EnvasePrimario;
use App\Support\Inventario\PrecioVigente;
use App\Support\Sedes\SedeActiva;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

abstract class ProductoPorTipoController extends Controller
{
    abstract protected function tipos(): array;

    abstract protected function config(): array;

    protected function tiposDelModulo(): array
    {
        return array_values(array_filter(array_map(
            fn ($cfg) => TipoProducto::idPorNombre($cfg['nombre']),
            $this->tipos()
        )));
    }

    protected function tiposRegistrados(): array
    {
        $tipos = [];

        foreach ($this->tipos() as $clave => $datos) {
            $id = TipoProducto::idPorNombre($datos['nombre']);

            if ($id !== null) {
                $tipos[$clave] = $datos + ['id' => $id, 'clave' => $clave];
            }
        }

        abort_if($tipos === [], 404, 'Los tipos de producto de esta pantalla no están registrados.');

        return $tipos;
    }

    protected function tipoIds(): array
    {
        return array_values(array_column($this->tiposRegistrados(), 'id'));
    }

    protected function claveDeTipo(?int $tipoId): ?string
    {
        foreach ($this->tiposRegistrados() as $clave => $tipo) {
            if ($tipo['id'] === $tipoId) {
                return $clave;
            }
        }

        return null;
    }

    protected function datosVista(array $extra = []): array
    {
        return array_merge(['config' => $this->config(), 'tipos' => $this->tiposRegistrados()], $extra);
    }

    public function index(Request $request)
    {
        $tipos = $this->tiposRegistrados();
        $tipoElegido = is_string($request->input('tipo')) && isset($tipos[$request->input('tipo')]) ? $request->input('tipo') : null;
        $tipoIds = $tipoElegido === null ? $this->tipoIds() : [$tipos[$tipoElegido]['id']];
        $sedeId = SedeActiva::id();
        $activo = (int) $request->input('activo', 1) === 1 ? Producto::STATUS_ACTIVO : Producto::STATUS_INACTIVO;
        $filtroSede = fn ($consulta) => $sedeId === null ? $consulta : $consulta->where('sed_id', $sedeId);

        $categorias = Categoria::select('id_cap', 'nombre_cap', 'tpr_id')
            ->where('status_id', Categoria::STATUS_ACTIVO)
            ->whereIn('tpr_id', $tipoIds)
            ->orderBy('nombre_cap')
            ->get();

        $categoriaElegida = $request->filled('categoria') && $categorias->contains('id_cap', (int) $request->input('categoria'))
            ? (int) $request->input('categoria')
            : null;

        $productos = Producto::with(['categoria', 'unidad', 'presentacion', 'precioProducto'])
            ->select('producto.*')
            ->join('categoria_producto', 'categoria_producto.id_cap', '=', 'producto.cap_id')
            ->whereIn('categoria_producto.tpr_id', $tipoIds)
            ->where('producto.status_id', $activo)
            ->when($categoriaElegida !== null, fn ($q) => $q->where('producto.cap_id', $categoriaElegida))
            ->when($request->filled('buscar'), function ($q) use ($request) {
                $buscar = '%' . mb_strtolower((string) $request->input('buscar')) . '%';
                $q->where(fn ($sub) => $sub->whereRaw('LOWER(producto.nombre_pdt) LIKE ?', [$buscar])->orWhereRaw('LOWER(producto.codigo_pdt) LIKE ?', [$buscar]));
            })
            ->withSum(['totales as existencia' => $filtroSede], 'cantidad_total_int')
            ->withSum(['totales as existencia_convertida' => $filtroSede], 'cantidad_convertida_total_int')
            ->orderBy('producto.nombre_pdt')
            ->paginate(10)
            ->withQueryString();

        $sedeNombre = $sedeId === null ? 'todas las sedes' : DB::table('sede')->where('id_sed', $sedeId)->value('nombre_sed');
        $tasa = PrecioVigente::tasa();

        return view('admin.maestros.productos_tipo.index', $this->datosVista(compact('productos', 'categorias', 'sedeNombre', 'tasa', 'tipoElegido', 'categoriaElegida')));
    }

    public function create(Request $request)
    {
        $tipo = is_string($request->input('tipo')) && isset($this->tiposRegistrados()[$request->input('tipo')]) ? $request->input('tipo') : null;

        return view('admin.maestros.productos_tipo.create', $this->datosVista($this->datosFormulario() + ['producto' => null, 'tipoInicial' => $tipo]));
    }

    public function store(Request $request)
    {
        $datos = $this->validar($request);
        $etiqueta = $this->tiposRegistrados()[$request->input('tipo')]['etiqueta'];
        $datos['imagen'] = $request->hasFile('imagen')
            ? $request->file('imagen')->store('imagenes/productos', 'public')
            : 'imagenes/productos/product-defect.webp';

        try {
            DB::transaction(fn () => Producto::crearProducto($datos));
        } catch (\Throwable $e) {
            Log::error('Error al crear producto por tipo', ['tipo' => $request->input('tipo'), 'error' => $e->getMessage()]);

            return back()->withInput()->with('error', 'No se pudo guardar: ' . \App\Support\Seguridad\MensajeError::de($e));
        }

        return redirect()->route($this->config()['ruta'] . '.index', ['tipo' => $request->input('tipo')])
            ->with('success', $etiqueta . ' registrado. Ya puedes incluirlo en las compras.');
    }

    public function edit($producto)
    {
        $modelo = $this->buscar($producto);

        return view('admin.maestros.productos_tipo.edit', $this->datosVista($this->datosFormulario() + [
            'producto'    => $modelo,
            'tipoInicial' => $this->claveDeTipo(TipoProducto::idDeProducto((int) $modelo->id_pdt)),
        ]));
    }

    public function update(Request $request, $producto)
    {
        $modelo = $this->buscar($producto);
        $datos = $this->validar($request, (int) $modelo->id_pdt);
        $datos['estado'] = (int) $modelo->status_id === Producto::STATUS_ACTIVO ? 1 : 0;
        $datos['codigo'] = $modelo->codigo_pdt;

        if ($request->hasFile('imagen')) {
            $datos['imagen'] = $request->file('imagen')->store('imagenes/productos', 'public');
        }

        try {
            DB::transaction(fn () => Producto::actualizarProducto($modelo->id_pdt, $datos));
        } catch (\Throwable $e) {
            Log::error('Error al actualizar producto por tipo', ['tipo' => $request->input('tipo'), 'error' => $e->getMessage()]);

            return back()->withInput()->with('error', 'No se pudo guardar: ' . \App\Support\Seguridad\MensajeError::de($e));
        }

        return redirect()->route($this->config()['ruta'] . '.index')->with('success', 'Producto actualizado.');
    }

    public function destroy($producto)
    {
        $modelo = $this->buscar($producto);
        Producto::eliminarProducto($modelo->id_pdt);

        return redirect()->route($this->config()['ruta'] . '.index')->with('success', 'Producto inactivado: ya no aparecerá en las compras.');
    }

    public function activar($producto)
    {
        $modelo = $this->buscar($producto);
        Producto::activarProducto($modelo->id_pdt);

        return redirect()->route($this->config()['ruta'] . '.index', ['activo' => 1])->with('success', 'Producto activado.');
    }

    protected function buscar($producto): Producto
    {
        return Producto::with(['precioProducto', 'presentacion', 'unidad'])
            ->select('producto.*')
            ->join('categoria_producto', 'categoria_producto.id_cap', '=', 'producto.cap_id')
            ->whereIn('categoria_producto.tpr_id', $this->tipoIds())
            ->where('producto.id_pdt', (int) $producto)
            ->firstOrFail();
    }

    protected function unidadId(): int
    {
        $id = TipoProducto::idUnidadDelArea();

        if ($id === null) {
            throw ValidationException::withMessages([
                'nombre' => 'Falta la unidad de medida "' . TipoProducto::UNIDAD_DEL_AREA . '". Pide al administrador que la registre.',
            ]);
        }

        return $id;
    }

    protected function datosFormulario(): array
    {
        $tipoIds = $this->tipoIds();
        $envases = [];

        foreach ($this->tiposRegistrados() as $clave => $tipo) {
            $envases[$clave] = EnvasePrimario::deTipos([$tipo['id']])->get();
        }

        return [
            'categorias' => Categoria::select('id_cap', 'nombre_cap', 'tpr_id')
                ->where('status_id', Categoria::STATUS_ACTIVO)
                ->whereIn('tpr_id', $tipoIds)
                ->orderBy('nombre_cap')
                ->get(),
            'envases'    => $envases,
        ];
    }

    protected function validar(Request $request, ?int $productoId = null): array
    {
        $tipos = $this->tiposRegistrados();
        $tipoId = is_string($request->input('tipo')) && isset($tipos[$request->input('tipo')]) ? $tipos[$request->input('tipo')]['id'] : 0;

        $datos = $request->validate([
            'tipo'                      => ['required', Rule::in(array_keys($tipos))],
            'nombre'                    => ['required', 'string', 'max:255', Rule::unique('producto', 'nombre_pdt')->ignore($productoId, 'id_pdt')],
            'categoria_id'              => ['required', Categoria::reglaDeTipos([$tipoId])],
            'presentacion_id'           => ['nullable', EnvasePrimario::reglaDeTipos([$tipoId])],
            'unidades_presentacion_pdt' => 'nullable|required_with:presentacion_id|integer|min:2',
            'costo_usd_ppr'             => 'required|numeric|min:0',
            'stock_minimo_pdt'          => 'required|integer|min:0',
            'stock_maximo_pdt'          => 'required|integer|min:0|gte:stock_minimo_pdt',
            'descripcion'               => 'nullable|string|max:1000',
            'imagen'                    => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ], [
            'tipo.required'                           => 'Elige primero el tipo: ' . implode(' o ', array_map('mb_strtolower', array_column($tipos, 'etiqueta'))) . '.',
            'tipo.in'                                 => 'Elige un tipo de la lista.',
            'nombre.required'                         => 'Escribe el nombre.',
            'nombre.unique'                           => 'Ya existe un producto con ese nombre.',
            'categoria_id.required'                   => 'Elige una categoría.',
            'categoria_id.exists'                     => 'Elige una categoría del tipo que marcaste.',
            'presentacion_id.exists'                  => 'Elige un tipo de caja o paquete de la lista.',
            'unidades_presentacion_pdt.required_with' => 'Indica cuántas unidades trae cada caja o paquete.',
            'unidades_presentacion_pdt.integer'       => 'Las unidades por caja deben ser un número entero.',
            'unidades_presentacion_pdt.min'           => 'Una caja o paquete debe traer al menos 2 unidades. Si viene suelto, desmarca "Se compra por caja o paquete".',
            'costo_usd_ppr.required'                  => 'Escribe el precio de compra en dólares.',
            'stock_minimo_pdt.required'               => 'Escribe el stock mínimo (puede ser 0).',
            'stock_maximo_pdt.required'               => 'Escribe el stock máximo (puede ser 0).',
            'stock_maximo_pdt.gte'                    => 'El stock máximo no puede ser menor que el mínimo.',
        ]);

        if (empty($datos['presentacion_id'])) {
            $datos['presentacion_id'] = null;
            $datos['unidades_presentacion_pdt'] = 1;
        }

        $datos['unidad_id'] = $this->unidadId();
        $datos['peso_contenido_pdt'] = 0;
        $datos['presentacion_dispensacion_pdt'] = null;
        $datos['estado'] = 1;
        unset($datos['imagen'], $datos['tipo']);

        return $datos;
    }
}
