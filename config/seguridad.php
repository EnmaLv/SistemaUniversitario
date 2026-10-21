<?php

return [

    'max_intentos_login' => (int) env('SEGURIDAD_MAX_INTENTOS_LOGIN', 3),

    'minutos_bloqueo_temporal' => (int) env('SEGURIDAD_MINUTOS_BLOQUEO_TEMPORAL', 5),

    'bloqueos_temporales_para_permanente' => (int) env('SEGURIDAD_BLOQUEOS_PARA_PERMANENTE', 3),

    'horas_ventana_bloqueos' => (int) env('SEGURIDAD_HORAS_VENTANA_BLOQUEOS', 24),

    'max_intentos_llave' => (int) env('SEGURIDAD_MAX_INTENTOS_LLAVE', 3),

    'max_intentos_recuperacion' => (int) env('SEGURIDAD_MAX_INTENTOS_RECUPERACION', 3),

    'minutos_validez_recuperacion' => (int) env('SEGURIDAD_MINUTOS_VALIDEZ_RECUPERACION', 10),

    'max_accesos_denegados' => (int) env('SEGURIDAD_MAX_ACCESOS_DENEGADOS', 10),

    'minutos_ventana_accesos' => (int) env('SEGURIDAD_MINUTOS_VENTANA_ACCESOS', 10),

    'minutos_inactividad' => (int) env('SEGURIDAD_MINUTOS_INACTIVIDAD', 15),

    'segundos_aviso_inactividad' => (int) env('SEGURIDAD_SEGUNDOS_AVISO_INACTIVIDAD', 60),

    'bitacora' => [
        'habilitada' => (bool) env('SEGURIDAD_BITACORA_HABILITADA', true),

        'registrar_peticiones' => (bool) env('SEGURIDAD_BITACORA_PETICIONES', true),

        'registrar_modelos' => (bool) env('SEGURIDAD_BITACORA_MODELOS', true),

        'max_caracteres_valor' => 1000,

        'max_filas_pdf' => 1000,
    ],

    'ruido' => [
        'metodos' => ['HEAD', 'OPTIONS'],

        'rutas' => [
            'chat.ping',
            'chat.contactos',
            'chat.contacts',
            'chat.fetch',
            'chat.mensajes',
            'notifications.*',
            'media.*',
            'seguridad.sesion.estado',
        ],

        'paths' => [
            'broadcasting/auth',
            'up',
            'favicon.ico',
            'robots.txt',
            'build/*',
            'css/*',
            'js/*',
            'img/*',
            'imagenes/*',
            'storage/*',
            'swipe/*',
            'vendor/*',
            'livewire/livewire.js',
            'livewire/livewire.min.js*',
            'livewire/preview-file/*',
        ],
    ],

    'campos_sensibles' => [
        'password',
        'password_confirmation',
        'current_password',
        'master_key',
        'current_master_key',
        'new_master_key',
        'new_master_key_confirmation',
        'security_questions',
        'remember_token',
        'answers',
        '_token',
        'token',
        'signature',
    ],

    'patrones_sensibles' => ['password', 'token', 'secret', 'master_key', 'security_question'],

    'campos_ignorados_modelos' => [
        'updated_at',
        'ultima_actividad_chat',
        'chat_activo_user_id',
        'last_used_at',
    ],

    'modelos_excluidos' => [
        App\Models\Bitacora::class,
    ],

    'cabeceras' => [
        'habilitadas' => (bool) env('SEGURIDAD_CABECERAS', true),

        'csp_report_only' => env(
            'SEGURIDAD_CSP_REPORT_ONLY',
            "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdn.ckeditor.com https://cdnjs.cloudflare.com https://js.pusher.com; style-src 'self' 'unsafe-inline' https://fonts.bunny.net https://cdnjs.cloudflare.com https://cdn.jsdelivr.net; font-src 'self' data: https://fonts.bunny.net https://cdnjs.cloudflare.com; img-src 'self' data: blob: https:; connect-src 'self' ws: wss: https://*.pusher.com; frame-ancestors 'self'; object-src 'none'; base-uri 'self'; form-action 'self'"
        ),
    ],
];
