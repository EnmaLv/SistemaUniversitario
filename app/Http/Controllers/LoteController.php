<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lote;
use App\Models\Modulo;
use App\Models\TipoProducto;
use App\Models\Sede;
use App\Support\Inventario\FiltroTipoProducto;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Concerns\FiltraPorModuloYTipos;

class LoteController extends Controller
{
    use FiltraPorModuloYTipos;
    
    private function procesarLotesVencidos()
    {
        $servicio          = app(\App\Support\Inventario\ServicioInventario::class);
        $sede              = \App\Support\Sedes\SedeActiva::id();
        $idsSinVencimiento = TipoProducto::idsTiposSinVencimiento();
        $permitidos        = $this->tiposDelModulo();

        DB::transaction(function () use ($servicio, $sede, $idsSinVencimiento, $permitidos) {
            $lotesVencidos = Lote::whereNotNull('fecha_vencimiento_lot')
                ->whereDate('fecha_vencimiento_lot', '<=', now())
                ->where('status_id', Lote::STATUS_ACTIVO)
                ->whereHas('producto.categoria', function ($q) use ($idsSinVencimiento, $permitidos) {
                    if ($idsSinVencimiento !== []) {
                        $q->whereNotIn('tpr_id', $idsSinVencimiento);
                    }
                    if ($permitidos !== null) {
                        $q->whereIn('tpr_id', $permitidos);
                    }
                })
                ->get();

            foreach ($lotesVencidos as $lote) {
                $existencias = DB::table('inventario_sede_lote')
                    ->where('lot_id', $lote->id_lot)
                    ->when($sede !== null, fn ($consulta) => $consulta->where('sed_id', $sede))
                    ->where('cantidad_isl', '>', 0)
                    ->get();

                foreach ($existencias as $existencia) {
                    $servicio->salida((int) $lote->id_lot, (int) $existencia->sed_id, (float) $existencia->cantidad_isl, (float) $existencia->cantidad_convertida_isl, [
                        'tipo'                => \App\Support\Inventario\ServicioInventario::MERMA,
                        'referencia_type_moi' => 'lote_vencido',
                        'referencia_id_moi'   => $lote->id_lot,
                        'observacion_moi'     => 'Producto vencido – merma manual',
                    ]);
                }

                $quedaStock = DB::table('inventario_sede_lote')->where('lot_id', $lote->id_lot)->where('cantidad_isl', '>', 0)->exists();

                if (! $quedaStock) {
                    $lote->update(['status_id' => Lote::STATUS_INACTIVO]);
                }
            }
        });
    }

    public function mermarVencidos()
    {
        $this->procesarLotesVencidos();
        return redirect()->route('admin.movimientos.lotes.index')->with('success', 'Productos vencidos mermados exitosamente.');
    }

