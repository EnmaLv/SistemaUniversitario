<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolModuloSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('rol_modulo')->insert([
            ['rol_id' => 3, 'mod_id' => 2],
            ['rol_id' => 4, 'mod_id' => 1],
            ['rol_id' => 5, 'mod_id' => 1],
            ['rol_id' => 6, 'mod_id' => 4],
            ['rol_id' => 7, 'mod_id' => 6],
            ['rol_id' => 8, 'mod_id' => 1],
            ['rol_id' => 9, 'mod_id' => 4],
        ]);

        $rolPsicologo = DB::table('rol')->where('slug_rol', 'psicologo')->value('id_rol');
        $moduloPsicologia = DB::table('modulo')->where('key_mod', 'psicologia')->value('id_mod');

        if ($rolPsicologo && $moduloPsicologia) {
            DB::table('rol_modulo')->insert(['rol_id' => $rolPsicologo, 'mod_id' => $moduloPsicologia]);
        }
    }
}
