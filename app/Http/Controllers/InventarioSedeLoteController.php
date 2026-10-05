<?php

namespace App\Http\Controllers;

use App\Models\InventarioSedeLote;
use App\Models\Sede;
use Illuminate\Http\Request;

class InventarioSedeLoteController extends Controller
{
    public function index()
    {
        $sedes = Sede::withCount('inventarioSedeLotes')->get();

        foreach ($sedes as $sede) {
            $sede->totalInventarioSedeLotes = InventarioSedeLote::where('sede_id', $sede->id)->sum('cantidad');
        }

        return view('admin.movimientos.sedes_lotes.index', compact('sedes'));
    }

    public function show($id, Request $request)
    {
        $buscar = $request->input('buscar');
        $activo = $request->has('estado') ? (int) $request->input('estado') : 1;

        $query = InventarioSedeLote::query()
            ->where('sede_id', $id)
            ->with(['lote.producto', 'lote.proveedor']);

        if ($buscar) {
            $query->where(function ($q) use ($buscar) {
                $q->whereHas('lote', function ($l) use ($buscar) {
                    $l->where('codigo_lote', 'like', "%{$buscar}%");
                })->orWhereHas('lote.producto', function ($p) use ($buscar) {
                    $p->where('nombre', 'like', "%{$buscar}%");
                });
            });
        }

        $query->whereHas('lote', function ($l) use ($activo) {
            $l->where('estado', $activo);
        });

        $sede = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();

        $sedes = Sede::withCount('inventarioSedeLotes')->get();

        return view('admin.movimientos.sedes_lotes.show', compact('sede', 'sedes', 'buscar', 'activo'));
    }
}