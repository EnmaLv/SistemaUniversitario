<x-app-layout>

    @include('components.alert')

    <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color:var(--text-main);">{{ $sede->nombre_sed }}</h1>
                    <p class="mt-1 text-xs sm:text-sm font-medium" style="color:var(--text-muted);">
                        <i class="fas fa-building mr-1" style="color:var(--color-primary)"></i>
                        {{ $sede->direccion_sed ?: 'Sin dirección' }} · {{ $sede->telefono_sed ?: 'Sin teléfono' }}
                    </p>
                </div>
                <a href="{{ route('admin.maestros.sedes.index') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-800 px-5 py-2.5 text-sm font-extrabold text-white shadow-lg transition-all hover:bg-red-900 active:scale-95">
                    <i class="fas fa-arrow-left text-xs"></i> Volver
                </a>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 mb-6">
                @foreach ([['Productos con existencia', $estadisticas['total_productos'], 'fa-boxes'], ['Lotes con existencia', $estadisticas['total_lotes'], 'fa-layer-group'], ['Movimientos del mes', $estadisticas['movimientos_mes'], 'fa-exchange-alt']] as [$titulo, $valor, $icono])
                    <div class="rounded-2xl border p-5 shadow-sm" style="background-color:var(--bg-card);border-color:var(--border-color);">
                        <p class="text-xs font-bold uppercase tracking-wider" style="color:var(--text-muted);"><i class="fas {{ $icono }} mr-1" style="color:var(--color-primary)"></i> {{ $titulo }}</p>
                        <p class="mt-2 text-3xl font-extrabold" style="color:var(--text-main);">{{ $valor }}</p>
                    </div>
                @endforeach
            </div>

            <div class="rounded-2xl border shadow-sm overflow-hidden" style="background-color:var(--bg-card);border-color:var(--border-color);">
                <div class="p-4 border-b" style="border-color:var(--border-color);">
                    <h3 class="text-lg font-extrabold" style="color:var(--text-main);">Inventario de la sede</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b text-[13px] font-black uppercase tracking-wider" style="border-color:var(--border-color);color:var(--text-main);">
                                <th class="px-6 py-4">Código</th>
                                <th class="px-6 py-4">Producto</th>
                                <th class="px-6 py-4">Lote</th>
                                <th class="px-6 py-4">Vencimiento</th>
                                <th class="px-6 py-4 text-right">Cantidad</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($sede->inventario as $item)
                                <tr class="border-b" style="border-color:var(--border-color);">
                                    <td class="px-6 py-4 font-bold" style="color:var(--text-muted);">{{ $item->producto_codigo }}</td>
                                    <td class="px-6 py-4 font-bold" style="color:var(--text-main);">{{ $item->producto_nombre }}</td>
                                    <td class="px-6 py-4" style="color:var(--text-main);">{{ $item->codigo_lote }}</td>
                                    <td class="px-6 py-4" style="color:var(--text-main);">{{ $item->fecha_vencimiento ? \Carbon\Carbon::parse($item->fecha_vencimiento)->format('d/m/Y') : 'Sin vencimiento' }}</td>
                                    <td class="px-6 py-4 text-right font-bold" style="color:var(--text-main);">{{ number_format((float) $item->cantidad, 2, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-10 text-center" style="color:var(--text-muted);">La sede no tiene existencias.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
