<?php

namespace App\Models;

use App\Traits\ConvierteAMayusculas;
use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Localidad extends Model
{
    use ConvierteAMayusculas;
    use HasFactory;
    use RegistraAutoria;
    use SoftDeletes;

    protected $table = 'parroquia';

    protected $primaryKey = 'id_par';

    protected $fillable = [
        'mun_id',
        'nombre_par',
        'status_id',
    ];

    protected $mayusculas = [
        'nombre_par'
    ];

    public function municipio()
    {
        return $this->belongsTo(Municipio::class, 'mun_id', 'id_mun');
    }

    public function estado()
    {
        return $this->hasOneThrough(Estado::class, Municipio::class, 'id_mun', 'id_esd', 'mun_id', 'esd_id');
    }
}
