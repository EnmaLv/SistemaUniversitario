<?php

/**
 * Fuente única de verdad de los permisos del sistema.
 *
 * Estructura:
 *   [grupo => [
 *       'label'       => Título visible en la vista de roles,
 *       'icon'        => Clase FontAwesome,
 *       'description' => Texto breve opcional,
 *       'items'       => [key => Label visible],
 *   ]]
 *
 * Los `key` deben coincidir EXACTAMENTE con los usados en @canMenu('key')
 * en los archivos de layouts/sidebar/*.blade.php.
 */
return [

    'general' => [
        'label'       => 'General',
        'icon'        => 'fas fa-home',
        'description' => 'Accesos base comunes a todos los usuarios.',
        'items' => [
            'home'           => 'Inicio',
            'chat'           => 'Mensajes',
            'notificaciones' => 'Notificaciones',
            'perfil'         => 'Mi perfil',
        ],
    ],

    'administracion' => [
        'label'       => 'Administración',
        'icon'        => 'fas fa-cog',
        'description' => 'Catálogos maestros, operaciones y configuración del sistema.',
        'items' => [
            // Catálogos
            'sedes'             => 'Sedes',
            'categorias'        => 'Categorías',
            'productos'         => 'Productos',
            'proveedores'       => 'Proveedores',
            'pnf'               => 'PNF',
            'persona'           => 'Personas',
            'envases_primarios' => 'Envases primarios',

            // Operaciones
            'compras'                 => 'Compras',
            'lotes'                   => 'Lotes',
            'movimientos'             => 'Movimientos de inventario',
            'registro_diario'         => 'Registro diario',
            'registro_comida'         => 'Registro de comida',
            'inventario_sedes_lotes'  => 'Inventario por sede',

            // Configuración
            'empleados'         => 'Usuarios / Empleados',
            'roles'             => 'Roles',
            'permisos'          => 'Permisos por usuario',
            'archivos'          => 'Archivos',
            'configuracion'     => 'Configuración general',
        ],
    ],

    'comedor' => [
        'label'       => 'Comedor',
        'icon'        => 'fas fa-utensils',
        'description' => 'Gestión del servicio de alimentación.',
        'items' => [
            'comedor'                  => 'Panel del comedor',
            'comedor_productos'        => 'Productos del comedor',
            'comedor_recetas'          => 'Recetas y platos',
            'comedor_lotes'            => 'Lotes y vencimientos',
            'comedor_compras'          => 'Compras',
            'comedor_registro_diario'  => 'Registro diario',
            'comedor_registro_comida'  => 'Registro de comida',
            'comedor_stock'            => 'Stock del comedor',
        ],
    ],

    'salud' => [
        'label'       => 'Salud y Farmacia',
        'icon'        => 'fas fa-heartbeat',
        'description' => 'Atención médica, farmacia y consultas.',
        'items' => [
            'envases_primarios'        => 'Envases primarios',
            'categorias_medicamentos'  => 'Categorías de medicamentos',
            'medicamentos'             => 'Medicamentos',
            'enfermedades'             => 'Enfermedades',
            'consultorios'             => 'Consultorios',
            'horarios'                 => 'Horarios de atención',
            'consultas'                => 'Consultas médicas',
        ],
    ],

    'psicologia' => [
        'label'       => 'Psicología',
        'icon'        => 'fas fa-brain',
        'description' => 'Agenda, historias clínicas y seguimiento psicológico.',
        'items' => [
            // Agenda
            'agenda'                => 'Vista de agenda',
            'agenda_historial'      => 'Historial de citas',
            'agenda_estadisticas'   => 'Estadísticas de agenda',
            'agenda_prioridades'    => 'Prioridades de atención',
            'enfermedades'          => 'Enfermedades',

            // Citas (paciente)
            'citas'                 => 'Mis citas',
            'citas_historial'       => 'Historial personal de citas',
            'citas_crear'           => 'Solicitar nueva cita',

            // Horarios
            'horarios_psi'              => 'Bloques de horario',
            'horarios_crear'        => 'Crear horario',
            'grupo_horarios'        => 'Grupos de horarios',

            // Historias clínicas
            'historias'             => 'Historias clínicas',
            'plantillas_globales'   => 'Esquema general',
            'plantillas'            => 'Anexos clínicos',
            'campos_evolucion'      => 'Campos de evolución',
            'avances_sesion'        => 'Avances de sesión',
            'estado_animos'         => 'Estados de ánimo',

            // Publicaciones
            'publicaciones'         => 'Publicaciones',
            'publicaciones_crear'   => 'Crear publicación',

            // Paciente
            'mural'                 => 'Mural de avisos',
            'estado_animo_diario'   => 'Estado de ánimo diario',
            'admin_users'           => 'Administración de usuarios clínicos',
        ],
    ],

    'becas' => [
        'label'       => 'Becas',
        'icon'        => 'fas fa-graduation-cap',
        'description' => 'Programas de becas, jornadas y beneficios.',
        'items' => [
            // Configuración
            'beneficios'    => 'Beneficios',
            'preguntas'     => 'Preguntas del formulario',
            'criterios'     => 'Criterios de selección',

            // Operación
            'becas'         => 'Panel de becas',
            'jornada'       => 'Jornadas de postulación',
            'solicitud'     => 'Solicitudes de beca',
            'solicitudes_becas' => 'Gestión de solicitudes',

            // Estudiante
            'solicitar'     => 'Solicitar beca',
        ],
    ],

    'transporte' => [
        'label'       => 'Transporte',
        'icon'        => 'fas fa-bus',
        'description' => 'Flota vehicular, rutas y mantenimiento.',
        'items' => [
            'bus_marcas'             => 'Marcas de vehículos',
            'bus_modelos'            => 'Modelos de vehículos',
            'bus_tipo_combustibles'  => 'Tipos de combustible',
            'bus_vehiculos'          => 'Vehículos',
            'bus_rutas'              => 'Rutas',
            'bus_paradas'            => 'Paradas',
            'bus_viajes'             => 'Viajes',
            'bus_mantenimientos'     => 'Mantenimientos',
            'bus_carga_combustibles' => 'Cargas de combustible',
        ],
    ],
];