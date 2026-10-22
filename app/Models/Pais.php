<?php

namespace App\Models;

use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pais extends Model
{
    use RegistraAutoria;
    use SoftDeletes;

    protected $table = 'pais';

    protected $primaryKey = 'id_pai';

    protected $fillable = ['nombre_pai', 'status_id'];

    public function estados()
    {
        return $this->hasMany(Estado::class, 'pai_id', 'id_pai');
    }
}
