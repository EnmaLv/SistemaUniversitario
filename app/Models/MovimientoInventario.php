<?php

namespace App\Models;

use App\Support\Inventario\FiltroTipoProducto;
use App\Traits\PerteneceASede;
use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MovimientoInventario extends Model
{
    use PerteneceASede;
    use RegistraAutoria;
    use SoftDeletes;

    protected $table = 'movimiento_inventario';

    protected $primaryKey = 'id_moi';

    protected $fillable = [
        'pdt_id',
        'lot_id',
        'sed_id',
        'mod_id',
        'tipo_movimiento_moi',
        'unm_id',
        'cantidad_moi',
        'cantidad_convertida_moi',
        'cantidad_anterior_moi',
        'cantidad_final_moi',
        'referencia_type_moi',
        'referencia_id_moi',
        'use_id',
        'fecha_movimiento_moi',
        'observacion_moi',
    ];

    protected $casts = [
        'fecha_movimiento_moi' => 'datetime',
    ];

    public function unidad()
    {
        return $this->belongsTo(Unidad::class, 'unm_id', 'id_unm');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'pdt_id', 'id_pdt');
    }

    public function lote()
    {
        return $this->belongsTo(Lote::class, 'lot_id', 'id_lot');
    }

    public function sede()
    {
        return $this->belongsTo(Sede::class, 'sed_id', 'id_sed');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'use_id', 'id_use');
    }

    public static function getData(array $filtro, bool $isPdf)
    {
        $query = self::query();

        if (!empty($filtro['buscar'])) {
            $query->where(function ($q) use ($filtro) {
                $q->where('tipo_movimiento_moi', 'like', "%{$filtro['buscar']}%")
                    ->orWhereHas('lote.producto', function ($p) use ($filtro) {
                        $p->where('nombre_pdt', 'like', "%{$filtro['buscar']}%");
                    })
                    ->orWhereHas('lote', function ($p) use ($filtro) {
                        $p->where('codigo_lote_lot', 'like', "%{$filtro['buscar']}%");
                    });
            });
        }

        if ($filtro['tipo_movimiento_moi'] !== null && $filtro['tipo_movimiento_moi'] !== '') {
            $query->where('tipo_movimiento_moi', 'LIKE', '%' . $filtro['tipo_movimiento_moi'] . '%');
        }

        if ($filtro['fecha_desde'] !== null && $filtro['fecha_desde'] !== '') {
            $query->whereDate('fecha_movimiento_moi', '>=', $filtro['fecha_desde']);
        }

        if ($filtro['fecha_hasta'] !== null && $filtro['fecha_hasta'] !== '') {
            $query->whereDate('fecha_movimiento_moi', '<=', $filtro['fecha_hasta']);
        }

        FiltroTipoProducto::aplicar($query, $filtro['tipo_producto'] ?? null, 'movimiento_inventario.pdt_id');

        $movimiento = $query
            ->with(['lote.producto', 'lote.proveedor', 'sede', 'unidad'])
            ->orderBy('id_moi', 'desc');

        if (!$isPdf) {
            return $movimiento->paginate(10)->withQueryString();
        }

        return $movimiento->get();
    }
}
