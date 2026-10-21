<?php

namespace App\Http\Controllers;

use App\Models\Localidad;
use App\Models\Municipio;
use App\Models\Estado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LocalidadController extends Controller
{
    public function index()
    {
        $municipios = Municipio::where('status', true)
            ->orderBy('nombre_municipio', 'asc')
            ->get();
        $estados = Estado::where('status', true)
            ->orderBy('nombre_estado', 'asc')
            ->get();
        $localidades = Localidad::with(['estado', 'municipio'])
            ->orderBy('nombre_localidad', 'asc')
            ->get();
        return view('admin.localidad.index', compact('localidades', 'municipios', 'estados'));
    }

    public function createModal()
    {
        $municipios = Municipio::where('status', true)
            ->orderBy('nombre_municipio', 'asc')
            ->get();
        $estados = Estado::where('status', true)
            ->orderBy('nombre_estado', 'asc')
            ->get();
        return view('admin.localidad.modales.createModal', compact('municipios', 'estados'));
    }
    
    public function getByMunicipio($municipio_id)
    {
        $localidades = Localidad::where('municipio_id', $municipio_id)
            ->where('status', true)
            ->get(['id', 'nombre_localidad']);
        return response()->json($localidades);
    }

    public function verificarExistencia(Request $request)
    {
        try {
            $request->validate([
                'nombre' => 'required|string|max:255',
                'municipio_id' => 'required|exists:municipios,id',
            ]);

            $existe = Localidad::where('nombre_localidad', $request->nombre)
                ->where('municipio_id', $request->municipio_id)
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
                'message' => 'Error al verificar la localidad',
                'error' => \App\Support\Seguridad\MensajeError::de($e)
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre_localidad' => 'required|string|max:255',
            'municipio_id' => 'required|exists:municipios,id',
        ]);

        $existe = Localidad::where('nombre_localidad', $validated['nombre_localidad'])
            ->where('municipio_id', $validated['municipio_id'])
            ->where('status', true)
            ->exists();

        if ($existe) {
            return redirect()
                ->route('admin.localidad.index')
                ->with('error', 'Ya existe una localidad con el mismo nombre y municipio.');
        }

        try {
            $localidad = new Localidad();
            $localidad->nombre_localidad = $validated['nombre_localidad'];
            $localidad->municipio_id = $validated['municipio_id'];
            $localidad->status = true;
            $localidad->save();

            return redirect()
                ->route('admin.localidad.index')
                ->with('success', 'La localidad fue registrada Exitosamente.');
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.localidad.index')
                ->with('error', 'Ocurrió un error al registrar la localidad: ' . \App\Support\Seguridad\MensajeError::de($e));
        }
    }

    public function update(Request $request, $id)
    {
        $localidad = Localidad::findOrFail($id);
        $validated = $request->validate([
            'nombre_localidad' => 'required|string|max:255',
            'municipio_id' => 'required|exists:municipios,id',
        ]);

        $existe = Localidad::where('nombre_localidad', $validated['nombre_localidad'])
            ->where('municipio_id', $validated['municipio_id'])
            ->where('id', '!=', $localidad->id)
            ->where('status', true)
            ->exists();

        if ($existe) {
            return redirect()
                ->route('admin.localidad.index')
                ->with('error', 'Ya existe una localidad con el mismo nombre y municipio.');
        }

        try {
            $localidad->nombre_localidad = $validated['nombre_localidad'];
            $localidad->municipio_id = $validated['municipio_id'];
            $localidad->status = true;
            $localidad->save();

            return redirect()
                ->route('admin.localidad.index')
                ->with('success', 'La localidad fue actualizada Exitosamente.');
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.localidad.index')
                ->with('error', 'Ocurrió un error al actualizar la localidad: ' . \App\Support\Seguridad\MensajeError::de($e));
        }
    }

    public function destroy($id)
    {
        $localidad = Localidad::find($id);

        if ($localidad) {
            $localidad->update(['status' => false]);
            return redirect()
                ->route('admin.localidad.index')
                ->with('success', 'La localidad fue eliminada Exitosamente.');
        }
        return redirect()
            ->route('admin.localidad.index')
            ->with('error', 'No se encontró la localidad especificada.');
    }
}
