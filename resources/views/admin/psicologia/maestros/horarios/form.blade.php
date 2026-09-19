@props([
    'horario' => null,
    'dias' => [],
    'grupoRetorno' => null,
    'grupoSeleccionado' => null,
    'horariosSeleccionadosIniciales' => [],
    'tieneCitasPendientes' => false,
])

@php
    $moduloActivo = strtolower(session('modulo_activo', 'general'));
    $esPsicologia = in_array($moduloActivo, ['psicologia', 'psicología', 'mental']);

    $themeColor = $esPsicologia ? 'indigo' : 'red';

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

    $DIAS = !empty($dias) ? $dias : ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];
    $totalJornadas = count($BLOQUES);

    // Precarga: si viene de old() por error de validación, si no, los bloques existentes del grupo/contexto actual
    $horariosSeleccionados = old('horarios', $horariosSeleccionadosIniciales ?? []);
@endphp

<form action="{{ route('admin.psicologia.maestros.horarios.store') }}" method="POST"
    class="rd-prevent-double-submit space-y-6">
    @csrf

    @if (isset($grupoRetorno) && $grupoRetorno)
        <input type="hidden" name="grupo_id" value="{{ $grupoRetorno }}">
    @endif

    @if (!empty($tieneCitasPendientes))
        <div
            class="p-4 text-xs sm:text-sm text-amber-800 dark:text-amber-400 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 flex items-center gap-3 shadow-xs">
            <i class="fas fa-lock text-amber-600 dark:text-amber-400 text-base shrink-0"></i>
            <span><strong>Acción Restringida:</strong> No puedes modificar bloques de horario mientras tengas citas pendientes o confirmadas.</span>
        </div>
    @endif

    {{-- Card de Info del Profesional y Contador --}}
    <div style="background-color: var(--bg-card); border-color: var(--border-color);"
        class="p-4 sm:p-5 rounded-2xl border shadow-sm flex flex-col md:flex-row md:items-end justify-between gap-4">

        <div class="w-full md:max-w-md">
            <label class="block text-[11px] font-black uppercase tracking-wider text-gray-400 mb-1.5">
                Profesional Asignado
            </label>
            <div class="flex items-center rounded-xl border overflow-hidden shadow-sm"
                style="border-color: var(--border-color);">
                <span
                    class="flex items-center justify-center px-3.5 py-2.5 bg-gray-50 dark:bg-black/20 text-{{ $themeColor }}-600 dark:text-{{ $themeColor }}-400 border-r"
                    style="border-color: var(--border-color);">
                    <i class="fas fa-user-md text-sm"></i>
                </span>
                <div class="w-full px-3 py-2 text-sm font-bold"
                    style="background-color: rgba(0,0,0,0.02); color: var(--text-main);">
                    {{ auth()->user()->nombre_completo ?? (auth()->user()->name ?? 'Usuario') }}
                </div>
            </div>
            @isset($grupoSeleccionado)
                <p class="mt-2 text-[11px] font-semibold text-gray-500 dark:text-gray-400 flex items-center gap-1.5">
                    <i class="fas fa-layer-group text-{{ $themeColor }}-500 text-[10px]"></i>
                    Grupo: <span
                        class="font-bold text-{{ $themeColor }}-600 dark:text-{{ $themeColor }}-400">{{ $grupoSeleccionado->nombre }}</span>
                </p>
            @endisset
        </div>

        <div
            class="flex items-center justify-between md:justify-end gap-3 w-full md:w-auto border-t md:border-t-0 pt-3 md:pt-0 border-gray-100 dark:border-gray-800">
            <div
                class="px-3.5 py-2 rounded-xl bg-{{ $themeColor }}-50 dark:bg-{{ $themeColor }}-950/40 border border-{{ $themeColor }}-100 dark:border-{{ $themeColor }}-900/50 flex items-center gap-2">
                <i class="fas fa-check-circle text-{{ $themeColor }}-600 dark:text-{{ $themeColor }}-400 text-xs"></i>
                <span class="text-xs font-extrabold text-{{ $themeColor }}-700 dark:text-{{ $themeColor }}-300">
                    <span id="contadorSeleccion">0</span> bloque(s) seleccionado(s)
                </span>
            </div>

            <button type="button" id="btnLimpiarSeleccion"
                class="px-3 py-2 rounded-xl text-xs font-bold text-gray-500 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 border border-transparent hover:border-rose-200 dark:hover:border-rose-900 transition-all flex items-center gap-1.5">
                <i class="fas fa-eraser text-[11px]"></i>
                <span>Limpiar Todo</span>
            </button>
        </div>
    </div>

    @error('horarios')
        <div
            class="px-4 py-3 rounded-xl border border-rose-200 dark:border-rose-900/60 bg-rose-50 dark:bg-rose-950/30 text-xs font-bold text-rose-600 dark:text-rose-400 flex items-center gap-2">
            <i class="fas fa-exclamation-circle text-sm"></i>
            <span>{{ $message }}</span>
        </div>
    @enderror

    {{-- Grid Principal --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        {{-- Sidebar Resumen --}}
        <div class="lg:col-span-3 lg:sticky lg:top-6">
            <div style="background-color: var(--bg-card); border-color: var(--border-color);"
                class="p-4 rounded-2xl border shadow-sm flex flex-col max-h-[calc(100vh-8rem)]">

                <div class="flex items-center justify-between pb-3 mb-3 border-b border-gray-100 dark:border-gray-800">
                    <h3
                        class="text-xs font-black uppercase tracking-wider text-gray-700 dark:text-gray-200 flex items-center gap-2">
                        <i class="fas fa-list-check text-{{ $themeColor }}-500"></i>
                        <span>Resumen</span>
                    </h3>
                </div>

                <p class="text-[11px] text-gray-500 dark:text-gray-400 mb-3 leading-tight">
                    Haz clic sobre un bloque para asignarlo o quitarlo.
                </p>

                <div id="resumenSeleccion" class="space-y-1.5 overflow-y-auto pr-1 flex-1 min-h-0">
                    <div class="text-center py-6 text-xs text-gray-400" id="resumenVacio">
                        <i class="fas fa-inbox text-2xl opacity-30 block mb-2"></i>
                        Aún no has seleccionado bloques.
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabla de Jornadas --}}
        <div class="lg:col-span-9 space-y-4">
            @foreach ($BLOQUES as $jornada => $bloques)
                @php $jornadaIndex = $loop->index; @endphp

                <div id="jornada-block-{{ $jornadaIndex }}"
                    class="jornada-block {{ $jornadaIndex !== 0 ? 'hidden' : '' }}">

                    <div style="background-color: var(--bg-card); border-color: var(--border-color);"
                        class="p-3 rounded-2xl border shadow-sm mb-4 flex items-center justify-between gap-3">

                        <div class="flex items-center gap-3">
                            <div class="w-2.5 h-7 rounded-full bg-{{ $themeColor }}-500"></div>
                            <h3
                                class="text-xs sm:text-sm font-black uppercase tracking-wider text-gray-800 dark:text-gray-200">
                                Jornada {{ $jornada }}
                            </h3>
                        </div>

                        <div
                            class="flex items-center gap-1.5 bg-gray-50 dark:bg-black/20 p-1 rounded-xl border border-gray-200/60 dark:border-gray-700/60">
                            <button type="button" onclick="cambiarJornada(-1)" title="Jornada Anterior"
                                class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-white dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-all shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>

                            <span
                                class="px-3 text-[11px] font-black text-gray-700 dark:text-gray-200 uppercase tracking-wider">
                                {{ $loop->iteration }} / {{ $totalJornadas }}
                            </span>

                            <button type="button" onclick="cambiarJornada(1)" title="Siguiente Jornada"
                                class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-white dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-all shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div style="border-color: var(--border-color); background-color: var(--bg-card);"
                        class="rounded-2xl border shadow-sm overflow-x-auto">
                        <div class="grid min-w-[720px]"
                            style="grid-template-columns: 110px repeat(5, minmax(0, 1fr));">

                            <div class="p-3 border-b border-r bg-gray-50/80 dark:bg-black/30 flex items-center justify-center font-extrabold text-[11px] text-gray-400 uppercase tracking-wider"
                                style="border-color: var(--border-color);">
                                <i class="far fa-clock mr-1.5"></i> Bloque
                            </div>

                            @foreach ($DIAS as $diaLabel)
                                <div class="p-3 text-center text-[11px] font-black uppercase tracking-wider text-gray-700 dark:text-gray-200 border-b bg-gray-50/80 dark:bg-black/30 {{ !$loop->last ? 'border-r' : '' }}"
                                    style="border-color: var(--border-color);">
                                    {{ $diaLabel }}
                                </div>
                            @endforeach

                            @foreach ($bloques as $bloque)
                                <div class="p-3 flex items-center justify-center text-center text-[11px] font-extrabold text-gray-700 dark:text-gray-200 whitespace-nowrap border-r bg-gray-50/30 dark:bg-black/10 {{ !$loop->last ? 'border-b' : '' }}"
                                    style="border-color: var(--border-color);">
                                    {{ \Carbon\Carbon::parse($bloque['inicio'])->format('g:i') }} -
                                    {{ \Carbon\Carbon::parse($bloque['fin'])->format('g:i') }}
                                </div>

                                @foreach ($DIAS as $diaLabel)
                                    @php
                                        $cellKey = "{$diaLabel}|{$bloque['inicio']}|{$bloque['fin']}";
                                    @endphp
                                    <div class="p-1.5 flex items-center justify-center {{ !$loop->last ? 'border-r' : '' }} {{ !$loop->parent->last ? 'border-b' : '' }}"
                                        style="border-color: var(--border-color);">

                                        <button type="button"
                                            class="drop-zone w-full h-full min-h-[72px] rounded-xl border border-dashed border-gray-300 dark:border-gray-700/80 bg-gray-50/40 dark:bg-black/10 p-1.5 flex flex-col items-center justify-center gap-1 transition-all duration-200 relative group hover:border-{{ $themeColor }}-400 hover:bg-{{ $themeColor }}-50/50 dark:hover:bg-{{ $themeColor }}-950/20"
                                            data-key="{{ $cellKey }}"
                                            data-dia="{{ $diaLabel }}"
                                            data-inicio="{{ $bloque['inicio'] }}"
                                            data-fin="{{ $bloque['fin'] }}"
                                            onclick="toggleBloque(this)">

                                            <div
                                                class="empty-placeholder flex flex-col items-center justify-center text-[10px] font-semibold text-gray-400 dark:text-gray-500 gap-1 pointer-events-none">
                                                <i
                                                    class="fas fa-plus-circle text-xs opacity-40 group-hover:opacity-90 group-hover:text-{{ $themeColor }}-500 transition-opacity"></i>
                                                <span>Click para asignar</span>
                                            </div>

                                            <div
                                                class="assigned-content hidden flex-col items-center justify-center text-[10px] font-bold gap-1 pointer-events-none w-full">
                                                <i
                                                    class="fas fa-check-circle text-{{ $themeColor }}-600 dark:text-{{ $themeColor }}-400 text-sm"></i>
                                                <span
                                                    class="text-{{ $themeColor }}-700 dark:text-{{ $themeColor }}-300">Asignado</span>
                                            </div>
                                        </button>
                                    </div>
                                @endforeach
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

    </div>

    {{-- Botón Flotante --}}
    <div class="fixed bottom-6 right-6 sm:bottom-6 sm:right-8 z-50">
        <div
            class="flex items-center gap-1.5 p-2 px-4 rounded-full bg-white/90 dark:bg-[#111322]/95 border border-gray-200 dark:border-[#222744] shadow-2xl backdrop-blur-md transition-all">

            <a href="{{ route('admin.psicologia.maestros.horarios.index', isset($grupoRetorno) && $grupoRetorno ? ['grupo' => $grupoRetorno] : []) }}"
                title="Cancelar y Volver"
                class="rd-submit-btn flex items-center gap-2 px-6 py-3 rounded-full bg-gray-100 hover:bg-gray-200/80 text-gray-700 dark:bg-[#1b1e3d] dark:hover:bg-[#252a54] dark:text-[#a5b4fc] font-bold text-xs transition-all active:scale-95 border border-gray-300/70 dark:border-[#2d335c]">
                <i class="fas fa-arrow-left text-[12px]"></i>
                <span>Cancelar</span>
            </a>

            <div class="h-5 w-[1px] bg-gray-200 dark:bg-[#222744] mx-1"></div>

            <button type="submit" title="Confirmar y Guardar"
                @disabled(!empty($tieneCitasPendientes))
                class="w-12 h-12 rounded-full bg-emerald-500 hover:bg-emerald-600 text-white dark:bg-[#0a2720] dark:hover:bg-[#0f3d32] border border-emerald-600 dark:border-[#135343] dark:text-[#34d399] flex items-center justify-center transition-all active:scale-90 shadow-md disabled:opacity-40 disabled:cursor-not-allowed">
                <i class="fas fa-check text-[16px]"></i>
            </button>
        </div>
    </div>
