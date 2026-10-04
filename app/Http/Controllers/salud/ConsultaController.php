<?php

namespace App\Http\Controllers\salud;

use App\Exceptions\Salud\StockInsuficienteException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Salud\BusquedaRequest;
use App\Http\Requests\Salud\ConsultaRequest;
use App\Http\Requests\Salud\DispensacionRequest;
use App\Http\Requests\Salud\EstadisticasRequest;
use App\Http\Requests\Salud\ListarConsultasRequest;
use App\Http\Requests\Salud\RecetaRequest;
use App\Models\Persona;
use App\Models\salud\Consulta;
use App\Models\salud\Consultorio;
use App\Models\salud\Enfermedad;
use App\Models\salud\HorarioConsultorio;
use App\Services\Salud\DispensacionService;
use App\Services\Salud\EstadisticasExportService;
use App\Services\Salud\RecetaService;
use App\Services\Salud\SaludHomeService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Throwable;

class ConsultaController extends Controller
{
    private const RUTA = 'admin.salud.movimientos.consultas';
    private const VISTA = 'admin.salud.movimientos.consultas';

    private const MSG_CON_ENTREGAS = 'No se puede modificar la consulta porque ya cuenta con entregas registradas.';

    public function __construct(
        private readonly RecetaService $recetas,
        private readonly DispensacionService $dispensaciones,
    ) {}

    /* ══════════════════════════════════════════════════════════════
     |  Listado
     ══════════════════════════════════════════════════════════════ */

    public function index(ListarConsultasRequest $request)
    {
        $consultas = Consulta::listar(
            $request->filtro('buscar'),
            $request->filtro('consultorio_id'),
            $request->filtro('medico_id'),
            $request->filtro('rango_fechas'),
            $request->filtro('estado')
        );

        return view(self::VISTA . '.index', [
            'consultas'    => $consultas,
            'consultorios' => $this->consultoriosActivos(),
            'medicos'      => HorarioConsultorio::usuariosElegibles(),
        ]);
    }

    /* ══════════════════════════════════════════════════════════════
     |  Paso 1: Consulta
     ══════════════════════════════════════════════════════════════ */

    public function create(?Consulta $consulta = null)
    {
        if ($consulta?->exists && !$consulta->esEditable()) {
            return $this->bloqueadaPorEntregas($consulta);
        }

        $consulta = $consulta?->exists
            ? $consulta->load(['enfermedades', 'paciente'])
            : new Consulta();

        return view(self::VISTA . '.create', [
            'consulta'     => $consulta,
            'consultorios' => $this->consultoriosActivos(),
            'medicos'      => HorarioConsultorio::usuariosElegibles(),
            'personas'     => Persona::where('estado', true)->orderBy('nombre_persona')->get(),
        ]);
    }

    public function store(ConsultaRequest $request): RedirectResponse
    {
        $consulta = Consulta::registrar(
            $request->datosConsulta(),
            $request->enfermedadesIds(),
            auth()->id()
        );

        return redirect()
            ->route(self::RUTA . '.recetacion', $consulta)
            ->with('success', 'Consulta registrada exitosamente. Continúe con la recetación.');
    }

    public function update(ConsultaRequest $request, Consulta $consulta): RedirectResponse
    {
        if (!$consulta->esEditable()) {
            return $this->bloqueadaPorEntregas($consulta);
        }

        $consulta->actualizarConEnfermedades(
            $request->datosConsulta(),
            $request->enfermedadesIds()
        );

        return redirect()
            ->route(self::RUTA . '.recetacion', $consulta)
            ->with('success', 'Consulta actualizada correctamente. Continúe con la recetación.');
    }

    /* ══════════════════════════════════════════════════════════════
     |  Paso 2: Receta
     ══════════════════════════════════════════════════════════════ */

    public function recetacion(Consulta $consulta)
    {
        $consulta->load(Consulta::CARGA_RECETACION);

        return view(self::VISTA . '.recetacion', [
            'consulta'         => $consulta,
            'estadoInicial'    => $this->recetas->estadoInicialFormulario($consulta->receta),
            'tieneDatosReales' => $consulta->receta !== null,
            'productos'        => $this->recetas->productosSalud(),
            'unidades'         => $this->recetas->unidades(),
        ]);
    }

    public function storeReceta(RecetaRequest $request, Consulta $consulta): RedirectResponse
    {
        try {
            $this->recetas->guardar($consulta, $request->validated(), auth()->id());
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with('error', 'Ocurrió un error al guardar la receta. Intente nuevamente.');
        }

        return redirect()
            ->route(self::RUTA . '.dispensacion', $consulta)
            ->with('success', 'Receta guardada correctamente. Proceda con la dispensación.');
    }

    /* ══════════════════════════════════════════════════════════════
     |  Paso 3: Dispensación
     ══════════════════════════════════════════════════════════════ */

    public function dispensacion(Consulta $consulta)
    {
        if (!$consulta->receta) {
            return redirect()
                ->route(self::RUTA . '.recetacion', $consulta)
                ->with('error', 'Debe registrar la receta médica antes de acceder a la dispensación.');
        }

        $consulta->load(Consulta::CARGA_DISPENSACION);
        $sedeId = $this->sedeActual();

        return view(self::VISTA . '.dispensacion', [
            'consulta'        => $consulta,
            'sedeId'          => $sedeId,
            'lotesPorDetalle' => $this->dispensaciones->lotesPorDetalle($consulta, $sedeId),
        ]);
    }

