<?php

namespace App\Models\Becas;

use Illuminate\Database\Eloquent\Model;
use App\Models\Persona;
use App\Models\Rol;

class BecaTutor extends Model
{
    protected $table = 'be_beca_tutores';

    protected $fillable = [
        'beca_id',
        'tutor_id',        'rol_id',        'descripcion',
    ];

    public function beca()
    {
        return $this->belongsTo(Beca::class, 'beca_id');
    }

    public function tutor()
    {
        return $this->belongsTo(Persona::class, 'tutor_id', 'per_id');
    }

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'rol_id', 'id_rol');
    }
}
