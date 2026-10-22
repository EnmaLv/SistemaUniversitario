<?php

namespace App\Http\Controllers\Deporte;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use App\Models\Deporte\DeporteHorario;
use App\Models\Deporte\DeporteCalendario;
use App\Models\Deporte\DeporteActividad;
use App\Models\Deporte\DeporteEspacio;

class HorarioDeporteController extends Controller
{
    private function resolveSemanaInicio($value): Carbon
    {
        $base = $value instanceof Carbon ? $value : Carbon::parse($value ?? now());

        return $base->copy()->startOfWeek(Carbon::MONDAY);
    }

    private function semanaActual(): Carbon
    {
        return Carbon::now()->startOfWeek(Carbon::MONDAY);
    }

    private function fechaEspecificaValida(?string $fecha): bool
    {
        if (!$fecha) {
            return false;
        }

        $fechaCarbon = Carbon::parse($fecha);
        $hoy = Carbon::today();

        if ($fechaCarbon->lt($hoy)) {
            return false;
        }

        if (in_array($fechaCarbon->dayOfWeek, [0, 6], true)) {
            return false;
        }

        return true;
    }

    public function index(Request $request)
    {
        $semanaActual = $this->semanaActual();
        $semanaParam = $request->query('semana');
        $semanaInicio = $this->resolveSemanaInicio($semanaParam ?? $semanaActual->format('Y-m-d'));

        if ($semanaInicio->lt($semanaActual)) {
            return redirect()->route('admin.deportes.maestros.horarios.index', ['semana' => $semanaActual->format('Y-m-d')]);
        }

        $semanaFin = $semanaInicio->copy()->addDays(4);

        $diasSemana = [];
        for ($i = 0; $i < 5; $i++) {
            $fechaDia = $semanaInicio->copy()->addDays($i);
            $diasSemana[] = [
                'nombre' => ucfirst($fechaDia->locale('es')->translatedFormat('l')),
                'fecha' => $fechaDia->format('Y-m-d'),
            ];
        }

        $semanaInicioDate = $semanaInicio->format('Y-m-d');
        $semanaFinDate = $semanaFin->format('Y-m-d');

        $horarios = DeporteHorario::query()
            ->where('estado', 'activo')
            ->where(function ($q) use ($semanaInicioDate, $semanaFinDate) {
                $q->where(function ($q2) use ($semanaInicioDate) {
                    $q2->whereNotNull('semana_inicio')
                        ->whereDate('semana_inicio', $semanaInicioDate);
                })->orWhere(function ($q2) use ($semanaInicioDate, $semanaFinDate) {
                    $q2->whereNotNull('fecha_especifica')
                        ->whereDate('fecha_especifica', '>=', $semanaInicioDate)
                        ->whereDate('fecha_especifica', '<=', $semanaFinDate);
                });
            })
            ->orderBy('hora_inicio')
            ->get();

        return view('admin.deportes.maestros.horarios.index', [
            'horarios' => $horarios,
            'semanaInicio' => $semanaInicio,
            'semanaFin' => $semanaFin,
            'diasSemana' => $diasSemana,
        ]);
    }

    private function bloqueKeyDelHorario(DeporteHorario $horario): string
    {
        $dia = !empty($horario->dia_semana)
            ? strtolower((string) $horario->dia_semana)
            : strtolower(Carbon::parse($horario->fecha_especifica)->locale('es')->translatedFormat('l'));

        return $dia . '|' . Carbon::parse($horario->hora_inicio)->format('H:i') . '|' . Carbon::parse($horario->hora_fin)->format('H:i');
    }

    private function normalizarBloquesSeleccionados(array $bloques): array
    {
        $resultado = [];

        foreach ($bloques as $bloque) {
            if (is_array($bloque)) {
                $bloque = $bloque['value'] ?? $bloque['key'] ?? null;
            }

            if (!is_string($bloque) || trim($bloque) === '') {
                continue;
            }

            $partes = explode('|', $bloque);
            if (count($partes) !== 3) {
                continue;
            }

            [$dia, $horaInicio, $horaFin] = $partes;
            $dia = trim((string) $dia);
            $horaInicio = trim((string) $horaInicio);
            $horaFin = trim((string) $horaFin);

            if ($dia === '' || $horaInicio === '' || $horaFin === '') {
                continue;
            }

            $resultado[] = [
                'dia' => strtolower($dia),
                'hora_inicio' => $horaInicio,
                'hora_fin' => $horaFin,
            ];
        }

        $resultado = array_values(array_unique(array_map(fn ($item) => $item['dia'] . '|' . $item['hora_inicio'] . '|' . $item['hora_fin'], $resultado)));

        $ordenado = [];
        foreach ($resultado as $item) {
            [$dia, $horaInicio, $horaFin] = explode('|', $item);
            $ordenado[] = [
                'dia' => $dia,
                'hora_inicio' => $horaInicio,
                'hora_fin' => $horaFin,
            ];
        }

        return $ordenado;
    }

