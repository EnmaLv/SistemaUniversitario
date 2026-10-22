<?php

namespace App\Models\Becas;

use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BecaAsignada extends Model
{
    use RegistraAutoria;
    use SoftDeletes;

    protected $table = 'beneficio_asignado';

    protected $primaryKey = 'id_bfa';

    protected $fillable = [
        'sob_id',
        'fecha_inicio_bfa',
        'fecha_fin_bfa',
        'estado_bfa',
        'motivo_revocacion_bfa',
        'status_id',
    ];

    protected $casts = [
        'fecha_inicio_bfa' => 'date',
        'fecha_fin_bfa'    => 'date',
        'status_id'        => 'integer',
    ];

    public function solicitud()
    {
        return $this->belongsTo(SolicitudBeca::class, 'sob_id', 'id_sob');
    }
}
