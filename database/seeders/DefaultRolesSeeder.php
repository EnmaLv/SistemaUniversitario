<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rol;

class DefaultRolesSeeder extends Seeder
{
    public function run()
    {
        Rol::updateOrCreate(
            ['nombre' => 'Administrador'],
            ['descripcion' => 'Rol por defecto Administrador']
        );

        Rol::updateOrCreate(
            ['nombre' => 'Secretaria De Bienestar'],
            ['descripcion' => 'Rol por defecto Secretaria De Bienestar']
        );

        Rol::updateOrCreate(
            ['nombre' => 'Obrero'],
            ['descripcion' => 'Rol por defecto Obrero']
        );
    }
}