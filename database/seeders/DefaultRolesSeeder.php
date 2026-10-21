<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rol;

class DefaultRolesSeeder extends Seeder
{
    public function run()
    {
        Rol::updateOrCreate(
            ['nombre_rol' => 'Administrador'],
            ['descripcion_rol' => 'Rol por defecto Administrador']
        );

        Rol::updateOrCreate(
            ['nombre_rol' => 'Secretaria De Bienestar'],
            ['descripcion_rol' => 'Rol por defecto Secretaria De Bienestar']
        );

        Rol::updateOrCreate(
            ['nombre_rol' => 'Obrero'],
            ['descripcion_rol' => 'Rol por defecto Obrero']
        );

        Rol::updateOrCreate(
            ['nombre_rol' => 'Psicólogo'],
            ['descripcion_rol' => 'Rol por defecto Psicólogo', 'slug_rol' => 'psicologo']
        );
    }
}