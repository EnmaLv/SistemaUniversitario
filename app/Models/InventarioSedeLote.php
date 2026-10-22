<?php

namespace App\Models;

use App\Traits\PerteneceASede;
use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventarioSedeLote extends Model
{
    use PerteneceASede;
    use RegistraAutoria;
    use SoftDeletes;

    protected $table = 'inventario_sede_lote';

    protected $primaryKey = 'id_isl';

    protected $fillable = [
        'sed_id',
        'lot_id',
        'cantidad_isl',
        'cantidad_convertida_isl',
    ];

    public function sede()
    {
        return $this->belongsTo(Sede::class, 'sed_id', 'id_sed');
    }

    public function lote()
    {
        return $this->belongsTo(Lote::class, 'lot_id', 'id_lot');
    }
}
