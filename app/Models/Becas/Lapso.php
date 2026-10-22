<?php

namespace App\Models\Becas;

use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lapso extends Model
{
    use RegistraAutoria;
    use SoftDeletes;

    protected $table = 'lapso_academico';

    protected $primaryKey = 'id_laa';

    protected $fillable = [
        'codigo_laa',
        'fecha_inicio_laa',
        'fecha_fin_laa',
        'es_actual_laa',
        'permite_solicitudes_laa',
        'status_id',
    ];

    protected $casts = [
        'fecha_inicio_laa'        => 'date',
        'fecha_fin_laa'           => 'date',
        'es_actual_laa'           => 'boolean',
        'permite_solicitudes_laa' => 'boolean',
        'status_id'               => 'integer',
    ];
}
