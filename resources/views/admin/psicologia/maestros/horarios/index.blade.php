@php
    use Carbon\Carbon;

    $moduloActivo = strtolower(session('modulo_activo', 'general'));
    $esPsicologia = in_array($moduloActivo, ['psicologia', 'psicología', 'mental']);

    $themeColor = $esPsicologia ? 'indigo' : 'red';
    $btnClass = $esPsicologia ? 'bg-indigo-600 hover:bg-indigo-700' : 'bg-red-600 hover:bg-red-700';
    $focusRingClass = $esPsicologia
        ? 'focus:ring-indigo-500/20 focus:border-indigo-500'
        : 'focus:ring-red-500/20 focus:border-red-500';

    $BLOQUES = [
        'Matutino' => [
            ['inicio' => '07:00', 'fin' => '08:15'],
            ['inicio' => '08:15', 'fin' => '09:00'],
            ['inicio' => '09:20', 'fin' => '10:00'],
            ['inicio' => '10:00', 'fin' => '10:45'],
            ['inicio' => '10:45', 'fin' => '11:30'],
            ['inicio' => '11:30', 'fin' => '12:00'],
        ],
        'Vespertino' => [
            ['inicio' => '13:45', 'fin' => '14:25'],
            ['inicio' => '14:25', 'fin' => '15:05'],
            ['inicio' => '15:05', 'fin' => '15:45'],
            ['inicio' => '15:45', 'fin' => '16:40'],
            ['inicio' => '16:40', 'fin' => '17:20'],
            ['inicio' => '17:20', 'fin' => '18:00'],
        ],
        'Nocturno' => [
            ['inicio' => '18:00', 'fin' => '18:35'],
            ['inicio' => '18:35', 'fin' => '19:10'],
            ['inicio' => '19:10', 'fin' => '19:45'],
            ['inicio' => '19:45', 'fin' => '20:20'],
            ['inicio' => '20:20', 'fin' => '20:55'],
            ['inicio' => '20:55', 'fin' => '21:30'],
        ],
    ];

    $DIAS = $dias;
    $totalJornadas = count($BLOQUES);

    // Mapa de bloques existentes para búsqueda rápida
    $horariosMap = [];
    foreach ($horarios as $h) {
        $key = $h->dia . '|' . Carbon::parse($h->hora_inicio)->format('H:i') . '|' . Carbon::parse($h->hora_fin)->format('H:i');
        $horariosMap[$key] = $h;
    }
@endphp

