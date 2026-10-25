<x-app-layout>
    <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @include('components.alert')

            @php
                $sedeId = request()->segment(count(request()->segments()));
                $sedeNombre = $sedes->firstWhere('id_sed', $sedeId)?->nombre_sed ?? 'Sede';
            @endphp

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <div class="mb-2 flex items-center gap-2 text-[11px] font-black uppercase tracking-wider text-gray-400">
                        <a href="{{ route('admin.movimientos.sedes_lotes', [\App\Support\Inventario\FiltroTipoProducto::PARAMETRO => \App\Support\Inventario\FiltroTipoProducto::valorUrl($tipoProducto)]) }}"
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

                <a href="{{ route('admin.movimientos.sedes_lotes', [\App\Support\Inventario\FiltroTipoProducto::PARAMETRO => \App\Support\Inventario\FiltroTipoProducto::valorUrl($tipoProducto)]) }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl border px-5 py-2.5 text-sm font-extrabold transition-all hover:border-red-500 hover:text-red-600"
                    style="border-color: var(--border-color); color: var(--text-main);">
                    <i class="fas fa-arrow-left text-xs"></i>
                    <span>Volver a sedes</span>
                </a>
            </div>

            @if ($totales->isNotEmpty())
                <div style="background-color: var(--bg-card); border-color: var(--border-color);"
                    class="rounded-2xl border shadow-sm overflow-hidden mb-6">
                    <div class="p-5 sm:p-6 border-b" style="border-color: var(--border-color);">
                        <h3 class="text-lg font-extrabold flex items-center gap-2" style="color: var(--text-main);">
                            <i class="fas fa-boxes-stacked text-red-700 dark:text-red-500"></i>
                            Total por producto
                        </h3>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Consolidado de todos los lotes de la sede, comparado con el stock mínimo y máximo del producto.
                        </p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="text-[11px] font-black uppercase tracking-wider border-b" style="border-color: var(--border-color); color: var(--text-muted);">
                                    <th class="px-6 py-3">Producto</th>
                                    <th class="px-6 py-3 text-center">Existencia</th>
                                    <th class="px-6 py-3 text-center">Unidad base</th>
                                    <th class="px-6 py-3 text-center">Mínimo / Máximo</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($totales as $total)
                                    <tr class="border-b" style="border-color: var(--border-color); color: var(--text-main);">
                                        <td class="px-6 py-3 font-bold">{{ $total->producto->nombre_pdt ?? 'Producto' }}</td>
                                        <td class="px-6 py-3 text-center">
                                            <x-alerta-stock :total="$total->cantidad_total_int" :minimo="$total->producto?->stock_minimo_pdt" :maximo="$total->producto?->stock_maximo_pdt" />
                                        </td>
                                        <td class="px-6 py-3 text-center">{{ number_format($total->cantidad_convertida_total_int, 2, ',', '.') }}</td>
                                        <td class="px-6 py-3 text-center">{{ $total->producto->stock_minimo_pdt ?? '—' }} / {{ $total->producto->stock_maximo_pdt ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <div style="background-color: var(--bg-card); border-color: var(--border-color);"
                class="rounded-2xl border shadow-sm overflow-hidden">

                <div class="p-5 sm:p-6 border-b" style="border-color: var(--border-color);">
                    <div class="flex flex-col lg:flex-row lg:items-center gap-4">

                        <div class="shrink-0">
                            <h3 class="text-lg font-extrabold flex items-center gap-2" style="color: var(--text-main);">
                                <i class="fas fa-warehouse text-red-700 dark:text-red-500"></i>
                                Lotes en esta sede
                            </h3>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Detalle de existencias por lote.
                            </p>
                        </div>

                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 lg:ml-auto">

                            @if ($tiposProducto->count() > 1)
                                <form action="{{ route('admin.movimientos.sedes_lotes.show', $sedeId) }}" method="GET">
                                    <input type="hidden" name="estado" value="{{ $activo }}">
                                    <input type="hidden" name="buscar" value="{{ $buscar ?? '' }}">
                                    <x-filtro-tipo-producto :tipos="$tiposProducto" :seleccionado="$tipoProducto" :etiqueta="false" />
                                </form>
                            @endif

                            <form action="{{ route('admin.movimientos.sedes_lotes.show', $sedeId) }}"
                                method="GET" class="relative flex-1 sm:w-64">
                                <input type="hidden" name="estado" value="{{ $activo }}">
                                <input type="hidden" name="tipo" value="{{ \App\Support\Inventario\FiltroTipoProducto::valorUrl($tipoProducto) }}">
                                <i class="fas fa-search absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400 pointer-events-none text-sm"></i>
                                <input type="text" name="buscar" value="{{ $buscar ?? '' }}"
                                    placeholder="Buscar lote o producto…"
                                    style="background-color: var(--input-bg); border-color: var(--border-color); color: var(--text-main);"
                                    class="w-full pl-10 pr-4 py-2.5 rounded-xl border text-sm font-medium focus:outline-none focus:ring-2 focus:ring-red-500/30 focus:border-red-500 transition-all placeholder-gray-400">
                            </form>

                            <div class="flex items-center gap-2 px-3 py-2.5 rounded-xl border shrink-0"
                                style="border-color: var(--border-color); background-color: rgba(0,0,0,0.015);">
                                <span class="text-[11px] font-black uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Solo activos
                                </span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" id="estadoToggle" class="sr-only peer"
                                        {{ ($activo ?? 1) == 1 ? 'checked' : '' }}>
                                    <span class="w-10 h-6 bg-gray-300 dark:bg-gray-700 rounded-full peer peer-checked:bg-red-700 transition-colors relative after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-4"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto" id="printArea">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b text-[11px] font-black uppercase tracking-wider"
                                style="border-color: var(--border-color); background-color: rgba(0,0,0,0.015);">
                                <th class="px-4 py-4 text-center" style="width:60px; color: var(--text-main);">#</th>
                                <th class="px-4 py-4 text-left" style="color: var(--text-main);">Código de lote</th>
                                <th class="px-4 py-4 text-left" style="color: var(--text-main);">Producto</th>
                                <th class="px-4 py-4 text-center" style="color: var(--text-main);">Unidades</th>
                                <th class="px-4 py-4 text-center" style="color: var(--text-main);">Gramos</th>
                                <th class="px-4 py-4 text-center" style="color: var(--text-main);">Entrada</th>
                                <th class="px-4 py-4 text-center" style="color: var(--text-main);">Vencimiento</th>
                                <th class="px-4 py-4 text-left" style="color: var(--text-main);">Proveedor</th>
                                <th class="px-4 py-4 text-center" style="width:120px; color: var(--text-main);">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm font-medium">
                            @forelse ($sede as $loteSede)
                                @php
                                    $lote = $loteSede->lote;
                                    $fechaVencimiento = $lote?->fecha_vencimiento_lot
                                        ? \Carbon\Carbon::parse($lote->fecha_vencimiento_lot)
                                        : null;

                                    $estaVencido    = $fechaVencimiento && $fechaVencimiento->isPast();
                                    $agotado        = $loteSede->cantidad_convertida_isl <= 0;
                                    $loteActivo     = (int) ($lote?->status_id ?? 0) === 1;
                                    $diasParaVencer = $fechaVencimiento
                                        ? \Carbon\Carbon::now()->diffInDays($fechaVencimiento, false)
                                        : null;

                                    $cercaDeVencer = !$estaVencido
                                        && !$agotado
                                        && $loteActivo
                                        && $diasParaVencer !== null
                                        && $diasParaVencer <= 7;
                                @endphp

                                <tr class="border-b transition-colors hover:bg-black/[0.015] dark:hover:bg-white/[0.02]"
                                    style="border-color: var(--border-color);">

                                    <td class="px-4 py-4 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center justify-center w-8 h-8 text-xs font-black rounded-lg border"
                                            style="border-color: var(--border-color); color: var(--text-main);">
                                            {{ ($sede->currentPage() - 1) * $sede->perPage() + $loop->iteration }}
                                        </span>
                                    </td>

                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[11px] font-black rounded-md border font-mono"
                                            style="border-color: var(--border-color); color: var(--text-main);">
                                            <i class="fas fa-barcode text-[10px] opacity-60"></i>
                                            {{ $loteSede->lote->codigo_lote_lot }}
                                        </span>
                                    </td>

                                    <td class="px-4 py-4 whitespace-nowrap" style="color: var(--text-main);">
                                        <div class="flex items-center gap-2.5">
                                            <span class="w-8 h-8 rounded-lg bg-red-50 dark:bg-red-950/30 text-red-700 dark:text-red-500 flex items-center justify-center shrink-0">
                                                <i class="fas fa-box text-xs"></i>
                                            </span>
                                            <span class="font-bold">{{ $loteSede->lote->producto->nombre_pdt ?? '—' }}</span>
                                        </div>
                                    </td>

                                    <td class="px-4 py-4 text-center whitespace-nowrap" style="color: var(--text-main);">
                                        <span class="font-black tabular-nums">{{ number_format($loteSede->cantidad_isl, 0) }}</span>
                                        <span class="text-[10px] font-black uppercase tracking-wider text-gray-400 dark:text-gray-500 ml-1">u</span>
                                    </td>

                                    <td class="px-4 py-4 text-center whitespace-nowrap text-gray-500 dark:text-gray-400">
                                        @if ($loteSede->no_aplica_gramos)
                                            <span class="italic text-gray-400">—</span>
                                        @else
                                            <span class="tabular-nums">{{ number_format($loteSede->cantidad_convertida_isl, 0) }}</span>
                                            <span class="text-[10px] font-black uppercase tracking-wider opacity-60 ml-1">g</span>
                                        @endif
                                    </td>

                                    <td class="px-4 py-4 text-center whitespace-nowrap text-gray-500 dark:text-gray-400">
                                        <i class="far fa-calendar mr-1.5 opacity-60"></i>
                                        {{ \Carbon\Carbon::parse($loteSede->lote->fecha_entrada_lot)->format('d/m/Y') }}
                                    </td>

                                    <td class="px-4 py-4 text-center whitespace-nowrap text-gray-500 dark:text-gray-400">
                                        @if ($loteSede->no_aplica_vencimiento)
                                            <span class="italic text-gray-400">—</span>
                                        @else
                                            <i class="far fa-calendar-times mr-1.5 opacity-60"></i>
                                            {{ \Carbon\Carbon::parse($loteSede->lote->fecha_vencimiento_lot)->format('d/m/Y') }}
                                        @endif
                                    </td>

                                    <td class="px-4 py-4 whitespace-nowrap text-gray-500 dark:text-gray-400">
                                        {{ $loteSede->lote->proveedor->nombre_contacto_prv ?? '—' }}
                                    </td>

                                    <td class="px-4 py-4 text-center whitespace-nowrap">
                                        @if ($loteSede->no_aplica_vencimiento)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10px] font-black uppercase tracking-wider rounded-md border border-gray-300 dark:border-gray-700 text-gray-500 dark:text-gray-400">
                                                <i class="fas fa-minus-circle"></i>
                                                No aplica
                                            </span>
                                        @elseif ($agotado)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10px] font-black uppercase tracking-wider rounded-md border border-gray-300 dark:border-gray-700 text-gray-500 dark:text-gray-400">
                                                <i class="fas fa-circle text-[6px]"></i>
                                                Agotado
                                            </span>
                                        @elseif ($estaVencido)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10px] font-black uppercase tracking-wider rounded-md bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-900">
                                                <i class="fas fa-circle-xmark"></i>
                                                Vencido
                                            </span>
                                        @elseif ($cercaDeVencer)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10px] font-black uppercase tracking-wider rounded-md bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-900">
                                                <i class="fas fa-clock"></i>
                                                Cerca de vencer
                                            </span>
                                        @elseif ($loteActivo)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10px] font-black uppercase tracking-wider rounded-md bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900">
                                                <i class="fas fa-check-circle"></i>
                                                Activo
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10px] font-black uppercase tracking-wider rounded-md border border-gray-300 dark:border-gray-700 text-gray-500 dark:text-gray-400">
                                                <i class="fas fa-ban"></i>
                                                Inactivo
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-6 py-16 text-center">
                                        <div class="flex flex-col items-center gap-3">
                                            <div class="w-16 h-16 rounded-full bg-gray-50 dark:bg-gray-800/60 flex items-center justify-center">
                                                <i class="fas fa-box-open text-2xl text-gray-300 dark:text-gray-600"></i>
                                            </div>
                                            <div>
                                                <p class="text-sm font-bold" style="color: var(--text-main);">
                                                    Sin inventario
                                                </p>
                                                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                                                    No hay lotes registrados para esta sede con los criterios actuales.
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($sede->hasPages())
                    <div class="px-6 py-4 border-t flex justify-center"
                        style="border-color: var(--border-color); background-color: rgba(0,0,0,0.015);">
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