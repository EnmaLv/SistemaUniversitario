<?php

namespace App\Models;

use App\Traits\PerteneceASede;
use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DetalleRegistroDiario extends Model
{
    use HasFactory;
    use PerteneceASede;
    use RegistraAutoria;
    use SoftDeletes;

    protected $table = 'detalle_registro_comedor';

    protected $primaryKey = 'id_drc';

    protected $fillable = [
        'rct_id',
        'sed_id',
        'tipo_comida_drc',
        'cantidad_servida_drc',
        'fecha_drc',
    ];

    public function receta()
    {
        return $this->belongsTo(Receta::class, 'rct_id', 'id_rct');
    }
}
