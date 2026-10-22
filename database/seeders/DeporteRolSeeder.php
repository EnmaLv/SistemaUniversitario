<?php

namespace Database\Seeders;

use App\Models\Rol;
use App\Support\Permisos\VersionPermisos;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DeporteRolSeeder extends Seeder
{
    public const SLUG = 'coordinador-deportes';

    public function run(): void
    {
        $rol = Rol::withTrashed()->where('slug_rol', self::SLUG)->first()
            ?? Rol::withTrashed()->where('nombre_rol', 'Coordinador de Deportes')->first();

        if (! $rol) {
            $rol = Rol::create([
                'nombre_rol'      => 'Coordinador de Deportes',
                'slug_rol'        => self::SLUG,
                'descripcion_rol' => 'Gestiona espacios, actividades, préstamos de implementos, sanciones, solvencias y entregas de material del módulo de deportes',
                'status_id'       => 1,
            ]);
        } elseif ($rol->trashed()) {
            $rol->restore();
        }

        if ($rol->slug_rol !== self::SLUG) {
            $rol->slug_rol = self::SLUG;
            $rol->save();
        }

        $modulo = DB::table('modulo')->where('key_mod', 'deporte')->value('id_mod');

        if ($modulo && ! DB::table('rol_modulo')->where('rol_id', $rol->id_rol)->where('mod_id', $modulo)->exists()) {
            DB::table('rol_modulo')->insert([
                'rol_id'     => $rol->id_rol,
                'mod_id'     => $modulo,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        VersionPermisos::invalidar();
    }
}
