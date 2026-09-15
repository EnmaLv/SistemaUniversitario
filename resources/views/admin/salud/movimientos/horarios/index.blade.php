<x-app-layout>
    <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @include('components.alert')

            {{-- Encabezado principal --}}
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-black tracking-tight flex items-center gap-3"
                        style="color: var(--text-main);">
                        <span>Horarios de Consultorios</span>
                        <span
                            class="px-2.5 py-1 text-xs font-bold rounded-full bg-sky-100 text-sky-700 dark:bg-sky-950/60 dark:text-sky-300">
                            Gestión Semanal
                        </span>
                    </h1>
                    <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                        Bienvenido <span
                            class="font-bold text-gray-700 dark:text-gray-200">{{ auth()->user()->persona->nombre_persona ?? auth()->user()->username }}</span>
                        ·
                        {{ \Carbon\Carbon::now()->format('d/m/Y') }}
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.salud.movimientos.horarios.create', ['consultorio_id' => $consultorioId]) }}"
                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 active:scale-95 text-white font-bold text-sm shadow-md shadow-sky-500/20 transition-all">
                        <i class="fas fa-plus text-xs"></i>
                        <span>Asignar Horario</span>
                    </a>
                </div>
            </div>

            {{-- Selector de consultorio y Acciones --}}
            <form id="filtro-form" action="{{ route('admin.salud.movimientos.horarios.index') }}" method="GET"
                style="background-color: var(--bg-card); border-color: var(--border-color);"
                class="p-4 rounded-2xl border shadow-sm mb-6 flex flex-col lg:flex-row lg:items-center justify-between gap-4">

                {{-- Selector de Consultorio --}}
                <div class="flex items-center rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all w-full lg:max-w-md shadow-sm"
                    style="border-color: var(--border-color);">
                    <span
                        class="flex items-center justify-center px-4 py-2.5 bg-gray-50 dark:bg-black/20 text-sky-600 dark:text-sky-400 border-r"
                        style="border-color: var(--border-color);">
                        <i class="fas fa-door-open text-base"></i>
                    </span>
                    <select name="consultorio_id" onchange="document.getElementById('filtro-form').submit()"
                        style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                        class="w-full px-3 py-2.5 text-sm font-semibold border-none focus:ring-0 focus:outline-none transition-all cursor-pointer">
                        @forelse ($consultorios as $consultorio)
                            <option value="{{ $consultorio->id }}"
                                {{ $consultorioId === $consultorio->id ? 'selected' : '' }}>
                                {{ $consultorio->nombre }}
                            </option>
                        @empty
                            <option value="">No hay consultorios registrados</option>
                        @endforelse
                    </select>
                </div>

                {{-- Botón PDF --}}
                <div class="flex items-center gap-3 w-full lg:w-auto justify-end">
                    <a href="{{ route('admin.salud.movimientos.horarios.pdf', ['consultorio_id' => $consultorioSeleccionado ?? $consultorioId]) }}"
                        target="_blank"
                        class="p-2 rounded-xl text-gray-500 dark:text-gray-400 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition-all flex items-center justify-center flex-shrink-0"
                        title="Imprimir Horarios en PDF">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                    </a>
                </div>

            </form>

            @if ($consultorios->isEmpty())
                <div style="background-color: var(--bg-card); border-color: var(--border-color);"
                    class="rounded-2xl border shadow-sm p-12 text-center">
                    <div
                        class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-400">
                        <i class="fas fa-door-closed text-2xl"></i>
                    </div>
                    <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider">No hay consultorios registrados</h3>
                    <p class="text-xs text-gray-400 mt-1">Registra un consultorio para comenzar a gestionar sus horarios.</p>
                </div>
            @else
                @php
                    $totalJornadas = count(\App\Models\salud\HorarioConsultorio::BLOQUES);
                @endphp

                {{-- Cuadrilla semanal por jornada --}}
                @foreach (\App\Models\salud\HorarioConsultorio::BLOQUES as $jornada => $bloques)
                    @php
                        $jornadaIndex = $loop->index;
                    @endphp

                    <div id="jornada-block-{{ $jornadaIndex }}"
                        class="jornada-block mb-8 {{ $jornadaIndex !== 0 ? 'hidden' : '' }}">

                        {{-- Encabezado --}}
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 mb-4">
                            <div class="hidden sm:block sm:w-44"></div>

                            <div class="flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-gray-100 dark:bg-gray-800/70 border border-gray-200/80 dark:border-gray-700/60 shadow-sm text-center">
                                <h3 class="text-xs sm:text-sm font-black uppercase tracking-wider text-gray-700 dark:text-gray-200">
                                    Jornada {{ $jornada }}
                                </h3>
                            </div>

                            <div style="background-color: var(--bg-card); border-color: var(--border-color); color: var(--text-main);"
                                class="flex items-center justify-between md:justify-center gap-1 border border-gray-200 dark:border-gray-700/60 p-1 h-12 rounded-2xl shadow-sm flex-shrink-0 w-full sm:w-auto">
                                <button type="button" onclick="cambiarJornada(-1)" title="Jornada Anterior"
                                    class="w-9 h-9 flex items-center justify-center rounded-xl hover:bg-white dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-all flex-shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                    </svg>
                                </button>

                                <span class="px-2 sm:px-4 text-[10px] sm:text-[11px] font-black text-gray-800 dark:text-gray-200 min-w-[90px] text-center uppercase tracking-wider leading-none whitespace-nowrap">
                                    {{ $loop->iteration }} / {{ $totalJornadas }}
                                </span>

                                <button type="button" onclick="cambiarJornada(1)" title="Siguiente Jornada"
                                    class="w-9 h-9 flex items-center justify-center rounded-xl hover:bg-white dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-all flex-shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        {{-- Tabla --}}
                        <div style="border-color: var(--border-color); background-color: var(--bg-card);"
                            class="rounded-2xl border shadow-sm overflow-x-auto">
                            <div class="grid min-w-[750px]" style="grid-template-columns: 130px repeat(5, 1fr);">

                                {{-- Header Días --}}
                                <div class="p-3 border-b border-r bg-gray-50/70 dark:bg-black/30 flex items-center justify-center font-bold text-[11px] text-gray-400 uppercase tracking-wider"
                                    style="border-color: var(--border-color);">
                                    <i class="far fa-clock mr-1.5"></i> Bloque
                                </div>
                                @foreach (\App\Models\salud\HorarioConsultorio::DIAS as $diaKey => $diaLabel)
                                    <div class="p-3 text-center text-[11px] font-black uppercase tracking-wider text-gray-600 dark:text-gray-300 border-b bg-gray-50/70 dark:bg-black/30 {{ !$loop->last ? 'border-r' : '' }}"
                                        style="border-color: var(--border-color);">
                                        {{ $diaLabel }}
                                    </div>
                                @endforeach

                                {{-- Filas por Bloque de Horario --}}
                                @foreach ($bloques as $bloque)
                                    {{-- Columna Hora --}}
                                    <div class="p-4 flex items-center justify-center text-center text-xs font-extrabold text-gray-700 dark:text-gray-200 whitespace-nowrap border-r bg-gray-50/30 dark:bg-black/10 {{ !$loop->last ? 'border-b' : '' }}"
                                        style="border-color: var(--border-color);">
                                        {{ \Carbon\Carbon::parse($bloque['inicio'])->format('g:i') }} -
                                        {{ \Carbon\Carbon::parse($bloque['fin'])->format('g:i') }}
                                    </div>

                                    {{-- Celdas por Día --}}
                                    @foreach (\App\Models\salud\HorarioConsultorio::DIAS as $diaKey => $diaLabel)
                                        @php
                                            $diaRegistros = $horarios->get($diaKey) ?? collect();
                                            $registrosEnBloque = collect($diaRegistros)->filter(function ($item) use ($bloque) {
                                                $inicioItem = \Carbon\Carbon::parse($item->hora_inicio)->format('H:i:s');
                                                $finItem = \Carbon\Carbon::parse($item->hora_fin)->format('H:i:s');
                                                $inicioBloque = \Carbon\Carbon::parse($bloque['inicio'])->format('H:i:s');
                                                $finBloque = \Carbon\Carbon::parse($bloque['fin'])->format('H:i:s');
                                                return $inicioItem === $inicioBloque && $finItem === $finBloque;
                                            });
                                        @endphp

                                        <div class="p-4 flex flex-col gap-1 items-center justify-center min-h-[68px] {{ !$loop->last ? 'border-r' : '' }} {{ !$loop->parent->last ? 'border-b' : '' }}"
                                            style="border-color: var(--border-color);">

                                            @if ($registrosEnBloque->isNotEmpty())
                                                <div class="w-full h-full max-h-[160px] overflow-y-auto flex flex-col gap-1 pr-0.5">
                                                    @foreach ($registrosEnBloque as $registro)
                                                        @php
                                                            $nombrePersonal = $registro->nombre_personal;
                                                            $nombreRolAsignado = $registro->nombre_rol_asignado;
                                                        @endphp

                                                        <div class="w-full rounded-xl bg-sky-300/20 hover:bg-sky-300/30 dark:bg-sky-800/30 hover:dark:bg-sky-800/50 border border-sky-200 dark:border-sky-800 p-1.5 flex items-center justify-between gap-1 shadow-sm group hover:border-sky-400 dark:hover:border-sky-600 transition-all">
                                                            <div class="flex items-center gap-1.5 min-w-0 flex-1">
                                                                <i class="fas fa-user-md text-[12px] text-sky-600 dark:text-sky-400 flex-shrink-0"></i>
                                                                <div class="min-w-0 leading-tight">
                                                                    <span class="block text-[12px] font-bold text-sky-900 dark:text-sky-100 truncate" title="{{ $nombrePersonal }}">
                                                                        {{ $nombrePersonal }}
                                                                    </span>
                                                                    <span class="block text-[10px] font-medium text-sky-600 dark:text-sky-400 truncate">
                                                                        {{ $nombreRolAsignado }}
                                                                    </span>
                                                                </div> 
                                                            </div>

                                                            <form id="form-delete-{{ $registro->id }}"
                                                                action="{{ route('admin.salud.movimientos.horarios.destroy', $registro->id) }}"
                                                                method="POST">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit"
                                                                    title="Eliminar Horario"
                                                                    class="w-4 h-4 flex items-center justify-center rounded text-sky-400 hover:text-rose-600 hover:bg-rose-100 dark:hover:bg-rose-950/60 transition-all active:scale-90 flex-shrink-0">
                                                                    <i class="fas fa-times text-[9px]"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <div class="w-full h-full min-h-[58px] rounded-xl border border-dashed border-gray-300 dark:border-gray-700 bg-gray-50/50 dark:bg-black/10 flex items-center justify-center text-[10px] font-semibold text-gray-500 dark:text-gray-500 select-none">
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
            @endif

        </div>
    </div>

    <script>
        let jornadaActual = 0;
        const totalJornadas = {{ count(\App\Models\salud\HorarioConsultorio::BLOQUES) }};

        function cambiarJornada(direccion) {
            const bloqueActual = document.getElementById(`jornada-block-${jornadaActual}`);
            if (bloqueActual) {
                bloqueActual.classList.add('hidden');
            }

            jornadaActual = (jornadaActual + direccion + totalJornadas) % totalJornadas;

            const siguienteBloque = document.getElementById(`jornada-block-${jornadaActual}`);
            if (siguienteBloque) {
                siguienteBloque.classList.remove('hidden');
            }
        }
        
    </script>
</x-app-layout>