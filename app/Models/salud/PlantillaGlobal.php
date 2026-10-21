<?php

namespace App\Models\salud;

use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PlantillaGlobal extends Model
{
    use RegistraAutoria;
    use SoftDeletes;

    public const ESTADO_ACTIVA = 1;

    public const ESTADO_PREDETERMINADA = 2;

    protected $table = 'plantilla_historia_clinica';

    protected $primaryKey = 'id_phc';

    protected $fillable = [
        'psicologo_use_id',
        'titulo_phc',
        'descripcion_phc',
        'secciones_phc',
        'estado_phc',
        'status_id',
    ];

    protected $casts = [
        'secciones_phc' => 'array',
        'estado_phc' => 'integer',
    ];

    public static function obtenerPorPsicologo($psicologoId)
    {
        $plantilla = DB::table('plantilla_historia_clinica')
            ->where('psicologo_use_id', $psicologoId)
            ->whereIn('estado_phc', [self::ESTADO_ACTIVA, self::ESTADO_PREDETERMINADA])
            ->whereNull('deleted_at')
            ->first();

        if ($plantilla) {
            $plantilla->secciones_decoded = json_decode($plantilla->secciones_phc, true) ?? [];
        }

        return $plantilla;
    }

    public static function obtenerPorId($id, $psicologoId)
    {
        $plantilla = DB::table('plantilla_historia_clinica')
            ->where('id_phc', $id)
            ->where('psicologo_use_id', $psicologoId)
            ->whereNull('deleted_at')
            ->first();

        if ($plantilla) {
            $plantilla->secciones_decoded = json_decode($plantilla->secciones_phc, true) ?? [];
        }

        return $plantilla;
    }

    public static function actualizar($psicologoId, $data)
    {
        return DB::table('plantilla_historia_clinica')
            ->where('psicologo_use_id', $psicologoId)
            ->whereNull('deleted_at')
            ->update([
                'titulo_phc' => $data['titulo'],
                'descripcion_phc' => $data['descripcion'] ?? null,
                'secciones_phc' => json_encode($data['secciones_estructura']),
                'estado_phc' => self::ESTADO_ACTIVA,
                'updated_id' => Auth::id(),
                'updated_at' => now(),
            ]);
    }

    public static function aplicarATodos($psicologoId)
    {
        $plantilla = self::obtenerPorPsicologo($psicologoId);
        if (!$plantilla || (int) $plantilla->estado_phc !== self::ESTADO_ACTIVA) {
            return ['success' => false, 'message' => 'Plantilla no encontrada o no está activa.'];
        }

        $secciones = $plantilla->secciones_decoded;
        if (empty($secciones)) {
            return ['success' => false, 'message' => 'La plantilla no tiene secciones definidas.'];
        }

        $pacientesIds = DB::table('cita_psicologica')
            ->where('psicologo_use_id', $psicologoId)
            ->whereIn('estado_cita_cps', ['realizada', 'confirmada'])
            ->whereNull('deleted_at')
            ->pluck('paciente_use_id')
            ->unique()
            ->values();

        if ($pacientesIds->isEmpty()) {
            return ['success' => false, 'message' => 'No tienes pacientes registrados.'];
        }

        try {
            DB::beginTransaction();

            $pacientesAfectados = 0;
            $usuarioId = Auth::id();

            foreach ($pacientesIds as $pacienteId) {
                $historia = DB::table('historia_clinica')
                    ->where('paciente_use_id', $pacienteId)
                    ->where('psicologo_use_id', $psicologoId)
                    ->first();

                if (!$historia) {
                    $historiaId = DB::table('historia_clinica')->insertGetId([
                        'paciente_use_id' => $pacienteId,
                        'psicologo_use_id' => $psicologoId,
                        'created_id' => $usuarioId,
                        'created_at' => now(),
                        'updated_at' => null,
                    ], 'id_hcl');
                } else {
                    $historiaId = $historia->id_hcl;
                }

                $seccionesPaciente = DB::table('historia_seccion_personalizada')
                    ->where('hcl_id', $historiaId)
                    ->get();

                $titulosPlantilla = collect($secciones)->pluck('titulo')->toArray();
                $titulosPaciente = $seccionesPaciente->pluck('titulo_hsp')->toArray();

                foreach ($seccionesPaciente as $seccionPac) {
                    if (!in_array($seccionPac->titulo_hsp, $titulosPlantilla)) {
                        $segmentosLlenos = DB::table('historia_segmento_personalizado')
                            ->where('hsp_id', $seccionPac->id_hsp)
                            ->whereNotNull('contenido_hsm')
                            ->where('contenido_hsm', '!=', '')
                            ->exists();

                        if (!$segmentosLlenos) {
                            DB::table('historia_segmento_personalizado')->where('hsp_id', $seccionPac->id_hsp)->delete();
                            DB::table('historia_seccion_personalizada')->where('id_hsp', $seccionPac->id_hsp)->delete();
                        }
                    }
                }

                $ordenActual = 1;

                foreach ($secciones as $seccionData) {
                    if (!in_array($seccionData['titulo'], $titulosPaciente)) {
                        $seccionId = DB::table('historia_seccion_personalizada')->insertGetId([
                            'hcl_id' => $historiaId,
                            'titulo_hsp' => $seccionData['titulo'],
                            'descripcion_general_hsp' => $seccionData['descripcion_general'] ?? null,
                            'orden_hsp' => $ordenActual,
                            'created_id' => $usuarioId,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ], 'id_hsp');

                        $segmentos = $seccionData['segmentos'] ?? [];
                        foreach ($segmentos as $indexSeg => $segmentoTitulo) {
                            if (!empty(trim($segmentoTitulo))) {
                                DB::table('historia_segmento_personalizado')->insert([
                                    'hsp_id' => $seccionId,
                                    'titulo_hsm' => $segmentoTitulo,
                                    'contenido_hsm' => null,
                                    'orden_hsm' => $indexSeg + 1,
                                    'created_id' => $usuarioId,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]);
                            }
                        }
                    } else {
                        DB::table('historia_seccion_personalizada')
                            ->where('hcl_id', $historiaId)
                            ->where('titulo_hsp', $seccionData['titulo'])
                            ->update(['orden_hsp' => $ordenActual, 'updated_id' => $usuarioId]);
                    }
                    $ordenActual++;
                }

                foreach ($seccionesPaciente as $seccionPac) {
                    if (!in_array($seccionPac->titulo_hsp, $titulosPlantilla)) {
                        $existe = DB::table('historia_seccion_personalizada')->where('id_hsp', $seccionPac->id_hsp)->exists();
                        if ($existe) {
                            DB::table('historia_seccion_personalizada')
                                ->where('id_hsp', $seccionPac->id_hsp)
                                ->update(['orden_hsp' => $ordenActual, 'updated_id' => $usuarioId]);
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
        return DB::table('plantilla_historia_clinica')
            ->where('psicologo_use_id', $psicologoId)
            ->where('estado_phc', self::ESTADO_ACTIVA)
            ->whereNull('deleted_at')
            ->exists();
    }
}
