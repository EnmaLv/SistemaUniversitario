<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PersonaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('persona')->insert([
            'nombre_persona' => 'Administrador',
            'segundo_nombre_persona' => null,
            'apellido_persona' => 'General',
            'segundo_apellido_persona' => null,
            'cedula_persona' => '12345678',
            'telefono_persona' => 04241234567,
            'genero_persona' => 'Masculino',
            'edad_persona' => \Carbon\Carbon::parse(now()->toDateString())->age,
            'fecha_nacimiento_persona' => now()->toDateString(),
            'email_persona' => 'admin@example.com',
            'semestre_persona' => null,
            'id_perfil' => 1,
            'id_sede' => 1,
        ]);

        DB::table('usuario')->insert([
            'id_persona' => 1,
            'id_perfil' => 1,
            'username' => 'admin@example.com',
            'password' => bcrypt('12345678'),
            'master_key' => bcrypt('masterkey123'),
            'security_questions' => json_encode([
                [
                    'question' => '¿Cuál es el nombre de tu primera mascota?',
                    'answer' => bcrypt('example'),
                ],
                [
                    'question' => '¿Cuál es el nombre de tu madre?',
                    'answer' => bcrypt('example'),
                ],
            ]),

            'extra_permissions' => null,
        ]);

        DB::table('rol_usuario')->insert([
            'id_rol' => 1,
            'id_usuario' => 1,
        ]);

        //Admin de salud
        DB::table('persona')->insert([
            'nombre_persona' => 'Nohely',
            'segundo_nombre_persona' => null,
            'apellido_persona' => 'Sosa',
            'segundo_apellido_persona' => null,
            'cedula_persona' => '31710990',
            'telefono_persona' => 04241234567,
            'genero_persona' => 'Femenino',
            'edad_persona' => \Carbon\Carbon::parse(now()->toDateString())->age,
            'fecha_nacimiento_persona' => now()->toDateString(),
            'email_persona' => 'nohelysq2006@gmail.com',
            'semestre_persona' => null,
            'id_perfil' => 1,
            'id_sede' => 1,
        ]);

        DB::table('usuario')->insert([
            'id_persona' => 2,
            'id_perfil' => 1,
            'username' => 'nohelysq2006@gmail.com',
            'password' => bcrypt('12345678'),
            'master_key' => bcrypt('masterkey123'),
            'security_questions' => null,
            'extra_permissions' => null,
        ]);

        DB::table('rol_usuario')->insert([
            'id_rol' => 8,
            'id_usuario' => 2,
        ]);

        DB::table('persona')->insert([
            'nombre_persona' => 'America',
            'segundo_nombre_persona' => null,
            'apellido_persona' => 'IDK',
            'segundo_apellido_persona' => null,
            'cedula_persona' => '11223344',
            'telefono_persona' => 04241234567,
            'genero_persona' => 'Femenino',
            'edad_persona' => \Carbon\Carbon::parse(now()->toDateString())->age,
            'fecha_nacimiento_persona' => now()->toDateString(),
            'email_persona' => 'america@example.com',
            'semestre_persona' => 6,
            'id_perfil' => 2,
            'id_sede' => 1,
        ]);

        DB::table('usuario')->insert([
            'id_persona' => 3,
            'id_perfil' => 1,
            'username' => 'america@example.com',
            'password' => bcrypt('12345678'),
            'master_key' => bcrypt('masterkey123'),
            'security_questions' => null,
            'extra_permissions' => null,
        ]);

        DB::table('rol_usuario')->insert([
            'id_rol' => 7,
            'id_usuario' => 3,
        ]);

        //Doctor
        DB::table('persona')->insert([
            'nombre_persona' => 'Argenis',
            'segundo_nombre_persona' => null,
            'apellido_persona' => 'Rivero',
            'segundo_apellido_persona' => null,
            'cedula_persona' => '31008661',
            'telefono_persona' => 04241234567,
            'genero_persona' => 'Masculino',
            'edad_persona' => \Carbon\Carbon::parse(now()->toDateString())->age,
            'fecha_nacimiento_persona' => now()->toDateString(),
            'email_persona' => 'argenisdoctor@gmail.com',
            'semestre_persona' => null,
            'id_perfil' => 2,
            'id_sede' => 1,
        ]);

        DB::table('usuario')->insert([
            'id_persona' => 4,
            'id_perfil' => 1,
            'username' => 'argenisdoctor@gmail.com',
            'password' => bcrypt('12345678'),
            'master_key' => bcrypt('masterkey123'),
            'security_questions' => null,
            'extra_permissions' => null,
        ]);

        DB::table('rol_usuario')->insert([
            'id_rol' => 4,
            'id_usuario' => 4,
        ]);

        //Doctora
        DB::table('persona')->insert([
            'nombre_persona' => 'Genesis',
            'segundo_nombre_persona' => null,
            'apellido_persona' => 'Escalona',
            'segundo_apellido_persona' => null,
            'cedula_persona' => '31008662',
            'telefono_persona' => 04241234567,
            'genero_persona' => 'Femenino',
            'edad_persona' => \Carbon\Carbon::parse(now()->toDateString())->age,
            'fecha_nacimiento_persona' => now()->toDateString(),
            'email_persona' => 'genesisdoctor@gmail.com',
            'semestre_persona' => null,
            'id_perfil' => 2,
            'id_sede' => 1,
        ]);

        DB::table('usuario')->insert([
            'id_persona' => 5,
            'id_perfil' => 1,
            'username' => 'genesisdoctor@gmail.com',
            'password' => bcrypt('12345678'),
            'master_key' => bcrypt('masterkey123'),
            'security_questions' => null,
            'extra_permissions' => null,
        ]);

        DB::table('rol_usuario')->insert([
            'id_rol' => 4,
            'id_usuario' => 5,
        ]);

        //Enfermero
        DB::table('persona')->insert([
            'nombre_persona' => 'Denis',
            'segundo_nombre_persona' => null,
            'apellido_persona' => 'Gil',
            'segundo_apellido_persona' => null,
            'cedula_persona' => '31710991',
            'telefono_persona' => 04241234567,
            'genero_persona' => 'Femenino',
            'edad_persona' => \Carbon\Carbon::parse(now()->toDateString())->age,
            'fecha_nacimiento_persona' => now()->toDateString(),
            'email_persona' => 'denisenfermero@gmail.com',
            'semestre_persona' => null,
            'id_perfil' => 2,
            'id_sede' => 1,
        ]);

        DB::table('usuario')->insert([
            'id_persona' => 6,
            'id_perfil' => 1,
            'username' => 'denisenfermero@gmail.com',
            'password' => bcrypt('12345678'),
            'master_key' => bcrypt('masterkey123'),
            'security_questions' => null,
            'extra_permissions' => null,
        ]);

        DB::table('rol_usuario')->insert([
            'id_rol' => 5,
            'id_usuario' => 6,
        ]);

        //Enfermero
        DB::table('persona')->insert([
            'nombre_persona' => 'Carmen',
            'segundo_nombre_persona' => null,
            'apellido_persona' => 'Pinero',
            'segundo_apellido_persona' => null,
            'cedula_persona' => '31008661',
            'telefono_persona' => 04241234567,
            'genero_persona' => 'Femenino',
            'edad_persona' => \Carbon\Carbon::parse(now()->toDateString())->age,
            'fecha_nacimiento_persona' => now()->toDateString(),
            'email_persona' => 'carmenenfermero@gmail.com',
            'semestre_persona' => null,
            'id_perfil' => 2,
            'id_sede' => 1,
        ]);

        DB::table('usuario')->insert([
            'id_persona' => 7,
            'id_perfil' => 1,
            'username' => 'carmenenfermero@gmail.com',
            'password' => bcrypt('12345678'),
            'master_key' => bcrypt('masterkey123'),
            'security_questions' => null,
            'extra_permissions' => null,
        ]);

        DB::table('rol_usuario')->insert([
            'id_rol' => 5,
            'id_usuario' => 7,
        ]);
    }
}
