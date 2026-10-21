<?php

namespace App\Http\Controllers;

use App\Models\Municipio;
use App\Models\Estado;
use Illuminate\Http\Request;

class MunicipioController extends Controller
{
    public function index()
    {
        $estados = Estado::where('status_id', 1)
            ->orderBy('nombre_esd', 'asc')
            ->get();
        $municipios = Municipio::orderBy('nombre_mun', 'asc')->get();
        return view('admin.municipio.index', compact('municipios', 'estados'));
    }

    public function createModal()
    {
        $estados = Estado::where('status_id', 1)
            ->orderBy('nombre_esd', 'asc')
            ->get();
        return view('admin.municipio.modales.createModal', compact('estados'));
    }

    public function getByEstado($esd_id)
    {
        $municipios = Municipio::where('esd_id', $esd_id)
            ->where('status_id', 1)
            ->get(['id_mun', 'nombre_mun']);
        return response()->json($municipios);
    }

    public function verificarExistencia(Request $request)
    {
        try {
            $request->validate([
                'nombre' => 'required|string|max:255',
                'esd_id' => 'required|exists:estado,id_esd',
            ]);

            $existe = Municipio::where('nombre_mun', $request->nombre)
                ->where('esd_id', $request->esd_id)
                ->where('status_id', 1)
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
            'nombre_mun' => 'required|string|max:255',
            'esd_id' => 'required|exists:estado,id_esd',
        ]);

        $existe = Municipio::where('nombre_mun', $validated['nombre_mun'])
            ->where('esd_id', $validated['esd_id'])
            ->where('status_id', 1)
            ->exists();

        if ($existe) {
            return redirect()
                ->route('admin.municipio.index')
                ->with('error', 'Ya existe un municipio con el mismo nombre y estado.');
        }

        try {
            $municipio = new Municipio();
            $municipio->nombre_mun = $validated['nombre_mun'];
            $municipio->esd_id = $validated['esd_id'];
            $municipio->status_id = 1;
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
            'nombre_mun' => 'required|string|max:255',
            'esd_id' => 'required|exists:estado,id_esd',
        ]);

        $existe = Municipio::where('nombre_mun', $validated['nombre_mun'])
            ->where('esd_id', $validated['esd_id'])
            ->where('id_mun', '!=', $municipio->id_mun)
            ->where('status_id', 1)
            ->exists();

        if ($existe) {
            return redirect()
                ->route('admin.municipio.index')
                ->with('error', 'Ya existe un municipio con el mismo nombre y estado.');
        }

        try {
            $municipio->nombre_mun = $validated['nombre_mun'];
            $municipio->esd_id = $validated['esd_id'];
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
                'status_id' => 2,
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
