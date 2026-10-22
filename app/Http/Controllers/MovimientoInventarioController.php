<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MovimientoInventario;
use App\Support\Inventario\FiltroTipoProducto;
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
        $tipoProducto = FiltroTipoProducto::resolver($request);
        $tiposProducto = FiltroTipoProducto::opciones();

        $movimiento = MovimientoInventario::getData([
            'buscar'          => $buscar,
            'tipo_movimiento_moi' => $tipo_movimiento_moi,
            'fecha_desde'     => $fecha_desde,
            'fecha_hasta'     => $fecha_hasta,
            'activo'          => $activo,
            'tipo_producto'   => $tipoProducto,
        ], false);

        $movimiento->appends(FiltroTipoProducto::PARAMETRO, FiltroTipoProducto::valorUrl($tipoProducto));

        return view('admin.movimientos.historial_movimientos.index', compact('movimiento', 'tipoProducto', 'tiposProducto'));
    }

    public function generarPdf(Request $request)
    {
        $tipoProducto = FiltroTipoProducto::resolver($request);

        $filtro = [
            'buscar'          => $request->input('buscar'),
            'tipo_movimiento_moi' => $request->input('tipo_movimiento_moi'),
            'fecha_desde'     => $request->input('fecha_desde'),
            'fecha_hasta'     => $request->input('fecha_hasta'),
            'activo'          => $request->input('estado'),
            'tipo_producto'   => $tipoProducto,
            'tipo_producto_nombre' => FiltroTipoProducto::nombre($tipoProducto),
        ];

        $movimiento = MovimientoInventario::getData($filtro, true);
        $datos = [
            'movimiento' => $movimiento,
            'filtro'     => $filtro,
        ];

        return PdfGeneratorUtil::ShowPdf('pdf.movimiento_inventario', $datos, "MovimientoInventario");
    }
}
