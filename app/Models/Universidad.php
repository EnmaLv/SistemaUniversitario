<?php

namespace App\Models;

use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Universidad extends Model
{
    use RegistraAutoria;
    use SoftDeletes;

    protected $table = 'universidad';

    protected $primaryKey = 'id_uni';

    protected $fillable = [
        'codigo_ministerio_uni',
        'nombre_uni',
        'siglas_uni',
        'rif_uni',
        'telefono_uni',
        'correo_uni',
        'sitio_web_uni',
        'loc_id',
        'status_id',
    ];

    public function sedes()
    {
        return $this->hasMany(Sede::class, 'uni_id', 'id_uni');
    }
}
