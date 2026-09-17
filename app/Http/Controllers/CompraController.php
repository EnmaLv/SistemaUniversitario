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
        $datos['modulos'] = $datos['modulos']->whereIn('nombre', ['Comedor', 'Salud', 'Deporte']);

        return view('admin.movimientos.compras.create', $datos);
    }

    public function edit($id)
    {
        $compra = Compra::with(['detalleCompras' => function ($query) {
            $query->with(['producto', 'lote']);
        }, 'proveedor'])->findOrFail($id);

        $datos = $compra->getDatosFormulario();
        $datos['modulos'] = $datos['modulos']->whereIn('nombre', ['Comedor', 'Salud', 'Deporte']);

        return view('admin.movimientos.compras.edit', array_merge($datos, [
            'compra' => $compra
        ]));
    }

    public function store(Request $request)
    {
        $id = Compra::crearCompra($request->validate([
            'proveedor_id'  => 'required|exists:proveedors,id',
            'modulo_id'     => 'required|exists:modulos,id',
            'fecha'         => 'required|date',
            'observaciones' => 'nullable|string'
        ]));

        return redirect()->route('admin.movimientos.compras.edit', $id)->with('success', 'Requisición creada exitosamente.');
    }

    public function show($id)
    {
        $compra = Compra::obtenerCompra($id);
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
        $lotesSinFecha = Lote::whereIn(
            'id',
            $compra->detalleCompras->pluck('lote_id')
        )
            ->whereNull('fecha_vencimiento')
            ->exists();

        if ($lotesSinFecha) {
            return back()->withErrors([
                'fecha_vencimiento' => 'Debe registrar la fecha de vencimiento de todos los productos antes de finalizar la requisición.'
            ]);
        }

        Compra::finalizarCompraDistribuida($compra);

        return redirect()->route('admin.movimientos.compras.index')->with('success', 'Requisición finalizada y distribuida equitativamente.');
    }

    public function cancelar(Compra $compra)
    {
        if ($compra->estado === 'Finalizada') {
            return redirect()
                ->route('admin.movimientos.compras.index')
                ->with('error', 'No se puede cancelar una requisición finalizada.');
        }
        Compra::eliminarCompra($compra->id);

        return redirect()
            ->route('admin.movimientos.compras.index')
            ->with('success', 'Requisición eliminada exitosamente.');
    }

    public function enviarCorreo(Compra $compra)
    {
        $proveedorEmail = $compra->proveedor->email;
        $compra->estado = 'Enviado al proveedor';
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

        $comprasAgrupadas = $itemsPlano->groupBy('compra_id');
        $compras = $comprasAgrupadas->map(function ($items, $compraId) {

            $primerItem = $items->first();
            $totalCompra = $items->sum('subtotal');

            return (object) [
                'id'                => $compraId,
                'fecha'             => $primerItem->fecha,
                'proveedor_empresa' => $primerItem->proveedor_empresa,
                'created_at'        => $primerItem->created_at,
                'total'             => $totalCompra,
                'observaciones'     => null,
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
