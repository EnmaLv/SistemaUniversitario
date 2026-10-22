<?php

namespace App\Http\Controllers\Deporte;

use App\Http\Controllers\Controller;
use App\Models\Deporte\DeporteHorario;
use App\Models\Deporte\EquipoTorneo;
use App\Models\Deporte\InscripcionDeportiva;
use App\Models\Deporte\IntegranteEquipo;
use App\Models\Deporte\TorneoDeportivo;
use App\Models\Pnf;
use App\Support\Auditoria\Bitacora;
use App\Support\Deportes\ServicioInscripcion;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TorneoDeportivoController extends Controller
{
    public function index(Request $request)
    {
        $buscar = trim((string) $request->input('q', ''));
        $estado = (string) $request->input('estado', '');

        $torneos = TorneoDeportivo::query()
            ->with(['horario.actividad', 'horario.espacio', 'horario.calendario'])
            ->withCount('equipos')
            ->when($buscar !== '', fn ($q) => $q->whereRaw('LOWER(nombre_tde) LIKE ?', ['%' . mb_strtolower($buscar) . '%']))
            ->when(array_key_exists($estado, TorneoDeportivo::ESTADOS), fn ($q) => $q->where('estado_tde', $estado))
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        $inscritos = InscripcionDeportiva::query()
            ->whereIn('hde_id', $torneos->pluck('hde_id')->filter()->unique()->all() ?: [0])
            ->where('estado_ide', '!=', InscripcionDeportiva::ESTADO_RETIRADO)
            ->selectRaw('hde_id, COUNT(*) AS total')
            ->groupBy('hde_id')
            ->pluck('total', 'hde_id');

        return view('admin.deportes.torneos.index', compact('torneos', 'buscar', 'estado', 'inscritos'));
    }

    public function create()
    {
        return view('admin.deportes.torneos.form', [
            'torneo'   => null,
            'horarios' => $this->horariosDisponibles(),
        ]);
    }

    public function store(Request $request)
    {
        $datos = $this->validar($request);

        $torneo = TorneoDeportivo::create($datos + [
            'estado_tde' => TorneoDeportivo::ESTADO_ABIERTO,
            'use_id'     => Auth::id(),
            'status_id'  => 1,
        ]);

        return redirect()->route('admin.deportes.torneos.show', $torneo->id_tde)->with('success', 'Torneo registrado correctamente.');
    }

    public function show(TorneoDeportivo $torneo)
    {
        $torneo->load([
            'horario.actividad',
            'horario.espacio',
            'horario.calendario.sede',
            'coordinador.persona',
            'equipos' => fn ($q) => $q->orderBy('nombre_equipo_etd'),
            'equipos.programa',
            'equipos.integrantes.persona',
        ]);

        $inscripciones = $torneo->horario
            ? $torneo->horario->inscripciones()->with('persona')->where('estado_ide', '!=', InscripcionDeportiva::ESTADO_RETIRADO)->orderBy('fecha_registro_ide')->get()
            : collect();

        $programas = Pnf::query()->where('status_id', 1)->orderBy('nombre_prg')->get(['id_prg', 'nombre_prg']);

        return view('admin.deportes.torneos.show', compact('torneo', 'inscripciones', 'programas'));
    }

    public function edit(TorneoDeportivo $torneo)
    {
        return view('admin.deportes.torneos.form', [
            'torneo'   => $torneo,
            'horarios' => $this->horariosDisponibles($torneo),
        ]);
    }

    public function update(Request $request, TorneoDeportivo $torneo)
    {
        if (! in_array($torneo->estado_tde, TorneoDeportivo::ESTADOS_VIGENTES, true)) {
            return back()->with('error', 'No se puede editar un torneo finalizado o cancelado.');
        }

        $datos = $this->validar($request, $torneo);

        if ((int) $datos['hde_id'] !== (int) $torneo->hde_id && $torneo->horario?->inscripcionesVigentes()->exists()) {
            throw ValidationException::withMessages(['hde_id' => 'No se puede cambiar el horario: el torneo ya tiene inscritos.']);
        }

        $torneo->update($datos);

        return redirect()->route('admin.deportes.torneos.show', $torneo->id_tde)->with('success', 'Torneo actualizado correctamente.');
    }

    public function updateEstado(Request $request, TorneoDeportivo $torneo)
    {
        $validado = $request->validate([
            'estado_tde' => ['required', Rule::in(array_keys(TorneoDeportivo::ESTADOS))],
        ]);

        if (! in_array($torneo->estado_tde, TorneoDeportivo::ESTADOS_VIGENTES, true)) {
            return back()->with('error', 'El torneo ya está ' . mb_strtolower($torneo->estadoLegible()) . ' y no puede cambiar de estado.');
        }

        $anterior = $torneo->estado_tde;
        $torneo->update(['estado_tde' => $validado['estado_tde']]);

        Bitacora::registrar(
            accion: 'cambio_estado_torneo',
            modulo: 'deporte',
            tablaAfectada: 'torneo_deportivo',
            registroId: $torneo->id_tde,
            datosAnteriores: ['estado_tde' => $anterior],
            datosModificados: ['estado_tde' => $validado['estado_tde']],
        );

        return back()->with('success', 'Estado del torneo actualizado a "' . $torneo->estadoLegible() . '".');
    }

    public function cartelPdf(TorneoDeportivo $torneo)
    {
        $torneo->load(['horario.actividad', 'horario.espacio', 'horario.calendario.sede']);

        abort_unless($torneo->horario, 404);

        $pdf = Pdf::loadView('admin.deportes.carteles.pdf', [
            'titulo'      => $torneo->nombre_tde,
            'subtitulo'   => 'Torneo de ' . ($torneo->horario->actividad?->nombre_acd ?? 'deporte'),
            'descripcion' => $torneo->descripcion_tde,
            'horario'     => $torneo->horario,
            'modalidad'   => $torneo->modalidad_tde,
            'cuposLibres' => $torneo->horario->cuposDisponibles(),
        ])->setPaper('letter', 'portrait');

        return $pdf->stream('cartel_torneo_' . $torneo->id_tde . '.pdf');
    }

    public function storeEquipo(Request $request, TorneoDeportivo $torneo)
    {
        if (! in_array($torneo->estado_tde, TorneoDeportivo::ESTADOS_VIGENTES, true)) {
            return back()->with('error', 'El torneo no admite equipos nuevos.');
        }

        $validado = $request->validate([
            'nombre_equipo_etd' => ['required', 'string', 'max:150'],
            'prg_id'            => ['nullable', 'integer', Rule::exists('programa', 'id_prg')->whereNull('deleted_at')],
        ], [
            'nombre_equipo_etd.required' => 'El nombre del equipo es obligatorio.',
            'prg_id.exists'              => 'El PNF seleccionado no es válido.',
        ]);

        $nombre = trim($validado['nombre_equipo_etd']);

        if ($torneo->equipos()->whereRaw('LOWER(nombre_equipo_etd) = ?', [mb_strtolower($nombre)])->exists()) {
            throw ValidationException::withMessages(['nombre_equipo_etd' => 'Ya existe un equipo con ese nombre en el torneo.']);
        }

        if (! empty($validado['prg_id']) && $torneo->equipos()->where('prg_id', $validado['prg_id'])->exists()) {
            throw ValidationException::withMessages(['prg_id' => 'Ese PNF ya tiene un equipo en este torneo.']);
        }

        $torneo->equipos()->create([
            'nombre_equipo_etd' => $nombre,
            'prg_id'            => $validado['prg_id'] ?? null,
            'estado_etd'        => 'inscrito',
            'status_id'         => 1,
        ]);

        return back()->with('success', 'Equipo "' . $nombre . '" registrado.');
    }

    public function destroyEquipo(EquipoTorneo $equipo)
    {
        $equipo->integrantes()->forceDelete();
        $equipo->forceDelete();

        return back()->with('success', 'Equipo eliminado del torneo.');
    }

    public function storeIntegrante(Request $request, EquipoTorneo $equipo)
    {
        $validado = $request->validate([
            'cedula' => ['required', 'string', 'max:20'],
        ], [
            'cedula.required' => 'Indica la cédula del integrante.',
        ]);

        $persona = ServicioInscripcion::personaPorCedula($validado['cedula']);

        $yaEnTorneo = IntegranteEquipo::query()
            ->where('per_id', $persona->id_per)
            ->whereHas('equipo', fn ($q) => $q->where('tde_id', $equipo->tde_id))
            ->exists();

        if ($yaEnTorneo) {
            throw ValidationException::withMessages(['cedula' => ServicioInscripcion::nombre($persona) . ' ya integra un equipo de este torneo.']);
        }

        $equipo->integrantes()->create([
            'per_id'    => $persona->id_per,
            'status_id' => 1,
        ]);

        return back()->with('success', ServicioInscripcion::nombre($persona) . ' agregado al equipo.');
    }

    public function destroyIntegrante(IntegranteEquipo $integrante)
    {
        $integrante->forceDelete();

        return back()->with('success', 'Integrante retirado del equipo.');
    }

    private function horariosDisponibles(?TorneoDeportivo $torneo = null)
    {
        return DeporteHorario::query()
            ->with(['actividad', 'espacio', 'calendario'])
            ->where(fn ($q) => $q->vigentes()->when($torneo, fn ($s) => $s->orWhere('id_hde', $torneo->hde_id)))
            ->whereDoesntHave('torneos', fn ($q) => $q->whereIn('estado_tde', TorneoDeportivo::ESTADOS_VIGENTES)->when($torneo, fn ($s) => $s->where('id_tde', '!=', $torneo->id_tde)))
            ->orderBy('hora_inicio_hde')
            ->get()
            ->sortBy(fn ($h) => $h->actividad?->nombre_acd . ' ' . $h->diaClave())
            ->values();
    }

    private function validar(Request $request, ?TorneoDeportivo $torneo = null): array
    {
        $validado = $request->validate([
            'nombre_tde'      => ['required', 'string', 'max:255'],
            'descripcion_tde' => ['nullable', 'string', 'max:2000'],
            'modalidad_tde'   => ['nullable', 'string', 'max:100'],
            'hde_id'          => ['required', 'integer'],
        ], [
            'nombre_tde.required' => 'El nombre del torneo es obligatorio.',
            'hde_id.required'     => 'Selecciona el horario del torneo.',
        ]);

        $horario = DeporteHorario::query()->find($validado['hde_id']);

        if (! $horario || ($horario->estado_hde !== DeporteHorario::ESTADO_ACTIVO && (int) $horario->id_hde !== (int) $torneo?->hde_id)) {
            throw ValidationException::withMessages(['hde_id' => 'Selecciona un horario activo.']);
        }

        $ocupado = $horario->torneos()
            ->whereIn('estado_tde', TorneoDeportivo::ESTADOS_VIGENTES)
            ->when($torneo, fn ($q) => $q->where('id_tde', '!=', $torneo->id_tde))
            ->exists();

        if ($ocupado) {
            throw ValidationException::withMessages(['hde_id' => 'Ese horario ya está asignado a otro torneo vigente.']);
        }

        $validado['nombre_tde'] = trim($validado['nombre_tde']);

        return $validado;
    }
}
