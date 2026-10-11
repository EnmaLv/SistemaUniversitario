<?php

namespace App\Models\Deporte;

use Illuminate\Database\Eloquent\Model;

class DeporteActividad extends Model
{
    protected $table = 'deporte_actividad';
    protected $fillable = ['nombre', 'descripcion', 'tipo', 'activo'];
    protected $casts = ['activo' => 'integer'];

    public function horarios()
    {
        return $this->hasMany(DeporteHorario::class, 'id_actividad');
    }
}
