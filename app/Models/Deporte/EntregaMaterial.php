<?php

namespace App\Models\Deporte;

use App\Models\Persona;
use App\Models\Sede;
use App\Models\Usuario;
use App\Traits\PerteneceASede;
use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EntregaMaterial extends Model
{
    use PerteneceASede;
    use RegistraAutoria;
    use SoftDeletes;

    protected $table = 'entrega_material';

    protected $primaryKey = 'id_ema';

    protected $fillable = [
        'fecha_ema',
        'dependencia_ema',
        'motivo_ema',
        'per_id',
        'sed_id',
        'use_id',
        'status_id',
    ];

    protected $casts = [
        'fecha_ema' => 'datetime',
    ];

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'per_id', 'id_per');
    }

    public function sede()
    {
        return $this->belongsTo(Sede::class, 'sed_id', 'id_sed');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'use_id', 'id_use');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleEntregaMaterial::class, 'ema_id', 'id_ema');
    }

    public function destinatario(): string
    {
        if ($this->persona) {
            return trim($this->persona->nombres_per . ' ' . $this->persona->apellidos_per) . ' (C.I. ' . $this->persona->cedula_per . ')';
        }

        return (string) $this->dependencia_ema;
    }
}
