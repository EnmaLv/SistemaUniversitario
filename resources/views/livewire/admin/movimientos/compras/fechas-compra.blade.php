<div>
    <form id="guardar-fechas-{{ $compra->id }}" wire:submit.prevent="guardar">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[560px] text-left text-sm">
                <thead>
                    <tr class="border-b text-[11px] font-black uppercase tracking-wider"
                        style="border-color:var(--border-color);color:var(--text-main);">
                        <th class="px-4 py-3">Producto</th>
                        <th class="px-4 py-3">Fecha de vencimiento</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($compra->detalleCompras as $detalle)
                        <tr class="border-b" style="border-color:var(--border-color);">
                            <td class="px-4 py-4 font-bold" style="color:var(--text-main);">
                                {{ $detalle->producto->nombre }}
                            </td>
                            <td class="px-4 py-4">
                                <input type="date"
                                    wire:model="fechas.{{ $detalle->id }}.fecha_vencimiento"
                                    min="{{ now()->addDay()->format('Y-m-d') }}"
                                    class="w-full rounded-xl border px-4 py-2.5 text-sm outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20"
                                    style="background:var(--input-bg);border-color:var(--border-color);color:var(--text-main);">
                                @error("fechas.$detalle->id.fecha_vencimiento")
                                    <p class="mt-2 text-sm font-semibold text-red-500">{{ $message }}</p>
                                @enderror
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </form>

    <div class="mt-5 flex flex-wrap justify-end gap-2 border-t pt-4" style="border-color:var(--border-color);">
        <button type="submit" form="guardar-fechas-{{ $compra->id }}" wire:loading.attr="disabled"
            class="inline-flex items-center gap-2 rounded-xl border border-red-600 bg-transparent px-5 py-2.5 text-sm font-extrabold text-red-300 shadow-sm transition hover:bg-red-950/50 hover:text-red-200 active:scale-95 disabled:cursor-not-allowed disabled:opacity-50">
            <i class="fas fa-save text-xs text-red-400"></i>
            Guardar fechas de vencimiento
        </button>

        <form action="{{ route('admin.movimientos.compras.finalizarCompra', $compra) }}" method="POST">
            @csrf
            <button type="submit"
                class="inline-flex items-center gap-2 rounded-xl border border-red-700 bg-red-700 px-5 py-2.5 text-sm font-extrabold text-white shadow-lg transition hover:bg-red-800 active:scale-95">
                <i class="fas fa-check text-xs"></i>
                Finalizar requisición
            </button>
        </form>
    </div>
</div>
