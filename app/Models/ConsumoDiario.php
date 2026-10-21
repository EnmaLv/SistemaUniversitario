<?php

namespace App\Models;

use App\Traits\PerteneceASede;
use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConsumoDiario extends Model
{
    use PerteneceASede;
    use RegistraAutoria;
    use SoftDeletes;

    protected $table = 'consumo_diario';

    protected $primaryKey = 'id_cnd';

    protected $fillable = [
        'rct_id',
        'sed_id',
        'fecha_consumo_cnd',
        'porciones_servidas_cnd',
    ];

    protected $casts = [
        'fecha_consumo_cnd' => 'date',
    ];

    public function receta()
    {
        return $this->belongsTo(Receta::class, 'rct_id', 'id_rct');
    }

    public function detalles()
    {
        return $this->hasMany(ConsumoDiarioDetalle::class, 'cnd_id', 'id_cnd');
    }
}