</form>

<script>
    let jornadaActual = 0;
    const totalJornadas = {{ $totalJornadas }};
    const themeColor = '{{ $themeColor }}';

    function cambiarJornada(direccion) {
        const bloqueActual = document.getElementById(`jornada-block-${jornadaActual}`);
        if (bloqueActual) bloqueActual.classList.add('hidden');

        jornadaActual = (jornadaActual + direccion + totalJornadas) % totalJornadas;

        const siguienteBloque = document.getElementById(`jornada-block-${jornadaActual}`);
        if (siguienteBloque) siguienteBloque.classList.remove('hidden');
    }

    function toggleBloque(btn) {
        const key = btn.getAttribute('data-key');
        const emptyPlaceholder = btn.querySelector('.empty-placeholder');
        const assignedContent = btn.querySelector('.assigned-content');
        const isSelected = btn.classList.contains('selected');

        if (isSelected) {
            btn.classList.remove(
                'selected',
                `border-${themeColor}-500`,
                `bg-${themeColor}-100`,
                `dark:bg-${themeColor}-900/40`,
                'border-solid'
            );
            btn.classList.add('border-dashed');

            emptyPlaceholder.classList.remove('hidden');
            assignedContent.classList.add('hidden');
            assignedContent.classList.remove('flex');

            const existing = btn.querySelector('input.input-horario');
            if (existing) existing.remove();
        } else {
            btn.classList.add(
                'selected',
                `border-${themeColor}-500`,
                `bg-${themeColor}-100`,
                `dark:bg-${themeColor}-900/40`,
                'border-solid'
            );
            btn.classList.remove('border-dashed');

            emptyPlaceholder.classList.add('hidden');
            assignedContent.classList.remove('hidden');
            assignedContent.classList.add('flex');

            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'horarios[]';
            input.className = 'input-horario';
            input.value = key;
            btn.appendChild(input);
        }

        actualizarContador();
        actualizarResumen();
    }

    function quitarDesdeResumen(key) {
        const btn = document.querySelector(`.drop-zone[data-key="${key}"]`);
        if (btn && btn.classList.contains('selected')) {
            toggleBloque(btn);
        }
    }

    function actualizarContador() {
        const asignados = document.querySelectorAll('.input-horario').length;
        const contador = document.getElementById('contadorSeleccion');
        if (contador) contador.textContent = asignados;
    }

    function actualizarResumen() {
        const resumen = document.getElementById('resumenSeleccion');
        const vacio = document.getElementById('resumenVacio');
        const inputs = document.querySelectorAll('.input-horario');

        resumen.querySelectorAll('.resumen-item').forEach(el => el.remove());

        if (inputs.length === 0) {
            if (vacio) vacio.classList.remove('hidden');
            return;
        }

        if (vacio) vacio.classList.add('hidden');

        const grouped = {};
        const orden = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];

        inputs.forEach(input => {
            const [dia, inicio, fin] = input.value.split('|');
            if (!grouped[dia]) grouped[dia] = [];
            grouped[dia].push({ inicio, fin, key: input.value });
        });

        orden.forEach(dia => {
            if (!grouped[dia]) return;

            const header = document.createElement('div');
            header.className =
                'resumen-item text-[10px] font-black uppercase tracking-wider text-gray-500 mt-2 mb-1 px-1';
            header.textContent = dia;
            resumen.appendChild(header);

            grouped[dia]
                .sort((a, b) => a.inicio.localeCompare(b.inicio))
                .forEach(item => {
                    const div = document.createElement('div');
                    div.className =
                        `resumen-item flex items-center justify-between gap-2 px-2.5 py-1.5 rounded-lg bg-${themeColor}-50 dark:bg-${themeColor}-950/40 border border-${themeColor}-100 dark:border-${themeColor}-900/50`;
                    div.innerHTML = `
                        <span class="text-[10px] font-bold text-${themeColor}-700 dark:text-${themeColor}-300">
                            ${item.inicio} - ${item.fin}
                        </span>
                        <button type="button"
                            class="text-rose-500 hover:text-rose-700 dark:hover:text-rose-400 hover:bg-rose-100/40 dark:hover:bg-rose-900/40 rounded p-0.5 transition-colors"
                            onclick="quitarDesdeResumen('${item.key}')">
                            <i class="fas fa-times text-[10px]"></i>
                        </button>
                    `;
                    resumen.appendChild(div);
                });
        });
    }

    document.getElementById('btnLimpiarSeleccion').addEventListener('click', function() {
        document.querySelectorAll('.drop-zone.selected').forEach(btn => {
            toggleBloque(btn);
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        const previos = @json($horariosSeleccionados ?? []);

        if (Array.isArray(previos) && previos.length > 0) {
            previos.forEach(key => {
                const btn = document.querySelector(`.drop-zone[data-key="${key}"]`);
                if (btn && !btn.classList.contains('selected')) {
                    toggleBloque(btn);
                }
            });
        } else {
            actualizarContador();
            actualizarResumen();
        }
    });
</script>