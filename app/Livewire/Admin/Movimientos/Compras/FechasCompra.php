<?php

namespace App\Livewire\Admin\Movimientos\Compras;

use Livewire\Component;
use App\Models\Compra;
use App\Models\DetalleCompra;
use App\Models\Lote;
use Illuminate\Support\Facades\DB;

class FechasCompra extends Component
{
    public $compra;
    public $fechas = [];

    public function mount(Compra $compra)
    {
        $this->compra = $compra;

        foreach ($compra->detalleCompras as $detalle) {
            $this->fechas[$detalle->id] = [
                'fecha_vencimiento' => optional($detalle->lote)->fecha_vencimiento,
                'lote_id' => $detalle->lote_id,
            ];
        }
    }

    public function guardar()
    {
        $this->resetErrorBag();

        foreach ($this->fechas as $detalleId => $datos) {
            $this->validate([
                "fechas.$detalleId.fecha_vencimiento" => 'required|date|after:today',
            ], [
                "fechas.$detalleId.fecha_vencimiento.required" => 'La fecha es obligatoria.',
                "fechas.$detalleId.fecha_vencimiento.after" => 'Debe ser mayor a hoy.',
            ]);
        }

        DB::transaction(function () {
            foreach ($this->fechas as $datos) {
                Lote::whereKey($datos['lote_id'])->update([
                    'fecha_vencimiento' => $datos['fecha_vencimiento'],
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
