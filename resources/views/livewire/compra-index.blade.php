<div class="overflow-hidden rounded-2xl border shadow-sm" style="background-color: var(--bg-card); border-color: var(--border-color);">
    <div class="border-b p-4 sm:p-5" style="border-color: var(--border-color);">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-lg font-extrabold" style="color: var(--text-main);">Requisiciones registradas</h2>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Consulta y administra las solicitudes de compra.</p>
            </div>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <div class="relative">
                    <i class="fas fa-search pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-gray-400"></i>
                    <input type="text" placeholder="Buscar requisición..." wire:model.live="buscar"
                        class="w-full rounded-xl border py-2.5 pl-10 pr-4 text-sm font-medium outline-none focus:border-red-600 focus:ring-2 focus:ring-red-600/20 sm:w-64"
                        style="background-color: var(--input-bg); border-color: var(--input-border); color: var(--text-main);">
                </div>
                <button type="button" id="compraFiltersToggle"
                    class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border px-4 text-xs font-extrabold transition hover:border-red-600 hover:bg-red-50 hover:text-red-700 dark:hover:bg-red-950/40"
                    style="border-color: var(--border-color); color: var(--text-main);">
                    <i class="fas fa-filter"></i> Filtros
                </button>
                <button type="button" id="pdfBtn"
                    class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-red-700 px-4 text-xs font-extrabold text-white shadow-md transition hover:bg-red-800 active:scale-95"
                    title="Exportar PDF">
                    <i class="fas fa-file-pdf"></i> PDF
                </button>
            </div>
        </div>
    </div>

    <div id="compraFilters" class="hidden border-b p-4" style="background-color: var(--input-bg); border-color: var(--border-color);">
        <form action="{{ route('admin.movimientos.compras.index') }}" method="GET" class="flex flex-col items-end gap-4 sm:flex-row">
            <div class="w-full sm:w-auto">
                <label for="fecha_desde" class="mb-2 block text-[11px] font-black uppercase tracking-wider text-gray-500 dark:text-gray-400">Desde</label>
                <input type="date" name="fecha_desde" id="fecha_desde" value="{{ request('fecha_desde') }}"
                    class="w-full rounded-xl border px-3 py-2.5 text-sm outline-none focus:border-red-600 focus:ring-2 focus:ring-red-600/20"
                    style="background-color: var(--bg-card); border-color: var(--input-border); color: var(--text-main);">
            </div>
            <div class="w-full sm:w-auto">
                <label for="fecha_hasta" class="mb-2 block text-[11px] font-black uppercase tracking-wider text-gray-500 dark:text-gray-400">Hasta</label>
                <input type="date" name="fecha_hasta" id="fecha_hasta" value="{{ request('fecha_hasta') }}"
                    class="w-full rounded-xl border px-3 py-2.5 text-sm outline-none focus:border-red-600 focus:ring-2 focus:ring-red-600/20"
                    style="background-color: var(--bg-card); border-color: var(--input-border); color: var(--text-main);">
            </div>
            <div class="flex gap-2">
                <button class="rounded-xl bg-red-700 px-4 py-2.5 text-xs font-extrabold text-white hover:bg-red-800" type="submit">Aplicar</button>
                <a href="{{ route('admin.movimientos.compras.index') }}" class="rounded-xl border px-4 py-2.5 text-xs font-extrabold hover:border-red-600 hover:text-red-600"
                    style="border-color: var(--border-color); color: var(--text-main);">Limpiar</a>
            </div>
        </form>
    </div>

    <div class="overflow-x-auto" id="printArea">
        <table class="w-full min-w-[850px] border-collapse text-left">
            <thead>
                <tr class="border-b text-[11px] font-black uppercase tracking-wider"
                    style="background-color: var(--input-bg); border-color: var(--border-color); color: var(--text-main);">
                    <th class="px-5 py-4 text-center">#</th>
                    <th class="px-5 py-4">Proveedor</th>
                    <th class="px-5 py-4">Fecha de la requisición</th>
                    <th class="px-5 py-4 text-right">Total</th>
                    <th class="px-5 py-4 text-center">Estado</th>
                    <th class="px-5 py-4 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y text-sm" style="divide-color: var(--border-color);">
                @forelse($compras as $compra)
                    <tr class="transition hover:bg-red-50/40 dark:hover:bg-red-950/20">
                        <td class="px-5 py-4 text-center font-bold text-gray-400">{{ ($compras->currentPage() - 1) * $compras->perPage() + $loop->iteration }}</td>
                        <td class="px-5 py-4 font-bold" style="color: var(--text-main);">{{ $compra->proveedor_nombre }}</td>
                        <td class="px-5 py-4 text-gray-500 dark:text-gray-400">{{ $compra->fecha }}</td>
                        <td class="px-5 py-4 text-right font-bold" style="color: var(--text-main);">{{ number_format($compra->total, 2, ',', '.') }} Bs.</td>
                        <td class="px-5 py-4 text-center">
                            @if ($compra->estado == 'Pendiente')
                                <span class="inline-flex rounded-lg border border-rose-200 bg-rose-50 px-3 py-1 text-[10px] font-black text-rose-600 dark:border-rose-900 dark:bg-rose-950/40 dark:text-rose-400">Pendiente</span>
                            @elseif ($compra->estado == 'Enviado al proveedor')
                                <span class="inline-flex rounded-lg border border-amber-200 bg-amber-50 px-3 py-1 text-[10px] font-black text-amber-600 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-400">En espera</span>
                            @else
                                <span class="inline-flex rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1 text-[10px] font-black text-emerald-600 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-400">Finalizada</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-center">
                            <div class="flex items-center justify-center gap-1">
                                @if ($compra->estado == 'Pendiente' || $compra->estado == 'Enviado al proveedor')
                                    <a href="{{ url('admin/movimientos/compras/' . $compra->id . '/edit') }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition hover:bg-amber-100 hover:text-amber-600 dark:hover:bg-amber-950/40" title="Continuar"><i class="fas fa-arrow-right text-xs"></i></a>
                                @endif
                                <a href="{{ url('admin/movimientos/compras/' . $compra->id) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition hover:bg-red-100 hover:text-red-600 dark:hover:bg-red-950/40" title="Ver detalles"><i class="fas fa-eye text-xs"></i></a>
                                @if ($compra->estado == 'Pendiente' || $compra->estado == 'Enviado al proveedor')
                                    <form action="{{ url('admin/movimientos/compras/' . $compra->id) }}" method="POST" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition hover:bg-rose-100 hover:text-rose-600 dark:hover:bg-rose-950/40" onclick="confirmDelete(event, this)" title="Inactivar"><i class="fas fa-trash text-xs"></i></button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-14 text-center text-sm font-bold text-gray-400"><i class="fas fa-clipboard-list mb-3 block text-3xl text-gray-300 dark:text-gray-700"></i>No hay requisiciones registradas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="flex justify-center border-t p-4" style="border-color: var(--border-color);">
        {{ $compras->onEachSide(1)->links('components.pagination-livewire') }}
    </div>
</div>

<script>
    document.getElementById('compraFiltersToggle')?.addEventListener('click', function () {
        document.getElementById('compraFilters')?.classList.toggle('hidden');
    });

    function confirmDelete(event, button) {
        event.preventDefault();
        const modal = window.AppModal
            ? window.AppModal.confirm('¿Inactivar requisición?', 'La requisición dejará de estar disponible.')
            : Promise.resolve(window.confirm('¿Desea inactivar la requisición?'));
        modal.then((result) => {
            if (result) button.closest('form').submit();
        });
    }
</script>
