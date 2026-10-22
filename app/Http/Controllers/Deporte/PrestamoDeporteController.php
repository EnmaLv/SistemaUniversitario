<?php

namespace App\Http\Controllers\Deporte;

use App\Exceptions\Deporte\OperacionDeporteException;
use App\Exceptions\Salud\StockInsuficienteException;
use App\Http\Controllers\Controller;
use App\Models\Deporte\PrestamoDeportivo;
use App\Models\Deporte\SancionDeportiva;
use App\Services\Deporte\PrestamoService;
use App\Support\Seguridad\MensajeError;
use App\Support\Sedes\SedeActiva;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PrestamoDeporteController extends Controller
{
    private const ESTADOS_FILTRO = [
        PrestamoDeportivo::ESTADO_ACTIVO,
        PrestamoDeportivo::ESTADO_VENCIDO,
        PrestamoDeportivo::ESTADO_SANCIONADO,
        PrestamoDeportivo::ESTADO_DEVUELTO,
        PrestamoDeportivo::ESTADO_REPUESTO,
    ];

    public function __construct(private PrestamoService $servicio)
    {
    }

    public function index(Request $request)
    {
        $this->servicio->marcarVencidos();

        $buscar = trim((string) $request->query('q', ''));
        $estado = in_array($request->query('estado'), self::ESTADOS_FILTRO, true) ? $request->query('estado') : null;
        $tipo = array_key_exists((string) $request->query('tipo'), PrestamoDeportivo::TIPOS_PRODUCTO) ? $request->query('tipo') : null;

        $prestamos = PrestamoDeportivo::with(['persona', 'producto', 'sede', 'detalles.producto'])
            ->withExists(['sanciones as tiene_sancion_activa' => fn ($consulta) => $consulta->todasLasSedes()->where('estado_snd', SancionDeportiva::ESTADO_ACTIVA)])
            ->when($buscar !== '', function ($consulta) use ($buscar) {
                $patron = '%' . mb_strtolower($buscar) . '%';

                $consulta->whereHas('persona', function ($persona) use ($patron) {
                    $persona->where(fn ($filtro) => $filtro
                        ->whereRaw('LOWER(cedula_per) LIKE ?', [$patron])
                        ->orWhereRaw('LOWER(nombres_per) LIKE ?', [$patron])
                        ->orWhereRaw('LOWER(apellidos_per) LIKE ?', [$patron]));
                });
            })
            ->deTipo($tipo)
            ->when($estado !== null, fn ($consulta) => $consulta->where('estado_prd', $estado))
            ->orderByDesc('id_prd')
            ->paginate(10)
            ->withQueryString();

        return view('admin.deportes.prestamos.index', compact('prestamos', 'buscar', 'estado', 'tipo'));
    }

    public function create(Request $request)
    {
        $sedeId = (int) SedeActiva::idParaCrear();
        $cedula = trim((string) $request->query('cedula', ''));
        $persona = null;
        $situacion = null;

        if ($cedula !== '') {
            $persona = PrestamoService::buscarPersona($cedula);
            $situacion = $persona ? $this->servicio->situacionPersona((int) $persona->id_per) : null;
        }

        $productos = $this->servicio->productosDisponibles($sedeId);
        $espacios = $this->espacios($sedeId);
        $sede = DB::table('sede')->where('id_sed', $sedeId)->value('nombre_sed');
        $usuario = Auth::user();
        $entregadoPor = trim((string) ($usuario?->nombre_completo ?: $usuario?->username_use));

        return view('admin.deportes.prestamos.create', compact('cedula', 'persona', 'situacion', 'productos', 'espacios', 'sede', 'entregadoPor'));
    }

    public function store(Request $request)
    {
        $sedeId = (int) SedeActiva::idParaCrear();

        $datos = $request->validate([
            'cedula'                    => ['required', 'string', 'max:20'],
            'tipo'                      => ['required', Rule::in(array_keys(PrestamoDeportivo::TIPOS_PRODUCTO))],
            'items'                     => ['required', 'array', 'min:1', 'max:20'],
            'items.*.pdt_id'            => ['required', 'integer', 'exists:producto,id_pdt'],
            'items.*.cantidad'          => ['required', 'integer', 'min:1', 'max:1000'],
            'motivo'                    => ['required', 'string', 'max:255'],
            'epd_id'                    => ['nullable', 'integer', Rule::exists('espacio_deportivo', 'id_epd')->where('sed_id', $sedeId)->whereNull('deleted_at')],
            'lugar'                     => ['nullable', 'required_without:epd_id', 'string', 'max:255'],
            'fecha_prestamo'            => ['nullable', 'date', 'before_or_equal:' . now()->addMinutes(5)->format('Y-m-d H:i:s')],
            'fecha_devolucion_esperada' => ['required', 'date', 'after:now'],
            'observaciones'             => ['nullable', 'string', 'max:1000'],
        ], [
            'tipo.required'                   => 'Elija si presta implementos deportivos o material de oficina.',
            'items.required'                  => 'Agregue al menos un producto.',
            'items.*.pdt_id.required'         => 'Elija el producto de cada línea.',
            'items.*.cantidad.integer'        => 'Las cantidades se cuentan por unidad: use números enteros.',
            'items.*.cantidad.min'            => 'Cada cantidad debe ser de al menos 1 unidad.',
            'lugar.required_without'          => 'Indique dónde se usará lo prestado: un espacio deportivo o un lugar.',
            'fecha_devolucion_esperada.after' => 'La fecha "hasta" debe ser futura.',
        ]);

        try {
            $prestamo = $this->servicio->registrar($datos, $sedeId);
        } catch (OperacionDeporteException|StockInsuficienteException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', MensajeError::de($e));
        }

        return redirect()->route('admin.deportes.prestamos.show', $prestamo->id_prd)->with('success', 'Préstamo registrado.');
    }

    public function show($id)
    {
        $prestamo = PrestamoDeportivo::with([
            'persona', 'producto', 'lote', 'sede', 'espacio', 'registradoPor.persona', 'recibidoPor.persona',
            'detalles.producto', 'detalles.lote',
            'sanciones' => fn ($consulta) => $consulta->todasLasSedes()->orderByDesc('id_snd'),
        ])->findOrFail($id);

        return view('admin.deportes.prestamos.show', compact('prestamo'));
    }

    public function updateDevolucion(Request $request, $id)
    {
        $prestamo = PrestamoDeportivo::findOrFail($id);

        $datos = $request->validate([
            'lineas'               => ['nullable', 'required_without:estado_devolucion', 'array'],
            'lineas.*.devuelta'    => ['nullable', 'integer', 'min:0'],
            'lineas.*.danada'      => ['nullable', 'integer', 'min:0'],
            'lineas.*.consumida'   => ['nullable', 'integer', 'min:0'],
            'estado_devolucion'    => ['nullable', 'required_without:lineas', Rule::in(PrestamoDeportivo::ESTADOS_DEVOLUCION)],
            'observaciones'        => ['nullable', 'string', 'max:1000'],
        ], [
            'lineas.required_without'    => 'Indique qué pasó con cada producto prestado.',
            'lineas.*.devuelta.integer'  => 'Las cantidades se cuentan por unidad: use números enteros.',
            'lineas.*.danada.integer'    => 'Las cantidades se cuentan por unidad: use números enteros.',
            'lineas.*.consumida.integer' => 'Las cantidades se cuentan por unidad: use números enteros.',
        ]);

        try {
            $prestamo = ! empty($datos['lineas'])
                ? $this->servicio->devolver($prestamo->id_prd, $datos['lineas'], $datos['observaciones'] ?? null)
                : $this->servicio->devolverConEstado($prestamo->id_prd, $datos['estado_devolucion'], $datos['observaciones'] ?? null);
        } catch (OperacionDeporteException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', MensajeError::de($e));
        }

        $mensaje = $prestamo->estado_prd === PrestamoDeportivo::ESTADO_DEVUELTO
            ? 'Devolución registrada: lo devuelto en buen estado volvió al inventario y lo gastado quedó como consumo.'
            : 'Devolución registrada. Hay material dañado o perdido: la persona queda sancionada hasta que lo reponga.';

        return redirect()->route('admin.deportes.prestamos.show', $prestamo->id_prd)->with('success', $mensaje);
    }

    public function redirigirEntregas()
    {
        return redirect()->route('admin.deportes.prestamos.index', ['tipo' => PrestamoDeportivo::TIPO_OFICINA]);
    }

    private function espacios(int $sedeId)
    {
        return DB::table('espacio_deportivo')
            ->where('sed_id', $sedeId)
            ->whereNull('deleted_at')
            ->where('status_id', 1)
            ->orderBy('nombre_epd')
            ->get(['id_epd', 'nombre_epd']);
    }
}