    public function index(Request $request)
    {
        $buscar      = $request->input('buscar');
        $estado      = $request->input('estado');
        $fecha_desde = $request->input('fecha_desde');
        $fecha_hasta = $request->input('fecha_hasta');
        $filtro      = $request->input('filtro');

        $sedeId = \App\Support\Sedes\SedeActiva::idParaCrear();

        $query = Lote::with(['producto', 'proveedor'])
            ->addSelect([
                'lote.*',
                'cantidad_sede' => DB::table('inventario_sede_lote')
                    ->selectRaw('COALESCE(SUM(cantidad_isl), 0)')
                    ->whereColumn('inventario_sede_lote.lot_id', 'lote.id_lot')
                    ->where('inventario_sede_lote.sed_id', $sedeId)
                    ->whereNull('inventario_sede_lote.deleted_at'),
                'cantidad_gramos_sede' => DB::table('inventario_sede_lote')
                    ->selectRaw('COALESCE(SUM(cantidad_convertida_isl), 0)')
                    ->whereColumn('inventario_sede_lote.lot_id', 'lote.id_lot')
                    ->where('inventario_sede_lote.sed_id', $sedeId)
                    ->whereNull('inventario_sede_lote.deleted_at'),
            ]);

        if ($filtro === 'vencido') {
            $query->whereDate('fecha_vencimiento_lot', '<', now()->addDays(1));
        }
        if ($filtro === 'por_vencer') {
            $query->whereDate('fecha_vencimiento_lot', '>', now());
        }

        if ($request->filled('estado')) {
            $query->where('status_id', (int) $estado === 1 ? Lote::STATUS_ACTIVO : Lote::STATUS_INACTIVO);
        } else {
            $query->where('status_id', Lote::STATUS_ACTIVO);
        }

        if ($buscar) {
            $query->where('codigo_lote_lot', 'like', "%{$buscar}%");
        }

        $permitidos    = $this->tiposDelModulo();
        $tipoProducto  = $this->resolverTipoProductoRequest($request);
        if ($permitidos !== null && ! in_array($tipoProducto, $permitidos, true)) {
            $tipoProducto = $permitidos[0] ?? null;
        }
        FiltroTipoProducto::aplicar($query, $tipoProducto, 'lote.pdt_id');

        $tiposProducto = $this->opcionesTipoProducto();
        if ($permitidos !== null) {
            $tiposProducto = $tiposProducto
                ->filter(fn ($t) => in_array((int) $t->id_tpr, $permitidos, true))
                ->values();
        }

        if ($fecha_desde && $fecha_hasta) {
            $query->whereBetween('fecha_vencimiento_lot', [$fecha_desde, $fecha_hasta]);
        } elseif ($fecha_desde) {
            $query->where('fecha_entrada_lot', '>=', $fecha_desde);
        } elseif ($fecha_hasta) {
            $query->where('fecha_vencimiento_lot', '<=', $fecha_hasta);
        }

        $lotes = $query->orderBy('id_lot', 'desc')
            ->paginate(10)
            ->withQueryString()
            ->appends(FiltroTipoProducto::PARAMETRO, FiltroTipoProducto::valorUrl($tipoProducto));

        $productoIds       = $lotes->pluck('pdt_id')->filter()->unique()->all();
        $idsSinVencimiento = TipoProducto::idsProductosSinVencimiento($productoIds);

        $lotes->each(function ($lote) use ($idsSinVencimiento) {
            if (in_array((int) $lote->pdt_id, $idsSinVencimiento, true)
                || $lote->fecha_vencimiento_lot === null) {
                $lote->no_aplica_vencimiento = true;
                $lote->is_expired            = false;
                $lote->days_to_expire        = null;
                return;
            }

            $fecha = Carbon::parse($lote->fecha_vencimiento_lot);
            $lote->is_expired            = $fecha->isPast();
            $lote->days_to_expire        = now()->diffInDays($fecha, false);
            $lote->no_aplica_vencimiento = false;
        });

        $hayLotesVencidosSinMerma = $this->hayLotesVencidosSinMermar($sedeId);

        $sedeNombre = Sede::where('id_sed', $sedeId)->value('nombre_sed');

        return view('admin.movimientos.lotes.index', compact(
            'lotes',
            'hayLotesVencidosSinMerma',
            'sedeNombre',
            'tipoProducto',
            'tiposProducto'
        ));
    }

    private function hayLotesVencidosSinMermar(int $sedeId): bool
    {
        $idsSinVencimiento = TipoProducto::idsTiposSinVencimiento();
        $permitidos        = $this->tiposDelModulo();

        return Lote::whereNotNull('fecha_vencimiento_lot')
            ->whereDate('fecha_vencimiento_lot', '<=', now())
            ->where('status_id', Lote::STATUS_ACTIVO)
            ->whereHas('producto.categoria', function ($q) use ($idsSinVencimiento, $permitidos) {
                if ($idsSinVencimiento !== []) {
                    $q->whereNotIn('tpr_id', $idsSinVencimiento);
                }
                if ($permitidos !== null) {
                    $q->whereIn('tpr_id', $permitidos);
                }
            })
            ->whereHas('inventarioSedeLotes', function ($q) use ($sedeId) {
                $q->where('sed_id', $sedeId)
                ->where('cantidad_isl', '>', 0)
                ->whereNull('deleted_at');
            })
            ->exists();
    }
    
}