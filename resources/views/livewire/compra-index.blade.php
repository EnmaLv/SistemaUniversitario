<div x-data="{ openFilters: false }">

    {{-- Card de Buscador y Filtros --}}
    <div style="background-color: var(--bg-card); border-color: var(--border-color);"
        class="p-2.5 rounded-2xl border shadow-sm mb-3 flex flex-col lg:flex-row lg:items-center gap-4">

        {{-- Buscador Livewire --}}
        <div class="relative w-full">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                <i class="fas fa-search text-sm"></i>
            </div>
            <input type="text" wire:model.live="buscar" placeholder="Buscar requisición..."
                style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                class="w-full pl-10 pr-4 py-2.5 rounded-xl border text-sm font-medium focus:outline-none focus:ring-2 focus:ring-sky-500 transition-all">
        </div>

        {{-- Botón Toggle Filtros --}}
        <div class="flex items-center gap-3 shrink-0">
            <button type="button" @click="openFilters = !openFilters"
                class="w-10 h-10 flex items-center justify-center rounded-xl border border-gray-200 dark:border-gray-800/50 text-gray-600 dark:text-gray-300 hover:bg-red-50 dark:hover:bg-red-950/50 hover:text-red-600 hover:border-red-300 dark:hover:border-red-800 shadow-sm active:scale-95 transition-all"
                title="Filtros">
                <i class="fas fa-filter text-sm"></i>
            </button>
        </div>
    </div>

    {{-- Filtros Colapsables --}}
    <div x-show="openFilters" x-transition style="background-color: var(--bg-card); border-color: var(--border-color);"
        class="p-4 rounded-2xl border shadow-sm mb-3">
        <form action="{{ route('admin.movimientos.compras.index') }}" method="GET"
            class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">

            <div>
                <label
                    class="block text-[11px] font-black uppercase tracking-wider mb-2 ml-1 text-gray-500 dark:text-gray-400">
                    Desde
                </label>
                <input type="date" name="fecha_desde" id="fecha_desde" value="{{ request('fecha_desde') }}"
                    style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                    class="w-full px-3 py-2 rounded-xl border text-sm font-medium focus:outline-none focus:ring-2 focus:ring-sky-500 transition-all">
            </div>

            <div>
                <label
                    class="block text-[11px] font-black uppercase tracking-wider mb-2 ml-1 text-gray-500 dark:text-gray-400">
                    Hasta
                </label>
                <input type="date" name="fecha_hasta" id="fecha_hasta" value="{{ request('fecha_hasta') }}"
                    style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                    class="w-full px-3 py-2 rounded-xl border text-sm font-medium focus:outline-none focus:ring-2 focus:ring-sky-500 transition-all">
            </div>

            <div class="flex gap-2">
                <button type="submit"
                    class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl bg-red-700 hover:bg-red-800 text-white font-extrabold text-xs shadow-md shadow-red-600/20 active:scale-95 transition-all">
                    <i class="fas fa-check text-[10px]"></i> Aplicar
                </button>
                <a href="{{ route('admin.movimientos.compras.index') }}"
                    class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-800/80 text-gray-700 dark:text-gray-300 font-extrabold text-xs hover:bg-gray-200 dark:hover:bg-gray-700 active:scale-95 transition-all">
                    <i class="fas fa-times text-[10px]"></i> Limpiar
                </a>
            </div>
        </form>
    </div>

    {{-- Card de la Tabla --}}
    <div style="background-color: var(--bg-card); border-color: var(--border-color);"
        class="rounded-2xl border shadow-sm overflow-hidden">

        <div class="overflow-x-auto" id="printArea">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr
                        class="bg-gray-50/50 dark:bg-black/20 border-b border-gray-100 dark:border-gray-800 text-[13px] font-black uppercase tracking-wider">
                        <th class="px-6 py-4 text-center">#</th>
                        <th class="px-6 py-4 text-center">Proveedor</th>
                        <th class="px-6 py-4 text-center">Fecha de Requisición</th>
                        <th class="px-6 py-4 text-center">Total</th>
                        <th class="px-6 py-4 text-center">Estado</th>
                        <th class="px-6 py-4 text-center">Acciones</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100 dark:divide-gray-800/60 text-xs font-medium">
                    @forelse($compras as $compra)
                        <x-table-row :id="$compra->id"
                            class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition-colors">
                            <td class="px-6 py-4 text-center font-bold text-gray-500 whitespace-nowrap">
                                {{ ($compras->currentPage() - 1) * $compras->perPage() + $loop->iteration }}
                            </td>

                            <td class="px-6 py-4 text-center whitespace-nowrap font-bold"
                                style="color: var(--text-main);">
                                {{ $compra->proveedor_nombre }}
                            </td>

                            <td class="px-6 py-4 text-center whitespace-nowrap text-gray-500 dark:text-gray-400">
                                {{ $compra->fecha }}
                            </td>

                            <td class="px-6 py-4 text-center whitespace-nowrap font-black"
                                style="color: var(--text-main);">
                                {{ number_format($compra->total, 2, ',', '.') }} Bs.
                            </td>

                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                @if ($compra->estado == 'Pendiente')
                                    <span
                                        class="inline-flex items-center gap-1 px-3 py-1 text-[11px] font-black rounded-lg bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-900">
                                        <i class="fas fa-clock text-[10px]"></i> Pendiente
                                    </span>
                                @elseif ($compra->estado == 'Enviado al proveedor')
                                    <span
                                        class="inline-flex items-center gap-1 px-3 py-1 text-[11px] font-black rounded-lg bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-900">
                                        <i class="fas fa-hourglass-half text-[10px]"></i> En espera
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1 px-3 py-1 text-[11px] font-black rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900">
                                        <i class="fas fa-check-circle text-[10px]"></i> Finalizada
                                    </span>
                                @endif
                            </td>

                            <x-table-actions :id="$compra->id" baseUrl="admin/movimientos/compras" :edit="in_array($compra->estado, ['Pendiente', 'Enviado al proveedor'])"
                                :toggle="in_array($compra->estado, ['Pendiente', 'Enviado al proveedor'])" :status="in_array($compra->estado, ['Pendiente', 'Enviado al proveedor']) ? 1 : null" class="text-center" />
                        </x-table-row>
                    @empty
                        <tr>
                            <td colspan="6"
                                class="px-6 py-12 text-center text-gray-400 dark:text-gray-500 font-bold text-xs uppercase tracking-wider">
                                <i
                                    class="fas fa-clipboard-list text-3xl mb-3 block text-gray-300 dark:text-gray-700"></i>
                                No hay Requisiciones registradas
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($compras->hasPages())
            <div class="p-4 border-t border-gray-100 dark:border-gray-800 flex justify-center">
                {{ $compras->onEachSide(1)->links('components.pagination-livewire') }}
            </div>
        @endif
    </div>

    <script>
        function confirmDelete(event, button) {
            event.preventDefault();
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Desea inactivar la compra?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, inactivar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    button.closest('form').submit();
                }
            });
        }
    </script>
</div>
