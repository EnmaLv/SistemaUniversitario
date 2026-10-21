<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MovimientoInventario;
use App\Utilities\PdfGeneratorUtil;

class MovimientoInventarioController extends Controller
{
    public function index(Request $request)
    {
        $buscar = $request->input('buscar');
        $tipo_movimiento_moi = $request->input('tipo_movimiento_moi');
        $fecha_desde = $request->input('fecha_desde');
        $fecha_hasta = $request->input('fecha_hasta');
        $activo = $request->input('estado');

        $movimiento = MovimientoInventario::getData([
            'buscar'          => $buscar,
            'tipo_movimiento_moi' => $tipo_movimiento_moi,
            'fecha_desde'     => $fecha_desde,
            'fecha_hasta'     => $fecha_hasta,
            'activo'          => $activo,
        ], false);

        return view('admin.movimientos.historial_movimientos.index', compact('movimiento'));
    }

    public function generarPdf(Request $request)
    {
        $filtro = [
            'buscar'          => $request->input('buscar'),
            'tipo_movimiento_moi' => $request->input('tipo_movimiento_moi'),
            'fecha_desde'     => $request->input('fecha_desde'),
            'fecha_hasta'     => $request->input('fecha_hasta'),
            'activo'          => $request->input('estado'),
        ];

        $movimiento = MovimientoInventario::getData($filtro, true);
        $datos = [
            'movimiento' => $movimiento,
            'filtro'     => $filtro,
        ];

        return PdfGeneratorUtil::ShowPdf('pdf.movimiento_inventario', $datos, "MovimientoInventario");
    }
}