<?php

namespace App\Models\salud;

use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class UnidadDosis extends Model
{
    use RegistraAutoria;
    use SoftDeletes;

    protected $table = 'unidad_dosis';

    protected $primaryKey = 'id_udo';

    protected $fillable = [
        'nombre_udo',
        'abreviatura_udo',
        'status_id',
    ];

    public static function catalogo(): Collection
    {
        return static::activos()->orderBy('nombre_udo')->get();
    }
}
