<?php

namespace App\Http\Controllers\Deporte;

use App\Http\Controllers\Controller;
use App\Models\Deporte\DeporteActividad;
use App\Models\Deporte\DeporteCalendario;
use App\Models\Deporte\DeporteEspacio;
use App\Models\Deporte\DeporteHorario;
use App\Models\Deporte\TorneoDeportivo;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class HorarioDeporteController extends Controller
{
    private function lunesDe($valor): Carbon
    {
        try {
            $base = $valor instanceof Carbon ? $valor->copy() : Carbon::parse($valor ?? now());
        } catch (\Throwable $e) {
            $base = Carbon::now();
        }

        return $base->startOfWeek(Carbon::MONDAY)->startOfDay();
    }

    private function semanaActual(): Carbon
    {
        return Carbon::now()->startOfWeek(Carbon::MONDAY)->startOfDay();
    }

    private function diasDeSemana(Carbon $lunes): array
    {
        $dias = [];

        for ($i = 0; $i < 5; $i++) {
            $fecha = $lunes->copy()->addDays($i);
            $clave = DeporteHorario::diaDeFecha($fecha);
            $dias[] = [
                'clave'  => $clave,
                'nombre' => DeporteHorario::DIAS_LEGIBLES[$clave],
                'fecha'  => $fecha->format('Y-m-d'),
            ];
        }

        return $dias;
    }

    private function horariosDeSemana(Carbon $lunes): Collection
    {
        $viernes = $lunes->copy()->addDays(4);

        return DeporteHorario::query()
            ->with(['calendario', 'actividad', 'espacio'])
            ->withCount(['inscripcionesVigentes as inscritos_count'])
            ->vigentes()
            ->enRango($lunes, $viernes)
            ->orderBy('hora_inicio_hde')
            ->get()
            ->filter(function (DeporteHorario $horario) use ($lunes) {
                for ($i = 0; $i < 5; $i++) {
                    if ($horario->aplicaEnFecha($lunes->copy()->addDays($i))) {
                        return true;
                    }
                }

                return false;
            })
            ->values();
    }

    private function fechaEspecificaValida(?string $fecha): bool
    {
        if (! $fecha) {
            return false;
        }

        $fechaCarbon = Carbon::parse($fecha)->startOfDay();

        return ! $fechaCarbon->lt(Carbon::today()) && ! $fechaCarbon->isWeekend();
    }

    private function normalizarBloquesSeleccionados($bloques): array
    {
        $resultado = [];

        foreach ((array) $bloques as $bloque) {
            if (is_array($bloque)) {
                $bloque = $bloque['value'] ?? $bloque['key'] ?? null;
            }

            if (! is_string($bloque) || substr_count($bloque, '|') !== 2) {
                continue;
            }

            [$dia, $horaInicio, $horaFin] = array_map('trim', explode('|', $bloque));
            $dia = DeporteHorario::normalizarDia($dia);

            if (! $dia || ! preg_match('/^\d{2}:\d{2}$/', $horaInicio) || ! preg_match('/^\d{2}:\d{2}$/', $horaFin) || $horaInicio >= $horaFin) {
                continue;
            }

            $resultado[$dia . '|' . $horaInicio . '|' . $horaFin] = [
                'dia'         => $dia,
                'hora_inicio' => $horaInicio,
                'hora_fin'    => $horaFin,
            ];
        }

        return array_values($resultado);
    }

    private function datosFormulario(?DeporteHorario $horario = null): array
    {
        $calendarios = DeporteCalendario::query()
            ->with('sede')
            ->where(fn ($q) => $q->where('status_id', 1)->when($horario, fn ($s) => $s->orWhere('id_cld', $horario->cld_id)))
            ->orderByDesc('fecha_inicio_cld')
            ->get();

        $actividades = DeporteActividad::query()
            ->where(fn ($q) => $q->where('status_id', 1)->when($horario, fn ($s) => $s->orWhere('id_acd', $horario->acd_id)))
            ->orderBy('nombre_acd')
            ->get();

        $espacios = DeporteEspacio::query()
            ->with('sede')
            ->where(fn ($q) => $q->where('status_id', 1)->when($horario, fn ($s) => $s->orWhere('id_epd', $horario->epd_id)))
            ->orderBy('nombre_epd')
            ->get();

        $horariosActivos = DeporteHorario::query()
            ->with('calendario')
            ->vigentes()
            ->when($horario, fn ($q) => $q->where('id_hde', '!=', $horario->id_hde))
            ->get()
            ->map(function (DeporteHorario $h) {
                $vigencia = $h->vigencia();

                return $vigencia === null ? null : [
                    'id'          => $h->id_hde,
                    'epd_id'      => $h->epd_id,
                    'dia'         => $h->diaClave(),
                    'desde'       => $vigencia[0]->toDateString(),
                    'hasta'       => $vigencia[1]->toDateString(),
                    'hora_inicio' => $h->horaInicio(),
                    'hora_fin'    => $h->horaFin(),
                ];
            })
            ->filter()
            ->values();

        return compact('calendarios', 'actividades', 'espacios', 'horariosActivos');
    }

    private function catalogosValidos(array $validado, ?DeporteHorario $horario = null): array
    {
        $calendario = DeporteCalendario::query()->find($validado['cld_id']);
        $actividad = DeporteActividad::query()->find($validado['acd_id']);
        $espacio = DeporteEspacio::query()->find($validado['epd_id']);
        $errores = [];

        if (! $calendario || ((int) $calendario->status_id !== 1 && (int) $calendario->id_cld !== (int) $horario?->cld_id)) {
            $errores['cld_id'] = 'Selecciona un calendario activo.';
        }

        if (! $actividad || ((int) $actividad->status_id !== 1 && (int) $actividad->id_acd !== (int) $horario?->acd_id)) {
            $errores['acd_id'] = 'Selecciona una actividad activa.';
        }

        if (! $espacio || ((int) $espacio->status_id !== 1 && (int) $espacio->id_epd !== (int) $horario?->epd_id)) {
            $errores['epd_id'] = 'Selecciona un espacio activo.';
        }

        if ($calendario && $espacio && (int) $calendario->sed_id !== (int) $espacio->sed_id) {
            $errores['epd_id'] = 'El espacio pertenece a otra sede distinta a la del calendario.';
        }

        if ($errores) {
            throw ValidationException::withMessages($errores);
        }

        return [$calendario, $actividad, $espacio];
    }

    private function vigenciaCandidata(string $tipo, DeporteCalendario $calendario, Carbon $semana, ?string $fecha): array
    {
        if ($tipo === 'fecha_especifica') {
            $dia = Carbon::parse($fecha)->startOfDay();

            return [$dia, $dia->copy()];
        }

        [$desde, $hasta] = DeporteHorario::vigenciaRecurrente($semana, $calendario->fecha_inicio_cld, $calendario->fecha_fin_cld);

        if ($desde->gt($hasta)) {
            throw ValidationException::withMessages(['semana_inicio' => 'El calendario "' . $calendario->nombre_cld . '" termina el ' . $calendario->fecha_fin_cld->format('d/m/Y') . ', antes de la semana seleccionada.']);
        }

        return [$desde, $hasta];
    }

    private function mensajeSolape(DeporteHorario $existente): string
    {
        return 'El espacio ya está ocupado por "' . ($existente->actividad?->nombre_acd ?? 'otra actividad') . '" (' . $existente->descripcionCuando() . ').';
    }

    public function index(Request $request)
    {
        $semanaActual = $this->semanaActual();
        $semanaInicio = $this->lunesDe($request->query('semana') ?? $semanaActual);

        if ($semanaInicio->lt($semanaActual)) {
            return redirect()->route('admin.deportes.maestros.horarios.index', ['semana' => $semanaActual->format('Y-m-d')]);
        }

        return view('admin.deportes.maestros.horarios.index', [
            'horarios'     => $this->horariosDeSemana($semanaInicio),
            'semanaInicio' => $semanaInicio,
            'semanaFin'    => $semanaInicio->copy()->addDays(4),
            'diasSemana'   => $this->diasDeSemana($semanaInicio),
        ]);
    }

    public function create(Request $request)
    {
        $semanaActual = $this->semanaActual();
        $semanaInicio = $this->lunesDe($request->query('semana') ?? $semanaActual);

        if ($semanaInicio->lt($semanaActual)) {
            $semanaInicio = $semanaActual;
        }

        return view('admin.deportes.maestros.horarios.create', $this->datosFormulario() + [
            'semanaInicio' => $semanaInicio->format('Y-m-d'),
        ]);
    }

    public function historial()
    {
        $horarios = DeporteHorario::query()
            ->with(['actividad', 'espacio', 'calendario'])
            ->where('estado_hde', DeporteHorario::ESTADO_CANCELADO)
            ->orderByDesc('updated_at')
            ->orderByDesc('id_hde')
            ->get();

        return view('admin.deportes.maestros.horarios.historial', compact('horarios'));
    }

    public function store(Request $request)
    {
        $tipo = $request->input('tipo_horario', 'recurrente');

        $validado = $request->validate([
            'tipo_horario'      => ['required', Rule::in(['recurrente', 'fecha_especifica'])],
            'cld_id'            => ['required', 'integer'],
            'acd_id'            => ['required', 'integer'],
            'epd_id'            => ['required', 'integer'],
            'fecha_especifica'  => ['nullable', 'date', Rule::requiredIf($tipo === 'fecha_especifica')],
            'cupo_maximo_hde'   => ['nullable', 'integer', 'min:1'],
            'observaciones_hde' => ['nullable', 'string', 'max:2000'],
            'semana_inicio'     => ['nullable', 'date'],
        ]);

        [$calendario, , $espacio] = $this->catalogosValidos($validado);
        $semana = $this->lunesDe($validado['semana_inicio'] ?? now());
        $fecha = $tipo === 'fecha_especifica' ? Carbon::parse($validado['fecha_especifica'])->toDateString() : null;

        if ($fecha !== null) {
            if (! $this->fechaEspecificaValida($fecha)) {
                return back()->withErrors(['fecha_especifica' => 'La fecha específica debe ser hoy o una fecha futura de lunes a viernes.'])->withInput();
            }

            if (! $calendario->contieneFecha($fecha)) {
                return back()->withErrors(['fecha_especifica' => 'La fecha debe estar dentro del calendario (' . $calendario->fecha_inicio_cld->format('d/m/Y') . ' - ' . $calendario->fecha_fin_cld->format('d/m/Y') . ').'])->withInput();
            }

            $semana = $this->lunesDe($fecha);
        }

        $bloques = $this->normalizarBloquesSeleccionados($request->input('bloques', []));

        if (empty($bloques)) {
            return back()->withErrors(['bloques' => 'No has seleccionado ningún bloque de horario.'])->withInput();
        }

        [$desde, $hasta] = $this->vigenciaCandidata($tipo, $calendario, $semana, $fecha);
        $registros = [];

        foreach ($bloques as $bloque) {
            if ($fecha !== null && $bloque['dia'] !== DeporteHorario::diaDeFecha($fecha)) {
                return back()->withErrors(['bloques' => 'Los bloques deben corresponder al día de la fecha específica.'])->withInput();
            }

            $existente = DeporteHorario::buscarSolape($espacio->id_epd, $bloque['dia'], $desde, $hasta, $bloque['hora_inicio'], $bloque['hora_fin']);

            if ($existente) {
                return back()->withErrors(['bloques' => $this->mensajeSolape($existente)])->withInput();
            }

            $registros[] = [
                'cld_id'               => $calendario->id_cld,
                'acd_id'               => $validado['acd_id'],
                'epd_id'               => $espacio->id_epd,
                'use_id'               => Auth::id(),
                'dia_semana_hde'       => $fecha === null ? $bloque['dia'] : null,
                'fecha_especifica_hde' => $fecha,
                'semana_inicio_hde'    => $semana->toDateString(),
                'hora_inicio_hde'      => $bloque['hora_inicio'] . ':00',
                'hora_fin_hde'         => $bloque['hora_fin'] . ':00',
                'cupo_maximo_hde'      => $validado['cupo_maximo_hde'] ?? null,
                'observaciones_hde'    => $validado['observaciones_hde'] ?? null,
                'estado_hde'           => DeporteHorario::ESTADO_ACTIVO,
                'status_id'            => 1,
            ];
        }

        DB::transaction(function () use ($registros) {
            foreach ($registros as $registro) {
                DeporteHorario::create($registro);
            }
        });

        return redirect()->route('admin.deportes.maestros.horarios.index', ['semana' => $semana->format('Y-m-d')])
            ->with('success', count($registros) === 1 ? 'Horario creado correctamente.' : count($registros) . ' horarios creados correctamente.');
    }

    public function show($id)
    {
        $horario = DeporteHorario::query()
            ->with(['calendario.sede', 'actividad', 'espacio', 'responsable.persona', 'torneos'])
            ->withCount(['inscripcionesVigentes as inscritos_count'])
            ->findOrFail($id);

        return view('admin.deportes.maestros.horarios.show', compact('horario'));
    }

    public function edit($id)
    {
        $horario = DeporteHorario::query()->with('calendario')->findOrFail($id);

        if ($horario->estado_hde !== DeporteHorario::ESTADO_ACTIVO) {
            return redirect()->route('admin.deportes.maestros.horarios.historial')->with('error', 'Solo se pueden editar horarios activos. Reactiva el horario primero.');
        }

        $bloque = $horario->diaClave() . '|' . $horario->horaInicio() . '|' . $horario->horaFin();
        $semanaInicio = $horario->semana_inicio_hde
            ? $this->lunesDe($horario->semana_inicio_hde)->format('Y-m-d')
            : $this->semanaActual()->format('Y-m-d');

        return view('admin.deportes.maestros.horarios.edit', $this->datosFormulario($horario) + [
            'horario'                                      => $horario,
            'bloquesSeleccionadosIniciales'                => [$bloque],
            'bloquesSeleccionadosInicialesRecurrente'      => $horario->esFechaEspecifica() ? [] : [$bloque],
            'bloquesSeleccionadosInicialesFechaEspecifica' => $horario->esFechaEspecifica() ? [$bloque] : [],
            'semanaInicio'                                 => $semanaInicio,
        ]);
    }

    public function update(Request $request, $id)
    {
        $horario = DeporteHorario::query()->with('calendario')->findOrFail($id);

        if ($horario->estado_hde !== DeporteHorario::ESTADO_ACTIVO) {
            return redirect()->route('admin.deportes.maestros.horarios.historial')->with('error', 'Solo se pueden editar horarios activos.');
        }

        $validado = $request->validate([
            'acd_id'            => ['required', 'integer'],
            'epd_id'            => ['required', 'integer'],
            'cupo_maximo_hde'   => ['nullable', 'integer', 'min:1'],
            'observaciones_hde' => ['nullable', 'string', 'max:2000'],
            'semana_inicio'     => ['nullable', 'date'],
        ]);
        $validado['cld_id'] = $horario->cld_id;

        [$calendario, , $espacio] = $this->catalogosValidos($validado, $horario);

        $inscritos = $horario->cuposOcupados();

        if (! empty($validado['cupo_maximo_hde']) && $validado['cupo_maximo_hde'] < $inscritos) {
            return back()->withErrors(['cupo_maximo_hde' => 'El cupo no puede ser menor que los ' . $inscritos . ' inscritos actuales.'])->withInput();
        }

        $fecha = $horario->esFechaEspecifica() ? $horario->fecha_especifica_hde->toDateString() : null;
        $tipo = $fecha !== null ? 'fecha_especifica' : 'recurrente';
        $semana = $fecha !== null
            ? $this->lunesDe($fecha)
            : $this->lunesDe($validado['semana_inicio'] ?? $horario->semana_inicio_hde ?? now());

        $bloques = $this->normalizarBloquesSeleccionados($request->input('bloques', []));

        if (empty($bloques)) {
            return back()->withErrors(['bloques' => 'No has seleccionado ningún bloque de horario.'])->withInput();
        }

        [$desde, $hasta] = $this->vigenciaCandidata($tipo, $calendario, $semana, $fecha);

        foreach ($bloques as $bloque) {
            if ($fecha !== null && $bloque['dia'] !== DeporteHorario::diaDeFecha($fecha)) {
                return back()->withErrors(['bloques' => 'Los bloques deben corresponder al día de la fecha específica.'])->withInput();
            }

            $existente = DeporteHorario::buscarSolape($espacio->id_epd, $bloque['dia'], $desde, $hasta, $bloque['hora_inicio'], $bloque['hora_fin'], $horario->id_hde);

            if ($existente) {
                return back()->withErrors(['bloques' => $this->mensajeSolape($existente)])->withInput();
            }
        }

        DB::transaction(function () use ($horario, $bloques, $validado, $espacio, $fecha, $semana) {
            $comunes = [
                'acd_id'            => $validado['acd_id'],
                'epd_id'            => $espacio->id_epd,
                'cupo_maximo_hde'   => $validado['cupo_maximo_hde'] ?? null,
                'observaciones_hde' => $validado['observaciones_hde'] ?? null,
                'semana_inicio_hde' => $semana->toDateString(),
            ];

            foreach ($bloques as $indice => $bloque) {
                $datos = $comunes + [
                    'dia_semana_hde'  => $fecha === null ? $bloque['dia'] : null,
                    'hora_inicio_hde' => $bloque['hora_inicio'] . ':00',
                    'hora_fin_hde'    => $bloque['hora_fin'] . ':00',
                ];

                if ($indice === 0) {
                    $horario->update($datos);

                    continue;
                }

                DeporteHorario::create($datos + [
                    'cld_id'               => $horario->cld_id,
                    'use_id'               => Auth::id(),
                    'fecha_especifica_hde' => $fecha,
                    'estado_hde'           => DeporteHorario::ESTADO_ACTIVO,
                    'status_id'            => 1,
                ]);
            }
        });

        $semanaDestino = $semana->gt($this->semanaActual()) ? $semana : $this->semanaActual();

        return redirect()->route('admin.deportes.maestros.horarios.index', ['semana' => $semanaDestino->format('Y-m-d')])
            ->with('success', 'Horario actualizado correctamente.');
    }

    private function cancelar(DeporteHorario $horario, string $motivo): void
    {
        $torneoVigente = $horario->torneos()->whereIn('estado_tde', TorneoDeportivo::ESTADOS_VIGENTES)->exists();

        if ($torneoVigente) {
            throw ValidationException::withMessages(['motivo_cancelacion' => 'El horario tiene un torneo abierto o en curso. Cancela o finaliza el torneo primero.']);
        }

        $horario->update([
            'estado_hde'             => DeporteHorario::ESTADO_CANCELADO,
            'motivo_cancelacion_hde' => $motivo,
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $validado = $request->validate([
            'motivo_cancelacion' => ['required', 'string', 'min:5', 'max:500'],
        ], [
            'motivo_cancelacion.required' => 'Debe ingresar un motivo para cancelar el horario.',
            'motivo_cancelacion.min'      => 'El motivo debe tener al menos 5 caracteres.',
        ]);

        $this->cancelar(DeporteHorario::query()->findOrFail($id), $validado['motivo_cancelacion']);

        return redirect()->route('admin.deportes.maestros.horarios.index')->with('success', 'Horario cancelado correctamente. Se conserva en el historial.');
    }

    public function deactivate(Request $request, $id)
    {
        $motivo = trim((string) $request->input('motivo_cancelacion', '')) ?: 'Desactivado por la coordinación de deportes.';

        $this->cancelar(DeporteHorario::query()->findOrFail($id), $motivo);

        return redirect()->route('admin.deportes.maestros.horarios.index')->with('success', 'Horario desactivado.');
    }

    public function activate($id)
    {
        $horario = DeporteHorario::query()->with(['calendario', 'espacio'])->findOrFail($id);

        if (! $horario->calendario || (int) $horario->calendario->status_id !== 1) {
            return back()->with('error', 'El calendario del horario está inactivo; actívalo primero.');
        }

        if (! $horario->espacio || (int) $horario->espacio->status_id !== 1) {
            return back()->with('error', 'El espacio del horario está inactivo; actívalo primero.');
        }

        $vigencia = $horario->vigencia();

        if ($vigencia === null || $vigencia[1]->lt(Carbon::today())) {
            return back()->with('error', 'El horario ya no tiene fechas vigentes dentro de su calendario.');
        }

        $existente = DeporteHorario::buscarSolape($horario->epd_id, $horario->diaClave(), $vigencia[0], $vigencia[1], $horario->horaInicio(), $horario->horaFin(), $horario->id_hde);

        if ($existente) {
            return back()->with('error', 'No se puede reactivar. ' . $this->mensajeSolape($existente));
        }

        $horario->update([
            'estado_hde'             => DeporteHorario::ESTADO_ACTIVO,
            'motivo_cancelacion_hde' => null,
        ]);

        return redirect()->route('admin.deportes.maestros.horarios.index')->with('success', 'Horario activado.');
    }

    public function exportarPdf(Request $request)
    {
        $semanaInicio = $this->lunesDe($request->query('semana') ?? $this->semanaActual());

        $pdf = Pdf::loadView('admin.deportes.maestros.horarios.pdf', [
            'horarios'     => $this->horariosDeSemana($semanaInicio),
            'semanaInicio' => $semanaInicio,
            'diasSemana'   => $this->diasDeSemana($semanaInicio),
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('horarios_deportes_' . $semanaInicio->format('Y-m-d') . '.pdf');
    }

    public function cartelPdf($id)
    {
        $horario = DeporteHorario::query()
            ->with(['calendario.sede', 'actividad', 'espacio'])
            ->findOrFail($id);

        $pdf = Pdf::loadView('admin.deportes.carteles.pdf', [
            'titulo'      => $horario->actividad?->nombre_acd ?? 'Actividad deportiva',
            'subtitulo'   => 'Actividad deportiva ' . mb_strtolower($horario->actividad?->tipoLegible() ?? ''),
            'descripcion' => $horario->actividad?->descripcion_acd,
            'horario'     => $horario,
            'modalidad'   => null,
            'cuposLibres' => $horario->cuposDisponibles(),
        ])->setPaper('letter', 'portrait');

        return $pdf->stream('cartel_actividad_' . $horario->id_hde . '.pdf');
    }
}
