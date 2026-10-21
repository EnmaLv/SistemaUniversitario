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
        return $this->hasMany(Consultorio::class, 'sed_id');
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
            $sede->inventario = DB::table('inventario_sede_lotes as isl')
                ->join('lotes', 'lotes.id', '=', 'isl.lote_id')
                ->join('productos', 'productos.id', '=', 'lotes.producto_id')
                ->where('isl.sed_id', $id)
                ->where('isl.cantidad', '>', 0)
                ->select(
                    'productos.nombre as producto_nombre',
                    'productos.codigo as producto_codigo',
                    'lotes.codigo_lote',
                    'lotes.fecha_vencimiento',
                    'isl.cantidad'
                )
                ->get();

            $sede->total_productos = $sede->inventario->count();
            $sede->cantidad_total = $sede->inventario->sum('cantidad');
        }

        return $sede;
    }

    public static function tieneInventario($id)
    {
        return DB::table('inventario_sede_lotes')
            ->where('sed_id', $id)
            ->where('cantidad', '>', 0)
            ->exists();
    }

    public static function tieneMovimientos($id)
    {
        return DB::table('movimiento_inventarios')
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
            'total_productos' => DB::table('inventario_sede_lotes as isl')
                ->join('lotes', 'lotes.id', '=', 'isl.lote_id')
                ->where('isl.sed_id', $id)
                ->where('isl.cantidad', '>', 0)
                ->distinct('lotes.producto_id')
                ->count('lotes.producto_id'),

            'total_lotes' => DB::table('inventario_sede_lotes')
                ->where('sed_id', $id)
                ->where('cantidad', '>', 0)
                ->count(),

            'movimientos_mes' => DB::table('movimiento_inventarios')
                ->where('sed_id', $id)
                ->whereMonth('fecha', date('m'))
                ->whereYear('fecha', date('Y'))
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
