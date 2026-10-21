<?php

namespace App\Http\Controllers;

use App\Models\Municipio;
use App\Models\Estado;
use Illuminate\Http\Request;

class MunicipioController extends Controller
{
    public function index()
    {
        $estados = Estado::where('status', true)
            ->orderBy('nombre_estado', 'asc')
            ->get();
        $municipios = Municipio::orderBy('nombre_municipio', 'asc')->get();
        return view('admin.municipio.index', compact('municipios', 'estados'));
    }

    public function createModal()
    {
        $estados = Estado::where('status', true)
            ->orderBy('nombre_estado', 'asc')
            ->get();
        return view('admin.municipio.modales.createModal', compact('estados'));
    }

    public function getByEstado($estado_id)
    {
        $municipios = Municipio::where('estado_id', $estado_id)
            ->where('status', true)
            ->get(['id', 'nombre_municipio']);
        return response()->json($municipios);
    }

    public function verificarExistencia(Request $request)
    {
        try {
            $request->validate([
                'nombre' => 'required|string|max:255',
                'estado_id' => 'required|exists:estados,id',
            ]);

            $existe = Municipio::where('nombre_municipio', $request->nombre)
                ->where('estado_id', $request->estado_id)
                ->where('status', true)
                ->exists();

            return response()->json([
                'success' => true,
                'existe' => $existe
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Error en verificarExistencia: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al verificar el municipio',
                'error' => \App\Support\Seguridad\MensajeError::de($e)
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre_municipio' => 'required|string|max:255',
            'estado_id' => 'required|exists:estados,id',
        ]);

        $existe = Municipio::where('nombre_municipio', $validated['nombre_municipio'])
            ->where('estado_id', $validated['estado_id'])
            ->where('status', true)
            ->exists();

        if ($existe) {
            return redirect()
                ->route('admin.municipio.index')
                ->with('error', 'Ya existe un municipio con el mismo nombre y estado.');
        }

        try {
            $municipio = new Municipio();
            $municipio->nombre_municipio = $validated['nombre_municipio'];
            $municipio->estado_id = $validated['estado_id'];
            $municipio->status = true;
            $municipio->save();

            return redirect()
                ->route('admin.municipio.index')
                ->with('success', 'El municipio fue registrado Exitosamente.');
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.municipio.index')
                ->with('error', 'Ocurrió un error al registrar el municipio: ' . \App\Support\Seguridad\MensajeError::de($e));
        }
    }

    public function update(Request $request, $id)
    {
        $municipio = Municipio::findOrFail($id);

        $validated = $request->validate([
            'nombre_municipio' => 'required|string|max:255',
            'estado_id' => 'required|exists:estados,id',
        ]);

        $existe = Municipio::where('nombre_municipio', $validated['nombre_municipio'])
            ->where('estado_id', $validated['estado_id'])
            ->where('id', '!=', $municipio->id)
            ->where('status', true)
            ->exists();

        if ($existe) {
            return redirect()
                ->route('admin.municipio.index')
                ->with('error', 'Ya existe un municipio con el mismo nombre y estado.');
        }

        try {
            $municipio->nombre_municipio = $validated['nombre_municipio'];
            $municipio->estado_id = $validated['estado_id'];
            $municipio->save();

            return redirect()
                ->route('admin.municipio.index')
                ->with('success', 'El municipio fue actualizado Exitosamente.');
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.municipio.index')
                ->with('error', 'Ocurrió un error al actualizar el municipio: ' . \App\Support\Seguridad\MensajeError::de($e));
        }
    }

    public function destroy($id)
    {
        $municipio = Municipio::find($id);

        if ($municipio) {
            $municipio->update([
                'status' => false,
            ]);
            return redirect()
                ->route('admin.municipio.index')
                ->with('success', 'El municipio fue eliminado Exitosamente.');
        }
        
        return redirect()
            ->route('admin.municipio.index')
            ->with('error', 'No se encontró el municipio especificado.');
    }
}
