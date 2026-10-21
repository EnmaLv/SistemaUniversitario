<?php

namespace App\Models;

use App\Traits\ConvierteAMayusculasNoEloquent;
use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Proveedor extends Model
{
    use ConvierteAMayusculasNoEloquent;
    use HasFactory;
    use RegistraAutoria;
    use SoftDeletes;

    protected $table = 'proveedor';

    protected $primaryKey = 'id_prv';

    protected $fillable = [
        'empresa_prv',
        'rif_prv',
        'direccion_prv',
        'nombre_contacto_prv',
        'telefono_prv',
        'email_prv',
        'status_id',
    ];

    public function lotes()
    {
        return $this->hasMany(Lote::class, 'prv_id', 'id_prv');
    }

    public function compras()
    {
        return $this->hasMany(Compra::class, 'prv_id', 'id_prv');
    }

    public static function listarProveedores($buscar = null, $activo = null)
    {
        $query = DB::table('proveedor')
            ->select('proveedor.*')
            ->whereNull('deleted_at');

        if ($buscar) {
            $query->where(function ($q) use ($buscar) {
                $q->where('empresa_prv', 'like', "%{$buscar}%")
                    ->orWhere('rif_prv', 'like', "%{$buscar}%")
                    ->orWhere('nombre_contacto_prv', 'like', "%{$buscar}%")
                    ->orWhere('email_prv', 'like', "%{$buscar}%");
            });
        }

        if ($activo !== null && $activo !== '') {
            $query->where('status_id', (int) $activo === 1 ? self::STATUS_ACTIVO : self::STATUS_INACTIVO);
        } else {
            $query->where('status_id', self::STATUS_ACTIVO);
        }

        return $query->orderBy('id_prv', 'desc')->paginate(10)->withQueryString();
    }

    public static function crearProveedor(array $data)
    {
        $helper = new self();

        $data = $helper->convertirCamposAMayusculas($data, ['empresa', 'rif', 'direccion', 'nombre']);
        return DB::table('proveedor')->insertGetId([
            'empresa_prv'         => $data['empresa'] ?? null,
            'rif_prv'             => $data['rif'] ?? null,
            'direccion_prv'       => $data['direccion'] ?? null,
            'nombre_contacto_prv' => $data['nombre'] ?? null,
            'telefono_prv'        => $data['telefono'],
            'email_prv'           => $data['email'],
            'status_id'           => self::STATUS_ACTIVO,
            'created_id'          => Auth::id(),
            'created_at'          => now(),
            'updated_at'          => now(),
        ], 'id_prv');
    }

    public static function obtenerProveedor($id)
    {
        return DB::table('proveedor')
            ->where('id_prv', $id)
            ->first();
    }

    public static function actualizarProveedor($id, array $data)
    {
        $helper = new self();

        $data = $helper->convertirCamposAMayusculas($data, ['empresa', 'rif', 'direccion', 'nombre']);
        return DB::table('proveedor')
            ->where('id_prv', $id)
            ->update([
                'empresa_prv'         => $data['empresa'] ?? null,
                'rif_prv'             => $data['rif'] ?? null,
                'direccion_prv'       => $data['direccion'] ?? null,
                'nombre_contacto_prv' => $data['nombre'] ?? null,
                'telefono_prv'        => $data['telefono'],
                'email_prv'           => $data['email'],
                'updated_id'          => Auth::id(),
                'updated_at'          => now(),
            ]);
    }

    public static function eliminarProveedor($id)
    {
        return DB::table('proveedor')
            ->where('id_prv', $id)
            ->update([
                'status_id'  => self::STATUS_INACTIVO,
                'updated_id' => Auth::id(),
                'updated_at' => now(),
            ]);
    }

    public static function activarProveedor($id)
    {
        return DB::table('proveedor')
            ->where('id_prv', $id)
            ->update([
                'status_id'  => self::STATUS_ACTIVO,
                'updated_id' => Auth::id(),
                'updated_at' => now(),
            ]);
    }

    public static function cambiarEstado($id, $estado)
    {
        return DB::table('proveedor')
            ->where('id_prv', $id)
            ->update([
                'status_id'  => (int) $estado === 1 ? self::STATUS_ACTIVO : self::STATUS_INACTIVO,
                'updated_id' => Auth::id(),
                'updated_at' => now(),
            ]);
    }

    public static function obtenerProveedorConCompras($id)
    {
        $proveedor = DB::table('proveedor')
            ->where('id_prv', $id)
            ->first();

        if ($proveedor) {
            $proveedor->compras = \App\Support\Sedes\SedeActiva::filtrar(DB::table('compra'), 'compra.sed_id')
                ->where('prv_id', $id)
                ->whereNull('deleted_at')
                ->orderBy('fecha_compra_cmp', 'desc')
                ->get();
        }

        return $proveedor;
    }

    public static function tieneCompras($id)
    {
        return DB::table('compra')
            ->where('prv_id', $id)
            ->exists();
    }
}
