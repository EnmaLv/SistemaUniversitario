@php
    $user   = auth()->user();
    $nombre = $user->persona?->nombre_persona ?? $user->name ?? 'Usuario';
    $hora   = (int) \Carbon\Carbon::now()->format('H');
    $saludo = match (true) {
        $hora >= 5  && $hora < 12 => 'Buenos días',
        $hora >= 12 && $hora < 19 => 'Buenas tardes',
        default                   => 'Buenas noches',
    };
    $iniciales = strtoupper(mb_substr($nombre, 0, 1));

    $totalHoy         = isset($confirmadasHoy) ? $confirmadasHoy->count() : 0;
    $totalPendientes  = isset($citasPendientesAntiguas) ? $citasPendientesAntiguas->count() : 0;
    $realizadasTotal  = $estadisticasCitas['realizada'] ?? 0;
    $tendenciaUltima  = isset($tendenciaPacientes) && $tendenciaPacientes->count() > 0
        ? $tendenciaPacientes->last()['total'] ?? 0
        : 0;
@endphp

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div style="background-color: var(--bg-card); border-color: var(--border-color);"
        class="rounded-2xl border shadow-sm overflow-hidden flex flex-col">
        <div class="p-5 border-b flex items-center justify-between" style="border-color: var(--border-color);">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400 flex items-center justify-center">
                    <i class="fas fa-calendar-check text-sm"></i>
                </div>
                <div>
                    <h3 class="font-extrabold text-sm" style="color: var(--text-main);">Agenda de hoy</h3>
                    <p class="text-[10px] text-gray-400 uppercase tracking-wider font-black">Próximas citas confirmadas</p>
                </div>
            </div>
            <a href="{{ route('admin.psicologia.maestros.agenda.index') }}"
                class="text-xs font-bold text-red-600 hover:text-red-700 dark:text-red-400 inline-flex items-center gap-1">
                Ver agenda <i class="fas fa-arrow-right text-[10px]"></i>
            </a>
        </div>

        <div class="p-4 flex-1">
            @if (isset($confirmadasHoy) && $confirmadasHoy->count() > 0)
                <div class="space-y-2">
                    @foreach ($confirmadasHoy as $cita)
                        @php
                            $colorPrioridad = match (strtolower($cita->prioridad ?? '')) {
                                'alta'                => 'bg-amber-500',
                                'crítica', 'critica'  => 'bg-rose-500',
                                'media'               => 'bg-red-500',
                                'baja'                => 'bg-emerald-500',
                                default               => 'bg-red-500',
                            };
                        @endphp
                        <div class="flex items-center gap-3 p-3 rounded-xl border transition-all hover:translate-x-0.5"
                            style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color);">
                            <span class="w-2 h-2 rounded-full {{ $colorPrioridad }} shrink-0"></span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold truncate" style="color: var(--text-main);">
                                    {{ optional($cita->paciente)->name ?: 'Paciente confirmado' }}
                                </p>
                                <p class="text-[10px] text-gray-400 capitalize">
                                    Prioridad: {{ $cita->prioridad ?? 'media' }}
                                </p>
                            </div>
                            <span class="text-xs font-black font-mono text-red-600 dark:text-red-400">
                                {{ \Carbon\Carbon::parse($cita->hora)->format('H:i') }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-10 text-center">
                    <div class="w-14 h-14 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mb-3">
                        <i class="fas fa-mug-hot text-xl"></i>
                    </div>
                    <p class="text-sm font-bold" style="color: var(--text-main);">Sin citas próximas para hoy</p>
                    <p class="text-xs text-gray-400 mt-1">Disfruta el respiro o revisa tu agenda completa.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- ─────── SOLICITUDES PENDIENTES ─────── --}}
    <div style="background-color: var(--bg-card); border-color: var(--border-color);"
        class="rounded-2xl border shadow-sm overflow-hidden flex flex-col">
        <div class="p-5 border-b flex items-center justify-between" style="border-color: var(--border-color);">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                    <i class="fas fa-hourglass-half text-sm"></i>
                </div>
                <div>
                    <h3 class="font-extrabold text-sm" style="color: var(--text-main);">Solicitudes por revisar</h3>
                    <p class="text-[10px] text-gray-400 uppercase tracking-wider font-black">Las más antiguas primero</p>
                </div>
            </div>
            <a href="{{ route('admin.psicologia.maestros.agenda.index') }}"
                class="text-xs font-bold text-red-600 hover:text-red-700 dark:text-red-400 inline-flex items-center gap-1">
                Atender <i class="fas fa-arrow-right text-[10px]"></i>
            </a>
        </div>

        <div class="p-4 flex-1">
            @if (isset($citasPendientesAntiguas) && $citasPendientesAntiguas->count() > 0)
                <div class="space-y-2">
                    @foreach ($citasPendientesAntiguas as $cita)
                        @php
                            $colorPrioridad = match (strtolower($cita->prioridad ?? '')) {
                                'alta'                => 'bg-amber-500',
                                'crítica', 'critica'  => 'bg-rose-500',
                                'media'               => 'bg-red-500',
                                'baja'                => 'bg-emerald-500',
                                default               => 'bg-red-500',
                            };
                        @endphp
                        <div class="flex items-center gap-3 p-3 rounded-xl border transition-all hover:translate-x-0.5"
                            style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color);">
                            <span class="w-2 h-2 rounded-full {{ $colorPrioridad }} shrink-0"></span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold truncate" style="color: var(--text-main);">
                                    {{ $cita->paciente->persona->nombre_persona ?? 'Paciente' }}
                                </p>
                                <p class="text-[10px] text-gray-400 capitalize">
                                    Prioridad: {{ $cita->prioridad ?? 'media' }}
                                </p>
                            </div>
                            <div class="text-right shrink-0">
                                <p class="text-[10px] text-gray-400 uppercase font-black">Solicitada</p>
                                <p class="text-xs font-black font-mono text-gray-500">
                                    {{ \Carbon\Carbon::parse($cita->created_at)->format('d/m/y') }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-10 text-center">
                    <div class="w-14 h-14 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mb-3">
                        <i class="fas fa-check-double text-xl"></i>
                    </div>
                    <p class="text-sm font-bold" style="color: var(--text-main);">No hay solicitudes pendientes</p>
                    <p class="text-xs text-gray-400 mt-1">Estás al día con las solicitudes de tus pacientes.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<div class="mb-4">
    <div class="flex items-center gap-3 mb-4">
        <div class="w-1 h-6 rounded-full bg-red-600"></div>
        <h2 class="text-lg font-extrabold tracking-tight" style="color: var(--text-main);">
            Accesos rápidos
        </h2>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

        {{-- Mi Agenda --}}
        <a href="{{ route('admin.psicologia.maestros.agenda.index') }}"
            style="background-color: var(--bg-card); border-color: var(--border-color);"
            class="group p-5 rounded-2xl border shadow-sm hover:shadow-md hover:border-red-500/40 transition-all flex flex-col gap-3">
            <div class="w-11 h-11 rounded-xl bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                <i class="fas fa-calendar-alt text-lg"></i>
            </div>
            <div>
                <h3 class="font-extrabold text-sm mb-0.5" style="color: var(--text-main);">Mi Agenda</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                    Gestiona tus citas, confirma, rechaza o pospón solicitudes.
                </p>
            </div>
            <span class="text-[11px] font-bold text-red-600 dark:text-red-400 inline-flex items-center gap-1 mt-auto group-hover:translate-x-1 transition-transform">
                Ir <i class="fas fa-arrow-right text-[9px]"></i>
            </span>
        </a>

        {{-- Historias Clínicas --}}
        <a href="{{ route('admin.psicologia.maestros.historias.index') }}"
            style="background-color: var(--bg-card); border-color: var(--border-color);"
            class="group p-5 rounded-2xl border shadow-sm hover:shadow-md hover:border-red-500/40 transition-all flex flex-col gap-3">
            <div class="w-11 h-11 rounded-xl bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                <i class="fas fa-folder-open text-lg"></i>
            </div>
            <div>
                <h3 class="font-extrabold text-sm mb-0.5" style="color: var(--text-main);">Historias Clínicas</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                    Consulta y actualiza los expedientes de tus pacientes.
                </p>
            </div>
            <span class="text-[11px] font-bold text-red-600 dark:text-red-400 inline-flex items-center gap-1 mt-auto group-hover:translate-x-1 transition-transform">
                Ir <i class="fas fa-arrow-right text-[9px]"></i>
            </span>
        </a>

        {{-- Horarios --}}
        <a href="{{ route('admin.psicologia.maestros.horarios.index') }}"
            style="background-color: var(--bg-card); border-color: var(--border-color);"
            class="group p-5 rounded-2xl border shadow-sm hover:shadow-md hover:border-red-500/40 transition-all flex flex-col gap-3">
            <div class="w-11 h-11 rounded-xl bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                <i class="fas fa-clock text-lg"></i>
            </div>
            <div>
                <h3 class="font-extrabold text-sm mb-0.5" style="color: var(--text-main);">Horarios</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                    Define tus bloques de atención y disponibilidad semanal.
                </p>
            </div>
            <span class="text-[11px] font-bold text-red-600 dark:text-red-400 inline-flex items-center gap-1 mt-auto group-hover:translate-x-1 transition-transform">
                Ir <i class="fas fa-arrow-right text-[9px]"></i>
            </span>
        </a>

        {{-- Reportes --}}
        <a href="{{ route('admin.psicologia.maestros.agenda.estadisticas') }}?format=html"
            style="background-color: var(--bg-card); border-color: var(--border-color);"
            class="group p-5 rounded-2xl border shadow-sm hover:shadow-md hover:border-red-500/40 transition-all flex flex-col gap-3">
            <div class="w-11 h-11 rounded-xl bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                <i class="fas fa-chart-bar text-lg"></i>
            </div>
            <div>
                <h3 class="font-extrabold text-sm mb-0.5" style="color: var(--text-main);">Reportes</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                    Estadísticas de citas, avances clínicos y seguimiento.
                </p>
            </div>
            <span class="text-[11px] font-bold text-red-600 dark:text-red-400 inline-flex items-center gap-1 mt-auto group-hover:translate-x-1 transition-transform">
                Ir <i class="fas fa-arrow-right text-[9px]"></i>
            </span>
        </a>
    </div>
</div>