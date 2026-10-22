<?php

namespace App\Models\Becas;

use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Beneficio extends Model
{
    use RegistraAutoria;
    use SoftDeletes;

    protected $table = 'beneficio_estudiantil';

    protected $primaryKey = 'id_bes';

    protected $fillable = [
        'nombre_beneficio_bes',
        'descripcion_bes',
        'slug_bes',
        'cupones_disponibles_bes',
        'cupones_ocupados_bes',
        'tbe_id',
        'status_id',
    ];

    protected $casts = [
        'cupones_disponibles_bes' => 'integer',
        'cupones_ocupados_bes'    => 'integer',
        'status_id'               => 'integer',
    ];

    public function criterios()
    {
        return $this->hasMany(BeneficioCriterio::class, 'bes_id', 'id_bes');
    }
}
