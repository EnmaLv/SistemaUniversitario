<?php

namespace App\Models\salud;

use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SeccionPersonalizada extends Model
{
    use RegistraAutoria;
    use SoftDeletes;

    protected $table = 'historia_seccion_personalizada';

    protected $primaryKey = 'id_hsp';

    protected $fillable = [
        'hcl_id',
        'titulo_hsp',
        'descripcion_general_hsp',
        'orden_hsp',
        'status_id',
    ];

    public function historia()
    {
        return $this->belongsTo(HistoriaClinica::class, 'hcl_id', 'id_hcl');
    }

    public function segmentos()
    {
        return $this->hasMany(SegmentoPersonalizado::class, 'hsp_id', 'id_hsp');
    }

    public static function obtenerPorId($id)
    {
        return DB::table('historia_seccion_personalizada')->where('id_hsp', $id)->first();
    }

    public static function obtenerHistoriaClinica($historiaClinicaId)
    {
        return DB::table('historia_clinica')->where('id_hcl', $historiaClinicaId)->first();
    }

    public static function obtenerSegmentos($seccionId)
    {
        return DB::table('historia_segmento_personalizado')
            ->where('hsp_id', $seccionId)
            ->whereNull('deleted_at')
            ->orderBy('orden_hsm')
            ->get();
    }

    public static function crear($historia, $data)
    {
        try {
            DB::beginTransaction();

            $historiaId = is_object($historia) ? $historia->id_hcl : $historia;
            $psicologoId = Auth::id();

            $seccionId = DB::table('historia_seccion_personalizada')->insertGetId([
                'hcl_id'                  => $historiaId,
                'titulo_hsp'              => $data['titulo'],
                'descripcion_general_hsp' => $data['descripcion_general'] ?? null,
                'orden_hsp'               => DB::table('historia_seccion_personalizada')->where('hcl_id', $historiaId)->count() + 1,
                'created_id'              => $psicologoId,
                'created_at'              => now(),
                'updated_at'              => null,
            ], 'id_hsp');

            if (!empty($data['segmentos_titulos'])) {
                foreach ($data['segmentos_titulos'] as $index => $titulo) {
                    DB::table('historia_segmento_personalizado')->insert([
                        'hsp_id' => $seccionId,
                        'titulo_hsm' => $titulo,
                        'orden_hsm' => $index + 1,
                        'created_id' => $psicologoId,
                        'created_at' => now(),
                        'updated_at' => null,
                    ]);
                }
            }

            $plantilla = DB::table('seccion_plantilla_historia')
                ->where('psicologo_use_id', $psicologoId)
                ->where('titulo_sph', $data['titulo'])
                ->first();

            if ($plantilla) {
                DB::table('seccion_plantilla_historia')->where('id_sph', $plantilla->id_sph)
                    ->update(['updated_id' => $psicologoId, 'updated_at' => now()]);
            } else {
                DB::table('seccion_plantilla_historia')->insert([
                    'psicologo_use_id' => $psicologoId,
                    'titulo_sph' => $data['titulo'],
                    'created_id' => $psicologoId,
                    'created_at' => now(),
                    'updated_at' => null,
                ]);
            }

            $seccion = DB::table('historia_seccion_personalizada')->where('id_hsp', $seccionId)->first();
            DB::commit();
            return $seccion;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function eliminar($id)
    {
        try {
            DB::beginTransaction();
            $res = DB::table('historia_seccion_personalizada')
                ->where('id_hsp', $id)
                ->update([
                    'deleted_at' => now(),
                    'updated_id' => Auth::id(),
                    'updated_at' => now(),
                ]);
            DB::commit();
            return $res;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function reordenar($seccionId, $direccion)
    {
        try {
            DB::beginTransaction();

            $seccion = self::obtenerPorId($seccionId);
            if (!$seccion || $seccion->deleted_at !== null) {
                DB::rollBack();
                return false;
            }

            $historiaId = $seccion->hcl_id;
            $ordenActual = $seccion->orden_hsp;

            if ($direccion === 'up') {
                $otraSeccion = DB::table('historia_seccion_personalizada')
                    ->where('hcl_id', $historiaId)
                    ->whereNull('deleted_at')
                    ->where('orden_hsp', '<', $ordenActual)
                    ->orderBy('orden_hsp', 'desc')
                    ->first();
            } else {
                $otraSeccion = DB::table('historia_seccion_personalizada')
                    ->where('hcl_id', $historiaId)
                    ->whereNull('deleted_at')
                    ->where('orden_hsp', '>', $ordenActual)
                    ->orderBy('orden_hsp', 'asc')
                    ->first();
            }

            if ($otraSeccion) {
                DB::table('historia_seccion_personalizada')
                    ->where('id_hsp', $seccion->id_hsp)
                    ->update(['orden_hsp' => $otraSeccion->orden_hsp, 'updated_id' => Auth::id(), 'updated_at' => now()]);

                DB::table('historia_seccion_personalizada')
                    ->where('id_hsp', $otraSeccion->id_hsp)
                    ->update(['orden_hsp' => $ordenActual, 'updated_id' => Auth::id(), 'updated_at' => now()]);
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
