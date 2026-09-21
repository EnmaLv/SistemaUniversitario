<?php

namespace App\Http\Controllers\salud;

use App\Models\salud\Medicamento;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\MedicamentoRequest;
use App\Models\Categoria;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Log;
use App\Models\ExchangeRates;
use App\Models\Producto;
use Illuminate\Support\Facades\Http;

class MedicamentoController extends Controller
{

    public function index(Request $request)
    {
        $activo = $request->input('activo', 1);
        $categoria = $request->input('categoria', null);

        $productos = Producto::listarProductos($request->buscar, $activo, $categoria, 10, null, null, 2);

        $categorias = Categoria::select('id', 'nombre')
            ->where('activo', 1)
            ->where('tipo_producto_id', 2)
            ->get();

        return view('admin.salud.maestros.medicamentos.index', compact('productos', 'categorias'));
    }

    public function create()
    {
        $datos = Producto::getDatosFormulario(2);

        $datos['envases'] = DB::table('envase_primarios')
            ->select('id', 'nombre')
            ->orderBy('nombre', 'asc')
            ->get();

        return view('admin.salud.maestros.medicamentos.create', $datos);
    }

    public function store(MedicamentoRequest $request)
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
                    ->with('success', 'Medicamento creado exitosamente.');
            } else {
                return redirect()->route('admin.salud.maestros.medicamentos.index')
                    ->with('success', 'Medicamento creado y precio actualizado Exitosamente.');
            }
        } catch (\Exception $e) {

            DB::rollBack();
            Log::error('Error al crear medicamento', ['error' => $e->getMessage()]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error al crear el medicamento: ' . $e->getMessage());
        }
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
                    'nombre'         => 'Oficial',
                    'fecha_vigencia' => $fechaVigencia
                ],
                [
                    'fuente'   => $data['fuente'],
                    'promedio' => $data['promedio'],
                ]
            );

            $productos = Producto::with('precioProducto')->get();

            foreach ($productos as $producto) {
                if (!$producto->precioProducto) {
                    continue;
                }

                $precioUSD = $producto->precioProducto->precio_usd
                    ?? $producto->precioProducto->costo_usd;

                if (!$precioUSD || $precioUSD <= 0) {
                    continue;
                }

                $margen = $producto->precioProducto->margen ?? 0;

                $precioBs = round(
                    $precioUSD * (1 + $margen / 100) * $tasa->promedio,
                    2
                );

                DB::table('productos')
                    ->where('id', $producto->id)
                    ->update([
                        'precio_compra' => $precioBs,
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
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with(
                'error',
                'Ocurrió un error al actualizar la tasa.'
            );
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $medicamento = Producto::with(['categoria', 'unidad', 'presentacion'])->findOrFail($id);

        return view('admin.salud.maestros.medicamentos.show', compact('medicamento'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $medicamento = Producto::findOrFail($id);

        $datos = Producto::getDatosFormulario(2);

        $datos['envases'] = DB::table('envase_primarios')
            ->select('id', 'nombre')
            ->orderBy('nombre', 'asc')
            ->get();

        return view('admin.salud.maestros.medicamentos.edit', array_merge($datos, [
            'medicamento' => $medicamento
        ]));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(MedicamentoRequest $request, $id)
    {
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
                ->route('admin.salud.maestros.medicamentos.index')
                ->with('success', 'Medicamento actualizado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar medicamento', ['error' => $e->getMessage()]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error al actualizar el medicamento: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        Producto::eliminarProducto($id);

        return redirect()
            ->route('admin.salud.maestros.medicamentos.index')
            ->with('success', 'Medicamento inactivado exitosamente.'); 
    }


    public function activar($id)
    {
        Producto::activarProducto($id);
        return redirect()->route('admin.salud.maestros.medicamentos.index')->with('success', 'Medicamento activado exitosamente.');
    }

    private function procesarTasaYPrecios(?int $productoId = null)
    {
        $tasa = ExchangeRates::orderByDesc('fecha_vigencia')->first();

        if (!$tasa) {
            throw new \Exception('No existe una tasa registrada');
        }

        $query = Producto::with('precioProducto');

        if ($productoId) {
            $query->where('id', $productoId);
        }

        $productos = $query->get();

        foreach ($productos as $producto) {

            if (!$producto->precioProducto) {
                continue;
            }

            $precioUSD =
                $producto->precioProducto->costo_usd ??
                $producto->precioProducto->precio_usd ??
                0;

            if ($precioUSD <= 0) {
                continue;
            }

            $margen = $producto->precioProducto->margen ?? 0;

            $precioBs = round(
                $precioUSD * (1 + $margen / 100) * $tasa->promedio,
                2
            );
    
            DB::table('productos')
                ->where('id', $producto->id)
                ->update([
                    'precio_compra' => $precioBs,
                    'updated_at'    => now()
                ]);
        }
    }
}
