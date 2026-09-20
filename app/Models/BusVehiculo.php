<?php

namespace App\Models;

use App\Traits\ConvierteAMayusculasNoEloquent;
use Illuminate\Database\Eloquent\Model;
use App\Traits\FiltroPorSede;

class BusVehiculo extends Model
{
    use FiltroPorSede;

    use ConvierteAMayusculasNoEloquent;

    protected $table = 'vehiculos';

    protected $fillable = [
        'placa',
        'modelo_id',
        'anio',
        'color',
        'cantidad_pasajeros',
        'peso',
        'tipo_combustible_id',
        'cantidad_cilindros',
        'capacidad_tanque_litros',
        'nivel_combustible_actual',
        'consumo_urbano',
        'consumo_carretera',
        'consumo_relenti',
        'km_actual',
        'km_proximo_mantenimiento',
        'sede_id',
        'activo',
        'estado',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'consumo_urbano' => 'decimal:3',
        'consumo_carretera' => 'decimal:3',
        'consumo_relenti' => 'decimal:3',
        'capacidad_tanque_litros' => 'decimal:2',
        'km_actual' => 'decimal:2',
        'km_proximo_mantenimiento' => 'decimal:2',
    ];

    public function modelo()
    {
        return $this->belongsTo(BusModelo::class, 'modelo_id');
    }

    public function tipoCombustible()
    {
        return $this->belongsTo(BusTipoCombustible::class, 'tipo_combustible_id');
    }

    public function sede()
    {
        return $this->belongsTo(Sede::class, 'sede_id');
    }

    public function conductor()
    {
        return $this->belongsTo(\App\Models\Usuario::class, 'conductor_id', 'id_usuario');
    }

    public static function listarVehiculos($buscar = null, $estado = "todos", $activo = 1)
    {
        return self::query()
            ->with(['modelo.busMarca', 'tipoCombustible', 'sede'])
            ->when($buscar, fn($q) => $q->where('placa', 'like', "%{$buscar}%")
                ->orWhere('color', 'like', "%{$buscar}%"))
            ->when($activo !== null && $activo !== '', fn($q) => $q->where('activo', $activo))
            ->when($estado !== null && $estado !== '' && $estado !== 'todos', fn($q) => $q->where('estado', $estado))
            ->orderBy('placa')
            ->paginate(10)
            ->withQueryString();
    }

    public static function crearVehiculo(array $datos)
    {
        return self::create($datos);
    }

    public static function actualizarVehiculo(BusVehiculo $vehiculo, array $datos)
    {
        $vehiculo->update($datos);
        return $vehiculo;
    }

    public function getEstadoBadgeAttribute(): string
    {
        return match ($this->estado) {
            'disponible'   => '<span class="rd-badge rd-badge-success">Disponible</span>',
            'en_ruta'      => '<span class="rd-badge rd-badge-info">En Ruta</span>',
            'mantenimiento' => '<span class="rd-badge rd-badge-warning">Mantenimiento</span>',
            'inactivo'     => '<span class="rd-badge rd-badge-danger">Inactivo</span>',
            default        => '<span class="rd-badge">Desconocido</span>',
        };
    }
}
