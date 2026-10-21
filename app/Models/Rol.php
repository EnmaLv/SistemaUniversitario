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
    ];

    public function usuarios()
    {
        return $this->belongsToMany(Usuario::class, 'rol_usuario', 'id_rol', 'id_usuario');
    }

    public function modulos()
    {
        return $this->belongsToMany(\App\Models\Modulo::class, 'rol_modulo', 'rol_id', 'modulo_id');
    }

    public function permisos()
    {
        return $this->belongsToMany(Permiso::class, 'rol_permiso', 'rol_id', 'permiso_id')->withTimestamps();
    }

    public function esSuperAdmin(): bool
    {
        $rolesSuperadmin = config('permisos.roles_superadmin', []);
        $superAdmins = array_map('mb_strtolower', is_array($rolesSuperadmin) ? $rolesSuperadmin : []);

        return in_array(mb_strtolower($this->nombre ?? ''), $superAdmins, true)
            || in_array(mb_strtolower($this->slug ?? ''), $superAdmins, true);
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
