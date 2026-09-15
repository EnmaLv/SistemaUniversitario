<?php

namespace App\Models;

use App\Models\salud\Consultorio;
use App\Observers\SedeObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use App\Traits\ConvierteAMayusculasNoEloquent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Sede extends Model
{
    use ConvierteAMayusculasNoEloquent;
    use HasFactory;

    protected $table = 'sede';

    protected $fillable = [
        'nombre',
        'direccion',
        'telefono',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean'
    ];

    public function inventarioSedeLotes()
    {
        return $this->hasMany(InventarioSedeLote::class, 'sede_id');
    }

    public function movimientos()
    {
        return $this->hasMany(MovimientoInventario::class, 'sede_id');
    }

    public function consultorios()
    {
        return $this->hasMany(Consultorio::class, 'sede_id');
    }

    public static function listarSedes($buscar = null, $activo = null)
    {
        $query = DB::table('sede')
            ->select('sede.*');

        if ($buscar) {
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                    ->orWhere('direccion', 'like', "%{$buscar}%")
                    ->orWhere('telefono', 'like', "%{$buscar}%");
            });
        }

        if ($activo !== null && $activo !== '') {
            $query->where('activo', (int)$activo);
        } else {
            $query->where('activo', 1);
        }
        return $query->orderBy('id', 'desc')->paginate(10)->withQueryString();
    }

    public static function crearSede(array $data)
    {
        $helper = new self();

        $data = $helper->convertirCamposAMayusculas($data, ['nombre', 'direccion']);
        $sede = Sede::create([
            'nombre'     => $data['nombre'],
            'direccion'  => $data['direccion'],
            'telefono'   => $data['telefono'],
            'activo'     => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return $sede->id;
    }

    public static function obtenerSede($id)
    {
        return DB::table('sede')
            ->where('id', $id)
            ->first();
    }

    public static function actualizarSede($id, array $data)
    {
        return DB::table('sede')
            ->where('id', $id)
            ->update([
                'nombre'     => $data['nombre'],
                'direccion'  => $data['direccion'],
                'telefono'   => $data['telefono'],
                'activo'     => 1,
                'updated_at' => now()
            ]);
    }

    public static function eliminarSede($id)
    {
        return DB::table('sede')
            ->where('id', $id)
            ->update([
                'activo' => 0,
                'updated_at' => now()
            ]);
    }

    public static function activarSede($id)
    {
        return DB::table('sede')
            ->where('id', $id)
            ->update([
                'activo' => 1,
                'updated_at' => now()
            ]);
    }

    public static function obtenerSedeConInventario($id)
    {
        $sede = DB::table('sede')
            ->where('id', $id)
            ->first();

        if ($sede) {
            $sede->inventario = DB::table('inventario_sede_lotes as isl')
                ->join('lotes', 'lotes.id', '=', 'isl.lote_id')
                ->join('productos', 'productos.id', '=', 'lotes.producto_id')
                ->where('isl.sede_id', $id)
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
            ->where('sede_id', $id)
            ->where('cantidad', '>', 0)
            ->exists();
    }

    public static function tieneMovimientos($id)
    {
        return DB::table('movimiento_inventarios')
            ->where('sede_id', $id)
            ->exists();
    }

    public static function obtenerSedesActivas()
    {
        return DB::table('sede')
            ->where('activo', 1)
            ->select('id', 'nombre', 'direccion', 'telefono')
            ->orderBy('nombre', 'asc')
            ->get();
    }

    public static function obtenerEstadisticas($id)
    {
        return [
            'total_productos' => DB::table('inventario_sede_lotes as isl')
                ->join('lotes', 'lotes.id', '=', 'isl.lote_id')
                ->where('isl.sede_id', $id)
                ->where('isl.cantidad', '>', 0)
                ->distinct('lotes.producto_id')
                ->count('lotes.producto_id'),

            'total_lotes' => DB::table('inventario_sede_lotes')
                ->where('sede_id', $id)
                ->where('cantidad', '>', 0)
                ->count(),

            'movimientos_mes' => DB::table('movimiento_inventarios')
                ->where('sede_id', $id)
                ->whereMonth('fecha', date('m'))
                ->whereYear('fecha', date('Y'))
                ->count(),
        ];
    }

    public static function exportarCSV($buscar = null, $activo = null)
    {
        $query = DB::table('sede')
            ->select('id', 'nombre', 'direccion', 'telefono', 'activo');

        if ($buscar) {
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                    ->orWhere('direccion', 'like', "%{$buscar}%")
                    ->orWhere('telefono', 'like', "%{$buscar}%");
            });
        }

        if ($activo !== null && $activo !== '') {
            $query->where('activo', (int)$activo);
        }

        return $query->orderBy('id', 'desc')->get();
    }
}
