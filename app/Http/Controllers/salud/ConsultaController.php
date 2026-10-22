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
use App\Support\Auditoria\Bitacora;
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
            'personas'     => Persona::where('status_id', 1)->orderBy('nombres_per')->get(),
        ]);
    }

    public function store(ConsultaRequest $request): RedirectResponse
    {
        $consulta = Consulta::registrar(
            $request->datosConsulta(),
            $request->enfermedadesIds(),
            auth()->id()
        );

        $this->registrarAtencionTardia($consulta);

        return redirect()
            ->route(self::RUTA . '.recetacion', $consulta)
            ->with('success', 'Consulta registrada exitosamente. Continúe con la recetación.');
    }

    public function update(ConsultaRequest $request, Consulta $consulta): RedirectResponse
    {
        if (!$consulta->esEditable()) {
            return $this->bloqueadaPorEntregas($consulta);
        }

        $anteriores = [
            'fecha_cme'                  => optional($consulta->fecha_cme)->toDateString(),
            'motivo_registro_tardio_cme' => $consulta->motivo_registro_tardio_cme,
        ];

        $consulta->actualizarConEnfermedades(
            $request->datosConsulta(),
            $request->enfermedadesIds()
        );

        if ($anteriores['fecha_cme'] !== optional($consulta->fecha_cme)->toDateString()
            || $anteriores['motivo_registro_tardio_cme'] !== $consulta->motivo_registro_tardio_cme) {
            $this->registrarAtencionTardia($consulta, $anteriores);
        }

        return redirect()
            ->route(self::RUTA . '.recetacion', $consulta)
            ->with('success', 'Consulta actualizada correctamente. Continúe con la recetación.');
    }

    public function recetacion(Consulta $consulta)
    {
        $consulta->load(Consulta::CARGA_RECETACION);
        $unidadesDosis = $this->recetas->unidadesDosis();
        $permitidas = $this->recetas->unidadesDosisPermitidas($consulta);

        return view(self::VISTA . '.recetacion', [
            'consulta'         => $consulta,
            'estadoInicial'    => $this->recetas->estadoInicialFormulario($consulta->receta),
            'tieneDatosReales' => $consulta->receta !== null,
            'productos'        => $this->recetas->productosParaFormulario($unidadesDosis),
            'unidadesDosis'    => $unidadesDosis,
            'unidadesExtra'    => array_values(array_diff($permitidas, $unidadesDosis->pluck('nombre_udo')->all())),
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
            'factores'        => $this->dispensaciones->factoresPorDetalle($consulta),
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
            return back()->withInput()->with('error', 'Error al dispensar: ' . \App\Support\Seguridad\MensajeError::de($e));
        } catch (Throwable $e) {
            report($e);

            return back()->withInput()->with('error', 'Error al dispensar. Intente nuevamente.');
        }

        return redirect()
            ->route(self::RUTA . '.index')
            ->with('success', $this->dispensaciones->mensajeResultado($resultado));
    }

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
            $consulta->paciente->cedula_per ?? 'sin-cedula',
            optional($consulta->fecha_cme)->format('Y-m-d') ?? 'sin-fecha'
        );

        return Pdf::loadView(self::VISTA . '.recipe', compact('consulta'))
            ->setPaper('a5', 'portrait')
            ->stream($nombreArchivo);
    }

    public function buscarEnfermedades(BusquedaRequest $request)
    {
        if (!$request->terminoValido()) {
            return response()->json([]);
        }

        $q = $request->termino();

        return response()->json(
            Enfermedad::where('categoria_efm', Enfermedad::TIPO_FISICA)
                ->where('status_id', 1)
                ->where('nombre_efm', 'like', "%{$q}%")
                ->orderBy('nombre_efm')
                ->limit(15)
                ->get(['id_efm', 'nombre_efm', 'codigo_efm'])
                ->map(fn($enfermedad) => [
                    'id'     => $enfermedad->id_efm,
                    'nombre' => $enfermedad->nombre_efm,
                    'codigo' => $enfermedad->codigo_efm,
                ])
        );
    }

    public function buscarPersonas(BusquedaRequest $request)
    {
        if (!$request->terminoValido()) {
            return response()->json([]);
        }

        $q = $request->termino();

        $personas = Persona::deSedeActiva()->where(function ($query) use ($q) {
            $query->where('nombres_per', 'like', "%{$q}%")
                ->orWhere('apellidos_per', 'like', "%{$q}%")
                ->orWhere('cedula_per', 'like', "%{$q}%");
        })
            ->limit(10)
            ->get()
            ->map(fn($persona) => [
                'per_id' => $persona->id_per,
                'nombre'     => ' ' . ($persona->cedula_per ?? 'S/C')
                    . ' ' . ($persona->nombres_per ?? '')
                    . ' ' . ($persona->apellidos_per ?? ''),
            ]);

        return response()->json($personas);
    }

    public function estadisticas(
        EstadisticasRequest $request,
        SaludHomeService $service,
        EstadisticasExportService $exportador
    ) {
        $data    = $service->getDashboardData($request->all());
        $filtros = $request->validated();

        return match ($request->formato()) {
            'json' => response()->json([
                'consultas'   => $data['consultas'],
                'resumen'     => $data['resumen'],
                'fechaInicio' => $data['fechaInicio'],
                'fechaFin'    => $data['fechaFin'],
            ]),
            'pdf'  => $exportador->pdf($data, $request->periodo(), $request->tipoReporte(), $filtros),
            'word' => $exportador->word($data, $request->periodo(), $request->tipoReporte(), $filtros),
        };
    }

    private function registrarAtencionTardia(Consulta $consulta, ?array $anteriores = null): void
    {
        if (! $consulta->esRegistroTardio()) {
            return;
        }

        Bitacora::registrar(
            accion: 'consulta_registro_tardio',
            modulo: 'salud',
            tablaAfectada: 'consulta_medica',
            registroId: $consulta->id_cme,
            datosAnteriores: $anteriores,
            datosModificados: [
                'fecha_cme'                  => $consulta->fecha_cme->toDateString(),
                'fecha_registro'             => $consulta->fechaRegistro(),
                'motivo_registro_tardio_cme' => $consulta->motivo_registro_tardio_cme,
            ],
            observacion: 'Atención del ' . $consulta->fecha_cme->format('d/m/Y')
                . ' registrada el ' . \Carbon\Carbon::parse($consulta->fechaRegistro())->format('d/m/Y')
                . '. Motivo: ' . $consulta->motivo_registro_tardio_cme,
        );
    }

    private function consultoriosActivos()
    {
        return Consultorio::where('status_id', 1)->orderBy('nombre_csr')->get();
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