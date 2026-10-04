<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Rol extends Model
{
    use SoftDeletes;
    protected $table = 'rol';
    protected $primaryKey = 'id_rol';
    public $timestamps = true;

    protected $fillable = [
        'nombre',
        'descripcion',
        'slug',
        'menu_permissions',
    ];

    protected $casts = [
        'menu_permissions' => 'array',
    ];

    public function usuarios()
    {
        return $this->belongsToMany(Usuario::class, 'rol_usuario', 'id_rol', 'id_usuario');
    }

    public function modulos()
    {
        return $this->belongsToMany(\App\Models\Modulo::class, 'rol_modulo', 'rol_id', 'modulo_id');
    }

    public static function tieneUsuarios($id)
    {
        return DB::table('rol_usuario')
            ->where('id_rol', $id)
            ->exists();
    }

    public static function contarUsuarios($id)
    {
        return DB::table('rol_usuario')
            ->where('id_rol', $id)
            ->count();
    }
}
