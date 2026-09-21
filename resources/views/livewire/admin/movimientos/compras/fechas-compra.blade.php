<div>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-5">
        <div>
            <h4 class="text-sm font-extrabold tracking-tight" style="color: var(--text-main);">
                Fechas de vencimiento
            </h4>
            <p class="text-[11px] text-gray-400 font-medium mt-0.5">
                Registra la fecha de vencimiento correspondiente a cada producto recibido.
            </p>
        </div>
        <div
            class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-amber-50 dark:bg-amber-950/20 text-amber-600 dark:text-amber-400 text-[10px] font-bold">
            <i class="fas fa-calendar-alt"></i>
            Fecha obligatoria
        </div>
    </div>

    <div class="overflow-x-auto rounded-2xl border" style="border-color: var(--border-color);">
        <table class="w-full text-sm">
            <thead
                style="
                    background-color: rgba(0,0,0,0.025);
                    border-bottom: 1px solid var(--border-color);
                ">
                <tr>
                    <th class="px-4 py-3 text-left text-[10px] uppercase tracking-wider font-black text-gray-500 dark:text-gray-400">
                        Producto
                    </th>
                    <th class="px-4 py-3 text-left text-[10px] uppercase tracking-wider font-black text-gray-500 dark:text-gray-400">
                        Lote
                    </th>
                    <th class="px-4 py-3 text-left text-[10px] uppercase tracking-wider font-black text-gray-500 dark:text-gray-400">
                        Fecha de vencimiento
                    </th>
                </tr>
            </thead>

            <tbody>
                @foreach ($compra->detalleCompras as $detalle)
                    @php
                        $esMedicina = (bool) ($detalle->producto->presentacion_id ?? false);
                    @endphp
                    <tr class="border-b last:border-b-0 transition-colors hover:bg-gray-50 dark:hover:bg-white/[0.02]"
                        style="border-color: var(--border-color);">
                        <td class="px-4 py-4">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-9 h-9 rounded-xl bg-red-50 dark:bg-red-950/30 text-red-800 dark:text-red-400 flex items-center justify-center shrink-0">
                                    <i class="fas {{ $esMedicina ? 'fa-pills' : 'fa-box' }} text-xs"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs font-bold truncate" style="color: var(--text-main);">
                                        {{ $detalle->producto->nombre }}
                                    </p>
                                    @if ($detalle->producto->codigo ?? false)
                                        <p class="text-[10px] text-gray-400 mt-0.5">
                                            {{ $detalle->producto->codigo }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-4">
                            <span
                                class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-gray-100 dark:bg-gray-800 text-[10px] font-mono font-semibold text-gray-600 dark:text-gray-300">
                                <i class="fas fa-barcode text-[9px] text-gray-400"></i>
                                {{ $detalle->lote->codigo_lote }}
                            </span>
                        </td>
                        <td class="px-4 py-4">
                            <div class="max-w-xs">
                                <div class="relative">
                                    <i
                                        class="fas fa-calendar-day absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none">
                                    </i>
                                    <input type="date" wire:model="fechas.{{ $detalle->id }}.fecha_vencimiento"
                                        min="{{ now()->addDay()->format('Y-m-d') }}"
                                        class="w-full pl-10 pr-3.5 py-2.5 text-sm font-medium rounded-xl border focus:outline-none focus:ring-2 focus:ring-red-800/20 focus:border-red-800 transition-all"
                                        style="
                                            background-color: rgba(0,0,0,0.02);
                                            border-color: var(--border-color);
                                            color: var(--text-main);
                                        ">
                                </div>

                                @error("fechas.$detalle->id.fecha_vencimiento")
                                    <p class="mt-1.5 text-xs font-semibold text-rose-500 flex items-center gap-1">
                                        <i class="fas fa-exclamation-circle text-[10px]"></i>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-5 pt-5 border-t flex flex-col sm:flex-row items-center justify-between gap-4"
        style="border-color: var(--border-color);">

        <div class="flex items-center gap-2 text-[11px] font-medium text-gray-400">
            <i class="fas fa-info-circle text-[10px]"></i>
            <span>
                Las fechas deben ser posteriores al día actual.
            </span>
        </div>

        <button type="button" wire:click="guardar" wire:loading.attr="disabled"
            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl bg-red-800 hover:bg-red-900 text-white font-bold text-sm shadow-md shadow-red-800/20 active:scale-95 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
            <span wire:loading.remove wire:target="guardar">
                <i class="fas fa-save text-xs mr-1"></i>
                Guardar fechas
            </span>
            <span wire:loading wire:target="guardar">
                <i class="fas fa-spinner fa-spin text-xs mr-1"></i>
                Guardando...
            </span>
        </button>
    </div>
</div>