<?php

namespace App\Http\Resources\becas;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JornadaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_jnb,
            'nombre' => $this->nombre_jornada_jnb,
            'descripcion' => $this->descripcion_jornada_jnb ?? 'Sin descripción disponible',
            
            // Agrupamos las fechas de solicitud
            'periodo_solicitud' => [
                'inicio' => $this->fecha_inicio_solicitud_jnb,
                'fin' => $this->fecha_fin_solicitud_jnb,
            ],
            
            // Manejo de los contadores de cupos
            'cupos' => [
                'maximos' => (int) $this->cupos_maximos_jnb,
                'asignados' => (int) $this->cupos_asignados_jnb,
                'disponibles' => (int) ($this->cupos_maximos_jnb - $this->cupos_asignados_jnb),
            ],
            
            // Convertimos el tinyint(1) a un booleano real de PHP
            'esta_activa' => $this->estaActivo(),
            
            // Relaciones opcionales (Solo se incluirán si las cargas en el controlador con ->load())
            'beneficio' => $this->whenLoaded('beneficio'), 
            // 'lapso' => $this->whenLoaded('lapso'),

            'creado_el' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
        ];
    }
}
