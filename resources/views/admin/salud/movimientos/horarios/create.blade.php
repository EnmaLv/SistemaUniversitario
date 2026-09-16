<x-app-layout>
    <div class="pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @include('components.alert')

            {{-- Encabezado Principal --}}
            <div
                class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-1 border-b border-gray-200/50 dark:border-gray-800/50">
                <div>
                    <div class="flex items-center gap-3 flex-wrap">
                        <h1 class="text-2xl sm:text-3xl font-black tracking-tight" style="color: var(--text-main);">
                            Asignar Horario
                        </h1>
                        <span
                            class="px-2.5 py-1 text-xs font-bold rounded-full bg-sky-100 text-sky-700 dark:bg-sky-950/60 dark:text-sky-300">
                            Gestión Múltiple
                        </span>
                    </div>
                    <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                        Bienvenido <span
                            class="font-bold text-gray-700 dark:text-gray-200">{{ auth()->user()->nombre_completo }}</span>
                        · {{ \Carbon\Carbon::now()->format('d/m/Y') }}
                    </p>
                </div>

                <a href="{{ route('admin.salud.movimientos.horarios.index', ['consultorio_id' => $consultorioSeleccionado]) }}"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border text-xs font-bold hover:bg-gray-50 dark:hover:bg-white/5 transition-all shadow-sm self-start sm:self-center"
                    style="border-color: var(--border-color); color: var(--text-main);">
                    <i class="fas fa-arrow-left text-[10px]"></i>
                    <span>Volver</span>
                </a>
            </div>

            <form action="{{ route('admin.salud.movimientos.horarios.store') }}" method="POST"
                class="rd-prevent-double-submit space-y-6">
                @csrf

                {{-- Card de Selección de Consultorio y Barra de Estado --}}
                <div style="background-color: var(--bg-card); border-color: var(--border-color);"
                    class="p-4 sm:p-5 rounded-2xl border shadow-sm flex flex-col md:flex-row md:items-end justify-between gap-4">

                    {{-- Selector de Consultorio --}}
                    <div class="w-full md:max-w-md">
                        <label class="block text-[11px] font-black uppercase tracking-wider text-gray-400 mb-1.5">
                            Seleccionar Consultorio
                        </label>
                        <div class="flex items-center rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all shadow-sm"
                            style="border-color: var(--border-color);">
                            <span
                                class="flex items-center justify-center px-3.5 py-2.5 bg-gray-50 dark:bg-black/20 text-sky-600 dark:text-sky-400 border-r"
                                style="border-color: var(--border-color);">
                                <i class="fas fa-door-open text-sm"></i>
                            </span>
                            <select name="consultorio_id" id="consultorio_id" required
                                style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                class="w-full px-3 py-2 text-sm font-semibold border-none focus:ring-0 focus:outline-none transition-all cursor-pointer">
                                <option value="" disabled {{ !$consultorioSeleccionado ? 'selected' : '' }}>
                                    Seleccione un consultorio
                                </option>
                                @foreach ($consultorios as $consultorio)
                                    <option value="{{ $consultorio->id }}"
                                        {{ old('consultorio_id', $consultorioSeleccionado) == $consultorio->id ? 'selected' : '' }}>
                                        {{ $consultorio->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('consultorio_id')
                            <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Contador y Limpieza de Selección --}}
                    <div
                        class="flex items-center justify-between md:justify-end gap-3 w-full md:w-auto border-t md:border-t-0 pt-3 md:pt-0 border-gray-100 dark:border-gray-800">
                        <div
                            class="px-3.5 py-2 rounded-xl bg-sky-50 dark:bg-sky-950/40 border border-sky-100 dark:border-sky-900/50 flex items-center gap-2">
                            <i class="fas fa-check-circle text-sky-600 dark:text-sky-400 text-xs"></i>
                            <span class="text-xs font-extrabold text-sky-700 dark:text-sky-300">
                                <span id="contadorSeleccion">0</span> asignación(es) realizada(s)
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

                {{-- Contenedor Principal en Grid 12 Columnas --}}
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

                    {{-- Lista Lateral de Personal Disponible --}}
                    <div class="lg:col-span-3 lg:sticky lg:top-6">
                        <div style="background-color: var(--bg-card); border-color: var(--border-color);"
                            class="p-4 rounded-2xl border shadow-sm flex flex-col max-h-[calc(100vh-8rem)]">

                            <div
                                class="flex items-center justify-between pb-3 mb-3 border-b border-gray-100 dark:border-gray-800">
                                <h3
                                    class="text-xs font-black uppercase tracking-wider text-gray-700 dark:text-gray-200 flex items-center gap-2">
                                    <i class="fas fa-user-md text-sky-500"></i>
                                    <span>Personal Disponible</span>
                                </h3>
                                <span
                                    class="px-2 py-0.5 text-[10px] font-extrabold rounded-full bg-sky-100 text-sky-700 dark:bg-sky-950 dark:text-sky-300">
                                    {{ count($usuariosElegibles) }}
                                </span>
                            </div>

                            <p class="text-[11px] text-gray-500 dark:text-gray-400 mb-3 leading-tight">
                                Arrastra los usuarios hacia los bloques correspondientes.
                            </p>

                            {{-- Buscador de Usuarios --}}
                            <div class="relative mb-3">
                                <i class="fas fa-search absolute left-3 top-2.5 text-xs text-gray-400"></i>
                                <input type="text" id="searchUser" placeholder="Buscar persona..."
                                    class="w-full pl-8 pr-3 py-1.5 text-xs rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-black/20 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-1 focus:ring-sky-500">
                            </div>

                            {{-- Tarjetas Draggable --}}
                            <div id="usersList" class="space-y-2 overflow-y-auto pr-1 flex-1 min-h-0">
                                @forelse ($usuariosElegibles as $usr)
                                    <div class="user-card flex items-center gap-2.5 p-2.5 rounded-xl border border-gray-200 dark:border-gray-700/80 bg-white dark:bg-gray-800/60 hover:border-sky-400 cursor-grab active:cursor-grabbing transition-all select-none group"
                                        draggable="true" ondragstart="handleDragStart(event)"
                                        data-id="{{ $usr->id_rol_usuario }}" data-nombre="{{ $usr->nombre_completo }}"
                                        data-rol="{{ $usr->nombre_rol }}">

                                        <div
                                            class="w-8 h-8 rounded-lg bg-sky-100 dark:bg-sky-900/50 text-sky-600 flex items-center justify-center font-bold text-xs flex-shrink-0 group-hover:bg-sky-500 group-hover:text-white transition-colors">
                                            <i class="fas fa-user text-[11px]"></i>
                                        </div>

                                        <div class="overflow-hidden min-w-0 flex-1">
                                            <p
                                                class="user-card-name text-xs font-bold text-gray-800 dark:text-gray-200 truncate">
                                                {{ $usr->nombre_completo }}
                                            </p>
                                            <p class="text-[10px] font-medium text-sky-600 dark:text-sky-400 truncate">
                                                {{ $usr->nombre_rol }}
                                            </p>
                                        </div>

                                        <i
                                            class="fas fa-grip-vertical text-gray-300 dark:text-gray-600 text-xs opacity-0 group-hover:opacity-100 transition-opacity"></i>
                                    </div>
                                @empty
                                    <div class="text-center py-6 text-xs text-gray-400">
                                        No hay usuarios disponibles.
                                    </div>
                                @endforelse
                            </div>

                        </div>
                    </div>

                    {{-- Tabla de Horarios --}}
                    <div class="lg:col-span-9 space-y-4">
                        @php
                            $totalJornadas = count(\App\Models\salud\HorarioConsultorio::BLOQUES);
                        @endphp

                        @foreach (\App\Models\salud\HorarioConsultorio::BLOQUES as $jornada => $bloques)
                            @php
                                $jornadaIndex = $loop->index;
                            @endphp

                            <div id="jornada-block-{{ $jornadaIndex }}"
                                class="jornada-block {{ $jornadaIndex !== 0 ? 'hidden' : '' }}">

                                {{-- Header Unificado de Jornada --}}
                                <div style="background-color: var(--bg-card); border-color: var(--border-color);"
                                    class="p-3 rounded-2xl border shadow-sm mb-4 flex items-center justify-between gap-3">

                                    <div class="flex items-center gap-3">
                                        <div class="w-2.5 h-7 rounded-full bg-sky-500"></div>
                                        <div>
                                            <h3
                                                class="text-xs sm:text-sm font-black uppercase tracking-wider text-gray-800 dark:text-gray-200">
                                                Jornada {{ $jornada }}
                                            </h3>
                                        </div>
                                    </div>

                                    <div
                                        class="flex items-center gap-1.5 bg-gray-50 dark:bg-black/20 p-1 rounded-xl border border-gray-200/60 dark:border-gray-700/60">
                                        <button type="button" onclick="cambiarJornada(-1)" title="Jornada Anterior"
                                            class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-white dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-all shadow-sm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
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
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 5l7 7-7 7" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                {{-- Tabla Grid --}}
                                <div style="border-color: var(--border-color); background-color: var(--bg-card);"
                                    class="rounded-2xl border shadow-sm overflow-x-auto">
                                    <div class="grid min-w-[720px]"
                                        style="grid-template-columns: 110px repeat(5, minmax(0, 1fr));">

                                        {{-- Header --}}
                                        <div class="p-3 border-b border-r bg-gray-50/80 dark:bg-black/30 flex items-center justify-center font-extrabold text-[11px] text-gray-400 uppercase tracking-wider"
                                            style="border-color: var(--border-color);">
                                            <i class="far fa-clock mr-1.5"></i> Bloque
                                        </div>

                                        @foreach (\App\Models\salud\HorarioConsultorio::DIAS as $diaKey => $diaLabel)
                                            <div class="p-3 text-center text-[11px] font-black uppercase tracking-wider text-gray-700 dark:text-gray-200 border-b bg-gray-50/80 dark:bg-black/30 {{ !$loop->last ? 'border-r' : '' }}"
                                                style="border-color: var(--border-color);">
                                                {{ $diaLabel }}
                                            </div>
                                        @endforeach

                                        {{-- Filas --}}
                                        @foreach ($bloques as $bloque)
                                            <div class="p-3 flex items-center justify-center text-center text-[11px] font-extrabold text-gray-700 dark:text-gray-200 whitespace-nowrap border-r bg-gray-50/30 dark:bg-black/10 {{ !$loop->last ? 'border-b' : '' }}"
                                                style="border-color: var(--border-color);">
                                                {{ \Carbon\Carbon::parse($bloque['inicio'])->format('g:i') }} -
                                                {{ \Carbon\Carbon::parse($bloque['fin'])->format('g:i') }}
                                            </div>

                                            @foreach (\App\Models\salud\HorarioConsultorio::DIAS as $diaKey => $diaLabel)
                                                @php
                                                    $cellKey = "{$diaKey}|{$bloque['inicio']}|{$bloque['fin']}";
                                                @endphp
                                                <div class="p-1.5 flex items-center justify-center {{ !$loop->last ? 'border-r' : '' }} {{ !$loop->parent->last ? 'border-b' : '' }}"
                                                    style="border-color: var(--border-color);">

                                                    <div class="drop-zone w-full h-full min-h-[72px] max-h-[160px] overflow-y-auto rounded-xl border border-dashed border-gray-300 dark:border-gray-700/80 bg-gray-50/40 dark:bg-black/10 p-1.5 flex flex-col gap-1 transition-all duration-200 relative group hover:border-sky-400"
                                                        data-key="{{ $cellKey }}"
                                                        data-dia="{{ $diaKey }}"
                                                        data-inicio="{{ $bloque['inicio'] }}"
                                                        data-fin="{{ $bloque['fin'] }}"
                                                        ondragover="handleDragOver(event)"
                                                        ondragleave="handleDragLeave(event)"
                                                        ondrop="handleDrop(event)">

                                                        <div
                                                            class="empty-placeholder flex flex-col items-center justify-center my-auto py-2 text-[10px] font-semibold text-gray-400 dark:text-gray-500 gap-1 pointer-events-none transition-opacity">
                                                            <i class="fas fa-plus-circle text-xs opacity-40"></i>
                                                            <span>Arrastrar aquí</span>
                                                        </div>

                                                        <div class="assigned-list w-full flex flex-col gap-1.5"></div>

                                                    </div>

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

                        {{-- Botón Cancelar / Volver --}}
                        <a href="{{ route('admin.salud.movimientos.horarios.index', ['consultorio_id' => $consultorioSeleccionado]) }}"
                            title="Cancelar y Volver"
                            class="rd-submit-btn flex items-center gap-2 px-6 py-3 rounded-full bg-gray-100 hover:bg-gray-200/80 text-gray-700 dark:bg-[#1b1e3d] dark:hover:bg-[#252a54] dark:text-[#a5b4fc] font-bold text-xs transition-all active:scale-95 border border-gray-300/70 dark:border-[#2d335c]">
                            <i class="fas fa-arrow-left text-[12px]"></i>
                            <span>Cancelar</span>
                        </a>

                        {{-- Separador Vertical --}}
                        <div class="h-5 w-[1px] bg-gray-200 dark:bg-[#222744] mx-1"></div>

                        {{-- Botón Circular con Tilde --}}
                        <button type="submit" title="Confirmar y Guardar"
                            class="w-12 h-12 rounded-full bg-emerald-500 hover:bg-emerald-600 text-white dark:bg-[#0a2720] dark:hover:bg-[#0f3d32] border border-emerald-600 dark:border-[#135343] dark:text-[#34d399] flex items-center justify-center transition-all active:scale-90 shadow-md">
                            <i class="fas fa-check text-[16px]"></i>
                        </button>
                    </div>
                </div>
        </div>
        </form>

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

        // --- DRAG AND DROP ---
        function handleDragStart(e) {
            const id = e.currentTarget.getAttribute('data-id');
            const nombre = e.currentTarget.getAttribute('data-nombre');
            const rol = e.currentTarget.getAttribute('data-rol');
            e.dataTransfer.setData('text/plain', JSON.stringify({
                id,
                nombre,
                rol
            }));
            e.dataTransfer.effectAllowed = 'copy';
        }

        function handleDragOver(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'copy';
            e.currentTarget.classList.add('border-sky-500', 'bg-sky-50/50', 'dark:bg-sky-950/40');
        }

        function handleDragLeave(e) {
            e.currentTarget.classList.remove('border-sky-500', 'bg-sky-50/50', 'dark:bg-sky-950/40');
        }

        function handleDrop(e) {
            e.preventDefault();
            const zone = e.currentTarget;
            zone.classList.remove('border-sky-500', 'bg-sky-50/50', 'dark:bg-sky-950/40');

            try {
                const data = JSON.parse(e.dataTransfer.getData('text/plain'));
                if (data && data.id) {
                    asignarUsuarioABloque(zone, data.id, data.nombre, data.rol);
                }
            } catch (err) {
                console.error("Error procesando soltado:", err);
            }
        }

        function asignarUsuarioABloque(zone, userId, userName, userRol) {
            const key = zone.getAttribute('data-key');
            const listContainer = zone.querySelector('.assigned-list');
            const placeholder = zone.querySelector('.empty-placeholder');

            if (!listContainer) return;

            // Evitar duplicados en la misma casilla
            if (listContainer.querySelector(`[data-user-id="${userId}"]`)) {
                return;
            }

            if (placeholder) {
                placeholder.classList.add('hidden');
            }

            const rolTexto = userRol || '';

            const pill = document.createElement('div');
            pill.className =
                "user-pill flex items-center justify-between w-full px-2 py-1 rounded-lg bg-sky-300/20 dark:bg-sky-800/30 border border-sky-200 dark:border-sky-800 text-[12px] shadow-sm";
            pill.setAttribute('data-user-id', userId);
            pill.innerHTML = `
                <div class="flex items-center gap-1 min-w-0 pr-1">
                    <i class="fas fa-user-md text-sky-600 dark:text-sky-400 text-[10px] flex-shrink-0"></i>
                    <div class="min-w-0 leading-tight">
                        <span class="block font-bold text-sky-900 dark:text-sky-200 truncate" title="${userName}">${userName}</span>
                        <span class="block text-[9px] font-medium text-sky-500 dark:text-sky-400 truncate">${rolTexto}</span>
                    </div>
                </div>
                <button type="button" onclick="quitarUsuarioPill(this)" class="text-rose-500 hover:text-rose-700 dark:hover:text-rose-400 hover:bg-rose-200/30 dark:hover:bg-rose-700/50 rounded flex-shrink-0 p-0.5">
                    <i class="fas fa-times"></i>
                </button>
                <input type="hidden" class="input-horario" name="horarios[]" value="${key}|${userId}">
            `;

            listContainer.appendChild(pill);
            actualizarContador();
        }

        function quitarUsuarioPill(btn) {
            const pill = btn.closest('.user-pill');
            const zone = btn.closest('.drop-zone');
            const listContainer = zone.querySelector('.assigned-list');
            const placeholder = zone.querySelector('.empty-placeholder');

            pill.remove();

            if (listContainer.children.length === 0 && placeholder) {
                placeholder.classList.remove('hidden');
            }

            actualizarContador();
        }

        function actualizarContador() {
            const asignados = document.querySelectorAll('.input-horario').length;
            const contador = document.getElementById('contadorSeleccion');
            if (contador) {
                contador.textContent = asignados;
            }
        }

        // --- BUSCADOR REAL-TIME ---
        document.getElementById('searchUser').addEventListener('input', function(e) {
            const query = e.target.value.toLowerCase().trim();
            document.querySelectorAll('.user-card').forEach(function(card) {
                const nombre = card.querySelector('.user-card-name').textContent.toLowerCase();
                if (nombre.includes(query)) {
                    card.classList.remove('hidden');
                } else {
                    card.classList.add('hidden');
                }
            });
        });

        // --- BOTÓN LIMPIAR TODO ---
        document.getElementById('btnLimpiarSeleccion').addEventListener('click', function() {
            document.querySelectorAll('.drop-zone').forEach(function(zone) {
                const listContainer = zone.querySelector('.assigned-list');
                const placeholder = zone.querySelector('.empty-placeholder');
                if (listContainer) listContainer.innerHTML = '';
                if (placeholder) placeholder.classList.remove('hidden');
            });
            actualizarContador();
        });

        // --- RECARGAR AL CAMBIAR CONSULTORIO ---
        document.getElementById('consultorio_id').addEventListener('change', function() {
            const consultorioId = this.value;
            if (consultorioId) {
                window.location.href = "{{ route('admin.salud.movimientos.horarios.create') }}?consultorio_id=" +
                    consultorioId;
            }
        });

        // --- RESTAURAR DATOS GUARDADOS EN CARGA INICIAL ---
        const horariosActuales = @json($horariosActuales ?? []);

        document.addEventListener('DOMContentLoaded', function() {
            if (horariosActuales && horariosActuales.length > 0) {
                horariosActuales.forEach(item => {
                    const zone = document.querySelector(`.drop-zone[data-key="${item.clave_simple}"]`);
                    if (zone) {
                        asignarUsuarioABloque(zone, item.id_rol_usuario, item.nombre_usuario, item
                            .rol_usuario);
                    }
                });
            }
        });
    </script>
</x-app-layout>
