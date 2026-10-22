<?php

namespace App\Models\salud;

use Illuminate\Database\Eloquent\Model;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;

class Publicacion extends Model
{
    protected $table = 'publicaciones';

    protected $fillable = [
        'psicologo_id',
        'titulo',
        'contenido',
        'alcance',
        'tipo',
        'color_fondo',
        'media_path',
        'estatus',
    ];

    protected $attributes = [
        'contenido' => '',
        'alcance' => 'todos',
        'tipo' => 'texto',
        'estatus' => 1,
    ];

    public function psicologo()
    {
        return $this->belongsTo(Usuario::class, 'psicologo_id', 'id_use');
    }

    public function scopeActivas($query)
    {
        return $query->where('estatus', 1);
    }

    public static function byPsicologo($psicologoId)
    {
        return self::activas()
            ->where('psicologo_id', $psicologoId)
            ->latest()
            ->take(14)
            ->get();
    }

    public static function findById($id)
    {
        return self::activas()
            ->where('id', $id)
            ->first();
    }

    public static function forPacientes()
    {
        return self::activas()
            ->join('user', 'publicaciones.psicologo_id', '=', 'user.id_use')
            ->select(
                'publicaciones.*',
            )
            ->latest('publicaciones.created_at')
            ->take(14)
            ->get();
    }

    public static function desactivar($id)
    {
        return self::where('id', $id)->update(['estatus' => 0]);
    }
}
