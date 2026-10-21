<?php

namespace App\Models;

use App\Traits\PerteneceASede;
use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventarioTotal extends Model
{
    use PerteneceASede;
    use RegistraAutoria;
    use SoftDeletes;

    protected $table = 'inventario_total';

    protected $primaryKey = 'id_int';

    protected $fillable = [
        'pdt_id',
        'sed_id',
        'cantidad_total_int',
        'cantidad_convertida_total_int',
    ];

    protected $casts = [
        'cantidad_total_int'            => 'float',
        'cantidad_convertida_total_int' => 'float',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'pdt_id', 'id_pdt');
    }

    public function sede()
    {
        return $this->belongsTo(Sede::class, 'sed_id', 'id_sed');
    }

    public function estadoStock(): string
    {
        return static::clasificar($this->cantidad_total_int, $this->producto?->stock_minimo_pdt, $this->producto?->stock_maximo_pdt);
    }

    public static function clasificar(?float $total, $minimo, $maximo): string
    {
        $total = (float) $total;

        return match (true) {
            $total <= 0                                      => 'agotado',
            $minimo !== null && $total <= (float) $minimo     => 'bajo_minimo',
            $maximo !== null && (float) $maximo > 0 && $total > (float) $maximo => 'sobre_maximo',
            default                                          => 'normal',
        };
    }
}
