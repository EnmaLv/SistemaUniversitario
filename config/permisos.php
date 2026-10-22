<?php

return [

    'acciones' => [
        'ver'      => ['label' => 'Ver',      'icon' => 'fas fa-eye'],
        'crear'    => ['label' => 'Crear',    'icon' => 'fas fa-plus'],
        'editar'   => ['label' => 'Editar',   'icon' => 'fas fa-pen'],
        'eliminar' => ['label' => 'Eliminar', 'icon' => 'fas fa-trash-alt'],
        'activar'  => ['label' => 'Activar',  'icon' => 'fas fa-toggle-on'],
        'exportar' => ['label' => 'Exportar', 'icon' => 'fas fa-file-export'],
    ],

    'roles_superadmin' => [
        'administrador',
        'secretaria de bienestar',
        'secretaria-de-bienestar',
    ],

    'roles_psicologo' => [
        'psicologo',
        'psicólogo',
    ],

    'grupo_modulo' => [
        'general'        => null,
        'administracion' => 'administracion',
        'comedor'        => 'comedor',
        'salud'          => 'salud',
        'psicologia'     => 'psicologia',
        'becas'          => 'beca',
        'transporte'     => 'transporte',
        'deportes'       => 'deporte',
    ],

    'sufijos' => [
        'exportar' => [
            'export', 'export_*', 'exportar', 'exportar_*',
            'pdf', '*_pdf', 'excel', '*_excel', 'word', '*_word', '*_zip',
            'download', 'download_*', 'descargar', 'descargar_*',
        ],
        'eliminar' => ['destroy', 'destroy_*', 'delete', 'eliminar', 'eliminar_*'],
        'activar'  => ['activar', 'activate', 'deactivate', 'desactivar', 'toggle'],
        'crear'    => ['create', 'store', 'store_*', 'crear', 'crear_*'],
        'editar'   => ['edit', 'edit_*', 'update', 'update_*', 'actualizar', 'actualizar_*'],
    ],

    'rutas_accion' => [
        'admin.movimientos.compras.enviarCorreo'          => 'editar',
        'admin.deportes.asistencia.reabrir'               => 'editar',
        'admin.salud.movimientos.consultas.recetacion'    => 'crear',
        'admin.salud.movimientos.consultas.dispensacion'  => 'crear',
        'admin.psicologia.maestros.publicaciones.reaccionar' => 'ver',
        'notifications.readAll'                           => 'ver',
        'admin.configuracion.seguridad.bloqueos.desbloquear' => 'activar',
        'admin.becas.lapsos.avanzar'                      => 'editar',
        'admin.transporte.maestros.bus_viajes.marcar_referencia' => 'editar',
    ],

    'rutas_libres' => [
        'paths' => [
            'admin/configuracion/master-key',
            'admin/configuracion/master-key/verify',
        ],
        'nombres' => [
            'admin.configuracion.master_key.form',
            'admin.configuracion.master_key.verify',
            'admin.becas.solicitudes.store',
            'admin.becas.solicitudes.renovar',
            'admin.becas.solicitudes.descargar_documento',
            'media.profile_photos',
            'media.publicaciones',
        ],
    ],

    'claves_obsoletas' => [
        'admin/modulos/seleccionar',
    ],
];
