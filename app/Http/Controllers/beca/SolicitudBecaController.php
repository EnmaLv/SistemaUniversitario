<?php

namespace App\Http\Controllers\beca;

use App\Http\Controllers\Controller;
use App\Http\Requests\beca\GuardarSolicitudRequest;
use App\Models\Becas\BecaPregunta;
use App\Models\Becas\SolicitudBeca;
use App\Models\Becas\JornadaBeca;
use App\Models\Becas\Beneficio;
use App\Models\Becas\JornadaCriterio;
use App\Models\Becas\Lapso;
use App\Models\Persona;
use App\Models\Usuario;
use App\Services\becas\SolicitudBecasService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SolicitudBecaController extends Controller
{
    protected SolicitudBecasService $solicitudService;

    public function __construct(SolicitudBecasService $solicitudService)
    {
        $this->solicitudService = $solicitudService;
    }

    public function index(Request $request)
    {
        $solicitudes = $this->solicitudService->listarSolicitudes($request->all());
        $beneficios = Beneficio::where('status', 1)->get();

        return view('admin.becas.solicitudes.index', compact('solicitudes', 'beneficios'));
    }

    public function create()
    {
        $hoy = now()->toDateString();

        $jornadas = JornadaBeca::where('activa', 1)
            ->whereDate('fecha_inicio_solicitud', '<=', $hoy)
            ->whereDate('fecha_fin_solicitud', '>=', $hoy)
            ->with(['beneficio', 'lapso'])
            ->get();

        $estudiantes = Persona::where('id_perfil', 2)
            ->orderBy('nombre_persona')
            ->orderBy('apellido_persona')
            ->get();

        $formularios = $this->construirFormularios($jornadas);

        return view('admin.becas.solicitudes.create',
            compact('jornadas', 'estudiantes', 'formularios'));
    }

    public function solicitarEstudiante()
    {
        $hoy = now()->toDateString();

        $jornadas = JornadaBeca::where('activa', 1)
            ->whereDate('fecha_inicio_solicitud', '<=', $hoy)
            ->whereDate('fecha_fin_solicitud', '>=', $hoy)
            ->with(['beneficio', 'lapso'])
            ->get();

        $formularios = $this->construirFormularios($jornadas);

        return view('admin.becas.solicitudes.create_estudiante',
            compact('jornadas', 'formularios'));
    }

    private function construirFormularios($jornadas): array
    {
        $beneficioIds = $jornadas->pluck('beneficio_id')->unique()->all();

        $preguntas = BecaPregunta::whereIn('id_be_beneficio', $beneficioIds)
            ->where('activo', true)
            ->with('opciones')
            ->orderBy('id_be_beneficio')
            ->orderBy('orden')
            ->get()
            ->groupBy('id_be_beneficio');

        $criteriosPorJornada = JornadaCriterio::whereIn('id_jornada', $jornadas->pluck('id'))
            ->get()
            ->groupBy('id_jornada');

        $formularios = [];

        foreach ($jornadas as $jornada) {
            $preguntasBeneficio = $preguntas[$jornada->beneficio_id] ?? collect();
            $criterios = $criteriosPorJornada[$jornada->id] ?? collect();

            $criteriosMap = $criterios->keyBy('id_pregunta');

            $formularios[$jornada->id] = [
                'beneficio_id' => $jornada->beneficio_id,
                'lapso_id'     => $jornada->lapsos_id,
                'preguntas'    => $preguntasBeneficio->map(function ($p) use ($criteriosMap) {
                    $c = $criteriosMap[$p->id] ?? null;

                    return [
                        'id'             => $p->id,
                        'codigo'         => $p->codigo,
                        'etiqueta'       => $p->etiqueta,
                        'placeholder'    => $p->placeholder,
                        'tipo'           => $p->tipo,
                        'obligatoria'    => (bool) $p->obligatoria,
                        'valor_min'      => $p->valor_min,
                        'valor_max'      => $p->valor_max,
                        'min_length'     => $p->min_length,
                        'max_length'     => $p->max_length,
                        'regex'          => $p->regex,
                        'opciones'       => $p->opciones->map(fn ($o) => [
                            'etiqueta' => $o->etiqueta,
                            'valor'    => $o->valor,
                        ])->values()->all(),
                        'criterio'       => $c ? [
                            'operador'        => $c->operador,
                            'valor_esperado'  => $c->valor_esperado,
                            'es_eliminatoria' => (bool) $c->es_eliminatoria,
                        ] : null,
                    ];
                })->values()->all(),
            ];
        }

        return $formularios;
    }

    public function store(GuardarSolicitudRequest $request)
    {
        /** @var Usuario $user */
        $user = Auth::user();
        try {
            $this->solicitudService->crearSolicitud($request->validated());
            
            if(!$user || !$user->tieneRol(['paciente', 'becario', 'estudiante'])){
                return redirect()
                ->route('home')
                ->with('success', 'Solicitud de beca registrada exitosamente.');
            }
            return redirect()
                ->route('admin.becas.solicitudes.index')
                ->with('success', 'Solicitud de beca registrada exitosamente.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error al registrar la solicitud: ' . $e->getMessage());
        }
    }

    public function show(int $id)
    {
        $solicitud = SolicitudBeca::with([
            'persona.personaPnf.pnf',
            'beneficio',
            'jornada',
            'lapso',
            'verificador.persona',
            'respuestas.pregunta.opciones',
        ])->findOrFail($id);

        return view('admin.becas.solicitudes.show', compact('solicitud'));
    }

    public function verificar(Request $request, int $id)
    {
        $request->validate([
            'estado' => 'required|in:1,2',
            'comentario_verificador' => 'required_if:estado,2|nullable|string|max:1000',
        ], [
            'comentario_verificador.required_if' => 'Debe ingresar la razón o comentario en caso de rechazo.',
        ]);

        try {
            $verificadorId = Auth::user()->id_usuario;
            $this->solicitudService->verificarSolicitud(
                $id, 
                intval($request->estado), 
                $request->comentario_verificador, 
                $verificadorId
            );

            $mensaje = intval($request->estado) === 1 ? 'aprobada' : 'rechazada';

            return redirect()
                ->route('admin.becas.solicitudes.index')
                ->with('success', "La solicitud de beca ha sido {$mensaje} correctamente.");
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error al procesar la solicitud: ' . $e->getMessage());
        }
    }
}
