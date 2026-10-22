<?php

namespace App\Models\salud;

use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PlantillaSeccion extends Model
{
    use RegistraAutoria;
    use SoftDeletes;

    protected $table = 'seccion_plantilla_historia';

    protected $primaryKey = 'id_sph';

    protected $fillable = [
        'psicologo_use_id',
        'titulo_sph',
        'descripcion_general_sph',
        'segmentos_sph',
        'status_id',
    ];

    protected $casts = [
        'segmentos_sph' => 'array',
    ];

    public static function obtenerPsicologo($psicologoId)
    {
        return DB::table('user')->where('id_use', $psicologoId)->first();
    }

    public static function obtenerPorPsicologo($psicologoId)
    {
        return DB::table('seccion_plantilla_historia')
            ->where('psicologo_use_id', $psicologoId)
            ->whereNull('deleted_at')
            ->orderBy('titulo_sph')
            ->paginate(8);
    }

    public static function obtenerPorId($id, $psicologoId)
    {
        return DB::table('seccion_plantilla_historia')
            ->where('id_sph', $id)
            ->where('psicologo_use_id', $psicologoId)
            ->whereNull('deleted_at')
            ->first();
    }

    public static function existeTitulo($titulo, $psicologoId, $excluirId = null)
    {
        $query = DB::table('seccion_plantilla_historia')
            ->where('psicologo_use_id', $psicologoId)
            ->whereNull('deleted_at')
            ->where('titulo_sph', $titulo);

        if ($excluirId) {
            $query->where('id_sph', '!=', $excluirId);
        }

        return $query->exists();
    }

    public static function crear($psicologoId, $data)
    {
        $valores = [
            'descripcion_general_sph' => $data['descripcion_general'] ?? null,
            'segmentos_sph' => isset($data['segmentos']) ? json_encode($data['segmentos']) : null,
        ];

        $eliminada = DB::table('seccion_plantilla_historia')
            ->where('psicologo_use_id', $psicologoId)
            ->where('titulo_sph', $data['titulo'])
            ->whereNotNull('deleted_at')
            ->first();

        if ($eliminada) {
            DB::table('seccion_plantilla_historia')
                ->where('id_sph', $eliminada->id_sph)
                ->update($valores + [
                    'deleted_at' => null,
                    'updated_id' => Auth::id(),
                    'updated_at' => now(),
                ]);

            return $eliminada->id_sph;
        }

        return DB::table('seccion_plantilla_historia')->insertGetId($valores + [
            'psicologo_use_id' => $psicologoId,
            'titulo_sph' => $data['titulo'],
            'created_id' => Auth::id(),
            'created_at' => now(),
            'updated_at' => null,
        ], 'id_sph');
    }

    public static function actualizar($id, $psicologoId, $data)
    {
        return DB::table('seccion_plantilla_historia')
            ->where('id_sph', $id)
            ->where('psicologo_use_id', $psicologoId)
            ->whereNull('deleted_at')
            ->update([
                'titulo_sph' => $data['titulo'],
                'descripcion_general_sph' => $data['descripcion_general'] ?? null,
                'segmentos_sph' => isset($data['segmentos']) ? json_encode($data['segmentos']) : null,
                'updated_id' => Auth::id(),
                'updated_at' => now(),
            ]);
    }

    public static function eliminar($id, $psicologoId)
    {
        return DB::table('seccion_plantilla_historia')
            ->where('id_sph', $id)
            ->where('psicologo_use_id', $psicologoId)
            ->update([
                'deleted_at' => now(),
                'updated_id' => Auth::id(),
                'updated_at' => now(),
            ]);
    }

    public static function estaEnUso($id, $psicologoId)
    {
        $plantilla = self::obtenerPorId($id, $psicologoId);
        if (!$plantilla) return false;

        $titulosUso = DB::table('historia_seccion_personalizada as hs')
            ->join('historia_clinica as hc', 'hs.hcl_id', '=', 'hc.id_hcl')
            ->where('hc.psicologo_use_id', $psicologoId)
            ->where('hs.titulo_hsp', $plantilla->titulo_sph)
            ->exists();

        return $titulosUso;
    }
}
