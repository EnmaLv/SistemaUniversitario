<?php

namespace App\Http\Controllers\Deporte;

use App\Http\Controllers\Controller;
use App\Models\Deporte\DeporteActividad;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ActividadDeportivaController extends Controller
{
    public function index(Request $request)
    {
        $buscar = trim((string) $request->input('q', ''));
        $estado = (string) $request->input('estado', '');

        $actividades = DeporteActividad::query()
            ->when($buscar !== '', fn ($q) => $q->whereRaw('LOWER(nombre_acd) LIKE ?', ['%' . mb_strtolower($buscar) . '%']))
            ->when(in_array($estado, ['1', '2'], true), fn ($q) => $q->where('status_id', (int) $estado))
            ->orderBy('nombre_acd')
            ->paginate(10)
            ->withQueryString();

        return view('admin.deportes.actividades.index', compact('actividades', 'buscar', 'estado'));
    }

    public function create()
    {
        return view('admin.deportes.actividades.form', ['actividad' => null]);
    }

    public function store(Request $request)
    {
        DeporteActividad::create($this->validar($request) + ['status_id' => 1]);

        return redirect()->route('admin.deportes.actividades.index')->with('success', 'Actividad deportiva registrada correctamente.');
    }

    public function edit(DeporteActividad $actividad)
    {
        return view('admin.deportes.actividades.form', compact('actividad'));
    }

    public function update(Request $request, DeporteActividad $actividad)
    {
        $actividad->update($this->validar($request, $actividad));

        return redirect()->route('admin.deportes.actividades.index')->with('success', 'Actividad deportiva actualizada correctamente.');
    }

    public function destroy(DeporteActividad $actividad)
    {
        $actividad->update(['status_id' => 2]);

        return redirect()->route('admin.deportes.actividades.index')->with('success', 'Actividad deportiva inactivada. Sus horarios existentes se conservan.');
    }

    public function activar(DeporteActividad $actividad)
    {
        $actividad->update(['status_id' => 1]);

        return redirect()->route('admin.deportes.actividades.index')->with('success', 'Actividad deportiva activada.');
    }

    private function validar(Request $request, ?DeporteActividad $actividad = null): array
    {
        $validado = $request->validate([
            'nombre_acd'      => ['required', 'string', 'max:150'],
            'descripcion_acd' => ['nullable', 'string', 'max:2000'],
            'tipo_acd'        => ['required', Rule::in(array_keys(DeporteActividad::TIPOS))],
        ], [
            'nombre_acd.required' => 'El nombre de la actividad es obligatorio.',
            'tipo_acd.in'         => 'El tipo de actividad no es válido.',
        ]);

        $validado['nombre_acd'] = trim($validado['nombre_acd']);

        $duplicado = DeporteActividad::query()
            ->whereRaw('LOWER(nombre_acd) = ?', [mb_strtolower($validado['nombre_acd'])])
            ->when($actividad, fn ($q) => $q->where('id_acd', '!=', $actividad->id_acd))
            ->exists();

        if ($duplicado) {
            throw ValidationException::withMessages(['nombre_acd' => 'Ya existe una actividad con ese nombre.']);
        }

        return $validado;
    }
}
