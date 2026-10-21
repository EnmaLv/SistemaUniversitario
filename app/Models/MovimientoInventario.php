<?php

namespace App\Models;

use App\Traits\PerteneceASede;
use Illuminate\Database\Eloquent\Model;

class MovimientoInventario extends Model
{
    use PerteneceASede;

    protected $table = 'movimiento_inventarios';

    protected $fillable = [
        'producto_id',
        'lote_id',
        'sede_id',
        'modulo_origen_id',
        'tipo_movimiento',
        'unidad_id',
        'cantidad',
        'cantidad_convertida',
        'cantidad_anterior',
        'cantidad_final',
        'referencia_type',
        'referencia_id',
        'usuario_id',
        'fecha',
        'observaciones',
    ];

    public function unidad()
    {
        return $this->belongsTo(Unidad::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function lote()
    {
        return $this->belongsTo(Lote::class);
    }

    public function sede()
    {
        return $this->belongsTo(Sede::class, 'sede_id');
    }

    public static function getData(array $filtro, bool $isPdf)
    {
        $query = self::query();

        if (!empty($filtro['buscar'])) {
            $query->where(function ($q) use ($filtro) {
                $q->where('tipo_movimiento', 'like', "%{$filtro['buscar']}%")
                    ->orWhereHas('lote.producto', function ($p) use ($filtro) {
                        $p->where('nombre', 'like', "%{$filtro['buscar']}%");
                    })
                    ->orWhereHas('lote', function ($p) use ($filtro) {
                        $p->where('codigo_lote', 'like', "%{$filtro['buscar']}%");
                    });
            });
        }

        if ($filtro['activo'] !== null && $filtro['activo'] !== '') {
            $query->where('estado', (int)$filtro['activo']);
        }

        if ($filtro['tipo_movimiento'] !== null && $filtro['tipo_movimiento'] !== '') {
            $query->where('tipo_movimiento', 'LIKE', '%' . $filtro['tipo_movimiento'] . '%');
        }

        if ($filtro['fecha_desde'] !== null && $filtro['fecha_desde'] !== '') {
            $query->whereDate('fecha', '>=', $filtro['fecha_desde']);
        }

        if ($filtro['fecha_hasta'] !== null && $filtro['fecha_hasta'] !== '') {
            $query->whereDate('fecha', '<=', $filtro['fecha_hasta']);
        }

        $movimiento = $query
            ->with(['lote.producto', 'lote.proveedor', 'sede', 'unidad'])
            ->orderBy('id', 'desc');

        if (!$isPdf) {
            return $movimiento->paginate(10)->withQueryString();
        }

        return $movimiento->get();
    }
}
