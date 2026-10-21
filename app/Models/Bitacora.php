<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use LogicException;

class Bitacora extends Model
{
    public const CREATED_AT = 'fecha_hora';
    public const UPDATED_AT = null;

    public const NIVEL_INFO = 'info';
    public const NIVEL_ADVERTENCIA = 'advertencia';
    public const NIVEL_CRITICO = 'critico';

    protected $table = 'bitacora';

    protected $fillable = [
        'modulo',
        'accion',
        'tabla_afectada',
        'registro_id',
        'usuario_id',
        'nombre_usuario',
        'datos_anteriores',
        'datos_modificados',
        'ip_address',
        'navegador',
        'sistema_operativo',
        'metodo_http',
        'ruta',
        'url',
        'codigo_respuesta',
        'nivel',
        'session_id',
        'observacion',
    ];

    protected $casts = [
        'datos_anteriores'  => 'array',
        'datos_modificados' => 'array',
        'fecha_hora'        => 'datetime',
        'codigo_respuesta'  => 'integer',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id', 'id_usuario');
    }

    public function save(array $options = [])
    {
        if ($this->exists) {
            throw new LogicException('La bitácora es de solo inserción: no se permite modificar registros.');
        }

        return parent::save($options);
    }

    public function delete()
    {
        throw new LogicException('La bitácora es de solo inserción: no se permite eliminar registros.');
    }

    public function forceDelete()
    {
        $this->delete();
    }

    public static function destroy($ids)
    {
        throw new LogicException('La bitácora es de solo inserción: no se permite eliminar registros.');
    }

    public function scopeFiltrar(Builder $query, array $filtros): Builder
    {
        return $query
            ->when($filtros['usuario'] ?? null, function ($q, $usuario) {
                $q->where(function ($sub) use ($usuario) {
                    $sub->where('nombre_usuario', 'like', '%' . $usuario . '%');
                    if (ctype_digit((string) $usuario)) {
                        $sub->orWhere('usuario_id', (int) $usuario);
                    }
                });
            })
            ->when($filtros['modulo'] ?? null, fn ($q, $modulo) => $q->where('modulo', $modulo))
            ->when($filtros['accion'] ?? null, fn ($q, $accion) => $q->where('accion', $accion))
            ->when($filtros['nivel'] ?? null, fn ($q, $nivel) => $q->where('nivel', $nivel))
            ->when($filtros['ip'] ?? null, fn ($q, $ip) => $q->where('ip_address', 'like', $ip . '%'))
            ->when($filtros['desde'] ?? null, fn ($q, $desde) => $q->where('fecha_hora', '>=', $desde . ' 00:00:00'))
            ->when($filtros['hasta'] ?? null, fn ($q, $hasta) => $q->where('fecha_hora', '<=', $hasta . ' 23:59:59'));
    }
}
