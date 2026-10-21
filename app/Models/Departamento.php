<?php

namespace App\Models;

use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Departamento extends Model
{
    use RegistraAutoria;
    use SoftDeletes;

    protected $table = 'departamento';

    protected $primaryKey = 'id_dep';

    protected $fillable = [
        'nombre_dep',
        'descripcion_dep',
        'status_id',
    ];
}
