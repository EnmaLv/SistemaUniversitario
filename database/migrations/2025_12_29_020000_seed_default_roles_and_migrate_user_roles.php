<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('rol')) {
            return;
        }

        $defaults = [
            ['nombre_rol' => 'Administrador', 'descripcion_rol' => 'Rol con todos los permisos'],
            ['nombre_rol' => 'Secretaria De Bienestar', 'descripcion_rol' => 'Rol administrativo'],
            ['nombre_rol' => 'Obrero', 'descripcion_rol' => 'Rol operativo'],
            ['nombre_rol' => 'Doctor(a)', 'descripcion_rol' => 'Rol operativo'],
            ['nombre_rol' => 'Enfermero(a)', 'descripcion_rol' => 'Rol operativo'],
            ['nombre_rol' => 'Administrador de Beca', 'descripcion_rol' => 'Rol operativo'],
            ['nombre_rol' => 'Paciente', 'descripcion_rol' => 'Rol estudiantil'],
            ['nombre_rol' => 'Administrador de Salud', 'descripcion_rol' => 'Rol operativo']
        ];

        foreach ($defaults as $d) {
            $exists = DB::table('rol')->where('nombre_rol', $d['nombre_rol'])->exists();
            if (! $exists) {
                $slug = strtolower(str_replace(' ', '-', $d['nombre_rol']));
                DB::table('rol')->insert(array_merge($d, ['slug_rol' => $slug, 'created_at' => now(), 'updated_at' => now()]));
            }
        }
    }

    public function down(): void {}
};
