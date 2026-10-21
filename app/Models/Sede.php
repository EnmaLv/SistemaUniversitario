<?php

namespace App\Models;

use App\Models\salud\Consultorio;
use App\Observers\SedeObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use App\Traits\ConvierteAMayusculasNoEloquent;
use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Sede extends Model
{
    use ConvierteAMayusculasNoEloquent;
    use HasFactory;
    use RegistraAutoria;
    use SoftDeletes;

    protected $table = 'sede';

    protected $primaryKey = 'id_sed';

    protected $fillable = [
        'nombre_sed',
        'siglas_sed',
        'direccion_sed',
        'telefono_sed',
        'correo_sed',
        'uni_id',
        'loc_id',
        'status_id',
    ];

    public function universidad()
    {
        return $this->belongsTo(Universidad::class, 'uni_id', 'id_uni');
    }

    public function inventarioSedeLotes()
    {
        return $this->hasMany(InventarioSedeLote::class, 'sed_id');
    }

    public function movimientos()
    {
        return $this->hasMany(MovimientoInventario::class, 'sed_id');
    }

    public function consultorios()
    {
        return $this->hasMany(Consultorio::class, 'sed_id', 'id_sed');
    }

    public static function listarSedes($buscar = null, $activo = null)
    {
        $query = DB::table('sede')
            ->select('sede.*');

        if ($buscar) {
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre_sed', 'like', "%{$buscar}%")
                    ->orWhere('direccion_sed', 'like', "%{$buscar}%")
                    ->orWhere('telefono_sed', 'like', "%{$buscar}%");
            });
        }

        if ($activo !== null && $activo !== '') {
            $query->where('status_id', (int) $activo === 1 ? self::STATUS_ACTIVO : self::STATUS_INACTIVO);
        } else {
            $query->where('status_id', self::STATUS_ACTIVO);
        }
        return $query->whereNull('deleted_at')->orderBy('id_sed', 'desc')->paginate(10)->withQueryString();
    }

    public static function crearSede(array $data)
    {
        $helper = new self();

        $data = $helper->convertirCamposAMayusculas($data, ['nombre', 'direccion']);
        $sede = Sede::create([
            'nombre_sed'    => $data['nombre'],
            'direccion_sed' => $data['direccion'],
            'telefono_sed'  => $data['telefono'],
            'uni_id'        => Universidad::query()->orderBy('id_uni')->value('id_uni'),
            'status_id'     => self::STATUS_ACTIVO,
        ]);

        return $sede->id_sed;
    }

    public static function obtenerSede($id)
    {
        return DB::table('sede')
            ->where('id_sed', $id)
            ->first();
    }

    public static function actualizarSede($id, array $data)
    {
        return DB::table('sede')
            ->where('id_sed', $id)
            ->update([
                'nombre_sed'    => $data['nombre'],
                'direccion_sed' => $data['direccion'],
                'telefono_sed'  => $data['telefono'],
                'status_id'     => self::STATUS_ACTIVO,
                'updated_id'    => auth()->id(),
                'updated_at'    => now()
            ]);
    }

    public static function eliminarSede($id)
    {
        return DB::table('sede')
            ->where('id_sed', $id)
            ->update([
                'status_id' => self::STATUS_INACTIVO,
                'updated_id' => auth()->id(),
                'updated_at' => now()
            ]);
    }

    public static function activarSede($id)
    {
        return DB::table('sede')
            ->where('id_sed', $id)
            ->update([
                'status_id' => self::STATUS_ACTIVO,
                'updated_id' => auth()->id(),
                'updated_at' => now()
            ]);
    }

    public static function obtenerSedeConInventario($id)
    {
        $sede = DB::table('sede')
            ->where('id_sed', $id)
            ->first();

        if ($sede) {
            $sede->inventario = DB::table('inventario_sede_lote as isl')
                ->join('lote', 'lote.id_lot', '=', 'isl.lot_id')
                ->join('producto', 'producto.id_pdt', '=', 'lote.pdt_id')
                ->where('isl.sed_id', $id)
                ->where('isl.cantidad_isl', '>', 0)
                ->whereNull('isl.deleted_at')
                ->whereNull('lote.deleted_at')
                ->select(
                    'producto.nombre_pdt as producto_nombre',
                    'producto.codigo_pdt as producto_codigo',
                    'lote.codigo_lote_lot',
                    'lote.fecha_vencimiento_lot',
                    'isl.cantidad_isl'
                )
                ->get();

            $sede->total_productos = $sede->inventario->count();
            $sede->cantidad_total = $sede->inventario->sum('cantidad_isl');
        }

        return $sede;
    }

    public static function tieneInventario($id)
    {
        return DB::table('inventario_sede_lote')
            ->where('sed_id', $id)
            ->where('cantidad_isl', '>', 0)
            ->whereNull('deleted_at')
            ->exists();
    }

    public static function tieneMovimientos($id)
    {
        return DB::table('movimiento_inventario')
            ->where('sed_id', $id)
            ->exists();
    }

    public static function obtenerSedesActivas()
    {
        return DB::table('sede')
            ->where('status_id', self::STATUS_ACTIVO)
            ->select('id_sed', 'nombre_sed', 'direccion_sed', 'telefono_sed')
            ->orderBy('nombre_sed', 'asc')
            ->get();
    }

    public static function obtenerEstadisticas($id)
    {
        return [
            'total_productos' => DB::table('inventario_sede_lote as isl')
                ->join('lote', 'lote.id_lot', '=', 'isl.lot_id')
                ->where('isl.sed_id', $id)
                ->where('isl.cantidad_isl', '>', 0)
                ->whereNull('isl.deleted_at')
                ->distinct('lote.pdt_id')
                ->count('lote.pdt_id'),

            'total_lotes' => DB::table('inventario_sede_lote')
                ->where('sed_id', $id)
                ->where('cantidad_isl', '>', 0)
                ->whereNull('deleted_at')
                ->count(),

            'movimientos_mes' => DB::table('movimiento_inventario')
                ->where('sed_id', $id)
                ->whereNull('deleted_at')
                ->whereMonth('fecha_movimiento_moi', date('m'))
                ->whereYear('fecha_movimiento_moi', date('Y'))
                ->count(),
        ];
    }

    public static function exportarCSV($buscar = null, $activo = null)
    {
        $query = DB::table('sede')
            ->select('id_sed', 'nombre_sed', 'direccion_sed', 'telefono_sed', 'status_id');

        if ($buscar) {
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre_sed', 'like', "%{$buscar}%")
                    ->orWhere('direccion_sed', 'like', "%{$buscar}%")
                    ->orWhere('telefono_sed', 'like', "%{$buscar}%");
            });
        }

        if ($activo !== null && $activo !== '') {
            $query->where('status_id', (int) $activo === 1 ? self::STATUS_ACTIVO : self::STATUS_INACTIVO);
        }

        return $query->orderBy('id_sed', 'desc')->get();
    }
}
