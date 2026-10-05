<div class="space-y-5" x-data="{
    finalizarModal: false,
    abrirModal() { this.finalizarModal = true },
    cerrarModal() { this.finalizarModal = false }
}" @open-modal.window="abrirModal()"
    @finalizar-dia-guardado.window="cerrarModal()">

    @include('components.alert')

    <div class="grid grid-cols-1 lg:grid-cols-[minmax(280px,340px)_minmax(0,1fr)] gap-5 items-start">

        <div class="rounded-2xl border shadow-sm overflow-hidden lg:sticky lg:top-20"
            style="background-color: var(--bg-card); border-color: var(--border-color);">

            <div class="px-5 py-4 border-b" style="border-color: var(--border-color);">
                <h2 class="text-base font-extrabold flex items-center gap-2" style="color: var(--text-main);">
                    <i class="fas fa-qrcode text-red-700 dark:text-red-500"></i>
                    Registro diario
                </h2>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                    Escanea el carnet del estudiante para registrar su entrada al comedor.
                </p>
            </div>

            @if ($horarioPermitido && $tipoComidaLabel)
                <div class="px-5 py-3 border-b flex items-center justify-between gap-3"
                    style="border-color: var(--border-color); background-color: rgba(16,185,129,0.05);">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <span class="w-9 h-9 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                            @if ($tipoComidaActual === 'desayuno')
                                <i class="fas fa-mug-saucer text-sm"></i>
                            @elseif ($tipoComidaActual === 'almuerzo')
                                <i class="fas fa-utensils text-sm"></i>
                            @else
                                <i class="fas fa-moon text-sm"></i>
                            @endif
                        </span>
                        <div class="min-w-0">
                            <p class="text-[10px] font-black uppercase tracking-wider text-emerald-700 dark:text-emerald-400">
                                Turno activo
                            </p>
                            <p class="text-sm font-extrabold truncate" style="color: var(--text-main);">
                                {{ $tipoComidaLabel }} · {{ $ventanaActual }}
                            </p>
                        </div>
                    </div>
                    <div class="text-right shrink-0">
                        <p class="text-[10px] font-black uppercase tracking-wider text-gray-400 dark:text-gray-500">
                            Disponibles
                        </p>
                        <p class="text-sm font-extrabold" style="color: var(--text-main);">
                            {{ $racionesDisponibles }} / {{ $racionesServidas }}
                        </p>
                    </div>
                </div>
            @endif

            <div class="p-5">

                <form wire:submit.prevent="save" autocomplete="off">
                    @csrf

                    <label for="cedula"
                        class="block text-[11px] font-black uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-2">
                        Cédula del estudiante
                    </label>

                    <div class="flex items-stretch gap-2">
                        <div class="flex-1 flex items-stretch rounded-xl border overflow-hidden transition-all focus-within:ring-2 focus-within:ring-red-500/30 focus-within:border-red-500 @error('cedula') border-rose-400 @enderror"
                            style="border-color: var(--border-color);">
                            <span class="flex items-center justify-center px-3.5 border-r text-gray-400"
                                style="background-color: rgba(0,0,0,0.03); border-color: var(--border-color);">
                                <i class="fas fa-id-card text-sm"></i>
                            </span>
                            <input type="tel" id="cedula" wire:model="cedula" @disabled(!$enableInput)
                                placeholder="Ej: 12345678" maxlength="8" inputmode="numeric" autocomplete="off"
                                class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none disabled:opacity-60 disabled:cursor-not-allowed"
                                style="background-color: rgba(0,0,0,0.02); color: var(--text-main);">
                        </div>

                        <button type="submit" @disabled(!$enableInput)
                            class="inline-flex items-center justify-center rounded-xl bg-red-800 hover:bg-red-900 text-white text-sm font-extrabold px-4 shadow-md transition-all active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed shrink-0"
                            title="Buscar y registrar">
                            <i class="fas fa-check"></i>
                        </button>
                    </div>

                    <p
                        class="mt-2 text-[11px] text-gray-400 dark:text-gray-500 flex items-start gap-1.5 leading-relaxed">
                        <i class="fas fa-info-circle mt-0.5 opacity-60"></i>
                        <span>Solo números, máximo 8 dígitos. El campo mantiene el foco para lectores QR.</span>
                    </p>

                    @error('cedula')
                        <div
                            class="mt-3 p-2.5 rounded-lg border border-rose-200 dark:border-rose-800/60 bg-rose-50 dark:bg-rose-950/30 flex items-start gap-2">
                            <i class="fas fa-exclamation-circle text-rose-600 dark:text-rose-400 mt-0.5 text-xs"></i>
                            <p class="text-xs font-semibold text-rose-700 dark:text-rose-300">{{ $message }}</p>
                        </div>
                    @enderror
                </form>

                @if ($limiteAlcanzado)
                    <div
                        class="mt-4 p-3 rounded-xl border border-amber-200 dark:border-amber-800/60 bg-amber-50 dark:bg-amber-950/30 flex items-start gap-2">
                        <i class="fas fa-triangle-exclamation text-amber-600 dark:text-amber-400 mt-0.5 text-sm"></i>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-black uppercase tracking-wider text-amber-800 dark:text-amber-300">
                                Límite alcanzado
                            </p>
                            <p class="mt-1 text-xs text-amber-700 dark:text-amber-400 leading-relaxed">
                                {{ $limiteAlcanzado }}
                            </p>
                        </div>
                        <button type="button" wire:click="$set('limiteAlcanzado', null)"
                            class="w-6 h-6 rounded flex items-center justify-center text-amber-500 hover:text-amber-700 hover:bg-amber-100 dark:hover:bg-amber-900/40 transition-colors shrink-0"
                            title="Descartar">
                            <i class="fas fa-times text-[10px]"></i>
                        </button>
                    </div>
                @endif

                @if (!$horarioPermitido)
                    <div
                        class="mt-4 p-3 rounded-xl border border-amber-200 dark:border-amber-800/60 bg-amber-50 dark:bg-amber-950/30 flex items-start gap-2">
                        <i class="fas fa-clock text-amber-600 dark:text-amber-400 mt-0.5 text-sm"></i>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-black uppercase tracking-wider text-amber-800 dark:text-amber-300">
                                Fuera del horario de servicio
                            </p>
                            <p class="mt-1 text-xs text-amber-700 dark:text-amber-400 leading-relaxed">
                                {{ $mensajeHorario }}
                            </p>

                            @if ($proximoHorario)
                                <div class="mt-3 pt-3 border-t border-amber-200/60 dark:border-amber-800/60">
                                    <p class="text-[10px] font-black uppercase tracking-wider text-amber-800 dark:text-amber-300">
                                        Próxima comida
                                    </p>
                                    <p class="mt-1 text-xs text-amber-700 dark:text-amber-400">
                                        <strong>{{ $proximoHorario['label'] }}</strong>
                                        a las {{ $proximoHorario['hora'] }}
                                        @if (!empty($proximoHorario['manana']))
                                            (mañana)
                                        @endif
                                    </p>
                                </div>
                            @endif

                            <div class="mt-3 pt-3 border-t border-amber-200/60 dark:border-amber-800/60 space-y-1.5">
                                <p class="text-[10px] font-black uppercase tracking-wider text-amber-800 dark:text-amber-300 mb-1.5">
                                    Horarios de servicio
                                </p>
                                <div class="flex items-center gap-2 text-xs text-amber-700 dark:text-amber-400">
                                    <i class="fas fa-mug-saucer text-[10px] w-3"></i>
                                    <span><strong>Desayuno:</strong> 9:00 – 10:00</span>
                                </div>
                                <div class="flex items-center gap-2 text-xs text-amber-700 dark:text-amber-400">
                                    <i class="fas fa-utensils text-[10px] w-3"></i>
                                    <span><strong>Almuerzo:</strong> 12:00 – 14:00</span>
                                </div>
                                <div class="flex items-center gap-2 text-xs text-amber-700 dark:text-amber-400">
                                    <i class="fas fa-moon text-[10px] w-3"></i>
                                    <span><strong>Cena:</strong> 18:00 – 20:00</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @elseif (!$receta_diario)
                    <div
                        class="mt-4 p-3 rounded-xl border border-amber-200 dark:border-amber-800/60 bg-amber-50 dark:bg-amber-950/30 flex items-start gap-2">
                        <i class="fas fa-utensils text-amber-600 dark:text-amber-400 mt-0.5 text-sm"></i>
                        <div>
                            <p class="text-xs font-black uppercase tracking-wider text-amber-800 dark:text-amber-300">
                                Sin recetas registradas
                            </p>
                            <p class="mt-1 text-xs text-amber-700 dark:text-amber-400 leading-relaxed">
                                Debes registrar las recetas de <strong>{{ $tipoComidaLabel }}</strong> antes de poder pasar lista de estudiantes.
                            </p>
                        </div>
                    </div>
                @elseif (!$enableInput && $racionesDisponibles <= 0)
                    <div
                        class="mt-4 p-3 rounded-xl border border-rose-200 dark:border-rose-800/60 bg-rose-50 dark:bg-rose-950/30 flex items-start gap-2">
                        <i class="fas fa-lock text-rose-600 dark:text-rose-400 mt-0.5 text-sm"></i>
                        <div>
                            <p class="text-xs font-black uppercase tracking-wider text-rose-800 dark:text-rose-300">
                                Raciones agotadas
                            </p>
                            <p class="mt-1 text-xs text-rose-700 dark:text-rose-400 leading-relaxed">
                                Ya se sirvieron todas las raciones de {{ $tipoComidaLabel }} para hoy.
                            </p>
                        </div>
                    </div>
                @endif

                @if ($showNotification && isset($notification['message']))
                    @php
                        $type = $notification['type'] ?? 'info';
                        $palette = [
                            'success' => [
                                'border' => 'border-emerald-200 dark:border-emerald-800/60',
                                'bg' => 'bg-emerald-50 dark:bg-emerald-950/30',
                                'icon' => 'fa-circle-check text-emerald-600 dark:text-emerald-400',
                                'label' => 'text-emerald-800 dark:text-emerald-300',
                                'text' => 'text-emerald-700 dark:text-emerald-400',
                            ],
                            'danger' => [
                                'border' => 'border-rose-200 dark:border-rose-800/60',
                                'bg' => 'bg-rose-50 dark:bg-rose-950/30',
                                'icon' => 'fa-circle-xmark text-rose-600 dark:text-rose-400',
                                'label' => 'text-rose-800 dark:text-rose-300',
                                'text' => 'text-rose-700 dark:text-rose-400',
                            ],
                        ][$type] ?? [
                            'border' => 'border-sky-200 dark:border-sky-800/60',
                            'bg' => 'bg-sky-50 dark:bg-sky-950/30',
                            'icon' => 'fa-circle-info text-sky-600 dark:text-sky-400',
                            'label' => 'text-sky-800 dark:text-sky-300',
                            'text' => 'text-sky-700 dark:text-sky-400',
                        ];
                    @endphp

                    <div wire:key="notif-{{ md5($notification['message'] . microtime()) }}" x-data="{ visible: true }"
                        x-init="setTimeout(() => { visible = false;
                            $wire.set('showNotification', false); }, 4500)" x-show="visible" x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        class="mt-4 p-3 rounded-xl border {{ $palette['border'] }} {{ $palette['bg'] }} flex items-start gap-2">
                        <i class="fas {{ $palette['icon'] }} mt-0.5 text-sm"></i>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-black uppercase tracking-wider {{ $palette['label'] }}">
                                {{ $type === 'success' ? 'Registro exitoso' : 'Aviso' }}
                            </p>
                            <p class="mt-1 text-xs {{ $palette['text'] }} leading-relaxed break-words">
                                {{ $notification['message'] }}
                            </p>
                        </div>
                        <button type="button" @click="visible = false; $wire.set('showNotification', false)"
                            class="w-6 h-6 rounded flex items-center justify-center opacity-60 hover:opacity-100 transition-opacity shrink-0">
                            <i class="fas fa-times text-[10px]"></i>
                        </button>
                    </div>
                @endif
            </div>

            <div class="px-5 py-3 border-t"
                style="border-color: var(--border-color); background-color: rgba(0,0,0,0.015);">
                <p class="text-[11px] text-gray-400 dark:text-gray-500 flex items-center gap-1.5">
                    <i class="fas fa-shield-alt opacity-60"></i>
                    Mantén tu carnet a mano y verifica los datos antes de confirmar.
                </p>
            </div>
        </div>

        <div class="rounded-2xl border shadow-sm overflow-hidden"
            style="background-color: var(--bg-card); border-color: var(--border-color);">

            <div class="p-5 sm:p-6 border-b" style="border-color: var(--border-color);">

                <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">

                    <div class="shrink-0">
                        <h3 class="text-lg font-extrabold flex items-center gap-2" style="color: var(--text-main);">
                            <i class="fas fa-list-check text-red-700 dark:text-red-500"></i>
                            Registros del día
                        </h3>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Historial de ingresos al comedor.
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2 shrink-0">

                        <div class="relative flex-1 min-w-[200px] sm:w-64">
                            <i
                                class="fas fa-search absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400 pointer-events-none text-sm"></i>
                            <input type="text" name="buscar" value="{{ $buscar }}"
                                wire:model.live.debounce.400ms="buscar" placeholder="Nombre, apellido o PNF…"
                                style="background-color: var(--input-bg); border-color: var(--border-color); color: var(--text-main);"
                                class="w-full pl-10 pr-4 py-2.5 rounded-xl border text-sm font-medium focus:outline-none focus:ring-2 focus:ring-red-500/30 focus:border-red-500 transition-all placeholder-gray-400">
                        </div>

                        <button type="button" x-data="{ open: false }"
                            @click="open = !open; document.getElementById('filtros-diario').classList.toggle('hidden')"
                            class="inline-flex items-center justify-center gap-2 rounded-xl border px-3 py-2.5 text-sm font-bold transition-all hover:bg-gray-50 dark:hover:bg-white/5 shrink-0"
                            style="border-color: var(--border-color); color: var(--text-main);"
                            title="Filtros por fecha">
                            <i class="fas fa-filter text-xs"></i>
                            <span class="hidden sm:inline">Fechas</span>
                        </button>

                        <a href="{{ route('admin.movimientos.registro_diario.export_excel', request()->only(['buscar', 'fecha_desde', 'fecha_hasta'])) }}"
                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-emerald-300 dark:border-emerald-800/60 bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 px-4 py-2.5 text-sm font-extrabold shadow-sm transition-all hover:bg-emerald-100 dark:hover:bg-emerald-950/50 active:scale-[0.98] shrink-0"
                            target="_blank" rel="noopener" title="Exportar a Excel">
                            <i class="fas fa-file-excel text-xs"></i>
                            <span class="hidden sm:inline">Excel</span>
                        </a>

                        <a href="{{ route('admin.movimientos.registro_diario.export_pdf', request()->only(['buscar', 'fecha_desde', 'fecha_hasta'])) }}"
                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-rose-300 dark:border-rose-800/60 bg-rose-50 dark:bg-rose-950/30 text-rose-700 dark:text-rose-400 px-4 py-2.5 text-sm font-extrabold shadow-sm transition-all hover:bg-rose-100 dark:hover:bg-rose-950/50 active:scale-[0.98] shrink-0"
                            target="_blank" rel="noopener" title="Exportar a PDF">
                            <i class="fas fa-file-pdf text-xs"></i>
                            <span class="hidden sm:inline">PDF</span>
                        </a>

                        @if ($showBtnFinalizar)
                            <button type="button" @click="abrirModal()"
                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-800 hover:bg-red-900 text-white text-sm font-extrabold px-4 py-2.5 shadow-md transition-all active:scale-[0.98] shrink-0"
                                title="Finalizar la jornada del día">
                                <i class="fas fa-sun text-xs"></i>
                                <span class="hidden sm:inline">Finalizar día</span>
                            </button>
                        @endif
                    </div>
                </div>

                <div id="filtros-diario" class="hidden mt-4">
                    <div class="rounded-xl border p-4"
                        style="border-color: var(--border-color); background-color: rgba(0,0,0,0.015);">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
                            <div>
                                <label
                                    class="block text-[11px] font-black uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1.5">
                                    Desde
                                </label>
                                <input type="date" wire:model.live="fecha_desde" max="{{ date('Y-m-d') }}"
                                    style="background-color: var(--input-bg); border-color: var(--border-color); color: var(--text-main);"
                                    class="w-full px-3 py-2.5 rounded-xl border text-sm font-medium focus:outline-none focus:ring-2 focus:ring-red-500/30 focus:border-red-500 transition-all">
                            </div>
                            <div>
                                <label
                                    class="block text-[11px] font-black uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1.5">
                                    Hasta
                                </label>
                                <input type="date" wire:model.live="fecha_hasta" max="{{ date('Y-m-d') }}"
                                    style="background-color: var(--input-bg); border-color: var(--border-color); color: var(--text-main);"
                                    class="w-full px-3 py-2.5 rounded-xl border text-sm font-medium focus:outline-none focus:ring-2 focus:ring-red-500/30 focus:border-red-500 transition-all">
                            </div>
                            <div class="flex gap-2">
                                <button type="button"
                                    wire:click="$set('fecha_desde', '{{ date('Y-m-d') }}'); $set('fecha_hasta', '{{ date('Y-m-d') }}'); $set('buscar', '')"
                                    class="flex-1 inline-flex items-center justify-center gap-2 rounded-xl border px-3 py-2.5 text-sm font-bold transition-all hover:bg-gray-50 dark:hover:bg-white/5"
                                    style="border-color: var(--border-color); color: var(--text-main);">
                                    <i class="fas fa-rotate-left text-xs"></i>
                                    Limpiar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b text-[11px] font-black uppercase tracking-wider"
                            style="border-color: var(--border-color); background-color: rgba(0,0,0,0.015);">
                            <th class="px-6 py-4 text-center" style="width:80px; color: var(--text-main);">#</th>
                            <th class="px-6 py-4 text-left" style="color: var(--text-main);">Estudiante</th>
                            <th class="px-6 py-4 text-left" style="color: var(--text-main);">PNF</th>
                            <th class="px-6 py-4 text-center" style="width:140px; color: var(--text-main);">Registrado
                            </th>
                            <th class="px-6 py-4 text-center" style="width:130px; color: var(--text-main);">Estado
                            </th>
                            <th class="px-6 py-4 text-center" style="width:100px; color: var(--text-main);">Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody class="text-sm font-medium">
                        @forelse ($data as $registro)
                            <x-table-row :id="$registro->id" class="border-b"
                                style="border-color: var(--border-color);">

                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center justify-center w-8 h-8 text-xs font-black rounded-lg border"
                                        style="border-color: var(--border-color); color: var(--text-main);">
                                        {{ ($data->currentPage() - 1) * $data->perPage() + $loop->iteration }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap" style="color: var(--text-main);">
                                    <div class="flex items-center gap-2.5">
                                        <span
                                            class="w-8 h-8 rounded-lg bg-red-50 dark:bg-red-950/30 text-red-700 dark:text-red-500 flex items-center justify-center shrink-0">
                                            <i class="fas fa-user text-xs"></i>
                                        </span>
                                        <div class="min-w-0">
                                            <p class="font-bold truncate">{{ $registro->nombre_persona }}</p>
                                            <p class="text-[11px] text-gray-400 dark:text-gray-500 truncate">
                                                {{ $registro->apellido_persona }}</p>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-gray-500 dark:text-gray-400">
                                    {{ $registro->nombre_pnf ?? '—' }}
                                </td>

                                <td class="px-6 py-4 text-center whitespace-nowrap text-gray-500 dark:text-gray-400">
                                    <i class="far fa-calendar mr-1.5 opacity-60"></i>
                                    {{ \Carbon\Carbon::parse($registro->fecha_regis_diario_c)->format('d/m/Y') }}
                                </td>

                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center gap-1.5 px-3 py-1 text-[10px] font-black rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900">
                                        <i class="fas fa-check-circle"></i>
                                        Aprobado
                                    </span>
                                </td>

                                <x-table-actions :id="$registro->id" :show="false" :edit="false"
                                    :toggle="false">
                                    <a href="{{ route('admin.movimientos.registro_diario.show', $registro->id) }}"
                                        onclick="event.stopPropagation()"
                                        class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-sky-600 hover:bg-sky-50 dark:hover:bg-sky-950/50 transition-colors"
                                        title="Ver detalle">
                                        <i class="fas fa-eye text-xs"></i>
                                    </a>
                                </x-table-actions>
                            </x-table-row>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center gap-3">
                                        <div
                                            class="w-16 h-16 rounded-full bg-gray-50 dark:bg-gray-800/60 flex items-center justify-center">
                                            <i
                                                class="fas fa-clipboard-list text-2xl text-gray-300 dark:text-gray-600"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold" style="color: var(--text-main);">
                                                Sin registros
                                            </p>
                                            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                                                @if ($buscar)
                                                    No se encontraron registros para «{{ $buscar }}».
                                                @else
                                                    Aún no se ha registrado ningún ingreso en este período.
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($data->hasPages())
                <div class="px-6 py-4 border-t flex justify-center"
                    style="border-color: var(--border-color); background-color: rgba(0,0,0,0.015);">
                    {{ $data->onEachSide(1)->links('components.pagination-livewire') }}
                </div>
            @endif
        </div>
    </div>

    <div x-show="finalizarModal" x-cloak x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 flex items-center justify-center p-4"
        style="background-color: rgba(0,0,0,0.65); backdrop-filter: blur(4px);" @click.self="cerrarModal()"
        @keydown.escape.window="cerrarModal()">

        <div class="w-full max-w-lg rounded-2xl border shadow-2xl overflow-hidden"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            style="background-color: var(--bg-card); border-color: var(--border-color);">

            <div class="px-5 py-4 border-b flex items-center justify-between"
                style="border-color: var(--border-color); background-color: rgba(0,0,0,0.015);">
                <div>
                    <h3 class="text-base font-extrabold flex items-center gap-2" style="color: var(--text-main);">
                        <i class="fas fa-file-signature text-red-700 dark:text-red-500"></i>
                        Reporte de cierre de jornada
                    </h3>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Registra el sobrante del día y la acción tomada.
                    </p>
                </div>
                <button type="button" @click="cerrarModal()"
                    class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950/30 transition-colors shrink-0"
                    title="Cerrar">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>

            <form wire:submit.prevent="finalizarDia">
                <div class="p-5 space-y-4">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                        <div>
                            <label
                                class="block text-[11px] font-black uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-2">
                                Fecha de cierre
                            </label>
                            <div class="flex items-stretch rounded-xl border overflow-hidden opacity-75"
                                style="border-color: var(--border-color);">
                                <span class="flex items-center justify-center px-3.5 border-r text-gray-400"
                                    style="background-color: rgba(0,0,0,0.03); border-color: var(--border-color);">
                                    <i class="fas fa-calendar-day text-sm"></i>
                                </span>
                                <input wire:model="fecha" type="date" readonly
                                    style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                    class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none cursor-not-allowed">
                            </div>
                            @error('fecha')
                                <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label
                                class="block text-[11px] font-black uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-2">
                                Raciones sobrantes
                            </label>
                            <div class="flex items-stretch rounded-xl border overflow-hidden opacity-75"
                                style="border-color: var(--border-color);">
                                <span class="flex items-center justify-center px-3.5 border-r text-gray-400"
                                    style="background-color: rgba(0,0,0,0.03); border-color: var(--border-color);">
                                    <i class="fas fa-utensils text-sm"></i>
                                </span>
                                <input wire:model="sobrante" type="number" readonly min="0"
                                    style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                    class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none cursor-not-allowed">
                            </div>
                            @error('sobrante')
                                <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label
                            class="block text-[11px] font-black uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-2">
                            Motivo del cierre
                        </label>
                        <div class="flex items-stretch rounded-xl border overflow-hidden transition-all focus-within:ring-2 focus-within:ring-red-500/30 focus-within:border-red-500 @error('motivo') border-rose-400 @enderror"
                            style="border-color: var(--border-color);">
                            <span class="flex items-center justify-center px-3.5 border-r text-gray-400"
                                style="background-color: rgba(0,0,0,0.03); border-color: var(--border-color);">
                                <i class="fas fa-clipboard-list text-sm"></i>
                            </span>
                            <select wire:model="motivo"
                                style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none">
                                <option value="">— Seleccione el motivo —</option>
                                <option value="Baja personal">Baja asistencia del personal operativo</option>
                                <option value="Suspension actividades">Suspensión de actividades académicas (paros,
                                    asambleas, elecciones)</option>
                                <option value="Horario reducido">Reducción de jornada académica</option>
                                <option value="Baja asistencia estudiantil">Baja asistencia estudiantil no prevista
                                </option>
                                <option value="Cambio horario estudiantes">Cambio inesperado en horarios académicos
                                </option>
                                <option value="Sobreestimacion demanda">Sobreestimación de la demanda diaria</option>
                                <option value="Entrega tardia">Entrega tardía de alimentos preparados</option>
                                <option value="Emergencia">Emergencia o contingencia (climática, sanitaria, seguridad)
                                </option>
                                <option value="Otro">Otro (especificar en observaciones)</option>
                            </select>
                        </div>
                        @error('motivo')
                            <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label
                            class="block text-[11px] font-black uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-2">
                            Acción tomada con el sobrante
                        </label>
                        <div class="flex items-stretch rounded-xl border overflow-hidden transition-all focus-within:ring-2 focus-within:ring-red-500/30 focus-within:border-red-500 @error('accion') border-rose-400 @enderror"
                            style="border-color: var(--border-color);">
                            <span class="flex items-center justify-center px-3.5 border-r text-gray-400"
                                style="background-color: rgba(0,0,0,0.03); border-color: var(--border-color);">
                                <i class="fas fa-hand-holding-heart text-sm"></i>
                            </span>
                            <input wire:model="accion" type="text"
                                placeholder="Ej: Donación, refrigeración, descarte…"
                                style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none">
                        </div>
                        @error('accion')
                            <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="px-5 py-4 border-t flex items-center justify-end gap-3"
                    style="border-color: var(--border-color); background-color: rgba(0,0,0,0.015);">
                    <button type="button" @click="cerrarModal()"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border text-sm font-bold hover:bg-gray-50 dark:hover:bg-white/5 transition-all"
                        style="border-color: var(--border-color); color: var(--text-main);">
                        <i class="fas fa-times text-xs"></i>
                        Cancelar
                    </button>
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-red-800 hover:bg-red-900 text-white text-sm font-extrabold shadow-md active:scale-[0.98] transition-all">
                        <i class="fas fa-save text-xs"></i>
                        Guardar y finalizar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('livewire:init', () => {

            Livewire.on('swal', (payload) => {
                const data = Array.isArray(payload) ? payload[0] : payload;
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: data.title,
                        text: data.text,
                        icon: data.icon,
                        confirmButtonColor: '#991b1b',
                        confirmButtonText: 'Aceptar',
                        timer: 5000,
                        timerProgressBar: true,
                    });
                } else {
                    alert((data.title || '') + '\n\n' + (data.text || ''));
                }
            });

            Livewire.on('notify-limite', (payload) => {
                const data = Array.isArray(payload) ? payload[0] : payload;
                const mensaje = data?.message || 'Se alcanzó el límite de raciones.';
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Límite alcanzado',
                        text: mensaje,
                        confirmButtonColor: '#991b1b',
                        confirmButtonText: 'Entendido',
                    });
                }
            });

            Livewire.on('finalizar-dia-guardado', (payload) => {
                const data = Array.isArray(payload) ? payload[0] : payload;
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: data.icon || 'success',
                        title: data.title || '¡Listo!',
                        text: data.text || '',
                        confirmButtonColor: '#991b1b',
                        timer: 3500,
                        timerProgressBar: true,
                    });
                } else {
                    alert((data.title || '') + '\n\n' + (data.text || ''));
                }
            });
        });

        (function() {
            function isTypingInAnotherField() {
                const a = document.activeElement;
                if (!a) return false;
                const tag = a.tagName;
                if (tag !== 'INPUT' && tag !== 'TEXTAREA' && tag !== 'SELECT') return false;
                return a.id !== 'cedula';
            }

            function isModalOpen() {
                const modal = document.querySelector('[x-show="finalizarModal"]');
                if (!modal) return false;
                return modal.getAttribute('style')?.includes('display: block') === true ||
                    (modal.style.display !== 'none' && !modal.style.display);
            }

            function focusCedula() {
                const cedula = document.getElementById('cedula');
                if (!cedula || cedula.disabled) return;
                if (isTypingInAnotherField()) return;
                if (isModalOpen()) return;
                cedula.focus({
                    preventScroll: true
                });
            }

            document.addEventListener('DOMContentLoaded', () => setTimeout(focusCedula, 150));
            document.addEventListener('livewire:navigated', () => setTimeout(focusCedula, 150));

            document.addEventListener('click', (e) => {
                const t = e.target;
                if (!t) return;
                if (t.closest('input, textarea, select, button, a, label, [contenteditable="true"]')) return;
                focusCedula();
            });

            window.addEventListener('focus', () => focusCedula());

            document.addEventListener('input', (e) => {
                if (e.target && e.target.id === 'cedula') {
                    let v = e.target.value.replace(/[^0-9]/g, '');
                    if (v.length > 8) v = v.slice(0, 8);
                    if (v !== e.target.value) e.target.value = v;
                }
            });
        })();
    </script>
@endpush