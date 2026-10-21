<?php

namespace App\Models\salud;

use App\Traits\PerteneceASede;
use App\Traits\RegistraAutoria;
use App\Models\Sede;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Traits\ConvierteAMayusculasNoEloquent;


class Consultorio extends Model
{
    use PerteneceASede;
    use RegistraAutoria;
    use SoftDeletes;

    use ConvierteAMayusculasNoEloquent;

    protected $table = 'consultorio';

    protected $primaryKey = 'id_csr';

    protected $fillable = [
        'nombre_csr',
        'sed_id',
        'descripcion_csr',
        'status_id',
    ];

    public function sede()
    {
        return $this->belongsTo(Sede::class, 'sed_id', 'id_sed');
    }

    public function horarios()
    {
        return $this->hasMany(HorarioConsultorio::class, 'csr_id', 'id_csr');
    }

    public static function listar($buscar = null, $activo = 1)
    {
        $query = self::query()->with('sede');

        if ($buscar) {
            $query->where('nombre_csr', 'like', "%{$buscar}%");
        }

        if ($activo !== null && $activo !== '') {
            $query->where('status_id', (int) $activo === 1 ? self::STATUS_ACTIVO : self::STATUS_INACTIVO);
        }

        return $query->orderBy('nombre_csr', 'asc')
            ->paginate(10)
            ->withQueryString();
    }


    public static function crear(array $data)
    {
        $helper = new self();

        $data = $helper->convertirCamposAMayusculas($data, ['nombre', 'descripcion']);

        return DB::table('consultorio')->insertGetId([
            'nombre_csr'      => $data['nombre'],
            'descripcion_csr' => $data['descripcion'] ?? null,
            'sed_id'          => $data['sed_id'],
            'status_id'       => self::STATUS_ACTIVO,
            'created_id'      => Auth::id(),
            'created_at'      => now(),
            'updated_at'      => now(),
        ], 'id_csr');
    }

    public static function actualizar($id, array $data)
    {
        $helper = new self();

        $data = $helper->convertirCamposAMayusculas($data, ['nombre', 'descripcion']);

        return DB::table('consultorio')
            ->where('id_csr', $id)
            ->update([
                'nombre_csr'      => $data['nombre'],
                'descripcion_csr' => $data['descripcion'] ?? null,
                'sed_id'          => $data['sed_id'],
                'updated_id'      => Auth::id(),
                'updated_at'      => now(),
            ]);
    }

    public static function eliminar($id)
    {
        return DB::table('consultorio')
            ->where('id_csr', $id)
            ->update([
                'status_id'  => self::STATUS_INACTIVO,
                'updated_id' => Auth::id(),
                'updated_at' => now(),
            ]);
    }

    public static function activar($id)
    {
        return DB::table('consultorio')
            ->where('id_csr', $id)
            ->update([
                'status_id'  => self::STATUS_ACTIVO,
                'updated_id' => Auth::id(),
                'updated_at' => now(),
            ]);
    }
}
