<?php

namespace App\Models\salud;

use App\Models\Lote;
use App\Models\Persona;
use App\Models\Producto;
use App\Models\Sede;
use App\Models\Unidad;
use App\Models\Usuario;
use App\Traits\PerteneceASede;
use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dispensacion extends Model
{
    use PerteneceASede;
    use RegistraAutoria;
    use SoftDeletes;

    protected $table = 'dispensacion_medicamento';

    protected $primaryKey = 'id_dsm';

    protected $fillable = [
        'rme_id',
        'drm_id',
        'pdt_id',
        'paciente_per_id',
        'lot_id',
        'cantidad_dsm',
        'unm_id',
        'sed_id',
        'use_id',
        'fecha_dispensacion_dsm',
        'observaciones_dsm',
        'status_id',
    ];

    protected $casts = [
        'cantidad_dsm'           => 'decimal:2',
        'fecha_dispensacion_dsm' => 'date',
    ];

    public function receta()
    {
        return $this->belongsTo(RecetasMedica::class, 'rme_id', 'id_rme');
    }

    public function detalle()
    {
        return $this->belongsTo(DetalleRecetasMedica::class, 'drm_id', 'id_drm');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'pdt_id', 'id_pdt');
    }

    public function paciente()
    {
        return $this->belongsTo(Persona::class, 'paciente_per_id', 'id_per');
    }

    public function lote()
    {
        return $this->belongsTo(Lote::class, 'lot_id', 'id_lot');
    }

    public function unidad()
    {
        return $this->belongsTo(Unidad::class, 'unm_id', 'id_unm');
    }

    public function sede()
    {
        return $this->belongsTo(Sede::class, 'sed_id', 'id_sed');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'use_id', 'id_use');
    }
}
