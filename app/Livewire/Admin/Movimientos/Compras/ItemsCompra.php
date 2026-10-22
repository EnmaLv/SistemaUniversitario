<?php

namespace App\Livewire\Admin\Movimientos\Compras;

use App\Models\Compra;
use App\Models\DetalleCompra;
use App\Models\Lote;
use App\Models\Producto;
use App\Models\TipoProducto;
use App\Models\Usuario;
use App\Support\Inventario\PrecioVigente;
use App\Support\Inventario\UnidadCompra;
use App\Support\Sedes\SedeCompradora;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ItemsCompra extends Component
{
    public Compra $compra;

    public ?int $productoId = null;

    public $cantidad = null;

    public $precioCompra = null;

    public $codigoLote;

    public $productos;

    public $totalCompra;

    public array $unidad = [];

    public array $precio = [];

    public function mount(Compra $compra)
    {
        $this->compra = $compra;
        $this->obtenerProductosPorModulo();
        $this->cargarDatos();
    }

    public function obtenerProductosPorModulo()
    {
        $tipos = TipoProducto::idsParaModulo($this->compra->mod_id === null ? null : (int) $this->compra->mod_id);

        $this->productos = Producto::where('status_id', Producto::STATUS_ACTIVO)
            ->whereHas('categoria', function ($q) use ($tipos) {
                $q->whereIn('tpr_id', $tipos);
            })
            ->orderBy('nombre_pdt')
            ->get();
    }

    public function cargarDatos()
    {
        $this->compra->load('detalleCompras.producto', 'detalleCompras.lote');
        $this->totalCompra = $this->compra->detalleCompras->sum('subtotal_dcm');

        $this->reset(['productoId', 'codigoLote', 'cantidad', 'precioCompra', 'unidad', 'precio']);
    }

    public function updatedProductoId($value)
    {
        $this->resetErrorBag();
        $producto = $value ? Producto::find($value) : null;

        if (! $producto) {
            $this->reset(['codigoLote', 'precioCompra', 'unidad', 'precio', 'cantidad']);

            return;
        }

        $this->unidad = UnidadCompra::de($producto)->aArreglo();
        $this->precio = PrecioVigente::de((int) $producto->id_pdt);
        $this->precioCompra = $this->precio['bs'];
        $this->cantidad = null;
        $this->codigoLote = $this->generateCodigoLote($producto);
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

    private function motivoParaNoEditar(): ?string
    {
        $usuario = Auth::user();

        if (! $usuario instanceof Usuario || ! $usuario->puede('compras', 'editar')) {
            return 'No tienes permiso para cambiar los productos de las compras.';
        }

        if ($this->compra->estado_compra_cmp !== Compra::ESTADO_PENDIENTE) {
            return 'Esta requisición ya fue enviada al proveedor; ya no se pueden cambiar sus productos.';
        }

        return SedeCompradora::motivoParaNoComprar();
    }

    private function numero($valor): ?float
    {
        if ($valor === null || $valor === '') {
            return null;
        }

        $texto = str_replace(',', '.', trim((string) $valor));

        return is_numeric($texto) ? (float) $texto : null;
    }

    public function agregarItems()
    {
        if ($motivo = $this->motivoParaNoEditar()) {
            $this->dispatch('mostrar-alerta', icono: 'error', mensaje: $motivo);

            return;
        }

        $producto = $this->productoId ? Producto::find($this->productoId) : null;

        if (! $producto) {
            $this->addError('productoId', 'Elige el producto que vas a comprar.');

            return;
        }

        $unidad = UnidadCompra::de($producto);
        $this->cantidad = $this->numero($this->cantidad) ?? $this->cantidad;
        $this->precioCompra = $this->numero($this->precioCompra) ?? $this->precioCompra;

        if (empty($this->codigoLote)) {
            $this->codigoLote = $this->generateCodigoLote($producto);
        }

        $reglaCantidad = $unidad->fraccionable()
            ? ['required', 'numeric', 'min:0.01', 'regex:/^\d+(\.\d{1,2})?$/']
            : ['required', 'integer', 'min:1'];

        $this->validate([
            'productoId'   => 'required|exists:producto,id_pdt',
            'codigoLote'   => 'nullable|string|max:50',
            'cantidad'     => $reglaCantidad,
            'precioCompra' => 'required|numeric|min:0',
        ], [
            'productoId.required'   => 'Elige el producto que vas a comprar.',
            'cantidad.required'     => 'Escribe cuánto vas a comprar.',
            'cantidad.numeric'      => 'La cantidad debe ser un número, por ejemplo 7.5.',
            'cantidad.integer'      => 'Este producto se compra por ' . $unidad->unidadPlural . ' completos: escribe un número entero, sin decimales.',
            'cantidad.min'          => $unidad->fraccionable() ? 'La cantidad debe ser mayor que cero.' : 'Compra al menos 1 ' . $unidad->unidad . '.',
            'cantidad.regex'        => 'Usa como máximo dos decimales, por ejemplo 7.25.',
            'precioCompra.required' => 'Escribe el precio ' . $unidad->precioPor() . ' en bolívares.',
            'precioCompra.numeric'  => 'El precio debe ser un número.',
            'precioCompra.min'      => 'El precio no puede ser negativo.',
        ]);

        if ($producto->unm_id === null) {
            $this->addError('productoId', 'Este producto no tiene unidad de medida. Edítalo, elige su unidad y vuelve a intentarlo.');

            return;
        }

        $cantidad = round((float) $this->cantidad, 2);
        $precio = round((float) $this->precioCompra, 2);
        $cantidadConvertida = $unidad->convertir($cantidad);

        DB::beginTransaction();
        try {
            $lote = Lote::create([
                'pdt_id'                => $producto->id_pdt,
                'prv_id'                => $this->compra->prv_id,
                'codigo_lote_lot'       => $this->codigoLote,
                'fecha_entrada_lot'     => now()->toDateString(),
                'fecha_vencimiento_lot' => null,
                'cantidad_inicial_lot'  => $cantidadConvertida,
                'cantidad_actual_lot'   => $cantidadConvertida,
                'precio_compra_lot'     => $precio,
                'status_id'             => Lote::STATUS_ACTIVO,
            ]);

            $this->compra->detalleCompras()->create([
                'pdt_id'                  => $producto->id_pdt,
                'lot_id'                  => $lote->id_lot,
                'cantidad_dcm'            => $cantidad,
                'cantidad_convertida_dcm' => $cantidadConvertida,
                'precio_unitario_dcm'     => $precio,
                'subtotal_dcm'            => round($cantidad * $precio, 2),
                'unm_id'                  => $producto->unm_id,
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
                mensaje: 'No se pudo agregar el producto: ' . \App\Support\Seguridad\MensajeError::de($e)
            );
        }
    }

    public function render()
    {
        $unidadesDetalle = [];

        foreach ($this->compra->detalleCompras as $detalle) {
            $unidadesDetalle[$detalle->id_dcm] = UnidadCompra::de((int) $detalle->pdt_id);
        }

        $cantidad = $this->numero($this->cantidad);
        $precio = $this->numero($this->precioCompra);
        $resumen = null;

        if ($this->unidad !== [] && $this->productoId && $cantidad !== null && $cantidad > 0) {
            $unidad = UnidadCompra::de((int) $this->productoId);
            $resumen = [
                'texto'    => $unidad->resumen($cantidad),
                'subtotal' => $precio !== null ? round($cantidad * $precio, 2) : null,
            ];
        }

        return view('livewire.admin.movimientos.compras.items-compra', [
            'unidadesDetalle' => $unidadesDetalle,
            'resumen'         => $resumen,
            'editable'        => $this->compra->estado_compra_cmp === Compra::ESTADO_PENDIENTE,
        ]);
    }

    public function aggItems()
    {
        $this->dispatch(
            'mostrar-alerta',
            icono: 'success',
            mensaje: 'Producto agregado a la requisición.'
        );
    }

    public function eliminarItem($detalleId)
    {
        if ($motivo = $this->motivoParaNoEditar()) {
            $this->dispatch('mostrar-alerta', icono: 'error', mensaje: $motivo);

            return;
        }

        DB::beginTransaction();
        try {
            $detalle = DetalleCompra::where('cmp_id', $this->compra->id_cmp)->find($detalleId);

            if (! $detalle) {
                throw new \RuntimeException('Ese producto ya no está en la requisición.');
            }

            $lote = Lote::find($detalle->lot_id);
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
                mensaje: 'Producto quitado de la requisición.'
            );
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch(
                'mostrar-alerta',
                icono: 'error',
                mensaje: 'No se pudo quitar el producto: ' . \App\Support\Seguridad\MensajeError::de($e)
            );
        }
    }
}
