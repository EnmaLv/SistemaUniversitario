<?php

namespace App\Models\salud;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Enfermedad extends Model
{
    protected $table = 'enfermedades';

    protected $fillable = [
        'codigo',
        'nombre',
        'categoria',
        'nivel',
        'activo',
    ];

    const TIPO_MENTAL = 'mental';
    const TIPO_FISICA = 'fisica';
    const TIPO_BIOPSICOSOCIAL = 'biopsicosocial';

    public static function obtenerEnfermedades($cantidad = 8, $search = null, $categoria = null, $activo = 1)
    {
        $query = DB::table('enfermedades');

        // Filtrar dinámicamente por estado
        if ($activo !== null && $activo !== '') {
            $query->where('activo', (int)$activo);
        }

        if ($search) {
            $searchNormalized = mb_strtolower(trim($search), 'UTF-8');
            $query->where(function ($q) use ($searchNormalized) {
                $q->whereRaw("LOWER(nombre) LIKE ?", ["%{$searchNormalized}%"])
                    ->orWhereRaw("LOWER(codigo) LIKE ?", ["%{$searchNormalized}%"]);
            });
        }

        if ($categoria) {
            $query->where('categoria', $categoria);
        }

        return $query->orderBy('codigo', 'asc')
            ->orderBy('nombre', 'asc')
            ->paginate($cantidad);
    }

    public static function obtenerPorId($id)
    {
        return DB::table('enfermedades')
            ->where('id', $id)
            ->first();
    }

    public static function existeEnfermedad($nombre, $codigo = null, $categoria = null, $excludeId = null)
    {
        $query = DB::table('enfermedades')
            ->where('nombre', $nombre);

        if ($codigo) {
            $query->where('codigo', $codigo);
        }

        if ($categoria) {
            $query->where('categoria', $categoria);
        }

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    public static function crearEnfermedad($data)
    {
        try {
            DB::beginTransaction();
            $res = DB::table('enfermedades')->insertGetId([
                'codigo' => $data['codigo'] ?? $data['tipo'] ?? null,
                'nombre' => $data['nombre'],
                'categoria' => $data['categoria'] ?? 'fisica',
                'nivel' => $data['nivel'] ?? 0,
                'activo' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
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
            $res = DB::table('enfermedades')->where('id', $id)->update([
                'codigo' => $data['codigo'] ?? $data['tipo'] ?? null,
                'nombre' => $data['nombre'],
                'categoria' => $data['categoria'] ?? 'fisica',
                'nivel'     => $data['nivel'] ?? 0,
                'updated_at' => Carbon::now(),
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
            $res = DB::table('enfermedades')->where('id', $id)->update([
                'activo' => 0,
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
            $res = DB::table('enfermedades')->where('id', $id)->update([
                'activo' => 1,
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
        return DB::table('enfermedades')
            ->where('categoria', self::TIPO_MENTAL)
            ->where('activo', 1);
    }

    public static function fisica()
    {
        return DB::table('enfermedades')
            ->where('categoria', self::TIPO_FISICA)
            ->where('activo', 1);
    }

    public static function obtenerTodasActivas()
    {
        return DB::table('enfermedades')
            ->where('activo', 1)
            ->orderBy('nombre')
            ->get();
    }

    public static function obtenerNombrePorId($id)
    {
        return DB::table('enfermedades')
            ->where('id', $id)
            ->value('nombre');
    }
}
