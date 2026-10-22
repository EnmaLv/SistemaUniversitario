<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Http\Requests\CategoriaRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoriaController extends Controller
{
    private const TIPO_PRODUCTO_ID = 1;

    public function index(Request $request)
    {
        $categorias = Categoria::listarCategorias(
            $request->input('buscar'),
            $request->input('activo', 1),
            self::TIPO_PRODUCTO_ID
        );

        return view('admin.maestros.categorias.index', compact('categorias'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:100',
                Rule::unique('categoria_producto', 'nombre_cap')->where(function ($query) {
                    return $query->where('tpr_id', self::TIPO_PRODUCTO_ID);
                }),
            ],
            'descripcion' => 'nullable|string|max:255',
        ], [
            'nombre.unique' => 'Ya existe una categoría con este nombre en Comedor.',
        ]);

        Categoria::crearCategoria(
            $validated,
            self::TIPO_PRODUCTO_ID
        );

        return redirect()->to($request->input('from', route('admin.maestros.categorias.index')))
            ->with('exito', 'Categoría registrada correctamente.');
    }

    public function update(Request $request, $id)
    { 
        $validated = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:100',
                Rule::unique('categoria_producto', 'nombre_cap')
                    ->where('tpr_id', self::TIPO_PRODUCTO_ID)
                    ->ignore($id, 'id_cap'),
            ],
            'descripcion' => 'nullable|string|max:255',
        ], [
            'nombre.unique' => 'Ya existe una categoría con este nombre en Comedor.',
        ]);

        Categoria::actualizarCategoria($id, $validated);

        return redirect()->to($request->input('from', route('admin.maestros.categorias.index')))
            ->with('exito', 'Categoría actualizada exitosamente.');
    }

    public function destroy($id)
    {
        if (Categoria::tieneProductos($id)) {
            $cantidad = Categoria::contarProductos($id);

            return redirect()
                ->route('admin.maestros.categorias.index')
                ->with('error', "No se puede eliminar la categoría porque tiene {$cantidad} producto(s) asociado(s).")
                ->with('icono', 'error');
        }

        Categoria::eliminarCategoria($id);

        return redirect()
            ->route('admin.maestros.categorias.index')
            ->with('success', 'Categoría eliminada exitosamente.');
    }

    public function activar($id)
    {
        Categoria::activarCategoria($id);
        return redirect()->route('admin.maestros.categorias.index')->with('success', 'Categoria activada exitosamente.');
    }
}
