<?php

/**
 * Mapeo de cada permiso (key del menú) a sus rutas del sistema.
 *
 * Los patrones se comparan contra:
 *   - El nombre de la ruta (Route::currentRouteName())
 *   - El path del request (sin / inicial)
 *
 * Usa wildcards de Laravel: admin.psicologia.maestros.horarios.*
 */
return [

    /* ═══════════════════ GENERAL ═══════════════════ */
    'home'           => ['home', 'admin.modulos.*'],
    'chat'           => ['chat.*'],
    'notificaciones' => ['notifications.*'],
    'perfil'         => ['profile.*'],

    /* ═══════════════════ ADMINISTRACIÓN ═══════════════════ */
    'sedes'                  => ['admin.maestros.sedes.*'],
    'categorias'             => ['admin.maestros.categorias.*'],
    'productos'              => ['admin.maestros.productos.*', 'productos.actualizar.tasa'],
    'proveedores'            => ['admin.maestros.proveedores.*'],
    'pnf'                    => ['admin.maestros.pnf.*'],
    'persona'                => ['admin.configuracion.persona.*'],
    'envases_primarios'      => ['admin.salud.maestros.envases_primarios.*'],
    'compras'                => ['admin.movimientos.compras.*'],
    'lotes'                  => ['admin.movimientos.lotes.*', 'admin.movimientos.lotes.mermar'],
    'movimientos'            => ['admin.movimientos.historial_movimientos.*'],
    'registro_diario'        => ['admin.movimientos.registro_diario.*'],
    'registro_comida'        => ['admin.movimientos.registro_comida.*'],
    'inventario_sedes_lotes' => ['admin.movimientos.sedes_lotes', 'admin.movimientos.sedes_lotes.show'],
    'empleados'              => ['admin.configuracion.empleados.*'],
    'roles'                  => ['admin.configuracion.roles.*'],
    'permisos'               => ['admin.configuracion.permisos.*'],
    'archivos'               => ['admin.configuracion.archivos.*'],

    /* ═══════════════════ COMEDOR ═══════════════════ */
    'comedor'                 => ['home'],
    'comedor_productos'       => ['admin.maestros.productos.*'],
    'comedor_recetas'         => ['admin.maestros.recetas.*', 'admin.maestros.receta_ingredientes.*'],
    'comedor_lotes'           => ['admin.movimientos.lotes.*'],
    'comedor_compras'         => ['admin.movimientos.compras.*'],
    'comedor_registro_diario' => ['admin.movimientos.registro_diario.*'],
    'comedor_registro_comida' => ['admin.movimientos.registro_comida.*'],
    'comedor_stock'           => ['admin.movimientos.sedes_lotes', 'admin.movimientos.sedes_lotes.show'],

    /* ═══════════════════ SALUD ═══════════════════ */
    'categorias_medicamentos' => ['admin.salud.maestros.categorias.*'],
    'medicamentos'            => ['admin.salud.maestros.medicamentos.*'],
    'enfermedades'            => ['admin.enfermedades.*'],
    'enfermedades_salud'      => ['admin.enfermedades.*'],
    'consultorios'            => ['admin.salud.maestros.consultorios.*'],
    'horarios'                => ['admin.salud.movimientos.horarios.*'],
    'consultas'               => ['admin.salud.movimientos.consultas.*'],

    /* ═══════════════════ PSICOLOGÍA ═══════════════════ */
    'agenda' => [
        'admin.psicologia.maestros.agenda.*',
        'admin.psicologia.maestros.citas.*',
    ],
    'agenda_historial'    => ['admin.psicologia.maestros.agenda.index'],
    'agenda_estadisticas' => ['admin.psicologia.maestros.agenda.estadisticas'],
    'agenda_prioridades'  => [
        'admin.psicologia.maestros.prioridades.*',
        'admin.psicologia.maestros.agenda.prioridades.*',
    ],
    'citas'           => ['admin.psicologia.maestros.citas.*'],
    'citas_historial' => ['admin.psicologia.maestros.citas.history.json'],
    'citas_crear'     => ['admin.psicologia.maestros.citas.create', 'admin.psicologia.maestros.citas.store'],

    'horarios_psi'   => ['admin.psicologia.maestros.horarios.*'],
    'horarios_crear' => ['admin.psicologia.maestros.horarios.create', 'admin.psicologia.maestros.horarios.store'],
    'grupo_horarios' => ['admin.psicologia.maestros.grupos_horarios.*'],

    'historias'           => ['admin.psicologia.maestros.historias.*'],
    'plantillas_globales' => ['admin.psicologia.maestros.plantillas_globales.*'],
    'plantillas'          => ['admin.psicologia.maestros.plantillas.*'],
    'campos_evolucion'    => ['admin.psicologia.maestros.campos_evolucion.*'],
    'avances_sesion'      => ['admin.psicologia.maestros.avances_sesion.*'],
    'estado_animos'       => ['admin.psicologia.maestros.estado_animos.*'],

    'publicaciones'       => ['admin.psicologia.maestros.publicaciones.*'],
    'publicaciones_crear' => ['admin.psicologia.maestros.publicaciones.create', 'admin.psicologia.maestros.publicaciones.store'],
    'mural'               => ['admin.psicologia.maestros.publicaciones.mural'],
    'estado_animo_diario' => ['admin.psicologia.maestros.estado_animo_diario.*'],
    'admin_users'         => ['admin.configuracion.empleados.*'],

    /* ═══════════════════ BECAS ═══════════════════ */
    'beneficios'        => ['admin.becas.beneficios.*'],
    'preguntas'         => ['admin.becas.preguntas.*'],
    'criterios'         => ['admin.becas.criterios.*'],
    'becas'             => ['admin.becas.*'],
    'jornada'           => ['admin.becas.jornada.*'],
    'solicitud'         => ['admin.becas.solicitudes.*'],
    'solicitudes_becas' => ['admin.becas.solicitudes.*'],
    'solicitar'         => ['admin.becas.solicitar'],

    /* ═══════════════════ TRANSPORTE ═══════════════════ */
    'bus_marcas'             => ['admin.transporte.maestros.bus_marcas.*'],
    'bus_modelos'            => ['admin.transporte.maestros.bus_modelos.*'],
    'bus_tipo_combustibles'  => ['admin.transporte.maestros.bus_tipo_combustibles.*'],
    'bus_vehiculos'          => ['admin.transporte.maestros.bus_vehiculos.*'],
    'bus_rutas'              => ['admin.transporte.maestros.bus_rutas.*'],
    'bus_paradas'            => ['admin.transporte.maestros.bus_paradas.*'],
    'bus_viajes'             => ['admin.transporte.maestros.bus_viajes.*'],
    'bus_mantenimientos'     => ['admin.transporte.maestros.bus_mantenimientos.*'],
    'bus_carga_combustibles' => ['admin.transporte.maestros.bus_carga_combustibles.*'],
];
