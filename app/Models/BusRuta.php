<?php

namespace App\Models;

use App\Traits\PerteneceASede;
use App\Traits\ConvierteAMayusculasNoEloquent;
use Illuminate\Database\Eloquent\Model;

class BusRuta extends Model
{
    use PerteneceASede;

    use ConvierteAMayusculasNoEloquent;

    protected $table = 'bus_rutas';

    protected $fillable = [
        'nombre',
        'distancia_km',
        'descripcion',
        'sed_id',
        'estado',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];

    public function sede()
    {
        return $this->belongsTo(Sede::class, 'sed_id');
    }

    public function horarios()
    {
        return $this->hasMany(RutaHorario::class, 'bus_ruta_id');
    }

    public function paradas()
    {
        return $this->belongsToMany(BusParada::class, 'bus_ruta_paradas')
            ->withPivot('orden')
            ->orderBy('bus_ruta_paradas.orden');
    }

    public function getDescripcionAttribute($value): string
    {
        return $value ?? 'Ninguna';
    }

    public static function listarRutas($buscar = null, $estado = 1)
    {
        return self::query()
            ->with(['sede', 'horarios'])
            ->when($buscar, fn($q) => $q->where('nombre', 'like', "%{$buscar}%"))
            ->when($estado !== null && $estado !== '', fn($q) => $q->where('estado', $estado))
            ->orderBy('nombre')
            ->paginate(10)
            ->withQueryString();
    }

    public static function crearRuta(array $datos)
    {
        return self::create([
            'nombre'       => $datos['nombre'],
            'distancia_km' => $datos['distancia_km'],
            'descripcion'  => $datos['descripcion'] ?? null,
            'sed_id'      => $datos['sed_id'],
            'estado'       => 1,
        ]);
    }

    public static function actualizarRuta(BusRuta $ruta, array $datos)
    {
        $ruta->update([
            'nombre'       => $datos['nombre'],
            'distancia_km' => $datos['distancia_km'],
            'descripcion'  => $datos['descripcion'] ?? null,
            'sed_id'      => $datos['sed_id'],
        ]);
        return $ruta;
    }
    public function rutaParadas()
    {
        return $this->hasMany(BusRutaParada::class, 'bus_ruta_id')->orderBy('orden');
    }
}