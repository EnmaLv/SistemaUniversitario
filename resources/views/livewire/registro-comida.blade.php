<div>
    @include('components.alert')

    <div class="space-y-6">

        <div style="background-color: var(--bg-card); border-color: var(--border-color);"
            class="rounded-2xl border shadow-sm overflow-hidden">

            <div class="px-6 sm:px-8 py-5 border-b" style="border-color: var(--border-color);">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-extrabold flex items-center gap-2" style="color: var(--text-main);">
                            <i class="fas fa-utensils text-red-700 dark:text-red-500"></i>
                            Registro de comidas
                        </h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Registra las recetas servidas en cada comida del día.
                        </p>
                    </div>

                    @if ($horarioPermitido && $tipoComidaLabel)
                        <span class="inline-flex items-center gap-2 rounded-xl border border-emerald-200 dark:border-emerald-800/60 bg-emerald-50 dark:bg-emerald-950/30 px-3 py-2 text-xs font-black text-emerald-700 dark:text-emerald-400 shrink-0">
                            <i class="fas fa-circle-check"></i>
                            Turno activo: {{ $tipoComidaLabel }} · {{ $ventanaActual }}
                        </span>
                    @else
                        <span class="inline-flex items-center gap-2 rounded-xl border px-3 py-2 text-xs font-black shrink-0"
                            style="border-color: var(--border-color); color: var(--text-main);">
                            <i class="far fa-clock opacity-60"></i>
                            {{ now()->format('d/m/Y · h:i A') }}
                        </span>
                    @endif
                </div>
            </div>

            <div class="p-6 sm:p-8">

                @if (!$horarioPermitido)
                    <div class="mb-6 p-4 rounded-xl border border-amber-200 dark:border-amber-800/60 bg-amber-50 dark:bg-amber-950/30 flex items-start gap-3">
                        <i class="fas fa-clock text-amber-600 dark:text-amber-400 mt-0.5"></i>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-black text-amber-800 dark:text-amber-300">
                                Fuera del horario de servicio
                            </p>
                            <p class="mt-1 text-xs text-amber-700 dark:text-amber-400 leading-relaxed">
                                {{ $mensajeHorario }}
                            </p>

                            @if ($proximoHorario)
                                <div class="mt-3 pt-3 border-t border-amber-200/60 dark:border-amber-800/60">
                                    <p class="text-[11px] font-black uppercase tracking-wider text-amber-800 dark:text-amber-300">
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

                            <div class="mt-3 pt-3 border-t border-amber-200/60 dark:border-amber-800/60">
                                <p class="text-[11px] font-black uppercase tracking-wider text-amber-800 dark:text-amber-300 mb-2">
                                    Horarios de servicio
                                </p>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                    <div class="flex items-center gap-2 text-xs text-amber-700 dark:text-amber-400">
                                        <i class="fas fa-mug-saucer text-[10px]"></i>
                                        <span><strong>Desayuno:</strong> 9:00 – 10:00</span>
                                    </div>
                                    <div class="flex items-center gap-2 text-xs text-amber-700 dark:text-amber-400">
                                        <i class="fas fa-utensils text-[10px]"></i>
                                        <span><strong>Almuerzo:</strong> 12:00 – 14:00</span>
                                    </div>
                                    <div class="flex items-center gap-2 text-xs text-amber-700 dark:text-amber-400">
                                        <i class="fas fa-moon text-[10px]"></i>
                                        <span><strong>Cena:</strong> 18:00 – 20:00</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if ($inventarioError)
                    <div class="mb-6 p-4 rounded-xl border border-rose-200 dark:border-rose-800/60 bg-rose-50 dark:bg-rose-950/30 flex items-start gap-3">
                        <div class="w-10 h-10 rounded-lg bg-rose-100 dark:bg-rose-900/40 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0">
                            <i class="fas fa-boxes-stacked text-base"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-black text-rose-800 dark:text-rose-300">
                                Inventario insuficiente
                            </p>
                            <p class="mt-1 text-xs text-rose-700 dark:text-rose-400 leading-relaxed whitespace-pre-line">
                                {{ $inventarioError }}
                            </p>
                            <p class="mt-2 text-[11px] text-rose-600/80 dark:text-rose-500">
                                Ajusta las cantidades o verifica el stock antes de reintentar.
                            </p>
                        </div>
                        <button type="button"
                            wire:click="$set('inventarioError', null)"
                            class="w-7 h-7 rounded-lg flex items-center justify-center text-rose-400 hover:text-rose-700 hover:bg-rose-100 dark:hover:bg-rose-900/30 transition-colors shrink-0"
                            title="Descartar">
                            <i class="fas fa-times text-xs"></i>
                        </button>
                    </div>
                @endif

                @if (!empty($inventarioAdvertencias))
                    <div class="mb-6 p-4 rounded-xl border border-amber-200 dark:border-amber-800/60 bg-amber-50 dark:bg-amber-950/30 flex items-start gap-3">
                        <div class="w-10 h-10 rounded-lg bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                            <i class="fas fa-triangle-exclamation text-base"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-black text-amber-800 dark:text-amber-300">
                                Avisos sobre la receta
                            </p>
                            <ul class="mt-1.5 space-y-1 text-xs text-amber-700 dark:text-amber-400 leading-relaxed list-disc pl-4">
                                @foreach ($inventarioAdvertencias as $adv)
                                    <li>{{ $adv }}</li>
                                @endforeach
                            </ul>
                            <p class="mt-2 text-[11px] text-amber-600/80 dark:text-amber-500">
                                Estos ingredientes no se descontarán del inventario. Edita la receta para corregirlos si es necesario.
                            </p>
                        </div>
                        <button type="button"
                            wire:click="$set('inventarioAdvertencias', [])"
                            class="w-7 h-7 rounded-lg flex items-center justify-center text-amber-400 hover:text-amber-700 hover:bg-amber-100 dark:hover:bg-amber-900/30 transition-colors shrink-0"
                            title="Descartar">
                            <i class="fas fa-times text-xs"></i>
                        </button>
                    </div>
                @endif

                @if ($errors->has('existe'))
                    <div class="mb-6 p-4 rounded-xl border border-amber-200 dark:border-amber-800/60 bg-amber-50 dark:bg-amber-950/30 flex items-start gap-3">
                        <i class="fas fa-info-circle text-amber-600 dark:text-amber-400 mt-0.5"></i>
                        <p class="text-sm font-bold text-amber-800 dark:text-amber-300">
                            {{ $errors->first('existe') }}
                        </p>
                    </div>
                @endif

                @if ($errors->has('horario'))
                    <div class="mb-6 p-4 rounded-xl border border-amber-200 dark:border-amber-800/60 bg-amber-50 dark:bg-amber-950/30 flex items-start gap-3">
                        <i class="fas fa-clock text-amber-600 dark:text-amber-400 mt-0.5"></i>
                        <p class="text-sm font-bold text-amber-800 dark:text-amber-300">
                            {{ $errors->first('horario') }}
                        </p>
                    </div>
                @endif

                @if ($horarioPermitido && $tipoComidaLabel)
                    <form wire:submit.prevent="saveDesayuno" autocomplete="off">

                        <div class="mb-5 p-4 rounded-xl border"
                            style="border-color: var(--border-color); background-color: rgba(0,0,0,0.015);">
                            <div class="flex items-center justify-between gap-3 flex-wrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-red-50 dark:bg-red-950/30 text-red-700 dark:text-red-500 flex items-center justify-center shrink-0">
                                        @if ($tipoComidaActual === 'desayuno')
                                            <i class="fas fa-mug-saucer"></i>
                                        @elseif ($tipoComidaActual === 'almuerzo')
                                            <i class="fas fa-utensils"></i>
                                        @else
                                            <i class="fas fa-moon"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <p class="text-[11px] font-black uppercase tracking-wider text-gray-400 dark:text-gray-500">
                                            Comida activa
                                        </p>
                                        <p class="text-base font-extrabold" style="color: var(--text-main);">
                                            {{ $tipoComidaLabel }}
                                        </p>
                                    </div>
                                </div>

                                <div class="text-right">
                                    <p class="text-[11px] font-black uppercase tracking-wider text-gray-400 dark:text-gray-500">
                                        Ventana
                                    </p>
                                    <p class="text-sm font-bold" style="color: var(--text-main);">
                                        {{ $ventanaActual }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-3">
                            @foreach ($desayunos_agregados as $index => $desayuno)
                                <div wire:key="desayuno-{{ $index }}"
                                    class="rounded-2xl border p-4"
                                    style="border-color: var(--border-color); background-color: rgba(0,0,0,0.015);">

                                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">

                                        <div class="md:col-span-7">
                                            <label class="block text-[11px] font-black uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-2">
                                                <span class="inline-flex items-center justify-center w-5 h-5 rounded-md text-[10px] font-black mr-1.5 shrink-0"
                                                    style="background-color: var(--bg-card); color: var(--text-main); border: 1px solid var(--border-color);">
                                                    {{ $index + 1 }}
                                                </span>
                                                Receta / platillo
                                            </label>
                                            <div class="flex items-stretch rounded-xl border overflow-hidden transition-all focus-within:ring-2 focus-within:ring-red-500/30 focus-within:border-red-500 @error('desayunos_agregados.' . $index . '.receta_id') border-rose-400 @enderror"
                                                style="border-color: var(--border-color);">
                                                <span class="flex items-center justify-center px-3.5 border-r text-gray-400"
                                                    style="background-color: rgba(0,0,0,0.03); border-color: var(--border-color);">
                                                    <i class="fas fa-book-open text-sm"></i>
                                                </span>
                                                <select
                                                    wire:model.live="desayunos_agregados.{{ $index }}.receta_id"
                                                    @disabled($desayuno_registrado)
                                                    style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                                    class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none disabled:opacity-60 disabled:cursor-not-allowed">
                                                    <option value="">— Seleccione una opción —</option>
                                                    @foreach ($comidas as $comida)
                                                        <option value="{{ $comida->id }}">{{ $comida->nombre }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            @error('desayunos_agregados.' . $index . '.receta_id')
                                                <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div class="md:col-span-3">
                                            <label class="block text-[11px] font-black uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-2">
                                                Cantidad
                                            </label>
                                            <div class="flex items-stretch rounded-xl border overflow-hidden transition-all focus-within:ring-2 focus-within:ring-red-500/30 focus-within:border-red-500 @error('desayunos_agregados.' . $index . '.cantidad') border-rose-400 @enderror"
                                                style="border-color: var(--border-color);">
                                                <span class="flex items-center justify-center px-3.5 border-r text-gray-400"
                                                    style="background-color: rgba(0,0,0,0.03); border-color: var(--border-color);">
                                                    <i class="fas fa-hashtag text-sm"></i>
                                                </span>
                                                <input type="number" min="1"
                                                    wire:model.live="desayunos_agregados.{{ $index }}.cantidad"
                                                    placeholder="Ej: 50"
                                                    @disabled($desayuno_registrado)
                                                    style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                                    class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none disabled:opacity-60 disabled:cursor-not-allowed">
                                            </div>
                                            @error('desayunos_agregados.' . $index . '.cantidad')
                                                <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div class="md:col-span-2">
                                            @if (count($desayunos_agregados) > 1 && !$desayuno_registrado)
                                                <button type="button"
                                                    wire:click="removeDesayuno({{ $index }})"
                                                    class="w-full inline-flex items-center justify-center gap-2 rounded-xl border px-3 py-2.5 text-xs font-bold transition-all hover:border-rose-400 hover:text-rose-600 hover:bg-rose-50/50 dark:hover:bg-rose-950/20"
                                                    style="border-color: var(--border-color); color: var(--text-main);">
                                                    <i class="fas fa-trash-alt text-xs"></i>
                                                    Quitar
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @if ($errors->has('general') || $errors->has('duplicado'))
                            <div class="mt-4 p-4 rounded-xl border border-rose-200 dark:border-rose-800/60 bg-rose-50 dark:bg-rose-950/30 flex items-start gap-3">
                                <i class="fas fa-exclamation-circle text-rose-600 dark:text-rose-400 mt-0.5"></i>
                                <div class="text-sm text-rose-700 dark:text-rose-300">
                                    @if ($errors->has('general'))
                                        <p class="font-bold">{{ $errors->first('general') }}</p>
                                    @endif
                                    @if ($errors->has('duplicado'))
                                        <p class="font-bold">{{ $errors->first('duplicado') }}</p>
                                    @endif
                                </div>
                            </div>
                        @endif

                        @if (!$desayuno_registrado)
                            <div class="mt-6 flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-3">
                                <button type="button"
                                    wire:click="addDesayuno"
                                    class="inline-flex items-center justify-center gap-2 rounded-xl border px-5 py-3 text-sm font-bold transition-all hover:bg-gray-50 dark:hover:bg-white/5"
                                    style="border-color: var(--border-color); color: var(--text-main);">
                                    <i class="fas fa-plus text-xs"></i>
                                    Añadir otro registro
                                </button>

                                <button type="submit"
                                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-800 hover:bg-red-900 text-white text-sm font-extrabold px-6 py-3 shadow-md active:scale-[0.98] transition-all">
                                    <i class="fas fa-save text-xs"></i>
                                    Guardar {{ $tipoComidaLabel }}
                                </button>
                            </div>
                        @else
                            <div class="mt-6 p-4 rounded-xl border flex items-center gap-3"
                                style="border-color: var(--border-color); background-color: rgba(0,0,0,0.015);">
                                <i class="fas fa-lock text-gray-400"></i>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    El registro de {{ $tipoComidaLabel }} de hoy ya fue guardado y no puede modificarse.
                                </p>
                            </div>
                        @endif
                    </form>
                @endif
            </div>

            <div class="px-6 sm:px-8 py-3 border-t" style="border-color: var(--border-color); background-color: rgba(0,0,0,0.015);">
                <p class="text-[11px] text-gray-400 dark:text-gray-500 flex items-center gap-1.5">
                    <i class="fas fa-shield-alt"></i>
                    Cada comida se registra en su horario y no puede modificarse después de guardarla.
                </p>
            </div>
        </div>

        <div style="background-color: var(--bg-card); border-color: var(--border-color);"
            class="rounded-2xl border shadow-sm overflow-hidden">

            <div class="p-5 sm:p-6 border-b" style="border-color: var(--border-color);">
                <div class="flex flex-col lg:flex-row lg:items-center gap-4">
                    <div>
                        <h2 class="text-lg font-extrabold flex items-center gap-2" style="color: var(--text-main);">
                            <i class="fas fa-list-alt text-red-700 dark:text-red-500"></i>
                            Historial de registros
                        </h2>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Consumo de comidas registrado en el sistema.
                        </p>
                    </div>

                    <div class="relative w-full lg:w-80 lg:ml-auto">
                        <i class="fas fa-search absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400 pointer-events-none text-sm"></i>
                        <input type="text" name="buscar" value="{{ $buscar ?? '' }}"
                            wire:model.live.debounce.400ms="buscar"
                            placeholder="Buscar por nombre de receta…"
                            style="background-color: var(--input-bg); border-color: var(--border-color); color: var(--text-main);"
                            class="w-full pl-10 pr-4 py-2.5 rounded-xl border text-sm font-medium focus:outline-none focus:ring-2 focus:ring-red-500/30 focus:border-red-500 transition-all placeholder-gray-400">
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b text-[11px] font-black uppercase tracking-wider"
                            style="border-color: var(--border-color); background-color: rgba(0,0,0,0.015);">
                            <th class="px-6 py-4 text-center" style="width:80px; color: var(--text-main);">#</th>
                            <th class="px-6 py-4 text-left" style="color: var(--text-main);">Receta</th>
                            <th class="px-6 py-4 text-center" style="width:140px; color: var(--text-main);">Comida</th>
                            <th class="px-6 py-4 text-center" style="width:140px; color: var(--text-main);">Cantidad</th>
                            <th class="px-6 py-4 text-center" style="width:180px; color: var(--text-main);">Registrado</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm font-medium">
                        @forelse ($data as $registro)
                            @php
                                $tipoReg = $registro->tipo_comida ?? null;
                                $labelTipo = match ($tipoReg) {
                                    'desayuno' => 'Desayuno',
                                    'almuerzo' => 'Almuerzo',
                                    'cena'     => 'Cena',
                                    default    => '—',
                                };
                                $iconTipo = match ($tipoReg) {
                                    'desayuno' => 'fa-mug-saucer',
                                    'almuerzo' => 'fa-utensils',
                                    'cena'     => 'fa-moon',
                                    default    => 'fa-circle',
                                };
                            @endphp

                            <tr class="border-b transition-colors hover:bg-black/[0.015] dark:hover:bg-white/[0.02]"
                                style="border-color: var(--border-color);">

                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center justify-center w-8 h-8 text-xs font-black rounded-lg border"
                                        style="border-color: var(--border-color); color: var(--text-main);">
                                        {{ $loop->iteration + ($data->currentPage() - 1) * $data->perPage() }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap" style="color: var(--text-main);">
                                    <div class="flex items-center gap-2.5">
                                        <span class="w-8 h-8 rounded-lg bg-red-50 dark:bg-red-950/30 text-red-700 dark:text-red-500 flex items-center justify-center shrink-0">
                                            <i class="fas fa-utensils text-xs"></i>
                                        </span>
                                        <span class="font-bold">{{ $registro->receta->nombre ?? 'Receta eliminada' }}</span>
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 text-[10px] font-black rounded-lg border"
                                        style="border-color: var(--border-color); color: var(--text-main);">
                                        <i class="fas {{ $iconTipo }} text-[10px] opacity-60"></i>
                                        {{ $labelTipo }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-black rounded-lg border"
                                        style="border-color: var(--border-color); color: var(--text-main);">
                                        <i class="fas fa-hashtag text-[10px] opacity-60"></i>
                                        {{ $registro->cantidad_servido }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-center whitespace-nowrap text-gray-500 dark:text-gray-400">
                                    <i class="far fa-calendar mr-1.5 opacity-60"></i>
                                    {{ \Carbon\Carbon::parse($registro->created_at)->format('d/m/Y · h:i A') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center gap-3">
                                        <div class="w-16 h-16 rounded-full bg-gray-50 dark:bg-gray-800/60 flex items-center justify-center">
                                            <i class="fas fa-inbox text-2xl text-gray-300 dark:text-gray-600"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold" style="color: var(--text-main);">
                                                Sin registros
                                            </p>
                                            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                                                @if ($buscar)
                                                    No se encontraron registros para «{{ $buscar }}».
                                                @else
                                                    Aún no se ha registrado ningún consumo.
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
                <div class="px-6 py-4 border-t" style="border-color: var(--border-color); background-color: rgba(0,0,0,0.015);">
                    {{ $data->appends(request()->query())->links() }}
                </div>
            @endif
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
                        });
                    } else {
                        alert((data.title || '') + '\n\n' + (data.text || ''));
                    }
                });

                Livewire.on('notify-inventario', (payload) => {
                    const data = Array.isArray(payload) ? payload[0] : payload;
                    const mensaje = data?.message || 'No hay suficiente inventario para completar el registro.';

                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Inventario insuficiente',
                            text: mensaje,
                            confirmButtonColor: '#991b1b',
                            confirmButtonText: 'Entendido',
                        });
                    } else {
                        alert('Inventario insuficiente\n\n' + mensaje);
                    }
                });
            });
        </script>
    @endpush
</div>