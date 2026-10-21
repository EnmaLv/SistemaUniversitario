<?php

namespace App\Models\salud;

use App\Models\Lote;
use App\Models\Persona;
use App\Models\Producto;
use App\Models\Sede;
use App\Models\Unidad;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Model;
use App\Traits\PerteneceASede;

class Dispensacion extends Model
{
    use PerteneceASede;

    protected $table = 'dispensacions';

    protected $fillable = [
        'receta_medica_id',
        'detalle_receta_medica_id',
        'pdt_id',
        'per_id',
        'lot_id',
        'cantidad',
        'unm_id',
        'sed_id',
        'usuario_id',
        'fecha',
        'observaciones',
    ];

    protected $casts = [
        'cantidad' => 'decimal:2',
        'fecha'    => 'date',
    ];

    public function receta()
    {
        return $this->belongsTo(RecetasMedica::class, 'receta_medica_id');
    }

    public function detalle()
    {
        return $this->belongsTo(DetalleRecetasMedica::class, 'detalle_receta_medica_id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'pdt_id', 'id_pdt');
    }

    public function paciente()
    {
        return $this->belongsTo(Persona::class, 'per_id', 'id_per');
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
        return $this->belongsTo(Usuario::class, 'usuario_id', 'id_use');
    }
}