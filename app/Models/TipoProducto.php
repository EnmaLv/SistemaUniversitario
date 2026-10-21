<?php

namespace App\Models;

use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TipoProducto extends Model
{
    use RegistraAutoria;
    use SoftDeletes;

    protected $table = 'tipo_producto';

    protected $primaryKey = 'id_tpr';

    protected $fillable = [
        'nombre_tpr',
        'mod_id',
        'status_id',
    ];

    public function modulo()
    {
        return $this->belongsTo(Modulo::class, 'mod_id', 'id_mod');
    }

    public function categorias()
    {
        return $this->hasMany(Categoria::class, 'tpr_id', 'id_tpr');
    }
}
