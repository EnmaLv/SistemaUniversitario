<?php

namespace App\Models\salud;

use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class SegmentoPersonalizado extends Model
{
    use RegistraAutoria;
    use SoftDeletes;

    protected $table = 'historia_segmento_personalizado';

    protected $primaryKey = 'id_hsm';

    protected $fillable = [
        'hsp_id',
        'titulo_hsm',
        'subtitulo_hsm',
        'icono_hsm',
        'contenido_hsm',
        'orden_hsm',
        'status_id',
    ];

    public function seccion()
    {
        return $this->belongsTo(SeccionPersonalizada::class, 'hsp_id', 'id_hsp');
    }

    public static function obtenerSeccion($seccionId)
    {
        return DB::table('historia_seccion_personalizada')->where('id_hsp', $seccionId)->first();
    }

    public static function actualizarSegmentosExtra($segmentosExtra)
    {
        if (empty($segmentosExtra) || !is_array($segmentosExtra)) return;

        try {
            DB::beginTransaction();
            foreach ($segmentosExtra as $segmentoId => $contenido) {
                if ($contenido !== null) {
                    $encrypted = Crypt::encryptString($contenido);
                    DB::table('historia_segmento_personalizado')
                        ->where('id_hsm', $segmentoId)
                        ->update(['contenido_hsm' => $encrypted, 'updated_id' => Auth::id(), 'updated_at' => now()]);
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function actualizarMetadata($metadata)
    {
        if (empty($metadata) || !is_array($metadata)) return;

        try {
            DB::beginTransaction();
            foreach ($metadata as $segmentoId => $meta) {
                DB::table('historia_segmento_personalizado')
                    ->where('id_hsm', $segmentoId)
                    ->update(['titulo_hsm' => $meta['titulo'] ?? null, 'updated_id' => Auth::id(), 'updated_at' => now()]);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
