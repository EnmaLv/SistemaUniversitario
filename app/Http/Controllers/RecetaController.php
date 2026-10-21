<?php

namespace App\Http\Controllers;

use App\Models\Receta;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RecetaController extends Controller
{
    public function index(Request $request)
    {
        $buscar = $request->input('buscar');
        $estado = $request->input('estado', 1);

        $query = Receta::query();

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

        return view('admin.maestros.recetas.index', compact('recetas', 'buscar', 'estado'));
    }

    public function create()
    {
        return view('admin.maestros.recetas.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:255',
                'unique:receta,nombre_rct'
            ],
            'descripcion' => 'nullable|string',
        ], [
            'nombre.unique' => 'Ya existe una receta con este nombre',
        ]);

        $receta = new Receta();
        $receta->nombre_rct = $validated['nombre'];
        $receta->descripcion_rct = $validated['descripcion'];
        $receta->status_id = Receta::STATUS_ACTIVO;
        $receta->save();

        $from = $request->input('from');

        if ($from) {
            return redirect($from . '?rct_id=' . $receta->id_rct)
                ->with('success', 'Receta creada exitosamente.');
        } else {
            return redirect()->route('admin.maestros.recetas.index')
                ->with('success', 'Receta creada exitosamente.');
        }
    }


    public function edit($id)
    {
        $receta = Receta::findOrFail($id);
        return view('admin.maestros.recetas.edit', compact('receta'));
    }

    public function update(Request $request, Receta $receta)
    {
        $validated = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:255',
                Rule::unique('receta', 'nombre_rct')->ignore($receta->id_rct, 'id_rct'),
            ],
            'descripcion' => 'nullable|string',
        ], [
            'nombre.unique' => 'Ya existe una receta con este nombre',
        ]);

        $receta->nombre_rct = $validated['nombre'];
        $receta->descripcion_rct = $validated['descripcion'];
        $receta->save();

        return redirect()
            ->route('admin.maestros.recetas.index')
            ->with('success', 'Receta actualizada exitosamente.');
    }


    public function destroy($id)
    {
        if (Receta::tieneIngredienes($id)) {

            $cantidad = Receta::cantidadIngredientes($id);

            return redirect()
                ->route('admin.maestros.recetas.index')
                ->with(
                    'error',
                    "No se puede eliminar la receta porque tiene " . round($cantidad) . " ingrediente(s) asociado(s)."
                )
                ->with('icono', 'error');
        }
        Receta::eliminarReceta($id);
        return redirect()->route('admin.maestros.recetas.index')->with('success', 'Receta eliminada exitosamente.');
    }

    public function activar($id)
    {
        Receta::activarReceta($id);
        return redirect()->route('admin.maestros.recetas.index')->with('success', 'Receta activada exitosamente.');
    }
}
