<x-app-layout>
    <div class="py-2">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">

            <div class="p-6 sm:p-8 text-gray-900 dark:text-gray-100">
                <div>

                    <div id="activeAppointmentsView">
                        <div class="mb-2 flex justify-between items-center gap-4 pb-6">
                            <div class="flex-shrink-0">
                                <h3
                                    class="text-3xl font-black text-gray-900 dark:text-white tracking-tight whitespace-nowrap">
                                    {{ auth()->user()->tieneRol('psicologo') ? 'Gestión Global de Citas' : 'Mis Citas' }}
                                </h3>
                                <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">Recorrido
                                    clínico completo</p>
                            </div>

                            @if (!auth()->user()->tieneRol('psicologo'))
                                <div class="flex items-center gap-2 sm:gap-3 shrink-0 ml-auto">
                                    <button onclick="toggleHistoryView(true)"
                                        class="w-10 h-10 flex items-center justify-center rounded-2xl bg-slate-50 dark:bg-gray-700 text-slate-400 dark:text-gray-400 hover:text-blue-700 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-gray-600 transition-all"
                                        title="Ver historial de sesiones">
                                        <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </button>
                                    <a href="{{ route('admin.psicologia.maestros.citas.create') }}"
                                        class="inline-flex shrink-0 whitespace-nowrap items-center px-4 py-2 sm:px-5 sm:py-2.5 bg-blue-800 hover:bg-blue-700 text-white text-sm font-bold rounded-2xl transition-all shadow-md shadow-blue-100 dark:shadow-blue-900/30">+
                                        Solicitar cita</a>
                                </div>
                            @endif
                        </div>



                        @php
                            $citasActivas = $citas->filter(fn($c) => in_array($c->estado, ['pendiente', 'confirmada']));
                        @endphp

                        <div id="emptyCitasState"
                            class="text-center py-20 bg-slate-50 dark:bg-gray-700/30 rounded-3xl border border-dashed border-slate-200 dark:border-gray-700 {{ $citasActivas->isEmpty() ? '' : 'hidden' }}">
                            <div
                                class="bg-slate-100 dark:bg-gray-700 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-slate-400 dark:text-gray-500" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                    </path>
                                </svg>
                            </div>
                            <p class="text-lg font-bold text-slate-900 dark:text-white">No tienes citas en Gestión.</p>
                            <p class="text-sm text-slate-500 dark:text-gray-400 mt-2">
                                {{ auth()->user()->tieneRol('psicologo') ? 'Aún no se han generado solicitudes de citas en el sistema.' : 'Solicita una nueva cita o consulta tu historial.' }}
                            </p>
                        </div>

                        <div id="citasCardsContainer" class="{{ $citasActivas->isEmpty() ? 'hidden' : '' }}">

                            <div
                                class="rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden">
                                <div class="overflow-x-auto">
                                    <table style="background-color: var(--bg-card); border-color: var(--border-color);"
                                        class="w-full text-left border-collapse">
                                        <thead>
                                            <tr
                                                class="bg-gray-50/50 dark:bg-black/20 border-b border-gray-100 dark:border-gray-800 text-[11px] font-black uppercase tracking-wider text-gray-400">
                                                <th class="px-6 py-4">Fecha / Hora</th>
                                                @if (auth()->user()->tieneRol('psicologo'))
                                                    <th class="px-6 py-4">Paciente</th>
                                                @endif
                                                <th class="px-6 py-4">Psicólogo</th>
                                                <th class="px-6 py-4">Motivo</th>
                                                <th class="px-6 py-4 text-center">Estado</th>
                                                <th class="px-6 py-4 text-right">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody
                                            class="divide-y divide-gray-100 dark:divide-gray-800/60 text-xs font-medium">
                                            @foreach ($citasActivas as $cita)
                                                <tr id="cita-card-{{ $cita->id }}"
                                                    class="hover:bg-gray-50/60 dark:hover:bg-white/[0.02] transition-colors"
                                                    data-ajax-remove-card="true">

                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        @php
                                                            $bloqueConfirmado = $cita->bloque_propuesto;
                                                            if ($bloqueConfirmado) {
                                                                preg_match(
                                                                    '/^([^\s]+)\s+(\d{1,2}:\d{2})\s*[-–—]\s*(\d{1,2}:\d{2})$/',
                                                                    $bloqueConfirmado,
                                                                    $matches,
                                                                );
                                                                if (count($matches) === 4) {
                                                                    $bloqueConfirmado =
                                                                        \Carbon\Carbon::createFromFormat(
                                                                            'H:i',
                                                                            $matches[2],
                                                                        )->format('g:i A') .
                                                                        ' - ' .
                                                                        \Carbon\Carbon::createFromFormat(
                                                                            'H:i',
                                                                            $matches[3],
                                                                        )->format('g:i A');
                                                                } else {
                                                                    $dias = [
                                                                        'Lunes',
                                                                        'Martes',
                                                                        'Miércoles',
                                                                        'Miercoles',
                                                                        'Jueves',
                                                                        'Viernes',
                                                                        'Sábado',
                                                                        'Sabado',
                                                                        'Domingo',
                                                                    ];
                                                                    $bloqueConfirmado = trim(
                                                                        str_ireplace($dias, '', $bloqueConfirmado),
                                                                    );
                                                                }
                                                            }
                                                        @endphp
                                                        <div class="flex flex-col gap-0.5">
                                                            <span
                                                                class="font-black text-sm text-slate-800 dark:text-white flex items-center gap-1.5">
                                                                <svg class="w-4 h-4 text-blue-600 dark:text-blue-400 shrink-0"
                                                                    fill="none" stroke="currentColor"
                                                                    viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2"
                                                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                                                    </path>
                                                                </svg>
                                                                @if ($cita->estado === 'confirmada' && $cita->fecha)
                                                                    {{ ucfirst(\Carbon\Carbon::parse($cita->fecha)->locale('es')->translatedFormat('l, d M Y')) }}
                                                                @else
                                                                    En Espera
                                                                @endif
                                                            </span>
                                                            @if ($cita->estado === 'confirmada' && $bloqueConfirmado)
                                                                <span
                                                                    class="text-[11px] font-bold text-blue-600 dark:text-blue-400 flex items-center gap-1">
                                                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none"
                                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round"
                                                                            stroke-linejoin="round" stroke-width="2"
                                                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z">
                                                                        </path>
                                                                    </svg>
                                                                    {{ $bloqueConfirmado }}
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </td>

                                                    @if (auth()->user()->tieneRol('psicologo'))
                                                        <td class="px-6 py-4 whitespace-nowrap">
                                                            <span
                                                                class="font-bold text-slate-700 dark:text-gray-300">{{ optional($cita->paciente->persona)->nombre_persona ?: $cita->paciente->persona ?? 'N/A' }}</span>
                                                        </td>
                                                    @endif

                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <div class="flex items-center gap-2">
                                                            <span
                                                                class="w-6 h-6 rounded-full bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-400 flex items-center justify-center text-[10px] font-black shrink-0">{{ substr(optional($cita->psicologo->persona)->nombre_persona ?: 'N', 0, 1) }}</span>
                                                            <span class="font-bold text-slate-700 dark:text-gray-300">
                                                                {{ optional($cita->psicologo->persona)->nombre_persona ?: 'No asignado' }}
                                                                {{ optional($cita->psicologo->persona)->apellido_persona ?: '' }}
                                                            </span>
                                                        </div>
                                                    </td>

                                                    <td class="px-6 py-4 max-w-[200px]">
                                                        <p class="text-slate-600 dark:text-gray-300 italic truncate">
                                                            "{{ $cita->motivo ?: 'Sin motivo' }}"</p>
                                                    </td>

                                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                                        <span
                                                            class="inline-flex items-center px-3 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-widest border
                                                            {{ $cita->estado === 'confirmada' ? 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800' : 'bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 border-amber-200 dark:border-amber-800' }}">
                                                            {{ ucfirst($cita->estado ?: 'pendiente') }}
                                                        </span>
                                                    </td>

                                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                                        <div class="flex items-center justify-end gap-2">
                                                            @php
                                                                $puedeCancelar = true;
                                                                if (
                                                                    auth()
                                                                        ->user()
                                                                        ->tieneRol(['paciente']) &&
                                                                    $cita->estado === 'confirmada' &&
                                                                    $cita->fecha &&
                                                                    $cita->hora
                                                                ) {
                                                                    $fechaSolo = substr($cita->fecha, 0, 10);
                                                                    $fechaHoraCita = \Carbon\Carbon::parse(
                                                                        $fechaSolo . ' ' . $cita->hora,
                                                                    );
                                                                    if ($fechaHoraCita->isPast()) {
                                                                        $puedeCancelar = false;
                                                                    }
                                                                }
                                                            @endphp
                                                            @if ($puedeCancelar)
                                                                <button type="button"
                                                                    onclick="openPatientCancelModal('{{ route('admin.psicologia.maestros.citas.cancel', $cita->id) }}', '{{ $cita->id }}')"
                                                                    class="w-9 h-9 rounded-xl border border-gray-200 dark:border-gray-700/60 dark:hover:border-red-700/60 text-gray-400 hover:text-rose-600 hover:bg-rose-600/10 hover:border-rose-300 inline-flex items-center justify-center transition-all active:scale-95"
                                                                    title="Cancelar cita">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            @else
                                                                <span
                                                                    class="text-[10px] text-slate-400 bg-slate-100 dark:bg-gray-700 px-2 py-1 rounded-lg font-bold uppercase tracking-wide whitespace-nowrap">Cita
                                                                    pasada</span>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>

                                                @if (auth()->user()->tieneRol(['paciente']) &&
                                                        isset($cita->propuesta_estado) &&
                                                        $cita->propuesta_estado === 'pendiente')
                                                    <tr
                                                        class="bg-sky-50/60 dark:bg-sky-950/20 border-b border-sky-100 dark:border-sky-900/40">
                                                        <td colspan="{{ auth()->user()->tieneRol('psicologo') ? 6 : 5 }}"
                                                            class="px-6 py-4">
                                                            <div class="flex items-start gap-3">
                                                                <div
                                                                    class="p-2 bg-blue-700 text-white rounded-xl shadow-md shrink-0 mt-0.5">
                                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                                        class="h-4 w-4" fill="none"
                                                                        viewBox="0 0 24 24" stroke="currentColor">
                                                                        <path stroke-linecap="round"
                                                                            stroke-linejoin="round" stroke-width="2.5"
                                                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                                    </svg>
                                                                </div>
                                                                <div class="flex-1 min-w-0">
                                                                    <h4
                                                                        class="text-sm font-black text-slate-800 dark:text-white uppercase tracking-tight mb-0.5">
                                                                        Propuesta de cambio de horario</h4>
                                                                    <p
                                                                        class="text-xs text-slate-600 dark:text-gray-400 font-medium italic mb-3">
                                                                        "No puedo atenderte en los horarios que
                                                                        sugeriste, sin embargo, quiero atenderte lo más
                                                                        pronto posible, esta es mi propuesta para
                                                                        atenderte"</p>

                                                                    <form id="form-propuesta-{{ $cita->id }}"
                                                                        onsubmit="responderPropuestaForm(event, {{ $cita->id }})"
                                                                        class="w-full">
                                                                        <p
                                                                            class="text-[10px] font-black uppercase tracking-wider text-slate-500 dark:text-gray-400 mb-2">
                                                                            Opciones de horario propuestas por el
                                                                            psicólogo:
                                                                        </p>
                                                                        <div
                                                                            class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2 mb-3">
                                                                            @php
                                                                                $bloquesArray = array_filter(
                                                                                    array_map(
                                                                                        'trim',
                                                                                        explode(
                                                                                            ';',
                                                                                            $cita->bloques_propuestos_raw ??
                                                                                                '',
                                                                                        ),
                                                                                    ),
                                                                                );
                                                                            @endphp
                                                                            @foreach ($bloquesArray as $index => $bloque)
                                                                                <label
                                                                                    class="cursor-pointer block w-full h-full">
                                                                                    <input type="radio"
                                                                                        name="bloque_seleccionado"
                                                                                        value="{{ $bloque }}"
                                                                                        class="peer sr-only" required
                                                                                        onchange="document.getElementById('rechazo-area-{{ $cita->id }}').style.display='none'; if(typeof updateSubmitBtnState === 'function') updateSubmitBtnState({{ $cita->id }});">
                                                                                    <div
                                                                                        class="p-2.5 bg-white dark:bg-gray-800/80 border-2 border-slate-200 dark:border-gray-700/80 rounded-xl peer-checked:border-sky-600 dark:peer-checked:border-sky-500 peer-checked:bg-sky-50 dark:peer-checked:bg-sky-950/40 transition-all h-full w-full flex items-center justify-center text-center shadow-sm">
                                                                                        <p
                                                                                            class="text-xs font-bold text-slate-700 dark:text-gray-200 peer-checked:text-sky-700 dark:peer-checked:text-sky-400">
                                                                                            @php
                                                                                                $parts = explode(
                                                                                                    '|',
                                                                                                    $bloque,
                                                                                                );
                                                                                                if (
                                                                                                    count($parts) == 2
                                                                                                ) {
                                                                                                    $fechaFormateada = \Carbon\Carbon::parse(
                                                                                                        $parts[0],
                                                                                                    )
                                                                                                        ->locale('es')
                                                                                                        ->translatedFormat(
                                                                                                            'l d M, Y',
                                                                                                        );
                                                                                                    $dias = [
                                                                                                        'Lunes',
                                                                                                        'Martes',
                                                                                                        'Miércoles',
                                                                                                        'Miercoles',
                                                                                                        'Jueves',
                                                                                                        'Viernes',
                                                                                                        'Sábado',
                                                                                                        'Sabado',
                                                                                                        'Domingo',
                                                                                                    ];
                                                                                                    $horarioLimpio = trim(
                                                                                                        str_ireplace(
                                                                                                            $dias,
                                                                                                            '',
                                                                                                            $parts[1],
                                                                                                        ),
                                                                                                    );
                                                                                                    echo ucfirst(
                                                                                                        $fechaFormateada,
                                                                                                    ) .
                                                                                                        '<br><span class="text-xs font-normal opacity-75 dark:opacity-80">' .
                                                                                                        $horarioLimpio .
                                                                                                        '</span>';
                                                                                                } else {
                                                                                                    echo $bloque;
                                                                                                }
                                                                                            @endphp
                                                                                        </p>
                                                                                    </div>
                                                                                </label>
                                                                            @endforeach
                                                                            <label
                                                                                class="cursor-pointer block w-full h-full">
                                                                                <input type="radio"
                                                                                    id="radio-ninguno-{{ $cita->id }}"
                                                                                    name="bloque_seleccionado"
                                                                                    value="ninguno"
                                                                                    class="peer sr-only" required
                                                                                    onchange="document.getElementById('rechazo-area-{{ $cita->id }}').style.display='block'; if(typeof initCalendarForCita === 'function') initCalendarForCita({{ $cita->id }}, {{ $cita->psicologo_id }}); if(typeof updateSubmitBtnState === 'function') updateSubmitBtnState({{ $cita->id }});">
                                                                                <div
                                                                                    class="p-2.5 bg-white dark:bg-gray-800/80 border-2 border-slate-200 dark:border-gray-700/80 rounded-xl peer-checked:border-rose-500 dark:peer-checked:border-rose-500 peer-checked:bg-rose-50 dark:peer-checked:bg-rose-950/40 transition-all w-full flex items-center justify-center h-full text-center shadow-sm">
                                                                                    <p
                                                                                        class="text-xs font-bold text-slate-700 dark:text-gray-200 peer-checked:text-rose-700 dark:peer-checked:text-rose-400">
                                                                                        Ninguno</p>
                                                                                </div>
                                                                            </label>
                                                                        </div>

                                                                        <div id="rechazo-area-{{ $cita->id }}"
                                                                            style="display: none;" class="mb-3">
                                                                            <div
                                                                                class="mt-3 border-t border-slate-200 dark:border-gray-700/80 pt-3">
                                                                                <h5
                                                                                    class="text-sm font-bold text-slate-700 dark:text-gray-200 mb-1">
                                                                                    Proponer nuevos horarios</h5>
                                                                                <p
                                                                                    class="text-xs text-slate-500 dark:text-gray-400 mb-3">
                                                                                    Selecciona los días y horarios en
                                                                                    los que podrías asistir.</p>
                                                                                <div
                                                                                    class="bg-slate-50/50 dark:bg-gray-800/40 p-3 rounded-xl border border-slate-200 dark:border-gray-700/80 mb-3">
                                                                                    <div
                                                                                        class="grid grid-cols-7 gap-1 text-center text-[10px] font-black uppercase text-slate-400 dark:text-gray-400 mb-2">
                                                                                        <div>Dom</div>
                                                                                        <div>Lun</div>
                                                                                        <div>Mar</div>
                                                                                        <div>Mié</div>
                                                                                        <div>Jue</div>
                                                                                        <div>Vie</div>
                                                                                        <div>Sáb</div>
                                                                                    </div>
                                                                                    <div id="calendarGrid-{{ $cita->id }}"
                                                                                        class="grid grid-cols-7 gap-1">
                                                                                    </div>
                                                                                </div>
                                                                                <div id="slotsContainer-{{ $cita->id }}"
                                                                                    class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                                                                </div>
                                                                                <p id="minBlocksHelpText-{{ $cita->id }}"
                                                                                    class="text-xs text-rose-600 dark:text-rose-400 font-bold mt-2 hidden">
                                                                                    Debes seleccionar al menos 2 días, y
                                                                                    elegir mínimo un bloque de horario
                                                                                    por cada día.
                                                                                </p>
                                                                                <input type="hidden"
                                                                                    name="nuevos_bloques"
                                                                                    id="nuevos_bloques_{{ $cita->id }}">
                                                                            </div>
                                                                            <label
                                                                                class="block text-xs font-black text-slate-500 dark:text-gray-400 uppercase tracking-widest mt-3 mb-1">Motivo
                                                                                del rechazo (Breve)</label>
                                                                            <textarea name="motivo_rechazo" maxlength="50"
                                                                                class="w-full bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl p-3 text-sm text-slate-800 dark:text-gray-100 placeholder-slate-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-sky-600 dark:focus:ring-sky-500 outline-none resize-none shadow-sm transition-all"
                                                                                rows="2" placeholder="Explique por qué no puede asistir en estos horarios..."></textarea>
                                                                        </div>

                                                                        <div
                                                                            class="flex justify-end gap-2 pt-2 border-t border-slate-100 dark:border-gray-700/50">
                                                                            <button type="submit"
                                                                                id="btn-submit-{{ $cita->id }}"
                                                                                class="px-5 py-2 text-xs font-black bg-slate-200 dark:bg-gray-700 text-slate-400 dark:text-gray-400 rounded-xl transition-all cursor-not-allowed">
                                                                                Selecciona una opción
                                                                            </button>
                                                                        </div>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @elseif(auth()->user()->tieneRol(['paciente']) &&
                                                        isset($cita->propuesta_estado) &&
                                                        $cita->propuesta_estado !== 'pendiente')
                                                    <tr
                                                        class="bg-slate-50/50 dark:bg-gray-700/20 border-b border-slate-100 dark:border-gray-800">
                                                        <td colspan="{{ auth()->user()->tieneRol('psicologo') ? 6 : 5 }}"
                                                            class="px-6 py-3">
                                                            <p
                                                                class="text-xs font-bold text-slate-500 dark:text-gray-400 flex items-center gap-2">
                                                                <svg class="w-3.5 h-3.5 shrink-0" fill="none"
                                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round"
                                                                        stroke-linejoin="round" stroke-width="2"
                                                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                                                    </path>
                                                                </svg>
                                                                Has respondido a la propuesta:
                                                                <strong
                                                                    class="text-slate-800 dark:text-white uppercase tracking-wider">
                                                                    @if ($cita->propuesta_estado === 'aceptada')
                                                                        Contrapropuesta aceptada
                                                                    @elseif($cita->propuesta_estado === 'cualquier_dia')
                                                                        Cualquier día está bien
                                                                    @elseif($cita->propuesta_estado === 'sugerencia_aceptada')
                                                                        Sugerencia aceptada
                                                                        ({{ $cita->propuesta_bloque_seleccionado }})
                                                                    @elseif($cita->propuesta_estado === 'rechazada')
                                                                        Rechazado, vas a esperar nueva cita
                                                                    @endif
                                                                </strong>
                                                            </p>
                                                        </td>
                                                    </tr>
                                                @endif

                                                @if ($cita->estado !== 'confirmada')
                                                    <tr
                                                        class="bg-slate-50/30 dark:bg-gray-800/20 border-b border-slate-100 dark:border-gray-800">
                                                        <td colspan="{{ auth()->user()->tieneRol('psicologo') ? 6 : 5 }}"
                                                            class="px-6 py-3">
                                                            @php
                                                                $rawBloques = $cita->bloques_sugeridos ?: '';
                                                                $horariosDisplay = trim(
                                                                    preg_replace(
                                                                        '/^\s*Horarios\s*(propuestos)?\s*:\s*/i',
                                                                        '',
                                                                        $rawBloques,
                                                                    ),
                                                                );
                                                                $excepcionesDisplay = '';
                                                                if (str_contains($rawBloques, '|')) {
                                                                    $partes = explode('|', $rawBloques);
                                                                    $excepcionesDisplay = trim(
                                                                        preg_replace(
                                                                            '/^\s*D[íi]as exceptuados:\s*/i',
                                                                            '',
                                                                            $partes[0],
                                                                        ),
                                                                    );
                                                                    $horariosDisplay = trim(
                                                                        preg_replace(
                                                                            '/^\s*Horarios\s*(propuestos)?\s*:\s*/i',
                                                                            '',
                                                                            $partes[1] ?? '',
                                                                        ),
                                                                    );
                                                                }
                                                            @endphp
                                                            <div class="flex flex-wrap items-center gap-2">
                                                                <span
                                                                    class="text-[10px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest shrink-0">Horarios
                                                                    sugeridos:</span>
                                                                @if ($horariosDisplay)
                                                                    @foreach (array_filter(array_map('trim', explode(';', $horariosDisplay))) as $bloque)
                                                                        <span
                                                                            class="px-2.5 py-0.5 bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 border border-blue-100 dark:border-blue-800 rounded-lg text-[10px] font-bold">{{ $bloque }}</span>
                                                                    @endforeach
                                                                @else
                                                                    <span
                                                                        class="text-xs text-slate-400 dark:text-gray-500 italic">No
                                                                        definidos</span>
                                                                @endif
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endif

                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div id="historyAppointmentsView"
                        class="hidden animate-in fade-in slide-in-from-right-4 duration-500">

                        <div class="mb-2 flex flex-col sm:flex-row justify-between items-center pb-6">
                            <div class="flex items-center gap-4">

                                <div class="">
                                    <h3
                                        class="text-3xl font-black text-gray-900 dark:text-white tracking-tight whitespace-nowrap">
                                        Historial de Sesiones</h3>
                                    <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                                        Recorrido clínico completo</p>
                                </div>
                            </div>
                            <div class="mt-4 sm:mt-0 flex items-center gap-3">
                                <span id="historyCount"
                                    class="text-[10px] font-black text-slate-300 dark:text-gray-600 uppercase tracking-widest">TOTAL:
                                    0 REGISTROS</span>
                                <button onclick="toggleHistoryView(false)"
                                    class="w-10 h-10 flex items-center justify-center rounded-2xl bg-slate-50 dark:bg-gray-700 text-slate-400 dark:text-gray-400 hover:text-blue-700 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-gray-600 transition-all">
                                    <i class="fa-solid fa-calendar-days"></i>
                                </button>
                                <button type="button"
                                    onclick="document.getElementById('patientFilterModal').classList.remove('hidden'); document.getElementById('patientFilterModal').classList.add('flex');"
                                    class="flex items-center gap-2 bg-blue-800 hover:bg-blue-700 text-white px-4 h-10 rounded-xl shadow-sm transition-all"
                                    title="Filtrar Fechas">
                                    <svg class="w-4 h-4 opacity-80 flex-shrink-0" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z">
                                        </path>
                                    </svg>
                                    <span class="text-[10px] font-black uppercase tracking-wide">Filtrar</span>
                                </button>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden">
                            <div class="overflow-x-auto">
                                <table style="background-color: var(--bg-card); border-color: var(--border-color);"
                                    class="w-full text-left border-collapse">
                                    <thead>
                                        <tr
                                            class="bg-gray-50/50 dark:bg-black/20 border-b border-gray-100 dark:border-gray-800 text-[11px] font-black uppercase tracking-wider text-gray-400">
                                            <th class="px-6 py-4">Psicólogo</th>
                                            <th class="px-6 py-4">Fecha y Hora</th>
                                            <th class="px-6 py-4 text-center">Estado</th>
                                            <th class="px-6 py-4 text-right">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="historyTableBody"
                                        class="divide-y divide-gray-100 dark:divide-gray-800/60 text-xs font-medium">
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div id="historyPagination" class="mt-6 flex justify-center"></div>

                    </div>

                </div>
            </div>
        </div>
    </div>

    @if (!auth()->user()->tieneRol('psicologo'))
        <div id="historyDetailModal"
            class="fixed inset-0 z-[150] hidden items-center justify-center bg-slate-900/40 backdrop-blur-sm p-4 transition-all animate-in fade-in duration-200">
            <div style="background-color: var(--bg-card); border-color: var(--border-color);"
                class="bg-white dark:bg-gray-800 w-full max-w-lg rounded-[15px] shadow-2xl shadow-slate-200/50 dark:shadow-gray-900/50 flex flex-col overflow-hidden border border-slate-100 dark:border-gray-700">
                <div class="p-6 border-b border-slate-50 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="text-lg font-black text-slate-800 dark:text-white tracking-tight uppercase">Detalle de
                        Sesión</h3>
                    <button type="button" onclick="closeHistoryDetail()"
                        class="w-9 h-9 rounded-xl border border-gray-200 dark:border-gray-700/60 text-gray-400 hover:text-rose-600 hover:border-rose-300 inline-flex items-center justify-center transition-all active:scale-95">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div id="historyDetailContent" class="p-8 space-y-6 bg-white dark:bg-gray-800/30">
                </div>
            </div>
        </div>
    @endif

    <div id="patientCancelModal"
        class="fixed inset-0 z-[150] hidden items-center justify-center bg-slate-900/40 backdrop-blur-sm p-4 transition-all animate-in fade-in duration-200">
        <div style="background-color: var(--bg-card); border-color: var(--border-color);"
            class="bg-white dark:bg-gray-800 w-full max-w-lg rounded-[15px] shadow-2xl shadow-slate-200/50 dark:shadow-gray-900/50 flex flex-col overflow-hidden border border-slate-100 dark:border-gray-700">
            <form id="patientCancelForm" method="POST" action="" data-ajax="true" data-ajax-remove="true"
                data-ajax-close-modal="patientCancelModal" data-ajax-success-message="Cita cancelada correctamente.">
                @csrf
                @method('PATCH')
                <input type="hidden" id="patientCancelCitaId" value="">
                <div class="p-6 border-b border-slate-50 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="text-lg font-black text-gray-900 dark:text-white tracking-tight uppercase">Cancelar Cita
                    </h3>
                    <button type="button" onclick="closePatientCancelModal()"
                        class="w-9 h-9 rounded-xl border border-gray-200 dark:border-gray-700/60 text-gray-400 hover:text-rose-600 hover:border-rose-300 inline-flex items-center justify-center transition-all active:scale-95">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div class="p-6 bg-white dark:bg-gray-800/30">
                    <p class="text-sm font-medium text-slate-600 dark:text-gray-300 mb-4">¿Estás seguro que deseas
                        cancelar esta cita? Por favor explica brevemente el motivo.</p>
                    <textarea name="motivo_cancelacion" maxlength="50" required
                        class="w-full bg-white dark:bg-gray-800/30 border border-slate-200 dark:border-gray-700 rounded-xl p-3 text-sm focus:ring-2 focus:ring-rose-500 outline-none resize-none"
                        rows="2" placeholder="Me surgió un imprevisto... (máx 50 carac.)"></textarea>
                </div>
                <div class="p-6 flex justify-end gap-3 border-t border-slate-50 dark:border-gray-700">
                    <button type="button" onclick="closePatientCancelModal()"
                        class="px-5 py-2.5 text-xs font-black bg-slate-100 dark:bg-gray-700 text-slate-600 dark:text-gray-300 rounded-xl transition-all">Volver</button>
                    <button type="submit"
                        class="px-5 py-2.5 text-xs font-black text-white bg-rose-600 hover:bg-rose-700 rounded-xl transition-all shadow-md shadow-rose-200 dark:shadow-none">Confirmar</button>
                </div>
            </form>
        </div>
    </div>

    <div id="patientFilterModal"
        class="fixed inset-0 z-[150] hidden items-center justify-center bg-slate-900/40 backdrop-blur-sm p-4 transition-all animate-in fade-in duration-200">
        <div style="background-color: var(--bg-card); border-color: var(--border-color);"
            class="bg-white dark:bg-gray-800 w-full max-w-md rounded-[15px] shadow-2xl shadow-slate-200/50 dark:shadow-gray-900/50 flex flex-col max-h-[85vh] overflow-hidden border border-slate-100 dark:border-gray-700">
            <div class="p-6 border-b border-slate-50 dark:border-gray-700 flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-black text-slate-800 dark:text-white tracking-tight uppercase">Filtrar
                        Historial</h3>
                </div>
                <button type="button"
                    onclick="document.getElementById('patientFilterModal').classList.add('hidden'); document.getElementById('patientFilterModal').classList.remove('flex');"
                    class="w-9 h-9 rounded-xl border border-gray-200 dark:border-gray-700/60 text-gray-400 hover:text-rose-600 hover:border-rose-300 inline-flex items-center justify-center transition-all active:scale-95">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                            d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <form onsubmit="applyPatientFilter(event)"
                class="p-6 overflow-y-auto space-y-4 custom-scrollbar bg-white dark:bg-gray-800/30 flex-1">
                <div>
                    <label for="p_start_date"
                        class="block mb-2 text-sm font-black text-slate-700 dark:text-gray-300">Fecha de Inicio</label>
                    <input type="date" id="p_start_date" value="{{ now()->subMonth()->toDateString() }}"
                        class="w-full rounded-2xl border-slate-200 dark:border-gray-600 bg-slate-50 dark:bg-gray-700 px-4 py-3 text-sm focus:border-blue-700 focus:ring-4 focus:ring-blue-700/10 transition-all font-medium text-slate-800 dark:text-white"
                        required>
                </div>
                <div>
                    <label for="p_end_date"
                        class="block mb-2 text-sm font-black text-slate-700 dark:text-gray-300">Fecha de Fin</label>
                    <input type="date" id="p_end_date" value="{{ now()->toDateString() }}"
                        class="w-full rounded-2xl border-slate-200 dark:border-gray-600 bg-slate-50 dark:bg-gray-700 px-4 py-3 text-sm focus:border-blue-700 focus:ring-4 focus:ring-blue-700/10 transition-all font-medium text-slate-800 dark:text-white"
                        required>
                </div>
                <div class="pt-4 flex justify-end gap-2">
                    <button type="button" onclick="clearPatientFilter()"
                        class="px-6 py-3 bg-slate-100 hover:bg-slate-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-slate-700 dark:text-gray-300 font-bold rounded-2xl transition-all shadow-sm text-sm uppercase tracking-wider">Limpiar</button>
                    <button type="submit"
                        class="px-6 py-3 bg-blue-800 hover:bg-blue-700 text-white font-bold rounded-2xl transition-all shadow-md shadow-blue-100 dark:shadow-blue-900/30 text-sm uppercase tracking-wider">Aplicar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let currentHistoryData = [];
        let currentStartDate = '{{ now()->subMonth()->toDateString() }}';
        let currentEndDate = '{{ now()->toDateString() }}';

        function openCitaDetailPanel(citaId) {
            const row = document.getElementById('cita-card-' + citaId);
            if (row) {
                row.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
                row.classList.add('ring-2', 'ring-blue-400', 'ring-inset');
                setTimeout(() => row.classList.remove('ring-2', 'ring-blue-400', 'ring-inset'), 2000);
            }
        }

        function applyPatientFilter(e) {
            e.preventDefault();
            currentStartDate = document.getElementById('p_start_date').value;
            currentEndDate = document.getElementById('p_end_date').value;
            document.getElementById('patientFilterModal').classList.add('hidden');
            document.getElementById('patientFilterModal').classList.remove('flex');
            toggleHistoryView(true, 1);
        }

        document.addEventListener('DOMContentLoaded', () => {
            if (window.location.hash === '#historial') {
                toggleHistoryView(true);
            }
        });

        function toggleHistoryView(show, page = 1) {
            const activeView = document.getElementById('activeAppointmentsView');
            const historyView = document.getElementById('historyAppointmentsView');
            const tableBody = document.getElementById('historyTableBody');
            const historyCount = document.getElementById('historyCount');
            const paginationContainer = document.getElementById('historyPagination');

            if (show) {
                activeView.classList.add('hidden');
                historyView.classList.remove('hidden');

                tableBody.innerHTML = `
                    <tr>
                        <td colspan="4" class="py-12 text-center text-slate-400 dark:text-gray-500 font-bold text-xs uppercase tracking-widest">Cargando historial...</td>
                    </tr>
                `;
                if (paginationContainer) {
                    paginationContainer.innerHTML = '';
                }

                let fetchUrl =
                    `{{ route('admin.psicologia.maestros.citas.history.json') }}?page=${page}&start_date=${currentStartDate}&end_date=${currentEndDate}`;
                fetch(fetchUrl)
                    .then(res => res.json())
                    .then(response => {
                        const citas = response.data || [];
                        currentHistoryData = citas;
                        tableBody.innerHTML = '';
                        historyCount.innerText = `TOTAL: ${response.total || 0} REGISTROS`;

                        if (citas.length === 0) {
                            tableBody.innerHTML = `
                                <tr>
                                    <td colspan="4" class="py-12 text-center text-slate-400 dark:text-gray-500 font-bold text-xs uppercase tracking-widest">Historial de citas vacío</td>
                                </tr>
                            `;
                        } else {
                            citas.forEach(cita => {
                                const tr = document.createElement('tr');
                                tr.className =
                                    'hover:bg-gray-50/60 dark:hover:bg-white/[0.02] transition-colors';

                                let badgeClass =
                                    'bg-slate-50 dark:bg-gray-700 text-slate-500 dark:text-gray-400 border-slate-100 dark:border-gray-600';
                                if (cita.estado === 'realizada') badgeClass =
                                    'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 border-emerald-100 dark:border-emerald-800';
                                else if (cita.estado === 'cancelada' || cita.estado === 'rechazada' || cita
                                    .estado === 'no_asistio') badgeClass =
                                    'bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 border-rose-100 dark:border-rose-800';

                                tr.innerHTML = `
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 rounded-xl flex items-center justify-center text-[10px] font-black uppercase">
                                                ${cita.psicologo.charAt(0)}
                                            </div>
                                            <span class="text-sm font-bold text-slate-700 dark:text-gray-300">${cita.psicologo}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-bold text-slate-700 dark:text-gray-300">${cita.fecha_formateada}</span>
                                            <span class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase">${cita.hora}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center px-3 py-1 rounded-xl text-[9px] font-black uppercase tracking-widest border ${badgeClass}">
                                            ${cita.estado === 'no_asistio' ? 'AUSENTE' : (cita.estado === 'cancelada' && cita.cancelado_por ? (cita.cancelado_por === 'paciente' ? 'CANCELADA (TÚ)' : 'CANCELADA (PSICÓLOGO)') : cita.estado.toUpperCase())}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right whitespace-nowrap">
                                        <button onclick="showHistoryDetail(${cita.id})" class="w-9 h-9 rounded-xl border border-gray-200 dark:border-gray-700/60 dark:hover:border-blue-700/60 text-gray-400 hover:border-blue-300 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-600/10 dark:hover:bg-blue-950/50 inline-flex items-center justify-center transition-all active:scale-95" title="Ver detalle">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        </button>
                                    </td>
                                `;
                                tableBody.appendChild(tr);
                            });

                            renderPagination(response);
                        }
                    });
            } else {
                activeView.classList.remove('hidden');
                historyView.classList.add('hidden');
            }
        }

        function renderPagination(response) {
            const paginationContainer = document.getElementById('historyPagination');
            if (!paginationContainer) return;

            const currentPage = response.current_page;
            const lastPage = response.last_page;
            const from = response.from || 0;
            const to = response.to || 0;
            const total = response.total || 0;

            if (lastPage <= 1) {
                paginationContainer.innerHTML = '';
                return;
            }

            let html =
                `<div class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-gray-800 rounded-full shadow-sm border border-slate-100 dark:border-gray-700">`;

            if (currentPage > 1) {
                html +=
                    `<button onclick="toggleHistoryView(true, ${currentPage - 1})" class="w-8 h-8 flex items-center justify-center text-slate-500 dark:text-gray-400 hover:text-blue-800 dark:hover:text-blue-400 hover:bg-slate-50 dark:hover:bg-gray-700 rounded-lg transition-all" title="Anterior"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg></button>`;
            } else {
                html +=
                    `<button disabled class="w-8 h-8 flex items-center justify-center text-slate-300 dark:text-gray-600 rounded-lg cursor-not-allowed"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg></button>`;
            }

            const maxVisible = 5;
            let start = Math.max(1, currentPage - 2);
            let end = Math.min(lastPage, start + maxVisible - 1);
            if (end - start + 1 < maxVisible) {
                start = Math.max(1, end - maxVisible + 1);
            }

            for (let i = start; i <= end; i++) {
                if (i === currentPage) {
                    html +=
                        `<button disabled class="w-8 h-8 flex items-center justify-center text-white bg-blue-800 font-medium rounded-lg shadow-sm text-sm">${i}</button>`;
                } else {
                    html +=
                        `<button onclick="toggleHistoryView(true, ${i})" class="w-8 h-8 flex items-center justify-center text-slate-600 dark:text-gray-300 hover:text-blue-800 dark:hover:text-blue-400 hover:bg-slate-50 dark:hover:bg-gray-700 font-medium rounded-lg transition-all text-sm">${i}</button>`;
                }
            }

            if (currentPage < lastPage) {
                html +=
                    `<button onclick="toggleHistoryView(true, ${currentPage + 1})" class="w-8 h-8 flex items-center justify-center text-slate-500 dark:text-gray-400 hover:text-blue-800 dark:hover:text-blue-400 hover:bg-slate-50 dark:hover:bg-gray-700 rounded-lg transition-all" title="Siguiente"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg></button>`;
            } else {
                html +=
                    `<button disabled class="w-8 h-8 flex items-center justify-center text-slate-300 dark:text-gray-600 rounded-lg cursor-not-allowed"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg></button>`;
            }

            html += `</div>`;
            paginationContainer.innerHTML = html;
        }

        function showHistoryDetail(id) {
            const cita = currentHistoryData.find(c => c.id == id);
            if (!cita) return;

            const modal = document.getElementById('historyDetailModal');
            const content = document.getElementById('historyDetailContent');

            let statusLabel = cita.estado === 'no_asistio' ? 'Ausente' : cita.estado;
            if (cita.estado === 'cancelada' && cita.cancelado_por) {
                statusLabel = cita.cancelado_por === 'paciente' ? 'Cancelada por ti' : 'Cancelada por el psicólogo';
            }

            let parsedNotas = {
                motivo_consulta: '',
                observaciones: cita.notas || '',
                intervenciones: '',
                avance_estado: '',
                avance_detalle: ''
            };
            try {
                const json = JSON.parse(cita.notas);
                if (typeof json === 'object' && json !== null) {
                    parsedNotas = {
                        motivo_consulta: json.motivo_consulta || '',
                        observaciones: json.observaciones || '',
                        intervenciones: json.intervenciones || '',
                        avance_estado: json.avance_estado || '',
                        avance_detalle: json.avance_detalle || ''
                    };
                }
            } catch (e) {}

            let avanceBadge = '';
            if (parsedNotas.avance_estado) {
                const aMap = {
                    'estancado': {
                        color: 'text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-900/30 border-rose-100 dark:border-rose-800',
                        label: 'Estancado'
                    },
                    'en_progreso': {
                        color: 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/30 border-blue-100 dark:border-blue-800',
                        label: 'En Progreso'
                    },
                    'logrado': {
                        color: 'text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/30 border-emerald-100 dark:border-emerald-800',
                        label: 'Logrado'
                    }
                };
                const style = aMap[parsedNotas.avance_estado] || {
                    color: 'text-slate-400 dark:text-gray-500 bg-slate-50 dark:bg-gray-700 border-slate-100 dark:border-gray-600',
                    label: parsedNotas.avance_estado
                };
                avanceBadge =
                    `<span class="px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest border ${style.color}">${style.label}</span>`;
            }

            content.innerHTML = `
                <div class="grid grid-cols-2 gap-y-6 overflow-y-auto max-h-[70vh] pr-2 custom-scrollbar">
                    <div class="col-span-2 flex items-center gap-4 pb-4 border-b border-slate-50 dark:border-gray-700">
                        <div class="w-12 h-12 bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 rounded-2xl flex items-center justify-center text-sm font-black uppercase">
                            ${cita.psicologo.charAt(0)}
                        </div>
                        <div class="flex-1">
                            <p class="text-[10px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest mb-1">Profesional Asignado</p>
                            <h4 class="text-base font-black text-slate-800 dark:text-white tracking-tight">${cita.psicologo}</h4>
                        </div>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest mb-1">Fecha</p>
                        <p class="text-sm font-bold text-slate-700 dark:text-gray-300">${cita.fecha_formateada}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest mb-1">Estado</p>
                        <span class="text-[10px] font-black text-blue-700 dark:text-blue-400 uppercase tracking-widest">${statusLabel.toUpperCase()}</span>
                    </div>
                    <div class="col-span-2">
                        <p class="text-[10px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest mb-1">Motivo de Solicitud</p>
                        <p class="text-sm font-medium text-slate-600 dark:text-gray-300 italic leading-relaxed">"${cita.motivo || 'No especificado'}"</p>
                    </div>
            `;

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function clearPatientFilter() {
            document.getElementById('p_start_date').value = '';
            document.getElementById('p_end_date').value = '';
            currentStartDate = '';
            currentEndDate = '';
            document.getElementById('patientFilterModal').classList.add('hidden');
            document.getElementById('patientFilterModal').classList.remove('flex');
            toggleHistoryView(true, 1);
        }

        function closeHistoryDetail() {
            const modal = document.getElementById('historyDetailModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        window.addEventListener('click', (e) => {
            const modal = document.getElementById('historyDetailModal');
            if (e.target === modal) closeHistoryDetail();
            const filterModal = document.getElementById('patientFilterModal');
            if (e.target === filterModal) {
                filterModal.classList.add('hidden');
                filterModal.classList.remove('flex');
            }
            const cancelModal = document.getElementById('patientCancelModal');
            if (e.target === cancelModal) closePatientCancelModal();
        });

        function openPatientCancelModal(actionUrl, citaId) {
            const modal = document.getElementById('patientCancelModal');
            const form = document.getElementById('patientCancelForm');
            const idInput = document.getElementById('patientCancelCitaId');
            form.action = actionUrl;
            idInput.value = citaId;
            form.setAttribute('data-target-card-id', citaId);
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closePatientCancelModal() {
            const modal = document.getElementById('patientCancelModal');
            const form = document.getElementById('patientCancelForm');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            form.reset();
        }

        function responderPropuesta(citaId, opcion, bloque = '') {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (!token) return;

            AppModal.confirm('¿Confirmar respuesta?',
                '¿Estás seguro de enviar esta respuesta a la propuesta de cambio de horario?', {
                    iconColor: 'bg-blue-50 text-blue-700',
                    btnColor: 'bg-blue-700 hover:bg-blue-800'
                }).then(confirmed => {
                if (!confirmed) return;

                fetch(`citas/${citaId}/responder-propuesta`, {
                        method: 'PATCH',
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            opcion: opcion,
                            bloque: bloque
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            AppModal.alert('Éxito', data.message || 'Respuesta enviada correctamente.').then(
                            () => {
                                    window.location.reload();
                                });
                        } else {
                            AppModal.alert('Error', data.message || 'Error al enviar la respuesta.');
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        AppModal.alert('Error', 'Ocurrió un error de conexión.');
                    });
            });
        }

        function responderPropuestaForm(event, citaId) {
            event.preventDefault();
            const form = event.target;
            const formData = new FormData(form);
            const bloqueSeleccionado = formData.get('bloque_seleccionado');
            const motivoRechazo = formData.get('motivo_rechazo');
            const nuevosBloques = formData.get('nuevos_bloques');

            if (!bloqueSeleccionado) {
                AppModal.alert('Error', 'Debes seleccionar una opción.');
                return;
            }

            if (bloqueSeleccionado === 'ninguno' && (!motivoRechazo || motivoRechazo.trim() === '')) {
                AppModal.alert('Requerido', 'Por favor explica brevemente el motivo del rechazo.');
                return;
            }

            if (bloqueSeleccionado === 'ninguno') {
                const state = calendarsState[citaId];
                let daysWithSlotsCount = 0;
                if (state) {
                    state.diasSeleccionados.forEach(ymd => {
                        if (state.selectedSlotsByDate[ymd] && state.selectedSlotsByDate[ymd].length > 0) {
                            daysWithSlotsCount++;
                        }
                    });
                }
                const isValidDays = state && state.diasSeleccionados.length >= 2;
                const isValidSlots = state && state.diasSeleccionados.length > 0 && daysWithSlotsCount === state
                    .diasSeleccionados.length;

                if (!isValidDays || !isValidSlots) {
                    AppModal.alert('Requerido',
                        'Debes sugerir al menos un nuevo horario del calendario para enviar la propuesta (mín. 2 días).'
                        );
                    return;
                }
            }

            const opcion = bloqueSeleccionado === 'ninguno' ? 'rechazada' : 'aceptada';
            const bloque = bloqueSeleccionado === 'ninguno' ? '' : bloqueSeleccionado;

            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (!token) return;

            const submitBtn = document.getElementById(`btn-submit-${citaId}`);
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerText = 'Enviando...';
            }

            AppModal.confirm('¿Confirmar respuesta?',
                '¿Estás seguro de enviar esta respuesta a la propuesta de cambio de horario?', {
                    iconColor: 'bg-blue-50 text-blue-700',
                    btnColor: 'bg-blue-700 hover:bg-blue-800',
                    btnText: 'Sí, enviar'
                }).then(confirmed => {
                if (!confirmed) {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerText = 'Enviar';
                    }
                    return;
                }

                fetch(`citas/${citaId}/responder-propuesta`, {
                        method: 'PATCH',
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            opcion: opcion,
                            bloque: bloque,
                            nuevos_bloques: nuevosBloques,
                            motivo_rechazo: motivoRechazo
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            if (window.showToast) window.showToast(data.message ||
                                'Respuesta enviada correctamente.', 'success');
                            setTimeout(() => window.location.reload(), 1500);
                        } else {
                            AppModal.alert('Error', data.message || 'Error al enviar la respuesta.');
                            if (submitBtn) {
                                submitBtn.disabled = false;
                                submitBtn.innerText = 'Enviar';
                            }
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        AppModal.alert('Error', 'Ocurrió un error de conexión.');
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.innerText = 'Enviar';
                        }
                    });
            });
        }

        const MESES = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

        function toYMD(d) {
            const y = d.getFullYear();
            const m = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            return `${y}-${m}-${day}`;
        }

        const calendarsState = {};

        function initCalendarForCita(citaId, psicologoId) {
            try {
                if (calendarsState[citaId]) return;

                const startDate = new Date();
                const endDate = new Date();
                endDate.setDate(startDate.getDate() + 30);

                calendarsState[citaId] = {
                    startDate: startDate,
                    endDate: endDate,
                    diasSeleccionados: [],
                    selectedSlotsByDate: {},
                    disponibilidad: {},
                    activeDay: null,
                    psicologoId: psicologoId
                };

                const grid = document.getElementById(`calendarGrid-${citaId}`);
                if (!grid) return;
                grid.innerHTML =
                    '<div class="col-span-7 text-center py-4 text-slate-400 dark:text-gray-400 text-sm">Cargando disponibilidad...</div>';

                fetch(`citas/available_slots?psicologo_id=${psicologoId}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(r => r.json())
                    .then(data => {
                        calendarsState[citaId].disponibilidad = data.disponibilidad || {};
                        renderCalendarForCita(citaId);
                        renderSlotsForCita(citaId);
                    })
                    .catch(err => {
                        console.error(err);
                        if (grid) grid.innerHTML =
                            `<div class="col-span-7 text-center py-4 text-rose-600 dark:text-rose-400 text-sm">Error fetch: ${err.message}</div>`;
                    });
            } catch (err) {
                const grid = document.getElementById(`calendarGrid-${citaId}`);
                if (grid) grid.innerHTML =
                    `<div class="col-span-7 text-rose-600 dark:text-rose-400 text-xs py-2">Error init: ${err.message}</div>`;
            }
        }

        function renderCalendarForCita(citaId) {
            const grid = document.getElementById(`calendarGrid-${citaId}`);
            if (!grid) return;

            try {
                grid.innerHTML = '';

                const state = calendarsState[citaId];
                let dCounter = new Date(state.startDate);
                const allDays = [];
                while (dCounter <= state.endDate) {
                    allDays.push(new Date(dCounter));
                    dCounter.setDate(dCounter.getDate() + 1);
                }

                const firstDayOfWeek = allDays[0].getDay();
                for (let i = 0; i < firstDayOfWeek; i++) {
                    const emptyCell = document.createElement('div');
                    emptyCell.className = 'py-1';
                    grid.appendChild(emptyCell);
                }

                allDays.forEach(d => {
                    const ymd = toYMD(d);
                    const isAvailable = state.disponibilidad && state.disponibilidad[ymd] && state.disponibilidad[
                        ymd].length > 0;
                    const isActive = state.activeDay === ymd;
                    const isSelected = state.diasSeleccionados.includes(ymd);

                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.dataset.date = ymd;

                    let baseClasses =
                        'relative flex items-center justify-center h-8 w-full rounded-lg text-xs font-bold transition-all duration-200 ';

                    if (!isAvailable) {
                        baseClasses +=
                            'bg-slate-100 dark:bg-gray-800/60 text-slate-300 dark:text-gray-600 cursor-not-allowed opacity-50';
                    } else if (isSelected) {
                        baseClasses +=
                            'bg-sky-100 dark:bg-sky-950/60 text-sky-700 dark:text-sky-300 hover:bg-sky-200 dark:hover:bg-sky-900/60 cursor-pointer border border-sky-400 dark:border-sky-500/60 shadow-sm';
                    } else {
                        baseClasses +=
                            'bg-white dark:bg-gray-800 text-slate-600 dark:text-gray-300 border border-slate-200 dark:border-gray-700 hover:border-slate-300 dark:hover:border-gray-600 cursor-pointer shadow-sm';
                    }

                    if (isActive) baseClasses += ' ring-2 ring-sky-600 dark:ring-sky-400';

                    btn.className = baseClasses;
                    btn.innerHTML = `<span>${d.getDate()}</span>`;

                    if (isSelected && state.selectedSlotsByDate && state.selectedSlotsByDate[ymd] && state
                        .selectedSlotsByDate[ymd].length > 0) {
                        btn.innerHTML +=
                            '<span class="absolute top-1 right-1 w-1.5 h-1.5 bg-emerald-500 dark:bg-emerald-400 rounded-full"></span>';
                    }

                    if (isAvailable) {
                        btn.addEventListener('click', () => toggleDateForCita(citaId, ymd));
                    }
                    grid.appendChild(btn);
                });
            } catch (err) {
                grid.innerHTML =
                    `<div class="col-span-7 text-rose-600 dark:text-rose-400 text-xs py-2">Error render: ${err.message}</div>`;
            }
        }

        function toggleDateForCita(citaId, ymd) {
            const state = calendarsState[citaId];
            const isSelected = state.diasSeleccionados.includes(ymd);
            if (isSelected) {
                if (state.activeDay === ymd) {
                    state.diasSeleccionados = state.diasSeleccionados.filter(d => d !== ymd);
                    delete state.selectedSlotsByDate[ymd];
                    state.activeDay = state.diasSeleccionados.length > 0 ? state.diasSeleccionados[state.diasSeleccionados
                        .length - 1] : null;
                } else {
                    state.activeDay = ymd;
                }
            } else {
                state.diasSeleccionados.push(ymd);
                state.activeDay = ymd;
            }
            renderCalendarForCita(citaId);
            renderSlotsForCita(citaId);
            updateHiddenBlocksForCita(citaId);
        }

        function renderSlotsForCita(citaId) {
            const container = document.getElementById(`slotsContainer-${citaId}`);
            if (!container) return;
            container.innerHTML = '';
            const state = calendarsState[citaId];

            if (!state.activeDay || state.diasSeleccionados.length === 0) {
                container.innerHTML =
                    '<div class="col-span-full text-center text-xs text-slate-400 dark:text-gray-400 py-2">Selecciona un día en el calendario para ver los horarios.</div>';
                return;
            }

            const slots = state.disponibilidad[state.activeDay] || [];
            if (slots.length === 0) {
                container.innerHTML =
                    '<div class="col-span-full text-center text-xs text-slate-400 dark:text-gray-400 py-2">No hay horarios disponibles este día.</div>';
                return;
            }

            if (!state.selectedSlotsByDate[state.activeDay]) {
                state.selectedSlotsByDate[state.activeDay] = [];
            }

            slots.forEach(slot => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className =
                    'flex items-center justify-center px-3 py-2 rounded-lg border text-xs font-semibold transition-all duration-200 shadow-sm ';

                if (state.selectedSlotsByDate[state.activeDay].includes(slot)) {
                    btn.className +=
                        'border-sky-600 dark:border-sky-500 bg-sky-600 dark:bg-sky-400/10 text-white shadow-md scale-[1.02]';
                } else {
                    btn.className +=
                        'border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-slate-700 dark:text-gray-300 hover:border-sky-400 dark:hover:border-sky-500/50 hover:bg-sky-50 dark:hover:bg-sky-950/30';
                }

                btn.textContent = slot;
                btn.addEventListener('click', () => {
                    const ad = state.activeDay;
                    if (state.selectedSlotsByDate[ad].includes(slot)) {
                        state.selectedSlotsByDate[ad] = state.selectedSlotsByDate[ad].filter(s => s !==
                            slot);
                    } else {
                        state.selectedSlotsByDate[ad].push(slot);
                    }
                    renderCalendarForCita(citaId);
                    renderSlotsForCita(citaId);
                    updateHiddenBlocksForCita(citaId);
                });
                container.appendChild(btn);
            });
        }

        function updateHiddenBlocksForCita(citaId) {
            const state = calendarsState[citaId];
            const blocksStringParts = [];
            state.diasSeleccionados.sort().forEach(ymd => {
                if (state.selectedSlotsByDate[ymd] && state.selectedSlotsByDate[ymd].length > 0) {
                    blocksStringParts.push(`${ymd}: ${state.selectedSlotsByDate[ymd].join(', ')}`);
                }
            });

            const daysStr = state.diasSeleccionados.length > 0 ? "Días propuestos: " + state.diasSeleccionados.sort().join(
                ', ') + " | " : "Días propuestos: Ninguno | ";
            const slotsStr = "Horarios propuestos: " + (blocksStringParts.length > 0 ? blocksStringParts.join('; ') :
                "Ninguno");
            document.getElementById(`nuevos_bloques_${citaId}`).value = daysStr + slotsStr;
            updateSubmitBtnState(citaId);
        }

        function updateSubmitBtnState(citaId) {
            const btnSubmit = document.getElementById(`btn-submit-${citaId}`);
            if (!btnSubmit) return;

            const radioNinguno = document.getElementById(`radio-ninguno-${citaId}`);
            if (radioNinguno && radioNinguno.checked) {
                btnSubmit.innerText = 'Enviar';
                const state = calendarsState[citaId];
                let daysWithSlotsCount = 0;
                if (state) {
                    state.diasSeleccionados.forEach(ymd => {
                        if (state.selectedSlotsByDate[ymd] && state.selectedSlotsByDate[ymd].length > 0) {
                            daysWithSlotsCount++;
                        }
                    });
                }
                const isValidDays = state && state.diasSeleccionados.length >= 2;
                const isValidSlots = state && state.diasSeleccionados.length > 0 && daysWithSlotsCount === state
                    .diasSeleccionados.length;

                const helpText = document.getElementById(`minBlocksHelpText-${citaId}`);
                if (!isValidDays || !isValidSlots) {
                    btnSubmit.disabled = true;
                    btnSubmit.className =
                        'px-5 py-2 text-xs font-black bg-rose-600/40 dark:bg-rose-950/50 text-white/70 dark:text-rose-200/50 rounded-xl transition-all cursor-not-allowed';
                    if (helpText) helpText.classList.remove('hidden');
                } else {
                    btnSubmit.disabled = false;
                    btnSubmit.className =
                        'px-5 py-2 text-xs font-black bg-rose-600 hover:bg-rose-700 dark:bg-rose-600 dark:hover:bg-rose-500 text-white rounded-xl shadow-md transition-all active:scale-95';
                    if (helpText) helpText.classList.add('hidden');
                }
            } else {
                const radioOther = document.querySelector(
                    `#form-propuesta-${citaId} input[name="bloque_seleccionado"]:checked`);
                if (radioOther) {
                    btnSubmit.disabled = false;
                    btnSubmit.innerText = 'Enviar';
                    btnSubmit.className =
                        'px-5 py-2 text-xs font-black bg-sky-600 hover:bg-sky-700 dark:bg-sky-600 dark:hover:bg-sky-500 text-white rounded-xl shadow-md transition-all active:scale-95';
                    const helpText = document.getElementById(`minBlocksHelpText-${citaId}`);
                    if (helpText) helpText.classList.add('hidden');
                }
            }
        }
    </script>
</x-app-layout>
