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
        $municipios = Municipio::where('status_id', 1)
            ->orderBy('nombre_mun', 'asc')
            ->get();
        $estados = Estado::where('status_id', 1)
            ->orderBy('nombre_esd', 'asc')
            ->get();
        $localidades = Localidad::with(['estado', 'municipio'])
            ->orderBy('nombre_par', 'asc')
            ->get();
        return view('admin.localidad.index', compact('localidades', 'municipios', 'estados'));
    }

    public function createModal()
    {
        $municipios = Municipio::where('status_id', 1)
            ->orderBy('nombre_mun', 'asc')
            ->get();
        $estados = Estado::where('status_id', 1)
            ->orderBy('nombre_esd', 'asc')
            ->get();
        return view('admin.localidad.modales.createModal', compact('municipios', 'estados'));
    }
    
    public function getByMunicipio($mun_id)
    {
        $localidades = Localidad::where('mun_id', $mun_id)
            ->where('status_id', 1)
            ->get(['id_par', 'nombre_par']);
        return response()->json($localidades);
    }

    public function verificarExistencia(Request $request)
    {
        try {
            $request->validate([
                'nombre' => 'required|string|max:255',
                'mun_id' => 'required|exists:municipio,id_mun',
            ]);

            $existe = Localidad::where('nombre_par', $request->nombre)
                ->where('mun_id', $request->mun_id)
                ->where('status_id', 1)
                ->exists();

            return response()->json([
                'success' => true,
                'existe' => $existe
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
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
            'nombre_par' => 'required|string|max:255',
            'mun_id' => 'required|exists:municipio,id_mun',
        ]);

        $existe = Localidad::where('nombre_par', $validated['nombre_par'])
            ->where('mun_id', $validated['mun_id'])
            ->where('status_id', 1)
            ->exists();

        if ($existe) {
            return redirect()
                ->route('admin.localidad.index')
                ->with('error', 'Ya existe una localidad con el mismo nombre y municipio.');
        }

        try {
            $localidad = new Localidad();
            $localidad->nombre_par = $validated['nombre_par'];
            $localidad->mun_id = $validated['mun_id'];
            $localidad->status_id = 1;
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
            'nombre_par' => 'required|string|max:255',
            'mun_id' => 'required|exists:municipio,id_mun',
        ]);

        $existe = Localidad::where('nombre_par', $validated['nombre_par'])
            ->where('mun_id', $validated['mun_id'])
            ->where('id_par', '!=', $localidad->id_par)
            ->where('status_id', 1)
            ->exists();

        if ($existe) {
            return redirect()
                ->route('admin.localidad.index')
                ->with('error', 'Ya existe una localidad con el mismo nombre y municipio.');
        }

        try {
            $localidad->nombre_par = $validated['nombre_par'];
            $localidad->mun_id = $validated['mun_id'];
            $localidad->status_id = 1;
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
            $localidad->update(['status_id' => 2]);
            return redirect()
                ->route('admin.localidad.index')
                ->with('success', 'La localidad fue eliminada Exitosamente.');
        }
        return redirect()
            ->route('admin.localidad.index')
            ->with('error', 'No se encontró la localidad especificada.');
    }
}
