@php
    $secciones = $administracionData['secciones'] ?? [];

    $tips = [
        'Antes de eliminar un registro, verifica que no esté siendo usado por otros módulos del sistema.',
        'Los cambios en roles y permisos afectan a todos los usuarios con ese rol: hazlos con cuidado.',
        'Mantén los catálogos maestros actualizados: son la base que alimenta al resto de la plataforma.',
        'Usa categorías y tipos de producto para mantener el inventario organizado y las búsquedas rápidas.',
        'Revisa periódicamente el historial de movimientos para detectar irregularidades en el inventario.',
    ];
@endphp

<div class="mb-6">
    <h2 class="text-xl md:text-2xl font-extrabold flex items-center gap-2" style="color: var(--text-main);">
        Administración del Sistema
    </h2>
    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 max-w-3xl">
        Este módulo reúne las herramientas de configuración, los catálogos maestros y los registros
        operativos de toda la plataforma. Desde aquí se administran los datos que alimentan a los
        demás módulos del sistema.
    </p>
</div>

@php
    $sectionColors = [
        'fa-database'                => ['bg' => 'bg-red-50 dark:bg-red-950/40',           'text' => 'text-red-600 dark:text-red-400'],
        'fa-arrow-right-arrow-left'  => ['bg' => 'bg-amber-50 dark:bg-amber-950/40',       'text' => 'text-amber-600 dark:text-amber-400'],
        'fa-sliders'                 => ['bg' => 'bg-sky-50 dark:bg-sky-950/40',           'text' => 'text-sky-600 dark:text-sky-400'],
        'fa-heart-pulse'             => ['bg' => 'bg-emerald-50 dark:bg-emerald-950/40',   'text' => 'text-emerald-600 dark:text-emerald-400'],
    ];
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-10">
    @forelse ($secciones as $sec)
        @php
            $color = $sectionColors[$sec['icon']] ?? ['bg' => 'bg-red-50 dark:bg-red-950/40', 'text' => 'text-red-600 dark:text-red-400'];
        @endphp

        <div style="background-color: var(--bg-card); border-color: var(--border-color);"
            class="rounded-2xl border shadow-sm p-6 hover:shadow-md hover:border-red-500/30 transition-all">

            <div class="flex items-start gap-3 mb-5">
                <div class="w-12 h-12 rounded-xl {{ $color['bg'] }} {{ $color['text'] }} flex items-center justify-center flex-shrink-0">
                    <i class="fas {{ $sec['icon'] }} text-lg"></i>
                </div>
                <div class="min-w-0">
                    <h3 class="font-extrabold text-base" style="color: var(--text-main);">
                        {{ $sec['titulo'] }}
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed mt-0.5">
                        {{ $sec['descripcion'] }}
                    </p>
                </div>
            </div>

            <div class="pt-4 border-t" style="border-color: var(--border-color);">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-wider mb-3">
                    ¿Qué puedes hacer aquí?
                </p>
                <ul class="space-y-2">
                    @foreach ($sec['funcionalidades'] as $func)
                        <li class="flex items-start gap-2.5 text-xs text-gray-600 dark:text-gray-300">
                            <i class="fas fa-check text-red-500 mt-0.5 text-[10px] flex-shrink-0"></i>
                            <span class="leading-relaxed">{{ $func }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @empty
        <div class="md:col-span-2 p-10 text-center rounded-2xl border border-dashed"
            style="background-color: var(--bg-card); border-color: var(--border-color);">
            <i class="fas fa-cog text-4xl text-gray-300 dark:text-gray-600 mb-3"></i>
            <p class="text-sm font-bold text-gray-400">Información no disponible.</p>
        </div>
    @endforelse
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    <div style="background-color: var(--bg-card); border-color: var(--border-color);"
        class="lg:col-span-2 rounded-2xl border shadow-sm p-6">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-10 h-10 rounded-xl bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400 flex items-center justify-center">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
            <div>
                <h3 class="font-extrabold text-base" style="color: var(--text-main);">
                    Buenas prácticas de administración
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Recomendaciones para mantener el sistema estable y consistente.
                </p>
            </div>
        </div>

        <ul class="space-y-3">
            @foreach ($tips as $tip)
                <li class="flex items-start gap-3">
                    <i class="fas fa-circle-check text-red-500 mt-0.5 text-sm flex-shrink-0"></i>
                    <span class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">{{ $tip }}</span>
                </li>
            @endforeach
        </ul>
    </div>

    <div class="rounded-2xl border shadow-sm p-6 text-white relative overflow-hidden"
        style="background: linear-gradient(160deg, var(--color-primary, #dc2626), var(--color-tertiary, #7f1d1d)); border-color: transparent;">

        <div class="absolute -top-10 -right-10 w-40 h-40 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>

        <div class="relative z-10">
            <div class="w-12 h-12 rounded-2xl bg-white/20 backdrop-blur-sm border border-white/30 flex items-center justify-center mb-4">
                <i class="fas fa-screwdriver-wrench text-lg"></i>
            </div>

            <h3 class="font-extrabold text-lg mb-2">Soporte técnico</h3>
            <p class="text-sm opacity-90 leading-relaxed mb-5">
                Si necesitas modificar reglas de negocio, agregar campos o resolver incidencias complejas,
                contacta al equipo de desarrollo.
            </p>

            <div class="space-y-2.5 text-sm">
                <div class="flex items-center gap-3">
                    <i class="fas fa-envelope w-4 text-center opacity-80"></i>
                    <span class="opacity-95">soporte@uptp.edu.ve</span>
                </div>
                <div class="flex items-center gap-3">
                    <i class="fas fa-code w-4 text-center opacity-80"></i>
                    <span class="opacity-95">Equipo de Desarrollo</span>
                </div>
            </div>
        </div>
    </div>
</div>