<x-app-layout>
    <div class="pt-6 pb-16 min-h-[calc(100vh-4rem)]">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

            @include('components.alert')

            {{-- ═══════════ ENCABEZADO ═══════════ --}}
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div class="flex items-center gap-4">
                    <div
                        class="w-12 h-12 rounded-2xl bg-sky-600 flex items-center justify-center text-white shadow-lg shadow-sky-600/20 shrink-0">
                        <i class="fas fa-box-open text-lg"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color: var(--text-main);">
                            Dispensación de Medicamentos
                        </h1>
                        <p class="mt-0.5 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                            Paso 3 de 3 · consulta #{{ $consulta->id }}
                        </p>
                    </div>
                </div>

                <a href="{{ route('admin.salud.movimientos.consultas.recetacion', $consulta) }}"
                    class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl border text-sm font-bold hover:bg-gray-50 dark:hover:bg-white/5 transition-all"
                    style="border-color: var(--border-color); color: var(--text-main);">
                    <i class="fas fa-arrow-left text-xs"></i> Volver a Recetación (Paso 2)
                </a>
            </div>

            {{-- Stepper --}}
            <x-consulta-stepper :step="3" />

            {{-- ═══════════ RESUMEN CLÍNICO ═══════════ --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="p-5 rounded-2xl border shadow-sm"
                    style="background-color: var(--bg-card); border-color: var(--border-color);">
                    <div class="flex items-center gap-2.5 mb-4">
                        <div
                            class="w-8 h-8 rounded-xl bg-sky-50 dark:bg-sky-950/50 text-sky-600 dark:text-sky-400 flex items-center justify-center">
                            <i class="fas fa-notes-medical text-xs"></i>
                        </div>
                        <h3 class="text-sm font-extrabold tracking-tight" style="color: var(--text-main);">
                            Datos Clínicos
                        </h3>
                    </div>
                    <div class="grid grid-cols-2 gap-3 text-xs">
                        <div>
                            <span
                                class="text-gray-400 block font-semibold uppercase tracking-wider text-[10px] mb-1">Paciente</span>
                            <span class="font-bold text-sm" style="color: var(--text-main);">
                                {{ $consulta->paciente->cedula_persona ?? '' }}
                                {{ $consulta->paciente->nombre_persona ?? '' }}
                                {{ $consulta->paciente->apellido_persona ?? '' }}
                            </span>
                        </div>
                        <div>
                            <span
                                class="text-gray-400 block font-semibold uppercase tracking-wider text-[10px] mb-1">Médico</span>
                            <span class="font-bold text-sm" style="color: var(--text-main);">
                                {{ $consulta->medico->nombre_persona ?? '' }}
                                {{ $consulta->medico->apellido_persona ?? '' }}
                            </span>
                        </div>
                        <div class="col-span-2">
                            <span
                                class="text-gray-400 block font-semibold uppercase tracking-wider text-[10px] mb-1">Diagnóstico</span>
                            <span class="font-medium text-gray-600 dark:text-gray-300">
                                {{ $consulta->diagnostico }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="p-5 rounded-2xl border shadow-sm"
                    style="background-color: var(--bg-card); border-color: var(--border-color);">
                    <div class="flex items-center gap-2.5 mb-4">
                        <div
                            class="w-8 h-8 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                            <i class="fas fa-receipt text-xs"></i>
                        </div>
                        <h3 class="text-sm font-extrabold tracking-tight" style="color: var(--text-main);">
                            Datos de la Receta
                        </h3>
                    </div>
                    <div class="grid grid-cols-2 gap-3 text-xs">
                        <div>
                            <span
                                class="text-gray-400 block font-semibold uppercase tracking-wider text-[10px] mb-1">Fecha
                                Emisión</span>
                            <span class="font-bold text-sm" style="color: var(--text-main);">
                                {{ optional($consulta->receta->fecha)->format('d/m/Y') }}
                            </span>
                        </div>
                        <div>
                            <span
                                class="text-gray-400 block font-semibold uppercase tracking-wider text-[10px] mb-1">Vigencia</span>
                            <span class="font-bold text-sm" style="color: var(--text-main);">
                                {{ optional($consulta->receta->vigencia)->format('d/m/Y') }}
                            </span>
                        </div>
                        <div class="col-span-2">
                            <span
                                class="text-gray-400 block font-semibold uppercase tracking-wider text-[10px] mb-1">Indicaciones
                                Generales</span>
                            <span class="font-medium text-gray-600 dark:text-gray-300">
                                {{ $consulta->receta->descripcion }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Variables --}}
            @php
                $dispensacionesExistentes = $consulta->receta->detalles->flatMap->dispensaciones;
                $tieneDispensaciones = $dispensacionesExistentes->isNotEmpty();
                $detallesPendientes = $consulta->receta->detalles->filter(fn($d) => $d->cantidad_pendiente > 0);
                $detallesPendientesCount = $detallesPendientes->count();
            @endphp

            {{-- ═══════════ ENTREGAS PREVIAS ═══════════ --}}
            @if ($tieneDispensaciones)
                <div style="background-color: var(--bg-card); border-color: var(--border-color);"
                    class="rounded-2xl border shadow-sm p-6 mb-6">
                    <div class="flex items-center gap-2.5 mb-4">
                        <div
                            class="w-8 h-8 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                            <i class="fas fa-check-circle text-xs"></i>
                        </div>
                        <h3 class="text-sm font-extrabold tracking-tight" style="color: var(--text-main);">
                            Medicamentos Entregados Previamente
                        </h3>
                    </div>

                    <div class="space-y-2">
                        @foreach ($consulta->receta->detalles as $detalle)
                            @foreach ($detalle->dispensaciones as $disp)
                                <div class="flex items-center gap-3 px-3 py-2.5 rounded-xl border"
                                    style="border-color: var(--border-color); background-color: rgba(0,0,0,0.015);">
                                    <div
                                        class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                                        <i class="fas fa-check text-[10px]"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-bold truncate" style="color: var(--text-main);">
                                            {{ optional($detalle->producto)->nombre ?? '—' }}
                                        </p>
                                        <p class="text-[10px] text-gray-500 dark:text-gray-400">
                                            {{ optional($disp->fecha)->format('d/m/Y') }}
                                            · Lote {{ optional($disp->lote)->codigo_lote ?? '—' }}
                                            · por
                                            {{ optional(optional($disp->usuario)->persona)->nombre_persona ?? '—' }}
                                        </p>
                                    </div>
                                    <span
                                        class="inline-flex items-center px-2.5 py-1 text-[11px] font-bold rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 shrink-0">
                                        {{ $disp->cantidad }}
                                        {{ optional($detalle->unidad)->abreviatura ?? optional($detalle->unidad)->nombre }}
                                    </span>
                                </div>
                            @endforeach
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- ═══════════ FORMULARIO ═══════════ --}}
            <div x-data="{
                checkedCount: {{ $detallesPendientesCount }},
                totalPending: {{ $detallesPendientesCount }},
                showModal: false,
                modalType: 'confirm',
                modalTitle: '',
                modalMessage: '',
                validationError: '',
            
                validarYConfirmar() {
                    this.validationError = '';
                    this.modalType = 'confirm';
            
                    if (this.checkedCount <= 0) {
                        this.modalTitle = '¿Finalizar sin entregar?';
                        this.modalMessage = 'No ha seleccionado ningún medicamento. La consulta se dará por FINALIZADA sin registrar entregas. Una vez guardado, no podrá editarse. ¿Desea continuar?';
                    } else if (this.checkedCount < this.totalPending) {
                        this.modalTitle = '¿Registrar dispensación parcial?';
                        this.modalMessage = `Está registrando la entrega de ${this.checkedCount} de ${this.totalPending} medicamentos. Los no seleccionados QUEDARÁN PENDIENTES y podrá dispensarlos en una próxima sesión. ¿Desea continuar?`;
                    } else {
                        this.modalTitle = '¿Confirmar dispensación y finalizar?';
                        this.modalMessage = '¿Está seguro de registrar la dispensación de TODOS los medicamentos? Una vez confirmado, la atención quedará finalizada y no podrá modificarse.';
                    }
            
                    this.showModal = true;
                },
            
                submitForm() {
                    this.$refs.dispensacionForm.submit();
                }
            }">

                @if (!$consulta->receta->tieneItemsPendientes())
                    {{-- Todo entregado --}}
                    <div style="background-color: var(--bg-card); border-color: var(--border-color);"
                        class="rounded-2xl border shadow-sm p-10 text-center">
                        <div
                            class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 mb-4">
                            <i class="fas fa-check-circle text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-bold" style="color: var(--text-main);">Dispensación Completada</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5 mb-6 max-w-sm mx-auto">
                            Todos los medicamentos recetados para esta consulta han sido entregados o procesados
                            satisfactoriamente.
                        </p>

                        <a href="{{ route('admin.salud.movimientos.consultas.index') }}"
                            class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-bold text-sm shadow-md shadow-sky-600/20 transition-all">
                            Finalizar y Volver al Listado <i class="fas fa-check text-xs"></i>
                        </a>
                    </div>
                @else
                    <form x-ref="dispensacionForm"
                        action="{{ route('admin.salud.movimientos.consultas.dispensacion.store', $consulta) }}"
                        method="POST" class="rd-prevent-double-submit">
                        @csrf

                        {{-- Aviso de sede --}}
                        <div class="mb-6 p-4 rounded-2xl border flex items-start gap-3 text-xs"
                            style="background-color: rgba(14,165,233,0.06); border-color: rgba(14,165,233,0.2);">
                            <div
                                class="w-8 h-8 rounded-lg bg-sky-100 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 flex items-center justify-center shrink-0">
                                <i class="fas fa-info-circle text-xs"></i>
                            </div>
                            <div>
                                <p class="font-bold mb-0.5" style="color: var(--text-main);">
                                    Asignación automática de lotes (FIFO)
                                </p>
                                <p class="text-gray-500 dark:text-gray-400">
                                    Los lotes se consumen por <strong>vencimiento más próximo</strong>. Solo indique la
                                    cantidad a entregar.
                                </p>
                            </div>
                        </div>

                        {{-- Card: Medicamentos --}}
                        <div style="background-color: var(--bg-card); border-color: var(--border-color);"
                            class="rounded-2xl border shadow-sm p-6 mb-6">
                            <div class="flex items-center gap-2.5 mb-6">
                                <div
                                    class="w-8 h-8 rounded-xl bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                                    <i class="fas fa-boxes text-xs"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm font-extrabold tracking-tight" style="color: var(--text-main);">
                                        Medicamentos Recetados
                                    </h3>
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400">
                                        Los medicamentos desmarcados no quedarán pendientes.
                                    </p>
                                </div>
                            </div>

                            <div class="space-y-5">
                                @foreach ($detallesPendientes as $detalle)
                                    @php
                                        $lotesDisponibles = $lotesPorDetalle[$detalle->id] ?? collect();
                                        $stockTotalSede = $lotesDisponibles->sum(
                                            fn($l) => (float) ($l->inventarioSedeLotes->first()->cantidad ?? 0),
                                        );
                                        $sinStock = $stockTotalSede <= 0;
                                    @endphp

                                    <div x-data="{
                                        dispensar: {{ $sinStock ? 'false' : 'true' }},
                                        cantidad: {{ $sinStock ? 0 : min($detalle->cantidad_pendiente, $stockTotalSede) }},
                                        pendiente: {{ $detalle->cantidad_pendiente }},
                                        stockDisponible: {{ $stockTotalSede }},
                                        get maxCantidad() { return Math.min(this.pendiente, this.stockDisponible); },
                                        get excedeStock() { return this.cantidad > this.stockDisponible; }
                                    }"
                                        class="rounded-2xl border overflow-hidden transition-all"
                                        :class="{ 'opacity-50 grayscale': !dispensar }"
                                        style="border-color: var(--border-color);">

                                        {{-- ── HEADER del medicamento ── --}}
                                        <label class="flex items-start gap-3 cursor-pointer px-5 py-4 border-b"
                                            style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color);">

                                            <input type="hidden" name="items[{{ $detalle->id }}][dispensar]"
                                                value="0">
                                            <input type="checkbox" name="items[{{ $detalle->id }}][dispensar]"
                                                value="1" x-model="dispensar"
                                                @change="dispensar ? checkedCount++ : checkedCount--"
                                                class="mt-1 rounded border-gray-300 text-sky-600 focus:ring-sky-500 transition-all">

                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center justify-between gap-3 flex-wrap mb-1">
                                                    <span class="text-sm font-extrabold"
                                                        style="color: var(--text-main);">
                                                        {{ optional($detalle->producto)->nombre ?? '—' }}
                                                    </span>

                                                    @if ($sinStock)
                                                        <span
                                                            class="inline-flex items-center gap-1 px-2.5 py-1 text-[10px] font-black rounded-lg bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-900">
                                                            <i class="fas fa-times-circle text-[9px]"></i>
                                                            Sin stock
                                                        </span>
                                                    @else
                                                        <span
                                                            class="inline-flex items-center gap-1 px-2.5 py-1 text-[10px] font-black rounded-lg bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-900">
                                                            <i class="fas fa-prescription-bottle text-[9px]"></i>
                                                            Pendiente: {{ $detalle->cantidad_pendiente }}
                                                            {{ optional($detalle->unidad)->nombre }}
                                                        </span>
                                                    @endif
                                                </div>

                                                <p class="text-[11px] text-gray-500 dark:text-gray-400">
                                                    <i class="fas fa-clock text-[9px] mr-1"></i>
                                                    <strong>Frecuencia:</strong> {{ $detalle->frecuencia }}
                                                    @if ($detalle->observaciones)
                                                        <span class="mx-1.5">·</span>
                                                        <em>{{ $detalle->observaciones }}</em>
                                                    @endif
                                                </p>
                                            </div>
                                        </label>

                                        {{-- ── SIN STOCK ── --}}
                                        @if ($sinStock)
                                            <div
                                                class="px-5 py-4 flex items-start gap-3 bg-rose-50/40 dark:bg-rose-950/20">
                                                <div
                                                    class="w-8 h-8 rounded-lg bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0">
                                                    <i class="fas fa-exclamation-triangle text-xs"></i>
                                                </div>
                                                <div>
                                                    <p
                                                        class="text-xs font-bold text-rose-700 dark:text-rose-300 mb-0.5">
                                                        Sin stock disponible
                                                    </p>
                                                    <p class="text-[11px] text-rose-600 dark:text-rose-400">
                                                        No hay lotes con stock en tu sede (ID: {{ $sedeId }}).
                                                    </p>
                                                </div>
                                            </div>
                                        @else
                                            {{-- ── LOTES (rediseñados, no tabla) ── --}}
                                            {{-- ── LOTES (compacto) ── --}}
                                            <div class="px-5 py-3.5" style="background-color: var(--bg-card);">

                                                {{-- Header compacto con total --}}
                                                <div class="flex items-center justify-between mb-2.5">
                                                    <div class="flex items-center gap-2">
                                                        <i class="fas fa-layer-group text-[10px] text-gray-400"></i>
                                                        <span
                                                            class="text-[10px] font-black uppercase tracking-wider text-gray-400">
                                                            Lotes
                                                        </span>
                                                        <span
                                                            class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-sky-100 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400">
                                                            FIFO
                                                        </span>
                                                    </div>

                                                    <div class="flex items-center gap-1.5 text-[10px]">
                                                        <span
                                                            class="text-gray-400 uppercase tracking-wider font-black">Total:</span>
                                                        <span
                                                            class="font-black text-emerald-600 dark:text-emerald-400 text-xs">{{ $stockTotalSede }}</span>
                                                        <span
                                                            class="text-gray-400 font-medium">{{ optional($detalle->unidad)->abreviatura ?? '' }}</span>
                                                    </div>
                                                </div>

                                                {{-- Chips horizontales de lotes --}}
                                                <div class="flex flex-wrap gap-1.5">
                                                    @foreach ($lotesDisponibles as $idx => $lote)
                                                        @php
                                                            $stockLote =
                                                                (float) ($lote->inventarioSedeLotes->first()
                                                                    ->cantidad ?? 0);
                                                            $esPrimero = $idx === 0;
                                                            $vence = $lote->fecha_vencimiento
                                                                ? \Carbon\Carbon::parse(
                                                                    $lote->fecha_vencimiento,
                                                                )->format('m/y')
                                                                : '—';
                                                        @endphp

                                                        <div
                                                            class="inline-flex items-center gap-2 pl-1.5 pr-2.5 py-1 rounded-lg border text-[11px]
                {{ $esPrimero
                    ? 'border-sky-300 dark:border-sky-800 bg-sky-50 dark:bg-sky-950/40'
                    : 'border-gray-200 dark:border-gray-800 bg-gray-50/60 dark:bg-gray-800/30' }}">

                                                            {{-- Punto numerado compacto --}}
                                                            <span
                                                                class="w-4 h-4 rounded-full flex items-center justify-center text-[8px] font-black
                    {{ $esPrimero ? 'bg-sky-600 text-white' : 'bg-gray-300 dark:bg-gray-700 text-gray-600 dark:text-gray-300' }}">
                                                                {{ $idx + 1 }}
                                                            </span>

                                                            {{-- Info del lote --}}
                                                            <span
                                                                class="font-bold {{ $esPrimero ? 'text-sky-700 dark:text-sky-300' : 'text-gray-600 dark:text-gray-300' }}">
                                                                {{ $lote->codigo_lote }}
                                                            </span>

                                                            <span
                                                                class="text-gray-400 dark:text-gray-500 text-[10px]">·</span>

                                                            <span class="text-gray-500 dark:text-gray-400 text-[10px]">
                                                                vence {{ $vence }}
                                                            </span>

                                                            <span
                                                                class="text-gray-400 dark:text-gray-500 text-[10px]">·</span>

                                                            {{-- Stock --}}
                                                            <span
                                                                class="font-black {{ $esPrimero ? 'text-sky-600 dark:text-sky-400' : 'text-gray-700 dark:text-gray-200' }}">
                                                                {{ $stockLote }}
                                                            </span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>

                                            {{-- ── INPUTS ── --}}
                                            <div x-show="dispensar" x-collapse
                                                class="grid grid-cols-1 md:grid-cols-2 gap-3 px-5 py-4 border-t"
                                                style="background-color: rgba(0,0,0,0.015); border-color: var(--border-color);">

                                                <div>
                                                    <label
                                                        class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-1.5">
                                                        Cantidad a entregar
                                                    </label>
                                                    <div class="relative">
                                                        <i
                                                            class="fas fa-prescription-bottle absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                                                        <input type="number"
                                                            name="items[{{ $detalle->id }}][cantidad]"
                                                            x-model.number="cantidad" :disabled="!dispensar"
                                                            min="0.01" :max="maxCantidad" step="0.01"
                                                            style="background-color: var(--bg-card); color: var(--text-main); border-color: var(--border-color);"
                                                            class="w-full pl-9 pr-3 py-2.5 text-sm font-bold rounded-xl border focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all">
                                                    </div>

                                                    <div class="flex items-center justify-between mt-1.5 px-0.5">
                                                        <p
                                                            class="text-[10px] text-gray-500 dark:text-gray-400 font-medium">
                                                            Máximo: <span x-text="maxCantidad"
                                                                class="font-bold text-gray-700 dark:text-gray-200"></span>
                                                        </p>
                                                        <template x-if="excedeStock">
                                                            <p
                                                                class="text-[10px] text-rose-600 dark:text-rose-400 font-semibold">
                                                                <i class="fas fa-exclamation-triangle text-[9px]"></i>
                                                                Supera el stock
                                                            </p>
                                                        </template>
                                                    </div>
                                                </div>

                                                <div>
                                                    <label
                                                        class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-1.5">
                                                        Observación <span
                                                            class="normal-case font-medium text-gray-400 dark:text-gray-500">(opcional)</span>
                                                    </label>
                                                    <div class="relative">
                                                        <i
                                                            class="fas fa-comment-medical absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                                                        <input type="text"
                                                            name="items[{{ $detalle->id }}][observaciones]"
                                                            :disabled="!dispensar" placeholder="Ej: Entrega parcial"
                                                            style="background-color: var(--bg-card); color: var(--text-main); border-color: var(--border-color);"
                                                            class="w-full pl-9 pr-3 py-2.5 text-sm font-medium rounded-xl border focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all">
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- ── Botones ── --}}
                        <div class="p-4 sm:p-5 rounded-2xl border shadow-sm flex items-center justify-between gap-3"
                            style="background-color: var(--bg-card); border-color: var(--border-color);">
                            <a href="{{ route('admin.salud.movimientos.consultas.index') }}"
                                class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl border text-sm font-bold hover:bg-gray-50 dark:hover:bg-white/5 transition-all"
                                style="border-color: var(--border-color); color: var(--text-main);">
                                Cancelar
                            </a>

                            <button type="button" @click="validarYConfirmar()"
                                class="rd-submit-btn inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-bold text-sm shadow-md shadow-sky-600/20 active:scale-95 transition-all">
                                <i class="fas fa-check-circle text-xs"></i> Registrar y Finalizar Atención
                            </button>
                        </div>
                    </form>
                @endif

                {{-- ── MODAL ── --}}
                <div x-show="showModal" x-cloak x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
                    @keydown.escape.window="showModal = false">

                    <div class="relative w-full max-w-md rounded-2xl shadow-xl border p-6 text-center"
                        style="background-color: var(--bg-card); border-color: var(--border-color);"
                        @click.away="showModal = false">

                        <div class="w-12 h-12 rounded-full inline-flex items-center justify-center mb-4"
                            :class="modalType === 'warning' ?
                                'bg-amber-100 text-amber-600 dark:bg-amber-950/60 dark:text-amber-400' :
                                'bg-sky-100 text-sky-600 dark:bg-sky-950/60 dark:text-sky-400'">
                            <i class="fas"
                                :class="modalType === 'warning' ? 'fa-exclamation-triangle text-xl' :
                                    'fa-question-circle text-xl'"></i>
                        </div>

                        <h3 class="text-base font-extrabold mb-2" style="color: var(--text-main);"
                            x-text="modalTitle"></h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-6 leading-relaxed"
                            x-text="modalMessage"></p>

                        <div class="flex items-center justify-center gap-3">
                            <template x-if="modalType === 'warning'">
                                <button type="button" @click="showModal = false"
                                    class="px-5 py-2 rounded-xl bg-gray-200 dark:bg-gray-800 text-xs font-bold transition-all">
                                    Entendido
                                </button>
                            </template>

                            <template x-if="modalType === 'confirm'">
                                <div class="flex gap-3 w-full">
                                    <button type="button" @click="showModal = false"
                                        class="flex-1 py-2.5 rounded-xl border text-xs font-bold transition-all"
                                        style="border-color: var(--border-color); color: var(--text-main);">
                                        Cancelar
                                    </button>
                                    <button type="button" @click="submitForm()"
                                        class="flex-1 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs shadow-md shadow-sky-600/20 transition-all">
                                        Confirmar y Finalizar
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
