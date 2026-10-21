<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;  

class PerfilSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        DB::table('perfil')->insert([   
            'nombre_perfil' => 'Usuario',
            'status_id' => 1,
        ]);
        
        DB::table('perfil')->insert([   
            'nombre_perfil' => 'Estudiante',
            'status_id' => 1,
        ]);
    }
}
