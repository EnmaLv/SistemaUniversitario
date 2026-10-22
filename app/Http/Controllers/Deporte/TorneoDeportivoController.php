<?php

namespace App\Http\Controllers\Deporte;

use App\Http\Controllers\Controller;
use App\Models\Deporte\DeporteActividad;
use App\Models\Deporte\DeporteCalendario;
use App\Models\Deporte\DeporteEspacio;
use App\Models\Deporte\DeporteHorario;
use App\Models\Deporte\EquipoTorneo;
use App\Models\Deporte\IntegranteEquipo;
use App\Models\Deporte\TorneoDeportivo;
use App\Models\Pnf;
use App\Support\Auditoria\Bitacora;
use App\Support\Deportes\SedesDeporte;
use App\Support\Deportes\ServicioActividades;
use App\Support\Deportes\ServicioInscripcion;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

        $participantes = DB::table('integrante_equipo_deporte as i')
            ->join('equipo_torneo_deportivo as e', 'e.id_etd', '=', 'i.etd_id')
            ->whereIn('e.tde_id', $torneos->pluck('id_tde')->all() ?: [0])
            ->whereNull('i.deleted_at')
            ->whereNull('e.deleted_at')
            ->groupBy('e.tde_id')
            ->selectRaw('e.tde_id, COUNT(*) AS total')
            ->pluck('total', 'tde_id');

        return view('admin.deportes.torneos.index', compact('torneos', 'buscar', 'estado', 'participantes'));
    }

    public function create()
    {
        return view('admin.deportes.torneos.form', $this->datosFormulario(null));
    }

    public function store(Request $request)
    {
        $datos = $this->validar($request);

        $torneo = DB::transaction(function () use ($datos) {
            $actividad = ServicioActividades::guardar($this->cabecera($datos), [$this->bloque($datos)]);

            return TorneoDeportivo::create([
                'nombre_tde'      => $datos['nombre_tde'],
                'descripcion_tde' => $datos['descripcion_tde'] ?? null,
                'modalidad_tde'   => $datos['modalidad_tde'] ?? null,
                'estado_tde'      => TorneoDeportivo::ESTADO_ABIERTO,
                'hde_id'          => $actividad->horarios->first()->id_hde,
                'use_id'          => Auth::id(),
                'status_id'       => 1,
            ]);
        });

        return redirect()->route('admin.deportes.torneos.show', $torneo->id_tde)->with('success', 'Torneo registrado. Ahora agrega los equipos y sus integrantes por cédula.');
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

        $programas = Pnf::query()->where('status_id', 1)->orderBy('nombre_prg')->get(['id_prg', 'nombre_prg']);
        $participantes = $torneo->equipos->sum(fn ($equipo) => $equipo->integrantes->count());

        return view('admin.deportes.torneos.show', compact('torneo', 'programas', 'participantes'));
    }

    public function edit(TorneoDeportivo $torneo)
    {
        if (! $torneo->estaVigente()) {
            return redirect()->route('admin.deportes.torneos.show', $torneo->id_tde)->with('error', 'No se puede editar un torneo finalizado o cancelado.');
        }

        return view('admin.deportes.torneos.form', $this->datosFormulario($torneo));
    }

    public function update(Request $request, TorneoDeportivo $torneo)
    {
        if (! $torneo->estaVigente()) {
            return back()->with('error', 'No se puede editar un torneo finalizado o cancelado.');
        }

        $torneo->load('horario.calendario');
        $bloque = $torneo->horario;
        $actividad = $bloque?->calendario;

        abort_unless($bloque && $actividad && $actividad->esTorneo(), 404);

        $datos = $this->validar($request, $actividad);

        DB::transaction(function () use ($datos, $torneo, $bloque, $actividad) {
            ServicioActividades::guardar($this->cabecera($datos), [$this->bloque($datos, $bloque->id_hde)], $actividad);

            $torneo->update([
                'nombre_tde'      => $datos['nombre_tde'],
                'descripcion_tde' => $datos['descripcion_tde'] ?? null,
                'modalidad_tde'   => $datos['modalidad_tde'] ?? null,
            ]);
        });

        return redirect()->route('admin.deportes.torneos.show', $torneo->id_tde)->with('success', 'Torneo actualizado correctamente.');
    }

    public function updateEstado(Request $request, TorneoDeportivo $torneo)
    {
        $validado = $request->validate([
            'estado_tde' => ['required', Rule::in(array_keys(TorneoDeportivo::ESTADOS))],
        ]);

        if (! $torneo->estaVigente()) {
            return back()->with('error', 'El torneo ya está ' . mb_strtolower($torneo->estadoLegible()) . ' y no puede cambiar de estado.');
        }

        $anterior = $torneo->estado_tde;

        DB::transaction(function () use ($torneo, $validado) {
            $torneo->update(['estado_tde' => $validado['estado_tde']]);

            $estadoBloque = match ($validado['estado_tde']) {
                TorneoDeportivo::ESTADO_CANCELADO  => DeporteHorario::ESTADO_CANCELADO,
                TorneoDeportivo::ESTADO_FINALIZADO => DeporteHorario::ESTADO_COMPLETADO,
                default                            => DeporteHorario::ESTADO_ACTIVO,
            };

            DeporteHorario::query()->whereKey($torneo->hde_id)->first()?->update([
                'estado_hde'             => $estadoBloque,
                'motivo_cancelacion_hde' => $estadoBloque === DeporteHorario::ESTADO_CANCELADO ? 'Torneo cancelado.' : null,
            ]);
        });

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
        $torneo->load(['horario.actividad', 'horario.espacio', 'horario.calendario.sede', 'equipos']);

        abort_unless($torneo->horario && $torneo->horario->calendario, 404);

        return Pdf::loadView('admin.deportes.carteles.pdf', [
            'actividad' => $torneo->horario->calendario,
            'bloques'   => collect([$torneo->horario]),
            'torneo'    => $torneo,
        ])->setPaper('letter', 'portrait')->stream('cartel_torneo_' . $torneo->id_tde . '.pdf');
    }

    public function storeEquipo(Request $request, TorneoDeportivo $torneo)
    {
        if (! $torneo->estaVigente()) {
            return back()->with('error', 'El torneo ya terminó o fue cancelado: no admite equipos nuevos.');
        }

        $validado = $request->validate([
            'nombre_equipo_etd' => ['required', 'string', 'max:150'],
            'prg_id'            => ['nullable', 'integer', Rule::exists('programa', 'id_prg')->whereNull('deleted_at')],
        ], [
            'nombre_equipo_etd.required' => 'Escribe el nombre del equipo.',
            'prg_id.exists'              => 'El PNF elegido no es válido.',
        ]);

        $nombre = trim($validado['nombre_equipo_etd']);

        if ($torneo->equipos()->whereRaw('LOWER(nombre_equipo_etd) = ?', [mb_strtolower($nombre)])->exists()) {
            throw ValidationException::withMessages(['nombre_equipo_etd' => 'Ya hay un equipo con ese nombre en el torneo.']);
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

        return back()->with('success', 'Equipo "' . $nombre . '" registrado. Ahora agrega sus integrantes por cédula.');
    }

    public function destroyEquipo(EquipoTorneo $equipo)
    {
        $equipo->loadMissing('torneo');

        if ($equipo->torneo && ! $equipo->torneo->estaVigente()) {
            return back()->with('error', 'El torneo ya terminó o fue cancelado: no se pueden quitar equipos.');
        }

        if (DB::table('asistencia_deportiva')->whereIn('ied_id', $equipo->integrantes()->pluck('id_ied')->all() ?: [0])->exists()) {
            return back()->with('error', 'No se puede eliminar el equipo: sus integrantes ya tienen asistencia registrada.');
        }

        DB::transaction(function () use ($equipo) {
            $equipo->integrantes()->forceDelete();
            $equipo->forceDelete();
        });

        return back()->with('success', 'Equipo eliminado del torneo.');
    }

    public function storeIntegrante(Request $request, EquipoTorneo $equipo)
    {
        $validado = $request->validate([
            'cedula' => ['required', 'string', 'max:2000'],
        ], [
            'cedula.required' => 'Escribe la cédula del integrante.',
        ]);

        $equipo->loadMissing('torneo');

        if ($equipo->torneo && ! $equipo->torneo->estaVigente()) {
            return back()->with('error', 'El torneo ya terminó o fue cancelado: no admite integrantes nuevos.');
        }

        $agregados = [];
        $problemas = [];

        foreach (array_unique(ServicioInscripcion::separarCedulas($validado['cedula'])) as $cedula) {
            try {
                $persona = ServicioInscripcion::personaPorCedula($cedula);
            } catch (ValidationException $e) {
                $problemas[] = collect($e->errors())->flatten()->first();

                continue;
            }

            $yaEnTorneo = IntegranteEquipo::query()
                ->with('equipo')
                ->where('per_id', $persona->id_per)
                ->whereHas('equipo', fn ($q) => $q->where('tde_id', $equipo->tde_id))
                ->first();

            if ($yaEnTorneo) {
                $problemas[] = ServicioInscripcion::nombre($persona) . ' ya integra el equipo "' . ($yaEnTorneo->equipo?->nombre_equipo_etd ?? 'otro') . '" de este torneo.';

                continue;
            }

            $retirado = IntegranteEquipo::query()->onlyTrashed()->where('etd_id', $equipo->id_etd)->where('per_id', $persona->id_per)->first();

            if ($retirado) {
                $retirado->restore();
            } else {
                $equipo->integrantes()->create([
                    'per_id'    => $persona->id_per,
                    'status_id' => 1,
                ]);
            }

            $agregados[] = ServicioInscripcion::nombre($persona);
        }

        if ($agregados === [] && $problemas !== []) {
            throw ValidationException::withMessages(['cedula' => $problemas]);
        }

        $respuesta = back()->with('success', count($agregados) === 1
            ? $agregados[0] . ' agregado(a) al equipo.'
            : count($agregados) . ' integrantes agregados al equipo.');

        return $problemas === [] ? $respuesta : $respuesta->withErrors(['cedula' => $problemas]);
    }

    public function destroyIntegrante(IntegranteEquipo $integrante)
    {
        $integrante->loadMissing('equipo.torneo');

        if ($integrante->equipo?->torneo && ! $integrante->equipo->torneo->estaVigente()) {
            return back()->with('error', 'El torneo ya terminó o fue cancelado: no se pueden quitar integrantes.');
        }

        if (DB::table('asistencia_deportiva')->where('ied_id', $integrante->id_ied)->exists()) {
            $integrante->delete();
        } else {
            $integrante->forceDelete();
        }

        return back()->with('success', 'Integrante retirado del equipo.');
    }

    private function datosFormulario(?TorneoDeportivo $torneo): array
    {
        $bloque = $torneo?->horario()->with('calendario')->first();

        return [
            'torneo'      => $torneo,
            'bloque'      => $bloque,
            'actividad'   => $bloque?->calendario,
            'disciplinas' => DeporteActividad::query()->where(fn ($q) => $q->where('status_id', 1)->when($bloque, fn ($s) => $s->orWhere('id_acd', $bloque->acd_id)))->orderBy('nombre_acd')->get(['id_acd', 'nombre_acd']),
            'espacios'    => DeporteEspacio::query()->with('sede')->where(fn ($q) => $q->where('status_id', 1)->when($bloque, fn ($s) => $s->orWhere('id_epd', $bloque->epd_id)))->orderBy('nombre_epd')->get(['id_epd', 'nombre_epd', 'sed_id']),
            'sedes'       => SedesDeporte::opciones(),
        ];
    }

    private function validar(Request $request, ?DeporteCalendario $actividad = null): array
    {
        $validado = $request->validate([
            'nombre_tde'       => ['required', 'string', 'max:255'],
            'descripcion_tde'  => ['nullable', 'string', 'max:2000'],
            'modalidad_tde'    => ['nullable', 'string', 'max:100'],
            'acd_id'           => ['required', 'integer'],
            'epd_id'           => ['required', 'integer'],
            'fecha_inicio_cld' => ['required', 'date'],
            'fecha_fin_cld'    => ['required', 'date', 'after_or_equal:fecha_inicio_cld'],
            'hora_inicio'      => ['required', 'date_format:H:i'],
            'hora_fin'         => ['required', 'date_format:H:i', 'after:hora_inicio'],
            'dias'             => ['nullable', 'array'],
            'dias.*'           => ['string', Rule::in(DeporteCalendario::DIAS)],
            'sed_id'           => ['nullable', 'integer'],
        ], [
            'nombre_tde.required'           => 'Escribe el nombre del torneo.',
            'acd_id.required'               => 'Elige la disciplina del torneo.',
            'epd_id.required'               => 'Elige el espacio donde se jugará.',
            'fecha_inicio_cld.required'     => 'Indica la fecha de inicio del torneo.',
            'fecha_fin_cld.required'        => 'Indica la fecha final del torneo.',
            'fecha_fin_cld.after_or_equal'  => 'La fecha final debe ser igual o posterior a la de inicio.',
            'hora_inicio.required'          => 'Indica la hora de inicio.',
            'hora_fin.required'             => 'Indica la hora de fin.',
            'hora_fin.after'                => 'La hora de fin debe ser después de la hora de inicio.',
        ]);

        $validado['nombre_tde'] = trim($validado['nombre_tde']);
        $validado['sed_id'] = SedesDeporte::resolver($validado['sed_id'] ?? $actividad?->sed_id);

        return $validado;
    }

    private function cabecera(array $datos): array
    {
        return [
            'nombre_cld'       => $datos['nombre_tde'],
            'descripcion_cld'  => $datos['descripcion_tde'] ?? null,
            'tipo_cld'         => DeporteCalendario::TIPO_TORNEO,
            'fecha_inicio_cld' => $datos['fecha_inicio_cld'],
            'fecha_fin_cld'    => $datos['fecha_fin_cld'],
            'dias'             => $datos['dias'] ?? [],
            'sed_id'           => $datos['sed_id'],
        ];
    }

    private function bloque(array $datos, ?int $id = null): array
    {
        return [
            'id'          => $id,
            'acd_id'      => (int) $datos['acd_id'],
            'epd_id'      => (int) $datos['epd_id'],
            'hora_inicio' => $datos['hora_inicio'],
            'hora_fin'    => $datos['hora_fin'],
            'cupo'        => null,
        ];
    }
}
