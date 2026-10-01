<x-app-layout>
    @php
        $fmt = fn($n) => rtrim(rtrim(number_format((float) $n, 2, '.', ''), '0'), '.');

        $receta = $consulta->receta;
        $paciente = $consulta->paciente;
        $pacienteNombre = trim(($paciente->nombre_persona ?? '') . ' ' . ($paciente->apellido_persona ?? ''));
        $medicoNombre = trim(($consulta->medico->nombre_persona ?? '') . ' ' . ($consulta->medico->apellido_persona ?? ''));
        $recetaVencida = $receta->vigencia && \Carbon\Carbon::parse($receta->vigencia)->endOfDay()->isPast();

        $dispensacionesExistentes = $receta->detalles->flatMap->dispensaciones;
        $detallesPendientes = $receta->detalles->filter(fn($d) => $d->cantidad_pendiente > 0);
        $totalPendientes = $detallesPendientes->count();
    @endphp

    @include('admin.salud.movimientos.consultas.partials.ui')

    <div class="pt-6 pb-16 min-h-[calc(100vh-4rem)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @include('components.alert')

            <x-consulta-encabezado
                titulo="Entrega de medicamentos"
                descripcion="Confirma lo que se entrega hoy en farmacia."
                :step="3"
                :consulta="$consulta"
                :volver-url="route('admin.salud.movimientos.consultas.recetacion', $consulta)"
                volver-texto="Volver a la receta" />

            <div x-data="dispensacionForm()" class="grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_340px] gap-6 items-start">

                {{-- ═══════════════ COLUMNA PRINCIPAL ═══════════════ --}}
                <div class="space-y-4 min-w-0">

                    @if (!$receta->tieneItemsPendientes())
                        {{-- ── Todo entregado ── --}}
                        <div class="cx-card px-6 py-12 text-center">
                            <span class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 mb-4">
                                <i class="fas fa-check text-xl" aria-hidden="true"></i>
                            </span>
                            <h2 class="text-lg font-bold cx-text">No queda nada por entregar</h2>
                            <p class="text-sm cx-muted mt-1.5 mb-6 max-w-sm mx-auto">
                                Todos los medicamentos de esta receta ya fueron entregados o procesados.
                            </p>
                            <a href="{{ route('admin.salud.movimientos.consultas.index') }}" class="cx-btn cx-btn-primary">
                                Volver a consultas
                            </a>
                        </div>
                    @else
                        <form x-ref="dispensacionForm" id="form-dispensacion"
                            action="{{ route('admin.salud.movimientos.consultas.dispensacion.store', $consulta) }}"
                            method="POST" class="rd-prevent-double-submit space-y-4">
                            @csrf

                            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-2">
                                <div>
                                    <h2 class="cx-title">Por entregar</h2>
                                    <p class="text-sm cx-muted mt-0.5">
                                        Desmarca lo que no se entregue hoy: quedará pendiente para otra visita.
                                    </p>
                                </div>
                                <p class="text-xs cx-muted flex items-center gap-1.5 shrink-0"
                                    title="Se usan primero los lotes que vencen antes">
                                    <i class="fas fa-layer-group text-[10px]" aria-hidden="true"></i>
                                    Lotes asignados por vencimiento
                                </p>
                            </div>

                            @foreach ($detallesPendientes as $detalle)
                                @php
                                    $lotesDisponibles = $lotesPorDetalle[$detalle->id] ?? collect();
                                    $lotesJs = $lotesDisponibles->map(function ($l) {
                                        $vence = $l->fecha_vencimiento ? \Carbon\Carbon::parse($l->fecha_vencimiento) : null;
                                        return [
                                            'codigo' => $l->codigo_lote,
                                            'vence' => $vence?->format('d/m/Y'),
                                            'proximo' => $vence ? $vence->lte(now()->addDays(30)) : false,
                                            'stock' => (float) ($l->inventarioSedeLotes->first()->cantidad ?? 0),
                                        ];
                                    })->values();
                                    $stockTotalSede = $lotesJs->sum('stock');
                                    $sinStock = $stockTotalSede <= 0;
                                    $pendiente = (float) $detalle->cantidad_pendiente;
                                    $unidadNombre = optional($detalle->unidad)->nombre ?? '';
                                    $unidadCorta = optional($detalle->unidad)->abreviatura ?? $unidadNombre;
                                    $prescrita = $detalle->cantidad_prescrita ?? null;
                                    // Tras un error de validación se respeta lo que el usuario había marcado
                                    $oldDispensar = old("items.{$detalle->id}.dispensar");
                                    $oldCantidad = old("items.{$detalle->id}.cantidad");
                                    $configDetalle = [
                                        'id' => $detalle->id,
                                        'nombre' => optional($detalle->producto)->nombre ?? '—',
                                        'unidad' => $unidadCorta,
                                        'dispensar' => !$sinStock && ($oldDispensar === null || filter_var($oldDispensar, FILTER_VALIDATE_BOOLEAN)),
                                        'cantidad' => $sinStock ? 0 : (float) ($oldCantidad ?? $fmt(min($pendiente, $stockTotalSede))),
                                        'pendiente' => $pendiente,
                                        'stock' => $stockTotalSede,
                                        'lotes' => $lotesJs,
                                    ];
                                @endphp

                                <article x-data="registrar(crearDetalle(@js($configDetalle)))"
                                    class="cx-card transition-opacity"
                                    :class="{ 'opacity-60': !dispensar }"
                                    @error("items.{$detalle->id}.cantidad") style="border-color:#e11d48" @enderror>

                                    {{-- ── Cabecera seleccionable ── --}}
                                    <label class="flex items-start gap-4 p-5 {{ $sinStock ? 'cursor-not-allowed' : 'cursor-pointer' }}">
                                        <input type="hidden" name="items[{{ $detalle->id }}][dispensar]" value="0">
                                        <input type="checkbox" name="items[{{ $detalle->id }}][dispensar]" value="1"
                                            x-model="dispensar" {{ $sinStock ? 'disabled' : '' }}
                                            class="mt-0.5 w-5 h-5 rounded border-gray-300 text-sky-600 focus:ring-sky-500 disabled:opacity-40 shrink-0">

                                        <span class="flex-1 min-w-0">
                                            <span class="flex flex-wrap items-center gap-x-3 gap-y-1">
                                                <span class="text-[15px] font-bold cx-text">
                                                    {{ optional($detalle->producto)->nombre ?? '—' }}
                                                </span>
                                                @if ($sinStock)
                                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-xs font-semibold bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300">
                                                        <i class="fas fa-ban text-[10px]" aria-hidden="true"></i> Sin existencias
                                                    </span>
                                                @endif
                                            </span>

                                            <span class="block text-sm cx-muted mt-1">
                                                {{ $detalle->frecuencia }}
                                                @if ($detalle->observaciones)
                                                    <span class="cx-faint">—</span> {{ $detalle->observaciones }}
                                                @endif
                                            </span>

                                            <span class="block text-xs mt-2 font-medium text-amber-700 dark:text-amber-400">
                                                Falta entregar {{ $fmt($pendiente) }} {{ $unidadNombre }}
                                                @if ($prescrita && (float) $prescrita > $pendiente)
                                                    <span class="cx-muted font-normal">de {{ $fmt($prescrita) }} recetadas</span>
                                                @endif
                                            </span>
                                        </span>
                                    </label>

                                    @if ($sinStock)
                                        <div class="cx-divider px-5 py-3.5 flex items-start gap-3 text-sm bg-rose-50/50 dark:bg-rose-950/20">
                                            <i class="fas fa-circle-info text-rose-500 mt-0.5" aria-hidden="true"></i>
                                            <p class="text-rose-700 dark:text-rose-300">
                                                No hay lotes vigentes con existencias en tu sede (#{{ $sedeId }}), así que no se puede entregar hoy.
                                            </p>
                                        </div>
                                    @else
                                        <div x-show="dispensar" x-collapse>
                                            <div class="cx-divider px-5 py-4 grid grid-cols-1 md:grid-cols-[220px_minmax(0,1fr)] gap-4">

                                                {{-- Cantidad --}}
                                                <div>
                                                    <label for="cantidad_{{ $detalle->id }}" class="cx-label">Cantidad a entregar</label>
                                                    <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500/30 focus-within:border-sky-600"
                                                        style="border-color: var(--border-color);"
                                                        :style="excede ? 'border-color:#e11d48' : ''">
                                                        <input type="number" id="cantidad_{{ $detalle->id }}"
                                                            name="items[{{ $detalle->id }}][cantidad]"
                                                            x-model.number="cantidad" :disabled="!dispensar"
                                                            min="0.01" :max="maxCantidad" step="0.01" inputmode="decimal"
                                                            class="w-full min-w-0 px-3.5 py-2.5 text-base font-bold tabular-nums bg-transparent border-0 focus:ring-0 cx-text">
                                                        <span class="px-3 flex items-center text-xs font-semibold cx-muted cx-soft">{{ $unidadCorta }}</span>
                                                    </div>
                                                    <div class="flex items-center justify-between mt-1.5">
                                                        <span class="text-xs cx-muted">
                                                            Máximo <span class="font-semibold tabular-nums" x-text="fmt(maxCantidad)"></span>
                                                        </span>
                                                        <button type="button" class="cx-link !text-xs" x-show="cantidad !== maxCantidad"
                                                            @click="cantidad = maxCantidad">Usar máximo</button>
                                                    </div>
                                                    <p x-show="excede" x-cloak class="cx-error">
                                                        <i class="fas fa-circle-exclamation mt-0.5" aria-hidden="true"></i>
                                                        <span x-text="cantidad > stock ? 'Supera las existencias de la sede.' : 'Supera lo pendiente.'"></span>
                                                    </p>
                                                    <p x-show="dispensar && !(cantidad > 0)" x-cloak class="cx-error">
                                                        <i class="fas fa-circle-exclamation mt-0.5" aria-hidden="true"></i>
                                                        Indica una cantidad mayor a 0.
                                                    </p>
                                                    @error("items.{$detalle->id}.cantidad")
                                                        <p class="cx-error"><i class="fas fa-circle-exclamation mt-0.5" aria-hidden="true"></i>{{ $message }}</p>
                                                    @enderror
                                                </div>

                                                {{-- Lotes + observación --}}
                                                <div class="space-y-4 min-w-0">
                                                    <div>
                                                        <p class="cx-label">Se tomará de</p>
                                                        <ul class="flex flex-wrap gap-2">
                                                            <template x-for="(a, i) in asignacion" :key="a.codigo">
                                                                <li class="inline-flex items-center gap-2 pl-2 pr-3 py-1.5 rounded-lg border text-xs"
                                                                    :class="i === 0 ? 'border-sky-200 dark:border-sky-900 bg-sky-50 dark:bg-sky-950/30' : ''"
                                                                    style="border-color: var(--border-color);">
                                                                    <span class="font-bold tabular-nums text-sky-700 dark:text-sky-300" x-text="fmt(a.toma)"></span>
                                                                    <span class="cx-faint">del lote</span>
                                                                    <span class="font-semibold cx-text" x-text="a.codigo"></span>
                                                                    <span x-show="a.vence" :class="a.proximo ? 'text-amber-600 dark:text-amber-400 font-semibold' : 'cx-muted'"
                                                                        x-text="`vence ${a.vence}`"></span>
                                                                </li>
                                                            </template>
                                                            <li x-show="asignacion.length === 0" class="text-xs cx-muted">Indica una cantidad para ver los lotes.</li>
                                                        </ul>

                                                        <div x-data="{ verTodos: false }" class="mt-2">
                                                            <button type="button" class="cx-link !text-xs" @click="verTodos = !verTodos" :aria-expanded="verTodos">
                                                                <span x-text="verTodos ? 'Ocultar existencias' : `Ver existencias (${fmt(stock)} ${unidad} en ${lotes.length} ${lotes.length === 1 ? 'lote' : 'lotes'})`"></span>
                                                            </button>
                                                            <table x-show="verTodos" x-collapse class="mt-2 w-full text-xs">
                                                                <thead>
                                                                    <tr class="cx-muted text-left">
                                                                        <th class="font-medium py-1 pr-3">Orden</th>
                                                                        <th class="font-medium py-1 pr-3">Lote</th>
                                                                        <th class="font-medium py-1 pr-3">Vence</th>
                                                                        <th class="font-medium py-1 text-right">Existencias</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <template x-for="(l, i) in lotes" :key="l.codigo">
                                                                        <tr class="cx-divider">
                                                                            <td class="py-1.5 pr-3 cx-muted tabular-nums" x-text="i + 1"></td>
                                                                            <td class="py-1.5 pr-3 font-semibold cx-text" x-text="l.codigo"></td>
                                                                            <td class="py-1.5 pr-3" :class="l.proximo ? 'text-amber-600 dark:text-amber-400 font-semibold' : 'cx-muted'"
                                                                                x-text="l.vence || 'Sin fecha'"></td>
                                                                            <td class="py-1.5 text-right font-semibold tabular-nums cx-text" x-text="fmt(l.stock)"></td>
                                                                        </tr>
                                                                    </template>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>

                                                    <div>
                                                        <label for="obs_{{ $detalle->id }}" class="cx-label">
                                                            Observación<span class="cx-opt">(opcional)</span>
                                                        </label>
                                                        <input type="text" id="obs_{{ $detalle->id }}" maxlength="255"
                                                            name="items[{{ $detalle->id }}][observaciones]"
                                                            :disabled="!dispensar" placeholder="Ej.: se entrega una caja de dos"
                                                            value="{{ old("items.{$detalle->id}.observaciones") }}"
                                                            class="cx-input">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </article>
                            @endforeach
                        </form>
                    @endif

                    {{-- ── Entregas anteriores ── --}}
                    @if ($dispensacionesExistentes->isNotEmpty())
                        <details class="cx-card group" @if (!$receta->tieneItemsPendientes()) open @endif>
                            <summary class="flex items-center justify-between gap-3 px-5 py-4 cursor-pointer list-none rounded-2xl focus-visible:outline focus-visible:outline-2 focus-visible:outline-sky-600">
                                <span class="flex items-center gap-3">
                                    <i class="fas fa-clock-rotate-left text-sm text-emerald-600 dark:text-emerald-400" aria-hidden="true"></i>
                                    <span class="text-sm font-semibold cx-text">Entregas anteriores</span>
                                    <span class="px-2 py-0.5 rounded-md text-xs font-semibold cx-soft cx-muted">{{ $dispensacionesExistentes->count() }}</span>
                                </span>
                                <i class="fas fa-chevron-down text-xs cx-faint transition-transform group-open:rotate-180" aria-hidden="true"></i>
                            </summary>

                            <ul class="cx-divider divide-y" style="--tw-divide-opacity:1;">
                                @foreach ($receta->detalles as $detalle)
                                    @foreach ($detalle->dispensaciones as $disp)
                                        <li class="flex items-center gap-4 px-5 py-3" style="border-color: var(--border-color);">
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-semibold cx-text truncate">
                                                    {{ optional($detalle->producto)->nombre ?? '—' }}
                                                </p>
                                                <p class="text-xs cx-muted mt-0.5">
                                                    {{ optional($disp->fecha)->format('d/m/Y') }},
                                                    lote {{ optional($disp->lote)->codigo_lote ?? '—' }},
                                                    entregó {{ optional(optional($disp->usuario)->persona)->nombre_persona ?? '—' }}
                                                </p>
                                            </div>
                                            <span class="text-sm font-bold tabular-nums text-emerald-700 dark:text-emerald-400 shrink-0">
                                                {{ $fmt($disp->cantidad) }}
                                                <span class="text-xs font-medium cx-muted">{{ optional($detalle->unidad)->abreviatura ?? optional($detalle->unidad)->nombre }}</span>
                                            </span>
                                        </li>
                                    @endforeach
                                @endforeach
                            </ul>
                        </details>
                    @endif
                </div>

                {{-- ═══════════════ FICHA LATERAL ═══════════════ --}}
                <aside class="order-first lg:order-none lg:sticky lg:top-6 space-y-4" aria-label="Datos del paciente y la receta">
                    <div class="cx-card p-5">
                        <div class="flex items-center gap-3">
                            <span class="w-10 h-10 rounded-full bg-sky-600 text-white flex items-center justify-center text-sm font-bold shrink-0" aria-hidden="true">
                                {{ mb_strtoupper(mb_substr($paciente->nombre_persona ?? '?', 0, 1) . mb_substr($paciente->apellido_persona ?? '', 0, 1)) }}
                            </span>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold cx-text truncate">{{ $pacienteNombre ?: 'Paciente' }}</p>
                                <p class="text-xs cx-muted">C.I. {{ $paciente->cedula_persona ?? 'S/C' }}</p>
                            </div>
                        </div>

                        <dl class="cx-ficha mt-4 pt-4 cx-divider space-y-3">
                            <div>
                                <dt>Médico</dt>
                                <dd>{{ $medicoNombre ?: '—' }}</dd>
                            </div>
                            <div x-data="{ abierto: false }">
                                <dt>Diagnóstico</dt>
                                <dd class="!font-medium" :class="abierto ? '' : 'line-clamp-3'">{{ $consulta->diagnostico }}</dd>
                                @if (mb_strlen($consulta->diagnostico ?? '') > 140)
                                    <button type="button" class="cx-link mt-1 !text-xs" @click="abierto = !abierto"
                                        x-text="abierto ? 'Ver menos' : 'Ver completo'"></button>
                                @endif
                            </div>
                        </dl>
                    </div>

                    <div class="cx-card p-5">
                        <h2 class="text-sm font-semibold cx-text">Receta</h2>
                        <dl class="cx-ficha mt-3 grid grid-cols-2 gap-3">
                            <div>
                                <dt>Emitida</dt>
                                <dd>{{ optional($receta->fecha)->format('d/m/Y') ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt>Válida hasta</dt>
                                <dd class="{{ $recetaVencida ? '!text-rose-600' : '' }}">
                                    {{ optional($receta->vigencia)->format('d/m/Y') ?? 'Sin límite' }}
                                    @if ($recetaVencida)
                                        <span class="block text-xs font-semibold">Vencida</span>
                                    @endif
                                </dd>
                            </div>
                            @if ($receta->descripcion)
                                <div class="col-span-2">
                                    <dt>Indicaciones generales</dt>
                                    <dd class="!font-medium">{{ $receta->descripcion }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>

                    @if ($receta->tieneItemsPendientes())
                        <div class="cx-card p-5">
                            <div class="flex items-baseline justify-between">
                                <h2 class="text-sm font-semibold cx-text">Entrega de hoy</h2>
                                <span class="text-xs font-semibold tabular-nums cx-muted"
                                    x-text="`${seleccionados.length} de ${items.length}`"></span>
                            </div>
                            <div class="mt-3 h-1.5 rounded-full bg-gray-200 dark:bg-gray-800 overflow-hidden">
                                <div class="h-full rounded-full bg-sky-600 transition-[width] duration-300"
                                    :style="`width: ${items.length ? (seleccionados.length / items.length) * 100 : 0}%`"></div>
                            </div>
                            <p class="text-xs cx-muted mt-3" x-text="textoEstado"></p>
                        </div>
                    @endif
                </aside>

                {{-- ═══════════════ ACCIONES ═══════════════ --}}
                @if ($receta->tieneItemsPendientes())
                    <div class="lg:col-span-2 cx-actionbar !mt-0 px-4 py-3 sm:px-5 flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-3">
                        <a href="{{ route('admin.salud.movimientos.consultas.index') }}" class="cx-btn cx-btn-ghost">
                            Cancelar
                        </a>
                        <button type="button" @click="validarYConfirmar()" class="rd-submit-btn cx-btn cx-btn-primary"
                            :class="{ '!bg-amber-600 hover:!bg-amber-700': seleccionados.length === 0 }">
                            <i class="fas text-xs" :class="seleccionados.length === 0 ? 'fa-door-closed' : 'fa-check'" aria-hidden="true"></i>
                            <span x-text="textoBoton"></span>
                        </button>
                    </div>
                @endif

                {{-- ═══════════════ MODAL DE CONFIRMACIÓN ═══════════════ --}}
                <div x-show="showModal" x-cloak
                    x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
                    @keydown.escape.window="showModal = false"
                    role="dialog" aria-modal="true" aria-labelledby="modal-titulo">

                    <div class="relative w-full max-w-md cx-card shadow-2xl p-6" @click.outside="showModal = false">

                        <div class="flex items-start gap-4">
                            <span class="w-11 h-11 rounded-full flex items-center justify-center shrink-0"
                                :class="{
                                    'bg-rose-100 text-rose-600 dark:bg-rose-950/60 dark:text-rose-400': modalType === 'warning',
                                    'bg-amber-100 text-amber-600 dark:bg-amber-950/60 dark:text-amber-400': modalType === 'confirm' && seleccionados.length === 0,
                                    'bg-sky-100 text-sky-600 dark:bg-sky-950/60 dark:text-sky-400': modalType === 'confirm' && seleccionados.length > 0,
                                }">
                                <i class="fas text-lg" :class="modalType === 'warning' ? 'fa-circle-exclamation' : 'fa-clipboard-check'" aria-hidden="true"></i>
                            </span>
                            <div class="min-w-0">
                                <h3 id="modal-titulo" class="text-base font-bold cx-text" x-text="modalTitle"></h3>
                                <p class="text-sm cx-muted mt-1 leading-relaxed" x-text="modalMessage"></p>
                            </div>
                        </div>

                        {{-- Resumen de lo que se entregará --}}
                        <ul x-show="modalType === 'confirm' && seleccionados.length" class="mt-5 rounded-xl cx-soft divide-y text-sm"
                            style="--tw-divide-opacity:1;">
                            <template x-for="it in seleccionados" :key="it.id">
                                <li class="flex items-center justify-between gap-3 px-4 py-2.5" style="border-color: var(--border-color);">
                                    <span class="truncate cx-text" x-text="it.nombre"></span>
                                    <span class="font-bold tabular-nums cx-text shrink-0" x-text="`${fmt(it.cantidad)} ${it.unidad}`"></span>
                                </li>
                            </template>
                        </ul>

                        {{-- Errores a corregir --}}
                        <ul x-show="modalType === 'warning'" class="mt-4 space-y-1.5 text-sm text-rose-700 dark:text-rose-300 list-disc pl-5">
                            <template x-for="it in invalidos" :key="it.id">
                                <li x-text="it.nombre"></li>
                            </template>
                        </ul>

                        <div class="mt-6 flex flex-col-reverse sm:flex-row gap-3">
                            <template x-if="modalType === 'warning'">
                                <button type="button" @click="showModal = false" class="cx-btn cx-btn-primary w-full">
                                    Revisar cantidades
                                </button>
                            </template>
                            <template x-if="modalType === 'confirm'">
                                <div class="flex flex-col-reverse sm:flex-row gap-3 w-full">
                                    <button type="button" @click="showModal = false" class="cx-btn cx-btn-ghost flex-1">
                                        Seguir revisando
                                    </button>
                                    <button type="button" @click="submitForm()" class="cx-btn cx-btn-primary flex-1"
                                        :class="{ '!bg-amber-600 hover:!bg-amber-700': seleccionados.length === 0 }"
                                        x-text="seleccionados.length === 0 ? 'Sí, cerrar sin entregar' : 'Sí, registrar entrega'">
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function fmt(n) {
            const num = Number(n);
            return Number.isFinite(num) ? String(+num.toFixed(2)) : '0';
        }

        function crearDetalle(cfg) {
            return {
                ...cfg,
                get maxCantidad() {
                    return +Math.min(this.pendiente, this.stock).toFixed(2);
                },
                get excede() {
                    return this.dispensar && this.cantidad > this.maxCantidad;
                },
                // Reparto FIFO solo para mostrar (el servidor hace el descuento real)
                get asignacion() {
                    let restante = Math.min(Number(this.cantidad) || 0, this.stock);
                    const reparto = [];
                    for (const lote of this.lotes) {
                        if (restante <= 0) break;
                        if (lote.stock <= 0) continue;
                        const toma = Math.min(lote.stock, restante);
                        reparto.push({ ...lote, toma });
                        restante = +(restante - toma).toFixed(4);
                    }
                    return reparto;
                },
            };
        }

        function dispensacionForm() {
            return {
                items: [],
                showModal: false,
                modalType: 'confirm',
                modalTitle: '',
                modalMessage: '',

                registrar(detalle) {
                    this.items.push(detalle);
                    return detalle;
                },

                get seleccionados() {
                    return this.items.filter(i => i.dispensar);
                },

                get invalidos() {
                    return this.seleccionados.filter(i => !(i.cantidad > 0) || i.cantidad > i.maxCantidad);
                },

                get textoBoton() {
                    const n = this.seleccionados.length;
                    if (n === 0) return 'Cerrar sin entregar';
                    if (n < this.items.length) return `Registrar entrega parcial (${n})`;
                    return 'Registrar entrega';
                },

                get textoEstado() {
                    const n = this.seleccionados.length, total = this.items.length;
                    if (n === 0) return 'No marcaste ningún medicamento: la atención se cerrará sin entregas.';
                    if (n < total) return `${total - n} quedará${total - n === 1 ? '' : 'n'} pendiente${total - n === 1 ? '' : 's'} para otra visita.`;
                    return 'Se entrega todo: la atención quedará cerrada.';
                },

                validarYConfirmar() {
                    const n = this.seleccionados.length, total = this.items.length;

                    if (this.invalidos.length) {
                        this.modalType = 'warning';
                        this.modalTitle = 'Revisa las cantidades';
                        this.modalMessage = 'Estos medicamentos tienen una cantidad vacía o mayor a la permitida:';
                    } else if (n === 0) {
                        this.modalType = 'confirm';
                        this.modalTitle = '¿Cerrar la atención sin entregar?';
                        this.modalMessage = 'No marcaste ningún medicamento. La atención se cerrará sin registrar entregas y ya no podrá editarse.';
                    } else if (n < total) {
                        this.modalType = 'confirm';
                        this.modalTitle = '¿Registrar entrega parcial?';
                        this.modalMessage = `Se entregarán ${n} de ${total} medicamentos. Los demás quedarán pendientes para otra visita.`;
                    } else {
                        this.modalType = 'confirm';
                        this.modalTitle = '¿Registrar la entrega completa?';
                        this.modalMessage = 'Se entregarán todos los medicamentos y la atención quedará cerrada. No podrá modificarse.';
                    }

                    this.showModal = true;
                },

                submitForm() {
                    this.showModal = false;
                    document.getElementById('form-dispensacion')?.submit();
                },
            };
        }
    </script>
</x-app-layout>
