<?php

namespace App\Models;

use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Model;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Rol extends Model
{
    use RegistraAutoria;
    use SoftDeletes;

    protected $table = 'rol';
    protected $primaryKey = 'id_rol';
    public $timestamps = true;

    protected $fillable = [
        'nombre_rol',
        'descripcion_rol',
        'slug_rol',
        'dep_id',
        'status_id',
    ];

    public function usuarios()
    {
        return $this->belongsToMany(Usuario::class, 'usuario_rol', 'rol_id', 'use_id');
    }

    public function modulos()
    {
        return $this->belongsToMany(\App\Models\Modulo::class, 'rol_modulo', 'rol_id', 'mod_id');
    }

    public function permisos()
    {
        return $this->belongsToMany(Permiso::class, 'rol_permiso', 'rol_id', 'pmi_id')->withTimestamps();
    }

    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'dep_id', 'id_dep');
    }

    public function esSuperAdmin(): bool
    {
        $rolesSuperadmin = config('permisos.roles_superadmin', []);
        $superAdmins = array_map('mb_strtolower', is_array($rolesSuperadmin) ? $rolesSuperadmin : []);

        return in_array(mb_strtolower($this->nombre_rol ?? ''), $superAdmins, true)
            || in_array(mb_strtolower($this->slug_rol ?? ''), $superAdmins, true);
    }

    public static function tieneUsuarios($id)
    {
        return DB::table('usuario_rol')
            ->where('rol_id', $id)
            ->exists();
    }

    public static function contarUsuarios($id)
    {
        return DB::table('usuario_rol')
            ->where('rol_id', $id)
            ->count();
    }
}
