<?php

namespace App\Models;

use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConsumoDiarioDetalle extends Model
{
    use RegistraAutoria;
    use SoftDeletes;

    protected $table = 'consumo_diario_detalle';

    protected $primaryKey = 'id_cdd';

    protected $fillable = [
        'cnd_id',
        'pdt_id',
        'cantidad_total_cdd',
        'unm_id',
    ];

    public function consumoDiario()
    {
        return $this->belongsTo(ConsumoDiario::class, 'cnd_id', 'id_cnd');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'pdt_id', 'id_pdt');
    }

    public function unidad()
    {
        return $this->belongsTo(Unidad::class, 'unm_id', 'id_unm');
    }
}
