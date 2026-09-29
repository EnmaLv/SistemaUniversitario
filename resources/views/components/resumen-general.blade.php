@php
    $modulosData  = $resumenGeneral['modulos'] ?? [];
    $totalModulos = $resumenGeneral['totalModulos'] ?? 0;

    $user   = auth()->user();
    $nombre = $user->persona?->nombre_persona ?? $user->name ?? 'Usuario';
    $hora   = (int) \Carbon\Carbon::now()->format('H');
    $saludo = match (true) {
        $hora >= 5  && $hora < 12 => 'Buenos días',
        $hora >= 12 && $hora < 19 => 'Buenas tardes',
        default                   => 'Buenas noches',
    };
    $iniciales = strtoupper(mb_substr($nombre, 0, 1));

    // Pasos de uso — informativos
    $pasos = [
        [
            'icon'  => 'fa-hand-pointer',
            'title' => 'Selecciona un módulo',
            'desc'  => 'Desde el menú lateral elige el área en la que vas a trabajar. Puedes cambiarla cuando quieras.',
        ],
        [
            'icon'  => 'fa-compass',
            'title' => 'Navega por sus opciones',
            'desc'  => 'Cada módulo tiene sus propias secciones: registros, maestros, movimientos y reportes.',
        ],
        [
            'icon'  => 'fa-comments',
            'title' => 'Comunícate por el chat',
            'desc'  => 'Usa el botón de Mensajes para contactar a otros usuarios del sistema sin salir de la plataforma.',
        ],
        [
            'icon'  => 'fa-bell',
            'title' => 'Atiende las alertas',
            'desc'  => 'Las notificaciones te avisan sobre vencimientos, solicitudes pendientes y tareas importantes.',
        ],
    ];
@endphp

{{-- ============================================================
     HERO DE BIENVENIDA
     ============================================================ --}}
<div class="rounded-3xl p-8 md:p-10 mb-8 text-white shadow-xl relative overflow-hidden"
    style="background: linear-gradient(135deg, var(--color-primary, #dc2626), var(--color-tertiary, #7f1d1d));">

    <div class="absolute -top-1/2 -right-10 w-72 h-72 bg-white/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-24 -left-10 w-64 h-64 bg-white/5 rounded-full blur-3xl pointer-events-none"></div>

    <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
        <div class="flex-1">
            <p class="text-sm font-medium opacity-90 mb-2 tracking-wide">
                {{ $saludo }},
            </p>
            <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight mb-4 leading-tight">
                {{ $nombre }}
            </h1>
            <p class="text-sm md:text-base opacity-90 max-w-2xl leading-relaxed">
                Bienvenido al <strong>Sistema de Gestión de Bienestar Estudiantil</strong> de la UPTP.
                Desde un solo lugar administras el comedor, la atención en salud, el acompañamiento psicológico,
                las becas y el transporte universitario.
            </p>
        </div>

        <div class="flex items-center gap-4 flex-shrink-0">
            <div class="hidden md:block text-right">
                <div class="text-xs opacity-80 mb-1">Hoy es</div>
                <div class="font-bold text-xl leading-tight">
                    {{ \Carbon\Carbon::now()->translatedFormat('d \d\e M') }}
                </div>
                <div class="text-xs opacity-80 capitalize mt-0.5">
                    {{ \Carbon\Carbon::now()->translatedFormat('l, Y') }}
                </div>
            </div>
            <div class="w-16 h-16 rounded-2xl bg-white/20 backdrop-blur-sm border border-white/30 flex items-center justify-center text-2xl font-black shadow-lg flex-shrink-0">
                {{ $iniciales }}
            </div>
        </div>
    </div>
</div>

{{-- ============================================================
     PRIMEROS PASOS
     ============================================================ --}}
