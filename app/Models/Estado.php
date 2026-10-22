<?php

namespace App\Models;

use App\Traits\ConvierteAMayusculas;
use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Estado extends Model
{
    use ConvierteAMayusculas;
    use RegistraAutoria;
    use SoftDeletes;

    protected $table = 'estado';

    protected $primaryKey = 'id_esd';

    protected $fillable = [
        'nombre_esd',
        'pai_id',
        'status_id',
    ];

    protected $mayusculas = [
        'nombre_esd'
    ];

    protected static function booted(): void
    {
        static::creating(function (Estado $estado) {
            $estado->pai_id ??= Pais::query()->orderBy('id_pai')->value('id_pai');
        });
    }

    public function pais()
    {
        return $this->belongsTo(Pais::class, 'pai_id', 'id_pai');
    }

    public function municipio()
    {
        return $this->hasMany(Municipio::class, 'esd_id', 'id_esd');
    }
}
