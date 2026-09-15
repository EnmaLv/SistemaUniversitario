<?php

namespace App\Models\salud;

use App\Models\Lote;
use App\Models\Persona;
use App\Models\Producto;
use App\Models\Sede;
use App\Models\Unidad;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Model;

class Dispensacion extends Model
{
    protected $table = 'dispensacions';

    protected $fillable = [
        'receta_medica_id',
        'detalle_receta_medica_id',
        'producto_id',
        'id_persona',
        'lote_id',
        'cantidad',
        'unidad_id',
        'sede_id',
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
        return $this->belongsTo(RecetaMedica::class, 'receta_medica_id');
    }

    public function detalle()
    {
        return $this->belongsTo(DetalleRecetaMedica::class, 'detalle_receta_medica_id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function paciente()
    {
        return $this->belongsTo(Persona::class, 'id_persona', 'id_persona');
    }

    public function lote()
    {
        return $this->belongsTo(Lote::class, 'lote_id');
    }

    public function unidad()
    {
        return $this->belongsTo(Unidad::class, 'unidad_id');
    }

    public function sede()
    {
        return $this->belongsTo(Sede::class, 'sede_id');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id', 'id_usuario');
    }
}