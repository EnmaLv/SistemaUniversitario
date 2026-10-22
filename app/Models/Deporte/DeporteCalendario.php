<?php

namespace App\Models\Deporte;

use Illuminate\Database\Eloquent\Model;

class DeporteCalendario extends Model
{
    protected $table = 'deporte_calendario';
    protected $fillable = ['nombre', 'descripcion', 'fecha_inicio', 'fecha_fin', 'id_sede', 'activo', 'creado_por'];
    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'activo' => 'integer',
    ];

    public function horarios()
    {
        return $this->hasMany(DeporteHorario::class, 'id_calendario');
    }
}
