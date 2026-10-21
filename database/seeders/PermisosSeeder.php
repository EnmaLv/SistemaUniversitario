<?php

namespace Database\Seeders;

use App\Models\Permiso;
use App\Models\Rol;
use App\Support\Permisos\SincronizadorPermisos;
use App\Support\Permisos\VersionPermisos;
use Illuminate\Database\Seeder;

class PermisosSeeder extends Seeder
{
    private const POR_DEFECTO = [
        'Obrero'                 => ['registro_comida', 'registro_diario', 'persona'],
        'Administrador de Salud' => ['envases_primarios', 'categorias_medicamentos', 'medicamentos', 'enfermedades_salud', 'horarios', 'consultorios', 'consultas'],
        'Doctor(a)'              => ['horarios', 'consultas'],
        'Enfermero(a)'           => ['horarios', 'consultas'],
        'Administrador de Beca'  => ['becas', 'solicitudes_becas', 'beneficios_becas'],
        'Paciente'               => ['citas', 'mural', 'notificaciones', 'publicaciones', 'estado_animo_diario'],
        'Becario'                => ['solicitar'],
        'Psicólogo'              => [
            'agenda', 'agenda_historial', 'agenda_estadisticas', 'agenda_prioridades',
            'horarios_psi', 'horarios_crear', 'grupo_horarios',
            'historias', 'plantillas_globales', 'plantillas', 'campos_evolucion', 'avances_sesion', 'estado_animos',
            'publicaciones', 'publicaciones_crear', 'mural', 'enfermedades', 'notificaciones',
        ],
    ];

    public function run(): void
    {
        SincronizadorPermisos::sincronizar(array_merge(...array_values(self::POR_DEFECTO)));

        foreach (self::POR_DEFECTO as $nombreRol => $vistas) {
            $rol = Rol::where('nombre', $nombreRol)->first();

            if (! $rol || $rol->permisos()->exists()) {
                continue;
            }

            $ids = Permiso::whereHas('vista', fn ($q) => $q->whereIn('key', $vistas))
                ->pluck('id')
                ->all();

            $rol->permisos()->syncWithoutDetaching($ids);
        }

        VersionPermisos::invalidar();
    }
}