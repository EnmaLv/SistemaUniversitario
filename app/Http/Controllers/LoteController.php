<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lote;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\DetalleCompra;
use App\Models\MovimientoInventario;
use App\Models\Sede;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LoteController extends Controller
{
    private function procesarLotesVencidos()
    {
        $servicio = app(\App\Support\Inventario\ServicioInventario::class);
        $sede = \App\Support\Sedes\SedeActiva::id();

        DB::transaction(function () use ($servicio, $sede) {
            $lotesVencidos = Lote::whereDate('fecha_vencimiento_lot', '<=', now())
                ->where('status_id', Lote::STATUS_ACTIVO)
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
                    ->whereNull('inventario_sede_lote.deleted_at')
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

        if ($fecha_desde && $fecha_hasta) {
            $query->whereBetween('fecha_vencimiento_lot', [$fecha_desde, $fecha_hasta]);
        } elseif ($fecha_desde) {
            $query->where('fecha_entrada_lot', '>=', $fecha_desde);
        } elseif ($fecha_hasta) {
            $query->where('fecha_vencimiento_lot', '<=', $fecha_hasta);
        }

        $lotes = $query->orderBy('id_lot', 'desc')->paginate(10)->withQueryString();

        $lotes->each(function ($lote) {
            $fecha = Carbon::parse($lote->fecha_vencimiento_lot);
            $lote->is_expired      = $fecha->isPast();
            $lote->days_to_expire = now()->diffInDays($fecha, false);
        });

        $hayLotesVencidosSinMerma = Lote::whereDate('fecha_vencimiento_lot', '<=', now())
            ->where('status_id', Lote::STATUS_ACTIVO)
            ->exists();

        $sedeNombre = Sede::where('id_sed', $sedeId)->value('nombre_sed');

        return view('admin.movimientos.lotes.index', compact('lotes', 'hayLotesVencidosSinMerma', 'sedeNombre'));
    }
}