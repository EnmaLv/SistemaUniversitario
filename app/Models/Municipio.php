<?php

namespace App\Models;

use App\Traits\ConvierteAMayusculas;
use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Municipio extends Model
{
    use ConvierteAMayusculas;
    use RegistraAutoria;
    use SoftDeletes;

    protected $table = 'municipio';

    protected $primaryKey = 'id_mun';

    protected $fillable = [
        'nombre_mun',
        'esd_id',
        'status_id',
    ];

    protected $mayusculas = [
        'nombre_mun'
    ];

    public function estado()
    {
        return $this->belongsTo(Estado::class, 'esd_id', 'id_esd');
    }

    public function localidades()
    {
        return $this->hasMany(Localidad::class, 'mun_id', 'id_mun');
    }
}
