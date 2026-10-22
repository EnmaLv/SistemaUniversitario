<?php

namespace App\Livewire\Admin\Movimientos\Compras;

use Livewire\Component;
use App\Models\Compra;
use App\Models\Lote;
use App\Models\TipoProducto;
use Illuminate\Support\Facades\DB;

class FechasCompra extends Component
{
    public $compra;
    public $fechas = [];

    public array $sinVencimiento = [];

    public function mount(Compra $compra)
    {
        $this->compra = $compra;
        $this->sinVencimiento = TipoProducto::idsProductosSinVencimiento(
            $compra->detalleCompras->pluck('pdt_id')->map(fn ($id) => (int) $id)->unique()->values()->all()
        );

        foreach ($compra->detalleCompras as $detalle) {
            if (in_array((int) $detalle->pdt_id, $this->sinVencimiento, true)) {
                continue;
            }

            $this->fechas[$detalle->id_dcm] = [
                'fecha_vencimiento_lot' => optional($detalle->lote)->fecha_vencimiento_lot,
                'lote_id' => $detalle->lot_id,
            ];
        }
    }

    public function guardar()
    {
        $this->resetErrorBag();

        foreach ($this->fechas as $detalleId => $datos) {
            $this->validate([
                "fechas.$detalleId.fecha_vencimiento_lot" => 'required|date|after:today',
            ], [
                "fechas.$detalleId.fecha_vencimiento_lot.required" => 'La fecha es obligatoria.',
                "fechas.$detalleId.fecha_vencimiento_lot.after" => 'Debe ser mayor a hoy.',
            ]);
        }

        DB::transaction(function () {
            foreach ($this->fechas as $datos) {
                Lote::whereKey($datos['lote_id'])->update([
                    'fecha_vencimiento_lot' => $datos['fecha_vencimiento_lot'],
                ]);
            }
        });

        $this->dispatch(
            'swal',
            icon: 'success',
            title: '¡Éxito!',
            text: 'Fechas de vencimiento guardadas exitosamente.'
        );
    }


    public function render()
    {
        return view('livewire.admin.movimientos.compras.fechas-compra');
    }
}
