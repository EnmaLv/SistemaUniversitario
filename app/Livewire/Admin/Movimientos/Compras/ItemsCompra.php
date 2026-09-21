<?php

namespace App\Livewire\Admin\Movimientos\Compras;

use App\Models\Compra;
use App\Models\DetalleCompra;
use App\Models\Lote;
use App\Models\Producto;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class ItemsCompra extends Component
{
    public Compra $compra;

    public ?int $productoId = null;

    public int $cantidad = 1;

    public ?float $precioUnitario = null;

    public ?float $precioCompra = null;

    public $codigoLote;

    public $fechaVencimiento;

    public $productos;

    public $totalCompra;

    public function mount(Compra $compra)
    {
        $this->compra = $compra;
        $this->obtenerProductosPorModulo();
        $this->cargarDatos();
    }

    public function obtenerProductosPorModulo()
    {
        $moduloId = $this->compra->modulo_id;

        $this->productos = Producto::where('estado', 1)
            ->whereHas('categoria.tipoProducto', function ($q) use ($moduloId) {
                $q->where('modulo_id', $moduloId);
            })
            ->orderBy('nombre')
            ->get();
    }

    public function cargarDatos()
    {
        $this->compra->load('detalleCompras.producto', 'detalleCompras.lote');
        $this->totalCompra = $this->compra->detalleCompras->sum('subtotal');

        $this->reset(['productoId', 'codigoLote', 'cantidad', 'precioUnitario', 'precioCompra', 'fechaVencimiento']);
        $this->cantidad = 1;
    }

    protected $rules = [
        'productoId' => 'required|exists:productos,id',
        'codigoLote' => 'nullable|string|max:50',
        'cantidad' => 'required|integer|min:1',
        'precioCompra' => 'required|numeric|min:0',
        'fechaVencimiento' => 'nullable|date|after:today',
    ];

    public function updatedProductoId($value)
    {
        $producto = Producto::with('categoria')->find($value);
        if ($producto) {
            $this->precioCompra = $producto->precio_compra;
            $this->codigoLote = $this->generateCodigoLote($producto);
        } else {
            $this->precioCompra = 0;
            $this->codigoLote = null;
        }
    }

    protected function generateCodigoLote(Producto $producto): string
    {
        $prod = $producto->nombre ?? 'PROD';
        $prodPart = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $prod), 0, 3));
        $year = now()->format('Y');
        $julianDay = now()->format('z') + 1;
        $julian = str_pad($julianDay, 3, '0', STR_PAD_LEFT);
        $codigo = "{$julian}-{$year}-{$prodPart}";
        $contador = 1;
        $codigoFinal = $codigo;

        while (Lote::where('codigo_lote', $codigoFinal)->exists()) {
            $codigoFinal = "{$codigo}-{$contador}";
            $contador++;
        }

        return $codigoFinal;
    }

    public function confirmarEnvio()
    {
        $this->dispatch('confirmar-envio', compraId: $this->compra->id);
    }

    public function agregarItems()
    {
        $producto = Producto::find($this->productoId);
        if (!$producto) {
            $this->dispatch('mostrar-alerta', icono: 'error', mensaje: 'Seleccione un producto válido.');
            return;
        }
        if (empty($this->codigoLote)) {
            $this->codigoLote = $this->generateCodigoLote($producto);
        }

        $this->validate();

        DB::beginTransaction();
        try {
            $unidadId = $producto->unidad_id;
            $unidad = DB::table('unidades')->where('id', $producto->unidad_id)->first();
            $factor = $producto->presentacion_id
                ? ($producto->unidades_por_presentacion ?? 1)
                : ($producto->peso_contenido ?? 1);

            $cantidadConvertida = $this->cantidad * $factor;

            $lote = Lote::create([
                'producto_id' => $this->productoId,
                'proveedor_id' => $this->compra->proveedor_id,
                'codigo_lote' => $this->codigoLote,
                'fecha_entrada' => now()->toDateString(),
                'fecha_vencimiento' => null,
                'cantidad_inicial' => $this->cantidad,
                'cantidad_actual' => $cantidadConvertida,
                'precio_compra' => $this->precioCompra,
                'estado' => true,
            ]);

            $this->compra->detalleCompras()->create([
                'producto_id' => $producto->id,
                'lote_id' => $lote->id,
                'cantidad' => $this->cantidad,
                'cantidad_convertida' => $cantidadConvertida,
                'precio_unitario' => $this->precioCompra,
                'subtotal' => $this->cantidad * $this->precioCompra,
                'unidad_id' => $unidadId,
            ]);
            $this->compra->load('detalleCompras');
            $this->compra->total = $this->compra->detalleCompras->sum('subtotal');
            $this->compra->save();

            DB::commit();

            $this->cargarDatos();
            $this->aggItems();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch(
                'mostrar-alerta',
                icono: 'error',
                mensaje: 'Error: ' . $e->getMessage()
            );
        }
    }

    public function render()
    {
        return view('livewire.admin.movimientos.compras.items-compra');
    }

    public function aggItems()
    {
        $this->dispatch(
            'mostrar-alerta',
            icono: 'success',
            mensaje: 'Producto agregado exitosamente'
        );
    }

    public function eliminarItem($detalleId)
    {
        DB::beginTransaction();
        try {
            $detalle = DetalleCompra::find($detalleId);

            $lote_id = $detalle->lote_id;
            $lote = Lote::find($lote_id);
            if ($lote) {
                $lote->delete();
            }
            $detalle->delete();

            $this->compra->total = $this->compra->detalleCompras->sum('subtotal');
            $this->compra->save();

            DB::commit();
            $this->cargarDatos();
            $this->dispatch(
                'mostrar-alerta',
                icono: 'success',
                mensaje: 'Producto eliminado exitosamente'
            );
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch(
                'mostrar-alerta',
                icono: 'error',
                mensaje: 'Error: ' . $e->getMessage()
            );
        }
    }
}