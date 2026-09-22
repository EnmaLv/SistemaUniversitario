@include('components.alert-home')

<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5 mb-6">
    {{-- Sedes --}}
    @if ($visibleModules['sedes'] ?? false)
        <a href="{{ url('/admin/maestros/sedes') }}"
           style="background-color: var(--bg-card); border-color: var(--border-color); color: var(--text-main);"
           class="p-6 rounded-2xl border shadow-sm hover:shadow-md hover:border-red-500/40 transition-all text-center flex flex-col items-center justify-center gap-3 group">
            <div class="w-14 h-14 rounded-2xl bg-red-50 dark:bg-red-950/40 flex items-center justify-center text-red-600 dark:text-red-400 text-2xl group-hover:scale-110 transition-transform">
                <i class="fas fa-building"></i>
            </div>
            <div>
                <h5 class="font-bold text-base mb-1" style="color: var(--text-main);">Sedes</h5>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 m-0">{{ $total_sedes }} registradas</p>
            </div>
        </a>
    @endif

    {{-- Categorías --}}
    @if ($visibleModules['categorias'] ?? false)
        <a href="{{ url('/admin/maestros/categorias') }}"
           style="background-color: var(--bg-card); border-color: var(--border-color); color: var(--text-main);"
           class="p-6 rounded-2xl border shadow-sm hover:shadow-md hover:border-red-500/40 transition-all text-center flex flex-col items-center justify-center gap-3 group">
            <div class="w-14 h-14 rounded-2xl bg-red-50 dark:bg-red-950/40 flex items-center justify-center text-red-600 dark:text-red-400 text-2xl group-hover:scale-110 transition-transform">
                <i class="fas fa-tags"></i>
            </div>
            <div>
                <h5 class="font-bold text-base mb-1" style="color: var(--text-main);">Categorías</h5>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 m-0">{{ $total_categorias }} activas</p>
            </div>
        </a>
    @endif

    {{-- Productos --}}
    @if ($visibleModules['productos'] ?? false)
        <a href="{{ url('/admin/maestros/productos') }}"
           style="background-color: var(--bg-card); border-color: var(--border-color); color: var(--text-main);"
           class="p-6 rounded-2xl border shadow-sm hover:shadow-md hover:border-red-500/40 transition-all text-center flex flex-col items-center justify-center gap-3 group">
            <div class="w-14 h-14 rounded-2xl bg-red-50 dark:bg-red-950/40 flex items-center justify-center text-red-600 dark:text-red-400 text-2xl group-hover:scale-110 transition-transform">
                <i class="fas fa-box-open"></i>
            </div>
            <div>
                <h5 class="font-bold text-base mb-1" style="color: var(--text-main);">Productos</h5>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 m-0">{{ $total_productos }} en inventario</p>
            </div>
        </a>
    @endif

    {{-- Proveedores --}}
    @if ($visibleModules['proveedores'] ?? false)
        <a href="{{ url('/admin/maestros/proveedores') }}"
           style="background-color: var(--bg-card); border-color: var(--border-color); color: var(--text-main);"
           class="p-6 rounded-2xl border shadow-sm hover:shadow-md hover:border-red-500/40 transition-all text-center flex flex-col items-center justify-center gap-3 group">
            <div class="w-14 h-14 rounded-2xl bg-red-50 dark:bg-red-950/40 flex items-center justify-center text-red-600 dark:text-red-400 text-2xl group-hover:scale-110 transition-transform">
                <i class="fas fa-truck"></i>
            </div>
            <div>
                <h5 class="font-bold text-base mb-1" style="color: var(--text-main);">Proveedores</h5>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 m-0">{{ $total_proveedores }} disponibles</p>
            </div>
        </a>
    @endif

    {{-- Compras --}}
    @if ($visibleModules['compras'] ?? false)
        <a href="{{ url('/admin/movimientos/compras') }}"
           style="background-color: var(--bg-card); border-color: var(--border-color); color: var(--text-main);"
           class="p-6 rounded-2xl border shadow-sm hover:shadow-md hover:border-red-500/40 transition-all text-center flex flex-col items-center justify-center gap-3 group">
            <div class="w-14 h-14 rounded-2xl bg-red-50 dark:bg-red-950/40 flex items-center justify-center text-red-600 dark:text-red-400 text-2xl group-hover:scale-110 transition-transform">
                <i class="fas fa-cart-shopping"></i>
            </div>
            <div>
                <h5 class="font-bold text-base mb-1" style="color: var(--text-main);">Compras</h5>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 m-0">{{ $total_compras }} realizadas</p>
            </div>
        </a>
    @endif

    {{-- Comidas / Recetas --}}
    @if ($visibleModules['comidas'] ?? false)
        <a href="{{ url('/admin/maestros/recetas') }}"
           style="background-color: var(--bg-card); border-color: var(--border-color); color: var(--text-main);"
           class="p-6 rounded-2xl border shadow-sm hover:shadow-md hover:border-red-500/40 transition-all text-center flex flex-col items-center justify-center gap-3 group">
            <div class="w-14 h-14 rounded-2xl bg-red-50 dark:bg-red-950/40 flex items-center justify-center text-red-600 dark:text-red-400 text-2xl group-hover:scale-110 transition-transform">
                <i class="fas fa-utensils"></i>
            </div>
            <div>
                <h5 class="font-bold text-base mb-1" style="color: var(--text-main);">Comidas</h5>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 m-0">{{ $total_compras }} registradas</p>
            </div>
        </a>
    @endif

    {{-- Por Vencer --}}
    @if ($visibleModules['por_vencer'] ?? false)
        <a href="{{ url('/admin/movimientos/lotes?filtro=por_vencer') }}"
           style="background-color: var(--bg-card); border-color: var(--border-color); color: var(--text-main);"
           class="p-6 rounded-2xl border shadow-sm hover:shadow-md hover:border-red-500/40 transition-all text-center flex flex-col items-center justify-center gap-3 group">
            <div class="w-14 h-14 rounded-2xl bg-red-100 dark:bg-red-950/60 flex items-center justify-center text-red-600 dark:text-red-400 text-2xl group-hover:scale-110 transition-transform">
                <i class="fas fa-hourglass-half"></i>
            </div>
            <div>
                <h5 class="font-bold text-base mb-1" style="color: var(--text-main);">Por Vencer</h5>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 m-0">{{ $total_lotes_por_vencer }} próximos a vencer</p>
            </div>
        </a>
    @endif

    {{-- Lotes Vencidos --}}
    @if ($visibleModules['por_vencer'] ?? false)
        <a href="{{ url('/admin/movimientos/lotes?filtro=vencido') }}"
           style="background-color: var(--bg-card); border-color: var(--border-color); color: var(--text-main);"
           class="p-6 rounded-2xl border shadow-sm hover:shadow-md hover:border-red-500/40 transition-all text-center flex flex-col items-center justify-center gap-3 group">
            <div class="w-14 h-14 rounded-2xl bg-red-100 dark:bg-red-950/60 flex items-center justify-center text-red-600 dark:text-red-400 text-2xl group-hover:scale-110 transition-transform">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
            <div>
                <h5 class="font-bold text-base mb-1" style="color: var(--text-main);">Lotes Vencidos</h5>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 m-0">{{ $total_lotes_vencidos }} requieren atención</p>
            </div>
        </a>
    @endif
