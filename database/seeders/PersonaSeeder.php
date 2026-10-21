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
            'nombres_per' => 'Administrador',
            'segundo_nombre_per' => null,
            'apellidos_per' => 'General',
            'segundo_apellido_per' => null,
            'cedula_per' => '12345678',
            'telefono_per' => 04241234567,
            'genero_per' => 'Masculino',
            'fecha_nacimiento_per' => now()->toDateString(),
            'correo_per' => 'admin@example.com',
            'semestre_per' => null,
            'id_perfil' => 1,
            'sed_id' => 1,
        ]);

        DB::table('user')->insert([
            'per_id' => 1,
            'id_perfil' => 1,
            'username_use' => 'admin@example.com',
            'email_use' => 'admin@example.com',
            'password_use' => bcrypt('12345678'),
            'master_key_use' => bcrypt('masterkey123'),
            'security_questions_use' => json_encode([
                [
                    'question' => '¿Cuál es el nombre de tu primera mascota?',
                    'answer' => bcrypt('example'),
                ],
                [
                    'question' => '¿Cuál es el nombre de tu madre?',
                    'answer' => bcrypt('example'),
                ],
            ]),
        ]);

        DB::table('usuario_rol')->insert([
            'rol_id' => 1,
            'use_id' => 1,
        ]);

        //Admin de salud
        DB::table('persona')->insert([
            'nombres_per' => 'Nohely',
            'segundo_nombre_per' => null,
            'apellidos_per' => 'Sosa',
            'segundo_apellido_per' => null,
            'cedula_per' => '31710990',
            'telefono_per' => 04241234567,
            'genero_per' => 'Femenino',
            'fecha_nacimiento_per' => now()->toDateString(),
            'correo_per' => 'nohelysq2006@gmail.com',
            'semestre_per' => null,
            'id_perfil' => 1,
            'sed_id' => 1,
        ]);

        DB::table('user')->insert([
            'per_id' => 2,
            'id_perfil' => 1,
            'username_use' => 'nohelysq2006@gmail.com',
            'email_use' => 'nohelysq2006@gmail.com',
            'password_use' => bcrypt('12345678'),
            'master_key_use' => bcrypt('masterkey123'),
            'security_questions_use' => null,
        ]);

        DB::table('usuario_rol')->insert([
            'rol_id' => 8,
            'use_id' => 2,
        ]);

        DB::table('persona')->insert([
            'nombres_per' => 'America',
            'segundo_nombre_per' => null,
            'apellidos_per' => 'IDK',
            'segundo_apellido_per' => null,
            'cedula_per' => '11223344',
            'telefono_per' => 04241234567,
            'genero_per' => 'Femenino',
            'fecha_nacimiento_per' => now()->toDateString(),
            'correo_per' => 'america@example.com',
            'semestre_per' => 6,
            'id_perfil' => 2,
            'sed_id' => 1,
        ]);

        DB::table('user')->insert([
            'per_id' => 3,
            'id_perfil' => 1,
            'username_use' => 'america@example.com',
            'email_use' => 'america@example.com',
            'password_use' => bcrypt('12345678'),
            'master_key_use' => bcrypt('masterkey123'),
            'security_questions_use' => null,
        ]);

        DB::table('usuario_rol')->insert([
            'rol_id' => 7,
            'use_id' => 3,
        ]);

        //Doctor
        DB::table('persona')->insert([
            'nombres_per' => 'Argenis',
            'segundo_nombre_per' => null,
            'apellidos_per' => 'Rivero',
            'segundo_apellido_per' => null,
            'cedula_per' => '31008661',
            'telefono_per' => 04241234567,
            'genero_per' => 'Masculino',
            'fecha_nacimiento_per' => now()->toDateString(),
            'correo_per' => 'argenisdoctor@gmail.com',
            'semestre_per' => null,
            'id_perfil' => 2,
            'sed_id' => 1,
        ]);

        DB::table('user')->insert([
            'per_id' => 4,
            'id_perfil' => 1,
            'username_use' => 'argenisdoctor@gmail.com',
            'email_use' => 'argenisdoctor@gmail.com',
            'password_use' => bcrypt('12345678'),
            'master_key_use' => bcrypt('masterkey123'),
            'security_questions_use' => null,
        ]);

        DB::table('usuario_rol')->insert([
            'rol_id' => 4,
            'use_id' => 4,
        ]);

        //Doctora
        DB::table('persona')->insert([
            'nombres_per' => 'Genesis',
            'segundo_nombre_per' => null,
            'apellidos_per' => 'Escalona',
            'segundo_apellido_per' => null,
            'cedula_per' => '31008662',
            'telefono_per' => 04241234567,
            'genero_per' => 'Femenino',
            'fecha_nacimiento_per' => now()->toDateString(),
            'correo_per' => 'genesisdoctor@gmail.com',
            'semestre_per' => null,
            'id_perfil' => 2,
            'sed_id' => 2,
        ]);

        DB::table('user')->insert([
            'per_id' => 5,
            'id_perfil' => 1,
            'username_use' => 'genesisdoctor@gmail.com',
            'email_use' => 'genesisdoctor@gmail.com',
            'password_use' => bcrypt('12345678'),
            'master_key_use' => bcrypt('masterkey123'),
            'security_questions_use' => null,
        ]);

        DB::table('usuario_rol')->insert([
            'rol_id' => 4,
            'use_id' => 5,
        ]);

        //Enfermero
        DB::table('persona')->insert([
            'nombres_per' => 'Denis',
            'segundo_nombre_per' => null,
            'apellidos_per' => 'Gil',
            'segundo_apellido_per' => null,
            'cedula_per' => '31710991',
            'telefono_per' => 04241234567,
            'genero_per' => 'Femenino',
            'fecha_nacimiento_per' => now()->toDateString(),
            'correo_per' => 'denisenfermero@gmail.com',
            'semestre_per' => null,
            'id_perfil' => 2,
            'sed_id' => 1,
        ]);

        DB::table('user')->insert([
            'per_id' => 6,
            'id_perfil' => 1,
            'username_use' => 'denisenfermero@gmail.com',
            'email_use' => 'denisenfermero@gmail.com',
            'password_use' => bcrypt('12345678'),
            'master_key_use' => bcrypt('masterkey123'),
            'security_questions_use' => null,
        ]);

        DB::table('usuario_rol')->insert([
            'rol_id' => 5,
            'use_id' => 6,
        ]);

        //Enfermero
        DB::table('persona')->insert([
            'nombres_per' => 'Carmen',
            'segundo_nombre_per' => null,
            'apellidos_per' => 'Pinero',
            'segundo_apellido_per' => null,
            'cedula_per' => '31008663',
            'telefono_per' => 04241234567,
            'genero_per' => 'Femenino',
            'fecha_nacimiento_per' => now()->toDateString(),
            'correo_per' => 'carmenenfermero@gmail.com',
            'semestre_per' => null,
            'id_perfil' => 2,
            'sed_id' => 2,
        ]);

        DB::table('user')->insert([
            'per_id' => 7,
            'id_perfil' => 1,
            'username_use' => 'carmenenfermero@gmail.com',
            'email_use' => 'carmenenfermero@gmail.com',
            'password_use' => bcrypt('12345678'),
            'master_key_use' => bcrypt('masterkey123'),
            'security_questions_use' => null,
        ]);

        DB::table('usuario_rol')->insert([
            'rol_id' => 5,
            'use_id' => 7,
        ]);

        // Becario (Paciente + Becario)
        DB::table('persona')->insert([
            'nombres_per' => 'Estudiante',
            'segundo_nombre_per' => null,
            'apellidos_per' => 'Becario',
            'segundo_apellido_per' => null,
            'cedula_per' => '1234567890',
            'telefono_per' => 04120000000,
            'genero_per' => 'Masculino',
            'fecha_nacimiento_per' => now()->toDateString(),
            'correo_per' => 'estudiantebecario@example.com',
            'semestre_per' => 4,
            'id_perfil' => 2,
            'sed_id' => 1,
        ]);

        DB::table('user')->insert([
            'per_id' => 8,
            'id_perfil' => 1,
            'username_use' => 'estudiantebecario@example.com',
            'email_use' => 'estudiantebecario@example.com',
            'password_use' => bcrypt('12345678'),
            'master_key_use' => bcrypt('masterkey123'),
            'security_questions_use' => null,
        ]);

        // Se le asigna Paciente (7) y Becario (9)
        DB::table('usuario_rol')->insert([
            ['rol_id' => 7, 'use_id' => 8], // Paciente
            ['rol_id' => 9, 'use_id' => 8], // Becario
        ]);

        $rolPsicologo = DB::table('rol')->where('slug_rol', 'psicologo')->value('id_rol');

        foreach ([
            ['Laura', 'Mendoza', '20111222', 'psicologa.laura@example.com', 1],
            ['Carlos', 'Rivas', '20333444', 'psicologo.carlos@example.com', 2],
        ] as [$nombre, $apellido, $cedula, $correo, $sede]) {
            $idPersona = DB::table('persona')->insertGetId([
                'nombres_per' => $nombre,
                'apellidos_per' => $apellido,
                'cedula_per' => $cedula,
                'telefono_per' => '04140000000',
                'genero_per' => 'Otro',
                'fecha_nacimiento_per' => now()->subYears(35)->toDateString(),
                'correo_per' => $correo,
                'id_perfil' => 1,
                'sed_id' => $sede,
            ], 'id_per');

            $idUsuario = DB::table('user')->insertGetId([
                'per_id' => $idPersona,
                'id_perfil' => 1,
                'username_use' => $correo,
            'email_use' => $correo,
                'password_use' => bcrypt('12345678'),
                'master_key_use' => bcrypt('masterkey123'),
                'security_questions_use' => null,
            ], 'id_use');

            if ($rolPsicologo) {
                DB::table('usuario_rol')->insert(['rol_id' => $rolPsicologo, 'use_id' => $idUsuario]);
            }
        }

        $estudianteGuanare = DB::table('persona')->insertGetId([
            'nombres_per' => 'Estudiante',
            'apellidos_per' => 'Guanare',
            'cedula_per' => '30999888',
            'telefono_per' => '04120000001',
            'genero_per' => 'Femenino',
            'fecha_nacimiento_per' => now()->subYears(20)->toDateString(),
            'correo_per' => 'estudiante.guanare@example.com',
            'semestre_per' => 2,
            'id_perfil' => 2,
            'sed_id' => 2,
        ], 'id_per');

        \App\Support\Cuentas\CuentasComunidad::procesarLote([$estudianteGuanare], 'seeder');

        $programas = DB::table('programa')->orderBy('id_prg')->pluck('id_prg')->values();

        if ($programas->isNotEmpty()) {
            $filas = DB::table('persona')->where('id_perfil', 2)->orderBy('id_per')->pluck('id_per')
                ->values()
                ->map(fn ($personaId, $i) => [
                    'per_id'           => $personaId,
                    'prg_id'           => $programas[$i % $programas->count()],
                    'fecha_inicio_pep' => now()->subYear()->toDateString(),
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ])
                ->all();

            DB::table('persona_programa')->insert($filas);
        }
    }
}