    public function create(Request $request)
    {
        $semanaActual = $this->semanaActual();
        $semanaInicio = $this->resolveSemanaInicio($request->query('semana') ?? $request->input('semana') ?? $semanaActual->format('Y-m-d'));

        if ($semanaInicio->lt($semanaActual)) {
            $semanaInicio = $semanaActual;
        }

        $calendarios = DeporteCalendario::where('activo',1)->get();
        $actividades = DeporteActividad::where('activo',1)->get();
        $espacios = DeporteEspacio::where('activo',1)->get();
        $horariosActivos = DeporteHorario::query()
            ->where('estado', 'activo')
            ->whereNotNull('semana_inicio')
            ->get();

        return view('admin.deportes.maestros.horarios.create', compact('calendarios','actividades','espacios','horariosActivos','semanaInicio'));
    }

    public function historial()
    {
        $horarios = DeporteHorario::query()
            ->where('estado', 'cancelado')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->get();

        return view('admin.deportes.maestros.horarios.historial', compact('horarios'));
    }

    public function store(Request $request)
    {
        $tipoHorario = $request->input('tipo_horario', 'recurrente');
        $semanaInicio = $this->resolveSemanaInicio($request->input('semana_inicio') ?? $request->input('semana') ?? now());
        $bloquesSeleccionados = $this->normalizarBloquesSeleccionados($request->input('bloques', []));

        $validated = $request->validate([
            'tipo_horario' => 'required|in:recurrente,fecha_especifica',
            'id_calendario' => 'required|exists:deporte_calendario,id',
            'id_actividad' => 'required|exists:deporte_actividad,id',
            'id_espacio' => 'required|exists:deporte_espacio,id',
            'fecha_especifica' => ['nullable', 'date', Rule::requiredIf($tipoHorario === 'fecha_especifica')],
            'cupo_maximo' => 'nullable|integer|min:1',
            'observaciones' => 'nullable|string',
            'estado' => 'nullable|string',
            'semana_inicio' => ['nullable', 'date'],
        ]);

        if ($tipoHorario === 'fecha_especifica') {
            $fechaEspecifica = $request->input('fecha_especifica');
            if (!$this->fechaEspecificaValida($fechaEspecifica)) {
                return back()->withErrors(['fecha_especifica' => 'La fecha específica debe ser hoy o una fecha futura de lunes a viernes.'])->withInput();
            }
        }

        if (empty($bloquesSeleccionados)) {
            return back()->withErrors(['bloques' => 'No has seleccionado ningún bloque de horario.'])->withInput();
        }

        $registros = [];
        foreach ($bloquesSeleccionados as $bloque) {
            $registro = [
                'id_calendario' => $validated['id_calendario'],
                'id_actividad' => $validated['id_actividad'],
                'id_espacio' => $validated['id_espacio'],
                'hora_inicio' => $bloque['hora_inicio'],
                'hora_fin' => $bloque['hora_fin'],
                'cupo_maximo' => $validated['cupo_maximo'] ?? null,
                'observaciones' => $validated['observaciones'] ?? null,
                'estado' => 'activo',
                'creado_por' => Auth::id(),
                'semana_inicio' => $semanaInicio->format('Y-m-d'),
            ];

            if ($tipoHorario === 'recurrente') {
                $registro['dia_semana'] = strtolower($bloque['dia']);
                $registro['fecha_especifica'] = null;
            } else {
                $registro['dia_semana'] = null;
                $registro['fecha_especifica'] = $request->input('fecha_especifica');
                $registro['semana_inicio'] = $this->resolveSemanaInicio($request->input('fecha_especifica') ?? $semanaInicio)->format('Y-m-d');
            }

            $conflict = DeporteHorario::overlaps(
                $registro['id_espacio'],
                $registro['dia_semana'] ?? null,
                $registro['hora_inicio'],
                $registro['hora_fin'],
                null,
                $registro['fecha_especifica'] ?? null,
                $registro['semana_inicio']
            );

            if ($conflict) {
                return back()->withErrors(['bloques' => 'Uno o varios bloques seleccionados se solapan con otro horario activo del módulo Deportes en la misma semana.'])->withInput();
            }

            $registros[] = $registro;
        }

        foreach ($registros as $registro) {
            DeporteHorario::create($registro);
        }

        return redirect()->route('admin.deportes.maestros.horarios.index', ['semana' => $semanaInicio->format('Y-m-d')])->with('success', 'Horario creado correctamente.');
    }

