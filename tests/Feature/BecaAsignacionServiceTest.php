<?php

namespace Tests\Feature;

use App\Models\Becas\Beca;
use App\Models\Persona;
use App\Services\BecaAsignacionService;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BecaAsignacionServiceTest extends TestCase
{
    public function test_it_persists_assignment_when_syncing_assignments(): void
    {
        $this->markTestIncomplete('Prueba previa a la rama de seguridad: usa App\\Models\\Becas\\Beca, que no existe en el proyecto. Reescribir con los modelos actuales.');

        $beca = Beca::create([
            'codigo' => 'Beca-test-1',
            'nombre' => 'Beca prueba',
            'descripcion' => 'Prueba de asignación',
            'activo' => true,
        ]);

        $estatusId = DB::table('estatus')->insertGetId(['nombre_estatus' => 'Activo', 'created_at' => now(), 'updated_at' => now()]);
        $sucursalId = DB::table('sucursals')->insertGetId(['nombre' => 'Sucursal Prueba', 'direccion' => 'Dirección Prueba', 'activo' => true, 'created_at' => now(), 'updated_at' => now()]);
        $perfilId = DB::table('perfil')->insertGetId(['nombre_perfil' => 'Estudiante', 'id_estatus' => $estatusId, 'created_at' => now(), 'updated_at' => now()], 'id_perfil');
        $sedeId = DB::table('sede')->insertGetId(['nombre_sede' => 'Sede Prueba', 'estatus' => 1, 'id_sucursal' => $sucursalId, 'created_at' => now(), 'updated_at' => now()], 'id_sed');

        $persona = Persona::create([
            'nombres_per' => 'Ana',
            'segundo_nombre_per' => null,
            'apellidos_per' => 'Pérez',
            'segundo_apellido_per' => null,
            'cedula_per' => '12345678',
            'telefono_per' => '04141234567',
            'genero_per' => 'F',
            'edad_persona' => 20,
            'fecha_nacimiento_per' => '2006-01-01',
            'correo_per' => 'ana@test.com',
            'semestre_per' => '1',
            'estado' => true,
            'id_perfil' => $perfilId,
            'sed_id' => $sedeId,
        ]);

        $service = new BecaAsignacionService();

        $service->sincronizar($beca, [[
            'area' => 'Biblioteca',
            'horario' => 'Mañana',
            'tutor_id' => null,
            'observaciones' => 'Prueba',
            'activo' => true,
        ]]);

        $this->assertDatabaseHas('be_beca_trabajo_asignaciones', [
            'beca_id' => $beca->id,
            'area' => 'Biblioteca',
            'horario' => 'Mañana',
            'observaciones' => 'Prueba',
            'activo' => 1,
        ]);
    }
}
