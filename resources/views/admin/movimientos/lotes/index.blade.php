<x-app-layout>
    <div class="min-h-[calc(100vh-4rem)] px-4 pb-12 pt-6 sm:px-6 lg:px-8">
        @include('components.alert')

        <div class="mx-auto max-w-7xl">
            <div class="mb-6 flex flex-col justify-between gap-4 md:flex-row md:items-center">
                <div>
                    <h1 class="text-2xl font-extrabold tracking-tight sm:text-3xl" style="color: var(--text-main);">
                        Lotes
                    </h1>
                    <p class="mt-1 text-xs font-medium text-gray-500 sm:text-sm dark:text-gray-400">
                        Bienvenido <span class="font-bold">{{ auth()->user()->persona->nombre_persona }}</span> ·
                        {{ \Carbon\Carbon::now()->format('d/m/Y') }}
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="hidden text-right sm:block">
                        <small class="block text-[11px] font-bold uppercase tracking-wider text-gray-400">Inventario</small>
                        <span class="text-sm font-bold" style="color: var(--text-main);">Control de lotes</span>
                    </div>
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-red-100 text-red-700 dark:bg-red-950/50 dark:text-red-300">
                        <i class="fas fa-boxes-stacked text-lg"></i>
                    </div>
                </div>
            </div>

            @if ($hayLotesVencidosSinMerma)
                <div class="mb-4 rounded-2xl border-l-4 border-red-600 p-5 shadow-sm"
                    style="background-color: var(--bg-card); border-top-color: var(--border-color); border-right-color: var(--border-color); border-bottom-color: var(--border-color);">
                    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                        <div>
                            <h2 class="text-base font-extrabold text-red-700 dark:text-red-400">
                                <i class="fas fa-triangle-exclamation mr-1"></i> Productos vencidos detectados
                            </h2>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Existen lotes vencidos en el inventario. Realiza la merma para mantener el stock correcto.
                            </p>
                        </div>
                        <form action="{{ route('admin.movimientos.lotes.mermar') }}" method="POST">
                            @csrf
                            <button type="submit" onclick="confirmarMerma(event, this)"
                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-700 px-4 py-2.5 text-sm font-extrabold text-white shadow-md transition hover:bg-red-800 active:scale-95">
                                <i class="fas fa-trash-alt"></i> Mermar vencidos
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            <div class="mb-3 flex flex-col gap-4 rounded-2xl border p-2.5 shadow-sm lg:flex-row lg:items-center"
                style="background-color: var(--bg-card); border-color: var(--border-color);">
                <form action="{{ route('admin.movimientos.lotes.index') }}" method="GET" class="relative w-full">
                    <input type="hidden" name="estado" value="{{ request('estado') }}">
                    <input type="hidden" name="fecha_desde" value="{{ request('fecha_desde') }}">
                    <input type="hidden" name="fecha_hasta" value="{{ request('fecha_hasta') }}">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-400">
                        <i class="fas fa-search text-sm"></i>
                    </div>
                    <input type="text" name="buscar" value="{{ $buscar ?? '' }}" placeholder="Buscar lote o producto..."
                        class="w-full rounded-xl border py-2.5 pl-10 pr-4 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-red-500"
                        style="background-color: var(--input-bg); border-color: var(--border-color); color: var(--text-main);">
                </form>
                <button type="button" id="filtersToggle"
                    class="flex h-10 shrink-0 items-center justify-center gap-2 rounded-xl border px-4 text-xs font-extrabold transition hover:border-red-600 hover:bg-red-50 hover:text-red-700 dark:hover:bg-red-950/40"
                    style="border-color: var(--border-color); color: var(--text-main);" title="Filtros">
                    <i class="fas fa-filter"></i> Filtros
                </button>
            </div>

            <div id="filters" class="{{ request()->hasAny(['fecha_desde', 'fecha_hasta', 'estado']) ? '' : 'hidden' }} mb-3 rounded-2xl border p-4 shadow-sm"
                style="background-color: var(--bg-card); border-color: var(--border-color);">
                <form action="{{ route('admin.movimientos.lotes.index') }}" method="GET"
                    class="grid grid-cols-1 items-end gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <label for="fecha_desde" class="mb-2 block text-[11px] font-black uppercase tracking-wider text-gray-500 dark:text-gray-400">Desde</label>
                        <input type="date" name="fecha_desde" id="fecha_desde" value="{{ request('fecha_desde') }}"
                            class="w-full rounded-xl border px-3 py-2.5 text-sm focus:border-red-600 focus:outline-none focus:ring-2 focus:ring-red-600/20"
                            style="background-color: var(--input-bg); border-color: var(--input-border); color: var(--text-main);">
                    </div>
                    <div>
                        <label for="fecha_hasta" class="mb-2 block text-[11px] font-black uppercase tracking-wider text-gray-500 dark:text-gray-400">Hasta</label>
                        <input type="date" name="fecha_hasta" id="fecha_hasta" value="{{ request('fecha_hasta') }}" max="{{ now()->format('Y-m-d') }}"
                            class="w-full rounded-xl border px-3 py-2.5 text-sm focus:border-red-600 focus:outline-none focus:ring-2 focus:ring-red-600/20"
                            style="background-color: var(--input-bg); border-color: var(--input-border); color: var(--text-main);">
                    </div>
                    <div>
                        <label for="estado" class="mb-2 block text-[11px] font-black uppercase tracking-wider text-gray-500 dark:text-gray-400">Estado</label>
                        <select name="estado" id="estado" class="w-full rounded-xl border px-3 py-2.5 text-sm focus:border-red-600 focus:outline-none focus:ring-2 focus:ring-red-600/20"
                            style="background-color: var(--input-bg); border-color: var(--input-border); color: var(--text-main);">
                            <option value="">Todos los estados</option>
                            <option value="1" {{ request('estado') === '1' ? 'selected' : '' }}>Activos</option>
                            <option value="0" {{ request('estado') === '0' ? 'selected' : '' }}>Merma</option>
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <input type="hidden" name="buscar" value="{{ $buscar ?? '' }}">
                        <button type="submit" class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-red-700 px-4 py-2.5 text-xs font-extrabold text-white shadow-md transition hover:bg-red-800 active:scale-95">
                            <i class="fas fa-check"></i> Aplicar
                        </button>
                        <a href="{{ route('admin.movimientos.lotes.index') }}"
                            class="inline-flex items-center justify-center gap-1.5 rounded-xl border px-4 py-2.5 text-xs font-extrabold transition hover:border-red-600 hover:text-red-600"
                            style="border-color: var(--border-color); color: var(--text-main);">
                            <i class="fas fa-times"></i> Limpiar
                        </a>
                    </div>
                </form>
            </div>

            <div class="overflow-hidden rounded-2xl border shadow-sm"
                style="background-color: var(--bg-card); border-color: var(--border-color);">
                <div class="overflow-x-auto" id="printArea">
                    <table class="w-full min-w-[1100px] border-collapse text-left">
                        <thead>
                            <tr class="border-b text-[11px] font-black uppercase tracking-wider"
                                style="background-color: var(--input-bg); border-color: var(--border-color); color: var(--text-main);">
                                <th class="px-5 py-4 text-center">#</th>
                                <th class="px-5 py-4">Código de lote</th>
                                <th class="px-5 py-4">Producto</th>
                                <th class="px-5 py-4">Proveedor</th>
                                <th class="px-5 py-4">Entrada</th>
                                <th class="px-5 py-4">Vencimiento</th>
                                <th class="px-5 py-4 text-right">Días restantes</th>
                                <th class="px-5 py-4 text-right">Cantidad (U)</th>
                                <th class="px-5 py-4 text-right">Cantidad (g)</th>
                                <th class="px-5 py-4 text-center">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y text-sm" style="divide-color: var(--border-color);">
                            @forelse($lotes as $lote)
                                <tr class="transition hover:bg-red-50/40 dark:hover:bg-red-950/20">
                                    <td class="px-5 py-4 text-center font-bold text-gray-400">{{ ($lotes->currentPage() - 1) * $lotes->perPage() + $loop->iteration }}</td>
                                    <td class="px-5 py-4 font-bold" style="color: var(--text-main);">{{ $lote->codigo_lote }}</td>
                                    <td class="px-5 py-4 font-medium" style="color: var(--text-main);">{{ $lote->producto->nombre }}</td>
                                    <td class="px-5 py-4 text-gray-500 dark:text-gray-400">{{ $lote->proveedor->nombre }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-500 dark:text-gray-400">{{ $lote->fecha_entrada }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-gray-500 dark:text-gray-400">{{ $lote->fecha_vencimiento }}</td>
                                    <td class="px-5 py-4 text-right" style="color: var(--text-main);">{{ round($lote->days_to_expire) }} días</td>
                                    <td class="px-5 py-4 text-right font-bold" style="color: var(--text-main);">{{ round($lote->cantidad_sede) }}</td>
                                    <td class="px-5 py-4 text-right" style="color: var(--text-main);">{{ round($lote->cantidad_gramos_sede) }}g</td>
                                    <td class="px-5 py-4 text-center">
                                        @if ($lote->cantidad_sede && $lote->cantidad_sede <= 0)
                                            <span class="inline-flex rounded-lg border border-gray-200 bg-gray-100 px-3 py-1 text-[10px] font-black text-gray-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">Agotado</span>
                                        @elseif ($lote->is_expired)
                                            <span class="inline-flex rounded-lg border border-rose-200 bg-rose-50 px-3 py-1 text-[10px] font-black text-rose-600 dark:border-rose-900 dark:bg-rose-950/40 dark:text-rose-400">Vencido</span>
                                        @elseif ($lote->days_to_expire <= 7)
                                            <span class="inline-flex rounded-lg border border-amber-200 bg-amber-50 px-3 py-1 text-[10px] font-black text-amber-600 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-400">Cerca de vencer</span>
                                        @else
                                            <span class="inline-flex rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1 text-[10px] font-black text-emerald-600 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-400">Vigente</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="10" class="px-5 py-14 text-center text-sm font-bold text-gray-400">No hay lotes registrados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($lotes->hasPages())
                    <div class="flex justify-center border-t p-4" style="border-color: var(--border-color);">
                        {{ $lotes->onEachSide(1)->appends(request()->query())->links('components.pagination') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>

@push('js')
    <script>
        document.getElementById('filtersToggle')?.addEventListener('click', function () {
            document.getElementById('filters')?.classList.toggle('hidden');
        });

        function confirmarMerma(event, form) {
            event.preventDefault();
            const confirmar = window.AppModal
                ? window.AppModal.confirm('¿Registrar merma?', 'Los lotes vencidos serán retirados del inventario.')
                : Promise.resolve(window.confirm('¿Registrar merma de los lotes vencidos?'));
            confirmar.then((result) => {
                if (result) form.submit();
            });
        }
    </script>
@endpush
