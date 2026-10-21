<?php

namespace App\Models;

use App\Traits\PerteneceASede;
use Illuminate\Database\Eloquent\Model;

class InventarioTotal extends Model
{
    use PerteneceASede;

    protected $table = 'inventario_totals';

    protected $fillable = [
        'producto_id',
        'sede_id',
        'cantidad_total',
        'cantidad_convertida_total',
    ];

    protected $casts = [
        'cantidad_total'            => 'float',
        'cantidad_convertida_total' => 'float',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function sede()
    {
        return $this->belongsTo(Sede::class, 'sede_id');
    }

    public function estadoStock(): string
    {
        return static::clasificar($this->cantidad_total, $this->producto?->stock_minimo, $this->producto?->stock_maximo);
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