    public function show($id)
    {
        $horario = DeporteHorario::findOrFail($id);
        return view('admin.deportes.maestros.horarios.show', compact('horario'));
    }

    public function edit($id)
    {
        $horario = DeporteHorario::findOrFail($id);
        $calendarios = DeporteCalendario::where('activo',1)->get();
        $actividades = DeporteActividad::where('activo',1)->get();
        $espacios = DeporteEspacio::where('activo',1)->get();
        $horariosActivos = DeporteHorario::query()
            ->where('estado', 'activo')
            ->where('id', '!=', $id)
            ->whereNotNull('semana_inicio')
            ->get();

        $semanaInicio = $horario->semana_inicio
            ? Carbon::parse($horario->semana_inicio)->startOfWeek(Carbon::MONDAY)
            : ($horario->fecha_especifica
                ? Carbon::parse($horario->fecha_especifica)->startOfWeek(Carbon::MONDAY)
                : Carbon::now()->startOfWeek(Carbon::MONDAY));

        $bloquesSeleccionadosIniciales = [$this->bloqueKeyDelHorario($horario)];
        $bloquesSeleccionadosInicialesRecurrente = $horario->dia_semana ? $bloquesSeleccionadosIniciales : [];
        $bloquesSeleccionadosInicialesFechaEspecifica = $horario->fecha_especifica ? $bloquesSeleccionadosIniciales : [];

        return view('admin.deportes.maestros.horarios.edit', compact(
            'horario',
            'calendarios',
            'actividades',
            'espacios',
            'horariosActivos',
            'bloquesSeleccionadosIniciales',
            'bloquesSeleccionadosInicialesRecurrente',
            'bloquesSeleccionadosInicialesFechaEspecifica',
            'semanaInicio'
        ));
    }

