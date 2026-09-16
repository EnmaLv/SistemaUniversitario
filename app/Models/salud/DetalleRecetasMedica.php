<?php

namespace App\Models\salud;

use App\Models\Producto;
use App\Models\Unidad;
use Illuminate\Database\Eloquent\Model;

class DetalleRecetasMedica extends Model
{
    protected $table = 'detalle_recetas_medicas';

    protected $fillable = [
        'receta_id',
        'producto_id',
        'unidad_id',
        'cantidad',
        'frecuencia',
        'fecha_inicio',
        'fecha_fin',
        'observaciones',
    ];

    protected $casts = [
        'cantidad'     => 'decimal:2',
        'fecha_inicio' => 'date',
        'fecha_fin'    => 'date',
    ];

    public function receta()
    {
        return $this->belongsTo(RecetasMedica::class, 'receta_id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function unidad()
    {
        return $this->belongsTo(Unidad::class, 'unidad_id');
    }

    public function dispensaciones()
    {
        return $this->hasMany(Dispensacion::class, 'detalle_receta_medica_id');
    }

    /**
     * Total ya dispensado para este ítem (suma de todas sus dispensaciones).
     */
    public function getCantidadDispensadaAttribute(): float
    {
        // Usa la relación ya cargada si está disponible, para no disparar
        // una consulta extra por cada detalle en listados.
        if ($this->relationLoaded('dispensaciones')) {
            return (float) $this->dispensaciones->sum('cantidad');
        }

        return (float) $this->dispensaciones()->sum('cantidad');
    }

    public function getCantidadPendienteAttribute(): float
    {
        return max(0, round((float) $this->cantidad - $this->cantidad_dispensada, 2));
    }

    public function getEstaCompletoAttribute(): bool
    {
        return $this->cantidad_pendiente <= 0;
    }
}