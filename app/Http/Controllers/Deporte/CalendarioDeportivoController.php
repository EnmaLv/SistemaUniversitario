<?php

namespace App\Http\Controllers\Deporte;

use App\Http\Controllers\Controller;
use App\Models\Deporte\DeporteCalendario;
use App\Support\Deportes\SedesDeporte;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CalendarioDeportivoController extends Controller
{
    public function index(Request $request)
    {
        $buscar = trim((string) $request->input('q', ''));
        $estado = (string) $request->input('estado', '');

        $calendarios = DeporteCalendario::query()
            ->with(['sede', 'coordinador.persona'])
            ->withCount(['horarios as horarios_activos_count' => fn ($q) => $q->where('estado_hde', 'activo')])
            ->when($buscar !== '', fn ($q) => $q->whereRaw('LOWER(nombre_cld) LIKE ?', ['%' . mb_strtolower($buscar) . '%']))
            ->when(in_array($estado, ['1', '2'], true), fn ($q) => $q->where('status_id', (int) $estado))
            ->orderByDesc('fecha_inicio_cld')
            ->paginate(10)
            ->withQueryString();

        return view('admin.deportes.calendarios.index', compact('calendarios', 'buscar', 'estado'));
    }

    public function create()
    {
        return view('admin.deportes.calendarios.form', [
            'calendario' => null,
            'sedes'      => SedesDeporte::opciones(),
        ]);
    }

    public function store(Request $request)
    {
        DeporteCalendario::create($this->validar($request) + [
            'use_id'    => Auth::id(),
            'status_id' => 1,
        ]);

        return redirect()->route('admin.deportes.calendarios.index')->with('success', 'Calendario deportivo registrado correctamente.');
    }

    public function edit(DeporteCalendario $calendario)
    {
        return view('admin.deportes.calendarios.form', [
            'calendario' => $calendario,
            'sedes'      => SedesDeporte::opciones(),
        ]);
    }

    public function update(Request $request, DeporteCalendario $calendario)
    {
        $datos = $this->validar($request, $calendario);

        $horariosActivos = $calendario->horarios()->where('estado_hde', 'activo');

        if ((int) $datos['sed_id'] !== (int) $calendario->sed_id && (clone $horariosActivos)->exists()) {
            throw ValidationException::withMessages(['sed_id' => 'No se puede cambiar la sede de un calendario con horarios activos.']);
        }

        $fueraDeRango = (clone $horariosActivos)
            ->whereNotNull('fecha_especifica_hde')
            ->where(fn ($q) => $q->whereDate('fecha_especifica_hde', '<', $datos['fecha_inicio_cld'])->orWhereDate('fecha_especifica_hde', '>', $datos['fecha_fin_cld']))
            ->exists();

        if ($fueraDeRango) {
            throw ValidationException::withMessages(['fecha_fin_cld' => 'Hay horarios de fecha específica activos fuera del nuevo rango. Cancélalos o ajusta las fechas.']);
        }

        $calendario->update($datos);

        return redirect()->route('admin.deportes.calendarios.index')->with('success', 'Calendario deportivo actualizado correctamente.');
    }

    public function destroy(DeporteCalendario $calendario)
    {
        $calendario->update(['status_id' => 2]);

        return redirect()->route('admin.deportes.calendarios.index')->with('success', 'Calendario deportivo inactivado. No se podrán crear horarios nuevos en él.');
    }

    public function activar(DeporteCalendario $calendario)
    {
        $calendario->update(['status_id' => 1]);

        return redirect()->route('admin.deportes.calendarios.index')->with('success', 'Calendario deportivo activado.');
    }

    private function validar(Request $request, ?DeporteCalendario $calendario = null): array
    {
        $validado = $request->validate([
            'nombre_cld'       => ['required', 'string', 'max:255'],
            'descripcion_cld'  => ['nullable', 'string', 'max:2000'],
            'fecha_inicio_cld' => ['required', 'date'],
            'fecha_fin_cld'    => ['required', 'date', 'after_or_equal:fecha_inicio_cld'],
            'sed_id'           => ['nullable', 'integer'],
        ], [
            'nombre_cld.required'          => 'El nombre del calendario es obligatorio.',
            'fecha_inicio_cld.required'    => 'La fecha de inicio es obligatoria.',
            'fecha_fin_cld.required'       => 'La fecha de cierre es obligatoria.',
            'fecha_fin_cld.after_or_equal' => 'La fecha de cierre debe ser igual o posterior a la de inicio.',
        ]);

        $validado['nombre_cld'] = trim($validado['nombre_cld']);
        $validado['sed_id'] = SedesDeporte::resolver($validado['sed_id'] ?? ($calendario?->sed_id));

        return $validado;
    }
}
