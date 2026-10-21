<?php

namespace App\Models;

use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExchangeRates extends Model
{
    use HasFactory;
    use RegistraAutoria;
    use SoftDeletes;

    public const VARIACIONES = ['subio', 'bajo', 'igual'];

    protected $table = 'tasa_cambio';

    protected $primaryKey = 'id_tca';

    protected $fillable = [
        'nombre_tca',
        'fuente_tca',
        'promedio_tca',
        'variacion_tca',
        'fecha_vigencia_tca',
    ];
}
