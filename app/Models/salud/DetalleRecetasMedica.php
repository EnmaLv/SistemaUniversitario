<?php

namespace App\Models\salud;

use App\Models\Producto;
use App\Models\Unidad;
use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DetalleRecetasMedica extends Model
{
    use RegistraAutoria;
    use SoftDeletes;

    protected $table = 'detalle_receta_medica';

    protected $primaryKey = 'id_drm';

    protected $fillable = [
        'rme_id',
        'pdt_id',
        'unm_id',
        'cantidad_drm',
        'cantidad_prescrita_drm',
        'unidad_prescrita_drm',
        'equivalencia_ml_drm',
        'frecuencia_drm',
        'fecha_inicio_drm',
        'fecha_fin_drm',
        'instrucciones_drm',
        'status_id',
    ];

    protected $casts = [
        'cantidad_drm'           => 'decimal:2',
        'cantidad_prescrita_drm' => 'decimal:2',
        'equivalencia_ml_drm'    => 'decimal:2',
        'fecha_inicio_drm'       => 'date',
        'fecha_fin_drm'          => 'date',
    ];

    public function receta()
    {
        return $this->belongsTo(RecetasMedica::class, 'rme_id', 'id_rme');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'pdt_id', 'id_pdt');
    }

    public function unidad()
    {
        return $this->belongsTo(Unidad::class, 'unm_id', 'id_unm');
    }

    public function dispensaciones()
    {
        return $this->hasMany(Dispensacion::class, 'drm_id', 'id_drm');
    }

    /**
     * Cantidad entregada en la unidad de dosis recetada (cantidad_dsm está en la unidad de inventario).
     */
    public function getCantidadDispensadaAttribute(): float
    {
        return (float) $this->cantidad_drm;
    }

    public function getCantidadPendienteAttribute(): float
    {
        return max(0, round((float) $this->cantidad_prescrita_drm - $this->cantidad_dispensada, 2));
    }

    public function getEstaCompletoAttribute(): bool
    {
        return $this->cantidad_pendiente <= 0;
    }
}
