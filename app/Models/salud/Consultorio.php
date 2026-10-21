<?php

namespace App\Models\salud;

use App\Traits\PerteneceASede;
use App\Models\Sede;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Traits\ConvierteAMayusculasNoEloquent;


class Consultorio extends Model
{
    use PerteneceASede;

    use ConvierteAMayusculasNoEloquent;

    protected $table = 'consultorios';

    protected $fillable = [
        'nombre',
        'sede_id',
        'descripcion',
        'activo',
    ];

    public function sede()
    {
        return $this->belongsTo(Sede::class, 'sede_id');
    }

    public function horarios()
    {
        return $this->hasMany(HorarioConsultorio::class, 'consultorio_id');
    }

    public static function listar($buscar = null, $activo = 1)
    {
        $query = self::query()->with('sede');

        if ($buscar) {
            $query->where('nombre', 'like', "%{$buscar}%");
        }

        if ($activo !== null && $activo !== '') {
            $query->where('activo', (int)$activo);
        }

        return $query->orderBy('nombre', 'asc')
            ->paginate(10)
            ->withQueryString();
    }


    public static function crear(array $data)
    {
        $helper = new self();

        $data = $helper->convertirCamposAMayusculas($data, ['nombre', 'descripcion']);

        return DB::table('consultorios')->insertGetId([
            'nombre'      => $data['nombre'],
            'descripcion' => $data['descripcion'] ?? null,
            'sede_id'     => $data['sede_id'],
            'activo'      => 1,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }

    public static function actualizar($id, array $data)
    {
        $helper = new self();

        $data = $helper->convertirCamposAMayusculas($data, ['nombre', 'descripcion']);

        return DB::table('consultorios')
            ->where('id', $id)
            ->update([
                'nombre'      => $data['nombre'],
                'descripcion' => $data['descripcion'],
                'sede_id'     => $data['sede_id'],
                'updated_at'  => now(),
            ]);
    }

    public static function eliminar($id)
    {
        return DB::table('consultorios')
            ->where('id', $id)
            ->update([
                'activo' => 0,
                'updated_at' => now()
            ]);
    }

    public static function activar($id)
    {
        return DB::table('consultorios')
            ->where('id', $id)
            ->update([
                'activo' => 1,
                'updated_at' => now()
            ]);
    }
}
