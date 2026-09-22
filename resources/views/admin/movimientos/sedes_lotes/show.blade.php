<x-app-layout>
    <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @include('components.alert')

            @php
                $sedeId = request()->segment(count(request()->segments()));
                $sedeNombre = $sedes->firstWhere('id', $sedeId)?->nombre ?? 'Sede';
            @endphp

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <div class="mb-2 flex items-center gap-2 text-[11px] font-black uppercase tracking-wider text-gray-400">
                        <a href="{{ route('admin.movimientos.sedes_lotes') }}"
                            class="transition hover:text-red-600">Existencia por sedes</a>
                        <i class="fas fa-chevron-right text-[9px]"></i>
                        <span>{{ $sedeNombre }}</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color: var(--text-main);">
                        Inventario de {{ $sedeNombre }}
                    </h1>
                    <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                        Lotes y productos disponibles en esta sede ·
                        {{ \Carbon\Carbon::now()->format('d/m/Y') }}
                    </p>
                </div>

                <a href="{{ route('admin.movimientos.sedes_lotes') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl border px-5 py-2.5 text-sm font-extrabold transition hover:border-red-500 hover:text-red-600"
                    style="border-color: var(--border-color); color: var(--text-main);">
                    <i class="fas fa-arrow-left text-xs"></i>
                    <span>Volver a sedes</span>
                </a>
            </div>

            <div style="background-color: var(--bg-card); border-color: var(--border-color);"
                class="p-2.5 rounded-2xl border shadow-sm mb-3 flex flex-col lg:flex-row lg:items-center gap-4">
                <form action="{{ route('admin.movimientos.sedes_lotes.show', $sedeId) }}" method="GET"
                    class="relative w-full">
                    @if(request()->has('estado'))
                        <input type="hidden" name="estado" value="{{ request('estado') }}">
                    @endif
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                        <i class="fas fa-search text-sm"></i>
                    </div>
                    <input type="text" name="buscar" value="{{ $buscar ?? '' }}"
                        placeholder="Buscar lote o producto..."
                        style="background-color: var(--input-bg); border-color: var(--border-color); color: var(--text-main);"
                        class="w-full pl-10 pr-4 py-2.5 rounded-xl border text-sm font-medium focus:outline-none focus:ring-2 focus:ring-red-500 transition-all">
                </form>

                <div class="flex items-center gap-3 shrink-0">
                    <div class="flex items-center gap-2 px-3 py-2 rounded-xl border"
                        style="border-color: var(--border-color);">
                        <span class="text-[11px] font-black uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Activos
                        </span>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="estadoToggle" class="sr-only peer"
                                {{ request('estado', 1) == 1 ? 'checked' : '' }}>
                            <span
                                class="w-10 h-6 bg-gray-300 dark:bg-gray-700 rounded-full peer peer-checked:bg-red-700 transition-colors relative after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-4">
                            </span>
                        </label>
                    </div>
                </div>
            </div>

            <div style="background-color: var(--bg-card); border-color: var(--border-color);"
                class="rounded-2xl border shadow-sm overflow-hidden">
                <div class="overflow-x-auto" id="printArea">
                    <table class="w-full min-w-[1000px] text-left border-collapse">
                        <thead>
                            <tr
                                class="bg-gray-50/50 dark:bg-black/20 border-b border-gray-100 dark:border-gray-800 text-[13px] font-black uppercase tracking-wider">
                                <th class="px-6 py-4 text-center" style="width:80px;">#</th>
                                <th class="px-6 py-4">Código de lote</th>
                                <th class="px-6 py-4">Producto</th>
                                <th class="px-6 py-4 text-right">Cantidad (U)</th>
                                <th class="px-6 py-4 text-right">Cantidad (g)</th>
                                <th class="px-6 py-4">Entrada</th>
                                <th class="px-6 py-4">Vencimiento</th>
                                <th class="px-6 py-4">Proveedor</th>
                                <th class="px-6 py-4 text-center" style="width:120px;">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="text-xs font-medium">
                            @forelse($sede as $loteSede)
                                <tr class="border-b transition-colors hover:bg-red-50/40 dark:hover:bg-red-950/20"
                                    style="border-color: var(--border-color);">
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        <span
                                            class="inline-flex items-center px-3 py-1 text-[12px] font-black rounded-lg text-gray-600 dark:text-gray-300 border border-gray-300 dark:border-gray-800">
                                            {{ ($sede->currentPage() - 1) * $sede->perPage() + $loop->iteration }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap font-bold" style="color:var(--text-main);">
                                        {{ $loteSede->lote->codigo_lote }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap font-bold" style="color:var(--text-main);">
                                        {{ $loteSede->lote->producto->nombre }}
                                    </td>
                                    <td class="px-6 py-4 text-right whitespace-nowrap font-bold" style="color:var(--text-main);">
                                        {{ $loteSede->cantidad }}
                                    </td>
                                    <td class="px-6 py-4 text-right whitespace-nowrap" style="color:var(--text-main);">
                                        {{ $loteSede->cantidad_convertida }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-500 dark:text-gray-400">
                                        {{ $loteSede->lote->fecha_entrada }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-500 dark:text-gray-400">
                                        {{ $loteSede->lote->fecha_vencimiento }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-500 dark:text-gray-400">
                                        {{ $loteSede->lote->proveedor->nombre }}
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        @if($loteSede->estado)
                                            <span
                                                class="inline-flex items-center gap-1 px-3 py-1 text-[10px] font-black rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900">
                                                <i class="fas fa-check-circle"></i> Activo
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center gap-1 px-3 py-1 text-[10px] font-black rounded-lg bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-900">
                                                <i class="fas fa-times-circle"></i> Inactivo
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9"
                                        class="px-6 py-12 text-center text-xs font-bold uppercase tracking-wider text-gray-400">
                                        No hay registros de inventario para esta sede.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($sede->hasPages())
                    <div class="flex justify-center border-t p-4" style="border-color:var(--border-color);">
                        {{ $sede->onEachSide(1)->appends(request()->query())->links('components.pagination') }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('js')
        <script>
            document.getElementById('estadoToggle')?.addEventListener('change', function() {
                const url = new URL(window.location.href);
                url.searchParams.set('estado', this.checked ? '1' : '0');
                window.location.href = url.toString();
            });
        </script>
    @endpush
</x-app-layout>
