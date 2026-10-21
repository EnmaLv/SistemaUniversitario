<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const ROLES = [
        'Administrador'           => ['Rol por defecto Administrador', 'administrador'],
        'Secretaria De Bienestar' => ['Rol por defecto Secretaria De Bienestar', 'secretaria-de-bienestar'],
        'Obrero'                  => ['Rol por defecto Obrero', 'obrero'],
        'Administrador de Salud'  => ['Rol por defecto Administrador de Salud', 'administrador-de-salud'],
        'Doctor(a)'               => ['Rol por defecto Doctor de Salud', 'doctor-de-salud'],
        'Enfermero(a)'            => ['Rol por defecto Secretaria de Salud', 'secretaria-de-salud'],
        'Administrador de Beca'   => ['Rol por defecto Administrador de Beca', 'administrador-de-beca'],
        'Paciente'                => ['Rol por defecto paciente', 'paciente'],
        'Becario'                 => ['Rol por defecto becario', 'becario'],
        'Psicólogo'               => ['Rol por defecto Psicólogo', 'psicologo'],
    ];

    public function up()
    {
        if (! Schema::hasTable('rol')) {
            return;
        }

        $hasSlug = Schema::hasColumn('rol', 'slug');

        foreach (self::ROLES as $nombre => [$descripcion, $slug]) {
            $datos = [
                'descripcion' => $descripcion,
                'created_at'  => now(),
                'updated_at'  => now(),
            ];

            if ($hasSlug) {
                $datos['slug'] = $slug;
            }

            DB::table('rol')->updateOrInsert(['nombre' => $nombre], $datos);
        }
    }

    public function down()
    {
        if (Schema::hasTable('rol')) {
            DB::table('rol')->whereIn('nombre', array_keys(self::ROLES))->delete();
        }
    }
};