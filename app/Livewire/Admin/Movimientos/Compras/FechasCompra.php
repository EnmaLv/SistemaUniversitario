<?php

namespace App\Livewire\Admin\Movimientos\Compras;

use Livewire\Component;
use App\Models\Compra;
use App\Models\Lote;

class FechasCompra extends Component
{
    public $compra;
    public $fechas = [];

    protected $rules = [
        'fechas' => 'required|date|after:today',
    ];

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
        foreach ($this->fechas as $detalleId => $datos) {

            if (empty($datos['fecha_vencimiento'])) {
                $this->addError(
                    "fechas.$detalleId.fecha_vencimiento",
                    'La fecha es obligatoria.'
                );
                return;
            }

            if ($datos['fecha_vencimiento'] <= now()->toDateString()) {
                $this->addError(
                    "fechas.$detalleId.fecha_vencimiento",
                    'Debe ser mayor a hoy.'
                );
                return;
            }

            Lote::where('id', $datos['lote_id'])
                ->update([
                    'fecha_vencimiento' => $datos['fecha_vencimiento'],
                ]);
        }

        $this->dispatch(
            'swal',
            icon: 'success',
            title: '¡Éxito!',
            text: 'Fecha guardada Exitosamente.'
        );
    }


    public function render()
    {
        return view('livewire.admin.movimientos.compras.fechas-compra');
    }
}
