<x-app-layout>

    @include('components.alert')

    @php
        $formato = fn ($valor) => \App\Support\Inventario\UnidadCompra::numero((float) $valor);
        $activos = (int) request('activo', 1) === 1;
        $etiquetaTipo = collect($tipos)->mapWithKeys(fn ($tipo) => [$tipo['id'] => $tipo]);
        $filtrosTipo = ['' => 'Todos'] + collect($tipos)->map(fn ($tipo) => $tipo['filtro'])->all();
    @endphp

    <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color:var(--text-main);">{{ $config['plural'] }}</h1>
                    <p class="mt-1 text-xs sm:text-sm font-medium" style="color:var(--text-muted);">
                        <i class="fas {{ $config['icono'] }} mr-1" style="color:var(--color-primary)"></i>
                        {{ $config['descripcion'] }} Existencias de {{ $sedeNombre }}.
                    </p>
                </div>
                @canRoute($config['ruta'] . '.create')
                    <a href="{{ route($config['ruta'] . '.create', array_filter(['tipo' => $tipoElegido])) }}"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-800 px-5 py-2.5 text-sm font-extrabold text-white shadow-lg transition-all hover:bg-red-900 active:scale-95">
                        <i class="fas fa-plus text-xs"></i> {{ $config['nuevo'] }}
                    </a>
                @endcanRoute
            </div>

            <div class="rounded-2xl border shadow-sm overflow-hidden" style="background-color:var(--bg-card);border-color:var(--border-color);">
                <div class="p-3 border-b" style="border-color:var(--border-color);">
                    <div class="flex flex-wrap items-center gap-2 mb-3">
                        <span class="text-[11px] font-black uppercase tracking-wider" style="color:var(--text-muted);">Tipo:</span>
                        @foreach ($filtrosTipo as $clave => $texto)
                            @php $marcado = (string) $tipoElegido === (string) $clave; @endphp
                            <a href="{{ route($config['ruta'] . '.index', array_filter(array_merge(request()->except(['page', 'categoria', 'tipo']), ['tipo' => $clave]), fn ($valor) => $valor !== '' && $valor !== null)) }}"
                                class="px-3 py-1.5 rounded-lg text-xs font-bold border {{ $marcado ? 'bg-red-800 text-white border-red-800' : '' }}"
                                style="{{ $marcado ? '' : 'border-color:var(--border-color);color:var(--text-main);' }}">{{ $texto }}</a>
                        @endforeach
                    </div>
                    <form action="{{ route($config['ruta'] . '.index') }}" method="GET">
                        <input type="hidden" name="activo" value="{{ $activos ? 1 : 0 }}">
                        @if ($tipoElegido)
                            <input type="hidden" name="tipo" value="{{ $tipoElegido }}">
                        @endif
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
                            <div class="flex-1 flex items-center gap-2">
                                <a href="{{ route($config['ruta'] . '.index', array_merge(request()->except('page'), ['activo' => 1])) }}"
                                    class="px-3 py-1.5 rounded-lg text-xs font-bold border {{ $activos ? 'bg-red-800 text-white border-red-800' : '' }}"
                                    style="{{ $activos ? '' : 'border-color:var(--border-color);color:var(--text-main);' }}">Activos</a>
                                <a href="{{ route($config['ruta'] . '.index', array_merge(request()->except('page'), ['activo' => 0])) }}"
                                    class="px-3 py-1.5 rounded-lg text-xs font-bold border {{ ! $activos ? 'bg-red-800 text-white border-red-800' : '' }}"
                                    style="{{ ! $activos ? '' : 'border-color:var(--border-color);color:var(--text-main);' }}">Inactivos</a>
                            </div>
                            <div class="flex w-full flex-col sm:flex-row gap-2 lg:w-auto">
                                <select name="categoria" class="rounded-xl border px-3 py-2.5 text-sm outline-none"
                                    style="background-color:var(--input-bg);border-color:var(--input-border);color:var(--text-main);">
                                    <option value="">Todas las categorías</option>
                                    @foreach ($categorias as $categoria)
                                        <option value="{{ $categoria->id_cap }}" @selected($categoriaElegida === (int) $categoria->id_cap)>{{ $categoria->nombre_cap }}@if (! $tipoElegido) ({{ $etiquetaTipo[(int) $categoria->tpr_id]['filtro'] ?? '' }})@endif</option>
                                    @endforeach
                                </select>
                                <div class="flex min-w-0 flex-1 items-center gap-2 rounded-xl border px-3 py-2.5 lg:w-80" style="background-color:var(--input-bg);border-color:var(--input-border);">
                                    <i class="fas fa-search text-gray-400"></i>
                                    <input type="text" name="buscar" value="{{ request('buscar') }}" class="w-full border-0 bg-transparent text-sm outline-none"
                                        style="color:var(--text-main);" placeholder="Buscar por nombre o código...">
                                </div>
                                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-800 px-4 py-2.5 text-sm font-extrabold text-white hover:bg-red-900">
                                    <i class="fas fa-search text-xs"></i> Buscar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b text-[13px] font-black uppercase tracking-wider" style="background-color:var(--bg-card);border-color:var(--border-color);color:var(--text-main);">
                                <th class="px-6 py-4 text-center">Código</th>
                                <th class="px-6 py-4">Nombre</th>
                                <th class="px-6 py-4">Tipo y categoría</th>
                                <th class="px-6 py-4 text-center">Se compra por</th>
                                <th class="px-6 py-4 text-center">Precio vigente</th>
                                <th class="px-6 py-4 text-center">Existencia en {{ $sedeNombre }}</th>
                                <th class="px-6 py-4 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($productos as $producto)
                                @php
                                    $unidadCompra = \App\Support\Inventario\UnidadCompra::de($producto);
                                    $costo = $producto->precioProducto?->costo_usd_ppr;
                                    $estado = \App\Models\InventarioTotal::clasificar((float) $producto->existencia_convertida, $producto->stock_minimo_pdt, $producto->stock_maximo_pdt);
                                    $estilo = [
                                        'agotado' => ['bg-red-50 text-red-700 border-red-200 dark:bg-red-950/40 dark:text-red-400 dark:border-red-900', 'Agotado'],
                                        'bajo_minimo' => ['bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-400 dark:border-amber-900', 'Bajo el mínimo'],
                                        'sobre_maximo' => ['bg-violet-50 text-violet-700 border-violet-200 dark:bg-violet-950/40 dark:text-violet-400 dark:border-violet-900', 'Sobre el máximo'],
                                        'normal' => ['bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-400 dark:border-emerald-900', 'Normal'],
                                    ][$estado];
                                @endphp
                                <x-table-row :id="$producto->id_pdt" class="border-b" style="border-color:var(--border-color);">
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center px-3 py-1 text-[12px] font-black rounded-lg border" style="border-color:var(--border-color);color:var(--text-muted);">
                                            {{ $producto->codigo_pdt }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 font-bold text-sm" style="color:var(--text-main);">{{ $producto->nombre_pdt }}</td>
                                    <td class="px-6 py-4 text-xs" style="color:var(--text-muted);">
                                        @php $tipoFila = $etiquetaTipo[(int) ($producto->categoria->tpr_id ?? 0)] ?? null; @endphp
                                        @if ($tipoFila)
                                            <span class="inline-flex items-center gap-1 font-bold" style="color:var(--text-main);"><i class="fas {{ $tipoFila['icono'] }} text-[10px]"></i> {{ $tipoFila['etiqueta'] }}</span>
                                        @endif
                                        <span class="block">{{ $producto->categoria->nombre_cap ?? '—' }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-center text-xs whitespace-nowrap" style="color:var(--text-main);">
                                        {{ $unidadCompra->unidad }}
                                        @if ($unidadCompra->modo === \App\Support\Inventario\UnidadCompra::MODO_ENVASE)
                                            <span class="block text-[10px]" style="color:var(--text-muted);">trae {{ $formato($unidadCompra->factor) }} {{ $unidadCompra->unidadBase }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center text-xs whitespace-nowrap" style="color:var(--text-main);">
                                        @if ($costo !== null)
                                            <span class="font-bold">{{ number_format((float) $costo, 2, ',', '.') }} USD</span>
                                            <span class="block text-[10px]" style="color:var(--text-muted);">
                                                {{ $unidadCompra->precioPor() }}@if ($tasa) · ≈ {{ number_format((float) $costo * $tasa['valor'], 2, ',', '.') }} Bs @endif
                                            </span>
                                        @else
                                            <span style="color:var(--text-muted);">Sin precio</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        <span class="font-bold text-xs" style="color:var(--text-main);">{{ $formato($producto->existencia_convertida ?? 0) }} {{ $unidadCompra->unidadBase }}</span>
                                        <span class="mt-1 flex justify-center">
                                            <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-black rounded-lg border {{ $estilo[0] }}"
                                                title="Mínimo {{ $producto->stock_minimo_pdt ?? '—' }} · Máximo {{ $producto->stock_maximo_pdt ?? '—' }}">
                                                {{ $estilo[1] }}
                                            </span>
                                        </span>
                                    </td>
                                    <x-table-actions :id="$producto->id_pdt" :baseUrl="$config['url']" :show="false" :status="(int) $producto->status_id === 1" />
                                </x-table-row>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-sm" style="color:var(--text-muted);">
                                        <i class="fas {{ $config['icono'] }} text-3xl mb-3 block opacity-40"></i>
                                        @if (request()->filled('buscar') || request()->filled('categoria') || $tipoElegido || ! $activos)
                                            No hay resultados con estos filtros. Cambia la búsqueda, elige "Todos" o vuelve a "Activos".
                                        @else
                                            {{ $config['vacio'] }}
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($productos->hasPages())
                    <div class="p-4 border-t flex justify-center" style="border-color:var(--border-color);">
                        {{ $productos->onEachSide(1)->links('partials.pagination') }}
                    </div>
                @endif
            </div>

            @if (! $tasa)
                <p class="mt-3 text-xs" style="color:var(--text-muted);">
                    <i class="fas fa-info-circle mr-1"></i> No hay tasa del dólar registrada, por eso los precios solo se muestran en dólares.
                </p>
            @endif
        </div>
    </div>
</x-app-layout>
