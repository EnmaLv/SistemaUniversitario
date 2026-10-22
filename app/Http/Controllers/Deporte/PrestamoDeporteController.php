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
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PrestamoDeporteController extends Controller
{
    public function __construct(private PrestamoService $servicio)
    {
    }

    public function index(Request $request)
    {
        $this->servicio->marcarVencidos();

        $buscar = trim((string) $request->query('q', ''));
        $estado = $request->query('estado');

        $prestamos = PrestamoDeportivo::with(['persona', 'producto', 'sede'])
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
            ->when(in_array($estado, [
                PrestamoDeportivo::ESTADO_ACTIVO,
                PrestamoDeportivo::ESTADO_DEVUELTO,
                PrestamoDeportivo::ESTADO_VENCIDO,
                PrestamoDeportivo::ESTADO_SANCIONADO,
                PrestamoDeportivo::ESTADO_REPUESTO,
            ], true), fn ($consulta) => $consulta->where('estado_prd', $estado))
            ->orderByDesc('id_prd')
            ->paginate(10)
            ->withQueryString();

        return view('admin.deportes.prestamos.index', compact('prestamos', 'buscar', 'estado'));
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

        $implementos = $this->servicio->implementosDisponibles($sedeId);
        $espacios = $this->espacios($sedeId);
        $sede = DB::table('sede')->where('id_sed', $sedeId)->value('nombre_sed');

        return view('admin.deportes.prestamos.create', compact('cedula', 'persona', 'situacion', 'implementos', 'espacios', 'sede'));
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'cedula'                    => ['required', 'string', 'max:20'],
            'pdt_id'                    => ['nullable', 'integer', 'exists:producto,id_pdt'],
            'lot_id'                    => ['required', 'integer', 'exists:lote,id_lot'],
            'cantidad'                  => ['required', 'integer', 'min:1', 'max:100'],
            'motivo'                    => ['required', 'string', 'max:255'],
            'epd_id'                    => ['nullable', 'integer', Rule::exists('espacio_deportivo', 'id_epd')->where('sed_id', (int) SedeActiva::idParaCrear())->whereNull('deleted_at')],
            'lugar'                     => ['nullable', 'required_without:epd_id', 'string', 'max:255'],
            'fecha_prestamo'            => ['nullable', 'date', 'before_or_equal:' . now()->addMinutes(5)->format('Y-m-d H:i:s')],
            'fecha_devolucion_esperada' => ['required', 'date', 'after:now'],
            'observaciones'             => ['nullable', 'string', 'max:1000'],
        ], [
            'lugar.required_without'          => 'Indique el espacio deportivo o el lugar donde se usará el implemento.',
            'fecha_devolucion_esperada.after' => 'La devolución esperada debe ser una fecha futura.',
        ]);

        try {
            $prestamo = $this->servicio->registrar($datos, (int) SedeActiva::idParaCrear());
        } catch (OperacionDeporteException|StockInsuficienteException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', MensajeError::de($e));
        }

        return redirect()->route('admin.deportes.prestamos.show', $prestamo->id_prd)->with('success', 'Préstamo registrado.');
    }

    public function show($id)
    {
        $prestamo = PrestamoDeportivo::with(['persona', 'producto', 'lote', 'sede', 'registradoPor.persona', 'recibidoPor.persona', 'sanciones' => fn ($consulta) => $consulta->todasLasSedes()->orderByDesc('id_snd')])
            ->findOrFail($id);

        return view('admin.deportes.prestamos.show', compact('prestamo'));
    }

    public function updateDevolucion(Request $request, $id)
    {
        $prestamo = PrestamoDeportivo::findOrFail($id);

        $datos = $request->validate([
            'estado_devolucion' => ['required', Rule::in(PrestamoDeportivo::ESTADOS_DEVOLUCION)],
            'observaciones'     => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $prestamo = $this->servicio->devolver($prestamo->id_prd, $datos['estado_devolucion'], $datos['observaciones'] ?? null);
        } catch (OperacionDeporteException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return back()->with('error', MensajeError::de($e));
        }

        $mensaje = $prestamo->estado_prd === PrestamoDeportivo::ESTADO_DEVUELTO
            ? 'Devolución registrada: el implemento volvió al inventario.'
            : 'Devolución registrada con sanción activa hasta la reposición del implemento.';

        return redirect()->route('admin.deportes.prestamos.show', $prestamo->id_prd)->with('success', $mensaje);
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
