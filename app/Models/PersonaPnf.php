<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonaPnf extends Model
{
    protected $table = 'persona_pnf';
    protected $primaryKey = 'id_persona_pnf';
    protected $fillable = [
        "id_persona",
        "prg_id",
        "fecha_inicio",
        "fecha_fin"
    ];

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'id_persona');
    }

    public function pnf()
    {
        return $this->belongsTo(Pnf::class, 'prg_id', 'id_prg');
    }
}
