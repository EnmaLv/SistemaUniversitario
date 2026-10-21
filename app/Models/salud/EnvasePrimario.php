<?php

namespace App\Models\salud;

use App\Traits\ConvierteAMayusculasNoEloquent;
use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EnvasePrimario extends Model
{
    use HasFactory;
    use ConvierteAMayusculasNoEloquent;
    use RegistraAutoria;
    use SoftDeletes;

    protected $table = 'envase_primario';

    protected $primaryKey = 'id_enp';

    protected $fillable = [
        'nombre_enp',
        'status_id',
    ];

    public static function listar($buscar = null, $estado = null)
    {
        $query = DB::table('envase_primario')
            ->select('envase_primario.*')
            ->whereNull('deleted_at');

        if ($buscar) {
            $query->where('nombre_enp', 'like', "%{$buscar}%");
        }

        if ($estado !== null && $estado !== '') {
            $query->where('status_id', (int) $estado === 1 ? self::STATUS_ACTIVO : self::STATUS_INACTIVO);
        }

        return $query->orderBy('nombre_enp', 'asc')->paginate(10);
    }

    public static function crear(array $data)
    {
        $helper = new self();

        $data = $helper->convertirCamposAMayusculas($data, ['nombre']);

        return DB::table('envase_primario')->insertGetId([
            'nombre_enp' => $data['nombre'],
            'status_id'  => self::STATUS_ACTIVO,
            'created_id' => Auth::id(),
            'created_at' => now(),
            'updated_at' => now(),
        ], 'id_enp');
    }

    public static function obtenerDatos($id)
    {
        return DB::table('envase_primario')
            ->where('id_enp', $id)
            ->first();
    }

    public static function actualizar($id, array $data)
    {
        $helper = new self();

        $data = $helper->convertirCamposAMayusculas($data, ['nombre']);

        return DB::table('envase_primario')
            ->where('id_enp', $id)
            ->update([
                'nombre_enp' => $data['nombre'],
                'updated_id' => Auth::id(),
                'updated_at' => now(),
            ]);
    }

    public static function eliminar($id)
    {
        return DB::table('envase_primario')
            ->where('id_enp', $id)
            ->update([
                'status_id'  => self::STATUS_INACTIVO,
                'updated_id' => Auth::id(),
                'updated_at' => now(),
            ]);
    }

    public static function activar($id)
    {
        return DB::table('envase_primario')
            ->where('id_enp', $id)
            ->update([
                'status_id'  => self::STATUS_ACTIVO,
                'updated_id' => Auth::id(),
                'updated_at' => now(),
            ]);
    }
}
