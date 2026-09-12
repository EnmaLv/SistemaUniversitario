<?php

namespace App\Models;

use App\Traits\ConvierteAMayusculasNoEloquent;
use Illuminate\Database\Eloquent\Model;

class BusMarca extends Model
{
    use ConvierteAMayusculasNoEloquent;

    protected $table    = 'marcas';
    protected $fillable = ['nombre', 'descripcion', 'estado'];

    protected $casts = [
        'estado' => 'boolean',
    ];

    public function modelos()
    {
        return $this->hasMany(BusModelo::class, 'marca_id');
    }

    public static function listarMarcas($buscar = null, $estado = 1)
    {
        return self::query()
            ->when($buscar, fn ($q) => $q->where('nombre', 'like', "%{$buscar}%"))
            ->when($estado !== null && $estado !== '', fn ($q) => $q->where('estado', $estado))
            ->orderBy('nombre')
            ->paginate(10)
            ->withQueryString();
    }

    public static function crearMarca(array $datos)
    {
        return self::create([
            'nombre'      => $datos['nombre'],
            'descripcion' => $datos['descripcion'] ?? null,
            'estado'      => 1,
        ]);
    }

    public static function actualizarMarca(BusMarca $marca, array $datos)
    {
        $marca->update([
            'nombre'      => $datos['nombre'],
            'descripcion' => $datos['descripcion'] ?? null,
        ]);

        return $marca;
    }

    public function getDescripcionAttribute($value): string
    {
        return $value ?? 'Ninguna';
    }
}