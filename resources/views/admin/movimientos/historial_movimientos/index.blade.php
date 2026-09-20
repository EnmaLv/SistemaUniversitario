<x-app-layout>
    <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @include('components.alert')

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color: var(--text-main);">
                        Historial de movimientos
                    </h1>
                    <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                        Bienvenido <span class="font-bold">{{ auth()->user()->persona->nombre_persona ?? auth()->user()->name }}</span> ·
                        {{ \Carbon\Carbon::now()->format('d/m/Y') }}
                    </p>
                </div>

                <button type="button" id="pdfBtn"
                    class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-red-800 hover:bg-red-900 text-white font-extrabold text-sm shadow-lg active:scale-95 transition-all">
                    <i class="fas fa-file-pdf text-xs"></i>
                    <span>Exportar PDF</span>
                </button>
            </div>

            <div style="background-color: var(--bg-card); border-color: var(--border-color);"
                class="rounded-2xl border shadow-sm mb-3 p-3">
                <form action="{{ route('admin.movimientos.historial_movimientos.index') }}" method="GET"
                    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 items-end gap-3">
                    <div class="lg:col-span-2">
                        <label for="buscar"
                            class="block mb-1.5 text-[11px] font-black uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Buscar lote
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                <i class="fas fa-search text-sm"></i>
                            </div>
                            <input type="text" name="buscar" id="buscar" value="{{ request('buscar') }}"
                                placeholder="Escriba el lote..."
                                style="background-color: var(--input-bg); border-color: var(--border-color); color: var(--text-main);"
                                class="w-full pl-10 pr-4 py-2.5 rounded-xl border text-sm font-medium focus:outline-none focus:ring-2 focus:ring-red-500 transition-all">
                        </div>
                    </div>

                    <div>
                        <label for="fecha_desde"
                            class="block mb-1.5 text-[11px] font-black uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Desde
                        </label>
                        <input type="date" name="fecha_desde" id="fecha_desde" value="{{ request('fecha_desde') }}"
                            style="background-color: var(--input-bg); border-color: var(--border-color); color: var(--text-main);"
                            class="w-full px-3 py-2.5 rounded-xl border text-sm font-medium focus:outline-none focus:ring-2 focus:ring-red-500 transition-all">
                    </div>

                    <div>
                        <label for="fecha_hasta"
                            class="block mb-1.5 text-[11px] font-black uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Hasta
                        </label>
                        <input type="date" name="fecha_hasta" id="fecha_hasta" value="{{ request('fecha_hasta') }}"
                            style="background-color: var(--input-bg); border-color: var(--border-color); color: var(--text-main);"
                            class="w-full px-3 py-2.5 rounded-xl border text-sm font-medium focus:outline-none focus:ring-2 focus:ring-red-500 transition-all">
                    </div>

                    <div>
                        <label for="tipo_movimiento"
                            class="block mb-1.5 text-[11px] font-black uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Tipo de movimiento
                        </label>
                        <select name="tipo_movimiento" id="tipo_movimiento"
                            style="background-color: var(--input-bg); border-color: var(--border-color); color: var(--text-main);"
                            class="w-full px-3 py-2.5 rounded-xl border text-sm font-medium focus:outline-none focus:ring-2 focus:ring-red-500 transition-all">
                            <option value="">Todos</option>
                            <option value="ENTRADA" {{ request('tipo_movimiento') === 'ENTRADA' ? 'selected' : '' }}>
                                Entrada
                            </option>
                            <option value="SALIDA" {{ request('tipo_movimiento') === 'SALIDA' ? 'selected' : '' }}>
                                Salida
                            </option>
                        </select>
                    </div>

                    <div class="sm:col-span-2 lg:col-span-5 flex justify-end gap-2">
                        <a href="{{ route('admin.movimientos.historial_movimientos.index') }}"
                            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border text-sm font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                            style="border-color: var(--border-color);">
                            <i class="fas fa-eraser text-xs"></i>
                            Limpiar
                        </a>
                        <button type="submit"
                            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-red-800 hover:bg-red-900 text-white text-sm font-bold transition-colors">
                            <i class="fas fa-filter text-xs"></i>
                            Aplicar filtros
                        </button>
                    </div>
                </form>
            </div>

            <div style="background-color: var(--bg-card); border-color: var(--border-color);"
                class="rounded-2xl border shadow-sm overflow-hidden">
                <div class="overflow-x-auto" id="printArea">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr
                                class="bg-gray-50/50 dark:bg-black/20 border-b border-gray-100 dark:border-gray-800 text-[13px] font-black uppercase tracking-wider">
                                <th class="px-6 py-4 text-center" style="width: 60px;">#</th>
                                <th class="px-6 py-4 text-center">Tipo de movimiento</th>
                                <th class="px-6 py-4 text-center">Producto</th>
                                <th class="px-6 py-4 text-center">Lote</th>
                                <th class="px-6 py-4 text-center">Cantidad (g)</th>
                                <th class="px-6 py-4 text-center">Unidad</th>
                                <th class="px-6 py-4 text-center">Sede</th>
                                <th class="px-6 py-4 text-center">Fecha</th>
                                <th class="px-6 py-4 text-center">Observación</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800/60 text-xs font-medium">
                            @forelse($movimiento as $movimientos)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-black/20 transition-colors">
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        <span
                                            class="inline-flex items-center px-3 py-1 text-[12px] font-black rounded-lg text-gray-600 dark:text-gray-300 border border-gray-300 dark:border-gray-800">
                                            {{ ($movimiento->currentPage() - 1) * $movimiento->perPage() + $loop->iteration }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        @if ($movimientos->tipo_movimiento === 'ENTRADA')
                                            <span
                                                class="inline-flex items-center gap-1 px-3 py-1 text-[10px] font-black rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900">
                                                <i class="fas fa-arrow-down"></i> Entrada
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center gap-1 px-3 py-1 text-[10px] font-black rounded-lg bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-900">
                                                <i class="fas fa-arrow-up"></i> Salida
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap font-bold"
                                        style="color: var(--text-main);">
                                        {{ $movimientos->producto->nombre ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap" style="color: var(--text-main);">
                                        {{ $movimientos->lote->codigo_lote ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap text-gray-500 dark:text-gray-400">
                                        {{ $movimientos->cantidad_convertida }}
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap text-gray-500 dark:text-gray-400">
                                        {{ $movimientos->unidad->nombre ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap text-gray-500 dark:text-gray-400">
                                        {{ $movimientos->sede->nombre ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap text-gray-500 dark:text-gray-400">
                                        {{ $movimientos->fecha }}
                                    </td>
                                    <td class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                        {{ $movimientos->observacion ?: 'Sin observación' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9"
                                        class="px-6 py-12 text-center text-xs font-bold uppercase tracking-wider text-gray-400">
                                        <i class="fas fa-boxes mb-3 block text-3xl text-gray-300 dark:text-gray-700"></i>
                                        No hay movimientos registrados
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="flex justify-center border-t p-4" style="border-color: var(--border-color);">
                    {{ $movimiento->onEachSide(1)->appends(request()->query())->links('components.pagination') }}
                </div>
            </div>
        </div>
    </div>

    @push('js')
        <script>
            document.getElementById('pdfBtn')?.addEventListener('click', function() {
                const params = new URLSearchParams(window.location.search);
                const url = `{{ route('admin.movimientos.historial_movimientos.export_pdf') }}?${params.toString()}`;
                window.open(url, '_blank');
            });
        </script>
    @endpush
</x-app-layout>
