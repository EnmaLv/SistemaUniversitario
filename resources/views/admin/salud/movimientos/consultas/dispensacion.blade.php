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
                
                {{-- Botón para regresar al Paso 2 (Recetación) --}}
                <a href="{{ route('admin.salud.movimientos.consultas.recetacion', $consulta) }}"
                    class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl border text-sm font-bold hover:bg-gray-50 dark:hover:bg-white/5 transition-all"
                    style="border-color: var(--border-color); color: var(--text-main);">
                    <i class="fas fa-arrow-left text-xs"></i> Volver a Recetacion (Paso 2)
                </a>
            </div>

            {{-- Stepper del Wizard --}}
            <x-consulta-stepper :step="3" />

            {{-- Resumen de la Consulta y Receta Médica --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">

                {{-- Info de la Consulta --}}
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

                {{-- Info de la Receta --}}
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

            {{-- Variables de entregas previas --}}
            @php
                $dispensacionesExistentes = $consulta->receta->detalles->flatMap->dispensaciones;
                $tieneDispensaciones = $dispensacionesExistentes->isNotEmpty();
                $detallesPendientes = $consulta->receta->detalles->filter(fn($d) => $d->cantidad_pendiente > 0);
                $detallesPendientesCount = $detallesPendientes->count();
            @endphp

            {{-- 1. TABLA DE MEDICAMENTOS ENTREGADOS PREVIAMENTE --}}
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

            {{-- 2. FORMULARIO CON MODAL DE CONFIRMACIÓN --}}
            <div x-data="{
                checkedCount: {{ $detallesPendientesCount }},
                totalPending: {{ $detallesPendientesCount }},
                showModal: false,
                modalType: 'confirm',
                modalTitle: '',
                modalMessage: '',
            
                validarYConfirmar() {
                    this.modalType = 'confirm';
                    this.modalTitle = '¿Confirmar dispensación y finalizar?';
            
                    if (this.checkedCount <= 0) {
                        this.modalMessage = 'No ha seleccionado ningún medicamento a entregar. No se registrará ninguna entrega y la consulta se dará por finalizada sin pendientes. Tenga en cuenta que una vez guardado, no se podrá editar ni realizar cambios en esta consulta. ¿Desea continuar?';
                    } else if (this.checkedCount < this.totalPending) {
                        this.modalMessage = `Está registrando la entrega de ${this.checkedCount} de ${this.totalPending} medicamentos. Los medicamentos no seleccionados NO quedarán pendientes. Tenga en cuenta que una vez guardado, la consulta quedará cerrada y no podrá ser editada. ¿Desea registrar y finalizar la atención?`;
                    } else {
                        this.modalMessage = '¿Está seguro de registrar la dispensación de los medicamentos seleccionados? Una vez confirmada la información, la consulta finalizará y no se podrá editar ni modificar. ¿Desea continuar?';
                    }
                    this.showModal = true;
                },
            
                submitForm() {
                    $refs.dispensacionForm.submit();
                }
            }">

                @if (!$consulta->receta->tieneItemsPendientes())
                    {{-- Caso: Todos los ítems fueron entregados --}}
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
                    {{-- Formulario para dispensar ítems --}}
                    <form x-ref="dispensacionForm"
                        action="{{ route('admin.salud.movimientos.consultas.dispensacion.store', $consulta) }}"
                        method="POST" class="rd-prevent-double-submit">
                        @csrf

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
                                        Los medicamentos desmarcados o no entregados no quedarán en estado pendiente.
                                    </p>
                                </div>
                            </div>

                            <div class="space-y-4">
                                @foreach ($detallesPendientes as $detalle)
                                    <div x-data="{ dispensar: true }"
                                        class="rounded-xl border overflow-hidden transition-all"
                                        :class="{ 'opacity-60': !dispensar }"
                                        style="border-color: var(--border-color);">

                                        {{-- Header del medicamento (checkbox + nombre + estado) --}}
                                        <label class="flex items-start gap-3 cursor-pointer px-4 py-3 border-b"
                                            style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color);">
                                            <input type="hidden" name="items[{{ $detalle->id }}][dispensar]"
                                                value="0">
                                            <input type="checkbox" name="items[{{ $detalle->id }}][dispensar]"
                                                value="1" x-model="dispensar"
                                                @change="dispensar ? checkedCount++ : checkedCount--"
                                                class="mt-1 rounded border-gray-300 text-sky-600 focus:ring-sky-500">

                                            <div class="flex-1">
                                                <div class="flex items-center justify-between gap-2">
                                                    <span class="text-sm font-bold" style="color: var(--text-main);">
                                                        {{ optional($detalle->producto)->nombre ?? '—' }}
                                                    </span>
                                                    <span
                                                        class="inline-flex items-center px-2.5 py-1 text-[10px] font-black rounded-lg bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-900">
                                                        Recetado: {{ $detalle->cantidad_pendiente }}
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

                                        <div x-show="dispensar" x-collapse
                                            class="grid grid-cols-1 md:grid-cols-3 gap-3 p-4"
                                            style="background-color: var(--bg-card);">
                                            <div>
                                                <label
                                                    class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-1">
                                                    Lote Disponible
                                                </label>
                                                @php $lotesDisponibles = $lotesPorProducto[$detalle->producto_id] ?? collect(); @endphp
                                                <select name="items[{{ $detalle->id }}][lote_id]"
                                                    :disabled="!dispensar"
                                                    style="background-color: rgba(0,0,0,0.02); color: var(--text-main); border-color: var(--border-color);"
                                                    class="w-full px-2.5 py-2 text-xs font-medium rounded-lg border focus:outline-none focus:ring-2 focus:ring-sky-500 transition-all">
                                                    <option value="">-- Seleccionar Lote (Opcional) --</option>
                                                    @foreach ($lotesDisponibles as $lote)
                                                        <option value="{{ $lote->id }}">
                                                            Lote: {{ $lote->codigo_lote }} (Stock:
                                                            {{ $lote->cantidad_actual }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div>
                                                <label
                                                    class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-1">
                                                    Cantidad a Entregar
                                                </label>
                                                <input type="number" name="items[{{ $detalle->id }}][cantidad]"
                                                    :disabled="!dispensar" value="{{ $detalle->cantidad_pendiente }}"
                                                    min="0.01" max="{{ $detalle->cantidad_pendiente }}"
                                                    step="0.01"
                                                    style="background-color: rgba(0,0,0,0.02); color: var(--text-main); border-color: var(--border-color);"
                                                    class="w-full px-2.5 py-2 text-xs font-medium rounded-lg border focus:outline-none focus:ring-2 focus:ring-sky-500 transition-all">
                                            </div>

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

                        {{-- Botones de Acción --}}
                        <div class="p-4 sm:p-5 rounded-2xl border shadow-sm flex items-center justify-between gap-3"
                            style="background-color: var(--bg-card); border-color: var(--border-color);">

                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.salud.movimientos.consultas.index') }}"
                                    class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl border text-sm font-bold hover:bg-gray-50 dark:hover:bg-white/5 transition-all"
                                    style="border-color: var(--border-color); color: var(--text-main);">
                                    Cancelar
                                </a>
                            </div>

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
