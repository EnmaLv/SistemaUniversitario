<?php

namespace App\Http\Controllers\salud;

use App\Models\Categoria;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CategoriaMedicamentoController extends Controller
{
    private const TIPO_PRODUCTO_ID = 2;

    public function index(Request $request)
    {
        $categorias = Categoria::listarCategorias(
            $request->input('buscar'),
            $request->input('activo', 1),
            self::TIPO_PRODUCTO_ID
        );

        return view('admin.salud.maestros.categorias.index', compact('categorias'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:255',
                'unique:categoria_producto,nombre_cap'
            ],
            'descripcion' => 'nullable|string',
        ], [
            'nombre.unique' => 'Ya existe una categoría con este nombre',
        ]);

        DB::beginTransaction();
        try {
            Categoria::crearCategoria(
                $validated,
                self::TIPO_PRODUCTO_ID
            );

            DB::commit();

            return redirect()->to($request->input('from', route('admin.salud.maestros.categorias.index')))
                ->with('success', 'Categoría registrada correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear categoría', ['error' => $e->getMessage()]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al crear la categoría: ' . \App\Support\Seguridad\MensajeError::de($e));
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            Categoria::actualizarCategoria($id, $request->only(['nombre', 'descripcion']));

            DB::commit();

            return redirect()->to($request->input('from', route('admin.salud.maestros.categorias.index')))
                ->with('success', 'Categoría actualizada correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar categoría', ['error' => $e->getMessage()]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al actualizar la categoría: ' . \App\Support\Seguridad\MensajeError::de($e));
        }
    }

    public function destroy($id)
    {
        try {
            // Lógica para inactivar categoría
            $categoria = Categoria::findOrFail($id);
            $categoria->update(['status_id' => Categoria::STATUS_INACTIVO]);

            return redirect()->route('admin.salud.maestros.categorias.index')
                ->with('success', 'Categoría inactivada correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al inactivar categoría', ['error' => $e->getMessage()]);

            return redirect()->back()
                ->with('error', 'Error al inactivar la categoría.');
        }
    }

    public function activar($id)
    {
        try {
            // Lógica para activar categoría
            $categoria = Categoria::findOrFail($id);
            $categoria->update(['status_id' => Categoria::STATUS_ACTIVO]);

            return redirect()->route('admin.salud.maestros.categorias.index')
                ->with('success', 'Categoría activada correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al activar categoría', ['error' => $e->getMessage()]);

            return redirect()->back()
                ->with('error', 'Error al activar la categoría.');
        }
    }
}