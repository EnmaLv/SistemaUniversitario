<?php

namespace Tests\Feature\Seguridad;

use App\Models\Archivo;
use Illuminate\Support\Facades\Storage;

class ArchivosPrivadosTest extends SeguridadTestCase
{
    public function test_el_excel_importado_solo_se_descarga_con_permiso(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        Storage::disk('local')->put('importaciones/estudiantes.xlsx', 'contenido');

        $archivo = Archivo::create([
            'info_estudiantes' => 'Estudiantes UPTP - prueba',
            'ruta_archivo'     => 'importaciones/estudiantes.xlsx',
            'nombre_original'  => 'estudiantes.xlsx',
            'fecha'            => now()->toDateString(),
            'estado'           => 'Procesado',
        ]);

        $this->get(route('admin.configuracion.archivos.ver', $archivo->id))->assertRedirect(route('login'));

        $this->actingAs($this->crearUsuario('Obrero'))
            ->get(route('admin.configuracion.archivos.ver', $archivo->id))
            ->assertForbidden();

        $this->actingAs($this->crearUsuario('Administrador'))
            ->get(route('admin.configuracion.archivos.ver', $archivo->id))
            ->assertOk()
            ->assertDownload('estudiantes.xlsx');

        $this->assertFalse(Storage::disk('public')->exists('importaciones/estudiantes.xlsx'));
    }

    public function test_el_comando_mueve_los_excel_publicos_al_disco_privado(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        Storage::disk('public')->put('informacion/antiguo.xlsx', 'contenido');
        Storage::disk('public')->put('publicaciones/foto.png', 'imagen');

        $this->artisan('archivos:migrar-privados')->assertSuccessful();

        $this->assertFalse(Storage::disk('public')->exists('informacion/antiguo.xlsx'));
        $this->assertTrue(Storage::disk('local')->exists('importaciones/legado/antiguo.xlsx'));
        $this->assertTrue(Storage::disk('public')->exists('publicaciones/foto.png'));

        $this->artisan('archivos:migrar-privados')->assertSuccessful();
        $this->assertTrue(Storage::disk('local')->exists('importaciones/legado/antiguo.xlsx'));
    }

    public function test_un_usuario_sin_permiso_no_avanza_el_lapso_academico(): void
    {
        $this->actingAs($this->crearUsuario('Paciente'))
            ->post(route('admin.becas.lapsos.avanzar'), [
                'codigo'       => '2099-1',
                'fecha_inicio' => now()->toDateString(),
                'fecha_fin'    => now()->addMonths(4)->toDateString(),
            ])
            ->assertForbidden();
    }

    public function test_la_posicion_gps_exige_autenticacion(): void
    {
        $this->getJson('/api/viajes/1/posicion')->assertUnauthorized();
    }
}
