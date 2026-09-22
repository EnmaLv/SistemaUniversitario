<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Receta;
use App\Models\Producto;
use App\Models\Unidad;
use App\Models\RecetaIngrediente;
use Illuminate\Support\Facades\DB;

class RecetaIngredienteController extends Controller
{
    public function index(Request $request)
    {
        $buscar = $request->input('buscar');
        $estado = $request->input('estado', 1);

        $query = Receta::with('recetaIngredientes');

        if ($buscar) {
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%");
            });
        }

        if ($estado !== null && $estado !== '') {
            $query->where('estado', (int)$estado);
        } else {
            $query->where('estado', 1);
        }

        $recetas = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();

        return view('admin.maestros.receta_ingredientes.index', compact('recetas', 'buscar', 'estado'));
    }

    public function create()
    {
        $recetas = Receta::all();
        $productos = Producto::all();
        $unidades = Unidad::all();
        return view('admin.maestros.receta_ingredientes.create', compact('recetas', 'productos', 'unidades'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'recetas_id' => 'required|exists:recetas,id',

            // arrays
            'producto_id' => 'required|array|min:1',
            'producto_id.*' => 'required|exists:productos,id',

            'cantidad_porcion' => 'nullable|array',
            'cantidad_porcion.*' => 'nullable|numeric',

            'cantidad_gramos' => 'nullable|array',
            'cantidad_gramos.*' => 'nullable|numeric',

            'unidad_id' => 'required|array',
            'unidad_id.*' => 'required|exists:unidades,id',
        ]);

        DB::beginTransaction();
        try {
            foreach ($validated['producto_id'] as $index => $productoId) {
                $cantidad = $validated['cantidad_porcion'][$index] ?? null;
                $unidadId = $validated['unidad_id'][$index] ?? null;

                $unidad = Unidad::find($unidadId);
                $cantidadGramos = $cantidad * $unidad->factor_a_gramo;

                if (!$cantidad || !$unidadId) continue;

                RecetaIngrediente::create([
                    'recetas_id' => $validated['recetas_id'],
                    'producto_id' => $productoId,
                    'cantidad_porcion' => $cantidad,
                    'cantidad_gramos' => $cantidadGramos,
                    'unidad_id' => $unidadId,
                ]);
            }

            DB::commit();
            return redirect()->route('admin.maestros.receta_ingredientes.index')->with('success', 'Ingredientes agregados Exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function edit($recetaId)
    {
        $receta = Receta::with('recetaIngredientes.producto', 'recetaIngredientes.unidad')
            ->findOrFail($recetaId);

        $productos = Producto::all();
        $unidades = Unidad::all();

        return view('admin.maestros.receta_ingredientes.edit', compact(
            'receta',
            'productos',
            'unidades'
        ));
    }

    public function update(Request $request, $recetaId)
    {
        $validated = $request->validate([
            'recetas_id' => 'required|exists:recetas,id',
            'producto_id' => 'nullable|array',
            'producto_id.*' => 'required|exists:productos,id',
            'cantidad_porcion' => 'nullable|array',
            'cantidad_porcion.*' => 'required|numeric|min:0.0001',
            'unidad_id' => 'nullable|array',
            'unidad_id.*' => 'required|exists:unidades,id',
        ]);

        if ((int)$validated['recetas_id'] !== (int)$recetaId) {
            return redirect()->back()->withInput()->with('error', 'Id de receta inválido.');
        }

        DB::beginTransaction();
        try {
            RecetaIngrediente::where('recetas_id', $recetaId)->delete();

            foreach ($validated['producto_id'] ?? [] as $index => $productoId) {
                $cantidad = $validated['cantidad_porcion'][$index] ?? null;
                $unidadId = $validated['unidad_id'][$index] ?? null;

                $unidad = Unidad::find($unidadId);
                $cantidadGramos = $cantidad * $unidad->factor_a_gramo;


                if (!$cantidad || !$unidadId) continue;

                RecetaIngrediente::create([
                    'recetas_id' => $recetaId,
                    'producto_id' => $productoId,
                    'cantidad_porcion' => $cantidad,
                    'cantidad_gramos' => $cantidadGramos,
                    'unidad_id' => $unidadId,
                ]);
            }

            DB::commit();
            return redirect()->route('admin.maestros.receta_ingredientes.index')
                ->with('success', 'Ingredientes de la receta actualizados Exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function destroy($recetaId)
    {
        RecetaIngrediente::where('recetas_id', $recetaId)->delete();

        return redirect()
            ->route('admin.maestros.receta_ingredientes.index')
            ->with('success', 'Ingredientes de la receta eliminados Exitosamente.');
    }
}
