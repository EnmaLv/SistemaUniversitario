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
        $moduloId = $this->compra->mod_id;

        $this->productos = Producto::where('status_id', Producto::STATUS_ACTIVO)
            ->whereHas('categoria.tipoProducto', function ($q) use ($moduloId) {
                $q->where('mod_id', $moduloId);
            })
            ->orderBy('nombre_pdt')
            ->get();
    }

    public function cargarDatos()
    {
        $this->compra->load('detalleCompras.producto', 'detalleCompras.lote');
        $this->totalCompra = $this->compra->detalleCompras->sum('subtotal_dcm');

        $this->reset(['productoId', 'codigoLote', 'cantidad', 'precioUnitario', 'precioCompra', 'fechaVencimiento']);
        $this->cantidad = 1;
    }

    protected $rules = [
        'productoId' => 'required|exists:producto,id_pdt',
        'codigoLote' => 'nullable|string|max:50',
        'cantidad' => 'required|integer|min:1',
        'precioCompra' => 'required|numeric|min:0',
        'fechaVencimiento' => 'nullable|date|after:today',
    ];

    public function updatedProductoId($value)
    {
        $producto = Producto::with('categoria')->find($value);
        if ($producto) {
            $this->precioCompra = $producto->precio_compra_pdt;
            $this->codigoLote = $this->generateCodigoLote($producto);
        } else {
            $this->precioCompra = 0;
            $this->codigoLote = null;
        }
    }

    protected function generateCodigoLote(Producto $producto): string
    {
        $prod = $producto->nombre_pdt ?? 'PROD';
        $prodPart = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $prod), 0, 3));
        $year = now()->format('Y');
        $julianDay = now()->format('z') + 1;
        $julian = str_pad($julianDay, 3, '0', STR_PAD_LEFT);
        $codigo = "{$julian}-{$year}-{$prodPart}";
        $contador = 1;
        $codigoFinal = $codigo;

        while (Lote::where('codigo_lote_lot', $codigoFinal)->exists()) {
            $codigoFinal = "{$codigo}-{$contador}";
            $contador++;
        }

        return $codigoFinal;
    }

    public function confirmarEnvio()
    {
        $this->dispatch('confirmar-envio', compraId: $this->compra->id_cmp);
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
            $unidadId = $producto->unm_id;

            $factor = $producto->enp_id
                ? ($producto->unidades_presentacion_pdt ?? 1)
                : ($producto->peso_contenido_pdt ?? 1);

            $cantidadConvertida = $this->cantidad * $factor;

            $lote = Lote::create([
                'pdt_id'                => $this->productoId,
                'prv_id'                => $this->compra->prv_id,
                'codigo_lote_lot'       => $this->codigoLote,
                'fecha_entrada_lot'     => now()->toDateString(),
                'fecha_vencimiento_lot' => null,
                'cantidad_inicial_lot'  => $cantidadConvertida,
                'cantidad_actual_lot'   => $cantidadConvertida,
                'precio_compra_lot'     => $this->precioCompra,
                'status_id'             => Lote::STATUS_ACTIVO,
            ]);

            $this->compra->detalleCompras()->create([
                'pdt_id'                  => $producto->id_pdt,
                'lot_id'                  => $lote->id_lot,
                'cantidad_dcm'            => $this->cantidad,
                'cantidad_convertida_dcm' => $cantidadConvertida,
                'precio_unitario_dcm'     => $this->precioCompra,
                'subtotal_dcm'            => $this->cantidad * $this->precioCompra,
                'unm_id'                  => $unidadId,
            ]);
            $this->compra->load('detalleCompras');
            $this->compra->total_cmp = $this->compra->detalleCompras->sum('subtotal_dcm');
            $this->compra->save();

            DB::commit();

            $this->cargarDatos();
            $this->aggItems();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch(
                'mostrar-alerta',
                icono: 'error',
                mensaje: 'Error: ' . \App\Support\Seguridad\MensajeError::de($e)
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

            $lote_id = $detalle->lot_id;
            $lote = Lote::find($lote_id);
            if ($lote) {
                $lote->delete();
            }
            $detalle->delete();

            $this->compra->total_cmp = $this->compra->detalleCompras()->sum('subtotal_dcm');
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
                mensaje: 'Error: ' . \App\Support\Seguridad\MensajeError::de($e)
            );
        }
    }
}