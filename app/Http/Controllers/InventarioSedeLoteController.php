<?php

namespace App\Http\Controllers;

use App\Models\InventarioSedeLote;
use App\Models\InventarioTotal;
use App\Models\Lote;
use App\Models\Sede;
use App\Support\Inventario\FiltroTipoProducto;
use Illuminate\Http\Request;

class InventarioSedeLoteController extends Controller
{
    public function index(Request $request)
    {
        $tipoProducto = FiltroTipoProducto::resolver($request);
        $tiposProducto = FiltroTipoProducto::opciones();

        $sedes = Sede::withCount('inventarioSedeLotes')->get();

        foreach ($sedes as $sede) {
            $sede->totalInventarioSedeLotes = FiltroTipoProducto::aplicarPorLote(
                InventarioSedeLote::where('sed_id', $sede->id_sed),
                $tipoProducto,
                'inventario_sede_lote.lot_id'
            )->sum('cantidad_isl');
        }

        return view('admin.movimientos.sedes_lotes.index', compact('sedes', 'tipoProducto', 'tiposProducto'));
    }

    public function show($id, Request $request)
    {
        $buscar = $request->input('buscar');
        $activo = $request->has('estado') ? (int) $request->input('estado') : 1;
        $tipoProducto = FiltroTipoProducto::resolver($request);
        $tiposProducto = FiltroTipoProducto::opciones();

        $query = InventarioSedeLote::query()
            ->where('sed_id', $id)
            ->with(['lote.producto', 'lote.proveedor']);

        if ($buscar) {
            $query->where(function ($q) use ($buscar) {
                $q->whereHas('lote', function ($l) use ($buscar) {
                    $l->where('codigo_lote_lot', 'like', "%{$buscar}%");
                })->orWhereHas('lote.producto', function ($p) use ($buscar) {
                    $p->where('nombre_pdt', 'like', "%{$buscar}%");
                });
            });
        }

        $query->whereHas('lote', function ($l) use ($activo) {
            $l->where('status_id', $activo === 1 ? Lote::STATUS_ACTIVO : Lote::STATUS_INACTIVO);
        });

        FiltroTipoProducto::aplicarPorLote($query, $tipoProducto, 'inventario_sede_lote.lot_id');

        $sede = $query->orderBy('id_isl', 'desc')
            ->paginate(10)
            ->withQueryString()
            ->appends(FiltroTipoProducto::PARAMETRO, FiltroTipoProducto::valorUrl($tipoProducto));

        $sedes = Sede::withCount('inventarioSedeLotes')->get();

        $totales = FiltroTipoProducto::aplicar(
            InventarioTotal::with('producto')->where('sed_id', $id),
            $tipoProducto,
            'inventario_total.pdt_id'
        )
            ->orderByDesc('cantidad_total_int')
            ->get();

        return view('admin.movimientos.sedes_lotes.show', compact('sede', 'sedes', 'buscar', 'activo', 'totales', 'tipoProducto', 'tiposProducto'));
    }
}
