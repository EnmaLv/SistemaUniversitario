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

    'grupo_modulo' => [
        'general'        => null,
        'administracion' => 'administracion',
        'comedor'        => 'comedor',
        'salud'          => 'salud',
        'psicologia'     => 'psicologia',
        'becas'          => 'beca',
        'transporte'     => 'transporte',
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
        'admin.salud.movimientos.consultas.recetacion'    => 'crear',
        'admin.salud.movimientos.consultas.dispensacion'  => 'crear',
        'admin.psicologia.maestros.publicaciones.reaccionar' => 'ver',
        'notifications.readAll'                           => 'ver',
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
            'admin.becas.lapsos.avanzar',
        ],
    ],

    'claves_obsoletas' => [
        'admin/modulos/seleccionar',
    ],
];
