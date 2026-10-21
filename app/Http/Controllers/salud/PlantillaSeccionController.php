<?php

namespace App\Http\Controllers\salud;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\salud\PlantillaSeccion;
use Illuminate\Support\Facades\Auth;

class PlantillaSeccionController extends Controller
{
    public function index()
    {
        $plantillas = PlantillaSeccion::obtenerPorPsicologo(Auth::id());
        
        foreach ($plantillas as $plantilla) {
            $plantilla->esta_en_uso = PlantillaSeccion::estaEnUso($plantilla->id_sph, Auth::id());
        }

        return view('admin.psicologia.maestros.plantillas.index', compact('plantillas'));
    }

    public function create()
    {
        return view('admin.psicologia.maestros.plantillas.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion_general' => 'nullable|string|max:255',
            'segmentos' => 'nullable|array|max:4',
            'segmentos.*' => 'required|string|max:255',
        ]);

        if (PlantillaSeccion::existeTitulo($data['titulo'], Auth::id())) {
            return redirect()->back()->withInput()->with('error', 'Ya existe una plantilla con ese nombre. Por favor, elige un título diferente.');
        }

        PlantillaSeccion::crear(Auth::id(), $data);

        return redirect()->route('admin.psicologia.maestros.plantillas.index')->with('success', 'Plantilla creada exitosamente.');
    }

    public function edit($id)
    {
        $plantilla = PlantillaSeccion::obtenerPorId($id, Auth::id());
        
        if (!$plantilla) {
            abort(404);
        }

        if (PlantillaSeccion::estaEnUso($id, Auth::id())) {
            return redirect()->route('admin.psicologia.maestros.plantillas.index')->with('error', 'No se puede editar esta sección porque ya está siendo utilizada por al menos un paciente.');
        }

        if ($plantilla->segmentos_sph) {
            $plantilla->segmentos_sph = json_decode($plantilla->segmentos_sph, true);
        } else {
            $plantilla->segmentos_sph = [];
        }

        return view('admin.psicologia.maestros.plantillas.edit', compact('plantilla'));
    }

    public function update(Request $request, $id)
    {
        $plantilla = PlantillaSeccion::obtenerPorId($id, Auth::id());
        
        if (!$plantilla) {
            abort(404);
        }

        if (PlantillaSeccion::estaEnUso($id, Auth::id())) {
            return redirect()->route('admin.psicologia.maestros.plantillas.index')->with('error', 'No se puede editar esta sección porque ya está siendo utilizada.');
        }

        $data = $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion_general' => 'nullable|string|max:255',
            'segmentos' => 'nullable|array|max:4',
            'segmentos.*' => 'required|string|max:255',
        ]);

        if (PlantillaSeccion::existeTitulo($data['titulo'], Auth::id(), $id)) {
            return redirect()->back()->withInput()->with('error', 'Ya existe otra plantilla con ese nombre. Por favor, elige un título diferente.');
        }

        PlantillaSeccion::actualizar($id, Auth::id(), $data);

        return redirect()->route('admin.psicologia.maestros.plantillas.index')->with('success', 'Plantilla actualizada exitosamente.');
    }

    public function destroy($id)
    {
        $plantilla = PlantillaSeccion::obtenerPorId($id, Auth::id());
        
        if (!$plantilla) {
            abort(404);
        }

        if (PlantillaSeccion::estaEnUso($id, Auth::id())) {
            return redirect()->route('admin.psicologia.maestros.plantillas.index')->with('error', 'No se puede eliminar esta sección porque ya está siendo utilizada en el expediente de al menos un paciente.');
        }

        PlantillaSeccion::eliminar($id, Auth::id());

        return redirect()->route('admin.psicologia.maestros.plantillas.index')->with('success', 'Plantilla eliminada exitosamente.');
    }
}