    public function storeDispensacion(DispensacionRequest $request, Consulta $consulta): RedirectResponse
    {
        if (!$consulta->receta) {
            return back()->withInput()->with('error', 'Esta consulta no tiene una receta activa.');
        }

        try {
            $resultado = $this->dispensaciones->dispensar(
                $consulta,
                $request->itemsDispensacion(),
                $this->sedeActual(),
                auth()->id()
            );
        } catch (StockInsuficienteException $e) {
            return back()->withInput()->with('error', 'Error al dispensar: ' . $e->getMessage());
        } catch (Throwable $e) {
            report($e);

            return back()->withInput()->with('error', 'Error al dispensar. Intente nuevamente.');
        }

        return redirect()
            ->route(self::RUTA . '.index')
            ->with('success', $this->dispensaciones->mensajeResultado($resultado));
    }

    /* ══════════════════════════════════════════════════════════════
     |  Histórico y PDF
     ══════════════════════════════════════════════════════════════ */

    public function show(Consulta $consulta)
    {
        $consulta->load(Consulta::CARGA_HISTORICO);

        return view(self::VISTA . '.show', compact('consulta'));
    }

    public function generarRecipePdf(Consulta $consulta)
    {
        if (!$consulta->receta) {
            return redirect()
                ->back()
                ->with('error', 'Esta consulta no tiene un récipe médico registrado.');
        }

        ini_set('memory_limit', '256M');
        $consulta->load(Consulta::CARGA_RECIPE);

        $nombreArchivo = sprintf(
            'recipe-%s-%s.pdf',
            $consulta->paciente->cedula_persona ?? 'sin-cedula',
            $consulta->fecha->format('Y-m-d')
        );

        return Pdf::loadView(self::VISTA . '.recipe', compact('consulta'))
            ->setPaper('a5', 'portrait')
            ->stream($nombreArchivo);
    }

    public function generarConstanciaPdf(Consulta $consulta)
    {
        $consulta->load(Consulta::CARGA_CONSTANCIA);

        if (!$consulta->paciente) {
            return redirect()->back()->with('error', 'La consulta no tiene un paciente asociado.');
        }

        $nombreArchivo = sprintf(
            'constancia-%s-%s.pdf',
            $consulta->paciente->cedula_persona ?? 'sin-cedula',
            $consulta->fecha->format('Y-m-d')
        );

        return Pdf::loadView('admin.salud.movimientos.consultas.constancia', [
            'consulta' => $consulta,
            'numero'   => $consulta->numeroConstancia(),
            'programa' => $consulta->programaDelPaciente(),
            'emision'  => now(),
        ])->setPaper('letter', 'portrait')->stream($nombreArchivo);
    }

    /* ══════════════════════════════════════════════════════════════
     |  AJAX
     ══════════════════════════════════════════════════════════════ */

    public function buscarEnfermedades(BusquedaRequest $request)
    {
        if (!$request->terminoValido()) {
            return response()->json([]);
        }

        $q = $request->termino();

        return response()->json(
            Enfermedad::where('categoria', Enfermedad::TIPO_FISICA)
                ->where('activo', 1)
                ->where('nombre', 'like', "%{$q}%")
                ->orderBy('nombre')
                ->limit(15)
                ->get(['id', 'nombre', 'codigo'])
        );
    }

    public function buscarPersonas(BusquedaRequest $request)
    {
        if (!$request->terminoValido()) {
            return response()->json([]);
        }

        $q = $request->termino();

        $personas = Persona::where(function ($query) use ($q) {
            $query->where('nombre_persona', 'like', "%{$q}%")
                ->orWhere('apellido_persona', 'like', "%{$q}%")
                ->orWhere('cedula_persona', 'like', "%{$q}%");
        })
            ->limit(10)
            ->get()
            ->map(fn($persona) => [
                'id_persona' => $persona->id_persona,
                'nombre'     => ' ' . ($persona->cedula_persona ?? 'S/C')
                    . ' ' . ($persona->nombre_persona ?? '')
                    . ' ' . ($persona->apellido_persona ?? ''),
            ]);

        return response()->json($personas);
    }

    /* ══════════════════════════════════════════════════════════════
     |  Estadísticas
     ══════════════════════════════════════════════════════════════ */
    public function estadisticas(
        EstadisticasRequest $request,
        SaludHomeService $service,
        EstadisticasExportService $exportador
    ) {
        $data    = $service->getDashboardData($request->all());
        $filtros = $request->validated();

        if ($request->esListado()) {
            return $exportador->listado(
                $request->tipoReporte(),
                $request->formato(),
                $data,
                $request->periodo(),
                $filtros
            );
        }

        return match ($request->formato()) {
            'json' => response()->json([
                'consultas'   => $data['consultas'],
                'resumen'     => $data['resumen'],
                'fechaInicio' => $data['fechaInicio'],
                'fechaFin'    => $data['fechaFin'],
            ]),
            'pdf'   => $exportador->pdf($data, $request->periodo(), $request->tipoReporte(), $filtros),
            'word'  => $exportador->word($data, $request->periodo(), $request->tipoReporte(), $filtros),
            default => abort(400, 'Formato no soportado.'),
        };
    }

    /* ══════════════════════════════════════════════════════════════
     |  Helpers
     ══════════════════════════════════════════════════════════════ */

    private function consultoriosActivos()
    {
        return Consultorio::where('activo', true)->orderBy('nombre')->get();
    }

    private function sedeActual(): int
    {
        return DispensacionService::sedeDelUsuario(auth()->user());
    }

    private function bloqueadaPorEntregas(Consulta $consulta): RedirectResponse
    {
        return redirect()
            ->route(self::RUTA . '.show', $consulta)
            ->with('error', self::MSG_CON_ENTREGAS);
    }
}
