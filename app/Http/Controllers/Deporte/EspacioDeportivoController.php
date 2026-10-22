<?php

namespace App\Http\Controllers\Deporte;

use App\Http\Controllers\Controller;
use App\Models\Deporte\DeporteEspacio;
use App\Support\Deportes\SedesDeporte;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class EspacioDeportivoController extends Controller
{
    public function index(Request $request)
    {
        $buscar = trim((string) $request->input('q', ''));
        $estado = (string) $request->input('estado', '');

        $espacios = DeporteEspacio::query()
            ->with('sede')
            ->withCount(['horarios as horarios_activos_count' => fn ($q) => $q->where('estado_hde', 'activo')])
            ->when($buscar !== '', function ($q) use ($buscar) {
                $patron = '%' . mb_strtolower($buscar) . '%';
                $q->where(fn ($s) => $s->whereRaw('LOWER(nombre_epd) LIKE ?', [$patron])->orWhereRaw('LOWER(ubicacion_epd) LIKE ?', [$patron]));
            })
            ->when(in_array($estado, ['1', '2'], true), fn ($q) => $q->where('status_id', (int) $estado))
            ->orderBy('nombre_epd')
            ->paginate(10)
            ->withQueryString();

        return view('admin.deportes.espacios.index', compact('espacios', 'buscar', 'estado'));
    }

    public function create()
    {
        return view('admin.deportes.espacios.form', [
            'espacio' => null,
            'sedes'   => SedesDeporte::opciones(),
        ]);
    }

    public function store(Request $request)
    {
        $datos = $this->validar($request);

        DeporteEspacio::create($datos + ['status_id' => 1]);

        return redirect()->route('admin.deportes.espacios.index')->with('success', 'Espacio deportivo registrado correctamente.');
    }

    public function edit(DeporteEspacio $espacio)
    {
        return view('admin.deportes.espacios.form', [
            'espacio' => $espacio,
            'sedes'   => SedesDeporte::opciones(),
        ]);
    }

    public function update(Request $request, DeporteEspacio $espacio)
    {
        $datos = $this->validar($request, $espacio);

        if ((int) $datos['sed_id'] !== (int) $espacio->sed_id && $espacio->horarios()->where('estado_hde', 'activo')->exists()) {
            throw ValidationException::withMessages(['sed_id' => 'No se puede cambiar la sede de un espacio con horarios activos.']);
        }

        $espacio->update($datos);

        return redirect()->route('admin.deportes.espacios.index')->with('success', 'Espacio deportivo actualizado correctamente.');
    }

    public function destroy(DeporteEspacio $espacio)
    {
        $espacio->update(['status_id' => 2]);

        return redirect()->route('admin.deportes.espacios.index')->with('success', 'Espacio deportivo inactivado. Sus horarios existentes se conservan.');
    }

    public function activar(DeporteEspacio $espacio)
    {
        $espacio->update(['status_id' => 1]);

        return redirect()->route('admin.deportes.espacios.index')->with('success', 'Espacio deportivo activado.');
    }

    private function validar(Request $request, ?DeporteEspacio $espacio = null): array
    {
        $validado = $request->validate([
            'nombre_epd'      => ['required', 'string', 'max:150'],
            'descripcion_epd' => ['nullable', 'string', 'max:2000'],
            'ubicacion_epd'   => ['nullable', 'string', 'max:255'],
            'sed_id'          => ['nullable', 'integer'],
        ], [
            'nombre_epd.required' => 'El nombre del espacio es obligatorio.',
        ]);

        $validado['nombre_epd'] = trim($validado['nombre_epd']);
        $validado['sed_id'] = SedesDeporte::resolver($validado['sed_id'] ?? ($espacio?->sed_id));

        $duplicado = DeporteEspacio::query()->todasLasSedes()
            ->where('sed_id', $validado['sed_id'])
            ->whereRaw('LOWER(nombre_epd) = ?', [mb_strtolower($validado['nombre_epd'])])
            ->when($espacio, fn ($q) => $q->where('id_epd', '!=', $espacio->id_epd))
            ->exists();

        if ($duplicado) {
            throw ValidationException::withMessages(['nombre_epd' => 'Ya existe un espacio con ese nombre en la sede seleccionada.']);
        }

        return $validado;
    }
}
