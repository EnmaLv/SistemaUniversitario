<?php

namespace App\Models\salud;

use Illuminate\Support\Facades\DB;

class PlantillaGlobal
{
    public static function obtenerPorPsicologo($psicologoId)
    {
        $plantilla = DB::table('historia_plantillas_globales')
            ->where('psicologo_id', $psicologoId)
            ->whereIn('status', [1, 2])
            ->first();

        if ($plantilla) {
            $plantilla->secciones_decoded = json_decode($plantilla->secciones, true) ?? [];
        }

        return $plantilla;
    }

    public static function obtenerPorId($id, $psicologoId)
    {
        $plantilla = DB::table('historia_plantillas_globales')
            ->where('id', $id)
            ->where('psicologo_id', $psicologoId)
            ->first();

        if ($plantilla) {
            $plantilla->secciones_decoded = json_decode($plantilla->secciones, true) ?? [];
        }

        return $plantilla;
    }

    public static function actualizar($psicologoId, $data)
    {
        return DB::table('historia_plantillas_globales')
            ->where('psicologo_id', $psicologoId)
            ->update([
                'titulo' => $data['titulo'],
                'descripcion' => $data['descripcion'] ?? null,
                'secciones' => json_encode($data['secciones_estructura']),
                'status' => 1,
                'updated_at' => now(),
            ]);
    }

    public static function aplicarATodos($psicologoId)
    {
        $plantilla = self::obtenerPorPsicologo($psicologoId);
        if (!$plantilla || $plantilla->status != 1) {
            return ['success' => false, 'message' => 'Plantilla no encontrada o no está activa.'];
        }

        $secciones = $plantilla->secciones_decoded;
        if (empty($secciones)) {
            return ['success' => false, 'message' => 'La plantilla no tiene secciones definidas.'];
        }

        $pacientesIds = DB::table('citas')
            ->where('psicologo_id', $psicologoId)
            ->whereIn('estado', ['realizada', 'confirmada'])
            ->pluck('user_id')
            ->unique()
            ->values();

        if ($pacientesIds->isEmpty()) {
            return ['success' => false, 'message' => 'No tienes pacientes registrados.'];
        }

        try {
            DB::beginTransaction();

            $pacientesAfectados = 0;

            foreach ($pacientesIds as $pacienteId) {
                $historia = DB::table('historia_clinicas')
                    ->where('user_id', $pacienteId)
                    ->where('psicologo_id', $psicologoId)
                    ->first();

                if (!$historia) {
                    $historiaId = DB::table('historia_clinicas')->insertGetId([
                        'user_id' => $pacienteId,
                        'psicologo_id' => $psicologoId,
                        'created_at' => now(),
                        'updated_at' => null,
                    ]);
                } else {
                    $historiaId = $historia->id;
                }

                $seccionesPaciente = DB::table('historia_secciones_personalizadas')
                    ->where('historia_clinica_id', $historiaId)
                    ->get();
                
                $titulosPlantilla = collect($secciones)->pluck('titulo')->toArray();
                $titulosPaciente = $seccionesPaciente->pluck('titulo')->toArray();

                foreach ($seccionesPaciente as $seccionPac) {
                    if (!in_array($seccionPac->titulo, $titulosPlantilla)) {
                        $segmentosLlenos = DB::table('historia_segmentos_personalizados')
                            ->where('seccion_id', $seccionPac->id)
                            ->whereNotNull('contenido')
                            ->where('contenido', '!=', '')
                            ->exists();
                        
                        if (!$segmentosLlenos) {
                            DB::table('historia_segmentos_personalizados')->where('seccion_id', $seccionPac->id)->delete();
                            DB::table('historia_secciones_personalizadas')->where('id', $seccionPac->id)->delete();
                        }
                    }
                }

                $ordenActual = 1;

                foreach ($secciones as $seccionData) {
                    if (!in_array($seccionData['titulo'], $titulosPaciente)) {
                        $seccionId = DB::table('historia_secciones_personalizadas')->insertGetId([
                            'historia_clinica_id' => $historiaId,
                            'titulo' => $seccionData['titulo'],
                            'descripcion_general' => $seccionData['descripcion_general'] ?? null,
                            'orden' => $ordenActual,
                            'status' => 1,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        $segmentos = $seccionData['segmentos'] ?? [];
                        foreach ($segmentos as $indexSeg => $segmentoTitulo) {
                            if (!empty(trim($segmentoTitulo))) {
                                DB::table('historia_segmentos_personalizados')->insert([
                                    'seccion_id' => $seccionId,
                                    'titulo' => $segmentoTitulo,
                                    'contenido' => null,
                                    'orden' => $indexSeg + 1,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]);
                            }
                        }
                    } else {
                        DB::table('historia_secciones_personalizadas')
                            ->where('historia_clinica_id', $historiaId)
                            ->where('titulo', $seccionData['titulo'])
                            ->update(['orden' => $ordenActual]);
                    }
                    $ordenActual++;
                }

                foreach ($seccionesPaciente as $seccionPac) {
                    if (!in_array($seccionPac->titulo, $titulosPlantilla)) {
                        $existe = DB::table('historia_secciones_personalizadas')->where('id', $seccionPac->id)->exists();
                        if ($existe) {
                            DB::table('historia_secciones_personalizadas')
                                ->where('id', $seccionPac->id)
                                ->update(['orden' => $ordenActual]);
                            $ordenActual++;
                        }
                    }
                }

                $pacientesAfectados++;
            }

            DB::commit();

            return [
                'success' => true,
                'message' => "Plantilla aplicada exitosamente a {$pacientesAfectados} paciente(s).",
                'pacientes_afectados' => $pacientesAfectados,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => false, 'message' => 'Error al aplicar la plantilla: ' . \App\Support\Seguridad\MensajeError::de($e)];
        }
    }

    public static function tienePlantillaGlobal($psicologoId)
    {
        return DB::table('historia_plantillas_globales')
            ->where('psicologo_id', $psicologoId)
            ->where('status', 1)
            ->exists();
    }
}
