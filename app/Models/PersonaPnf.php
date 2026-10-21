<?php

namespace App\Models;

use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PersonaPnf extends Model
{
    use RegistraAutoria;
    use SoftDeletes;

    protected $table = 'persona_programa';

    protected $primaryKey = 'id_pep';

    protected $fillable = [
        'per_id',
        'prg_id',
        'fecha_inicio_pep',
        'fecha_fin_pep',
    ];

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'per_id', 'id_per');
    }

    public function pnf()
    {
        return $this->belongsTo(Pnf::class, 'prg_id', 'id_prg');
    }
}
