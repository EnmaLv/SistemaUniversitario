<?php

namespace App\Http\Controllers\Deporte;

use App\Http\Controllers\Controller;
use App\Models\Deporte\DeporteHorario;
use App\Models\Deporte\InscripcionDeportiva;
use App\Models\Deporte\TorneoDeportivo;
use App\Support\Cuentas\Cedula;
use App\Support\Deportes\ServicioInscripcion;
use Illuminate\Http\Request;

class InscripcionDeportivaController extends Controller
{
    public function index(Request $request)
    {
        $horarios = DeporteHorario::query()
            ->with(['actividad', 'espacio'])
            ->vigentes()
            ->withCount(['inscripcionesVigentes as inscritos_count'])
            ->orderBy('hora_inicio_hde')
            ->get()
            ->sortBy(fn ($h) => ($h->actividad?->nombre_acd ?? '') . ' ' . array_search($h->diaClave(), DeporteHorario::DIAS))
            ->values();

        $torneos = TorneoDeportivo::query()
            ->with('horario')
            ->where('estado_tde', TorneoDeportivo::ESTADO_ABIERTO)
            ->orderBy('nombre_tde')
            ->get();

        $horario = null;
        $torneo = null;
        $inscripciones = collect();

        if ($request->filled('tde_id')) {
            $torneo = TorneoDeportivo::query()->find($request->integer('tde_id'));
            $horarioId = $torneo?->hde_id;
        } else {
            $horarioId = $request->integer('hde_id') ?: null;
        }

        if ($horarioId) {
            $horario = DeporteHorario::query()
                ->with(['actividad', 'espacio', 'calendario', 'torneos'])
                ->find($horarioId);
        }

        if ($horario) {
            $torneo ??= $horario->torneos->sortByDesc('id_tde')->first();
            $inscripciones = $horario->inscripciones()
                ->with(['persona.perfil', 'registradoPor'])
                ->orderByRaw("CASE WHEN estado_ide = 'retirado' THEN 1 ELSE 0 END")
                ->orderBy('fecha_registro_ide')
                ->orderBy('id_ide')
                ->get();
        }

        return view('admin.deportes.inscripciones.index', compact('horarios', 'torneos', 'horario', 'torneo', 'inscripciones'));
    }

    public function buscarPersona(Request $request)
    {
        $cedula = (string) $request->query('cedula', '');

        if (Cedula::normalizar($cedula) === null) {
            return response()->json(['encontrada' => false, 'mensaje' => 'La cédula no tiene un formato válido.'], 422);
        }

        $persona = ServicioInscripcion::buscarPersona($cedula);

        if (! $persona) {
            return response()->json(['encontrada' => false, 'mensaje' => 'No existe ninguna persona con esa cédula.'], 404);
        }

        $persona->loadMissing(['perfil', 'sede']);

        return response()->json([
            'encontrada' => true,
            'persona'    => [
                'cedula' => $persona->cedula_per,
                'nombre' => ServicioInscripcion::nombre($persona),
                'perfil' => $persona->perfil?->nombre_perfil,
                'sede'   => $persona->sede?->nombre_sed,
                'activa' => (int) $persona->status_id === 1,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validado = $request->validate([
            'cedula'            => ['required', 'string', 'max:20'],
            'hde_id'            => ['nullable', 'integer', 'required_without:tde_id'],
            'tde_id'            => ['nullable', 'integer'],
            'observaciones_ide' => ['nullable', 'string', 'max:1000'],
        ], [
            'cedula.required'         => 'Indica la cédula de la persona a inscribir.',
            'hde_id.required_without' => 'Selecciona un horario o un torneo.',
        ]);

        if (! empty($validado['tde_id'])) {
            $torneo = TorneoDeportivo::query()->findOrFail($validado['tde_id']);
            $inscripcion = ServicioInscripcion::inscribirEnTorneo($torneo, $validado['cedula'], $validado['observaciones_ide'] ?? null);
            $destino = ['tde_id' => $torneo->id_tde];
        } else {
            $horario = DeporteHorario::query()->findOrFail($validado['hde_id']);
            $inscripcion = ServicioInscripcion::inscribir($horario, $validado['cedula'], $validado['observaciones_ide'] ?? null);
            $destino = ['hde_id' => $horario->id_hde];
        }

        $inscripcion->load('persona');

        return redirect()->route('admin.deportes.inscripciones.index', $destino)
            ->with('success', ServicioInscripcion::nombre($inscripcion->persona) . ' quedó inscrito(a) correctamente.');
    }

    public function destroy(InscripcionDeportiva $inscripcion)
    {
        if (! $inscripcion->estaVigente()) {
            return back()->with('error', 'La inscripción ya estaba retirada.');
        }

        ServicioInscripcion::retirar($inscripcion);

        return redirect()->route('admin.deportes.inscripciones.index', ['hde_id' => $inscripcion->hde_id])
            ->with('success', 'Inscripción retirada. El cupo quedó liberado.');
    }
}
