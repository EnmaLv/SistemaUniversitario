<?php

namespace App\Http\Controllers;

use App\Models\Estado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Livewire\WithPagination;

class EstadoController extends Controller
{
    use WithPagination;

    public function index()
    {
        $estados = Estado::orderBy('nombre_estado', 'asc')->Paginate(10)->withQueryString();

        return view('admin.estado.index', compact('estados'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre_estado' => 'required|string|max:255',
        ]);
        $existe = Estado::where('nombre_estado', $validated['nombre_estado'])
            ->where('status', true)
            ->exists();

        if ($existe) {
            return redirect()
                ->route('admin.estado.index')
                ->with('error', 'Ya existe un estado activo con este nombre.');
        }

        try {
            $estado = new Estado();
            $estado->nombre_estado = $validated['nombre_estado'];
            $estado->status = true;
            $estado->save();
            info('Se ha creado un nuevo estado: ' . $estado->nombre_estado);
            return redirect()
                ->route('admin.estado.index')
                ->with('success', 'Estado creado exitosamente.');
        } catch (\Exception $e) {
            info('Error al crear un nuevo estado: ' . $e->getMessage());
            return redirect()
                ->route('admin.estado.index')
                ->with('error', 'Error al crear el estado: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $estado = Estado::findOrFail($id);
        $validated = $request->validate([
            'nombre_estado' => 'required|string|max:255',
        ]);
        $existe = Estado::where('nombre_estado', $validated['nombre_estado'])
            ->where('status', true)
            ->where('id', '!=', $estado->id)
            ->exists();
        if ($existe) {
            return redirect()
                ->route('admin.estado.index')
                ->with('error', 'Ya existe otro estado activo con este nombre.');
        }

        try {
            $estado->nombre_estado = $validated['nombre_estado'];
            $estado->save();
            info('El estado con ID ' . $id . ' ha sido actualizado Exitosamente.');

            return redirect()
                ->route('admin.estado.index')
                ->with('success', 'Estado actualizado exitosamente.');
        } catch (\Exception $e) {
            info('Error al actualizar el estado con ID ' . $id . ': ' . $e->getMessage());

            return redirect()
                ->route('admin.estado.index')
                ->with('error', 'Error al actualizar el estado: ' . $e->getMessage());
        }
    }

    public function verificarExistencia(Request $request)
    {
        try {
            $request->validate([
                'nombre' => 'required|string|max:255',
            ]);

            $existe = Estado::where('nombre_estado', $request->nombre)
                ->where('status', true)
                ->exists();

            return response()->json([
                'success' => true,
                'existe' => $existe
            ]);
        } catch (\Exception $e) {
            Log::error('Error en verificarExistencia: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al verificar el estado',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $estado = Estado::find($id);
        if ($estado) {
            $estado->update(['status' => false]);

            info('El estado "' . $estado->nombre_estado . '" ha sido marcado como inactivo.');

            return redirect()
                ->route('admin.estado.index')
                ->with('success', 'Estado eliminado Exitosamente.');
        }

        info('No se encontró el estado con ID ' . $id . ' para eliminar.');

        return redirect()
            ->route('admin.estado.index')
            ->with('error', 'Estado no encontrado.');
    }
}
