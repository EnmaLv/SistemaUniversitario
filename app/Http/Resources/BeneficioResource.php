<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BeneficioResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre_beneficio' => $this->nombre_beneficio,
            'descripcion' => $this->descripcion,
            'slug' => $this->slug,
            'disponibilidad' => [
                'cupones_disponibles' => $this->cupones_disponibles,
                'cupones_ocupados' => $this->cupones_ocupados,
                'cupos_restantes' => $this->cupones_disponibles - $this->cupones_ocupados,
                'cupos_totales' => $this->cupones_disponibles + $this->cupones_ocupados,
            ],
            'status' =>(bool) $this->status,
            'creado_en' => $this->created_at,
            'actualizado_en' => $this->updated_at,
        ];
    }
}