</div>

{{-- RESUMEN GENERAL --}}
<div style="background-color: var(--bg-card); border-color: var(--border-color); color: var(--text-main);"
     class="rounded-2xl p-6 border shadow-sm mb-6">
    <h5 class="font-bold text-base mb-6 flex items-center gap-2" style="color: var(--text-main);">
        📊 Resumen General
    </h5>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
        <div class="p-4 rounded-xl bg-gray-50 dark:bg-black/20 border border-gray-100 dark:border-white/5">
            <div class="text-3xl font-black text-red-600 dark:text-red-500">
                {{ $total_productos }}
            </div>
            <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 mt-1">
                Total Productos
            </div>
        </div>

        <div class="p-4 rounded-xl bg-gray-50 dark:bg-black/20 border border-gray-100 dark:border-white/5">
            <div class="text-3xl font-black text-red-600 dark:text-red-500">
                {{ $total_compras }}
            </div>
            <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 mt-1">
                Compras Realizadas
            </div>
        </div>

        <div class="p-4 rounded-xl bg-gray-50 dark:bg-black/20 border border-gray-100 dark:border-white/5">
            <div class="text-3xl font-black text-red-600 dark:text-red-500">
                {{ $total_proveedores }}
            </div>
            <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 mt-1">
                Proveedores Activos
            </div>
        </div>

        <div class="p-4 rounded-xl bg-gray-50 dark:bg-black/20 border border-gray-100 dark:border-white/5">
            <div class="text-3xl font-black {{ $total_lotes_vencidos > 0 ? 'text-red-600 dark:text-red-500' : 'text-gray-700 dark:text-gray-300' }}">
                {{ $total_lotes_vencidos }}
            </div>
            <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 mt-1">
                Lotes Vencidos
            </div>
        </div>
    </div>
</div>

{{-- GRÁFICA DE ESTADÍSTICAS --}}
<div style="background-color: var(--bg-card); border-color: var(--border-color); color: var(--text-main);"
     class="rounded-2xl p-6 border shadow-sm mb-6">
    <h5 class="font-bold text-base mb-6 flex items-center gap-2" style="color: var(--text-main);">
        📈 Estadísticas del Sistema
    </h5>
    <div class="w-full relative" style="height: 300px;">
        <canvas id="mainChart"></canvas>
    </div>
</div>

@section('grafica')
    <script>
        const chartPalette = [
            '#dc2626',
            '#ef4444',
            '#f87171',
            '#b91c1c',
            '#991b1b',
            '#7f1d1d'
        ];

        const ctxMain = document.getElementById('mainChart');

        if (ctxMain) {
            new Chart(ctxMain, {
                type: 'bar',
                data: {
                    labels: [
                        'Compras',
                        'Lotes Vencidos'
                    ],
                    datasets: [{
                        label: 'Cantidad',
                        data: [
                            {{ $total_compras }},
                            {{ $total_lotes_vencidos }}
                        ],
                        backgroundColor: chartPalette.map(c => c + 'cc'),
                        borderColor: chartPalette,
                        borderWidth: 1,
                        borderRadius: 8,
                        borderSkipped: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: '#ffffff',
                            titleColor: '#111827',
                            bodyColor: '#374151',
                            borderColor: '#e5e7eb',
                            borderWidth: 1,
                            padding: 12,
                            callbacks: {
                                label: ctx => `${ctx.parsed.y} registros`
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                color: '#6b7280',
                                font: {
                                    size: 11
                                }
                            },
                            grid: {
                                color: 'rgba(156, 163, 175, 0.15)',
                                drawBorder: false
                            }
                        },
                        x: {
                            ticks: {
                                color: '#6b7280',
                                font: {
                                    size: 11
                                }
                            },
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }
    </script>
@endsection