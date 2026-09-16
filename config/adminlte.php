<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Title
    |--------------------------------------------------------------------------
    |
    | Here you can change the default title of your admin panel.
    |
    | For detailed instructions you can look the title section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'title' => 'Bienestar Estudiantil',
    'title_prefix' => '',
    'title_postfix' => '',

    /*
    |--------------------------------------------------------------------------
    | Favicon
    |--------------------------------------------------------------------------
    |
    | Here you can activate the favicon.
    |
    | For detailed instructions you can look the favicon section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'use_ico_only' => false,
    'use_full_favicon' => false,

    /*
    |--------------------------------------------------------------------------
    | Google Fonts
    |--------------------------------------------------------------------------
    |
    | Here you can allow or not the use of external google fonts. Disabling the
    | google fonts may be useful if your admin panel internet access is
    | restricted somehow.
    |
    | For detailed instructions you can look the google fonts section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'google_fonts' => [
        'allowed' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Logo
    |--------------------------------------------------------------------------
    |
    | Here you can change the logo of your admin panel.
    |
    | For detailed instructions you can look the logo section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'logo' => '<b>Bienestar Estudiantil</b>',
    'logo_img' => 'img/Logo.webp',
    'logo_img_class' => 'brand-image img-circle elevation-3',
    'logo_img_xl' => null,
    'logo_img_xl_class' => 'brand-image-xs',
    'logo_img_alt' => 'Admin Logo',

    /*
    |--------------------------------------------------------------------------
    | Authentication Logo
    |--------------------------------------------------------------------------
    |
    | Here you can setup an alternative logo to use on your login and register
    | screens. When disabled, the admin panel logo will be used instead.
    |
    | For detailed instructions you can look the auth logo section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'auth_logo' => [
        'enabled' => false,
        'img' => [
            'path' => 'img/Logo.webp',
            'alt' => 'Auth Logo',
            'class' => '',
            'width' => 50,
            'height' => 50,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Preloader Animation
    |--------------------------------------------------------------------------
    |
    | Here you can change the preloader animation configuration. Currently, two
    | modes are supported: 'fullscreen' for a fullscreen preloader animation
    | and 'cwrapper' to attach the preloader animation into the content-wrapper
    | element and avoid overlapping it with the sidebars and the top navbar.
    |
    | For detailed instructions you can look the preloader section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'preloader' => [
        'enabled' => true,
        'mode' => 'fullscreen',
        'img' => [
            'path' => 'img/Logo.webp',
            'alt' => 'AdminLTE Preloader Image',
            'effect' => 'animation__shake',
            'width' => 90,
            'height' => 90,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Menu
    |--------------------------------------------------------------------------
    |
    | Here you can activate and change the user menu.
    |
    | For detailed instructions you can look the user menu section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'usermenu_enabled' => true,
    'usermenu_header' => false,
    'usermenu_header_class' => 'bg-primary',
    'usermenu_image' => false,
    'usermenu_desc' => false,
    'usermenu_profile_url' => false,

    /*
    |--------------------------------------------------------------------------
    | Layout
    |--------------------------------------------------------------------------
    |
    | Here we change the layout of your admin panel.
    |
    | For detailed instructions you can look the layout section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'layout_topnav' => null,
    'layout_boxed' => null,
    'layout_fixed_sidebar' => null,
    'layout_fixed_navbar' => null,
    'layout_fixed_footer' => null,
    'layout_dark_mode' => null,

    /*
    |--------------------------------------------------------------------------
    | Authentication Views Classes
    |--------------------------------------------------------------------------
    |
    | Here you can change the look and behavior of the authentication views.
    |
    | For detailed instructions you can look the auth classes section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'classes_auth_card' => 'card-outline card-primary',
    'classes_auth_header' => '',
    'classes_auth_body' => '',
    'classes_auth_footer' => '',
    'classes_auth_icon' => '',
    'classes_auth_btn' => 'btn-flat btn-primary',

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Classes
    |--------------------------------------------------------------------------
    |
    | Here you can change the look and behavior of the admin panel.
    |
    | For detailed instructions you can look the admin panel classes here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'classes_body' => '',
    'classes_brand' => '',
    'classes_brand_text' => '',
    'classes_content_wrapper' => '',
    'classes_content_header' => '',
    'classes_content' => '',
    'classes_sidebar' => 'sidebar-bg elevation-4',
    'classes_sidebar_nav' => '',
    'classes_topnav' => 'navbar-white navbar-light',
    'classes_topnav_nav' => 'navbar-expand',
    'classes_topnav_container' => 'container',

    /*
    |--------------------------------------------------------------------------
    | Sidebar
    |--------------------------------------------------------------------------
    |
    | Here we can modify the sidebar of the admin panel.
    |
    | For detailed instructions you can look the sidebar section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'sidebar_mini' => 'lg',
    'sidebar_collapse' => false,
    'sidebar_collapse_auto_size' => false,
    'sidebar_collapse_remember' => false,
    'sidebar_collapse_remember_no_transition' => true,
    'sidebar_scrollbar_theme' => 'os-theme-light',
    'sidebar_scrollbar_auto_hide' => 'l',
    'sidebar_nav_accordion' => true,
    'sidebar_nav_animation_speed' => 300,

    /*
    |--------------------------------------------------------------------------
    | Control Sidebar (Right Sidebar)
    |--------------------------------------------------------------------------
    |
    | Here we can modify the right sidebar aka control sidebar of the admin panel.
    |
    | For detailed instructions you can look the right sidebar section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'right_sidebar' => false,
    'right_sidebar_icon' => 'fas fa-cogs',
    'right_sidebar_theme' => 'dark',
    'right_sidebar_slide' => true,
    'right_sidebar_push' => true,
    'right_sidebar_scrollbar_theme' => 'os-theme-light',
    'right_sidebar_scrollbar_auto_hide' => 'l',

    /*
    |--------------------------------------------------------------------------
    | URLs
    |--------------------------------------------------------------------------
    |
    | Here we can modify the url settings of the admin panel.
    |
    | For detailed instructions you can look the urls section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'use_route_url' => false,
    'dashboard_url' => 'home',
    'logout_url' => 'logout',
    'login_url' => 'login',
    'register_url' => 'register',
    'password_reset_url' => 'password/reset',
    'password_email_url' => 'password/email',
    'profile_url' => false,
    'disable_darkmode_routes' => false,

    /*
    |--------------------------------------------------------------------------
    | Laravel Asset Bundling
    |--------------------------------------------------------------------------
    |
    | Here we can enable the Laravel Asset Bundling option for the admin panel.
    | Currently, the next modes are supported: 'mix', 'vite' and 'vite_js_only'.
    | When using 'vite_js_only', it's expected that your CSS is imported using
    | JavaScript. Typically, in your application's 'resources/js/app.js' file.
    | If you are not using any of these, leave it as 'false'.
    |
    | For detailed instructions you can look the asset bundling section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Other-Configuration
    |
    */

    'laravel_asset_bundling' => false,
    'laravel_css_path' => 'css/app.css',
    'laravel_js_path' => 'js/app.js',

    /*
    |--------------------------------------------------------------------------
    | Menu Items
    |--------------------------------------------------------------------------
    |
    | Here we can modify the sidebar/top navigation of the admin panel.
    |
    | For detailed instructions you can look here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Menu-Configuration
    |
    */

    'menu' => [

        // 🔍 Barra superior
        [
            'type' => 'fullscreen-widget',
            'topnav_right' => true,
        ],

        // 🔍 Buscador lateral
        [
            'type' => 'sidebar-menu-search',
            'text' => 'Buscar',
        ],



        /* ---------------------------------------------------
        | 🍽️ GESTIÓN DE COMEDOR
        --------------------------------------------------- */
        [
            'header' => 'Gestión de Comedor',
            'classes' => 'text-bold',
            'module' => 'comedor',
        ],

        [
            'text' => 'Recetas y Platos',
            'icon' => 'fas fa-utensils',
            'module' => 'comedor',
            'active' => ['admin/maestros/recetas*', 'admin/maestros/receta_ingredientes*'],
            'submenu' => [
                [
                    'text' => 'Recetas',
                    'url' => 'admin/maestros/recetas',
                    'icon' => 'fas fa-book-open',
                    'active' => ['admin/maestros/recetas*'],
                ],
                [
                    'text' => 'Ingredientes',
                    'url' => 'admin/maestros/receta_ingredientes',
                    'icon' => 'fas fa-carrot',
                    'active' => ['admin/maestros/receta_ingredientes*'],
                ],
            ]
        ],

        [
            'text' => 'Registro de Comidas',
            'module' => 'comedor',
            'icon' => 'fas fa-clipboard-check',
            'active' => ['admin/movimientos/registro_comida*', 'admin/movimientos/registro_diario*'],
            'submenu' => [
                [
                    'text' => 'Registrar Comida',
                    'key' => 'registro_comida',
                    'url' => 'admin/movimientos/registro_comida',
                    'icon' => 'fas fa-utensils',
                    'active' => ['admin/movimientos/registro_comida*'],
                ],
                [
                    'text' => 'Registro Diario',
                    'key' => 'registro_diario',
                    'url' => 'admin/movimientos/registro_diario',
                    'icon' => 'fas fa-concierge-bell',
                    'active' => ['admin/movimientos/registro_diario*'],
                ],
            ]
        ],

        /* ---------------------------------------------------
        | 📦 GESTIÓN DE INVENTARIO
        --------------------------------------------------- */
        [
            'header' => 'Gestión de Inventario',
            'classes' => 'text-bold',
            'module' => 'comedor',
        ],

        [
            'text' => 'Catálogo de Productos',
            'icon' => 'fas fa-boxes',
            'module' => 'comedor',
            'active' => ['admin/maestros/categorias*', 'admin/maestros/productos*'],
            'submenu' => [
                [
                    'text' => 'Categorías',
                    'key' => 'productos_categorias',
                    'url' => 'admin/maestros/categorias',
                    'icon' => 'fas fa-tags',
                    'active' => ['admin/maestros/categorias*'],
                ],
                [
                    'text' => 'Productos',
                    'key' => 'productos',
                    'url' => 'admin/maestros/productos',
                    'icon' => 'fas fa-box',
                    'active' => ['admin/maestros/productos*'],
                ],
            ]
        ],

        [
            'text' => 'Compras y Requisiciones',
            'url' => 'admin/movimientos/compras',
            'module' => 'administracion',
            'icon' => 'fas fa-shopping-cart',
            'active' => ['admin/movimientos/compras*'],
        ],

        [
            'text' => 'Control de Stock',
            'icon' => 'fas fa-warehouse',
            'module' => 'comedor',
            'active' => ['admin/movimientos/inventario*', 'admin/movimientos/lotes*', 'admin/movimientos/sedes_lotes*'],
            'submenu' => [
                [
                    'text' => 'Lotes',
                    'url' => 'admin/movimientos/lotes',
                    'icon' => 'fas fa-boxes',
                    'active' => ['admin/movimientos/lotes*'],
                ],
                [
                    'text' => 'Existencias por Sede',
                    'url' => 'admin/movimientos/sedes_lotes',
                    'icon' => 'fas fa-store-alt',
                    'active' => ['admin/movimientos/sedes_lotes*'],
                ],
            ]
        ],

        [
            'text' => 'Historial de Movimientos',
            'url' => 'admin/movimientos/historial_movimientos',
            'module' => 'comedor',
            'icon' => 'fas fa-clipboard-list',
            'active' => ['admin/movimientos/historial_movimientos*'],
        ],

        /* ---------------------------------------------------
        | 🏢 CONFIGURACIÓN INSTITUCIONAL
        --------------------------------------------------- */
        [
            'header' => 'Configuración Institucional',
            'classes' => 'text-bold',
            'module' => 'administracion',
        ],

        [
            'text' => 'Configuración General',
            'icon' => 'fas fa-cog',
            'module' => 'administracion',
            'active' => ['admin/maestros/sedes*', 'admin/maestros/proveedores*', 'admin/maestros/pnf*'],
            'submenu' => [
                [
                    'text' => 'Sedes y Anexos',
                    'url' => 'admin/maestros/sedes',
                    'icon' => 'fas fa-store',
                    'active' => ['admin/maestros/sedes*'],
                ],
                [
                    'text' => 'Proveedores',
                    'key' => 'proveedores',
                    'url' => 'admin/maestros/proveedores',
                    'icon' => 'fas fa-truck',
                    'active' => ['admin/maestros/proveedores*'],
                ],

                [
                    'text' => 'Ubicaciones Geográficas',
                    'key' => 'ubicaciones',
                    'icon' => 'fas fa-map-marked-alt',
                    'active' => ['admin/estado*', 'admin/municipio*', 'admin/localidad*'],
                    'submenu' => [
                        [
                            'text' => 'Estados',
                            'key' => 'ubicaciones_estados',
                            'url'  => 'admin/estado',
                            'icon' => 'fas fa-globe',
                            'active' => ['admin/estado*'],
                        ],
                        [
                            'text' => 'Municipios',
                            'key' => 'ubicaciones_municipios',
                            'url'  => 'admin/municipio',
                            'icon' => 'fas fa-city',
                            'active' => ['admin/municipio*'],
                        ],
                        [
                            'text' => 'Localidades',
                            'key' => 'ubicaciones_localidades',
                            'url'  => 'admin/localidad',
                            'icon' => 'fas fa-home',
                            'active' => ['admin/localidad*'],
                        ],
                    ],
                ],

                [
                    'text' => 'Programas de Formación',
                    'key' => 'pnf',
                    'module' => 'administracion',
                    'url' => 'admin/maestros/pnf',
                    'icon' => 'fas fa-graduation-cap',
                    'active' => ['admin/maestro/pnf*'],
                ],

                [
                    'text' => 'Estudiantes',
                    'key' => 'persona',
                    'module' => 'administracion',
                    'url' => 'admin/persona',
                    'icon' => 'fas fa-user-graduate',
                    'active' => ['admin/persona*'],
                ],

            ]
        ],

        /* ---------------------------------------------------
        | ⚙️ ADMINISTRACIÓN DEL SISTEMA
        --------------------------------------------------- */
        [
            'header' => 'Administración del Sistema',
            'classes' => 'text-bold',
            'module' => 'administracion',
        ],

        [
            'text' => 'Gestión de Usuarios',
            'icon' => 'fas fa-users-cog',
            'module' => 'administracion',
            'active' => ['admin/configuracion/empleados*', 'admin/configuracion/roles*', 'admin/configuracion/permisos*'],
            'submenu' => [
                [
                    'text' => 'Empleados',
                    'key' => 'config_empleados',
                    'url' => 'admin/configuracion/empleados',
                    'icon' => 'fas fa-users',
                    'active' => ['admin/configuracion/empleados*'],
                ],
                [
                    'text' => 'Roles',
                    'key' => 'config_roles',
                    'url' => 'admin/configuracion/roles',
                    'icon' => 'fas fa-user-tag',
                    'active' => ['admin/configuracion/roles*'],
                ],
                [
                    'text' => 'Permisos',
                    'key' => 'config_permisos',
                    'url' => 'admin/configuracion/permisos',
                    'icon' => 'fas fa-key',
                    'active' => ['admin/configuracion/permisos*'],
                ],

                [
                    'text' => 'Archivos del Sistema',
                    'module' => 'administracion',
                    'url' => 'admin/configuracion/archivos',
                    'icon' => 'fas fa-folder-open',
                    'active' => ['admin/configuracion/archivos*'],
                ],
            ]
        ],

        [
            'text' => 'Envases Primarios',
            'key' => 'envases_primarios',
            'module' => 'salud',
            'url' => 'admin/salud/maestros/envases_primarios',
            'icon' => 'fas fa-box',
            'active' => ['admin/salud/maestros/envases_primarios*'],
        ],

        [
            'text' => 'Categorias',
            'key' => 'categorias_medicamentos',
            'module' => 'salud',
            'url' => 'admin/salud/maestros/categorias',
            'icon' => 'fas fa-box',
            'active' => ['admin/salud/maestros/categorias*'],
        ],

        [
            'text' => 'Medicamentos',
            'key' => 'medicamentos',
            'module' => 'salud',
            'url' => 'admin/salud/maestros/medicamentos',
            'icon' => 'fas fa-pills',
            'active' => ['admin/salud/maestros/medicamentos*'],
        ],
        [
            'text' => 'Enfermedades',
            'key' => 'enfermedades_salud',
            'module' => 'psicologia',
            //'route' => ['admin.enfermedades.index', ['tipo' => 'fisica']],
            'icon' => 'fas fa-procedures',
            'active' => ['admin/enfermedades*'],
        ],

        [
            'text' => 'Citas',
            'key' => 'citas',
            'module' => 'psicologia',
            'url' => 'admin/psicologia/maestros/citas',
            'icon' => 'fas fa-calendar-check',
            'active' => ['admin/psicologia/maestros/citas*'],
        ],

        [
            'text' => 'Agenda',
            'key' => 'agenda',
            'module' => 'psicologia',
            'url' => 'admin/psicologia/maestros/agenda',
            'icon' => 'fas fa-calendar-check',
            'active' => ['admin/psicologia/maestros/publicaciones/agenda*'],
        ],

        [
            'text' => 'Mural',
            'key' => 'mural',
            'module' => 'psicologia',
            'url' => 'admin/psicologia/maestros/mural',
            'icon' => 'fas fa-calendar-check',
            'active' => ['admin/psicologia/maestros/publicaciones/mural*'],
        ],

        [
            'text' => 'Notificaciones',
            'key' => 'notificaciones',
            'module' => 'psicologia',
            'url' => 'admin/notificaciones', 
            'icon' => 'fas fa-calendar-check',
            'active' => ['admin/notificaciones*'],
        ],

        [
            'text' => 'Estado de animo',
            'key' => 'estado_animo_diario',
            'module' => 'psicologia',
            'url' => 'admin/psicologia/maestros/estado_animo_diario', 
            'icon' => 'fas fa-calendar-check',
            'active' => ['admin/psicologia/maestros/estado_animo_diario*'],
        ],

        [
            'text' => 'Modulos del Sistema',
            'url' => 'admin/modulos/seleccionar',
            'icon' => 'fas fa-th-large',
            'active' => ['admin/modulos*'],
        ],

        /* ---------------------------------------------------
        | GESTION DE BECAS
        --------------------------------------------------- */
        [
            'header' => 'Gestion de Becas',
            'classes' => 'text-bold',
            'module' => 'beca',
        ],
        [
            'text' => 'Gestion de Becas',
            'key' => 'becas',
            'module' => 'beca',
            'url' => 'admin/becas',
            'icon' => 'fas fa-graduation-cap',
            'active' => ['admin/becas', 'admin/becas/create'],
        ],
        [
            'text' => 'Registrar Beneficios',
            'key' => 'beneficios_becas',
            'module' => 'beca',
            'url' => 'admin/becas/beneficios',
            'icon' => 'fas fa-gift',
            'active' => ['admin/becas/beneficios*'],
        ],
        [
            'text' => 'Solicitudes',
            'key' => 'solicitudes_becas',
            'module' => 'beca',
            'url' => 'admin/becas/solicitudes',
            'icon' => 'fas fa-file-alt',
            'active' => ['admin/becas/solicitudes*'],
        ],
        [
            'text' => 'Beneficios',
            'key' => 'beneficios',
            'module' => 'beca',
            'url' => 'admin/becas/beneficios',
            'icon' => 'fas fa-file-alt',
            'active' => ['admin/becas/beneficios*'],
        ],
        [
            'text' => 'Jornada',
            'key' => 'jornada_becas',
            'module' => 'beca',
            'url' => 'admin/becas/jornada',
            'icon' => 'fas fa-user-clock',
            'active' => ['admin/becas/jornada*'],
        ],
        //Opciones para becarios(Estudiantes y Pacientes)
        [
            'text' => 'Solicitar Beca',
            'key' => 'solicitar_beca',
            'module' => 'beca',
            'url' => 'admin/becas/solicitar',
            'icon' => 'fas fa-file-alt',
            'active' => ['admin/becas/solicitar*'],
        ],

        /* ---------------------------------------------------
        | GESTIÓN DE TRANSPORTE
        --------------------------------------------------- */
        [
            'header' => 'Gestión de Transporte',
            'classes' => 'text-bold',
            'module' => 'transporte',
        ],
        [
            'text' => 'Marcas',
            'icon' => 'fas fa-tag',
            'module' => 'transporte',
            'key' => 'transporte_marcas',
            'url' => 'admin/transporte/maestros/bus_marcas',
            'active' => ['admin/transporte/maestros/bus_marcas*'],
        ],
        [
            'text'   => 'Modelos',
            'icon'   => 'fas fa-car',
            'module' => 'transporte',
            'key'    => 'transporte_modelos',
            'url'    => 'admin/transporte/maestros/bus_modelos',
            'active' => ['admin/transporte/maestros/bus_modelos*'],
        ],
        [
            'text'   => 'Tipos de Combustible',
            'icon'   => 'fas fa-gas-pump',
            'module' => 'transporte',
            'key'    => 'transporte_tipo_combustibles',
            'url'    => 'admin/transporte/maestros/bus_tipo_combustibles',
            'active' => ['admin/transporte/maestros/bus_tipo_combustibles*'],
        ],
        [
            'text'   => 'Vehículos',
            'icon'   => 'fas fa-bus',
            'module' => 'transporte',
            'key'    => 'transporte_vehiculos',
            'url'    => 'admin/transporte/maestros/bus_vehiculos',
            'active' => ['admin/transporte/maestros/bus_vehiculos*'],
        ],

        [
            'text'   => 'Rutas',
            'icon'   => 'fas fa-route',
            'module' => 'transporte',
            'key'    => 'transporte_rutas',
            'url'    => 'admin/transporte/maestros/bus_rutas',
            'active' => ['admin/transporte/maestros/bus_rutas*'],
        ],

        [
            'text'   => 'Paradas',
            'icon'   => 'fas fa-map-pin',
            'module' => 'transporte',
            'key'    => 'transporte_paradas',
            'url'    => 'admin/transporte/maestros/bus_paradas',
            'active' => ['admin/transporte/maestros/bus_paradas*'],
        ],
        [
            'text'   => 'Viajes',
            'icon'   => 'fas fa-map-marked-alt',
            'module' => 'transporte',
            'key'    => 'transporte_viajes',
            'url'    => 'admin/transporte/maestros/bus_viajes',
            'active' => ['admin/transporte/maestros/bus_viajes*'],
        ],
        [
            'text'   => 'Mantenimiento',
            'icon'   => 'fas fa-tools',
            'module' => 'transporte',
            'key'    => 'transporte_mantenimientos',
            'url'    => 'admin/transporte/maestros/bus_mantenimientos',
            'active' => ['admin/transporte/maestros/bus_mantenimientos*'],
        ],
        [
            'text'   => 'Combustible',
            'icon'   => 'fas fa-gas-pump',
            'module' => 'transporte',
            'key'    => 'transporte_combustibles',
            'url'    => 'admin/transporte/maestros/bus_carga_combustibles',
            'active' => ['admin/transporte/maestros/bus_carga_combustibles*'],
        ],
    ],



    /*
    |--------------------------------------------------------------------------
    | Menu Filters
    |--------------------------------------------------------------------------
    |
    | Here we can modify the menu filters of the admin panel.
    |
    | For detailed instructions you can look the menu filters section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Menu-Configuration
    |
    */

    'filters' => [
        App\Menu\Filters\PermissionFilter::class,
        App\AdminLTE\Filters\ModuleFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\GateFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\HrefFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\SearchFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ActiveFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ClassesFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\LangFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\DataFilter::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Plugins Initialization
    |--------------------------------------------------------------------------
    |
    | Here we can modify the plugins used inside the admin panel.
    |
    | For detailed instructions you can look the plugins section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Plugins-Configuration
    |
    */

    'plugins' => [
        'Datatables' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/buttons/2.4.0/js/dataTables.buttons.min.js',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/buttons/2.4.0/js/buttons.bootstrap4.min.js',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/buttons/2.4.0/js/buttons.html5.min.js',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/buttons/2.4.0/js/buttons.print.min.js',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/buttons/2.4.0/js/buttons.colVis.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/buttons/2.4.0/css/buttons.bootstrap4.min.css',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => 'https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => 'https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => 'https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js',
                ],
            ],
        ],
        'Select2' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.css',
                ],
            ],
        ],
        'Chartjs' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.jsdelivr.net/npm/chart.js@3.8.0/dist/chart.min.js',
                ],
            ],
        ],
        'Sweetalert2' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => true,
                    'location' => 'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css',
                ],
            ],
        ],

        'InputMask' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.9/jquery.inputmask.min.js',
                ],
            ],
        ],

        'CKEditor' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js',
                ],
            ],
        ],

        'Flatpickr' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => 'https://cdn.jsdelivr.net/npm/flatpickr',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css',
                ],
            ],
        ],

        'Pace' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/themes/blue/pace-theme-center-radar.min.css',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/pace.min.js',
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | IFrame
    |--------------------------------------------------------------------------
    |
    | Here we change the IFrame mode configuration. Note these changes will
    | only apply to the view that extends and enable the IFrame mode.
    |
    | For detailed instructions you can look the iframe mode section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/IFrame-Mode-Configuration
    |
    */

    'iframe' => [
        'default_tab' => [
            'url' => null,
            'title' => null,
        ],
        'buttons' => [
            'close' => true,
            'close_all' => true,
            'close_all_other' => true,
            'scroll_left' => true,
            'scroll_right' => true,
            'fullscreen' => true,
        ],
        'options' => [
            'loading_screen' => 1000,
            'auto_show_new_tab' => true,
            'use_navbar_items' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Livewire
    |--------------------------------------------------------------------------
    |
    | Here we can enable the Livewire support.
    |
    | For detailed instructions you can look the livewire here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Other-Configuration
    |
    */

    'livewire' => false,
];
