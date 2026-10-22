<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\Lote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\CompraProveedorMail;
use App\Utilities\PdfGeneratorUtil;

class CompraController extends Controller
{
    public function index(Request $request)
    {
        if ($request->fecha_desde == null && $request->fecha_hasta == null) {
            $fechas = null;
        } else {
            $fechas = [
                'fecha_desde' => $request->fecha_desde,
                'fecha_hasta' => $request->fecha_hasta
            ];
        }
        $compras = Compra::listarCompras(
            $request->buscar,
            $request->estado,
            $fechas
        );

        return view('admin.movimientos.compras.index', compact('compras'));
    }

    public function create()
    {
        $datos = (new Compra())->getDatosFormulario();
        $datos['modulos'] = $datos['modulos']->whereIn('nombre_mod', ['Comedor', 'Salud', 'Deporte']);

        return view('admin.movimientos.compras.create', $datos);
    }

    public function edit($id)
    {
        $compra = Compra::with(['detalleCompras' => function ($query) {
            $query->with(['producto', 'lote']);
        }, 'proveedor'])->findOrFail($id);

        $datos = $compra->getDatosFormulario();
        $datos['modulos'] = $datos['modulos']->whereIn('nombre_mod', ['Comedor', 'Salud', 'Deporte']);

        return view('admin.movimientos.compras.edit', array_merge($datos, [
            'compra' => $compra
        ]));
    }

    public function store(Request $request)
    {
        $id = Compra::crearCompra($request->validate([
            'proveedor_id'  => 'required|exists:proveedor,id_prv',
            'modulo_id'     => 'required|exists:modulo,id_mod',
            'fecha'         => 'required|date',
            'observaciones' => 'nullable|string'
        ]));

        return redirect()->route('admin.movimientos.compras.edit', $id)->with('success', 'Requisición creada exitosamente.');
    }

    public function show($id)
    {
        $compra = Compra::obtenerCompra($id);
        abort_unless($compra, 404);
        $sede_destino = Compra::obtenerSedeDestino($id);
        $detalles = Compra::obtenerDetallesCompra($id);

        return view('admin.movimientos.compras.show', compact('compra', 'sede_destino', 'detalles'));
    }

    public function destroy($id)
    {
        Compra::eliminarCompra($id);
        return redirect()
            ->route('admin.movimientos.compras.index')
            ->with('success', 'Requisición eliminada exitosamente.');
    }

    public function finalizarCompra(Request $request, Compra $compra)
    {
        $sinVencimiento = \App\Models\TipoProducto::idsProductosSinVencimiento(
            $compra->detalleCompras->pluck('pdt_id')->map(fn ($id) => (int) $id)->unique()->values()->all()
        );

        $lotesSinFecha = Lote::whereIn(
            'id_lot',
            $compra->detalleCompras->pluck('lot_id')
        )
            ->whereNotIn('pdt_id', $sinVencimiento)
            ->whereNull('fecha_vencimiento_lot')
            ->exists();

        if ($lotesSinFecha) {
            return back()->withErrors([
                'fecha_vencimiento_lot' => 'Debe registrar la fecha de vencimiento de todos los productos antes de finalizar la requisición.'
            ]);
        }

        try {
            Compra::finalizarCompraDistribuida($compra);
        } catch (\Throwable $e) {
            return back()->with('error', \App\Support\Seguridad\MensajeError::de($e));
        }

        return redirect()->route('admin.movimientos.compras.index')->with('success', 'Requisición finalizada: el stock entró a la sede de la requisición.');
    }

    public function cancelar(Compra $compra)
    {
        if ($compra->estado_compra_cmp === Compra::ESTADO_FINALIZADA) {
            return redirect()
                ->route('admin.movimientos.compras.index')
                ->with('error', 'No se puede cancelar una requisición finalizada.');
        }
        Compra::eliminarCompra($compra->id_cmp);

        return redirect()
            ->route('admin.movimientos.compras.index')
            ->with('success', 'Requisición eliminada exitosamente.');
    }

    public function enviarCorreo(Compra $compra)
    {
        $proveedorEmail = $compra->proveedor->email_prv;
        $compra->estado_compra_cmp = 'Enviado al proveedor';
        $compra->save();

        Mail::to($proveedorEmail)->send(new CompraProveedorMail($compra));

        return back()->with('success', 'Correo enviado exitosamente.');
    }

    public function exportPdf(Request $request)
    {
        $itemsPlano = Compra::obtenerTodosDetallesCompras(
            $request->buscar,
            $request->estado
        );

        if ($itemsPlano->isEmpty()) {
            return back()->with('error', 'No se encontraron datos para el reporte.');
        }

        $comprasAgrupadas = $itemsPlano->groupBy('cmp_id');
        $compras = $comprasAgrupadas->map(function ($items, $compraId) {

            $primerItem = $items->first();
            $totalCompra = $items->sum('subtotal_dcm');

            return (object) [
                'id_cmp'            => $compraId,
                'fecha_compra_cmp'  => $primerItem->fecha_compra_cmp,
                'proveedor_empresa' => $primerItem->proveedor_empresa,
                'created_at'        => $primerItem->created_at,
                'total_cmp'         => $totalCompra,
                'observacion_cmp'   => null,
                'detalles'          => $items->all()
            ];
        })->values();

        $datos = [
            'compras' => $compras,
            'buscar'  => $request->buscar,
            'estado'  => $request->estado
        ];

        return PdfGeneratorUtil::ShowPdf('pdf.compra', $datos, "Compras");
    }
}