    public function update(Request $request, $id)
    {
        $horario = DeporteHorario::findOrFail($id);
        $tipoHorario = $horario->fecha_especifica ? 'fecha_especifica' : 'recurrente';
        $semanaInicio = $this->resolveSemanaInicio($request->input('semana_inicio') ?? ($horario->semana_inicio ?? $horario->fecha_especifica ?? now()));
        $bloquesSeleccionados = $this->normalizarBloquesSeleccionados($request->input('bloques', []));

        $validated = $request->validate([
            'id_actividad' => 'required|exists:deporte_actividad,id',
            'id_espacio' => 'required|exists:deporte_espacio,id',
            'cupo_maximo' => 'nullable|integer|min:1',
            'observaciones' => 'nullable|string',
            'semana_inicio' => ['nullable', 'date'],
        ]);

        if ($tipoHorario === 'fecha_especifica') {
            $fechaEspecifica = $request->input('fecha_especifica') ?? $horario->fecha_especifica;
            if (!$this->fechaEspecificaValida($fechaEspecifica)) {
                return back()->withErrors(['fecha_especifica' => 'La fecha específica debe ser hoy o una fecha futura de lunes a viernes.'])->withInput();
            }
        }

        if (empty($bloquesSeleccionados)) {
            return back()->withErrors(['bloques' => 'No has seleccionado ningún bloque de horario.'])->withInput();
        }

        $bloquesDeseados = [];
        foreach ($bloquesSeleccionados as $bloque) {
            $bloquesDeseados[] = strtolower($bloque['dia']) . '|' . Carbon::parse($bloque['hora_inicio'])->format('H:i') . '|' . Carbon::parse($bloque['hora_fin'])->format('H:i');
        }
        $bloquesDeseados = array_values(array_unique($bloquesDeseados));

        $bloquePrimero = $bloquesDeseados[0];
        [$diaObjetivo, $horaInicioObjetivo, $horaFinObjetivo] = explode('|', $bloquePrimero);

        $conflictActual = DeporteHorario::overlaps(
            $validated['id_espacio'],
            $tipoHorario === 'recurrente' ? strtolower($diaObjetivo) : null,
            Carbon::parse($horaInicioObjetivo)->format('H:i:s'),
            Carbon::parse($horaFinObjetivo)->format('H:i:s'),
            $horario->id,
            $tipoHorario === 'fecha_especifica' ? ($request->input('fecha_especifica') ?? $horario->fecha_especifica) : null,
            $semanaInicio->format('Y-m-d')
        );

        if ($conflictActual) {
            return back()->withErrors(['bloques' => 'El bloque seleccionado ya está ocupado por otro horario activo del módulo Deportes.'])->withInput();
        }

        $horario->update([
            'id_actividad' => $validated['id_actividad'],
            'id_espacio' => $validated['id_espacio'],
            'cupo_maximo' => $validated['cupo_maximo'] ?? null,
            'observaciones' => $validated['observaciones'] ?? null,
            'dia_semana' => $tipoHorario === 'recurrente' ? strtolower($diaObjetivo) : null,
            'fecha_especifica' => $tipoHorario === 'fecha_especifica' ? ($request->input('fecha_especifica') ?? $horario->fecha_especifica) : null,
            'semana_inicio' => $semanaInicio->format('Y-m-d'),
            'hora_inicio' => Carbon::parse($horaInicioObjetivo)->format('H:i:s'),
            'hora_fin' => Carbon::parse($horaFinObjetivo)->format('H:i:s'),
            'estado' => 'activo',
        ]);

        foreach (array_slice($bloquesDeseados, 1) as $bloqueKey) {
            [$dia, $horaInicio, $horaFin] = explode('|', $bloqueKey);

            $datos = [
                'id_calendario' => $horario->id_calendario,
                'id_actividad' => $validated['id_actividad'],
                'id_espacio' => $validated['id_espacio'],
                'hora_inicio' => $horaInicio,
                'hora_fin' => $horaFin,
                'cupo_maximo' => $validated['cupo_maximo'] ?? null,
                'observaciones' => $validated['observaciones'] ?? null,
                'estado' => 'activo',
                'creado_por' => Auth::id(),
                'semana_inicio' => $semanaInicio->format('Y-m-d'),
            ];

            if ($tipoHorario === 'recurrente') {
                $datos['dia_semana'] = strtolower($dia);
                $datos['fecha_especifica'] = null;
            } else {
                $datos['dia_semana'] = null;
                $datos['fecha_especifica'] = $horario->fecha_especifica;
            }

            if ($this->existeBloqueActivoEquivalente(
                $datos['id_espacio'],
                $datos['dia_semana'] ?? null,
                $datos['hora_inicio'],
                $datos['hora_fin'],
                $horario->id,
                $datos['fecha_especifica'] ?? null,
                $semanaInicio->format('Y-m-d')
            )) {
                continue;
            }

            $conflict = DeporteHorario::overlaps(
                $datos['id_espacio'],
                $datos['dia_semana'] ?? null,
                $datos['hora_inicio'],
                $datos['hora_fin'],
                $horario->id,
                $datos['fecha_especifica'] ?? null,
                $semanaInicio->format('Y-m-d')
            );

            if ($conflict) {
                return back()->withErrors(['bloques' => 'Uno o varios bloques seleccionados se solapan con otro horario activo.'])->withInput();
            }

            DeporteHorario::create($datos);
        }

        return redirect()->route('admin.deportes.maestros.horarios.index', ['semana' => $semanaInicio->format('Y-m-d')])->with('success', 'Horario actualizado correctamente.');
    }

    private function extraerDiaDesdeKey(string $bloqueKey): string
    {
        $partes = explode('|', $bloqueKey);
        return $partes[0] ?? 'lunes';
    }

