<?php

namespace App\Models\Deporte;

use App\Models\Lote;
use App\Models\Persona;
use App\Models\Producto;
use App\Models\Sede;
use App\Models\TipoProducto;
use App\Models\Usuario;
use App\Traits\PerteneceASede;
use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PrestamoDeportivo extends Model
{
    use PerteneceASede;
    use RegistraAutoria;
    use SoftDeletes;

    public const ESTADO_ACTIVO = 'activo';

    public const ESTADO_DEVUELTO = 'devuelto';

    public const ESTADO_VENCIDO = 'vencido';

    public const ESTADO_SANCIONADO = 'sancionado';

    public const ESTADO_REPUESTO = 'repuesto';

    public const ESTADOS_PENDIENTES = [self::ESTADO_ACTIVO, self::ESTADO_VENCIDO, self::ESTADO_SANCIONADO];

    public const DEVOLUCION_BUENO = 'bueno';

    public const DEVOLUCION_DANADO = 'dañado';

    public const DEVOLUCION_PERDIDO = 'perdido';

    public const ESTADOS_DEVOLUCION = [self::DEVOLUCION_BUENO, self::DEVOLUCION_DANADO, self::DEVOLUCION_PERDIDO];

    public const TIPO_DEPORTIVO = 'deportivo';

    public const TIPO_OFICINA = 'oficina';

    public const TIPOS_PRODUCTO = [
        self::TIPO_DEPORTIVO => TipoProducto::IMPLEMENTO_DEPORTIVO,
        self::TIPO_OFICINA   => TipoProducto::MATERIAL_OFICINA,
    ];

    protected $table = 'prestamo_deportivo';

    protected $primaryKey = 'id_prd';

    protected $fillable = [
        'fecha_prestamo_prd',
        'fecha_devolucion_esperada_prd',
        'fecha_devolucion_real_prd',
        'estado_prd',
        'estado_devolucion_prd',
        'tipo_prd',
        'cantidad_prd',
        'motivo_prd',
        'lugar_prd',
        'observaciones_prd',
        'pdt_id',
        'lot_id',
        'per_id',
        'epd_id',
        'sed_id',
        'registrado_por_use_id',
        'recibido_por_use_id',
        'status_id',
    ];

    protected $casts = [
        'fecha_prestamo_prd'            => 'datetime',
        'fecha_devolucion_esperada_prd' => 'datetime',
        'fecha_devolucion_real_prd'     => 'datetime',
        'cantidad_prd'                  => 'integer',
    ];

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'per_id', 'id_per');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'pdt_id', 'id_pdt')->withTrashed();
    }

    public function lote()
    {
        return $this->belongsTo(Lote::class, 'lot_id', 'id_lot')->withTrashed();
    }

    public function espacio()
    {
        return $this->belongsTo(DeporteEspacio::class, 'epd_id', 'id_epd');
    }

    public function sede()
    {
        return $this->belongsTo(Sede::class, 'sed_id', 'id_sed');
    }

    public function registradoPor()
    {
        return $this->belongsTo(Usuario::class, 'registrado_por_use_id', 'id_use');
    }

    public function recibidoPor()
    {
        return $this->belongsTo(Usuario::class, 'recibido_por_use_id', 'id_use');
    }

    public function detalles()
    {
        return $this->hasMany(DetallePrestamoDeportivo::class, 'prd_id', 'id_prd')->orderBy('id_dpd');
    }

    public function sanciones()
    {
        return $this->hasMany(SancionDeportiva::class, 'prd_id', 'id_prd');
    }

    public function scopePendientes($consulta)
    {
        return $consulta->whereIn($this->qualifyColumn('estado_prd'), self::ESTADOS_PENDIENTES);
    }

    public function scopeDeTipo($consulta, ?string $tipo)
    {
        return array_key_exists((string) $tipo, self::TIPOS_PRODUCTO)
            ? $consulta->where($this->qualifyColumn('tipo_prd'), $tipo)
            : $consulta;
    }

    public function esDeOficina(): bool
    {
        return $this->tipo_prd === self::TIPO_OFICINA;
    }

    public function resumenProductos(): string
    {
        $porProducto = [];

        foreach ($this->detalles as $detalle) {
            $nombre = $detalle->producto?->nombre_pdt ?? 'Producto';
            $porProducto[$nombre] = ($porProducto[$nombre] ?? 0) + (float) $detalle->cantidad_dpd;
        }

        if ($porProducto === []) {
            return ($this->producto?->nombre_pdt ?? 'Producto') . ' x' . $this->cantidad_prd;
        }

        return collect($porProducto)
            ->map(fn ($cantidad, $nombre) => $nombre . ' x' . DetallePrestamoDeportivo::numero($cantidad))
            ->implode(', ');
    }

    public function estaPendiente(): bool
    {
        return in_array($this->estado_prd, self::ESTADOS_PENDIENTES, true);
    }

    public function puedeDevolverse(): bool
    {
        return in_array($this->estado_prd, [self::ESTADO_ACTIVO, self::ESTADO_VENCIDO], true);
    }

    public function lugarDescriptivo(): string
    {
        return $this->espacio?->nombre_epd ?? $this->lugar_prd ?? 'Sin especificar';
    }

    public static function etiquetaTipo(?string $tipo): string
    {
        return match ($tipo) {
            self::TIPO_OFICINA => 'Material de oficina',
            default            => 'Implemento deportivo',
        };
    }

    public static function etiquetaEstado(?string $estado): string
    {
        return match ($estado) {
            self::ESTADO_ACTIVO     => 'Activo',
            self::ESTADO_DEVUELTO   => 'Devuelto',
            self::ESTADO_VENCIDO    => 'Vencido',
            self::ESTADO_SANCIONADO => 'Pendiente de reposición',
            self::ESTADO_REPUESTO   => 'Repuesto',
            default                 => ucfirst((string) $estado),
        };
    }
}
