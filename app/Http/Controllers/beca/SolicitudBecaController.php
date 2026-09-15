<?php

namespace App\Http\Controllers\beca;

use App\Http\Controllers\Controller;
use App\Http\Requests\beca\GuardarSolicitudRequest;
use App\Models\Becas\SolicitudBeca;
use App\Models\Becas\JornadaBeca;
use App\Models\Becas\Beneficio;
use App\Models\Becas\Lapso;
use App\Models\Persona;
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

    /**
     * Muestra la bandeja de entrada de solicitudes registradas.
     */
    public function index(Request $request)
    {
        $solicitudes = $this->solicitudService->listarSolicitudes($request->all());
        $beneficios = Beneficio::where('status', 1)->get();

        return view('admin.becas.solicitudes.index', compact('solicitudes', 'beneficios'));
    }

    /**
     * Muestra el formulario para registrar una nueva solicitud.
     */
    public function create()
    {
        // Solo jornadas activas actualmente vigentes
        $hoy = now()->toDateString();
        $jornadas = JornadaBeca::where('activa', 1)
            ->whereDate('fecha_inicio_solicitud', '<=', $hoy)
            ->whereDate('fecha_fin_solicitud', '>=', $hoy)
            ->with(['beneficio', 'lapso'])
            ->get();

        // Estudiantes registrados en el sistema (perfil id 2 = estudiante/paciente)
        $estudiantes = Persona::where('id_perfil', 2)
            ->orderBy('nombre_persona')
            ->orderBy('apellido_persona')
            ->get();

        return view('admin.becas.solicitudes.create', compact('jornadas', 'estudiantes'));
    }

    /**
     * Almacena una nueva solicitud.
     */
    public function store(GuardarSolicitudRequest $request)
    {
        try {
            $this->solicitudService->crearSolicitud($request->validated());
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

    /**
     * Muestra el detalle completo de una solicitud.
     */
    public function show(int $id)
    {
        $solicitud = SolicitudBeca::with([
            'persona.personaPnf.pnf', 
            'beneficio', 
            'jornada', 
            'lapso', 
            'verificador.persona'
        ])->findOrFail($id);

        return view('admin.becas.solicitudes.show', compact('solicitud'));
    }

    /**
     * Aprueba o rechaza una solicitud.
     */
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