    private function existeBloqueActivoEquivalente($idEspacio, $dia, $horaInicio, $horaFin, $excludeId = null, $fechaEspecifica = null, $semanaInicio = null): bool
    {
        $query = DeporteHorario::query()
            ->where('estado', 'activo')
            ->whereNotNull('semana_inicio')
            ->where('hora_inicio', Carbon::parse($horaInicio)->format('H:i:s'))
            ->where('hora_fin', Carbon::parse($horaFin)->format('H:i:s'))
            ->where('id', '!=', $excludeId ?? 0);

        if ($semanaInicio) {
            $query->whereDate('semana_inicio', Carbon::parse($semanaInicio)->startOfWeek(Carbon::MONDAY)->format('Y-m-d'));
        }

        $registros = $query->get();

        foreach ($registros as $registro) {
            $mismoDiaEfectivo = false;

            if ($fechaEspecifica) {
                if ($registro->fecha_especifica && Carbon::parse($registro->fecha_especifica)->toDateString() === Carbon::parse($fechaEspecifica)->toDateString()) {
                    $mismoDiaEfectivo = true;
                }

                if (!$registro->fecha_especifica && $registro->dia_semana) {
                    $diaFecha = strtolower(Carbon::parse($fechaEspecifica)->locale('es')->translatedFormat('l'));
                    if (strtolower((string) $registro->dia_semana) === $diaFecha) {
                        $mismoDiaEfectivo = true;
                    }
                }
            } else {
                if ($registro->fecha_especifica && $dia) {
                    $diaRegistro = strtolower(Carbon::parse($registro->fecha_especifica)->locale('es')->translatedFormat('l'));
                    if (strtolower((string) $dia) === $diaRegistro) {
                        $mismoDiaEfectivo = true;
                    }
                }

                if (!$registro->fecha_especifica && $registro->dia_semana && $dia) {
                    if (strtolower((string) $registro->dia_semana) === strtolower((string) $dia)) {
                        $mismoDiaEfectivo = true;
                    }
                }
            }

            if ($mismoDiaEfectivo) {
                return true;
            }
        }

        return false;
    }

    public function destroy(Request $request, $id)
    {
        $validated = $request->validate([
            'motivo_cancelacion' => ['required', 'string', 'min:5', 'max:500'],
        ], [
            'motivo_cancelacion.required' => 'Debe ingresar un motivo para cancelar el horario.',
            'motivo_cancelacion.min' => 'El motivo debe tener al menos 5 caracteres.',
        ]);

        $horario = DeporteHorario::findOrFail($id);
        $horario->estado = 'cancelado';
        $horario->motivo_cancelacion = $validated['motivo_cancelacion'];
        $horario->save();

        return redirect()->route('admin.deportes.maestros.horarios.index')->with('success', 'Horario cancelado correctamente. Se conserva como historial.');
    }

    public function activate($id)
    {
        $horario = DeporteHorario::findOrFail($id);
        $horario->estado = 'activo';
        $horario->save();
        return redirect()->route('admin.deportes.maestros.horarios.index')->with('success', 'Horario activado.');
    }

    public function deactivate($id)
    {
        $horario = DeporteHorario::findOrFail($id);
        $horario->estado = 'cancelado';
        $horario->save();
        return redirect()->route('admin.deportes.maestros.horarios.index')->with('success', 'Horario desactivado.');
    }

    public function exportarPdf(Request $request)
    {
        $semanaParam = $request->query('semana');
        $semanaInicio = $this->resolveSemanaInicio($semanaParam ?? $this->semanaActual()->format('Y-m-d'));
        $semanaFin = $semanaInicio->copy()->addDays(4);

        $semanaInicioDate = $semanaInicio->format('Y-m-d');
        $semanaFinDate = $semanaFin->format('Y-m-d');

        $horarios = DeporteHorario::query()
            ->where('estado', 'activo')
            ->where(function ($query) use ($semanaInicioDate, $semanaFinDate) {
                $query->where(function ($q) use ($semanaInicioDate) {
                    $q->whereNotNull('semana_inicio')
                        ->whereDate('semana_inicio', $semanaInicioDate);
                })->orWhere(function ($q) use ($semanaInicioDate, $semanaFinDate) {
                    $q->whereNotNull('fecha_especifica')
                        ->whereDate('fecha_especifica', '>=', $semanaInicioDate)
                        ->whereDate('fecha_especifica', '<=', $semanaFinDate);
                });
            })
            ->orderBy('hora_inicio')
            ->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.deportes.maestros.horarios.pdf', compact('horarios', 'semanaInicio'))->setPaper('a4', 'landscape');
        return $pdf->stream('horarios_deportes.pdf');
    }
}
