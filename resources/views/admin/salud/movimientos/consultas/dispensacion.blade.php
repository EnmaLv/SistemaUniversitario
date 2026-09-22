<x-app-layout>
    <div class="pt-6 pb-16 min-h-[calc(100vh-4rem)]">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

            @include('components.alert')

            {{-- Encabezado --}}
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

            {{-- Resumen de la Consulta y Receta --}}
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

            {{-- 1. ENTREGAS PREVIAS --}}
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

                    <div class="rounded-xl border overflow-hidden text-left" style="border-color: var(--border-color);">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr
                                    class="bg-gray-50/50 dark:bg-black/20 text-[10px] font-black uppercase tracking-wider text-gray-400">
                                    <th class="px-4 py-3">Fecha</th>
                                    <th class="px-4 py-3">Producto</th>
                                    <th class="px-4 py-3 text-center">Cantidad Entregada</th>
                                    <th class="px-4 py-3">Lote</th>
                                    <th class="px-4 py-3">Entregado Por</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800/60">
                                @foreach ($consulta->receta->detalles as $detalle)
                                    @foreach ($detalle->dispensaciones as $disp)
                                        <tr>
                                            <td class="px-4 py-2.5 text-gray-500 dark:text-gray-400">
                                                {{ optional($disp->fecha)->format('d/m/Y') }}
                                            </td>
                                            <td class="px-4 py-2.5 font-bold" style="color: var(--text-main);">
                                                {{ optional($detalle->producto)->nombre ?? '—' }}
                                            </td>
                                            <td class="px-4 py-2.5 text-center font-medium">
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400">
                                                    {{ $disp->cantidad }} {{ optional($detalle->unidad)->nombre }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-2.5 text-gray-500 dark:text-gray-400">
                                                {{ optional($disp->lote)->codigo_lote ?? '—' }}
                                            </td>
                                            <td class="px-4 py-2.5 text-gray-500 dark:text-gray-400">
                                                {{ optional(optional($disp->usuario)->persona)->nombre_persona ?? (optional($disp->usuario)->username ?? '—') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- 2. FORMULARIO --}}
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
                    this.modalTitle = '¿Confirmar dispensación y finalizar?';

                    // 🔎 Validar que cada ítem marcado tenga lote
                    let itemsSinLote = [];
                    document.querySelectorAll('[data-item-dispensar]:checked').forEach(el => {
                        const itemId = el.dataset.itemId;
                        const sel = document.querySelector(`select[name='items[${itemId}][lote_id]']`);
                        const cantInput = document.querySelector(`input[name='items[${itemId}][cantidad]']`);
                        if (!sel || !sel.value) {
                            itemsSinLote.push(el.dataset.itemNombre);
                        }
                    });

                    if (itemsSinLote.length > 0) {
                        this.modalType = 'warning';
                        this.modalTitle = 'Falta seleccionar lote';
                        this.modalMessage = 'Debe seleccionar un lote para: ' + itemsSinLote.join(', ') + '.';
                        this.showModal = true;
                        return;
                    }

                    if (this.checkedCount <= 0) {
                        this.modalMessage = 'No ha seleccionado ningún medicamento a entregar. La consulta se dará por finalizada sin entregas. ¿Desea continuar?';
                    } else if (this.checkedCount < this.totalPending) {
                        this.modalMessage = `Está registrando la entrega de ${this.checkedCount} de ${this.totalPending} medicamentos. Los no seleccionados NO quedarán pendientes. Una vez guardado, la consulta no podrá editarse. ¿Desea continuar?`;
                    } else {
                        this.modalMessage = '¿Está seguro de registrar la dispensación de los medicamentos seleccionados? Una vez confirmada, la consulta finalizará y no se podrá editar. ¿Desea continuar?';
                    }
                    this.showModal = true;
                },

                submitForm() {
                    $refs.dispensacionForm.submit();
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
                        <div class="mb-4 p-3 rounded-xl border flex items-center gap-3 text-xs"
                            style="background-color: rgba(14,165,233,0.06); border-color: rgba(14,165,233,0.2);">
                            <i class="fas fa-info-circle text-sky-600"></i>
                            <span style="color: var(--text-main);">
                                Se descontará del inventario de la <strong>sede actual</strong>. Solo se muestran los
                                lotes con stock disponible.
                            </span>
                        </div>

                        {{-- Card: Medicamentos Recetados --}}
                        <div style="background-color: var(--bg-card); border-color: var(--border-color);"
                            class="rounded-2xl border shadow-sm p-6 mb-6">
                            <div class="flex items-center gap-2.5 mb-5">
                                <div
                                    class="w-8 h-8 rounded-xl bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                                    <i class="fas fa-boxes text-xs"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm font-extrabold tracking-tight"
                                        style="color: var(--text-main);">
                                        Medicamentos Recetados
                                    </h3>
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400">
                                        Los medicamentos desmarcados no quedarán pendientes. Al guardar, la consulta
                                        se cierra.
                                    </p>
                                </div>
                            </div>

                            <div class="space-y-4">
                                @foreach ($detallesPendientes as $detalle)
                                    @php
                                        $lotesDisponibles = $lotesPorProducto[$detalle->producto_id] ?? collect();

                                        // Mapa de lote_id => stock en sede actual
                                        $stockPorLote = $lotesDisponibles->mapWithKeys(function ($lote) use ($sedeId) {
                                            $inv = $lote->inventarioSedeLotes->firstWhere('sede_id', $sedeId);
                                            return [$lote->id => (float) ($inv->cantidad ?? 0)];
                                        });
                                    @endphp

                                    <div x-data="{
                                        dispensar: true,
                                        loteId: '',
                                        cantidad: {{ $detalle->cantidad_pendiente }},
                                        stockPorLote: {{ \Illuminate\Support\Js::from($stockPorLote) }},
                                        pendiente: {{ $detalle->cantidad_pendiente }},

                                        get stockLote() {
                                            return this.loteId ? (this.stockPorLote[this.loteId] ?? 0) : 0;
                                        },
                                        get maxCantidad() {
                                            return Math.min(this.pendiente, this.stockLote || this.pendiente);
                                        },
                                        get sinStock() {
                                            return this.loteId && this.stockLote <= 0;
                                        },
                                        get excedeStock() {
                                            return this.loteId && this.cantidad > this.stockLote;
                                        }
                                    }" class="rounded-xl border overflow-hidden transition-all"
                                        :class="{ 'opacity-60': !dispensar }"
                                        style="border-color: var(--border-color);">

                                        {{-- Header --}}
                                        <label
                                            class="flex items-start gap-3 cursor-pointer px-4 py-3 border-b"
                                            style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color);">
                                            <input type="hidden"
                                                name="items[{{ $detalle->id }}][dispensar]" value="0">
                                            <input type="checkbox"
                                                name="items[{{ $detalle->id }}][dispensar]" value="1"
                                                x-model="dispensar" data-item-dispensar
                                                data-item-id="{{ $detalle->id }}"
                                                data-item-nombre="{{ optional($detalle->producto)->nombre ?? $detalle->producto_id }}"
                                                @change="dispensar ? checkedCount++ : checkedCount--"
                                                class="mt-1 rounded border-gray-300 text-sky-600 focus:ring-sky-500">

                                            <div class="flex-1">
                                                <div class="flex items-center justify-between gap-2 flex-wrap">
                                                    <span class="text-sm font-bold" style="color: var(--text-main);">
                                                        {{ optional($detalle->producto)->nombre ?? '—' }}
                                                    </span>
                                                    <span
                                                        class="inline-flex items-center px-2.5 py-1 text-[10px] font-black rounded-lg bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-900">
                                                        Pendiente: {{ $detalle->cantidad_pendiente }}
                                                        {{ optional($detalle->unidad)->nombre }}
                                                    </span>
                                                </div>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                                    <strong>Frecuencia:</strong> {{ $detalle->frecuencia }}
                                                    @if ($detalle->observaciones)
                                                        · <em>{{ $detalle->observaciones }}</em>
                                                    @endif
                                                </p>
                                            </div>
                                        </label>

                                        {{-- Body --}}
                                        <div x-show="dispensar" x-collapse
                                            class="grid grid-cols-1 md:grid-cols-3 gap-3 p-4"
                                            style="background-color: var(--bg-card);">

                                            {{-- Lote --}}
                                            <div>
                                                <label
                                                    class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-1">
                                                    Lote <span class="text-rose-500">*</span>
                                                </label>

                                                @if ($lotesDisponibles->isEmpty())
                                                    <div
                                                        class="px-3 py-2 rounded-lg border text-[11px] font-semibold bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-900 flex items-center gap-2">
                                                        <i class="fas fa-exclamation-triangle"></i>
                                                        Sin stock disponible en esta sede
                                                    </div>
                                                    <input type="hidden"
                                                        name="items[{{ $detalle->id }}][lote_id]" value="">
                                                @else
                                                    <select name="items[{{ $detalle->id }}][lote_id]"
                                                        x-model="loteId" :disabled="!dispensar"
                                                        style="background-color: rgba(0,0,0,0.02); color: var(--text-main); border-color: var(--border-color);"
                                                        class="w-full px-2.5 py-2 text-xs font-medium rounded-lg border focus:outline-none focus:ring-2 focus:ring-sky-500 transition-all">
                                                        <option value="">-- Seleccionar Lote --</option>
                                                        @foreach ($lotesDisponibles as $lote)
                                                            @php
                                                                $stockSede = (float) ($stockPorLote[$lote->id] ?? 0);
                                                            @endphp
                                                            <option value="{{ $lote->id }}">
                                                                {{ $lote->codigo_lote }}
                                                                @if ($lote->fecha_vencimiento)
                                                                    · vence
                                                                    {{ \Carbon\Carbon::parse($lote->fecha_vencimiento)->format('d/m/Y') }}
                                                                @endif
                                                                · Stock: {{ $stockSede }}
                                                                {{ optional($detalle->unidad)->abreviatura ?? '' }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                @endif
                                            </div>

                                            {{-- Cantidad --}}
                                            <div>
                                                <label
                                                    class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-1">
                                                    Cantidad a Entregar
                                                </label>
                                                <input type="number"
                                                    name="items[{{ $detalle->id }}][cantidad]"
                                                    x-model.number="cantidad"
                                                    :disabled="!dispensar"
                                                    min="0.01"
                                                    :max="maxCantidad"
                                                    step="0.01"
                                                    style="background-color: rgba(0,0,0,0.02); color: var(--text-main); border-color: var(--border-color);"
                                                    class="w-full px-2.5 py-2 text-xs font-medium rounded-lg border focus:outline-none focus:ring-2 focus:ring-sky-500 transition-all">

                                                <template x-if="dispensar && loteId && stockLote > 0">
                                                    <p class="mt-1 text-[10px] text-emerald-600 dark:text-emerald-400 font-semibold">
                                                        Disponible en lote: <span x-text="stockLote"></span>
                                                    </p>
                                                </template>
                                                <template x-if="excedeStock">
                                                    <p class="mt-1 text-[10px] text-rose-600 dark:text-rose-400 font-semibold">
                                                        ⚠ La cantidad supera el stock del lote
                                                    </p>
                                                </template>
                                            </div>

                                            {{-- Observación --}}
                                            <div>
                                                <label
                                                    class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-1">
                                                    Observación
                                                </label>
                                                <input type="text"
                                                    name="items[{{ $detalle->id }}][observaciones]"
                                                    :disabled="!dispensar" placeholder="Opcional..."
                                                    style="background-color: rgba(0,0,0,0.02); color: var(--text-main); border-color: var(--border-color);"
                                                    class="w-full px-2.5 py-2 text-xs font-medium rounded-lg border focus:outline-none focus:ring-2 focus:ring-sky-500 transition-all">
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Botones --}}
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

                {{-- MODAL --}}
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