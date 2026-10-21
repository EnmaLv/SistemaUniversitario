<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use LogicException;

class Bitacora extends Model
{
    public const UPDATED_AT = null;

    public const NIVEL_INFO = 'info';
    public const NIVEL_ADVERTENCIA = 'advertencia';
    public const NIVEL_CRITICO = 'critico';

    protected $table = 'bitacora_sistema';

    protected $primaryKey = 'id_bis';

    protected $fillable = [
        'modulo_bis',
        'tipo_evento_bis',
        'tabla_afectada_bis',
        'registro_id_bis',
        'use_id',
        'nombre_usuario_bis',
        'datos_anteriores_bis',
        'datos_nuevos_bis',
        'direccion_ip_bis',
        'navegador_bis',
        'sistema_operativo_bis',
        'metodo_http_bis',
        'ruta_bis',
        'url_bis',
        'codigo_respuesta_bis',
        'nivel_bis',
        'session_id_bis',
        'observacion_bis',
        'created_id',
    ];

    protected $casts = [
        'datos_anteriores_bis' => 'array',
        'datos_nuevos_bis'     => 'array',
        'created_at'           => 'datetime',
        'codigo_respuesta_bis' => 'integer',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'use_id', 'id_use');
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
                    $sub->where('nombre_usuario_bis', 'like', '%' . $usuario . '%');
                    if (ctype_digit((string) $usuario)) {
                        $sub->orWhere('use_id', (int) $usuario);
                    }
                });
            })
            ->when($filtros['modulo'] ?? null, fn ($q, $modulo) => $q->where('modulo_bis', $modulo))
            ->when($filtros['accion'] ?? null, fn ($q, $accion) => $q->where('tipo_evento_bis', $accion))
            ->when($filtros['nivel'] ?? null, fn ($q, $nivel) => $q->where('nivel_bis', $nivel))
            ->when($filtros['ip'] ?? null, fn ($q, $ip) => $q->where('direccion_ip_bis', 'like', $ip . '%'))
            ->when($filtros['desde'] ?? null, fn ($q, $desde) => $q->where('created_at', '>=', $desde . ' 00:00:00'))
            ->when($filtros['hasta'] ?? null, fn ($q, $hasta) => $q->where('created_at', '<=', $hasta . ' 23:59:59'));
    }
}
