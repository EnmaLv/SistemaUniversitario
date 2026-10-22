<?php

namespace App\Http\Controllers\Deporte;

use App\Http\Controllers\Controller;
use App\Models\Deporte\DeporteCalendario;
use App\Models\Deporte\DeporteHorario;
use App\Models\Deporte\InscripcionDeportiva;
use App\Support\Cuentas\Cedula;
use App\Support\Deportes\ServicioInscripcion;
use Carbon\Carbon;
use Illuminate\Http\Request;

class InscripcionDeportivaController extends Controller
{
    public function index(Request $request)
    {
        $actividades = DeporteCalendario::query()
            ->actividades()
            ->with([
                'programa',
                'horarios' => fn ($q) => $q->vigentes()
                    ->with(['actividad', 'espacio'])
                    ->withCount(['inscripcionesVigentes as inscritos_count'])
                    ->orderBy('hora_inicio_hde'),
            ])
            ->where('status_id', 1)
            ->where('con_inscripcion_cld', true)
            ->where('fecha_fin_cld', '>=', Carbon::today()->subDays(30)->toDateString())
            ->orderBy('fecha_inicio_cld')
            ->get()
            ->filter(fn ($actividad) => $actividad->horarios->isNotEmpty())
            ->values();

        $bloque = null;
        $inscripciones = collect();

        if ($request->filled('hde_id')) {
            $bloque = DeporteHorario::query()
                ->with(['calendario.programa', 'actividad', 'espacio', 'torneos'])
                ->find($request->integer('hde_id'));
        }

        if ($bloque && ! $bloque->esDeTorneo()) {
            $inscripciones = $bloque->inscripciones()
                ->with(['persona.perfil', 'registradoPor.persona'])
                ->orderByRaw("CASE WHEN estado_ide = 'retirado' THEN 1 ELSE 0 END")
                ->orderBy('fecha_registro_ide')
                ->orderBy('id_ide')
                ->get();
        }

        return view('admin.deportes.inscripciones.index', compact('actividades', 'bloque', 'inscripciones'));
    }

    public function buscarPersona(Request $request)
    {
        $cedula = (string) $request->query('cedula', '');

        if (Cedula::normalizar($cedula) === null) {
            return response()->json(['encontrada' => false, 'mensaje' => 'La cédula no es válida. Escríbela solo con números.'], 422);
        }

        $persona = ServicioInscripcion::buscarPersona($cedula);

        if (! $persona) {
            return response()->json(['encontrada' => false, 'mensaje' => 'No hay ninguna persona registrada con esa cédula.'], 404);
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
            'hde_id'            => ['required', 'integer'],
            'observaciones_ide' => ['nullable', 'string', 'max:1000'],
        ], [
            'cedula.required' => 'Escribe la cédula de la persona que quieres inscribir.',
            'hde_id.required' => 'Primero elige la actividad y el bloque donde se inscribe.',
        ]);

        $bloque = DeporteHorario::query()->findOrFail($validado['hde_id']);
        $inscripcion = ServicioInscripcion::inscribir($bloque, $validado['cedula'], $validado['observaciones_ide'] ?? null);
        $inscripcion->load('persona');

        return redirect()->route('admin.deportes.inscripciones.index', ['hde_id' => $bloque->id_hde])
            ->with('success', ServicioInscripcion::nombre($inscripcion->persona) . ' quedó inscrito(a).');
    }

    public function destroy(InscripcionDeportiva $inscripcion)
    {
        if (! $inscripcion->estaVigente()) {
            return back()->with('error', 'La inscripción ya estaba retirada.');
        }

        ServicioInscripcion::retirar($inscripcion);

        return redirect()->route('admin.deportes.inscripciones.index', ['hde_id' => $inscripcion->hde_id])
            ->with('success', 'Inscripción retirada. El cupo quedó libre.');
    }
}
