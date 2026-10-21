<?php

namespace App\Models\Becas;

use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SolicitudDocumento extends Model
{
    use RegistraAutoria;
    use SoftDeletes;

    protected $table = 'documento_solicitud_beneficio';

    protected $primaryKey = 'id_dsb';

    protected $fillable = [
        'sob_id',
        'nombre_documento_dsb',
        'ruta_archivo_dsb',
        'tipo_archivo_dsb',
        'status_id',
    ];

    protected $casts = [
        'status_id' => 'integer',
    ];

    public function solicitud()
    {
        return $this->belongsTo(SolicitudBeca::class, 'sob_id', 'id_sob');
    }
}
