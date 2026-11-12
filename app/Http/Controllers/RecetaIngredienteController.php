<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Receta;
use App\Models\Producto;
use App\Models\Unidad;
use App\Models\RecetaIngrediente;
use App\Models\TipoProducto;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RecetaIngredienteController extends Controller
{
    public function index(Request $request)
    {
        $buscar = $request->input('buscar');
        $estado = $request->input('estado', 1);

        $query = Receta::with('recetaIngredientes');

        if ($buscar) {
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre_rct', 'like', "%{$buscar}%");
            });
        }

        if ($estado !== null && $estado !== '') {
            $query->where('status_id', (int) $estado === 1 ? Receta::STATUS_ACTIVO : Receta::STATUS_INACTIVO);
        } else {
            $query->where('status_id', Receta::STATUS_ACTIVO);
        }

        $recetas = $query->orderBy('id_rct', 'desc')->paginate(10)->withQueryString();

        return view('admin.maestros.receta_ingredientes.index', compact('recetas', 'buscar', 'estado'));
    }

    private function productosAlimento()
    {
        return Producto::activos()
            ->whereIn('id_pdt', TipoProducto::idsProductosDeTipo(TipoProducto::ALIMENTO))
            ->get();
    }

    public function create()
    {
        $recetas = Receta::activos()->get();
        $productos = $this->productosAlimento();
        $unidades = Unidad::all();
        return view('admin.maestros.receta_ingredientes.create', compact('recetas', 'productos', 'unidades'));
    }

    public function store(Request $request)
    {
        $idsAlimento = TipoProducto::idsProductosDeTipo(TipoProducto::ALIMENTO);
        $validated = $request->validate([
            'rct_id' => 'required|exists:receta,id_rct',

            // arrays
            'producto_id' => 'required|array|min:1',
                'producto_id.*' => [
                    'required',
                    Rule::exists('producto', 'id_pdt')
                        ->whereIn('id_pdt', $idsAlimento)
                        ->whereNull('deleted_at'),
                ],

            'cantidad_porcion_rci' => 'nullable|array',
            'cantidad_porcion_rci.*' => 'nullable|numeric',

            'cantidad_convertida_rci' => 'nullable|array',
            'cantidad_convertida_rci.*' => 'nullable|numeric',

            'unidad_id' => 'required|array',
            'unidad_id.*' => 'required|exists:unidad_medida,id_unm',
        ]);

        DB::beginTransaction();
        try {
            foreach ($validated['producto_id'] as $index => $productoId) {
                $cantidad = $validated['cantidad_porcion_rci'][$index] ?? null;
                $unidadId = $validated['unidad_id'][$index] ?? null;

                $unidad = Unidad::find($unidadId);
                $cantidadGramos = $cantidad * $unidad->factor_conversion_unm;

                if (!$cantidad || !$unidadId) continue;

                RecetaIngrediente::create([
                    'rct_id' => $validated['rct_id'],
                    'pdt_id' => $productoId,
                    'cantidad_porcion_rci' => $cantidad,
                    'cantidad_convertida_rci' => $cantidadGramos,
                    'unm_id' => $unidadId,
                ]);
            }

            DB::commit();
            return redirect()->route('admin.maestros.receta_ingredientes.index')->with('success', 'Ingredientes agregados Exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Error: ' . \App\Support\Seguridad\MensajeError::de($e));
        }
    }

    public function edit($recetaId)
    {
        $receta = Receta::with('recetaIngredientes.producto', 'recetaIngredientes.unidad')
            ->findOrFail($recetaId);

        $productos = $this->productosAlimento();
        $unidades = Unidad::all();

        return view('admin.maestros.receta_ingredientes.edit', compact(
            'receta',
            'productos',
            'unidades'
        ));
    }

    public function update(Request $request, $recetaId)
    {
        $idsAlimento = TipoProducto::idsProductosDeTipo(TipoProducto::ALIMENTO);
        $validated = $request->validate([
            'rct_id' => 'required|exists:receta,id_rct',
            'producto_id' => 'nullable|array',
                'producto_id.*' => [
                'required',
                Rule::exists('producto', 'id_pdt')
                    ->whereIn('id_pdt', $idsAlimento)
                    ->whereNull('deleted_at'),
            ],
            'cantidad_porcion_rci' => 'nullable|array',
            'cantidad_porcion_rci.*' => 'required|numeric|min:0.0001',
            'unidad_id' => 'nullable|array',
            'unidad_id.*' => 'required|exists:unidad_medida,id_unm',
        ]);

        if ((int)$validated['rct_id'] !== (int)$recetaId) {
            return redirect()->back()->withInput()->with('error', 'Id de receta inválido.');
        }

        DB::beginTransaction();
        try {
            RecetaIngrediente::where('rct_id', $recetaId)->delete();

            foreach ($validated['producto_id'] ?? [] as $index => $productoId) {
                $cantidad = $validated['cantidad_porcion_rci'][$index] ?? null;
                $unidadId = $validated['unidad_id'][$index] ?? null;

                $unidad = Unidad::find($unidadId);
                $cantidadGramos = $cantidad * $unidad->factor_conversion_unm;


                if (!$cantidad || !$unidadId) continue;

                RecetaIngrediente::create([
                    'rct_id' => $recetaId,
                    'pdt_id' => $productoId,
                    'cantidad_porcion_rci' => $cantidad,
                    'cantidad_convertida_rci' => $cantidadGramos,
                    'unm_id' => $unidadId,
                ]);
            }

            DB::commit();
            return redirect()->route('admin.maestros.receta_ingredientes.index')
                ->with('success', 'Ingredientes de la receta actualizados Exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Error: ' . \App\Support\Seguridad\MensajeError::de($e));
        }
    }

    public function destroy($recetaId)
    {
        RecetaIngrediente::where('rct_id', $recetaId)->delete();

        return redirect()
            ->route('admin.maestros.receta_ingredientes.index')
            ->with('success', 'Ingredientes de la receta eliminados Exitosamente.');
    }
}
