<?php

namespace App\Models;

use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Perfil extends Model
{
    use RegistraAutoria;
    use SoftDeletes;

    protected $table = 'perfil';
    protected $primaryKey = 'id_perfil';
    public $timestamps = true;

    protected $fillable = ['nombre_perfil', 'status_id'];
}
