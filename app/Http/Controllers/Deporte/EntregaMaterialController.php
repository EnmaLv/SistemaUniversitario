<?php

namespace App\Http\Controllers\Deporte;

use App\Exceptions\Deporte\OperacionDeporteException;
use App\Exceptions\Salud\StockInsuficienteException;
use App\Http\Controllers\Controller;
use App\Models\Deporte\EntregaMaterial;
use App\Services\Deporte\EntregaMaterialService;
use App\Support\Seguridad\MensajeError;
use App\Support\Sedes\SedeActiva;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EntregaMaterialController extends Controller
{
    public function __construct(private EntregaMaterialService $servicio)
    {
    }

    public function index(Request $request)
    {
        $buscar = trim((string) $request->query('q', ''));

        $entregas = EntregaMaterial::with(['persona', 'sede', 'usuario.persona'])
            ->withCount('detalles')
            ->when($buscar !== '', function ($consulta) use ($buscar) {
                $consulta->where(function ($filtro) use ($buscar) {
                    $filtro->where('dependencia_ema', 'like', "%{$buscar}%")
                        ->orWhere('motivo_ema', 'like', "%{$buscar}%")
                        ->orWhereHas('persona', function ($persona) use ($buscar) {
                            $persona->where('cedula_per', 'like', "%{$buscar}%")
                                ->orWhere('nombres_per', 'like', "%{$buscar}%")
                                ->orWhere('apellidos_per', 'like', "%{$buscar}%");
                        });
                });
            })
            ->orderByDesc('id_ema')
            ->paginate(10)
            ->withQueryString();

        return view('admin.deportes.entregas.index', compact('entregas', 'buscar'));
    }

    public function create()
    {
        $sedeId = (int) SedeActiva::idParaCrear();
        $materiales = $this->servicio->materialesDisponibles($sedeId);
        $sede = DB::table('sede')->where('id_sed', $sedeId)->value('nombre_sed');

        return view('admin.deportes.entregas.create', compact('materiales', 'sede'));
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'cedula'           => ['nullable', 'required_without:dependencia', 'string', 'max:20'],
            'dependencia'      => ['nullable', 'required_without:cedula', 'string', 'max:150'],
            'motivo'           => ['required', 'string', 'max:255'],
            'items'            => ['required', 'array', 'min:1'],
            'items.*.pdt_id'   => ['required', 'integer', 'exists:producto,id_pdt'],
            'items.*.cantidad' => ['required', 'numeric', 'min:0.01'],
        ], [
            'cedula.required_without'      => 'Indique la cédula de la persona o la dependencia que recibe el material.',
            'dependencia.required_without' => 'Indique la cédula de la persona o la dependencia que recibe el material.',
            'items.required'               => 'Agregue al menos un producto.',
        ]);

        try {
            $entrega = $this->servicio->registrar($datos, (int) SedeActiva::idParaCrear());
        } catch (OperacionDeporteException|StockInsuficienteException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', MensajeError::de($e));
        }

        return redirect()->route('admin.deportes.entregas.show', $entrega->id_ema)->with('success', 'Entrega de material registrada.');
    }

    public function show($id)
    {
        $entrega = EntregaMaterial::with(['persona', 'sede', 'usuario.persona', 'detalles.producto'])->findOrFail($id);

        $movimientos = DB::table('movimiento_inventario')
            ->join('lote', 'lote.id_lot', '=', 'movimiento_inventario.lot_id')
            ->join('producto', 'producto.id_pdt', '=', 'movimiento_inventario.pdt_id')
            ->where('movimiento_inventario.referencia_type_moi', EntregaMaterialService::REFERENCIA)
            ->where('movimiento_inventario.referencia_id_moi', $entrega->id_ema)
            ->orderBy('movimiento_inventario.id_moi')
            ->get(['producto.nombre_pdt', 'lote.codigo_lote_lot', 'movimiento_inventario.cantidad_moi', 'movimiento_inventario.cantidad_final_moi', 'movimiento_inventario.fecha_movimiento_moi']);

        return view('admin.deportes.entregas.show', compact('entrega', 'movimientos'));
    }
}
