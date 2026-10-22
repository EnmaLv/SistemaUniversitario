<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\TipoProducto;
use App\Support\Inventario\PrecioVigente;
use App\Support\Sedes\SedeActiva;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

abstract class ProductoPorTipoController extends Controller
{
    abstract protected function tipo(): string;

    abstract protected function config(): array;

    protected function tipoId(): int
    {
        $id = TipoProducto::idPorNombre($this->tipo());

        abort_if($id === null, 404, 'El tipo de producto "' . $this->tipo() . '" no está registrado.');

        return $id;
    }

    protected function datosVista(array $extra = []): array
    {
        return array_merge(['config' => $this->config()], $extra);
    }

    public function index(Request $request)
    {
        $tipoId = $this->tipoId();
        $sedeId = SedeActiva::id();
        $activo = (int) $request->input('activo', 1) === 1 ? Producto::STATUS_ACTIVO : Producto::STATUS_INACTIVO;
        $filtroSede = fn ($consulta) => $sedeId === null ? $consulta : $consulta->where('sed_id', $sedeId);

        $productos = Producto::with(['categoria', 'unidad', 'presentacion', 'precioProducto'])
            ->select('producto.*')
            ->join('categoria_producto', 'categoria_producto.id_cap', '=', 'producto.cap_id')
            ->where('categoria_producto.tpr_id', $tipoId)
            ->where('producto.status_id', $activo)
            ->when($request->filled('categoria'), fn ($q) => $q->where('producto.cap_id', (int) $request->categoria))
            ->when($request->filled('buscar'), function ($q) use ($request) {
                $buscar = $request->buscar;
                $q->where(fn ($sub) => $sub->where('producto.nombre_pdt', 'like', "%{$buscar}%")->orWhere('producto.codigo_pdt', 'like', "%{$buscar}%"));
            })
            ->withSum(['totales as existencia' => $filtroSede], 'cantidad_total_int')
            ->withSum(['totales as existencia_convertida' => $filtroSede], 'cantidad_convertida_total_int')
            ->orderBy('producto.nombre_pdt')
            ->paginate(10)
            ->withQueryString();

        $categorias = Categoria::select('id_cap', 'nombre_cap')
            ->where('status_id', Categoria::STATUS_ACTIVO)
            ->where('tpr_id', $tipoId)
            ->orderBy('nombre_cap')
            ->get();

        $sedeNombre = $sedeId === null ? 'todas las sedes' : DB::table('sede')->where('id_sed', $sedeId)->value('nombre_sed');
        $tasa = PrecioVigente::tasa();

        return view('admin.maestros.productos_tipo.index', $this->datosVista(compact('productos', 'categorias', 'sedeNombre', 'tasa')));
    }

    public function create()
    {
        return view('admin.maestros.productos_tipo.create', $this->datosVista($this->datosFormulario() + ['producto' => null]));
    }

    public function store(Request $request)
    {
        $datos = $this->validar($request);
        $datos['imagen'] = $request->hasFile('imagen')
            ? $request->file('imagen')->store('imagenes/productos', 'public')
            : 'imagenes/productos/product-defect.webp';

        try {
            DB::transaction(fn () => Producto::crearProducto($datos));
        } catch (\Throwable $e) {
            Log::error('Error al crear producto por tipo', ['tipo' => $this->tipo(), 'error' => $e->getMessage()]);

            return back()->withInput()->with('error', 'No se pudo guardar: ' . \App\Support\Seguridad\MensajeError::de($e));
        }

        return redirect()->route($this->config()['ruta'] . '.index')->with('success', $this->config()['singular'] . ' registrado. Ya puedes incluirlo en las compras.');
    }

    public function edit($producto)
    {
        $modelo = $this->buscar($producto);

        return view('admin.maestros.productos_tipo.edit', $this->datosVista($this->datosFormulario() + ['producto' => $modelo]));
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
            Log::error('Error al actualizar producto por tipo', ['tipo' => $this->tipo(), 'error' => $e->getMessage()]);

            return back()->withInput()->with('error', 'No se pudo guardar: ' . \App\Support\Seguridad\MensajeError::de($e));
        }

