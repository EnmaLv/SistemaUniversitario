<?php

namespace App\Models\salud;

use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NotaEvolucionCampo extends Model
{
    use RegistraAutoria;
    use SoftDeletes;

    protected $table = 'campo_nota_evolucion';

    protected $primaryKey = 'id_cne';

    protected $fillable = [
        'psicologo_use_id',
        'titulo_cne',
        'status_id',
    ];

    public static function obtenerCamposDisponibles($psicologoId)
    {
        return DB::table('campo_nota_evolucion')
            ->where(function ($query) use ($psicologoId) {
                $query->where('psicologo_use_id', $psicologoId)
                      ->orWhereNull('psicologo_use_id');
            })
            ->whereNull('deleted_at')
            ->orderBy('id_cne', 'asc')
            ->get();
    }

    public static function obtenerCamposDisponiblesPaginados($psicologoId, $perPage = 9)
    {
        return DB::table('campo_nota_evolucion')
            ->where(function ($query) use ($psicologoId) {
                $query->where('psicologo_use_id', $psicologoId)
                      ->orWhereNull('psicologo_use_id');
            })
            ->whereNull('deleted_at')
            ->orderBy('id_cne', 'desc')
            ->paginate($perPage);
    }

    public static function crearPersonalizado($psicologoId, $titulo)
    {
        return DB::table('campo_nota_evolucion')->insertGetId([
            'psicologo_use_id' => $psicologoId,
            'titulo_cne' => $titulo,
            'created_id' => Auth::id(),
            'created_at' => now(),
            'updated_at' => null,
        ], 'id_cne');
    }

    public static function existeTitulo($psicologoId, $titulo, $excludeId = null)
    {
        $query = DB::table('campo_nota_evolucion')
            ->where(function ($q) use ($psicologoId) {
                $q->where('psicologo_use_id', $psicologoId)
                  ->orWhereNull('psicologo_use_id');
            })
            ->whereNull('deleted_at')
            ->where('titulo_cne', $titulo);

        if ($excludeId) {
            $query->where('id_cne', '!=', $excludeId);
        }

        return $query->exists();
    }

    public static function obtenerPorPsicologo($psicologoId)
    {
        return DB::table('campo_nota_evolucion')
            ->where('psicologo_use_id', $psicologoId)
            ->whereNull('deleted_at')
            ->orderBy('id_cne', 'desc')
            ->get();
    }

    public static function obtenerPorId($id, $psicologoId)
    {
        return DB::table('campo_nota_evolucion')
            ->where('id_cne', $id)
            ->where('psicologo_use_id', $psicologoId)
            ->whereNull('deleted_at')
            ->first();
    }

    public static function actualizar($id, $psicologoId, $titulo)
    {
        return DB::table('campo_nota_evolucion')
            ->where('id_cne', $id)
            ->where('psicologo_use_id', $psicologoId)
            ->update([
                'titulo_cne' => $titulo,
                'updated_id' => Auth::id(),
                'updated_at' => now(),
            ]);
    }

    public static function eliminar($id, $psicologoId)
    {
        return DB::table('campo_nota_evolucion')
            ->where('id_cne', $id)
            ->where('psicologo_use_id', $psicologoId)
            ->update([
                'deleted_at' => now(),
                'updated_id' => Auth::id(),
                'updated_at' => now(),
            ]);
    }
}