<x-app-layout>
    <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @include('components.alert')

            {{-- Header --}}
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-black tracking-tight flex items-center gap-3"
                        style="color: var(--text-main);">
                        <span>Horarios de Salud Mental</span>
                        <span
                            class="px-2.5 py-1 text-xs font-bold rounded-full bg-{{ $themeColor }}-100 text-{{ $themeColor }}-700 dark:bg-{{ $themeColor }}-950/60 dark:text-{{ $themeColor }}-300">
                            Gestión Semanal
                        </span>
                    </h1>
                    <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                        Bienvenido <span
                            class="font-bold text-gray-700 dark:text-gray-200">{{ auth()->user()->nombre_completo ?? (auth()->user()->name ?? 'Usuario') }}</span>
                        · {{ Carbon::now()->format('d/m/Y') }}
                    </p>
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    {{-- PDF --}}
                    <a href="{{ route('admin.psicologia.maestros.horarios.exportarPdf', isset($grupoActivo) ? ['grupo' => $grupoActivo->id] : []) }}"
                        target="_blank"
                        class="p-2.5 rounded-xl text-gray-500 dark:text-gray-400 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition-all border border-gray-200 dark:border-gray-700 flex items-center justify-center"
                        title="Descargar PDF">
                        <i class="fas fa-file-pdf text-sm"></i>
                    </a>

                    {{-- Guardar como grupo --}}
                    <button id="openGroupModal" type="button"
                        {{ isset($grupoActivo) || !empty($tieneCitasPendientes) ? 'disabled' : '' }}
                        class="p-2.5 rounded-xl text-gray-500 dark:text-gray-400 hover:text-emerald-600 dark:hover:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-950/30 transition-all border border-gray-200 dark:border-gray-700 flex items-center justify-center disabled:opacity-40 disabled:cursor-not-allowed"
                        title="Guardar como grupo de horarios">
                        <i class="fas fa-save text-sm"></i>
                    </button>

                    {{-- Ver grupos --}}
                    <a href="{{ route('admin.psicologia.maestros.grupos_horarios.index') }}"
                        class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 text-xs font-bold text-gray-600 dark:text-gray-300 transition-all">
                        <i class="fas fa-layer-group text-xs"></i>
                        <span>Ver Grupos</span>
                    </a>

                    {{-- Gestionar Horarios (Crear / Editar) --}}
                    <a href="{{ route('admin.psicologia.maestros.horarios.create', isset($grupoSeleccionado) ? ['grupo' => $grupoSeleccionado->id] : []) }}"
                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl {{ $btnClass }} text-white font-bold text-sm shadow-md active:scale-95 transition-all">
                        <i class="fas fa-calendar-plus text-xs"></i>
                        <span>Gestionar Horarios</span>
                    </a>
                </div>
            </div>

            @if (session('error'))
                <div
                    class="p-4 mb-6 text-sm text-rose-800 rounded-2xl bg-rose-50 dark:bg-rose-900/30 dark:text-rose-400 border border-rose-200 dark:border-rose-800 flex items-center gap-3">
                    <i class="fas fa-exclamation-circle text-rose-600 dark:text-rose-400 text-lg"></i>
                    <span><strong
                            class="font-black uppercase tracking-wider text-[10px] block mb-0.5">Error</strong>{{ session('error') }}</span>
                </div>
            @endif

            {{-- Banner de grupo seleccionado --}}
            @isset($grupoSeleccionado)
                <div
                    class="mb-6 p-4 rounded-2xl bg-{{ $themeColor }}-50 dark:bg-{{ $themeColor }}-950/40 border border-{{ $themeColor }}-200 dark:border-{{ $themeColor }}-800/60 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-xs">
                    <div class="flex items-center gap-3 min-w-0">
                        <div
                            class="w-10 h-10 rounded-xl bg-{{ $themeColor }}-100 dark:bg-{{ $themeColor }}-900/50 text-{{ $themeColor }}-600 dark:text-{{ $themeColor }}-400 flex items-center justify-center shrink-0">
                            <i class="fas fa-calendar-week text-base"></i>
                        </div>
                        <span class="text-xs sm:text-sm font-medium text-{{ $themeColor }}-900 dark:text-{{ $themeColor }}-300">
                            Estás viendo los bloques del grupo <strong
                                class="font-bold uppercase">{{ $grupoSeleccionado->nombre }}</strong>.
                        </span>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <a href="{{ route('admin.psicologia.maestros.horarios.index') }}"
                            class="px-3 py-1.5 text-xs font-bold bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all">
                            Ver horario activo
                        </a>
                    </div>
                </div>
            @endisset

            {{-- Badge activo / sin activo --}}
            @if (!isset($grupoSeleccionado))
                @if (isset($grupoActivo))
                    <div class="mb-6 flex items-center gap-2">
                        <span
                            class="inline-flex items-center px-3 py-1.5 rounded-lg text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800 uppercase tracking-wider">
                            <i class="fas fa-check-circle me-1 text-[10px]"></i> Horario activo:
                            {{ $grupoActivo->nombre }}
                        </span>
                    </div>
                @else
                    <div class="mb-6 flex items-center gap-2">
                        <span
                            class="inline-flex items-center px-3 py-1.5 rounded-lg text-[10px] font-bold bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-800 uppercase tracking-wider">
                            <i class="fas fa-exclamation-triangle me-1 text-[10px]"></i> Sin horario activo
                        </span>
                    </div>
                @endif
            @endif

            {{-- Alerta citas pendientes --}}
            @if (!empty($tieneCitasPendientes))
                <div
                    class="mb-6 p-4 text-xs sm:text-sm text-amber-800 dark:text-amber-400 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 flex items-center gap-3 shadow-xs">
                    <i class="fas fa-lock text-amber-600 dark:text-amber-400 text-base shrink-0"></i>
                    <span><strong>Acción Restringida:</strong> No puedes editar ni eliminar bloques de horario mientras
                        tengas citas pendientes o en espera.</span>
                </div>
            @endif

            {{-- Cuadrícula semanal por jornada --}}
            @foreach ($BLOQUES as $jornada => $bloques)
                @php $jornadaIndex = $loop->index; @endphp

                <div id="jornada-block-{{ $jornadaIndex }}"
                    class="jornada-block mb-8 {{ $jornadaIndex !== 0 ? 'hidden' : '' }}">

                    {{-- Encabezado --}}
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-3 mb-4">
                        <div class="hidden sm:block sm:w-44"></div>

                        <div
                            class="flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-gray-100 dark:bg-gray-800/70 border border-gray-200/80 dark:border-gray-700/60 shadow-sm text-center">
                            <h3
                                class="text-xs sm:text-sm font-black uppercase tracking-wider text-gray-700 dark:text-gray-200">
                                Jornada {{ $jornada }}
                            </h3>
                        </div>

                        <div style="background-color: var(--bg-card); border-color: var(--border-color); color: var(--text-main);"
                            class="flex items-center justify-between md:justify-center gap-1 border border-gray-200 dark:border-gray-700/60 p-1 h-12 rounded-2xl shadow-sm flex-shrink-0 w-full sm:w-auto">
                            <button type="button" onclick="cambiarJornada(-1)" title="Jornada Anterior"
                                class="w-9 h-9 flex items-center justify-center rounded-xl hover:bg-white dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-all flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>

                            <span
                                class="px-2 sm:px-4 text-[10px] sm:text-[11px] font-black text-gray-800 dark:text-gray-200 min-w-[90px] text-center uppercase tracking-wider leading-none whitespace-nowrap">
                                {{ $loop->iteration }} / {{ $totalJornadas }}
                            </span>

                            <button type="button" onclick="cambiarJornada(1)" title="Siguiente Jornada"
                                class="w-9 h-9 flex items-center justify-center rounded-xl hover:bg-white dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-all flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Tabla --}}
                    <div style="border-color: var(--border-color); background-color: var(--bg-card);"
                        class="rounded-2xl border shadow-sm overflow-x-auto">
                        <div class="grid min-w-[750px]" style="grid-template-columns: 130px repeat(5, 1fr);">

                            {{-- Header --}}
                            <div class="p-3 border-b border-r bg-gray-50/70 dark:bg-black/30 flex items-center justify-center font-bold text-[11px] text-gray-400 uppercase tracking-wider"
                                style="border-color: var(--border-color);">
                                <i class="far fa-clock mr-1.5"></i> Bloque
                            </div>
                            @foreach ($DIAS as $diaLabel)
                                <div class="p-3 text-center text-[11px] font-black uppercase tracking-wider text-gray-600 dark:text-gray-300 border-b bg-gray-50/70 dark:bg-black/30 {{ !$loop->last ? 'border-r' : '' }}"
                                    style="border-color: var(--border-color);">
                                    {{ $diaLabel }}
                                </div>
                            @endforeach

                            {{-- Filas --}}
                            @foreach ($bloques as $bloque)
                                <div class="p-4 flex items-center justify-center text-center text-xs font-extrabold text-gray-700 dark:text-gray-200 whitespace-nowrap border-r bg-gray-50/30 dark:bg-black/10 {{ !$loop->last ? 'border-b' : '' }}"
                                    style="border-color: var(--border-color);">
                                    {{ Carbon::parse($bloque['inicio'])->format('g:i') }} -
                                    {{ Carbon::parse($bloque['fin'])->format('g:i') }}
                                </div>

                                @foreach ($DIAS as $diaLabel)
                                    @php
                                        $key = "{$diaLabel}|{$bloque['inicio']}|{$bloque['fin']}";
                                        $registro = $horariosMap[$key] ?? null;
                                    @endphp

                                    <div class="p-4 flex flex-col gap-1 items-center justify-center min-h-[68px] {{ !$loop->last ? 'border-r' : '' }} {{ !$loop->parent->last ? 'border-b' : '' }}"
                                        style="border-color: var(--border-color);">

                                        @if ($registro)
                                            <div class="w-full rounded-xl bg-{{ $themeColor }}-300/20 hover:bg-{{ $themeColor }}-300/30 dark:bg-{{ $themeColor }}-800/30 dark:hover:bg-{{ $themeColor }}-800/50 border border-{{ $themeColor }}-200 dark:border-{{ $themeColor }}-800 p-2 flex items-center justify-between gap-2 shadow-sm transition-all">
                                                <div class="flex items-center gap-2 min-w-0 flex-1">
                                                    <i
                                                        class="fas fa-user-md text-[12px] text-{{ $themeColor }}-600 dark:text-{{ $themeColor }}-400 flex-shrink-0"></i>
                                                    <div class="min-w-0 leading-tight">
                                                        <span
                                                            class="block text-[12px] font-bold text-{{ $themeColor }}-900 dark:text-{{ $themeColor }}-100 truncate">
                                                            Ocupado
                                                        </span>
                                                        <span
                                                            class="block text-[10px] font-medium text-{{ $themeColor }}-600 dark:text-{{ $themeColor }}-400 truncate">
                                                            {{ \Carbon\Carbon::parse($registro->hora_inicio)->format('g:i A') }}
                                                            -
                                                            {{ \Carbon\Carbon::parse($registro->hora_fin)->format('g:i A') }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <span
                                                    class="w-2 h-2 rounded-full bg-emerald-500 shadow-[0_0_6px_rgba(16,185,129,0.8)] flex-shrink-0"></span>
                                            </div>
                                        @else
                                            <div
                                                class="w-full h-full min-h-[58px] rounded-xl border border-dashed border-gray-300 dark:border-gray-700 bg-gray-50/50 dark:bg-black/10 flex items-center justify-center text-[10px] font-semibold text-gray-500 dark:text-gray-500 select-none">
                                                Disponible
                                            </div>
                                        @endif

                                    </div>
                                @endforeach
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach

            {{-- Modal Guardar Grupo --}}
            <div id="groupModal"
                class="fixed inset-0 hidden items-center justify-center bg-black/60 backdrop-blur-xs z-50 p-4">
                <div style="background-color: var(--bg-card); border-color: var(--border-color); color: var(--text-main);"
                    class="rounded-2xl border shadow-2xl w-full max-w-md p-6 transition-all">

                    <div
                        class="flex justify-between items-center pb-4 mb-4 border-b border-gray-100 dark:border-gray-800">
                        <h3 class="text-lg font-extrabold tracking-tight" style="color: var(--text-main);">
                            Guardar Grupo de Horarios
                        </h3>
                        <button type="button" id="closeGroupModal"
                            class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-white text-lg rounded-xl transition-all">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <form method="POST"
                        action="{{ route('admin.psicologia.maestros.grupos_horarios.store_from_horarios') }}">
                        @csrf
                        <input type="hidden" name="action"
                            value="{{ isset($grupoActivo) ? 'update' : 'create' }}" />

                        <div class="mb-4">
                            @if (isset($grupoActivo))
                                <p class="text-xs text-gray-500 dark:text-gray-400 font-medium leading-relaxed">
                                    Existe un grupo activo llamado <strong
                                        class="text-gray-800 dark:text-gray-200 font-bold">"{{ $grupoActivo->nombre }}"</strong>.
                                    Los cambios se aplicarán directamente ahí.
                                </p>
                            @else
                                <p class="text-xs text-gray-500 dark:text-gray-400 font-medium leading-relaxed">
                                    No hay un grupo activo. Ingresa un nombre para crear uno nuevo.
                                </p>
                            @endif
                        </div>

                        <div class="mb-5" id="nombreField">
                            <label
                                class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1"
                                for="nombre_grupo">
                                Nombre del nuevo grupo
                            </label>
                            <input id="nombre_grupo" name="nombre" type="text"
                                placeholder="Ej. Semestre 2026-I"
                                style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                                class="w-full px-3.5 py-2.5 rounded-xl border text-sm font-medium focus:outline-none focus:ring-2 {{ $focusRingClass }} transition-all"
                                {{ !isset($grupoActivo) ? 'required' : 'disabled' }} />
                            <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
                        </div>

                        <div class="flex justify-end gap-2 pt-4 border-t border-gray-100 dark:border-gray-800">
                            <button type="button" id="closeGroupModalBtn"
                                class="px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 text-xs font-bold text-gray-600 dark:text-gray-300 transition-all">
                                Cancelar
                            </button>
                            <button type="submit"
                                class="px-5 py-2 rounded-xl {{ $btnClass }} text-white font-bold text-xs shadow-md active:scale-95 transition-all">
                                Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

    <script>
        let jornadaActual = 0;
        const totalJornadas = {{ $totalJornadas }};

        function cambiarJornada(direccion) {
            const bloqueActual = document.getElementById(`jornada-block-${jornadaActual}`);
            if (bloqueActual) bloqueActual.classList.add('hidden');

            jornadaActual = (jornadaActual + direccion + totalJornadas) % totalJornadas;

            const siguienteBloque = document.getElementById(`jornada-block-${jornadaActual}`);
            if (siguienteBloque) siguienteBloque.classList.remove('hidden');
        }

        var openGroupBtn = document.getElementById('openGroupModal');
        if (openGroupBtn) {
            openGroupBtn.addEventListener('click', function() {
                if (this.disabled) return;
                var groupModal = document.getElementById('groupModal');
                if (groupModal) {
                    groupModal.classList.remove('hidden');
                    groupModal.classList.add('flex');
                }
            });
        }

        var closeGroupBtn = document.getElementById('closeGroupModal');
        var closeGroupModalBtn = document.getElementById('closeGroupModalBtn');

        function hideGroupModal() {
            var groupModal = document.getElementById('groupModal');
            if (groupModal) {
                groupModal.classList.add('hidden');
                groupModal.classList.remove('flex');
            }
        }

        if (closeGroupBtn) closeGroupBtn.addEventListener('click', hideGroupModal);
        if (closeGroupModalBtn) closeGroupModalBtn.addEventListener('click', hideGroupModal);

        var groupModalEl = document.getElementById('groupModal');
        if (groupModalEl) {
            groupModalEl.addEventListener('click', function(event) {
                if (event.target.id === 'groupModal') hideGroupModal();
            });
        }

        function updateNombreField() {
            var nombreField = document.getElementById('nombreField');
            var nombreInput = document.getElementById('nombre_grupo');
            var actionInput = document.querySelector('input[name="action"]');
            if (!actionInput) return;

            if (actionInput.value === 'create') {
                if (nombreField) nombreField.style.display = 'block';
                if (nombreInput) {
                    nombreInput.required = true;
                    nombreInput.disabled = false;
                }
            } else {
                if (nombreField) nombreField.style.display = 'none';
                if (nombreInput) {
                    nombreInput.required = false;
                    nombreInput.disabled = true;
                }
            }
        }
        updateNombreField();
    </script>
</x-app-layout>