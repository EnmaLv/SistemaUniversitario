<?php

namespace App\Models;

use App\Traits\ConvierteAMayusculasNoEloquent;
use App\Traits\PerteneceASedePorRelacion;
use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusMantenimiento extends Model
{
    use ConvierteAMayusculasNoEloquent;
    use PerteneceASedePorRelacion;
    use RegistraAutoria;
    use SoftDeletes;

    public const COLUMNAS_FORMULARIO = [
        'bus_vehiculo_id' => 'vtr_id',
        'tipo'            => 'tipo_mnv',
        'titulo'          => 'titulo_mnv',
        'descripcion'     => 'descripcion_mnv',
        'costo'           => 'costo_mnv',
        'fecha'           => 'fecha_mnv',
        'km_al_servicio'  => 'km_al_servicio_mnv',
        'proximo_km'      => 'proximo_km_mnv',
        'proxima_fecha'   => 'proxima_fecha_mnv',
        'estado'          => 'estado_mnv',
    ];

    protected $relacionSede = 'vehiculo';

    protected $table = 'mantenimiento_vehiculo';

    protected $primaryKey = 'id_mnv';

    protected $fillable = [
        'vtr_id',
        'tipo_mnv',
        'titulo_mnv',
        'descripcion_mnv',
        'costo_mnv',
        'fecha_mnv',
        'km_al_servicio_mnv',
        'proximo_km_mnv',
        'proxima_fecha_mnv',
        'estado_mnv',
        'use_id',
        'status_id',
    ];

    protected $casts = [
        'fecha_mnv'         => 'date',
        'proxima_fecha_mnv' => 'date',
    ];

    public function vehiculo()
    {
        return $this->belongsTo(BusVehiculo::class, 'vtr_id', 'id_vtr');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'use_id', 'id_use');
    }

    public function getDescripcionMnvAttribute($value): string
    {
        return $value ?? 'Ninguna';
    }

    public static function columnasDesdeFormulario(array $datos): array
    {
        $columnas = [];

        foreach (self::COLUMNAS_FORMULARIO as $campo => $columna) {
            if (array_key_exists($campo, $datos)) {
                $columnas[$columna] = $datos[$campo];
            }
        }

        return $columnas;
    }

    public static function listarMantenimientos($buscar = null, $estado = null)
    {
        return self::query()
            ->with(['vehiculo'])
            ->when($buscar, fn ($q) => $q->where(fn ($sub) => $sub
                ->where('titulo_mnv', 'like', "%{$buscar}%")
                ->orWhereHas('vehiculo', fn ($q2) => $q2->where('placa_vtr', 'like', "%{$buscar}%"))))
            ->when($estado !== null && $estado !== '', fn ($q) => $q->where('estado_mnv', $estado))
            ->orderBy('fecha_mnv', 'desc')
            ->paginate(10)
            ->withQueryString();
    }

    public static function crearMantenimiento(array $datos, int $usuarioId): self
    {
        return self::create(array_merge(self::columnasDesdeFormulario($datos), ['use_id' => $usuarioId]));
    }

    public static function actualizarMantenimiento(self $mantenimiento, array $datos): self
    {
        $mantenimiento->update(self::columnasDesdeFormulario($datos));

        return $mantenimiento;
    }

    public function getEstadoBadgeAttribute(): string
    {
        return match ($this->estado_mnv) {
            'pendiente'  => '<span class="rd-badge rd-badge-warning">Pendiente</span>',
            'en_proceso' => '<span class="rd-badge rd-badge-info">En Proceso</span>',
            'completado' => '<span class="rd-badge rd-badge-success">Completado</span>',
            default      => '<span class="rd-badge">-</span>',
        };
    }
}
