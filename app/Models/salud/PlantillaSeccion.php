<?php

namespace App\Models\salud;

use Illuminate\Support\Facades\DB;
class PlantillaSeccion
{
    public static function obtenerPsicologo($psicologoId)
    {
        return DB::table('user')->where('id_use', $psicologoId)->first();
    }

    public static function obtenerPorPsicologo($psicologoId)
    {
        return DB::table('historia_plantillas_secciones')
            ->where('psicologo_id', $psicologoId)
            ->where('status', 1)
            ->orderBy('titulo')
            ->paginate(8);
    }

    public static function obtenerPorId($id, $psicologoId)
    {
        return DB::table('historia_plantillas_secciones')
            ->where('id', $id)
            ->where('psicologo_id', $psicologoId)
            ->where('status', 1)
            ->first();
    }

    public static function existeTitulo($titulo, $psicologoId, $excluirId = null)
    {
        $query = DB::table('historia_plantillas_secciones')
            ->where('psicologo_id', $psicologoId)
            ->where('status', 1)
            ->where('titulo', $titulo);

        if ($excluirId) {
            $query->where('id', '!=', $excluirId);
        }

        return $query->exists();
    }

    public static function crear($psicologoId, $data)
    {
        return DB::table('historia_plantillas_secciones')->insertGetId([
            'psicologo_id' => $psicologoId,
            'titulo' => $data['titulo'],
            'descripcion_general' => $data['descripcion_general'] ?? null,
            'segmentos' => isset($data['segmentos']) ? json_encode($data['segmentos']) : null,
            'status' => 1,
            'created_at' => now(),
            'updated_at' => null,
        ]);
    }

    public static function actualizar($id, $psicologoId, $data)
    {
        return DB::table('historia_plantillas_secciones')
            ->where('id', $id)
            ->where('psicologo_id', $psicologoId)
            ->where('status', 1)
            ->update([
                'titulo' => $data['titulo'],
                'descripcion_general' => $data['descripcion_general'] ?? null,
                'segmentos' => isset($data['segmentos']) ? json_encode($data['segmentos']) : null,
                'updated_at' => now(),
            ]);
    }

    public static function eliminar($id, $psicologoId)
    {
        return DB::table('historia_plantillas_secciones')
            ->where('id', $id)
            ->where('psicologo_id', $psicologoId)
            ->update(['status' => 0]);
    }

    public static function estaEnUso($id, $psicologoId)
    {
        $plantilla = self::obtenerPorId($id, $psicologoId);
        if (!$plantilla) return false;

        $titulosUso = DB::table('historia_secciones_personalizadas as hs')
            ->join('historia_clinicas as hc', 'hs.historia_clinica_id', '=', 'hc.id')
            ->where('hc.psicologo_id', $psicologoId)
            ->where('hs.titulo', $plantilla->titulo)
            ->exists();

        return $titulosUso;
    }
}
