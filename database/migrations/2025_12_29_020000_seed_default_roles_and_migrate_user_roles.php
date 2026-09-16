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
            ['nombre' => 'Administrador', 'descripcion' => 'Rol con todos los permisos'],
            ['nombre' => 'Secretaria De Bienestar', 'descripcion' => 'Rol administrativo'],
            ['nombre' => 'Obrero', 'descripcion' => 'Rol operativo'],
            ['nombre' => 'Doctor(a)', 'descripcion' => 'Rol operativo'],
            ['nombre' => 'Enfermero(a)', 'descripcion' => 'Rol operativo'],
            ['nombre' => 'Administrador de Beca', 'descripcion' => 'Rol operativo'],
            ['nombre' => 'Paciente', 'descripcion' => 'Rol estudiantil'],
            ['nombre' => 'Administrador de Salud', 'descripcion' => 'Rol operativo']
        ];

        foreach ($defaults as $d) {
            $exists = DB::table('rol')->where('nombre', $d['nombre'])->exists();
            if (! $exists) {
                $slug = strtolower(str_replace(' ', '-', $d['nombre']));
                DB::table('rol')->insert(array_merge($d, ['slug' => $slug, 'created_at' => now(), 'updated_at' => now()]));
            }
        }
    }

    public function down(): void {}
};
