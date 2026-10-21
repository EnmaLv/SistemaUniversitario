<?php

namespace App\Models;

use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vista extends Model
{
    use RegistraAutoria;
    use SoftDeletes;

    protected $table = 'vista';

    protected $primaryKey = 'id_vis';

    protected $fillable = [
        'key_vis',
        'nombre_vis',
        'descripcion_vis',
        'ruta_vis',
        'icono_vis',
        'orden_vis',
        'mod_id',
        'status_id',
    ];

    protected $casts = [
        'orden_vis' => 'integer',
    ];

    public function modulo()
    {
        return $this->belongsTo(Modulo::class, 'mod_id', 'id_mod');
    }

    public function permisos()
    {
        return $this->hasMany(Permiso::class, 'vis_id', 'id_vis');
    }
}
