<?php

namespace App\Models;

use App\Traits\PerteneceASedePorRelacion;
use App\Traits\ConvierteAMayusculasNoEloquent;
use Illuminate\Database\Eloquent\Model;

class BusMantenimiento extends Model
{
    use PerteneceASedePorRelacion;

    protected $relacionSede = 'vehiculo';

    use ConvierteAMayusculasNoEloquent;

    protected $table = 'mantenimientos';

    protected $fillable = [
        'bus_vehiculo_id',
        'tipo',
        'titulo',
        'descripcion',
        'costo',
        'fecha',
        'km_al_servicio',
        'proximo_km',
        'proxima_fecha',
        'estado',
        'usuario_registro_id',
    ];

    protected $casts = [
        'fecha'         => 'date',
        'proxima_fecha' => 'date',
    ];

    public function vehiculo()
    {
        return $this->belongsTo(BusVehiculo::class, 'vehiculo_id');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_registro_id', 'id_use');
    }

    public function getDescripcionAttribute($value): string
    {
        return $value ?? 'Ninguna';
    }

    public static function listarMantenimientos($buscar = null, $estado = null)
    {
        return self::query()
            ->with(['vehiculo'])
            ->when($buscar, fn ($q) => $q->where('titulo', 'like', "%{$buscar}%")
                ->orWhereHas('vehiculo', fn ($q2) => $q2->where('placa', 'like', "%{$buscar}%")))
            ->when($estado !== null && $estado !== '', fn ($q) => $q->where('estado', $estado))
            ->orderBy('fecha', 'desc')
            ->paginate(10)
            ->withQueryString();
    }

    public static function crearMantenimiento(array $datos, int $usuarioId): self
    {
        return self::create(array_merge($datos, ['usuario_registro_id' => $usuarioId]));
    }

    public static function actualizarMantenimiento(self $mantenimiento, array $datos): self
    {
        $mantenimiento->update($datos);
        return $mantenimiento;
    }

    public function getEstadoBadgeAttribute(): string
    {
        return match($this->estado) {
            'pendiente'   => '<span class="rd-badge rd-badge-warning">Pendiente</span>',
            'en_proceso'  => '<span class="rd-badge rd-badge-info">En Proceso</span>',
            'completado'  => '<span class="rd-badge rd-badge-success">Completado</span>',
            default       => '<span class="rd-badge">-</span>',
        };
    }
}