<x-app-layout>
    <style>
        .invisible-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .invisible-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>

    <div class="py-2">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
            <div
                class="w-full rounded-xl font-medium focus:outline-none focus:ring-2 {{ $focusRingClass ?? 'focus:ring-indigo-500' }} transition-all">
                <div class="p-6 sm:p-8 text-gray-900 dark:text-gray-100">

                    <x-psicologo-selector :psicologos="$psicologos" :psicologoId="$psicologoId" />

                    @include('components.alert')
                    @if (session('error'))
                        <div
                            class="p-4 mb-6 text-sm text-rose-800 dark:text-rose-300 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/60 flex items-center gap-3">
                            <i class="fas fa-exclamation-circle text-rose-600 dark:text-rose-400 text-lg"></i>
                            <span>
                                <strong
                                    class="font-extrabold uppercase tracking-wider text-[10px] block mb-0.5">Error</strong>
                                {{ session('error') }}
                            </span>
                        </div>
                    @endif

                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8 mt-4 w-full">
                        <div class="flex-shrink-0">
                            <h3
                                class="text-3xl font-black text-gray-900 dark:text-white tracking-tight whitespace-nowrap">
                                Mi Agenda
                            </h3>
                            <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                                Gestiona las citas y programación de atención.
                            </p>
                        </div>

                        <div
                            class="flex flex-col md:flex-row md:flex-nowrap items-stretch md:items-center justify-start md:justify-end gap-2 sm:gap-3 w-full md:w-auto overflow-x-auto invisible-scrollbar pb-1">
                            <div
                                class="flex items-center justify-center gap-2.5 bg-sky-600 hover:bg-sky-600/90 text-white px-4 h-12 rounded-2xl shadow-sm flex-shrink-0 w-full md:w-auto transition-all">
                                <svg class="w-4 h-4 opacity-90" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <div class="flex flex-col leading-none text-left">
                                    <span class="text-[8px] font-extrabold uppercase tracking-wider opacity-80">Fecha de
                                        hoy</span>
                                    <span
                                        class="text-[11px] sm:text-[12px] font-extrabold uppercase tracking-wide whitespace-nowrap">
                                        {{ \Carbon\Carbon::now()->locale('es')->isoFormat('ddd DD MMM, YYYY') }}
                                    </span>
                                </div>
                            </div>

                            @if ($view === 'list')
                                <button type="button"
                                    onclick="document.getElementById('filterModal').classList.remove('hidden'); document.getElementById('filterModal').classList.add('flex');"
                                    class="flex items-center justify-center gap-2 bg-sky-600 hover:bg-sky-700 text-white px-4 h-12 rounded-2xl shadow-sm transition-all flex-shrink-0 w-full md:w-auto"
                                    title="Filtrar Fechas">
                                    <svg class="w-4 h-4 opacity-90" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                                    </svg>
                                    <div class="flex flex-col leading-none">
                                        <span
                                            class="text-[12px] font-extrabold uppercase tracking-wider whitespace-nowrap">Filtrar</span>
                                    </div>
                                </button>
                            @endif

                            @if ($view !== 'list')
                                <div style="background-color: var(--bg-card); border-color: var(--border-color); color: var(--text-main);"
                                    class="flex items-center justify-between md:justify-center gap-1 border border-gray-200 dark:border-gray-700/60 p-1 h-12 rounded-2xl shadow-sm flex-shrink-0 w-full md:w-auto">
                                    <a href="{{ route('admin.psicologia.maestros.agenda.index', ['view' => $view, 'date' => ($view === 'month' ? $currentDate->copy()->subMonth() : $currentDate->copy()->subWeek())->toDateString(), 'psicologo_id' => $psicologoId]) }}"
                                        class="w-9 h-9 flex items-center justify-center rounded-xl hover:bg-white dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-all flex-shrink-0">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 19l-7-7 7-7" />
                                        </svg>
                                    </a>
                                    <span
                                        class="px-2 sm:px-4 text-[10px] sm:text-[11px] font-black text-gray-800 dark:text-gray-200 min-w-[110px] sm:min-w-[130px] text-center uppercase tracking-wider leading-none whitespace-nowrap">
                                        @if ($view === 'month')
                                            {{ $currentDate->translatedFormat('F Y') }}
                                        @else
                                            Semana {{ ceil($currentDate->day / 7) }}
                                        @endif
                                    </span>
                                    <a href="{{ route('admin.psicologia.maestros.agenda.index', ['view' => $view, 'date' => ($view === 'month' ? $currentDate->copy()->addMonth() : $currentDate->copy()->addWeek())->toDateString(), 'psicologo_id' => $psicologoId]) }}"
                                        class="w-9 h-9 flex items-center justify-center rounded-xl hover:bg-white dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-all flex-shrink-0">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>
                                </div>
                            @endif

                            <div style="background-color: var(--bg-card); border-color: var(--border-color); color: var(--text-main);"
                                class="border border-gray-200/60 dark:border-gray-700/60 p-1 h-12 rounded-2xl flex items-center justify-center md:justify-start gap-1 flex-shrink-0 w-full md:w-auto">
                                <a href="{{ route('admin.psicologia.maestros.agenda.index', ['view' => 'month', 'date' => $currentDate->toDateString(), 'psicologo_id' => $psicologoId]) }}"
                                    class="flex-1 md:flex-none px-3 sm:px-4 h-9 flex items-center justify-center rounded-xl text-[9px] sm:text-[10px] font-black uppercase tracking-wider transition-all whitespace-nowrap {{ $view === 'month' ? 'bg-sky-50 dark:bg-sky-950/40 text-sky-600 dark:text-sky-400 hover:bg-sky-100 dark:hover:bg-sky-900/50 border border-sky-100 dark:border-sky-800/40' : 'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200' }}"
                                    title="Vista Mensual">Mes</a>

                                <a href="{{ route('admin.psicologia.maestros.agenda.index', ['view' => 'week', 'date' => $currentDate->toDateString(), 'psicologo_id' => $psicologoId]) }}"
                                    class="flex-1 md:flex-none px-3 sm:px-4 h-9 flex items-center justify-center rounded-xl text-[9px] sm:text-[10px] font-black uppercase tracking-wider transition-all whitespace-nowrap {{ $view === 'week' ? 'bg-sky-50 dark:bg-sky-950/40 text-sky-600 dark:text-sky-400 hover:bg-sky-100 dark:hover:bg-sky-900/50 border border-sky-100 dark:border-sky-800/40' : 'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200' }}"
                                    title="Vista Semanal">Semana</a>

                                <a href="{{ route('admin.psicologia.maestros.agenda.index', ['view' => 'list', 'date' => $currentDate->toDateString(), 'psicologo_id' => $psicologoId]) }}"
                                    class="px-3 h-9 flex items-center justify-center rounded-xl transition-all flex-shrink-0 {{ $view === 'list' ? 'bg-sky-50 dark:bg-sky-950/40 text-sky-600 dark:text-sky-400 hover:bg-sky-100 dark:hover:bg-sky-900/50 border border-sky-100 dark:border-sky-800/40' : 'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200' }}"
                                    title="Historial de Sesiones">
                                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </a>

                                <a href="{{ route('admin.psicologia.maestros.agenda.estadisticas', ['format' => 'html', 'psicologo_id' => $psicologoId]) }}"
                                    class="px-3 h-9 flex items-center justify-center rounded-xl text-gray-400 hover:bg-sky-100 dark:hover:bg-sky-900/50 transition-all font-black text-[10px] uppercase tracking-wider gap-1.5 flex-shrink-0"
                                    title="Panel Estadístico">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                </a>

                                <a href="{{ route('admin.psicologia.maestros.agenda.exportarPdf', ['view' => $view, 'date' => $currentDate->toDateString(), 'psicologo_id' => $psicologoId]) }}"
                                    target="_blank"
                                    class="px-3 h-9 flex items-center justify-center rounded-xl text-gray-500 dark:text-gray-400 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition-all font-black tracking-wider gap-1.5 flex-shrink-0"
                                    title="Imprimir Agenda en PDF">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div
                        class="grid grid-cols-1 {{ $view === 'week' ? 'xl:grid-cols-4' : '' }} gap-8 flex-col-reverse xl:flex-row">
                        @if ($view === 'week')
                            <aside id="pendingRequestsPanel"
                                style="background-color: var(--bg-card); border-color: var(--border-color); color: var(--text-main);"
                                class="xl:col-span-1 bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700/60 p-6 shadow-sm order-2 xl:order-1 flex flex-col max-h-[calc(100vh-250px)] overflow-hidden">
                                <div class="flex items-center gap-3 mb-6">
                                    <h3
                                        class="text-lg font-black text-gray-900 dark:text-white tracking-tight uppercase tracking-wider">
                                        Pendientes</h3>
                                </div>

                                <div class="space-y-4 mb-6">
                                    <div class="relative">
                                        <label
                                            class="block mb-1.5 text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-wider"
                                            for="pendingFilter">Paciente</label>
                                        <input id="pendingFilter" type="text"
                                            class="w-full rounded-2xl border border-gray-200 dark:border-gray-700/60 bg-gray-50 dark:bg-gray-900/50 px-4 py-2.5 text-sm focus:border-sky-600 focus:ring-2 focus:ring-sky-600/20 transition-all placeholder-gray-400 dark:placeholder-gray-500 font-medium text-gray-900 dark:text-white pr-10"
                                            placeholder="Buscar..." />
                                        <div id="searchSpinner" class="absolute right-3 top-[34px] hidden">
                                            <svg class="animate-spin h-4 w-4 text-sky-600"
                                                xmlns="http://www.w3.org/2000/svg" fill="none"
                                                viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                                    stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor"
                                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                </path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div>
                                        <label
                                            class="block mb-1.5 text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-wider"
                                            for="priorityFilter">Prioridad</label>
                                        <select id="priorityFilter"
                                            class="w-full rounded-2xl border border-gray-200 dark:border-gray-700/60 bg-gray-50 dark:bg-gray-900/50 px-4 py-2.5 text-sm focus:border-sky-600 focus:ring-2 focus:ring-sky-600/20 transition-all font-medium text-gray-900 dark:text-white">
                                            <option value="">Todas</option>
                                            @foreach ($prioridadesDisponibles as $prioridad)
                                                <option value="{{ $prioridad->nombre }}">
                                                    {{ ucfirst($prioridad->nombre) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                @include('admin.psicologia.maestros.agenda.components.pending-list')
                            </aside>
                        @endif

                        <section
                            class="{{ $view === 'week' ? 'xl:col-span-3' : 'w-full' }} relative order-1 xl:order-2">
                            <div id="agendaMainView" class="transition-all duration-300 w-full rounded-3xl">
                                @if ($view === 'month')
                                    <div
                                        class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700/60 shadow-sm overflow-x-auto overflow-y-auto max-h-[calc(100vh-250px)] invisible-scrollbar relative">
                                        <div class="min-w-[700px]">
                                            <div style="background-color: var(--bg-card); border-color: var(--border-color); color: var(--text-main);"
                                                class="grid grid-cols-7 border-b border-gray-200 dark:border-gray-700/60 bg-gray-50/90 dark:bg-gray-800/90 backdrop-blur-md sticky top-0 z-10">
                                                @foreach (['DOM', 'LUN', 'MAR', 'MIÉ', 'JUE', 'VIE', 'SÁ'] as $diaLabel)
                                                    <div
                                                        class="py-4 text-center text-[10px] bg-gray-200/50 dark:bg-black/20 font-black text-gray-600 dark:text-gray-400 uppercase tracking-wider">
                                                        {{ $diaLabel }}</div>
                                                @endforeach
                                            </div>
                                            <div style="background-color: var(--bg-card); border-color: var(--border-color); color: var(--text-main);"
                                                class="grid grid-cols-7">
                                                @foreach ($calendarioData as $data)
                                                    <div onclick="openDailyAgenda(this, '{{ $data['date'] }}')"
                                                        class="min-h-[120px] p-2 border-b border-r border-gray-100 dark:border-gray-700/40 relative group cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-700/30 transition-all {{ !$data['isCurrentMonth'] ? 'bg-gray-200/30 dark:bg-gray-900/80' : '' }} {{ $data['isToday'] ? 'bg-sky-200/70 dark:bg-sky-600/10 hover:bg-sky-200 dark:hover:bg-sky-700/30' : '' }}"
                                                        data-date="{{ $data['date'] }}">
                                                        <div class="flex justify-between items-start mb-2">
                                                            <span
                                                                class="text-xs font-black {{ $data['isToday'] ? 'w-7 h-7 bg-sky-600 text-white rounded-xl flex items-center justify-center shadow-sm' : ($data['isCurrentMonth'] ? 'text-gray-900 dark:text-gray-200' : 'text-gray-300 dark:text-gray-600') }}">
                                                                {{ $data['day'] }}
                                                            </span>
                                                        </div>
                                                        <div
                                                            class="space-y-1 overflow-y-auto max-h-[80px] custom-scrollbar pointer-events-none">
                                                            @foreach ($data['citas']->where('estado', '!=', 'cancelada') as $cita)
                                                                <div
                                                                    class="px-2 py-1 rounded-lg text-[9px] font-extrabold truncate
                                                                    {{ $cita->estado === 'confirmada'
                                                                        ? 'bg-sky-50 dark:bg-sky-950/40 text-sky-600 dark:text-sky-400 border border-sky-200 dark:border-sky-800/60'
                                                                        : ($cita->estado === 'realizada'
                                                                            ? 'bg-sky-100 dark:bg-sky-950/40 text-sky-600 dark:text-sky-400 border border-sky-200 dark:border-sky-800/60'
                                                                            : ($cita->estado === 'no_asistio'
                                                                                ? 'bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-800/60'
                                                                                : 'bg-gray-100 dark:bg-gray-700/60 text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-600')) }}">
                                                                    {{ $cita->hora ? \Carbon\Carbon::parse($cita->hora)->format('g:i A') : 'S/H' }}
                                                                    - {{ $cita->paciente->persona->nombre_persona }}
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @elseif ($view === 'list')
                                    <div style="background-color: var(--bg-card); border-color: var(--border-color); color: var(--text-main);"
                                        class="rounded-2xl border shadow-sm overflow-hidden p-6 sm:p-8">
                                        <div
                                            class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-6">
                                            <h3 class="text-xl sm:text-2xl font-extrabold tracking-tight"
                                                style="color: var(--text-main);">
                                                Historial de Citas
                                            </h3>
                                            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl text-xs font-bold border border-gray-200 dark:border-gray-700/60 text-gray-500 dark:text-gray-400"
                                                style="background-color: rgba(0,0,0,0.02);">
                                                <i class="fas fa-list-check text-xs"></i>
                                                <span>Total: {{ $citasCalendario->total() }} registros</span>
                                            </div>
                                        </div>

                                        <div
                                            class="overflow-x-auto overflow-y-auto max-h-[calc(100vh-250px)] rounded-xl relative">
                                            <table class="min-w-[600px] w-full text-left border-collapse">
                                                <thead class="sticky top-0 z-10 shadow-sm"
                                                    style="background-color: var(--bg-card);">
                                                    <tr class="border-b border-gray-100 dark:border-gray-800">
                                                        <th
                                                            class="pb-3 text-[10px] font-black uppercase tracking-wider text-gray-400">
                                                            Paciente</th>
                                                        <th
                                                            class="pb-3 text-[10px] font-black uppercase tracking-wider text-gray-400">
                                                            Solicitada</th>
                                                        <th
                                                            class="pb-3 text-[10px] font-black uppercase tracking-wider text-gray-400">
                                                            Fecha y Hora</th>
                                                        <th
                                                            class="pb-3 text-[10px] font-black uppercase tracking-wider text-gray-400">
                                                            Estado</th>
                                                        <th class="pb-3"></th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800/60">
                                                    @forelse($citasCalendario as $cita)
                                                        <tr
                                                            class="group hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition-colors">
                                                            <td class="py-4">
                                                                <div class="flex items-center gap-3">
                                                                    <div class="w-9 h-9 text-{{ $themeColor ?? 'indigo' }}-600 dark:text-{{ $themeColor ?? 'indigo' }}-400 border border-{{ $themeColor ?? 'indigo' }}-200 dark:border-{{ $themeColor ?? 'indigo me' }}-800/40 rounded-xl flex items-center justify-center text-xs font-black uppercase"
                                                                        style="background-color: rgba(0,0,0,0.02);">
                                                                        {{ substr($cita->paciente->persona->nombre_persona, 0, 1) }}
                                                                    </div>
                                                                    <span class="text-sm font-bold"
                                                                        style="color: var(--text-main);">
                                                                        {{ $cita->paciente->persona->nombre_persona }}
                                                                    </span>
                                                                </div>
                                                            </td>
                                                            <td class="py-4">
                                                                <div class="flex flex-col">
                                                                    <span class="text-xs font-bold"
                                                                        style="color: var(--text-main);">
                                                                        {{ $cita->created_at ? \Carbon\Carbon::parse($cita->created_at)->translatedFormat('d M, Y') : 'N/A' }}
                                                                    </span>
                                                                    <span
                                                                        class="text-[10px] font-bold text-gray-400 tracking-wider">
                                                                        {{ $cita->created_at ? \Carbon\Carbon::parse($cita->created_at)->format('g:i A') : '' }}
                                                                    </span>
                                                                </div>
                                                            </td>
                                                            <td class="py-4">
                                                                <div class="flex flex-col">
                                                                    @if (!$cita->hora)
                                                                        <span
                                                                            class="text-xs font-bold text-gray-400 italic">Sin
                                                                            horario asignado</span>
                                                                    @else
                                                                        <span class="text-xs font-bold"
                                                                            style="color: var(--text-main);">
                                                                            {{ $cita->fecha ? $cita->fecha->translatedFormat('d M, Y') : 'Sin fecha' }}
                                                                        </span>
                                                                        <span
                                                                            class="text-[10px] font-bold text-gray-400 tracking-wider">
                                                                            {{ \Carbon\Carbon::parse($cita->hora)->format('g:i A') }}
                                                                        </span>
                                                                    @endif
                                                                </div>
                                                            </td>
                                                            <td class="py-4">
                                                                <span
                                                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-[10px] font-bold border uppercase tracking-wider
                                                                    {{ $cita->estado === 'realizada'
                                                                        ? 'border-sky-200 dark:border-sky-800 bg-sky-50 dark:bg-sky-950/40 text-sky-600 dark:text-sky-400'
                                                                        : ($cita->estado === 'cancelada'
                                                                            ? 'border-rose-200 dark:border-rose-800 bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400'
                                                                            : ($cita->estado === 'rechazada'
                                                                                ? 'border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400'
                                                                                : ($cita->estado === 'confirmada'
                                                                                    ? 'border-sky-200 dark:border-sky-800 bg-sky-50 dark:bg-sky-950/40 text-sky-600 dark:text-sky-400'
                                                                                    : ($cita->estado === 'no_asistio'
                                                                                        ? 'border-rose-200 dark:border-rose-800 bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400'
                                                                                        : 'border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-400')))) }}">
                                                                    <i class="fas fa-circle text-[6px]"></i>
                                                                    {{ str_replace('_', ' ', $cita->estado) }}
                                                                </span>
                                                            </td>
                                                            <td class="py-4 text-right">
                                                                <button type="button"
                                                                    onclick="abrirDetalleCita({{ json_encode([
                                                                        'id' => $cita->id,
                                                                        'paciente' => $cita->paciente->persona->nombre_persona,
                                                                        'estado' => $cita->estado,
                                                                        'motivo' => $cita->motivo ?? 'No especificado',
                                                                        'prioridad' => $cita->prioridad ?? 'Normal',
                                                                        'fecha_solicitud' => $cita->created_at
                                                                            ? \Carbon\Carbon::parse($cita->created_at)->translatedFormat('d M, Y - g:i A')
                                                                            : 'N/A',
                                                                        'fecha_programada' =>
                                                                            $cita->fecha && $cita->hora
                                                                                ? $cita->fecha->translatedFormat('d M, Y') . ' - ' . \Carbon\Carbon::parse($cita->hora)->format('g:i A')
                                                                                : 'Sin horario asignado',
                                                                        'fecha_programada_iso' => $cita->fecha ? $cita->fecha->format('Y-m-d') : null,
                                                                        'hora_programada_iso' => $cita->hora ? \Carbon\Carbon::parse($cita->hora)->format('H:i') : null,
                                                                        'cancelado_por' => $cita->cancelado_por ?? null,
                                                                        'motivo_rechazo' => $cita->motivo_rechazo_propuesta ?? null,
                                                                    ]) }})"
                                                                    class="w-9 h-9 rounded-xl border border-gray-200 dark:border-gray-700/60 text-gray-400 hover:text-{{ $themeColor ?? 'indigo' }}-600 hover:border-{{ $themeColor ?? 'indigo' }}-300 inline-flex items-center justify-center transition-all active:scale-95">
                                                                    <i class="fas fa-eye text-xs"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="5"
                                                                class="py-12 text-center text-gray-400 font-bold text-xs uppercase tracking-wider">
                                                                <i
                                                                    class="fas fa-inbox text-2xl block mb-2 opacity-50"></i>
                                                                Historial de citas vacío
                                                            </td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>

                                        @if ($citasCalendario->lastPage() > 1)
                                            <div class="mt-8 flex justify-center">
                                                {{ $citasCalendario->appends(request()->query())->links('admin.psicologia.maestros.agenda.partials.pagination') }}
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    @if (isset($grupoActivo) && $horarios->isNotEmpty())
                                        @php
                                            $currentDate =
                                                $currentDate->dayOfWeek === \Carbon\Carbon::SUNDAY
                                                    ? $currentDate->copy()->next(\Carbon\Carbon::MONDAY)
                                                    : $currentDate->copy()->startOfWeek(\Carbon\Carbon::MONDAY);

                                            $normalizeBlock = function ($text) {
                                                $value = trim($text ?? '');
                                                $value = preg_replace_callback(
                                                    '/(\d{1,2}):(\d{2})\s*(am|pm)\b/i',
                                                    function ($matches) {
                                                        $hours = (int) $matches[1];
                                                        $ampm = strtolower($matches[3]);
                                                        if ($ampm === 'pm' && $hours < 12) {
                                                            $hours += 12;
                                                        }
                                                        if ($ampm === 'am' && $hours === 12) {
                                                            $hours = 0;
                                                        }
                                                        return sprintf('%02d:%s', $hours, $matches[2]);
                                                    },
                                                    $value,
                                                );
                                                $value = preg_replace(
                                                    ['/\s*[-–—]\s*/u', '/(\d{1,2}:\d{2}):\d{2}/', '/\s+/'],
                                                    ['-', '$1', ' '],
                                                    $value,
                                                );
                                                $value = preg_replace('/(^|\s|-)(\d):/', '${1}0$2:', $value);
                                                return strtolower($value);
                                            };

                                            $dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];

                                            // === BLOQUES UNIFICADOS (idénticos a los del formulario de horarios) ===
                                            $intervalos = collect(\App\Models\salud\HorarioConsultorio::BLOQUES)
                                                ->flatMap(function ($bloques, $jornada) {
                                                    return collect($bloques)->map(function ($b) use ($jornada) {
                                                        return [
                                                            'inicio' => $b['inicio'],
                                                            'fin' => $b['fin'],
                                                            'seccion' => $jornada,
                                                        ];
                                                    });
                                                })
                                                ->sortBy(fn($i) => \Carbon\Carbon::parse($i['inicio'])->timestamp)
                                                ->values()
                                                ->all();
                                        @endphp

                                        <div style="background-color: var(--bg-card); border-color: var(--border-color);"
                                            class="overflow-x-auto overflow-y-auto max-h-[calc(100vh-250px)] rounded-3xl border border-gray-200 dark:border-gray-700/60 bg-white dark:bg-gray-800 shadow-sm invisible-scrollbar relative">
                                            <table
                                                class="min-w-[800px] w-full divide-y divide-gray-300 dark:divide-gray-700/60 text-sm bg-gray-100/40 dark:bg-black/10">
                                                <thead
                                                    class="px-4 py-2 text-[10px] bg-gray-200/40 dark:bg-black/20 font-black uppercase tracking-wider text-center">
                                                    <tr>
                                                        <th
                                                            class="px-4 py-4 text-center text-[10px] font-black text-gray-900 dark:text-gray-300 uppercase tracking-wider border-r border-gray-300 dark:border-gray-700/60">
                                                            Hora</th>
                                                        @foreach ($dias as $diaHeaderIndex => $dia)
                                                            @php
                                                                $fechaColumna = $currentDate
                                                                    ->copy()
                                                                    ->addDays($diaHeaderIndex);
                                                                $esHoy = $fechaColumna->isToday();
                                                            @endphp
                                                            <th
                                                                class="px-4 py-3 text-center uppercase tracking-wider {{ $esHoy ? 'bg-sky-100/90 dark:bg-sky-950/30' : '' }}">
                                                                <div class="flex flex-col items-center gap-1">
                                                                    <span
                                                                        class="text-[10px] font-black {{ $esHoy ? 'text-sky-600 dark:text-sky-400' : 'text-gray-900 dark:text-gray-300' }} tracking-wider">{{ $dia }}</span>
                                                                    <span
                                                                        class="{{ $esHoy ? 'w-7 h-7 bg-sky-600 dark:bg-sky-700/30 text-white rounded-xl flex items-center justify-center text-[11px] font-black shadow-sm' : 'text-[13px] font-black text-gray-800 dark:text-gray-200' }}">
                                                                        {{ $fechaColumna->day }}
                                                                    </span>
                                                                </div>
                                                            </th>
                                                        @endforeach
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-gray-300 dark:divide-gray-700/60">
                                                    @php $sectionActual = null; @endphp
                                                    @foreach ($intervalos as $intervalo)
                                                        @php
                                                            $seccion = $intervalo['seccion'];
                                                        @endphp

                                                        @if ($sectionActual !== $seccion)
                                                            <tr class="bg-gray-100/50 dark:bg-gray-900/40">
                                                                <td colspan="6"
                                                                    class="px-4 py-2 text-[11px] bg-gray-200/40 dark:bg-black/20 border-b border-gray-300 dark:border-gray-700/60 font-black uppercase tracking-wider text-center">
                                                                    {{ $seccion }}</td>
                                                            </tr>
                                                            @php $sectionActual = $seccion; @endphp
                                                        @endif

                                                        <tr class="disease-row">
                                                            <td
                                                                class="px-4 py-4 text-center text-[10px] font-black text-gray-600 dark:text-gray-400 border-r border-gray-300 dark:border-gray-700/60">
                                                                {{ \Carbon\Carbon::parse($intervalo['inicio'])->format('g:i') }}
                                                                -
                                                                {{ \Carbon\Carbon::parse($intervalo['fin'])->format('g:i') }}
                                                            </td>

                                                            @foreach ($dias as $diaIndex => $dia)
                                                                @php
                                                                    $horaInicio = $intervalo['inicio'];
                                                                    $horaFin = $intervalo['fin'];

                                                                    $horarioBloque = $horarios
                                                                        ->where('dia', $dia)
                                                                        ->first(function ($h) use (
                                                                            $horaInicio,
                                                                            $horaFin,
                                                                        ) {
                                                                            $hInicio = \Carbon\Carbon::parse(
                                                                                $h->hora_inicio,
                                                                            );
                                                                            $hFin = \Carbon\Carbon::parse($h->hora_fin);
                                                                            $iInicio = \Carbon\Carbon::parse(
                                                                                $horaInicio,
                                                                            );
                                                                            $iFin = \Carbon\Carbon::parse($horaFin);
                                                                            // El bloque se superpone con el intervalo (sin contar bordes exactos)
                                                                            return $hInicio->lt($iFin) &&
                                                                                $hFin->gt($iInicio);
                                                                        });

                                                                    $bloqueLabel = $horarioBloque
                                                                        ? $dia .
                                                                            ' ' .
                                                                            \Carbon\Carbon::parse(
                                                                                $horarioBloque->hora_inicio,
                                                                            )->format('H:i') .
                                                                            ' - ' .
                                                                            \Carbon\Carbon::parse(
                                                                                $horarioBloque->hora_fin,
                                                                            )->format('H:i')
                                                                        : "$dia $horaInicio - $horaFin";
                                                                    $normalizedSlotText = $normalizeBlock($bloqueLabel);
                                                                    $fechaDelDia = $currentDate
                                                                        ->copy()
                                                                        ->addDays($diaIndex)
                                                                        ->toDateString();

                                                                    $citasConfirmadasEnSlot = $citasCalendario->filter(
                                                                        fn($cita) => in_array($cita->estado, [
                                                                            'confirmada',
                                                                            'realizada',
                                                                            'no_asistio',
                                                                            'pendiente',
                                                                        ]) &&
                                                                            $cita->fecha &&
                                                                            $cita->fecha->isSameDay($fechaDelDia) &&
                                                                            (($cita->bloque_propuesto &&
                                                                                str_contains(
                                                                                    $normalizeBlock(
                                                                                        $cita->bloque_propuesto,
                                                                                    ),
                                                                                    $normalizedSlotText,
                                                                                )) ||
                                                                                ($cita->hora &&
                                                                                    \Carbon\Carbon::parse(
                                                                                        $cita->hora,
                                                                                    )->format('H:i') === $horaInicio)),
                                                                    );
                                                                    $assignedCita = $citasConfirmadasEnSlot->first();

                                                                    $canceladaCita = $citasCalendario
                                                                        ->filter(
                                                                            fn($cita) => $cita->estado ===
                                                                                'cancelada' &&
                                                                                $cita->fecha &&
                                                                                $cita->fecha->isSameDay($fechaDelDia) &&
                                                                                $cita->bloque_propuesto &&
                                                                                str_contains(
                                                                                    $normalizeBlock(
                                                                                        $cita->bloque_propuesto,
                                                                                    ),
                                                                                    $normalizedSlotText,
                                                                                ),
                                                                        )
                                                                        ->sortByDesc('updated_at')
                                                                        ->first();

                                                                    $citasEnSlot = $citasPendientes->filter(function (
                                                                        $cita,
                                                                    ) use (
                                                                        $normalizedSlotText,
                                                                        $normalizeBlock,
                                                                        $dia,
                                                                        $horaInicio,
                                                                        $horaFin,
                                                                        $fechaDelDia,
                                                                    ) {
                                                                        if (
                                                                            $cita->fecha &&
                                                                            \Carbon\Carbon::parse(
                                                                                $cita->fecha,
                                                                            )->isSameDay($fechaDelDia)
                                                                        ) {
                                                                            if (
                                                                                $cita->hora &&
                                                                                \Carbon\Carbon::parse(
                                                                                    $cita->hora,
                                                                                )->format('H:i') === $horaInicio
                                                                            ) {
                                                                                return true;
                                                                            }
                                                                            if (
                                                                                $cita->bloque_propuesto &&
                                                                                str_contains(
                                                                                    $normalizeBlock(
                                                                                        $cita->bloque_propuesto,
                                                                                    ),
                                                                                    $normalizedSlotText,
                                                                                )
                                                                            ) {
                                                                                return true;
                                                                            }
                                                                        }
                                                                        if (
                                                                            !$cita->bloques_sugeridos ||
                                                                            $cita->bloques_sugeridos === 'x'
                                                                        ) {
                                                                            return false;
                                                                        }

                                                                        $raw = $cita->bloques_sugeridos;
                                                                        $excepcionesStr = '';
                                                                        $horariosStr = $raw;
                                                                        if (str_contains($raw, '|')) {
                                                                            $parts = explode('|', $raw);
                                                                            $leftPart = trim($parts[0]);
                                                                            $rightPart = trim($parts[1]);
                                                                            if (
                                                                                str_contains($leftPart, 'exceptuados')
                                                                            ) {
                                                                                $excepcionesStr = trim(
                                                                                    str_replace(
                                                                                        'Días exceptuados:',
                                                                                        '',
                                                                                        $leftPart,
                                                                                    ),
                                                                                );
                                                                            }
                                                                            $horariosStr = preg_replace(
                                                                                '/^\s*Horarios\s*(propuestos)?\s*:\s*/i',
                                                                                '',
                                                                                $rightPart,
                                                                            );
                                                                        }
                                                                        if ($excepcionesStr !== '') {
                                                                            $excepcionesArray = array_map(
                                                                                'trim',
                                                                                explode(',', $excepcionesStr),
                                                                            );
                                                                            if (
                                                                                in_array(
                                                                                    $fechaDelDia,
                                                                                    $excepcionesArray,
                                                                                )
                                                                            ) {
                                                                                return false;
                                                                            }
                                                                        }
                                                                        $bloques = array_filter(
                                                                            array_map(
                                                                                'trim',
                                                                                explode(
                                                                                    ';',
                                                                                    str_replace(',', ';', $horariosStr),
                                                                                ),
                                                                            ),
                                                                        );
                                                                        foreach ($bloques as $bloque) {
                                                                            if (
                                                                                str_contains($bloque, $fechaDelDia) ||
                                                                                str_contains(
                                                                                    $normalizeBlock($bloque),
                                                                                    strtolower($dia),
                                                                                )
                                                                            ) {
                                                                                if (
                                                                                    preg_match(
                                                                                        '/(\d{1,2}:\d{2}(?:\s*[aApP][mM])?)\s*[-\x96\x97]\s*(\d{1,2}:\d{2}(?:\s*[aApP][mM])?)/i',
                                                                                        $bloque,
                                                                                        $m,
                                                                                    )
                                                                                ) {
                                                                                    $sI = \Carbon\Carbon::parse($m[1]);
                                                                                    $sF = \Carbon\Carbon::parse($m[2]);
                                                                                    if (
                                                                                        \Carbon\Carbon::parse(
                                                                                            $horaInicio,
                                                                                        )->lt($sF) &&
                                                                                        \Carbon\Carbon::parse(
                                                                                            $horaFin,
                                                                                        )->gt($sI)
                                                                                    ) {
                                                                                        return true;
                                                                                    }
                                                                                }
                                                                            }
                                                                        }
                                                                        return false;
                                                                    });
                                                                @endphp

                                                                <td class="px-2 py-3">
                                                                    @if ($horarioBloque)
                                                                        <button type="button"
                                                                            class="block-slot-button w-full p-3 text-center transition-all drop-zone group bg-gray-100/10 dark:bg-gray-900/10
                                                                                        {{ $assignedCita
                                                                                            ? 'bg-sky-50 dark:bg-sky-950/40 border-sky-200 dark:border-sky-800/60 text-sky-600 dark:text-sky-400 shadow-sm'
                                                                                            : ($horarioBloque->activo === \App\Models\salud\Horario::STATUS_ACTIVE
                                                                                                ? 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700/60 text-gray-900 dark:text-gray-300 hover:border-sky-300 dark:hover:border-sky-700 hover:shadow-sm'
                                                                                                : 'bg-amber-50/50 dark:bg-amber-950/30 border-amber-200/80 dark:border-amber-800/50 text-amber-700 dark:text-amber-400 hover:border-amber-300 dark:hover:border-amber-700') }}"
                                                                            data-block-label="{{ $bloqueLabel }}"
                                                                            data-block-date="{{ $fechaDelDia }}"
                                                                            data-block-time="{{ $horarioBloque->hora_inicio }}"
                                                                            data-block-active="{{ $horarioBloque->activo === \App\Models\salud\Horario::STATUS_ACTIVE ? 'true' : 'false' }}"
                                                                            data-pending-requests="{{ json_encode($citasEnSlot->map(fn($c) => ['id' => $c->id, 'paciente' => $c->paciente->persona->nombre_persona ?? ($c->paciente->persona->nombre_persona ?? 'Paciente'), 'motivo' => $c->motivo ?? 'Solicitud pendiente', 'estado' => $c->estado ?? 'pendiente'])->values()) }}"
                                                                            @if ($assignedCita) data-assigned-cita-id="{{ $assignedCita->id }}"
                                                                                        data-assigned-paciente="{{ $assignedCita->paciente->persona->nombre_persona }} {{ $assignedCita->paciente->persona->apellido_persona }}"
                                                                                        data-assigned-estado="{{ $assignedCita->estado }}"
                                                                                        data-assigned-block="true" @endif>

                                                                            <div
                                                                                class="flex items-center justify-center mb-1">
                                                                                <span
                                                                                    class="text-[10px] font-black uppercase tracking-wider opacity-55 group-hover:opacity-100 transition-opacity text-gray-900 dark:text-gray-400">
                                                                                    {{ \Carbon\Carbon::parse($horarioBloque->hora_inicio)->format('g:i') }}
                                                                                    -
                                                                                    {{ \Carbon\Carbon::parse($horarioBloque->hora_fin)->format('g:i') }}
                                                                                </span>
                                                                            </div>

                                                                            <div
                                                                                class="block-slot-status flex flex-col items-center gap-0.5">
                                                                                @if ($assignedCita)
                                                                                    <div
                                                                                        class="flex items-center gap-1">
                                                                                        @if ($assignedCita->estado === 'realizada')
                                                                                            <span
                                                                                                class="w-1.5 h-1.5 rounded-full bg-sky-500"></span>
                                                                                            @elseif ($assignedCita->estado === 'no_asistio')
                                                                                            <span
                                                                                                class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                                                        @else
                                                                                            <span
                                                                                                class="w-1.5 h-1.5 rounded-full bg-sky-600"></span>
                                                                                        @endif
                                                                                        <p
                                                                                            class="text-[10px] font-black leading-tight truncate text-sky-600 dark:text-sky-400">
                                                                                            {{ $assignedCita->paciente->persona->nombre_persona }}
                                                                                            {{ $assignedCita->paciente->persona->apellido_persona }}
                                                                                        </p>
                                                                                    </div>
                                                                                @else
                                                                                    @if ($citasEnSlot->isNotEmpty())
                                                                                        <div
                                                                                            class="flex items-center gap-1">
                                                                                            <span
                                                                                                class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                                                            <p
                                                                                                class="text-[10px] font-black text-amber-600 dark:text-amber-400">
                                                                                                {{ $citasEnSlot->count() }}
                                                                                                Solic.</p>
                                                                                        </div>
                                                                                    @else
                                                                                        <p
                                                                                            class="text-[9px] font-extrabold text-gray-300 dark:text-gray-600 group-hover:text-gray-400 dark:group-hover:text-gray-500 uppercase tracking-wider">
                                                                                            Libre</p>
                                                                                    @endif

                                                                                    @if ($canceladaCita)
                                                                                        <div class="flex items-center justify-center gap-1 w-full mt-0.5"
                                                                                            onclick="event.stopPropagation()">
                                                                                            <span
                                                                                                class="w-1.5 h-1.5 rounded-full bg-gray-400 flex-shrink-0"
                                                                                                title="{{ $canceladaCita->cancelado_por === 'paciente' ? 'Cancelado por el paciente' : 'Cancelada por el psicólogo' }}"></span>
                                                                                            <p
                                                                                                class="text-[10px] font-extrabold leading-tight truncate text-gray-400 dark:text-gray-500 line-through">
                                                                                                {{ $canceladaCita->paciente->persona->nombre_persona }}
                                                                                            </p>
                                                                                            <div onclick="dismissCancelMessage(event, {{ $canceladaCita->id }})"
                                                                                                class="ml-1 text-[10px] font-black text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors flex-shrink-0 cursor-pointer"
                                                                                                title="Ocultar">✕</div>
                                                                                        </div>
                                                                                    @endif
                                                                                @endif
                                                                            </div>
                                                                        </button>
                                                                    @else
                                                                        <div
                                                                            class="h-10 flex items-center justify-center">
                                                                            <div
                                                                                class="w-1 h-1 bg-gray-200 dark:bg-gray-700 rounded-full">
                                                                            </div>
                                                                        </div>
                                                                    @endif
                                                                </td>
                                                            @endforeach
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div
                                            class="mt-6 min-h-[400px] bg-white dark:bg-gray-800 rounded-3xl border-2 border-dashed border-gray-200 dark:border-gray-700/60 p-12 flex flex-col items-center justify-center text-center">
                                            <div
                                                class="w-20 h-20 bg-gray-50 dark:bg-gray-900/50 text-gray-400 dark:text-gray-500 rounded-3xl border border-gray-200/60 dark:border-gray-700/60 flex items-center justify-center mb-6">
                                                <svg class="w-10 h-10" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            </div>
                                            <h3 class="text-xl font-black text-gray-900 dark:text-white mb-2">Sin
                                                Horarios Activos</h3>
                                            <p
                                                class="text-gray-500 dark:text-gray-400 text-sm max-w-xs mx-auto font-medium">
                                                Gestiona tus grupos de horarios para comenzar a agendar citas en esta
                                                semana.</p>
                                        </div>
                                    @endif
                                @endif
                            </div>

                            <div id="agendaBlockManagerView"
                                style="background-color: var(--bg-card); border-color: var(--border-color);"
                                class="hidden opacity-0 transition-all duration-300 w-full bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700/60 shadow-sm overflow-hidden p-6 md:p-8">
                                <div class="flex flex-col h-full min-h-[400px]">
                                    <div
                                        class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700/60 pb-4 mb-6">
                                        <div>
                                            <button type="button" onclick="closeBlockManager()"
                                                class="flex items-center gap-2 text-sky-600 hover:text-sky-700 dark:text-sky-400 font-extrabold text-sm uppercase tracking-wider mb-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                                </svg>
                                                Volver a la Agenda
                                            </button>
                                            <h3 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight"
                                                id="blockManagerTitle"></h3>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <button type="button" id="blockManagerPrevBtn"
                                                onclick="navigateBlock(-1)"
                                                class="w-9 h-9 flex items-center justify-center rounded-xl hover:bg-white dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-all flex-shrink-0">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2.5" d="M15 19l-7-7 7-7" />
                                                </svg>
                                            </button>
                                            <button type="button" id="blockManagerNextBtn"
                                                onclick="navigateBlock(1)"
                                                class="w-9 h-9 flex items-center justify-center rounded-xl hover:bg-white dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-all flex-shrink-0">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2.5" d="M9 5l7 7-7 7" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 flex-1" id="blockManagerGrid">
                                        <div class="flex flex-col h-full transition-all duration-300"
                                            id="colCandidatos">
                                            <div class="flex justify-between items-center px-2 mb-4">
                                                <h4
                                                    class="text-[10px] font-black text-gray-400 dark:text-gray-400 uppercase tracking-wider">
                                                    Candidatos Disponibles</h4>
                                                <span
                                                    class="text-[9px] font-black px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 uppercase tracking-wider">Lista
                                                    de Espera</span>
                                            </div>
                                            <div
                                                class="w-full h-[320px] rounded-2xl border border-gray-200 dark:border-gray-700/60 bg-gray-50/50 dark:bg-gray-900/40 p-4 transition-all flex flex-col">
                                                <ul id="blockRequestsList"
                                                    class="space-y-3 custom-scrollbar overflow-y-auto flex-1 pr-1">
                                                </ul>
                                            </div>
                                        </div>

                                        <div class="flex flex-col h-full transition-all duration-300" id="colEstado">
                                            <div class="flex justify-between items-center px-2 mb-4">
                                                <h4
                                                    class="text-[10px] font-black text-gray-400 dark:text-gray-400 uppercase tracking-wider">
                                                    Estado de Cita</h4>
                                                <span id="blockConfirmationBadge"
                                                    class="text-[9px] font-black px-2 py-0.5 rounded-full bg-sky-50 dark:bg-sky-950/40 text-sky-600 dark:text-sky-400 border border-sky-200 dark:border-sky-800/60 uppercase tracking-wider hidden">Confirmado</span>
                                            </div>
                                            <div id="blockConfirmedContainer"
                                                class="w-full h-[320px] rounded-2xl border-2 border-dashed border-gray-200 dark:border-gray-700/60 bg-gray-50/50 dark:bg-gray-900/40 flex flex-col items-center justify-center p-8 text-center transition-all">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const CONFIG = {
                endpoints: {
                    json: (id) => `{{ url('admin/psicologia/maestros/citas') }}/${id}/json`,
                    prioridad: (id) => `{{ url('admin/psicologia/maestros/citas') }}/${id}/prioridad`,
                    rechazar: (id) => `{{ url('admin/psicologia/maestros/citas') }}/${id}/rechazar`,
                    aceptar: (id) => `{{ url('admin/psicologia/maestros/citas') }}/${id}/aceptar`,
                    proponer: (id) => `{{ url('admin/psicologia/maestros/citas') }}/${id}/proponer`,
                    quitarPropuesta: (id) =>
                        `{{ url('admin/psicologia/maestros/citas') }}/${id}/quitar-propuesta`,
                    enviarPropuesta: (id) =>
                        `{{ url('admin/psicologia/maestros/citas') }}/${id}/enviar-propuesta`,
                    realizar: (id) => `{{ url('admin/psicologia/maestros/citas') }}/${id}/realizar`,
                    noAsistio: (id) => `{{ url('admin/psicologia/maestros/citas') }}/${id}/no-asistio`,
                    posponer: (id) => `{{ url('admin/psicologia/maestros/citas') }}/${id}/posponer`,
                    cancelar: (id) => `{{ url('admin/psicologia/maestros/citas') }}/${id}/cancelar-psicologo`,
                    pendingList: '{{ route('admin.psicologia.maestros.agenda.pending.list') }}',
                    dailyCitas: '{{ route('admin.psicologia.maestros.agenda.daily_citas') }}'
                },
                csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            };

            const HORARIOS_PSICOLOGO = {!! json_encode($horarios->map(function ($h) {
                return [
                    'dia'    => $h->dia,
                    'inicio' => \Carbon\Carbon::parse($h->hora_inicio)->format('H:i'),
                    'fin'    => \Carbon\Carbon::parse($h->hora_fin)->format('H:i'),
                ];
            })->values()) !!};

            let state = {
                currentCitaId: null,
                currentCitaIndex: -1,
                pendingCitaIds: [],
                currentBlockLabel: null,
                currentBlockDate: null
            };

            const Utils = {
                escapeHtml: (str) => {
                    const div = document.createElement('div');
                    div.textContent = str || '';
                    return div.innerHTML;
                },
                formatAmPm: (label) => {
                    if (!label) return 'En espera';
                    return label.replace(/(\d{1,2}):(\d{2})/g, (m, h, min) => {
                        let hh = parseInt(h);
                        return `${hh % 12 || 12}:${min} ${hh >= 12 ? 'PM' : 'AM'}`;
                    });
                },
                normalize: (l) => {
                    let s = (l || '').trim().toLowerCase()
                        .replace(/(\d{1,2}):(\d{2})\s*(am|pm)\b/g, (m, h, min, ampm) => {
                            let hh = parseInt(h);
                            if (ampm === 'pm' && hh < 12) hh += 12;
                            if (ampm === 'am' && hh === 12) hh = 0;
                            return `${hh < 10 ? '0' : ''}${hh}:${min}`;
                        });
                    return s.replace(/(\d{1,2}:\d{2}):\d{2}/g, '$1')
                        .replace(/\s*[-–—]\s*/g, '-')
                        .replace(/\s+/g, ' ')
                        .replace(/(^|\s|-)(\d):/g, '$10$2:');
                },
                api: (url, method = 'GET', body = null) => {
                    const options = {
                        method,
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': CONFIG.csrfToken
                        }
                    };
                    if (body) options.body = JSON.stringify(body);
                    return fetch(url, options).then(res => res.ok ? res.json() : Promise.reject(res));
                },
                confirm: (title, text, options = {}) => {
                    return new Promise((resolve) => {
                        const m = document.getElementById('confirmModal');
                        const t = document.getElementById('confirmTitle');
                        const p = document.getElementById('confirmText');
                        const y = document.getElementById('confirmYesBtn');
                        const n = document.getElementById('confirmNoBtn');
                        const iconBox = document.getElementById('confirmIconBox');
                        const iconSvg = document.getElementById('confirmIconSvg');
                        const inputArea = document.getElementById('confirmInputArea');
                        const inputField = document.getElementById('confirmInputField');

                        if (title) t.innerText = title;
                        if (text) p.innerText = text;

                        const btnColor = options.btnColor ||
                            'bg-sky-600 hover:bg-sky-700 shadow-sm';
                        y.className =
                            `flex-1 py-3.5 px-6 ${btnColor} text-white rounded-2xl font-black text-xs uppercase tracking-wider transition-all`;

                        const iconColor = options.iconColor ||
                            'bg-sky-50 dark:bg-sky-950/40 text-sky-600 dark:text-sky-400 border border-sky-100 dark:border-sky-900/30';
                        iconBox.className =
                            `w-16 h-16 ${iconColor} rounded-2xl flex items-center justify-center mb-6 mx-auto`;
                        if (iconSvg && options.icon) iconSvg.innerHTML = options.icon;

                        if (options.inputLabel) {
                            document.getElementById('confirmInputLabel').textContent = options
                                .inputLabel;
                            inputField.value = options.inputDefault || '';
                            inputArea.classList.remove('hidden');
                        } else {
                            inputArea.classList.add('hidden');
                            inputField.value = '';
                        }

                        m.classList.remove('hidden');
                        m.classList.add('flex');

                        const cleanup = (val) => {
                            m.classList.add('hidden');
                            m.classList.remove('flex');
                            y.onclick = null;
                            n.onclick = null;
                            inputField.classList.remove('border-rose-500', 'ring-rose-500/20');
                            resolve(val);
                        };

                        y.onclick = () => {
                            if (options.inputLabel && options.requireInput && !inputField.value
                                .trim()) {
                                inputField.classList.add('border-rose-500', 'ring-rose-500/20');
                                inputField.focus();
                                return;
                            }
                            cleanup(options.inputLabel ? inputField.value.trim() : true);
                        };
                        n.onclick = () => cleanup(false);
                    });
                }
            };

            function openCitaModal(id) {
                state.pendingCitaIds = Array.from(document.querySelectorAll('.pending-item')).map(i => i.dataset
                    .citaId);
                state.currentCitaId = id;
                state.currentCitaIndex = state.pendingCitaIds.indexOf(String(id));
                updateCitaNavButtons();
                Utils.api(CONFIG.endpoints.json(id)).then(renderCitaDetails).catch(err => {
                    console.error(err);
                    AppModal.alert('Error', 'Error al cargar la cita.');
                });
            }

            function renderCitaDetails(cita) {
                const set = (id, val) => {
                    const el = document.getElementById(id);
                    if (el) el.textContent = val || '-';
                };

                set('citaPacienteName', cita.paciente);
                set('citaPsicologoName', 'Psicólogo: ' + (cita.psicologo || '-'));
                set('citaFechaSolicitud', cita.fecha_solicitud_iso ? new Date(cita.fecha_solicitud_iso)
                    .toLocaleTimeString([], {
                        hour: 'numeric',
                        minute: '2-digit',
                        hour12: true
                    }) : cita.fecha_solicitud);
                set('citaFechaConfirmada', cita.fecha_confirmada || 'Pendiente');
                set('citaBloqueConfirmado', Utils.formatAmPm(cita.bloque_confirmado));
                set('citaEstado', cita.estado);
                set('citaMotivo', cita.motivo);
                set('citaBloqueTag', (cita.estado || '').toUpperCase());

                const pMap = {
                    baja: 'bg-emerald-500',
                    media: 'bg-sky-600',
                    alta: 'bg-amber-500',
                    crítica: 'bg-rose-500'
                };
                const dot = document.getElementById('citaPrioridadDot');
                if (dot) dot.className = `h-2 w-2 rounded-full ${pMap[cita.prioridad] || 'bg-sky-600'}`;
                set('citaPrioridadTexto', (cita.prioridad || 'Media').charAt(0).toUpperCase() + (cita.prioridad ||
                    'media').slice(1));

                document.querySelectorAll('.prioridad-radio').forEach(r => r.checked = r.value === cita.prioridad);
                document.getElementById('prioridadMensaje')?.classList.add('hidden');

                const avatarContainer = document.getElementById('citaAvatarContainer');
                const avatarImg = document.getElementById('citaAvatarImg');
                const avatarText = document.getElementById('citaAvatarText');
                if (avatarContainer && avatarImg && avatarText) {
                    if (cita.paciente_foto && cita.paciente_foto !== '') {
                        avatarImg.src = cita.paciente_foto;
                        avatarImg.classList.remove('hidden');
                        avatarText.classList.add('hidden');
                    } else {
                        avatarImg.classList.add('hidden');
                        avatarText.classList.remove('hidden');
                        avatarText.textContent = (cita.paciente || 'P').split(' ').map(n => n[0]).join('')
                            .toUpperCase().slice(0, 2);
                    }
                }

                const cont = document.getElementById('citaBloquesSugeridos');
                if (cont) {
                    cont.innerHTML = '';
                    const raw = cita.bloques_sugeridos || '';
                    let horariosStr = raw;
                    if (raw.includes('|')) {
                        const parts = raw.split('|');
                        horariosStr = parts[1].replace('Horarios:', '').trim();
                    }
                    const list = horariosStr.split(';').map(s => s.trim()).filter(Boolean);
                    if (!list.length) {
                        const empty = document.createElement('span');
                        empty.className = 'text-[10px] font-bold text-gray-400 dark:text-gray-500 italic';
                        empty.textContent = 'No hay horarios sugeridos';
                        cont.appendChild(empty);
                    } else {
                        list.forEach(txt => {
                            const chip = document.createElement('span');
                            chip.className =
                                'px-3 py-1 bg-sky-50 dark:bg-sky-950/40 text-sky-600 dark:text-sky-400 border border-sky-100 dark:border-sky-900/30 rounded-2xl text-xs font-extrabold uppercase tracking-wider';
                            chip.textContent = txt;
                            cont.appendChild(chip);
                        });
                    }
                }

                const propInfo = document.getElementById('citaPropuestaInfo');
                const propAcciones = document.getElementById('citaPropuestaAcciones');
                const enviarBtn = document.getElementById('enviarPropuestaBtn');

                if (propInfo && propAcciones) {
                    propInfo.className = 'p-4 rounded-2xl border text-xs font-medium shadow-sm transition-all ';
                    propInfo.classList.add('hidden');
                    propAcciones.classList.add('hidden');

                    const propList = (cita.bloques_propuestos || '').split(/[;,]/).map(s => s.trim()).filter(
                        Boolean);

                    if (cita.propuesta_estado === 'pendiente') {
                        propInfo.classList.remove('hidden');
                        propInfo.classList.add('bg-amber-50', 'dark:bg-amber-950/40', 'border-amber-200',
                            'dark:border-amber-800/60', 'text-amber-700', 'dark:text-amber-400');
                        propInfo.innerHTML =
                            `Propuesta enviada al paciente. Esperando su respuesta.<br><strong>Bloques propuestos:</strong> ${propList.map(Utils.formatAmPm).join(', ')}`;
                    } else if (cita.propuesta_estado === 'cualquier_dia') {
                        propInfo.classList.remove('hidden');
                        propInfo.classList.add('bg-emerald-50', 'dark:bg-emerald-950/40', 'border-emerald-200',
                            'dark:border-emerald-800/60', 'text-emerald-700', 'dark:text-emerald-400');
                        propInfo.innerHTML =
                            `El paciente respondió: <strong>"Cualquier día está bien"</strong>. Puedes agendar esta cita en cualquier bloque.`;
                    } else if (cita.propuesta_estado === 'sugerencia_aceptada') {
                        propInfo.classList.remove('hidden');
                        propInfo.classList.add('bg-sky-50', 'dark:bg-sky-950/40', 'border-sky-200',
                            'dark:border-sky-800/60', 'text-sky-700', 'dark:text-sky-400');
                        propInfo.innerHTML =
                            `El paciente aceptó la propuesta para: <strong>${Utils.formatAmPm(cita.propuesta_bloque_seleccionado || 'Sugerencia')}</strong>.`;
                    } else if (cita.propuesta_estado === 'rechazada') {
                        propInfo.classList.remove('hidden');
                        propInfo.classList.add('bg-rose-50', 'dark:bg-rose-950/40', 'border-rose-200',
                            'dark:border-rose-800/60', 'text-rose-700', 'dark:text-rose-400');
                        let rejectedReason = cita.motivo_rechazo_propuesta ?
                            `<strong>Motivo:</strong> ${cita.motivo_rechazo_propuesta}<br>` : '';
                        let rejectedBlocks = propList.length > 0 ?
                            `<strong>Bloques descartados:</strong> ${propList.map(Utils.formatAmPm).join(', ')}<br>` :
                            '';
                        propInfo.innerHTML =
                            `El paciente rechazó la propuesta.<br>${rejectedReason}${rejectedBlocks}La cita permanece en cola.`;
                    } else if (propList.length > 0) {
                        propInfo.classList.remove('hidden');
                        propInfo.classList.add('bg-gray-50', 'dark:bg-gray-900/40', 'border-gray-200',
                            'dark:border-gray-700/60', 'text-gray-700', 'dark:text-gray-300');
                        propInfo.innerHTML =
                            `Tienes bloques propuestos: <strong>${propList.map(Utils.formatAmPm).join(', ')}</strong>. Puedes enviar la propuesta al paciente.`;
                        propAcciones.classList.remove('hidden');
                        if (enviarBtn) enviarBtn.onclick = () => window.enviarPropuesta(cita.id);
                    }
                }

                const m = document.getElementById('citaDetailsModal');
                if (m) {
                    m.classList.remove('hidden');
                    m.classList.add('flex');
                }
            }

            function updateCitaNavButtons() {
                const p = document.getElementById('prevCitaBtn'),
                    n = document.getElementById('nextCitaBtn');
                if (p && n) {
                    p.disabled = state.currentCitaIndex <= 0;
                    n.disabled = state.currentCitaIndex < 0 || state.currentCitaIndex >= state.pendingCitaIds
                        .length - 1;
                }
            }

            function openBlockManager(cell) {
                if (!cell) return;
                state.currentBlockLabel = cell.dataset.blockLabel;
                state.currentBlockDate = cell.dataset.blockDate;

                const title = document.getElementById('blockManagerTitle');
                if (title) {
                    const parsedDate = new Date(state.currentBlockDate + 'T12:00:00');
                    const dateStr = isNaN(parsedDate.getTime()) ? '' : parsedDate.toLocaleDateString('es-ES', {
                        weekday: 'long',
                        day: 'numeric',
                        month: 'long'
                    });
                    const timeOnly = (state.currentBlockLabel || '').replace(/^[a-záéíóúñü]+\s+/i, '');
                    title.textContent = dateStr.charAt(0).toUpperCase() + dateStr.slice(1) + ' · ' + Utils
                        .formatAmPm(timeOnly);
                }

                renderBlockRequests(cell);

                const mainView = document.getElementById('agendaMainView');
                const blockView = document.getElementById('agendaBlockManagerView');
                if (mainView && blockView && blockView.classList.contains('hidden')) {
                    mainView.classList.add('opacity-0');
                    setTimeout(() => {
                        mainView.classList.add('hidden');
                        blockView.classList.remove('hidden');
                        void blockView.offsetWidth;
                        blockView.classList.remove('opacity-0');
                    }, 300);
                }
            }

            window.closeBlockManager = function() {
                const mainView = document.getElementById('agendaMainView');
                const blockView = document.getElementById('agendaBlockManagerView');
                if (mainView && blockView) {
                    blockView.classList.add('opacity-0');
                    setTimeout(() => {
                        blockView.classList.add('hidden');
                        mainView.classList.remove('hidden');
                        void mainView.offsetWidth;
                        mainView.classList.remove('opacity-0');
                    }, 300);
                }
            };

            window.navigateBlock = function(dir) {
                const allButtons = Array.from(document.querySelectorAll('.block-slot-button'));
                if (!allButtons.length) return;

                const uniqueBlocksMap = new Map();
                allButtons.forEach(b => {
                    const key = b.dataset.blockDate + '|' + b.dataset.blockLabel;
                    if (!uniqueBlocksMap.has(key)) uniqueBlocksMap.set(key, b);
                });
                const buttons = Array.from(uniqueBlocksMap.values()).sort((a, b) => {
                    return new Date(a.dataset.blockDate + 'T' + (a.dataset.blockTime || '00:00:00'))
                        .getTime() -
                        new Date(b.dataset.blockDate + 'T' + (b.dataset.blockTime || '00:00:00'))
                        .getTime();
                });

                let currentIndex = buttons.findIndex(b => b.dataset.blockLabel === state.currentBlockLabel && b
                    .dataset.blockDate === state.currentBlockDate);
                if (currentIndex === -1) currentIndex = 0;

                let nextIndex = currentIndex + dir;
                if (nextIndex < 0) nextIndex = buttons.length - 1;
                if (nextIndex >= buttons.length) nextIndex = 0;

                if (buttons[nextIndex]) openBlockManager(buttons[nextIndex]);
            };

            function renderBlockRequests(cell) {
                const list = document.getElementById('blockRequestsList');
                const assignedList = document.getElementById('blockConfirmedContainer');
                const badge = document.getElementById('blockConfirmationBadge');
                const colCandidatos = document.getElementById('colCandidatos');
                const colEstado = document.getElementById('colEstado');

                list.innerHTML = '';
                assignedList.innerHTML = '';

                const assignedPac = cell.dataset.assignedPaciente;
                const assignedId = cell.dataset.assignedCitaId;
                const assignedEstado = cell.dataset.assignedEstado;

                if (assignedPac) {
                    colCandidatos?.classList.add('hidden');
                    colCandidatos?.classList.remove('lg:col-span-2');
                    colEstado?.classList.remove('hidden');
                    colEstado?.classList.add('lg:col-span-2');

                    if (badge) {
                        badge.classList.remove('hidden');
                        badge.textContent = assignedEstado === 'realizada' ? 'Realizada' : (assignedEstado ===
                            'no_asistio' ? 'Ausente' : 'Confirmado');
                        badge.className =
                            `px-3 py-1 rounded-full ${assignedEstado === 'realizada' ? 'bg-sky-50 dark:bg-sky-950/40 text-sky-600 dark:text-sky-400 border border-sky-200 dark:border-sky-800/60' : (assignedEstado === 'no_asistio' ? 'bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-800/60' : 'bg-sky-50 dark:bg-sky-950/40 text-sky-600 dark:text-sky-400 border border-sky-200 dark:border-sky-800/60')} text-[9px] font-black uppercase tracking-wider shadow-sm`;
                    }

                    let actionButtons = '';
                    if (assignedEstado === 'confirmada') {
                        actionButtons = `
                            <div class="flex flex-wrap justify-center gap-3 mt-6">
                                <button type="button" id="btn-realizar-${assignedId}" class="px-5 py-2.5 bg-sky-600 hover:bg-sky-700 text-white rounded-2xl font-black text-xs shadow-sm transition-all uppercase tracking-wider active:scale-95">Realizada</button>
                                <button type="button" id="btn-no-asistio-${assignedId}" class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-2xl font-black text-xs shadow-sm transition-all uppercase tracking-wider active:scale-95">Ausente</button>
                                <button type="button" id="btn-posponer-${assignedId}" class="px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-white rounded-2xl font-black text-xs shadow-sm transition-all uppercase tracking-wider active:scale-95">Reagendar</button>
                                <button type="button" id="btn-cancelar-${assignedId}" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-600 dark:text-gray-300 rounded-2xl font-black text-xs transition-all uppercase tracking-wider active:scale-95">Cancelar</button>
                            </div>`;
                    }

                    assignedList.innerHTML = `
                        <div class="w-16 h-16 rounded-2xl bg-sky-600 text-white flex items-center justify-center shadow-md mb-4 ring-4 ring-gray-100 dark:ring-gray-700/60">
                            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                        </div>
                        <span class="text-[10px] font-black text-sky-600 dark:text-sky-400 uppercase tracking-wider mb-1">Paciente Asignado</span>
                        <h3 class="text-xl font-black text-gray-900 dark:text-white tracking-tight">${Utils.escapeHtml(assignedPac)}</h3>
                        ${actionButtons}`;

                    if (assignedEstado === 'confirmada') {
                        document.getElementById(`btn-realizar-${assignedId}`)?.addEventListener('click', () =>
                            handleAction('realizar', assignedId));
                        document.getElementById(`btn-no-asistio-${assignedId}`)?.addEventListener('click', () =>
                            handleAction('no_asistio', assignedId));
                        document.getElementById(`btn-posponer-${assignedId}`)?.addEventListener('click', () =>
                            handleAction('posponer', assignedId));
                        document.getElementById(`btn-cancelar-${assignedId}`)?.addEventListener('click', () =>
                            handleAction('cancelar', assignedId));
                    }
                } else {
                    colEstado?.classList.add('hidden');
                    colEstado?.classList.remove('lg:col-span-2');
                    colCandidatos?.classList.remove('hidden');
                    colCandidatos?.classList.add('lg:col-span-2');
                    badge?.classList.add('hidden');

                    assignedList.innerHTML =
                        `
                        <div class="w-16 h-16 bg-gray-50 dark:bg-gray-900/50 text-gray-400 dark:text-gray-500 rounded-2xl border border-gray-200 dark:border-gray-700/60 flex items-center justify-center mb-4">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <p class="text-xs font-black text-gray-400 dark:text-gray-400 uppercase tracking-wider">No hay paciente confirmado aún.</p>`;

                    const candidates = getCandidatesForBlock(state.currentBlockLabel, state.currentBlockDate);
                    if (!candidates.length) {
                        list.innerHTML = `
                            <div class="flex-1 flex flex-col items-center justify-center h-full text-center py-12">
                                <div class="w-16 h-16 bg-gray-50 dark:bg-gray-900/50 text-gray-400 dark:text-gray-500 rounded-2xl border border-gray-200 dark:border-gray-700/60 flex items-center justify-center mb-4">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                </div>
                                <p class="text-xs font-black text-gray-400 dark:text-gray-400 uppercase tracking-wider">Sin pacientes interesados</p>
                            </div>`;
                    } else {
                        candidates.forEach(can => {
                            const li = document.createElement('li');
                            li.className =
                                `group rounded-2xl border border-emerald-200 dark:border-emerald-800/60 p-4 transition-all`;
                            li.innerHTML = `
                                <div class="flex justify-between items-center gap-4">
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 rounded-xl bg-gray-100 dark:bg-gray-700/60 flex items-center justify-center">
                                            <svg class="h-5 w-5 text-gray-400 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        </div>
                                        <div class="flex flex-col">
                                            ${can.status === 'proposed'
                                                ? (can.propuestaEstado === 'pendiente'
                                                    ? '<span class="text-[9px] font-black text-amber-600 dark:text-amber-400 uppercase tracking-wider">Contrapropuesta enviada, en espera</span>'
                                                    : '<span class="text-[9px] font-black text-sky-600 dark:text-sky-400 uppercase tracking-wider">Agregado al bloque</span>')
                                                : '<span class="text-[9px] font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">Solicitado por el paciente</span>'}
                                            <span class="text-sm font-black text-gray-900 dark:text-white">${Utils.escapeHtml(can.paciente)}</span>
                                        </div>
                                    </div>
                                    <div class="flex gap-2">
                                        <button title="Aceptar" class="block-request-action-btn h-9 w-9 flex items-center justify-center rounded-xl border border-emerald-200 dark:border-emerald-800/60 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-600 hover:text-white dark:hover:bg-emerald-600 dark:hover:text-white transition-colors" data-action="accept" data-cita-id="${can.citaId}">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                        </button>
                                        <button title="Rechazar" class="block-request-action-btn h-9 w-9 flex items-center justify-center rounded-xl border border-rose-200 dark:border-rose-800/60 text-rose-600 dark:text-rose-400 hover:bg-rose-600 hover:text-white dark:hover:bg-rose-600 dark:hover:text-white transition-colors" data-action="reject" data-cita-id="${can.citaId}">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                        <button title="Quitar sugerencia" class="block-request-action-btn h-9 w-9 flex items-center justify-center rounded-xl border border-gray-200 dark:border-gray-700/60 text-gray-500 dark:text-gray-400 hover:bg-gray-600 hover:text-white dark:hover:bg-gray-600 dark:hover:text-white transition-colors" data-action="remove_proposal" data-cita-id="${can.citaId}">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        </button>
                                    </div>
                                </div>`;
                            list.appendChild(li);
                        });
                    }
                }
            }

            function isBlockSuggested(sug, label, blockDate) {
                if (!sug) return false;
                const normLabel = Utils.normalize(label);
                const labelDayMatch = normLabel.match(/^([a-záéíóúñ]+)/);
                const labelDay = labelDayMatch ? labelDayMatch[1] : '';
                const timesMatch = normLabel.match(/(\d{2}:\d{2})-(\d{2}:\d{2})/);
                const labelTimes = timesMatch ? [timesMatch[1], timesMatch[2]] : [];

                let parts = sug.split('|');
                let excepcionesStr = '';
                let horariosStr = sug;
                if (parts.length > 1) {
                    const leftPart = parts[0].trim();
                    const rightPart = parts[1].trim();
                    if (leftPart.toLowerCase().includes('exceptuados')) {
                        excepcionesStr = leftPart.replace(/D[íi]as exceptuados:/i, '').trim();
                    }
                    horariosStr = rightPart.replace(/^\s*Horarios\s*(propuestos)?\s*:\s*/i, '').trim();
                } else {
                    horariosStr = sug.replace(/^\s*Horarios\s*(propuestos)?\s*:\s*/i, '').trim();
                }

                if (blockDate && excepcionesStr) {
                    const excArray = excepcionesStr.split(',').map(s => s.trim());
                    if (excArray.includes(blockDate)) return false;
                }
                if (blockDate) {
                    const bd = new Date(blockDate + 'T00:00:00');
                    const today = new Date();
                    today.setHours(0, 0, 0, 0);
                    const nextM = new Date(today);
                    nextM.setMonth(today.getMonth() + 1);
                    if (bd < today || bd > nextM) return false;
                }

                return horariosStr.split(';').map(s => s.trim()).filter(Boolean).some(b => {
                    if (blockDate && b.includes(blockDate)) {
                        const mTimes = b.match(
                            /(\d{1,2}:\d{2}(?:\s*[aApP][mM])?)\s*[-–—]\s*(\d{1,2}:\d{2}(?:\s*[aApP][mM])?)/i
                        );
                        if (mTimes && labelTimes.length === 2) {
                            const toMin = (t) => {
                                const m = t.trim().match(/(\d{1,2}):(\d{2})\s*([aApP][mM])?/);
                                if (!m) return 0;
                                let h = parseInt(m[1]),
                                    min = parseInt(m[2]);
                                const ap = (m[3] || '').toLowerCase();
                                if (ap === 'pm' && h !== 12) h += 12;
                                if (ap === 'am' && h === 12) h = 0;
                                return h * 60 + min;
                            };
                            const bStart = toMin(mTimes[1]);
                            const bEnd = toMin(mTimes[2]);
                            const [lh1, lm1] = labelTimes[0].split(':').map(Number);
                            const [lh2, lm2] = labelTimes[1].split(':').map(Number);
                            return (lh1 * 60 + lm1) < bEnd && (lh2 * 60 + lm2) > bStart;
                        }
                        return true;
                    }
                    const normB = Utils.normalize(b);
                    if (!normB.includes(labelDay)) return false;
                    const m = normB.match(/(\d{2}:\d{2})-(\d{2}:\d{2})/);
                    if (m && labelTimes.length > 0) return labelTimes[0] < m[2] && labelTimes[1] > m[1];
                    return false;
                });
            }

            function getCandidatesForBlock(label, blockDate) {
                const normLabel = Utils.normalize(label);
                return Array.from(document.querySelectorAll('.pending-item')).filter(i => {
                    const sug = i.dataset.bloquesSugeridos || '';
                    const pro = i.dataset.bloquesPropuestos || '';
                    const propEstado = i.dataset.propuestaEstado || '';
                    const matchesSug = isBlockSuggested(sug, label, blockDate);
                    let matchesPro = false;
                    if (pro) {
                        matchesPro = pro.split(';').map(p => p.trim()).some(b => {
                            const parts = b.split('|');
                            return (parts.length === 2 && parts[0] === blockDate && Utils.normalize(
                                    parts[1]) === normLabel) ||
                                (Utils.normalize(b) === normLabel);
                        }) && propEstado !== 'rechazada';
                    }
                    return matchesSug || matchesPro;
                }).map(i => {
                    const pro = i.dataset.bloquesPropuestos || '';
                    const propEstado = i.dataset.propuestaEstado || '';
                    let status = 'interested';
                    if (pro && propEstado !== 'rechazada') {
                        pro.split(';').forEach(b => {
                            const parts = b.split('|');
                            if ((parts.length === 2 && parts[0] === blockDate && Utils.normalize(
                                    parts[1]) === normLabel) || Utils.normalize(b) === normLabel)
                                status = 'proposed';
                        });
                    }
                    return {
                        citaId: i.dataset.citaId,
                        paciente: i.dataset.patientName || 'Paciente',
                        status,
                        propuestaEstado: propEstado
                    };
                });
            }

            function handleAction(action, id, targetBtn = null) {
                const normalizedAction = action === 'complete' ? 'realizar' : (action === 'cancel_confirmada' ?
                    'cancelar' : action);

                if (targetBtn) {
                    targetBtn.disabled = true;
                    targetBtn.classList.add('opacity-50', 'cursor-wait');
                    if (!targetBtn.innerHTML.includes('svg')) targetBtn.innerText = '...';
                }

                const enableBtn = () => {
                    if (targetBtn) {
                        targetBtn.disabled = false;
                        targetBtn.classList.remove('opacity-50', 'cursor-wait');
                    }
                };

                switch (normalizedAction) {
                    case 'reject':
                        Utils.confirm('Rechazar solicitud', 'Por favor, indica el motivo del rechazo.', {
                            iconColor: 'bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-800/60',
                            icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>',
                            btnColor: 'bg-rose-600 hover:bg-rose-700 shadow-sm',
                            inputLabel: 'Motivo',
                            inputDefault: 'Lo siento, no puedo atenderte en este momento.'
                        }).then(reason => {
                            if (!reason) {
                                enableBtn();
                                return;
                            }
                            Utils.api(CONFIG.endpoints.rechazar(id), 'PATCH', {
                                    motivo_rechazo: reason === true ?
                                        'Lo siento, no puedo atenderte en este momento.' : reason
                                })
                                .then(res => {
                                    if (typeof showToast === 'function') showToast(res.message ||
                                        'Solicitud rechazada.', 'success');
                                    refreshAll();
                                })
                                .catch(err => {
                                    console.error(err);
                                    enableBtn();
                                    AppModal.alert('Error', 'Error al rechazar la solicitud.');
                                });
                        });
                        return;

                    case 'accept':
                        const timeMatch = state.currentBlockLabel ? state.currentBlockLabel.match(
                            /(\d{1,2}:\d{2})/) : null;
                        const timeStr = timeMatch ? timeMatch[1] : '00:00';
                        const selectedDT = state.currentBlockDate ? new Date(state.currentBlockDate + 'T' +
                            timeStr + ':00') : new Date();
                        const citaElAccept = document.querySelector(`li[data-cita-id="${id}"]`);
                        const isManualAccept = citaElAccept && citaElAccept.dataset.isManual === '1';

                        if (selectedDT < new Date() && !isManualAccept) {
                            AppModal.alert('Error', 'No puedes agendar citas en fechas u horas pasadas.');
                            enableBtn();
                            return;
                        }
                        const existingCands = getCandidatesForBlock(state.currentBlockLabel, state
                            .currentBlockDate);
                        if (existingCands.some(c => c.propuestaEstado === 'pendiente' && c.citaId !== id
                                .toString())) {
                            AppModal.alert('Acción no permitida',
                                'Hay una contrapropuesta pendiente de respuesta en este bloque.');
                            enableBtn();
                            return;
                        }

                        Utils.api(CONFIG.endpoints.json(id)).then(cita => {
                            const caminoA = isBlockSuggested(cita.bloques_sugeridos, state
                                    .currentBlockLabel, state.currentBlockDate) || ['cualquier_dia',
                                    'aceptada'
                                ].includes(cita.propuesta_estado) ||
                                !cita.bloques_sugeridos;

                            if (caminoA) {
                                Utils.api(CONFIG.endpoints.aceptar(id), 'PATCH', {
                                    fecha: state.currentBlockDate || new Date().toISOString().split(
                                        'T')[0],
                                    hora: state.currentBlockLabel?.match(/(\d{1,2}:\d{2})/)?.[1],
                                    bloque: state.currentBlockLabel
                                }).then(res => {
                                    if (res.status === 'error') {
                                        enableBtn();
                                        AppModal.alert('Error', res.message ||
                                            'Error al confirmar la cita');
                                        return;
                                    }
                                    document.querySelectorAll(
                                        `.block-slot-button[data-block-label="${state.currentBlockLabel}"][data-block-date="${state.currentBlockDate}"]`
                                    ).forEach(cell => {
                                        cell.dataset.assignedBlock = 'true';
                                        cell.dataset.assignedPaciente = res.paciente ||
                                            'Paciente';
                                        cell.dataset.assignedCitaId = res.cita_id || id;
                                        cell.dataset.assignedEstado = 'confirmada';
                                        const blockView = document.getElementById(
                                            'agendaBlockManagerView');
                                        if (blockView && !blockView.classList.contains(
                                                'hidden')) renderBlockRequests(cell);
                                    });
                                    AppModal.alert('Éxito', res.message ||
                                        'Cita confirmada exitosamente');
                                    refreshAll();
                                }).catch(err => {
                                    console.error(err);
                                    enableBtn();
                                    err instanceof Response ? err.json().then(d => AppModal.alert(
                                        'Error', d.message || 'Error.')).catch(() => AppModal
                                        .alert('Error', 'Error.')) : AppModal.alert('Error',
                                        'Error.');
                                });
                            } else {
                                if (cita.propuesta_estado === 'pendiente') {
                                    AppModal.alert('Propuesta pendiente',
                                        'Ya se envió una contrapropuesta. Espera la respuesta del paciente.'
                                    );
                                    enableBtn();
                                    return;
                                }
                                Utils.confirm('Contrapropuesta de horario',
                                    'El paciente no solicitó este bloque. ¿Deseas enviarle una contrapropuesta?', {
                                        iconColor: 'bg-sky-50 dark:bg-sky-950/40 text-sky-600 dark:text-sky-400 border border-sky-200 dark:border-sky-800/60',
                                        icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>',
                                        btnColor: 'bg-sky-600 hover:bg-sky-700 shadow-sm'
                                    }).then(confirmed => {
                                    if (!confirmed) {
                                        enableBtn();
                                        return;
                                    }
                                    Utils.api(CONFIG.endpoints.enviarPropuesta(id), 'PATCH', {
                                            fecha: state.currentBlockDate,
                                            bloque: state.currentBlockLabel
                                        })
                                        .then(res => {
                                            if (res.status === 'error') throw new Error(res
                                                .message);
                                            if (typeof showToast === 'function') showToast(res
                                                .message ||
                                                'Contrapropuesta enviada al paciente.',
                                                'success');
                                            refreshAll();
                                        })
                                        .catch(err => {
                                            console.error(err);
                                            enableBtn();
                                            err instanceof Error ? AppModal.alert('Error', err
                                                    .message) : err instanceof Response ? err
                                                .json().then(d => AppModal.alert('Error', d
                                                    .message || 'Error.')).catch(() => AppModal
                                                    .alert('Error', 'Error.')) : AppModal.alert(
                                                    'Error',
                                                    'Error al enviar la contrapropuesta.');
                                        });
                                });
                            }
                        }).catch(err => {
                            console.error(err);
                            enableBtn();
                            AppModal.alert('Error', 'Error al validar el estado de la cita.');
                        });
                        return;

                    case 'propose':
                        const proposeDT = state.currentBlockDate ? new Date(state.currentBlockDate + 'T' + (state
                            .currentBlockLabel?.match(/(\d{1,2}:\d{2})/)?.[1] || '00:00') + ':00') : new Date();
                        if (proposeDT < new Date()) {
                            AppModal.alert('Error', 'No puedes sugerir bloques en fechas u horas pasadas.');
                            enableBtn();
                            return;
                        }
                        const currentCell = document.querySelector(
                            `.block-slot-button[data-block-label="${state.currentBlockLabel}"][data-block-date="${state.currentBlockDate}"]`
                        );
                        if (currentCell?.dataset.assignedBlock === 'true') {
                            AppModal.alert('Bloque ocupado', 'Este bloque ya tiene una cita confirmada.');
                            enableBtn();
                            return;
                        }
                        const existingCandsPropose = getCandidatesForBlock(state.currentBlockLabel, state
                            .currentBlockDate);
                        if (existingCandsPropose.some(c => c.citaId == id)) {
                            AppModal.alert('Acción no permitida',
                                'Este paciente ya está propuesto en este bloque.');
                            enableBtn();
                            return;
                        }
                        if (existingCandsPropose.length >= 10) {
                            AppModal.alert('Límite alcanzado', 'Este bloque ya tiene 10 solicitudes.');
                            enableBtn();
                            return;
                        }
                        if (existingCandsPropose.some(c => c.status === 'proposed' && c.propuestaEstado ===
                                'pendiente')) {
                            AppModal.alert('Acción no permitida',
                                'Ya hay una propuesta pendiente de respuesta para este bloque.');
                            enableBtn();
                            return;
                        }
                        Utils.api(CONFIG.endpoints.proponer(id), 'PATCH', {
                                fecha: state.currentBlockDate,
                                bloque: state.currentBlockLabel
                            })
                            .then(res => {
                                if (res.status === 'warning') AppModal.alert('Advertencia', res.message);
                                else if (res.message && typeof showToast === 'function') showToast(res.message,
                                    'success');
                                refreshAll();
                            })
                            .catch(err => {
                                console.error(err);
                                enableBtn();
                                err instanceof Response ? err.json().then(d => AppModal.alert('Error', d
                                        .message || 'Error.')).catch(() => AppModal.alert('Error', 'Error.')) :
                                    AppModal.alert('Error', 'Error al procesar la acción.');
                            });
                        return;

                    case 'remove_proposal':
                        Utils.api(CONFIG.endpoints.quitarPropuesta(id), 'PATCH', {
                                fecha: state.currentBlockDate,
                                bloque: state.currentBlockLabel
                            })
                            .then(res => {
                                if (res.message && typeof showToast === 'function') showToast(res.message,
                                    'success');
                                refreshAll();
                            })
                            .catch(err => {
                                console.error(err);
                                enableBtn();
                                AppModal.alert('Error', 'Error al quitar la propuesta.');
                            });
                        return;

                    case 'realizar':
                        Utils.confirm('¿Registrar evolución de la cita?',
                            'Serás redirigido a la nota de evolución clínica.', {
                                iconColor: 'bg-sky-50 dark:bg-sky-950/40 text-sky-600 dark:text-sky-400 border border-sky-200 dark:border-sky-800/60',
                                icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                                btnColor: 'bg-sky-600 hover:bg-sky-700 shadow-sm'
                            }).then(confirmed => {
                            if (!confirmed) {
                                enableBtn();
                                return;
                            }
                            Utils.api(CONFIG.endpoints.realizar(id), 'PATCH', {})
                                .then(res => {
                                    clearCellAssignment(id);
                                    window.location.href = res.redirect_url ||
                                        `{{ url('admin/psicologia/maestros/citas') }}/${id}/editar-nota`;
                                })
                                .catch(err => {
                                    console.error(err);
                                    enableBtn();
                                    err?.json ? err.json().then(d => {
                                        d.redirect_template ? AppModal.show('Atención', d
                                            .message, {
                                                type: 'alert',
                                                btnText: 'IR ALLÁ'
                                            }).then(() => window.location.href =
                                            '{{ route('admin.psicologia.maestros.plantillas_globales.index') }}'
                                        ) : d.is_warning ? AppModal.show('Atención', d
                                            .message, {
                                                type: 'alert',
                                                intent: 'warning'
                                            }) : AppModal.alert('Error', d.message ||
                                            'Error al procesar la cita.');
                                    }).catch(() => AppModal.alert('Error',
                                        'Error al procesar la cita.')) : AppModal.alert('Error',
                                        'Error al procesar la cita.');
                                });
                        });
                        return;

                    case 'no_asistio':
                        Utils.confirm('¿Marcar al paciente como ausente?',
                            'Se registrará la inasistencia y se procesarán las penalizaciones.', {
                                iconColor: 'bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-800/60',
                                icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>',
                                btnColor: 'bg-amber-500 hover:bg-amber-600 shadow-sm'
                            }).then(confirmed => {
                            if (!confirmed) {
                                enableBtn();
                                return;
                            }
                            closeModals();
                            Utils.api(CONFIG.endpoints.noAsistio(id), 'PATCH', {})
                                .then(res => {
                                    if (res.status === 'error') throw new Error(res.message);
                                    clearCellAssignment(id);
                                    if (typeof showToast === 'function') showToast(res.message ||
                                        'Paciente marcado como ausente.', 'success');
                                    refreshAll();
                                    setTimeout(() => window.location.reload(), 500);
                                })
                                .catch(err => {
                                    console.error(err);
                                    enableBtn();
                                    err instanceof Error ? AppModal.alert('Error', err.message) : err
                                        ?.json ? err.json().then(d => d.is_warning ? AppModal.show(
                                            'Atención', d.message, {
                                                type: 'alert',
                                                intent: 'warning'
                                            }) : AppModal.alert('Error', d.message || 'Error.')).catch(
                                            () => AppModal.alert('Error', 'Error.')) : AppModal.alert(
                                            'Error', 'Error al registrar la inasistencia.');
                                });
                        });
                        return;

                    case 'posponer':
                        const posponerDT = state.currentBlockDate ? new Date(state.currentBlockDate + 'T' + (Utils
                            .normalize(state.currentBlockLabel || '').match(/(\d{1,2}:\d{2})/)?.[1] ||
                            '00:00') + ':00') : new Date();
                        if (posponerDT < new Date()) {
                            AppModal.show('Acción no permitida',
                                'No puedes posponer una cita pasada. Registra su estado actual.', {
                                    type: 'alert',
                                    intent: 'warning'
                                });
                            enableBtn();
                            return;
                        }
                        Utils.confirm('¿Desea reagendar esta cita?',
                                'La cita pasará a pendiente para que puedas proponer otra fecha.', {
                                    btnColor: 'bg-amber-500 hover:bg-amber-600 shadow-sm'
                                })
                            .then(result => {
                                if (result === false) {
                                    enableBtn();
                                    return;
                                }
                                closeModals();
                                Utils.api(CONFIG.endpoints.posponer(id), 'PATCH', {})
                                    .then(res => {
                                        if (res.status === 'error') throw new Error(res.message);
                                        clearCellAssignment(id);
                                        if (typeof showToast === 'function') showToast(res.message ||
                                            'Cita devuelta a pendientes.', 'success');
                                        refreshAll();
                                        setTimeout(() => window.location.reload(), 500);
                                    })
                                    .catch(err => {
                                        console.error(err);
                                        enableBtn();
                                        AppModal.alert('Error', err.message ||
                                            'Error al reagendar la cita.');
                                    });
                            });
                        return;

                    case 'cancelar':
                        Utils.confirm('¿Cancelar esta cita?', 'Indica el motivo para notificar al paciente.', {
                            iconColor: 'bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-800/60',
                            icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                            btnColor: 'bg-rose-600 hover:bg-rose-700 shadow-sm',
                            inputLabel: 'Motivo de cancelación (Obligatorio)',
                            inputDefault: 'Lo siento, surgió un inconveniente a última hora.',
                            requireInput: true
                        }).then(result => {
                            if (result === false) {
                                enableBtn();
                                return;
                            }
                            const motivo = typeof result === 'string' ? result :
                                'Cancelado por el psicólogo.';
                            closeModals();
                            Utils.api(CONFIG.endpoints.cancelar(id), 'PATCH', {
                                    motivo_cancelacion: motivo
                                })
                                .then(res => {
                                    clearCellAssignment(id);
                                    if (typeof showToast === 'function') showToast(res.message ||
                                        'Cita cancelada.', 'success');
                                    refreshAll();
                                })
                                .catch(err => {
                                    console.error(err);
                                    enableBtn();
                                    AppModal.alert('Error', 'Error al cancelar la cita.');
                                });
                        });
                        return;

                    default:
                        console.error('Acción no reconocida:', action);
                        return;
                }
            }

            function clearCellAssignment(citaId) {
                document.querySelectorAll(
                        `.block-slot-button[data-block-label="${state.currentBlockLabel}"][data-block-date="${state.currentBlockDate}"]`
                    )
                    .forEach(cell => {
                        if (cell.dataset.assignedCitaId == citaId) {
                            delete cell.dataset.assignedBlock;
                            delete cell.dataset.assignedPaciente;
                            delete cell.dataset.assignedCitaId;
                            delete cell.dataset.assignedEstado;
                            const blockView = document.getElementById('agendaBlockManagerView');
                            if (blockView && !blockView.classList.contains('hidden')) renderBlockRequests(cell);
                        }
                    });
            }

            let pendingSearchTimeout = null;
            let pendingListAbortController = null;
            let pendingSearchSequence = 0;

            function refreshAll(targetUrl = null) {
                let url = targetUrl;
                if (!url) {
                    const params = new URLSearchParams(window.location.search);
                    const q = document.getElementById('pendingFilter')?.value?.trim();
                    const p = document.getElementById('priorityFilter')?.value;
                    if (q) params.set('q', q);
                    else params.delete('q');
                    if (p) params.set('prioridad', p);
                    else params.delete('prioridad');
                    url = `${CONFIG.endpoints.pendingList}?${params.toString()}`;
                }

                const requestSequence = ++pendingSearchSequence;
                pendingListAbortController?.abort();
                pendingListAbortController = new AbortController();
                const spinner = document.getElementById('searchSpinner');
                if (spinner) spinner.classList.remove('hidden');

                fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        signal: pendingListAbortController.signal
                    })
                    .then(res => {
                        if (!res.ok) throw new Error('Error al recargar');
                        return res.text();
                    })
                    .then(html => {
                        if (requestSequence !== pendingSearchSequence) return;
                        if (spinner) spinner.classList.add('hidden');
                        const wrapper = document.getElementById('pendingListWrapper');
                        if (wrapper) wrapper.outerHTML = html;
                        applyFilters();
                        updateCalendarStatuses();
                        const blockView = document.getElementById('agendaBlockManagerView');
                        if (blockView && !blockView.classList.contains('hidden')) {
                            const cell = document.querySelector(
                                `.block-slot-button[data-block-label="${state.currentBlockLabel}"][data-block-date="${state.currentBlockDate}"]`
                            );
                            if (cell) renderBlockRequests(cell);
                            else closeBlockManager();
                        }
                    })
                    .catch(err => {
                        if (requestSequence !== pendingSearchSequence) return;
                        if (spinner) spinner.classList.add('hidden');
                        if (err.name !== 'AbortError') console.error('Refresh error:', err);
                    });
            }

            function updateCalendarStatuses() {
                document.querySelectorAll('.block-slot-button').forEach(btn => {
                    const status = btn.querySelector('.block-slot-status');
                    if (!status) return;

                    if (btn.dataset.assignedBlock === 'true') {
                        const assignedEstado = btn.dataset.assignedEstado || 'confirmada';
                        const dotColor = assignedEstado === 'realizada' ?
                            'bg-sky-600 shadow-[0_0_2px_rgba(2,132,199,0.8)]' : (assignedEstado ===
                                'no_asistio' ? 'bg-rose-500 shadow-[0_0_2px_rgba(244,63,94,0.8)]' :
                                'bg-emerald-500 shadow-[0_0_2px_rgba(16,185,129,0.8)]');
                        btn.className =
                            `block-slot-button w-full p-3 text-center transition-all drop-zone group text-sky-900 dark:text-sky-400 shadow-sm`;
                        status.innerHTML =
                            `<div class="flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full ${dotColor}"></span><p class="text-[10px] font-black leading-tight truncate opacity-70 group-hover:opacity-100 transition-opacity text-sky-900 dark:text-sky-400 tracking-wider">${Utils.escapeHtml(btn.dataset.assignedPaciente)}</p></div>`;
                    } else {
                        const isActive = btn.dataset.blockActive === 'true';
                        btn.className =
                            `block-slot-button w-full p-3 text-center transition-all drop-zone group ${isActive ? ' border-gray-200 dark:border-gray-700/60 text-gray-900 dark:text-gray-300 hover:border-sky-600 dark:hover:border-sky-600 hover:shadow-sm' : 'bg-amber-50 dark:bg-amber-950/40 border-amber-200 dark:border-amber-800/60 text-amber-700 dark:text-amber-400 hover:border-amber-500 dark:hover:border-amber-500'}`;
                        const cands = getCandidatesForBlock(btn.dataset.blockLabel, btn.dataset.blockDate);
                        const canceledHtml = status.querySelector('.mt-0\\.5')?.outerHTML || '';
                        const statusHtml = cands.length ?
                            `<div class="flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span><p class="text-[10px] font-black text-amber-600 dark:text-amber-400 uppercase tracking-wider">${cands.length} Solic.</p></div>` :
                            '<p class="text-[10px] font-black opacity-50 group-hover:opacity-100 transition-opacity text-gray-900 dark:text-gray-400 group-hover:text-gray-500 dark:group-hover:text-gray-400 uppercase tracking-wider">Libre</p>';
                        status.innerHTML = statusHtml + canceledHtml;
                    }
                });
            }

            function applyFilters() {
                const q = document.getElementById('pendingFilter')?.value?.toLowerCase() || '';
                const p = document.getElementById('priorityFilter')?.value || '';
                let count = 0;
                document.querySelectorAll('.pending-item').forEach(i => {
                    const match = (!q || (i.dataset.patientName || '').toLowerCase().includes(q) || (i
                            .dataset.patientCedula || '').toLowerCase().startsWith(q)) &&
                        (!p || i.dataset.prioridad === p);
                    i.style.display = match ? '' : 'none';
                    if (match) count++;
                });
                document.getElementById('pendingNoResultsMessage')?.classList.toggle('hidden', count > 0);
            }

            function closeModals() {
                ['citaDetailsModal', 'dailyAgendaModal', 'detalleCitaModal'].forEach(id => {
                    const m = document.getElementById(id);
                    if (m) {
                        m.classList.add('hidden');
                        m.classList.remove('flex');
                    }
                });
            }

            document.addEventListener('click', (e) => {
                const pendingPaginationLink = e.target.closest('#pendingListWrapper nav a');
                if (pendingPaginationLink) {
                    e.preventDefault();
                    refreshAll(pendingPaginationLink.href);
                    return;
                }

                const btn = e.target.closest('button, a');
                if (!btn) return;

                if (btn.classList.contains('detail-btn')) openCitaModal(btn.dataset.citaId);
                else if (btn.classList.contains('block-slot-button')) openBlockManager(btn);
                else if (btn.classList.contains('block-request-action-btn')) handleAction(btn.dataset
                    .action, btn.dataset.citaId, btn);
                else if (['closeCitaModal', 'closeDailyAgendaModal'].includes(btn.id)) closeModals();
                else if (btn.id === 'prevCitaBtn') openCitaModal(state.pendingCitaIds[state
                    .currentCitaIndex - 1]);
                else if (btn.id === 'nextCitaBtn') openCitaModal(state.pendingCitaIds[state
                    .currentCitaIndex + 1]);
                else if (btn.id === 'guardarPrioridadBtn') {
                    const sel = document.querySelector('.prioridad-radio:checked')?.value;
                    if (!sel) return;
                    Utils.api(CONFIG.endpoints.prioridad(state.currentCitaId), 'PATCH', {
                            prioridad: sel
                        })
                        .then(() => {
                            document.getElementById('prioridadMensaje').textContent = 'Actualizado.';
                            document.getElementById('prioridadMensaje').classList.remove('hidden');
                            refreshAll();
                        });
                } else if (btn.classList.contains('agregar-manual-btn')) {
                    openManualCitaModal(btn);
                }


            });

            document.getElementById('pendingFilter')?.addEventListener('input', () => {
                applyFilters();
                clearTimeout(pendingSearchTimeout);
                pendingSearchTimeout = setTimeout(() => refreshAll(), 400);
            });
            document.getElementById('priorityFilter')?.addEventListener('change', () => applyFilters());

            [document.getElementById('citaDetailsModal'), document.getElementById('dailyAgendaModal')].forEach(
                m => {
                    m?.addEventListener('click', (e) => {
                        if (e.target === m) closeModals();
                    });
                });

            let draggedId = null;
            document.addEventListener('dragstart', (e) => {
                const el = e.target.closest?.('.draggable-patient');
                if (el) {
                    draggedId = el.dataset.citaId;
                    el.classList.add('opacity-50');
                }
            });
            document.addEventListener('dragend', (e) => {
                const el = e.target.closest?.('.draggable-patient');
                if (el) el.classList.remove('opacity-50');
                draggedId = null;
            });
            document.addEventListener('dragover', (e) => {
                const zone = e.target.closest('.drop-zone, #blockRequestsList, #blockConfirmedContainer');
                if (zone) {
                    e.preventDefault();
                    if (zone.classList.contains('drop-zone')) zone.classList.add('ring-2', 'ring-sky-600',
                        'ring-offset-2', 'dark:ring-offset-gray-800');
                }
            });
            document.addEventListener('dragleave', (e) => {
                e.target.closest('.drop-zone')?.classList.remove('ring-2', 'ring-sky-400', 'ring-sky-600',
                    'ring-offset-2', 'dark:ring-offset-gray-800');
            });
            document.addEventListener('drop', (e) => {
                e.preventDefault();
                document.querySelectorAll('.drop-zone').forEach(z => z.classList.remove('ring-2',
                    'ring-sky-400', 'ring-sky-600', 'ring-offset-2', 'dark:ring-offset-gray-800'));
                if (!draggedId) return;

                const zone = e.target.closest('.drop-zone') || e.target.closest('#blockRequestsList') || e
                    .target.closest('#blockConfirmedContainer');
                if (!zone) {
                    draggedId = null;
                    return;
                }

                if (zone.classList.contains('drop-zone')) {
                    state.currentBlockLabel = zone.dataset.blockLabel;
                    if (zone.dataset.blockDate) state.currentBlockDate = zone.dataset.blockDate;
                }

                const timeMatch = state.currentBlockLabel?.match(/(\d{1,2}:\d{2})/);
                const timeStr = timeMatch ? timeMatch[1] : '00:00';
                const dropDT = state.currentBlockDate ? new Date(state.currentBlockDate + 'T' + timeStr +
                    ':00') : new Date();
                const draggedEl = document.querySelector(`li[data-cita-id="${draggedId}"]`);
                const isManual = draggedEl?.dataset.isManual === '1';
                const currentDraggedId = draggedId;
                draggedId = null;

                if (dropDT < new Date() && !isManual) {
                    AppModal.alert('Error', 'No puedes agendar citas en fechas u horas pasadas.');
                    return;
                }

                if (isManual) {
                    window.showConfirmManualCita(() => handleAction('accept', currentDraggedId));
                } else if (draggedEl && !draggedEl.dataset.bloquesSugeridos) {
                    handleAction('accept', currentDraggedId);
                } else {
                    handleAction('propose', currentDraggedId);
                }
            });

            function addSwipe(modal, onPrev, onNext) {
                if (!modal) return;
                let startX = 0,
                    startY = 0;
                modal.addEventListener('touchstart', (e) => {
                    startX = e.touches[0].clientX;
                    startY = e.touches[0].clientY;
                }, {
                    passive: true
                });
                modal.addEventListener('touchend', (e) => {
                    const dx = e.changedTouches[0].clientX - startX;
                    const dy = e.changedTouches[0].clientY - startY;
                    if (Math.abs(dx) > 50 && Math.abs(dx) > Math.abs(dy)) {
                        if (dx > 0) onPrev();
                        else onNext();
                    }
                }, {
                    passive: true
                });
            }
            addSwipe(document.getElementById('citaDetailsModal'), () => openCitaModal(state.pendingCitaIds[state
                .currentCitaIndex - 1]), () => openCitaModal(state.pendingCitaIds[state.currentCitaIndex +
                1]));
            addSwipe(document.getElementById('agendaBlockManagerView'), () => navigateBlock(-1), () =>
                navigateBlock(1));

            document.addEventListener('keydown', (e) => {
                const citaModal = document.getElementById('citaDetailsModal');
                const blockView = document.getElementById('agendaBlockManagerView');
                if (e.key === 'ArrowLeft' || e.key === 'Left') {
                    if (!citaModal?.classList.contains('hidden')) {
                        e.preventDefault();
                        openCitaModal(state.pendingCitaIds[state.currentCitaIndex - 1]);
                    } else if (!blockView?.classList.contains('hidden')) {
                        e.preventDefault();
                        navigateBlock(-1);
                    }
                } else if (e.key === 'ArrowRight' || e.key === 'Right') {
                    if (!citaModal?.classList.contains('hidden')) {
                        e.preventDefault();
                        openCitaModal(state.pendingCitaIds[state.currentCitaIndex + 1]);
                    } else if (!blockView?.classList.contains('hidden')) {
                        e.preventDefault();
                        navigateBlock(1);
                    }
                }
            });

            const initialParams = new URLSearchParams(window.location.search);
            if (initialParams.has('q')) document.getElementById('pendingFilter').value = initialParams.get('q');
            if (initialParams.has('prioridad')) document.getElementById('priorityFilter').value = initialParams.get(
                'prioridad');
            applyFilters();
            updateCalendarStatuses();

            window.handleAction = handleAction;
            window.refreshAll = refreshAll;
            window.clearCellAssignment = clearCellAssignment;
            window.updateCalendarStatuses = updateCalendarStatuses;
            window.applyFilters = applyFilters;
            window.closeModals = closeModals;
            window.navigateBlock = navigateBlock;
            window.openBlockManager = openBlockManager;
            window.openCitaModal = openCitaModal;

            setInterval(() => {
                if (!document.hidden) refreshAll();
            }, 10 * 60 * 1000);

            window.showConfirmManualCita = function(onConfirm) {
                window.AppModal.show('Confirmar Cita Manual',
                    'Esta cita fue creada manualmente por ti. No requiere contrapropuesta. ¿Confirmas el agendamiento para esta fecha y horario?', {
                        type: 'confirm',
                        btnText: 'SÍ, AGENDAR',
                        intent: 'info'
                    }).then(result => {
                    if (result) onConfirm();
                });
            };

            function openManualCitaModal(btn) {
                const pacienteId   = (btn.dataset.pacienteId || '').trim();
                const pacienteName = (btn.dataset.pacienteName || 'Paciente').trim();

                if (!pacienteId) {
                    AppModal.alert('Error', 'El botón no tiene un ID de paciente válido.');
                    return;
                }

                document.getElementById('manualPacienteId').value    = pacienteId;
                document.getElementById('manualPacienteName').value  = pacienteName;

                const hoy = new Date().toISOString().split('T')[0];
                const fechaInput = document.getElementById('manualFecha');
                fechaInput.value = hoy;
                fechaInput.min   = hoy;

                populateManualBlocks();

                const modal = document.getElementById('manualCitaModal');
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }

            window.closeManualCitaModal = function () {
                const modal = document.getElementById('manualCitaModal');
                if (modal) {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                }
            };

            function populateManualBlocks() {
                const fecha = document.getElementById('manualFecha').value;
                const select = document.getElementById('manualBloque');

                if (!fecha) {
                    select.innerHTML = '<option value="">Selecciona primero una fecha</option>';
                    return;
                }

                // Mapeo JS de día (0=Domingo ... 6=Sábado) a los nombres usados en el modelo
                const DIAS_JS = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
                const diaSemana = DIAS_JS[new Date(fecha + 'T12:00:00').getDay()];

                const bloquesDia = HORARIOS_PSICOLOGO.filter(h => h.dia === diaSemana);

                if (!bloquesDia.length) {
                    select.innerHTML = '<option value="">No hay horario configurado para este día</option>';
                    select.disabled = true;
                    return;
                }

                select.disabled = false;
                select.innerHTML = '<option value="">Selecciona un bloque</option>';
                bloquesDia.forEach(b => {
                    const opt = document.createElement('option');
                    opt.value = `${b.inicio}|${b.fin}`;
                    opt.textContent = `${b.inicio} - ${b.fin}`;
                    select.appendChild(opt);
                });
            }

            // Listener de cambio de fecha (una sola vez)
            document.getElementById('manualFecha')?.addEventListener('change', populateManualBlocks);

            // Submit del modal
            document.getElementById('manualCitaForm')?.addEventListener('submit', async function (e) {
                e.preventDefault();

                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.textContent;

                const pacienteId = document.getElementById('manualPacienteId').value;
                const fecha      = document.getElementById('manualFecha').value;
                const bloqueVal  = document.getElementById('manualBloque').value;
                const prioridad  = document.getElementById('manualPrioridad').value;

                if (!pacienteId || !fecha || !bloqueVal) {
                    AppModal.alert('Error', 'Completa todos los campos para agendar.');
                    return;
                }

                const [bloqueInicio, bloqueFin] = bloqueVal.split('|');

                submitBtn.disabled = true;
                submitBtn.textContent = 'Agendando...';

                try {
                    const response = await fetch('{{ route('admin.psicologia.maestros.agenda.crear_cita_manual') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept':       'application/json',
                            'X-CSRF-TOKEN': CONFIG.csrfToken
                        },
                        body: JSON.stringify({
                            paciente_id:   pacienteId,
                            fecha:         fecha,
                            hora:          bloqueInicio,
                            bloque_inicio: bloqueInicio,
                            bloque_fin:    bloqueFin,
                            prioridad:     prioridad
                        })
                    });

                    const data = await response.json();

                    if (!response.ok || !data.success) {
                        throw new Error(data.message || 'Error al agendar la cita.');
                    }

                    window.closeManualCitaModal();

                    if (typeof showToast === 'function') {
                        showToast(data.message || 'Cita agendada correctamente.', 'success');
                    }

                    // Refrescar pendientes + recargar la vista semanal para ver el bloque ocupado
                    if (typeof refreshAll === 'function') refreshAll();
                    setTimeout(() => window.location.reload(), 600);

                } catch (err) {
                    console.error('❌ Error creando cita manual:', err);
                    AppModal.alert('Error', err.message || 'No se pudo crear la cita.');
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                }
            });
        });

        function openDailyAgenda(cell, date) {
            if (!cell) return;
            const subtitle = document.getElementById('dailyAgendaSubtitle');
            const content = document.getElementById('dailyAgendaContent');
            if (!content) return;

            const parsedDate = new Date(date + 'T12:00:00');
            const dateFormatted = isNaN(parsedDate.getTime()) ? date : parsedDate.toLocaleDateString('es-ES', {
                weekday: 'long',
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            });
            if (subtitle) subtitle.textContent = dateFormatted;

            content.innerHTML = `<div class="flex flex-col items-center justify-center py-12 text-center">
                <div class="w-12 h-12 border-4 border-gray-200 dark:border-gray-700 border-t-sky-600 dark:border-t-sky-500 rounded-full animate-spin mb-4"></div>
                <p class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-wider">Cargando agenda...</p>
            </div>`;

            const m = document.getElementById('dailyAgendaModal');
            if (m) {
                m.classList.remove('hidden');
                m.classList.add('flex');
            }

            const psicologoId = new URLSearchParams(window.location.search).get('psicologo_id') || '{{ $psicologoId }}';
            const dailyCitasUrl = '{{ route('admin.psicologia.maestros.agenda.daily_citas') }}';

            fetch(`${dailyCitasUrl}?fecha=${date}&psicologo_id=${psicologoId}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(res => res.ok ? res.json() : Promise.reject(res))
                .then(citas => {
                    content.innerHTML = '';
                    if (!citas.length) {
                        content.innerHTML = `<div class="flex flex-col items-center justify-center py-12 text-center">
                            <div class="w-16 h-16 rounded-2xl flex items-center justify-center mb-4 border border-gray-200 dark:border-gray-700/60 shadow-sm">
                                <svg class="w-8 h-8 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <p class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-wider">Sin citas para este día</p>
                        </div>`;
                    } else {
                        citas.forEach(cita => {
                            const div = document.createElement('div');
                            div.className =
                                'flex items-center justify-between p-4 rounded-2xl border border-gray-200 dark:border-gray-700/60 transition-all group shadow-sm';
                            const badgeMap = {
                                confirmada: 'bg-sky-50 dark:bg-sky-950/40 text-sky-600 dark:text-sky-400 border-sky-200 dark:border-sky-800/60',
                                realizada: 'bg-sky-600 dark:bg-sky-950/40 text-sky-600 dark:text-sky-400 border-sky-200 dark:border-sky-800/60',
                                no_asistio: 'bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 border-rose-200 dark:border-rose-800/60'
                            };
                            const badgeClass = badgeMap[cita.estado] ||
                                'bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-400 border-gray-200 dark:border-gray-700/60';
                            div.innerHTML = `
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-2xl border border-sky-200 dark:border-sky-800/60 flex items-center justify-center text-sky-600 dark:text-sky-400 font-black shadow-sm transition-transform">
                                        ${cita.hora !== 'S/H' ? cita.hora.split(':')[0] : '--'}
                                    </div>
                                    <div>
                                        <p class="text-sm font-black text-gray-900 dark:text-white leading-none mb-1.5">${cita.paciente}</p>
                                        <p class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-wider">${cita.hora}</p>
                                    </div>
                                </div>
                                <span class="px-3 py-1.5 rounded-xl border text-[9px] font-black uppercase tracking-wider ${badgeClass}">
                                    ${cita.estado === 'no_asistio' ? 'Ausente' : cita.estado}
                                </span>`;
                            content.appendChild(div);
                        });
                    }
                })
                .catch(err => {
                    console.error(err);
                    content.innerHTML =
                        `<p class="text-center text-rose-600 dark:text-rose-400 text-xs font-black uppercase tracking-wider py-8">Error al cargar citas.</p>`;
                });
        }

        function abrirDetalleCita(data) {
            const currentCitaId = data.id || data.cita_id;
            if (!currentCitaId) {
                console.error('ID de cita no encontrado:', data);
                return;
            }

            document.getElementById('modalCitaPaciente').textContent = data.paciente || '—';
            document.getElementById('modalCitaInitial').textContent = data.paciente ? data.paciente.charAt(0)
                .toUpperCase() : '—';
            document.getElementById('modalCitaSolicitud').textContent = data.fecha_solicitud || '—';
            document.getElementById('modalCitaProgramada').textContent = data.fecha_programada || '—';

            let estadoLabel = data.estado ? data.estado.replace(/_/g, ' ') : '—';
            if (data.estado === 'cancelada' && data.cancelado_por) {
                estadoLabel = data.cancelado_por === 'paciente' ? 'Cancelada por el paciente' :
                    'Cancelada por el psicólogo';
            }
            document.getElementById('modalCitaEstado').textContent = estadoLabel;
            document.getElementById('modalCitaPrioridad').textContent = data.prioridad || 'Normal';
            document.getElementById('modalCitaMotivo').textContent = data.motivo || 'No especificado';

            const cancelInfo = document.getElementById('modalCitaCancelInfo');
            if (data.cancelado_por || data.motivo_rechazo) {
                cancelInfo.classList.remove('hidden');
                if (data.motivo_rechazo) {
                    document.getElementById('modalCitaCancelLabel').textContent = 'Motivo de Rechazo';
                    document.getElementById('modalCitaCancelValue').textContent = data.motivo_rechazo;
                } else {
                    document.getElementById('modalCitaCancelLabel').textContent = 'Cancelado por';
                    document.getElementById('modalCitaCancelValue').textContent = data.cancelado_por === 'paciente' ?
                        'El paciente' : 'El psicólogo';
                }
            } else {
                cancelInfo.classList.add('hidden');
            }

            const actionsContainer = document.getElementById('detalleCitaActions');
            if (actionsContainer) {
                actionsContainer.querySelectorAll('.dynamic-action-btn').forEach(b => b.remove());
                if (data.estado === 'confirmada' || data.estado === 'confirmada_reprogramada') {
                    const id = currentCitaId;
                    const setBlock = () => {
                        if (data.fecha_programada_iso) window.state ? window.state.currentBlockDate = data
                            .fecha_programada_iso : null;
                        if (data.hora_programada_iso) window.state ? window.state.currentBlockLabel = data
                            .hora_programada_iso : null;
                    };

                    const actions = [{
                            label: 'Realizada',
                            cls: 'bg-sky-600 hover:bg-sky-700 text-white shadow-sm',
                            action: 'realizar'
                        },
                        {
                            label: 'Ausente',
                            cls: 'bg-rose-600 hover:bg-rose-700 text-white shadow-sm',
                            action: 'no_asistio'
                        },
                        {
                            label: 'Reagendar',
                            cls: 'bg-amber-500 hover:bg-amber-600 text-white shadow-sm',
                            action: 'posponer'
                        },
                        {
                            label: 'Cancelar',
                            cls: 'bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700/60 shadow-sm',
                            action: 'cancelar'
                        },
                    ];
                    actions.forEach(({
                        label,
                        cls,
                        action
                    }) => {
                        const b = document.createElement('button');
                        b.className =
                            `dynamic-action-btn px-5 py-2.5 ${cls} rounded-2xl font-black text-xs transition-all uppercase tracking-wider active:scale-95 w-full sm:w-auto`;
                        b.textContent = label;
                        b.onclick = () => {
                            cerrarDetalleCita();
                            setBlock();
                            if (typeof handleAction === 'function') handleAction(action, id);
                        };
                        actionsContainer.insertBefore(b, actionsContainer.firstChild);
                    });
                }
            }

            const modal = document.getElementById('detalleCitaModal');
            if (modal) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }
        }

        function cerrarDetalleCita() {
            const modal = document.getElementById('detalleCitaModal');
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        }

        function dismissCancelMessage(event, citaId) {
            event.stopPropagation();
            fetch(`admin/psicologia/maestros/citas/${citaId}/dismiss-cancel`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                }).then(r => r.json()).then(data => {
                    if (data.success) window.location.reload();
                    else alert(data.message || 'Error al ocultar el mensaje.');
                })
                .catch(err => {
                    console.error('Error:', err);
                    alert('Ocurrió un error.');
                });
        }

        window.enviarPropuesta = function(citaId) {
            const btn = document.getElementById('enviarPropuestaBtn');
            if (btn) {
                btn.disabled = true;
                btn.classList.add('opacity-50', 'cursor-wait');
                btn.innerText = 'Enviando...';
            }
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            fetch(`admin/psicologia/maestros/citas/${citaId}/enviar-propuesta`, {
                    method: 'PATCH',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                }).then(r => r.ok ? r.json() : Promise.reject(r))
                .then(res => {
                    if (res.status === 'error') throw new Error(res.message);
                    if (typeof showToast === 'function') showToast(res.message || 'Propuesta enviada al paciente.',
                        'success');
                    if (typeof closeModals === 'function') closeModals();
                    if (typeof refreshAll === 'function') refreshAll();
                })
                .catch(err => {
                    console.error(err);
                    if (btn) {
                        btn.disabled = false;
                        btn.classList.remove('opacity-50', 'cursor-wait');
                        btn.innerHTML = 'ENVIAR PROPUESTA AL PACIENTE';
                    }
                    err instanceof Error ? AppModal.alert('Error', err.message) : err?.json ? err.json().then(d =>
                        AppModal.alert('Error', d.message || 'Error al enviar.')).catch(() => AppModal.alert(
                        'Error', 'Error al enviar.')) : AppModal.alert('Error',
                        'Error al enviar la contrapropuesta.');
                });
        };
    </script>
    <div id="dailyAgendaModal"
        class="fixed inset-0 z-[140] hidden items-center justify-center bg-gray-900/40 backdrop-blur-sm p-4 transition-all">
        <div style="background-color: var(--bg-card); border-color: var(--border-color);"
            class="bg-white dark:bg-gray-800 w-full max-w-lg rounded-3xl shadow-2xl flex flex-col max-h-[85vh] overflow-hidden border border-gray-200 dark:border-gray-700/60">
            <div
                class="p-6 border-b border-gray-200 dark:border-gray-700/60 flex justify-between items-center bg-gray-200/40 dark:bg-black/20">
                <div>
                    <h3 class="text-lg font-black text-gray-900 dark:text-white tracking-tight uppercase">Agenda del
                        Día</h3>
                    <p id="dailyAgendaSubtitle"
                        class="text-xs font-extrabold text-gray-400 dark:text-gray-500 mt-0.5 tracking-wider"></p>
                </div>
                <button id="closeDailyAgendaModal" type="button"
                    class="w-10 h-10 flex items-center justify-center rounded-2xl text-gray-400 dark:text-gray-400 hover:bg-white dark:hover:bg-gray-700 transition-all shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div style="background-color: var(--bg-card); border-color: var(--border-color);">
                <div id="dailyAgendaContent" class="p-6 overflow-y-auto space-y-4 custom-scrollbar flex-1"></div>
            </div>

        </div>
    </div>

    <div id="detalleCitaModal"
        class="hidden fixed inset-0 bg-gray-900/40 backdrop-blur-sm items-center justify-center z-[100]"
        onclick="if(event.target===this){cerrarDetalleCita()}">
        <div style="background-color: var(--bg-card); border-color: var(--border-color); color: var(--text-main);"
            class="rounded-2xl border shadow-2xl w-full max-w-lg mx-4 overflow-hidden p-6 sm:p-8">

            <div class="flex items-center justify-between mb-6">
                <h4 class="text-xl sm:text-2xl font-extrabold tracking-tight" style="color: var(--text-main);">
                    Detalle de la Cita
                </h4>
                <button onclick="cerrarDetalleCita()"
                    class="w-9 h-9 rounded-xl border border-gray-200 dark:border-gray-700/60 text-gray-400 hover:text-{{ $themeColor ?? 'indigo' }}-600 hover:border-{{ $themeColor ?? 'indigo' }}-300 inline-flex items-center justify-center transition-all active:scale-95"
                    style="background-color: rgba(0,0,0,0.02);">
                    <i class="fas fa-xmark text-sm"></i>
                </button>
            </div>

            <div class="space-y-6">
                <div class="flex items-center gap-3 p-4 rounded-xl border border-{{ $themeColor ?? 'indigo' }}-200 dark:border-{{ $themeColor ?? 'indigo' }}-800/40"
                    style="background-color: rgba(0,0,0,0.02);">
                    <div id="modalCitaInitial"
                        class="w-10 h-10 text-{{ $themeColor ?? 'indigo' }}-600 dark:text-{{ $themeColor ?? 'indigo' }}-400 border border-{{ $themeColor ?? 'indigo' }}-200 dark:border-{{ $themeColor ?? 'indigo' }}-800/40 rounded-xl flex items-center justify-center text-sm font-black uppercase"
                        style="background-color: rgba(0,0,0,0.02);">—</div>
                    <div>
                        <span class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-0.5">
                            Paciente
                        </span>
                        <p id="modalCitaPaciente" class="text-sm font-bold" style="color: var(--text-main);">—</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="p-4 rounded-xl border border-gray-100 dark:border-gray-800"
                        style="background-color: rgba(0,0,0,0.02);">
                        <span class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-1">
                            Fecha Solicitada
                        </span>
                        <p id="modalCitaSolicitud" class="text-xs font-bold" style="color: var(--text-main);">—</p>
                    </div>

                    <div class="p-4 rounded-xl border border-gray-100 dark:border-gray-800"
                        style="background-color: rgba(0,0,0,0.02);">
                        <span class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-1">
                            Fecha Programada
                        </span>
                        <p id="modalCitaProgramada" class="text-xs font-bold" style="color: var(--text-main);">—</p>
                    </div>

                    <div class="p-4 rounded-xl border border-gray-100 dark:border-gray-800"
                        style="background-color: rgba(0,0,0,0.02);">
                        <span class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-1">
                            Estado
                        </span>
                        <p id="modalCitaEstado" class="text-xs font-bold capitalize"
                            style="color: var(--text-main);">—</p>
                    </div>

                    <div class="p-4 rounded-xl border border-gray-100 dark:border-gray-800"
                        style="background-color: rgba(0,0,0,0.02);">
                        <span class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-1">
                            Prioridad
                        </span>
                        <p id="modalCitaPrioridad" class="text-xs font-bold capitalize"
                            style="color: var(--text-main);">—</p>
                    </div>
                </div>

                <div class="p-4 rounded-xl border border-gray-100 dark:border-gray-800"
                    style="background-color: rgba(0,0,0,0.02);">
                    <span class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-1">
                        Motivo de Consulta
                    </span>
                    <p id="modalCitaMotivo" class="text-xs font-medium text-gray-600 dark:text-gray-300">—</p>
                </div>

                <div id="modalCitaCancelInfo"
                    class="hidden p-4 rounded-xl border border-rose-200 dark:border-rose-800/40 bg-rose-50/50 dark:bg-rose-950/20">
                    <span id="modalCitaCancelLabel"
                        class="block text-[10px] font-black uppercase tracking-wider text-rose-600 dark:text-rose-400 mb-1">
                        Cancelado por
                    </span>
                    <p id="modalCitaCancelValue" class="text-xs font-bold text-rose-700 dark:text-rose-300">—</p>
                </div>
            </div>

            <div class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-800 flex items-center justify-end gap-3"
                id="detalleCitaActions">
                <button onclick="cerrarDetalleCita()"
                    class="px-5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 text-xs font-bold text-gray-600 dark:text-gray-300 transition-all active:scale-95">
                    Cerrar
                </button>
            </div>
        </div>
    </div>

    <div id="filterModal"
        class="fixed inset-0 z-[150] hidden items-center justify-center bg-gray-900/40 backdrop-blur-sm p-4 transition-all">
        <div style="background-color: var(--bg-card); border-color: var(--border-color); color: var(--text-main);"
            class="w-full max-w-md rounded-2xl shadow-sm border flex flex-col max-h-[85vh] overflow-hidden">
            <div class="p-6 flex justify-between items-center border-b border-gray-100 dark:border-gray-800">
                <h3 class="text-xl sm:text-2xl font-extrabold tracking-tight" style="color: var(--text-main);">Filtrar
                    Historial</h3>
                <button type="button"
                    onclick="document.getElementById('filterModal').classList.add('hidden'); document.getElementById('filterModal').classList.remove('flex');"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form method="GET" action="{{ route('admin.psicologia.maestros.agenda.index') }}"
                class="p-6 overflow-y-auto space-y-6 custom-scrollbar flex-1">
                <input type="hidden" name="view" value="list">
                <input type="hidden" name="psicologo_id" value="{{ $psicologoId }}">
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-2">Fecha
                        Desde</label>
                    <input type="date" name="from_date" value="{{ request('from_date') }}"
                        style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                        class="w-full h-11 px-4 border rounded-xl focus:outline-none focus:ring-2 {{ $focusRingClass }} text-sm font-medium transition-all">
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-2">Fecha
                        Hasta</label>
                    <input type="date" name="to_date" value="{{ request('to_date') }}"
                        style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                        class="w-full h-11 px-4 border rounded-xl focus:outline-none focus:ring-2 {{ $focusRingClass }} text-sm font-medium transition-all">
                </div>
                <div>
                    <label
                        class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-2">Estado</label>
                    <select name="estado"
                        style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                        class="w-full h-11 px-4 border rounded-xl focus:outline-none focus:ring-2 {{ $focusRingClass }} text-sm font-medium transition-all">
                        <option value="">Todos los estados</option>
                        <option value="confirmada" {{ request('estado') === 'confirmada' ? 'selected' : '' }}>
                            Confirmada</option>
                        <option value="realizada" {{ request('estado') === 'realizada' ? 'selected' : '' }}>
                            Realizada</option>
                        <option value="no_asistio" {{ request('estado') === 'no_asistio' ? 'selected' : '' }}>No
                            Asistió</option>
                        <option value="cancelada" {{ request('estado') === 'cancelada' ? 'selected' : '' }}>
                            Cancelada</option>
                    </select>
                </div>
                <div
                    class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-800 flex items-center justify-end gap-3">
                    <button type="button"
                        onclick="document.getElementById('filterModal').classList.add('hidden'); document.getElementById('filterModal').classList.remove('flex');"
                        class="px-5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 text-xs font-bold text-gray-600 dark:text-gray-300 transition-all">Cancelar</button>
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl {{ $btnClass }} text-white text-xs font-bold shadow-md active:scale-95 transition-all">Aplicar
                        Filtros</button>
                </div>
            </form>
        </div>
    </div>

    <div id="confirmModal"
        class="fixed inset-0 z-[150] hidden items-center justify-center bg-gray-900/40 backdrop-blur-sm p-4">
        <div style="background-color: var(--bg-card); border-color: var(--border-color); color: var(--text-main);"
            class="rounded-2xl p-6 sm:p-8 max-w-sm w-full shadow-sm border">
            <div id="confirmIconBox"
                class="w-16 h-16 bg-{{ $themeColor }}-50 dark:bg-{{ $themeColor }}-950/40 text-{{ $themeColor }}-600 dark:text-{{ $themeColor }}-400 border border-{{ $themeColor }}-100 dark:border-{{ $themeColor }}-900/30 rounded-2xl flex items-center justify-center mb-6 mx-auto">
                <svg id="confirmIconSvg" class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h3 id="confirmTitle" class="text-xl font-extrabold text-center mb-2 tracking-tight"
                style="color: var(--text-main);"></h3>
            <p id="confirmText"
                class="text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400 text-center mb-6 leading-relaxed">
            </p>
            <div id="confirmInputArea" class="hidden mb-6">
                <label id="confirmInputLabel"
                    class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-2">Motivo</label>
                <textarea id="confirmInputField" rows="3"
                    style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                    class="w-full px-4 py-3 border rounded-xl focus:outline-none focus:ring-2 {{ $focusRingClass }} text-sm font-medium transition-all resize-none"></textarea>
            </div>
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-800">
                <button id="confirmNoBtn"
                    class="flex-1 px-5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 text-xs font-bold text-gray-600 dark:text-gray-300 transition-all">Cancelar</button>
                <button id="confirmYesBtn"
                    class="flex-1 px-6 py-2.5 rounded-xl {{ $btnClass }} text-white text-xs font-bold shadow-md active:scale-95 transition-all">Aceptar</button>
            </div>
        </div>
    </div>

    {{-- MODAL: Agendar cita manual --}}
    <div id="manualCitaModal"
        class="fixed inset-0 z-[160] hidden items-center justify-center bg-gray-900/40 backdrop-blur-sm p-4">
        <div style="background-color: var(--bg-card); border-color: var(--border-color); color: var(--text-main);"
            class="w-full max-w-md rounded-2xl shadow-2xl border overflow-hidden">

            <div class="p-5 flex justify-between items-center border-b border-gray-100 dark:border-gray-800">
                <div>
                    <h3 class="text-lg font-black tracking-tight" style="color: var(--text-main);">
                        Agendar Cita Manual
                    </h3>
                    <p class="text-[10px] font-black uppercase tracking-wider text-gray-400 mt-0.5">
                        Asignación directa al calendario
                    </p>
                </div>
                <button type="button" onclick="closeManualCitaModal()"
                    class="w-9 h-9 rounded-xl border border-gray-200 dark:border-gray-700/60 text-gray-400 hover:text-rose-500 hover:border-rose-300 inline-flex items-center justify-center transition-all active:scale-95"
                    style="background-color: rgba(0,0,0,0.02);">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form id="manualCitaForm" class="p-5 space-y-4">

                <div>
                    <label class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-1.5">
                        Paciente
                    </label>
                    <input type="hidden" id="manualPacienteId">
                    <input type="text" id="manualPacienteName" readonly
                        class="w-full h-11 px-4 border rounded-xl text-sm font-bold focus:outline-none cursor-not-allowed"
                        style="background-color: rgba(0,0,0,0.04); border-color: var(--border-color); color: var(--text-main);">
                </div>

                <div>
                    <label class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-1.5">
                        Fecha <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" id="manualFecha" required
                        class="w-full h-11 px-4 border rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 text-sm font-medium transition-all"
                        style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);">
                </div>

                <div>
                    <label class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-1.5">
                        Bloque horario <span class="text-rose-500">*</span>
                    </label>
                    <select id="manualBloque" required
                        class="w-full h-11 px-4 border rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 text-sm font-medium transition-all"
                        style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);">
                        <option value="">Selecciona un bloque</option>
                    </select>
                    <p class="mt-1.5 text-[10px] font-medium text-gray-400 leading-tight">
                        Solo se muestran los bloques de tu horario activo para el día seleccionado.
                    </p>
                </div>

                <div>
                    <label class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-1.5">
                        Prioridad
                    </label>
                    <select id="manualPrioridad"
                        class="w-full h-11 px-4 border rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 text-sm font-medium transition-all"
                        style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);">
                        <option value="baja">Baja</option>
                        <option value="media" selected>Media</option>
                        <option value="alta">Alta</option>
                        <option value="crítica">Crítica</option>
                    </select>
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t border-gray-100 dark:border-gray-800">
                    <button type="button" onclick="closeManualCitaModal()"
                        class="px-5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 text-xs font-bold text-gray-600 dark:text-gray-300 transition-all">
                        Cancelar
                    </button>
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold shadow-md active:scale-95 transition-all">
                        <i class="fas fa-calendar-check text-xs"></i>
                        <span>Agendar</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    @include('components.cita-details-modal')
    @include('components.aviso-atencion-modal')
    @include('admin.psicologia.maestros.agenda.components.patient-modal')
</x-app-layout>
