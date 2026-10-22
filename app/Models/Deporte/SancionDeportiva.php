<?php

namespace App\Models\Deporte;

use App\Models\Lote;
use App\Models\Persona;
use App\Models\Usuario;
use App\Traits\PerteneceASedePorRelacion;
use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SancionDeportiva extends Model
{
    use PerteneceASedePorRelacion;
    use RegistraAutoria;
    use SoftDeletes;

    public const ESTADO_ACTIVA = 'activa';

    public const ESTADO_LEVANTADA = 'levantada';

    public const FORMA_DEVOLUCION = 'devolucion';

    public const FORMA_REPOSICION = 'reposicion';

    protected $table = 'sancion_deportiva';

    protected $primaryKey = 'id_snd';

    protected $relacionSede = 'prestamo';

    protected $fillable = [
        'motivo_snd',
        'descripcion_snd',
        'fecha_sancion_snd',
        'fecha_levantamiento_snd',
        'estado_snd',
        'forma_levantamiento_snd',
        'prd_id',
        'per_id',
        'lot_id',
        'aplicada_por_use_id',
        'levantada_por_use_id',
        'status_id',
    ];

    protected $casts = [
        'fecha_sancion_snd'       => 'date',
        'fecha_levantamiento_snd' => 'date',
    ];

    public function prestamo()
    {
        return $this->belongsTo(PrestamoDeportivo::class, 'prd_id', 'id_prd');
    }

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'per_id', 'id_per');
    }

    public function loteReposicion()
    {
        return $this->belongsTo(Lote::class, 'lot_id', 'id_lot')->withTrashed();
    }

    public function aplicadaPor()
    {
        return $this->belongsTo(Usuario::class, 'aplicada_por_use_id', 'id_use');
    }

    public function levantadaPor()
    {
        return $this->belongsTo(Usuario::class, 'levantada_por_use_id', 'id_use');
    }

    public function scopeActivas($consulta)
    {
        return $consulta->where($this->qualifyColumn('estado_snd'), self::ESTADO_ACTIVA);
    }

    public function estaActiva(): bool
    {
        return $this->estado_snd === self::ESTADO_ACTIVA;
    }

    public static function etiquetaForma(?string $forma): string
    {
        return match ($forma) {
            self::FORMA_DEVOLUCION => 'Devolución de lo prestado',
            self::FORMA_REPOSICION => 'Reposición de lo dañado o perdido',
            default                => '—',
        };
    }
}
