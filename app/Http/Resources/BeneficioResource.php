<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BeneficioResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_bes,
            'nombre_beneficio' => $this->nombre_beneficio_bes,
            'descripcion' => $this->descripcion_bes,
            'slug' => $this->slug_bes,
            'disponibilidad' => [
                'cupones_disponibles' => $this->cupones_disponibles_bes,
                'cupones_ocupados' => $this->cupones_ocupados_bes,
                'cupos_restantes' => $this->cupones_disponibles_bes - $this->cupones_ocupados_bes,
                'cupos_totales' => $this->cupones_disponibles_bes + $this->cupones_ocupados_bes,
            ],
            'status' =>$this->estaActivo(),
            'creado_en' => $this->created_at,
            'actualizado_en' => $this->updated_at,
        ];
    }
}