<div class="mb-8">
    <div class="flex items-center gap-3 mb-4">
        <div class="w-1 h-6 rounded-full bg-red-600"></div>
        <h2 class="text-lg font-extrabold tracking-tight" style="color: var(--text-main);">
            Cómo empezar
        </h2>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach ($pasos as $i => $paso)
            <div style="background-color: var(--bg-card); border-color: var(--border-color);"
                class="group p-5 rounded-2xl border shadow-sm hover:shadow-md hover:border-red-500/30 transition-all">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-xl bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400 flex items-center justify-center text-base group-hover:scale-110 transition-transform">
                        <i class="fas {{ $paso['icon'] }}"></i>
                    </div>
                    <span class="text-3xl font-black text-red-100 dark:text-red-950/60 leading-none">
                        {{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}
                    </span>
                </div>
                <h3 class="font-bold text-sm mb-1.5" style="color: var(--text-main);">
                    {{ $paso['title'] }}
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                    {{ $paso['desc'] }}
                </p>
            </div>
        @endforeach
    </div>
</div>

{{-- ============================================================
     CATÁLOGO DE MÓDULOS — SOLO REFERENCIA
     ============================================================ --}}
<div class="mb-8">
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-3 mb-4">
        <div class="flex items-center gap-3">
            <div class="w-1 h-6 rounded-full bg-red-600"></div>
            <div>
                <h2 class="text-lg font-extrabold tracking-tight" style="color: var(--text-main);">
                    Módulos del sistema
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    Guía de referencia de cada área de trabajo.
                </p>
            </div>
        </div>

        <span class="inline-flex items-center gap-2 text-[11px] font-bold text-red-700 dark:text-red-300 bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-900/50 px-3 py-1.5 rounded-full self-start md:self-auto">
            <i class="fas fa-arrow-left"></i>
            Cambia de módulo desde el menú lateral
        </span>
    </div>

    <div style="background-color: var(--bg-card); border-color: var(--border-color);"
        class="rounded-2xl border shadow-sm overflow-hidden divide-y divide-gray-100 dark:divide-gray-800">

        @forelse ($modulosData as $key => $mod)
            <div class="p-5 md:p-6 flex flex-col md:flex-row md:items-start gap-4 hover:bg-red-50/30 dark:hover:bg-red-950/10 transition-colors">
                {{-- Icono --}}
                <div class="w-12 h-12 rounded-xl bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400 flex items-center justify-center text-lg flex-shrink-0">
                    <i class="fas {{ $mod['icon'] }}"></i>
                </div>

                {{-- Contenido --}}
                <div class="flex-1 min-w-0">
                    <h3 class="font-extrabold text-base mb-1.5" style="color: var(--text-main);">
                        {{ $mod['nombre'] }}
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed mb-3">
                        {{ $mod['descripcion'] }}
                    </p>

                    {{-- Funcionalidades como pills --}}
                    <div class="flex flex-wrap gap-1.5">
                        @foreach ($mod['funcionalidades'] as $func)
                            <span class="inline-flex items-center gap-1 text-[11px] font-medium text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-white/5 border border-gray-200 dark:border-white/10 px-2.5 py-1 rounded-full">
                                <i class="fas fa-check text-red-500 text-[9px]"></i>
                                {{ $func }}
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>
        @empty
            <div class="p-10 text-center">
                <i class="fas fa-cubes text-4xl text-gray-300 dark:text-gray-600 mb-3"></i>
                <p class="text-sm font-bold text-gray-400">No tienes módulos disponibles asignados.</p>
                <p class="text-xs text-gray-400 mt-1">Contacta al administrador del sistema.</p>
            </div>
        @endforelse
    </div>
</div>

{{-- ============================================================
     CONSEJOS + SOPORTE
     ============================================================ --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- Consejos --}}
    <div style="background-color: var(--bg-card); border-color: var(--border-color);"
        class="lg:col-span-2 rounded-2xl border shadow-sm p-6">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-10 h-10 rounded-xl bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400 flex items-center justify-center">
                <i class="fas fa-lightbulb"></i>
            </div>
            <div>
                <h3 class="font-extrabold text-base" style="color: var(--text-main);">
                    Consejos de uso
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Buenas prácticas para aprovechar mejor el sistema.
                </p>
            </div>
        </div>

        <ul class="space-y-3">
            @foreach ([
                'Mantén tus datos personales actualizados desde tu perfil para que los módulos te reconozcan correctamente.',
                'Revisa la sección de notificaciones con frecuencia para no perderte solicitudes o vencimientos.',
                'Si estás en un módulo y necesitas otro, no cierres sesión: cambia de módulo desde el menú lateral.',
                'Guarda tus cambios antes de navegar a otra sección para evitar perder información.',
                'Si detectas un error en el sistema, repórtalo al área de soporte con el mayor detalle posible.',
            ] as $consejo)
                <li class="flex items-start gap-3">
                    <i class="fas fa-circle-check text-red-500 mt-0.5 text-sm flex-shrink-0"></i>
                    <span class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">{{ $consejo }}</span>
                </li>
            @endforeach
        </ul>
    </div>

    {{-- Soporte --}}
    <div class="rounded-2xl border shadow-sm p-6 text-white relative overflow-hidden"
        style="background: linear-gradient(160deg, var(--color-primary, #dc2626), var(--color-tertiary, #7f1d1d)); border-color: transparent;">

        <div class="absolute -top-10 -right-10 w-40 h-40 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>

        <div class="relative z-10">
            <div class="w-12 h-12 rounded-2xl bg-white/20 backdrop-blur-sm border border-white/30 flex items-center justify-center mb-4">
                <i class="fas fa-headset text-lg"></i>
            </div>

            <h3 class="font-extrabold text-lg mb-2">¿Necesitas ayuda?</h3>
            <p class="text-sm opacity-90 leading-relaxed mb-5">
                Si tienes dudas sobre el uso del sistema o encuentras algún problema,
                contacta al equipo de Bienestar Estudiantil.
            </p>

            <div class="space-y-2.5 text-sm">
                <div class="flex items-center gap-3">
                    <i class="fas fa-envelope w-4 text-center opacity-80"></i>
                    <span class="opacity-95">bienestar@uptp.edu.ve</span>
                </div>
                <div class="flex items-center gap-3">
                    <i class="fas fa-phone w-4 text-center opacity-80"></i>
                    <span class="opacity-95">+58 000-000-0000</span>
                </div>
                <div class="flex items-center gap-3">
                    <i class="fas fa-map-marker-alt w-4 text-center opacity-80"></i>
                    <span class="opacity-95">Oficina de Bienestar Estudiantil</span>
                </div>
            </div>
        </div>
    </div>
</div>