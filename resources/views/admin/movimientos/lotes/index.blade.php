<x-app-layout>
    <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @include('components.alert')

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color: var(--text-main);">
                        Lotes
                    </h1>
                    <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                        Bienvenido <span class="font-bold">{{ auth()->user()->persona->nombre_persona ?? auth()->user()->name }}</span>
                        · {{ \Carbon\Carbon::now()->format('d/m/Y') }}
                    </p>
                </div>

                <div class="w-12 h-12 rounded-xl overflow-hidden shadow-md shrink-0 hidden md:block">
                    <img src="{{ asset('img/usuario-verificado.webp') }}" alt="Usuario"
                        class="w-full h-full object-cover">
                </div>
            </div>

            @if ($hayLotesVencidosSinMerma)
                <div class="rounded-2xl border shadow-sm overflow-hidden mb-6"
                    style="background-color: var(--bg-card); border-color: var(--border-color); border-left: 6px solid #dc2626;">

                    <div class="p-5 sm:p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">

                        <div class="flex items-start gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-lg bg-rose-100 dark:bg-rose-900/40 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-sm font-extrabold text-rose-800 dark:text-rose-300">
                                    Productos vencidos detectados
                                </h3>
                                <p class="mt-1 text-xs text-rose-700 dark:text-rose-400 leading-relaxed">
                                    Existen lotes vencidos que aún se encuentran en el inventario.
                                    Se recomienda realizar la <strong>merma</strong> para mantener el stock correcto.
                                </p>
                            </div>
                        </div>

                        <form action="{{ route('admin.movimientos.lotes.mermar') }}" method="POST" class="shrink-0">
                            @csrf
                            <button type="submit"
                                onclick="confirmarMerma(event, this)"
                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-700 hover:bg-red-800 text-white text-sm font-extrabold px-5 py-2.5 shadow-md active:scale-[0.98] transition-all">
                                <i class="fas fa-trash-alt text-xs"></i>
                                Mermar productos vencidos
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            <div class="rounded-2xl border shadow-sm overflow-hidden"
                style="background-color: var(--bg-card); border-color: var(--border-color);">

                <div class="p-5 sm:p-6 border-b" style="border-color: var(--border-color);">

                    <div class="flex flex-col lg:flex-row lg:items-center gap-4">

                        <div class="shrink-0">
                            <h3 class="text-lg font-extrabold flex items-center gap-2" style="color: var(--text-main);">
                                <i class="fas fa-boxes text-red-700 dark:text-red-500"></i>
                                Lotes registrados
                            </h3>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Listado completo de lotes del inventario.
                            </p>
                        </div>

                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 lg:ml-auto">

                            <div class="relative flex-1 sm:w-64">
                                <i class="fas fa-search absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400 pointer-events-none text-sm"></i>
                                <input type="text" name="buscar" value="{{ $buscar ?? '' }}"
                                    form="lotesSearchForm"
                                    placeholder="Buscar por código de lote…"
                                    style="background-color: var(--input-bg); border-color: var(--border-color); color: var(--text-main);"
                                    class="w-full pl-10 pr-4 py-2.5 rounded-xl border text-sm font-medium focus:outline-none focus:ring-2 focus:ring-red-500/30 focus:border-red-500 transition-all placeholder-gray-400">
                            </div>

                            <button type="button"
                                x-data="{ open: false }"
                                @click="open = !open; document.getElementById('filtros-lotes').classList.toggle('hidden')"
                                class="inline-flex items-center justify-center gap-2 rounded-xl border px-3 py-2.5 text-sm font-bold transition-all hover:bg-gray-50 dark:hover:bg-white/5 shrink-0"
                                style="border-color: var(--border-color); color: var(--text-main);"
                                title="Filtros">
                                <i class="fas fa-filter text-xs"></i>
                                <span class="hidden sm:inline">Filtros</span>
                            </button>

                            <button type="submit" form="lotesSearchForm"
                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-800 hover:bg-red-900 text-white text-sm font-extrabold px-5 py-2.5 shadow-md active:scale-[0.98] transition-all shrink-0">
                                <i class="fas fa-search text-xs"></i>
                                Buscar
                            </button>
                        </div>
                    </div>

                    <form id="lotesSearchForm" action="{{ route('admin.movimientos.lotes.index') }}" method="GET" class="hidden">
                        <input type="hidden" name="buscar" value="{{ $buscar ?? '' }}">
                    </form>

                    <div id="filtros-lotes" class="hidden mt-4">
                        <form action="{{ route('admin.movimientos.lotes.index') }}" method="GET">
                            <div class="rounded-xl border p-4"
                                style="border-color: var(--border-color); background-color: rgba(0,0,0,0.015);">

                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end">

                                    <div>
                                        <label class="block text-[11px] font-black uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1.5">
                                            Desde
                                        </label>
                                        <input type="date" name="fecha_desde" id="fecha_desde"
                                            value="{{ request('fecha_desde') }}"
                                            style="background-color: var(--input-bg); border-color: var(--border-color); color: var(--text-main);"
                                            class="w-full px-3 py-2.5 rounded-xl border text-sm font-medium focus:outline-none focus:ring-2 focus:ring-red-500/30 focus:border-red-500 transition-all">
                                    </div>

                                    <div>
                                        <label class="block text-[11px] font-black uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1.5">
                                            Hasta
                                        </label>
                                        <input type="date" name="fecha_hasta" id="fecha_hasta"
                                            value="{{ request('fecha_hasta') }}"
                                            max="{{ now()->format('Y-m-d') }}"
                                            style="background-color: var(--input-bg); border-color: var(--border-color); color: var(--text-main);"
                                            class="w-full px-3 py-2.5 rounded-xl border text-sm font-medium focus:outline-none focus:ring-2 focus:ring-red-500/30 focus:border-red-500 transition-all">
                                    </div>

                                    <div>
                                        <label class="block text-[11px] font-black uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1.5">
                                            Estado
                                        </label>
                                        <select name="estado"
                                            style="background-color: var(--input-bg); border-color: var(--border-color); color: var(--text-main);"
                                            class="w-full px-3 py-2.5 rounded-xl border text-sm font-medium focus:outline-none focus:ring-2 focus:ring-red-500/30 focus:border-red-500 transition-all">
                                            <option value="">Todos</option>
                                            <option value="1" {{ request('estado') === '1' ? 'selected' : '' }}>Activos</option>
                                            <option value="0" {{ request('estado') === '0' ? 'selected' : '' }}>Merma</option>
                                        </select>
                                    </div>

                                    <div class="flex gap-2">
                                        <button type="submit"
                                            class="flex-1 inline-flex items-center justify-center gap-2 rounded-xl bg-red-800 hover:bg-red-900 text-white text-sm font-extrabold px-4 py-2.5 shadow-md active:scale-[0.98] transition-all">
                                            <i class="fas fa-filter text-xs"></i>
                                            Aplicar
                                        </button>
                                        <a href="{{ route('admin.movimientos.lotes.index') }}"
                                            class="flex-1 inline-flex items-center justify-center gap-2 rounded-xl border px-4 py-2.5 text-sm font-bold transition-all hover:bg-gray-50 dark:hover:bg-white/5"
                                            style="border-color: var(--border-color); color: var(--text-main);">
                                            <i class="fas fa-rotate-left text-xs"></i>
                                            Limpiar
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div id="printArea" class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b text-[11px] font-black uppercase tracking-wider"
                                style="border-color: var(--border-color); background-color: rgba(0,0,0,0.015);">
                                <th class="px-4 py-4 text-center" style="width:60px; color: var(--text-main);">#</th>
                                <th class="px-4 py-4 text-left" style="color: var(--text-main);">Código lote</th>
                                <th class="px-4 py-4 text-left" style="color: var(--text-main);">Producto</th>
                                <th class="px-4 py-4 text-left" style="color: var(--text-main);">Proveedor</th>
                                <th class="px-4 py-4 text-center" style="color: var(--text-main);">Entrada</th>
                                <th class="px-4 py-4 text-center" style="color: var(--text-main);">Vencimiento</th>
                                <th class="px-4 py-4 text-center" style="color: var(--text-main);">Días restantes</th>
                                <th class="px-4 py-4 text-center" style="color: var(--text-main);">Unidades</th>
                                <th class="px-4 py-4 text-center" style="color: var(--text-main);">Gramos</th>
                                <th class="px-4 py-4 text-center" style="color: var(--text-main);">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm font-medium">
                            @forelse ($lotes as $lote)
                                @php
                                    $isExpired = (bool) $lote->is_expired;
                                    $isOut     = $lote->cantidad_sede !== null && $lote->cantidad_sede <= 0;
                                    $isNear    = !$isExpired && !$isOut && $lote->days_to_expire <= 7;
                                @endphp

                                <tr class="border-b transition-colors hover:bg-black/[0.015] dark:hover:bg-white/[0.02]"
                                    style="border-color: var(--border-color);">

                                    <td class="px-4 py-4 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center justify-center w-8 h-8 text-xs font-black rounded-lg border"
                                            style="border-color: var(--border-color); color: var(--text-main);">
                                            {{ ($lotes->currentPage() - 1) * $lotes->perPage() + $loop->iteration }}
                                        </span>
                                    </td>

                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[11px] font-black rounded-md border font-mono"
                                            style="border-color: var(--border-color); color: var(--text-main);">
                                            <i class="fas fa-barcode text-[10px] opacity-60"></i>
                                            {{ $lote->codigo_lote }}
                                        </span>
                                    </td>

                                    <td class="px-4 py-4 whitespace-nowrap" style="color: var(--text-main);">
                                        <div class="flex items-center gap-2.5">
                                            <span class="w-8 h-8 rounded-lg bg-red-50 dark:bg-red-950/30 text-red-700 dark:text-red-500 flex items-center justify-center shrink-0">
                                                <i class="fas fa-box text-xs"></i>
                                            </span>
                                            <span class="font-bold">{{ $lote->producto->nombre ?? '—' }}</span>
                                        </div>
                                    </td>

                                    <td class="px-4 py-4 whitespace-nowrap text-gray-500 dark:text-gray-400">
                                        {{ $lote->proveedor->nombre ?? '—' }}
                                    </td>

                                    <td class="px-4 py-4 text-center whitespace-nowrap text-gray-500 dark:text-gray-400">
                                        <i class="far fa-calendar mr-1.5 opacity-60"></i>
                                        {{ \Carbon\Carbon::parse($lote->fecha_entrada)->format('d/m/Y') }}
                                    </td>

                                    <td class="px-4 py-4 text-center whitespace-nowrap text-gray-500 dark:text-gray-400">
                                        <i class="far fa-calendar-times mr-1.5 opacity-60"></i>
                                        {{ \Carbon\Carbon::parse($lote->fecha_vencimiento)->format('d/m/Y') }}
                                    </td>

                                    <td class="px-4 py-4 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-black rounded-md border"
                                            style="border-color: var(--border-color); color: var(--text-main);">
                                            <i class="fas fa-hourglass-half text-[10px] opacity-60"></i>
                                            {{ round($lote->days_to_expire) }} días
                                        </span>
                                    </td>

                                    <td class="px-4 py-4 text-center whitespace-nowrap" style="color: var(--text-main);">
                                        <span class="font-black tabular-nums">{{ round($lote->cantidad_sede) }}</span>
                                    </td>

                                    <td class="px-4 py-4 text-center whitespace-nowrap text-gray-500 dark:text-gray-400">
                                        <span class="tabular-nums">{{ round($lote->cantidad_gramos_sede) }} g</span>
                                    </td>

                                    <td class="px-4 py-4 text-center whitespace-nowrap">
                                        @if ($isOut)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10px] font-black uppercase tracking-wider rounded-md border border-gray-300 dark:border-gray-700 text-gray-500 dark:text-gray-400">
                                                <i class="fas fa-circle text-[6px]"></i>
                                                Agotado
                                            </span>
                                        @elseif ($isExpired)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10px] font-black uppercase tracking-wider rounded-md bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-900">
                                                <i class="fas fa-circle-xmark"></i>
                                                Vencido
                                            </span>
                                        @elseif ($isNear)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10px] font-black uppercase tracking-wider rounded-md bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-900">
                                                <i class="fas fa-clock"></i>
                                                Cerca de vencer
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10px] font-black uppercase tracking-wider rounded-md bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900">
                                                <i class="fas fa-check-circle"></i>
                                                Vigente
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="px-6 py-16 text-center">
                                        <div class="flex flex-col items-center gap-3">
                                            <div class="w-16 h-16 rounded-full bg-gray-50 dark:bg-gray-800/60 flex items-center justify-center">
                                                <i class="fas fa-box-open text-2xl text-gray-300 dark:text-gray-600"></i>
                                            </div>
                                            <div>
                                                <p class="text-sm font-bold" style="color: var(--text-main);">
                                                    Sin lotes
                                                </p>
                                                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                                                    No se encontraron lotes con los criterios actuales.
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($lotes->hasPages())
                    <div class="px-6 py-4 border-t flex justify-center"
                        style="border-color: var(--border-color); background-color: rgba(0,0,0,0.015);">
                        {{ $lotes->onEachSide(1)->links('components.pagination') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>