        return redirect()->route($this->config()['ruta'] . '.index')->with('success', $this->config()['singular'] . ' actualizado.');
    }

    public function destroy($producto)
    {
        $modelo = $this->buscar($producto);
        Producto::eliminarProducto($modelo->id_pdt);

        return redirect()->route($this->config()['ruta'] . '.index')->with('success', $this->config()['singular'] . ' inactivado: ya no aparecerá en las compras.');
    }

    public function activar($producto)
    {
        $modelo = $this->buscar($producto);
        Producto::activarProducto($modelo->id_pdt);

        return redirect()->route($this->config()['ruta'] . '.index', ['activo' => 1])->with('success', $this->config()['singular'] . ' activado.');
    }

    protected function buscar($producto): Producto
    {
        return Producto::with(['precioProducto', 'presentacion', 'unidad'])
            ->select('producto.*')
            ->join('categoria_producto', 'categoria_producto.id_cap', '=', 'producto.cap_id')
            ->where('categoria_producto.tpr_id', $this->tipoId())
            ->where('producto.id_pdt', (int) $producto)
            ->firstOrFail();
    }

    protected function datosFormulario(): array
    {
        $datos = Producto::getDatosFormulario($this->tipoId());

        return [
            'categorias' => $datos['categorias'],
            'unidades'   => $datos['unidades'],
            'envases'    => $datos['envases'],
        ];
    }

    protected function validar(Request $request, ?int $productoId = null): array
    {
        $datos = $request->validate([
            'nombre'                    => ['required', 'string', 'max:255', Rule::unique('producto', 'nombre_pdt')->ignore($productoId, 'id_pdt')],
            'categoria_id'              => ['required', Categoria::reglaDeTipos([$this->tipoId()])],
            'unidad_id'                 => 'required|exists:unidad_medida,id_unm',
            'presentacion_id'           => 'nullable|exists:envase_primario,id_enp',
            'unidades_presentacion_pdt' => 'nullable|required_with:presentacion_id|integer|min:1',
            'costo_usd_ppr'             => 'required|numeric|min:0',
            'stock_minimo_pdt'          => 'required|integer|min:0',
            'stock_maximo_pdt'          => 'required|integer|min:0|gte:stock_minimo_pdt',
            'descripcion'               => 'nullable|string|max:1000',
            'imagen'                    => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ], [
            'nombre.required'                         => 'Escribe el nombre.',
            'nombre.unique'                           => 'Ya existe un producto con ese nombre.',
            'categoria_id.required'                   => 'Elige una categoría.',
            'categoria_id.exists'                     => 'Elige una categoría de la lista: solo se aceptan las de ' . mb_strtolower($this->config()['plural']) . '.',
            'unidad_id.required'                      => 'Elige la unidad de medida.',
            'unidades_presentacion_pdt.required_with' => 'Indica cuántas unidades trae cada caja o paquete.',
            'unidades_presentacion_pdt.integer'       => 'Las unidades por caja deben ser un número entero.',
            'costo_usd_ppr.required'                  => 'Escribe el precio de compra en dólares.',
            'stock_minimo_pdt.required'               => 'Escribe el stock mínimo (puede ser 0).',
            'stock_maximo_pdt.required'               => 'Escribe el stock máximo (puede ser 0).',
            'stock_maximo_pdt.gte'                    => 'El stock máximo no puede ser menor que el mínimo.',
        ]);

        if (empty($datos['presentacion_id'])) {
            $datos['presentacion_id'] = null;
            $datos['unidades_presentacion_pdt'] = 1;
        }

        $datos['peso_contenido_pdt'] = 0;
        $datos['presentacion_dispensacion_pdt'] = null;
        $datos['estado'] = 1;
        unset($datos['imagen']);

        return $datos;
    }
}
