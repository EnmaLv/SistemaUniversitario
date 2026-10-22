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
use App\Models\TipoProducto;
use Illuminate\Support\Facades\Http;

class MedicamentoController extends Controller
{

    public function index(Request $request)
    {
        $activo = $request->input('activo', 1);
        $categoria = $request->input('categoria', null);

        $tipoId = $this->tipoId();
        $productos = Producto::listarProductos($request->buscar, $activo, $categoria, 10, null, null, $tipoId);

        $categorias = Categoria::select('id_cap', 'nombre_cap')
            ->where('status_id', Categoria::STATUS_ACTIVO)
            ->where('tpr_id', $tipoId)
            ->get();

        $sedeId = \App\Support\Sedes\SedeActiva::id();
        $sedeNombre = $sedeId === null
            ? 'Todas las sedes'
            : DB::table('sede')->where('id_sed', $sedeId)->value('nombre_sed');

        return view('admin.salud.maestros.medicamentos.index', compact('productos', 'categorias', 'sedeNombre'));
    }

    public function create()
    {
        $datos = Producto::getDatosFormulario($this->tipoId());

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
                ->with('error', 'Error al crear el medicamento: ' . \App\Support\Seguridad\MensajeError::de($e));
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

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $medicamento = Producto::with(['categoria', 'unidad', 'presentacion'])->findOrFail($this->buscarMedicamento($id)->id_pdt);

        return view('admin.salud.maestros.medicamentos.show', compact('medicamento'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $medicamento = $this->buscarMedicamento($id);

        $datos = Producto::getDatosFormulario($this->tipoId());

        return view('admin.salud.maestros.medicamentos.edit', array_merge($datos, [
            'medicamento' => $medicamento
        ]));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(MedicamentoRequest $request, $id)
    {
        $id = $this->buscarMedicamento($id)->id_pdt;

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
                ->with('error', 'Error al actualizar el medicamento: ' . \App\Support\Seguridad\MensajeError::de($e));
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        Producto::eliminarProducto($this->buscarMedicamento($id)->id_pdt);

        return redirect()
            ->route('admin.salud.maestros.medicamentos.index')
            ->with('success', 'Medicamento inactivado exitosamente.'); 
    }


    public function activar($id)
    {
        Producto::activarProducto($this->buscarMedicamento($id)->id_pdt);
        return redirect()->route('admin.salud.maestros.medicamentos.index')->with('success', 'Medicamento activado exitosamente.');
    }

    private function tipoId(): int
    {
        $id = TipoProducto::idPorNombre(TipoProducto::MEDICAMENTO);

        abort_if($id === null, 404, 'El tipo de producto "' . TipoProducto::MEDICAMENTO . '" no está registrado.');

        return $id;
    }

    private function buscarMedicamento($id): Producto
    {
        $producto = Producto::findOrFail((int) $id);

        abort_unless(TipoProducto::idDeProducto((int) $producto->id_pdt) === $this->tipoId(), 404);

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
