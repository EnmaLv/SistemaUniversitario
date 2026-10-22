<?php

namespace App\Http\Controllers\Deporte;

use App\Http\Controllers\Controller;
use App\Models\Deporte\DeporteActividad;
use App\Models\Deporte\DeporteCalendario;
use App\Models\Deporte\DeporteEspacio;
use App\Models\Deporte\DeporteHorario;
use App\Models\Deporte\SesionAsistencia;
use App\Models\Pnf;
use App\Support\Deportes\SedesDeporte;
use App\Support\Deportes\ServicioActividades;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CalendarioDeportivoController extends Controller
{
    public function index(Request $request)
    {
        $buscar = trim((string) $request->input('q', ''));
        $estado = (string) $request->input('estado', '1');
        $tipo = (string) $request->input('tipo', '');

        $actividades = DeporteCalendario::query()
            ->actividades()
            ->with(['sede', 'programa', 'horarios' => fn ($q) => $q->where('estado_hde', '!=', DeporteHorario::ESTADO_CANCELADO)->with(['actividad', 'espacio'])])
            ->when($buscar !== '', fn ($q) => $q->where(fn ($s) => $s
                ->whereRaw('LOWER(nombre_cld) LIKE ?', ['%' . mb_strtolower($buscar) . '%'])
                ->orWhereRaw('LOWER(seccion_cld) LIKE ?', ['%' . mb_strtolower($buscar) . '%'])))
            ->when(in_array($estado, ['1', '2'], true), fn ($q) => $q->where('status_id', (int) $estado))
            ->when(in_array($tipo, [DeporteCalendario::TIPO_ESPORADICA, DeporteCalendario::TIPO_RECURRENTE], true), fn ($q) => $q->where('tipo_cld', $tipo))
            ->orderByDesc('fecha_inicio_cld')
            ->orderByDesc('id_cld')
            ->paginate(10)
            ->withQueryString();

        return view('admin.deportes.calendarios.index', compact('actividades', 'buscar', 'estado', 'tipo'));
    }

    public function create()
    {
        return view('admin.deportes.calendarios.form', $this->datosFormulario(null));
    }

    public function store(Request $request)
    {
        $actividad = ServicioActividades::guardar($this->validar($request), ServicioActividades::bloquesDesdeFormulario($request->input('bloques', [])));

        return redirect()->route('admin.deportes.calendarios.show', $actividad->id_cld)
            ->with('success', 'Actividad registrada. Ya aparece en los horarios y, el día del evento, en la lista de asistencia.');
    }

    public function show(DeporteCalendario $calendario)
    {
        if ($calendario->esTorneo()) {
            $torneo = $calendario->horarios()->with('torneos')->get()->pluck('torneos')->flatten()->first();

            if ($torneo) {
                return redirect()->route('admin.deportes.torneos.show', $torneo->id_tde);
            }
        }

        $calendario->load(['sede', 'programa', 'coordinador.persona']);

        $bloques = $calendario->horarios()
            ->with(['actividad', 'espacio'])
            ->withCount(['inscripcionesVigentes as inscritos_count'])
            ->orderBy('hora_inicio_hde')
            ->orderBy('id_hde')
            ->get();

        $hoy = Carbon::today();
        $pasadas = array_reverse($calendario->fechasEntre($calendario->fecha_inicio_cld, $hoy));
        $proximas = $calendario->fechasEntre($hoy->copy()->addDay(), $calendario->fechaFinEfectiva());

        $sesiones = SesionAsistencia::query()
            ->whereIn('hde_id', $bloques->pluck('id_hde')->all() ?: [0])
            ->withCount(['asistencias as presentes_count' => fn ($q) => $q->where('estado_asd', 'presente')])
            ->get()
            ->keyBy(fn ($sesion) => $sesion->hde_id . '|' . $sesion->fecha_sad->toDateString());

        return view('admin.deportes.calendarios.show', [
            'actividad' => $calendario,
            'bloques'   => $bloques,
            'pasadas'   => array_slice($pasadas, 0, 10),
            'proximas'  => array_slice($proximas, 0, 6),
            'totalFechas' => count($calendario->fechas()),
            'sesiones'  => $sesiones,
        ]);
    }

    public function edit(DeporteCalendario $calendario)
    {
        abort_if($calendario->esTorneo(), 404);

        return view('admin.deportes.calendarios.form', $this->datosFormulario($calendario));
    }

    public function update(Request $request, DeporteCalendario $calendario)
    {
        abort_if($calendario->esTorneo(), 404);

        ServicioActividades::guardar($this->validar($request, $calendario), ServicioActividades::bloquesDesdeFormulario($request->input('bloques', [])), $calendario);

        return redirect()->route('admin.deportes.calendarios.show', $calendario->id_cld)->with('success', 'Actividad actualizada correctamente.');
    }

    public function destroy(Request $request, DeporteCalendario $calendario)
    {
        if ($calendario->esTorneo()) {
            return back()->with('error', 'Los torneos se cancelan desde su propia pantalla, cambiando su estado.');
        }

        ServicioActividades::cancelar($calendario, $request->input('motivo'));

        return redirect()->route('admin.deportes.calendarios.index')->with('success', 'Actividad cancelada. Sus espacios quedaron libres y se conserva su historial.');
    }

    public function activar(DeporteCalendario $calendario)
    {
        if ($calendario->esTorneo()) {
            return back()->with('error', 'Los torneos se reactivan desde su propia pantalla.');
        }

        ServicioActividades::activar($calendario);

        return redirect()->route('admin.deportes.calendarios.index')->with('success', 'Actividad activada nuevamente.');
    }

    public function cartelPdf(DeporteCalendario $calendario)
    {
        $calendario->load(['sede', 'programa']);

        $bloques = $calendario->horarios()
            ->with(['actividad', 'espacio'])
            ->where('estado_hde', '!=', DeporteHorario::ESTADO_CANCELADO)
            ->withCount(['inscripcionesVigentes as inscritos_count'])
            ->orderBy('hora_inicio_hde')
            ->get();

        abort_if($bloques->isEmpty(), 404);

        $torneo = $calendario->esTorneo() ? $bloques->first()->torneos()->latest('id_tde')->first() : null;

        return Pdf::loadView('admin.deportes.carteles.pdf', [
            'actividad' => $calendario,
            'bloques'   => $bloques,
            'torneo'    => $torneo,
        ])->setPaper('letter', 'portrait')->stream('cartel_actividad_' . $calendario->id_cld . '.pdf');
    }

    private function datosFormulario(?DeporteCalendario $actividad): array
    {
        $bloques = $actividad
            ? $actividad->horarios()->where('estado_hde', '!=', DeporteHorario::ESTADO_CANCELADO)->orderBy('hora_inicio_hde')->orderBy('id_hde')->get()
            : collect();

        $disciplinas = DeporteActividad::query()
            ->where(fn ($q) => $q->where('status_id', 1)->orWhereIn('id_acd', $bloques->pluck('acd_id')->all() ?: [0]))
            ->orderBy('nombre_acd')
            ->get(['id_acd', 'nombre_acd']);

        $espacios = DeporteEspacio::query()
            ->with('sede')
            ->where(fn ($q) => $q->where('status_id', 1)->orWhereIn('id_epd', $bloques->pluck('epd_id')->all() ?: [0]))
            ->orderBy('nombre_epd')
            ->get(['id_epd', 'nombre_epd', 'sed_id']);

        $programas = Pnf::query()->where('status_id', 1)->orderBy('nombre_prg')->get(['id_prg', 'nombre_prg']);

        $bloquesIniciales = old('bloques', $bloques->map(fn (DeporteHorario $bloque) => [
            'id'          => $bloque->id_hde,
            'acd_id'      => $bloque->acd_id,
            'epd_id'      => $bloque->epd_id,
            'hora_inicio' => $bloque->horaInicio(),
            'hora_fin'    => $bloque->horaFin(),
            'cupo'        => $bloque->cupo_maximo_hde,
        ])->values()->all());

        if ($bloquesIniciales === []) {
            $bloquesIniciales = [['id' => null, 'acd_id' => '', 'epd_id' => '', 'hora_inicio' => '', 'hora_fin' => '', 'cupo' => '']];
        }

        return [
            'actividad'        => $actividad,
            'disciplinas'      => $disciplinas,
            'espacios'         => $espacios,
            'programas'        => $programas,
            'sedes'            => SedesDeporte::opciones(),
            'bloquesIniciales' => array_values($bloquesIniciales),
        ];
    }

    private function validar(Request $request, ?DeporteCalendario $actividad = null): array
    {
        $validado = $request->validate([
            'nombre_cld'          => ['required', 'string', 'max:255'],
            'descripcion_cld'     => ['nullable', 'string', 'max:2000'],
            'tipo_cld'            => ['required', Rule::in([DeporteCalendario::TIPO_ESPORADICA, DeporteCalendario::TIPO_RECURRENTE])],
            'fecha'               => ['nullable', 'date'],
            'fecha_inicio_cld'    => ['nullable', 'date'],
            'fecha_fin_cld'       => ['nullable', 'date'],
            'dias'                => ['nullable', 'array'],
            'dias.*'              => ['string', Rule::in(DeporteCalendario::DIAS)],
            'seccion_cld'         => ['nullable', 'string', 'max:50'],
            'prg_id'              => ['nullable', 'integer', Rule::exists('programa', 'id_prg')->whereNull('deleted_at')],
            'con_inscripcion_cld' => ['nullable', 'boolean'],
            'sed_id'              => ['nullable', 'integer'],
            'bloques'             => ['required', 'array', 'min:1'],
        ], [
            'nombre_cld.required' => 'Escribe el nombre de la actividad.',
            'tipo_cld.required'   => 'Elige si la actividad ocurre una sola vez o se repite cada semana.',
            'bloques.required'    => 'Agrega al menos un bloque: disciplina, espacio y hora.',
            'prg_id.exists'       => 'El PNF elegido no es válido.',
        ]);

        $validado['con_inscripcion_cld'] = $request->boolean('con_inscripcion_cld');
        $validado['sed_id'] = SedesDeporte::resolver($validado['sed_id'] ?? $actividad?->sed_id);

        return $validado;
    }
}
