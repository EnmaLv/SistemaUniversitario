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
use Illuminate\Support\Facades\Storage;

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

        $jornadasActivas = JornadaBeca::where('activa', 1)->with('beneficio')->get();
        $pendientesRenovacion = collect();
        foreach($jornadasActivas as $jornada) {
            $ids = $this->solicitudService->obtenerPendientesPorRenovar($jornada->id);
            if($ids->isNotEmpty()) {
                $personas = Persona::deSedeActiva()->whereIn('id_per', $ids)->get();
                $pendientesRenovacion->put($jornada->id, [
                    'jornada' => $jornada,
                    'personas' => $personas
                ]);
            }
        }

        return view('admin.becas.solicitudes.index', compact('solicitudes', 'beneficios', 'pendientesRenovacion'));
    }

    public function create()
    {
        $hoy = now()->toDateString();

        $jornadas = JornadaBeca::where('activa', 1)
            ->whereDate('fecha_inicio_solicitud', '<=', $hoy)
            ->whereDate('fecha_fin_solicitud', '>=', $hoy)
            ->whereHas('lapso', function ($q) {
                $q->where('es_actual', 1);
            })
            ->with(['beneficio', 'lapso'])
            ->get();

        $estudiantes = Persona::deSedeActiva()->where('id_perfil', 2)
            ->orderBy('nombres_per')
            ->orderBy('apellidos_per')
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
            ->whereHas('lapso', function ($q) {
                $q->where('es_actual', 1);
            })
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
            $datos = $request->validated();
            if (!$user->puedeAlguna(['solicitud', 'solicitudes_becas'], 'crear')) {
                $datos['per_id'] = $user->per_id;
            }

            $this->solicitudService->crearSolicitud($datos);
            
            if(!$user || $user->tieneRol(['paciente', 'becario', 'estudiante'])){
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
                ->with('error', 'Error al registrar la solicitud: ' . \App\Support\Seguridad\MensajeError::de($e));
        }
    }

    public function renovar(Request $request)
    {
        $request->validate([
            'jornada_id' => 'required|exists:be_jornadas_becas,id',
            'archivo_notas' => 'required|file|mimes:pdf|max:2048',
        ]);

        /** @var Usuario $user */
        $user = Auth::user();
        if (!$user || !$user->per_id) {
            return redirect()->back()->with('error', 'No se pudo identificar al estudiante.');
        }

        try {
            $this->solicitudService->renovarSolicitudSimplificada(
                $user->per_id,
                $request->input('jornada_id'),
                $request->file('archivo_notas')
            );
            
            return redirect()
                ->route('home')
                ->with('success', '¡Renovación de beca registrada exitosamente!');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error al renovar la beca: ' . \App\Support\Seguridad\MensajeError::de($e));
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
            'documento',
        ])->findOrFail($id);

        return view('admin.becas.solicitudes.show', compact('solicitud'));
    }

    public function descargarDocumento(int $id)
    {
        $solicitud = SolicitudBeca::with('documento')->findOrFail($id);

        /** @var Usuario $usuario */
        $usuario = Auth::user();
        $esPropia = (int) $solicitud->per_id === (int) $usuario->per_id;
        $puedeGestionar = $usuario->puedeAlguna(['solicitud', 'solicitudes_becas'], 'exportar')
            || $usuario->puedeAlguna(['solicitud', 'solicitudes_becas'], 'ver');

        abort_unless($esPropia || $puedeGestionar, 403, 'No tienes permiso para ver este documento.');

        $documento = $solicitud->documento;
        abort_unless($documento && $documento->ruta_archivo && Storage::disk('local')->exists($documento->ruta_archivo), 404);

        return Storage::disk('local')->response(
            $documento->ruta_archivo,
            basename($documento->ruta_archivo),
            ['X-Content-Type-Options' => 'nosniff']
        );
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
                ->with('error', 'Error al procesar la solicitud: ' . \App\Support\Seguridad\MensajeError::de($e));
        }
    }
}
