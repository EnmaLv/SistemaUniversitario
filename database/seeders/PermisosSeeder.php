<?php

namespace Database\Seeders;

use App\Models\Permiso;
use App\Models\Rol;
use App\Support\Permisos\SincronizadorPermisos;
use App\Support\Permisos\VersionPermisos;
use Illuminate\Database\Seeder;

class PermisosSeeder extends Seeder
{
    /**
     * Vistas por rol. Una clave numérica otorga todas las acciones de la vista;
     * 'vista' => ['ver', ...] otorga solo esas acciones.
     */
    private const POR_DEFECTO = [
        'Obrero'                 => ['registro_comida', 'registro_diario', 'persona'],
        'Administrador de Salud' => ['envases_primarios', 'categorias_medicamentos', 'medicamentos', 'enfermedades_salud', 'horarios', 'consultorios', 'consultas'],
        'Doctor(a)'              => ['horarios', 'consultas', 'medicamentos' => ['ver']],
        'Enfermero(a)'           => ['horarios', 'consultas', 'medicamentos' => ['ver']],
        'Administrador de Beca'  => ['becas', 'solicitudes_becas', 'beneficios_becas'],
        'Paciente'               => ['citas', 'mural', 'notificaciones', 'publicaciones', 'estado_animo_diario'],
        'Becario'                => ['solicitar'],
        'Psicólogo'              => [
            'agenda', 'agenda_historial', 'agenda_estadisticas', 'agenda_prioridades',
            'horarios_psi', 'horarios_crear', 'grupo_horarios',
            'historias', 'plantillas_globales', 'plantillas', 'campos_evolucion', 'avances_sesion', 'estado_animos',
            'publicaciones', 'publicaciones_crear', 'mural', 'enfermedades', 'notificaciones',
        ],
        'Coordinador de Deportes' => [
            'deportes_espacios', 'deportes_actividades', 'deportes_calendarios', 'deportes_horarios',
            'deportes_torneos', 'deportes_inscripciones', 'deportes_asistencia',
            'deportes_prestamos', 'deportes_sanciones', 'deportes_solvencia',
            'implementos_materiales',
            'categorias'             => ['ver'],
            'inventario_sedes_lotes' => ['ver'],
            'lotes'                  => ['ver'],
            'movimientos'            => ['ver', 'exportar'],
        ],
    ];

    public function run(): void
    {
        $porRol = array_map(fn (array $vistas) => static::normalizar($vistas), self::POR_DEFECTO);

        SincronizadorPermisos::sincronizar(array_values(array_unique(array_merge(...array_map('array_keys', array_values($porRol))))));

        foreach ($porRol as $nombreRol => $vistas) {
            $rol = Rol::where('nombre_rol', $nombreRol)->first();

            if (! $rol) {
                continue;
            }

            $ids = [];

            foreach ($vistas as $vista => $acciones) {
                $ids = array_merge($ids, Permiso::whereHas('vista', fn ($q) => $q->where('key_vis', $vista))
                    ->when($acciones !== null, fn ($q) => $q->whereIn('accion_pmi', $acciones))
                    ->pluck('id_pmi')
                    ->all());
            }

            $rol->permisos()->syncWithoutDetaching(array_values(array_unique($ids)));
        }

        VersionPermisos::invalidar();
    }

    /**
     * @return array<string, array<int, string>|null> vista => acciones (null = todas)
     */
    private static function normalizar(array $vistas): array
    {
        $normalizadas = [];

        foreach ($vistas as $clave => $valor) {
            if (is_int($clave)) {
                $normalizadas[$valor] = null;
            } else {
                $normalizadas[$clave] = array_values((array) $valor);
            }
        }

        return $normalizadas;
    }
}
