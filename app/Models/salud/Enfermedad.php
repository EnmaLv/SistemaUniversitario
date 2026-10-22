<?php

namespace App\Models\salud;

use App\Traits\RegistraAutoria;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Enfermedad extends Model
{
    use RegistraAutoria;
    use SoftDeletes;

    protected $table = 'enfermedad';

    protected $primaryKey = 'id_efm';

    protected $fillable = [
        'codigo_efm',
        'nombre_efm',
        'categoria_efm',
        'nivel_gravedad_efm',
        'status_id',
    ];

    const TIPO_MENTAL = 'mental';
    const TIPO_FISICA = 'fisica';
    const TIPO_BIOPSICOSOCIAL = 'biopsicosocial';

    public static function obtenerEnfermedades($cantidad = 8, $search = null, $categoria = null, $activo = 1)
    {
        $query = DB::table('enfermedad')->whereNull('deleted_at');

        if ($activo !== null && $activo !== '') {
            $query->where('status_id', (int) $activo === 1 ? self::STATUS_ACTIVO : self::STATUS_INACTIVO);
        }

        if ($search) {
            $searchNormalized = mb_strtolower(trim($search), 'UTF-8');
            $query->where(function ($q) use ($searchNormalized) {
                $q->whereRaw("LOWER(nombre_efm) LIKE ?", ["%{$searchNormalized}%"])
                    ->orWhereRaw("LOWER(codigo_efm) LIKE ?", ["%{$searchNormalized}%"]);
            });
        }

        if ($categoria) {
            $query->where('categoria_efm', $categoria);
        }

        return $query->orderBy('codigo_efm', 'asc')
            ->orderBy('nombre_efm', 'asc')
            ->paginate($cantidad);
    }

    public static function obtenerPorId($id)
    {
        return DB::table('enfermedad')
            ->where('id_efm', $id)
            ->whereNull('deleted_at')
            ->first();
    }

    public static function existeEnfermedad($nombre, $codigo = null, $categoria = null, $excludeId = null)
    {
        $query = DB::table('enfermedad')
            ->whereNull('deleted_at')
            ->where('nombre_efm', $nombre);

        if ($codigo) {
            $query->where('codigo_efm', $codigo);
        }

        if ($categoria) {
            $query->where('categoria_efm', $categoria);
        }

        if ($excludeId) {
            $query->where('id_efm', '!=', $excludeId);
        }

        return $query->exists();
    }

    public static function crearEnfermedad($data)
    {
        try {
            DB::beginTransaction();
            $res = DB::table('enfermedad')->insertGetId([
                'codigo_efm'         => $data['codigo'] ?? $data['tipo'] ?? null,
                'nombre_efm'         => $data['nombre'],
                'categoria_efm'      => $data['categoria'] ?? self::TIPO_FISICA,
                'nivel_gravedad_efm' => $data['nivel'] ?? 0,
                'status_id'          => self::STATUS_ACTIVO,
                'created_id'         => Auth::id(),
                'created_at'         => Carbon::now(),
                'updated_at'         => Carbon::now(),
            ], 'id_efm');
            DB::commit();
            return $res;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function actualizarEnfermedad($id, $data)
    {
        try {
            DB::beginTransaction();
            $res = DB::table('enfermedad')->where('id_efm', $id)->update([
                'codigo_efm'         => $data['codigo'] ?? $data['tipo'] ?? null,
                'nombre_efm'         => $data['nombre'],
                'categoria_efm'      => $data['categoria'] ?? self::TIPO_FISICA,
                'nivel_gravedad_efm' => $data['nivel'] ?? 0,
                'updated_id'         => Auth::id(),
                'updated_at'         => Carbon::now(),
            ]);
            DB::commit();
            return $res;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function eliminarEnfermedad($id)
    {
        try {
            DB::beginTransaction();
            $res = DB::table('enfermedad')->where('id_efm', $id)->update([
                'status_id'  => self::STATUS_INACTIVO,
                'updated_id' => Auth::id(),
                'updated_at' => Carbon::now(),
            ]);
            DB::commit();
            return $res;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function activar($id)
    {
        try {
            DB::beginTransaction();
            $res = DB::table('enfermedad')->where('id_efm', $id)->update([
                'status_id'  => self::STATUS_ACTIVO,
                'updated_id' => Auth::id(),
                'updated_at' => Carbon::now(),
            ]);
            DB::commit();
            return $res;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function mental()
    {
        return DB::table('enfermedad')
            ->where('categoria_efm', self::TIPO_MENTAL)
            ->where('status_id', self::STATUS_ACTIVO)
            ->whereNull('deleted_at');
    }

    public static function fisica()
    {
        return DB::table('enfermedad')
            ->where('categoria_efm', self::TIPO_FISICA)
            ->where('status_id', self::STATUS_ACTIVO)
            ->whereNull('deleted_at');
    }

    public static function obtenerTodasActivas()
    {
        return DB::table('enfermedad')
            ->where('status_id', self::STATUS_ACTIVO)
            ->whereNull('deleted_at')
            ->orderBy('nombre_efm')
            ->get();
    }

    public static function obtenerNombrePorId($id)
    {
        return DB::table('enfermedad')
            ->where('id_efm', $id)
            ->value('nombre_efm');
    }
}
