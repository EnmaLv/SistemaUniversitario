@props(['producto', 'rutaVolver', 'esMedicamento' => false])

<div style="background-color: var(--bg-card); border-color: var(--border-color);"
    class="rounded-2xl border shadow-sm mb-8 overflow-hidden">

    {{-- ENCABEZADO DEL PRODUCTO --}}
    <div class="p-6 md:p-8 border-b relative overflow-hidden" style="border-color: var(--border-color);">
        {{-- Decoración de fondo opcional --}}
        <div class="absolute top-0 right-0 -mt-4 -mr-4 w-32 h-32 bg-gradient-to-br from-gray-100 to-transparent dark:from-white/5 dark:to-transparent rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-start justify-between gap-6">
            <div class="flex-1">
                <h1 class="text-2xl md:text-3xl font-black mb-3 tracking-tight" style="color: var(--text-main);">
                    {{ $producto->nombre_pdt }}
                </h1>
                
                <div class="flex flex-wrap items-center gap-2 md:gap-3">
                    {{-- Badge Código --}}
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold border bg-gray-50 dark:bg-black/20"
                        style="border-color: var(--border-color); color: var(--text-main);">
                        <i class="fas fa-barcode text-gray-400"></i>
                        {{ $producto->codigo_pdt }}
                    </span>

                    {{-- Badge Categoría --}}
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold border bg-gray-50 dark:bg-black/20"
                        style="border-color: var(--border-color); color: var(--text-main);">
                        <i class="fas fa-tags text-gray-400"></i>
                        {{ optional($producto->categoria)->nombre_cap ?? 'Sin Categoría' }}
                    </span>

                    {{-- Badge Presentación (Si es medicamento) --}}
                    @if ($esMedicamento)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold border bg-sky-50 dark:bg-sky-500/10 text-sky-600 dark:text-sky-400"
                            style="border-color: var(--border-color);">
                            <i class="fas fa-box"></i>
                            {{ optional($producto->presentacion)->nombre_enp ?? 'Presentación no definida' }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="p-6 md:p-8">
        <div class="grid grid-cols-1 xl:grid-cols-12 gap-8">
            
            {{-- COLUMNA IZQUIERDA: IMAGEN --}}
            <div class="xl:col-span-4 flex flex-col gap-4">
                <div class="rounded-2xl border overflow-hidden bg-gray-50 dark:bg-black/20 group relative aspect-square flex items-center justify-center" 
                     style="border-color: var(--border-color);">
                    @if ($producto->imagen_pdt && $producto->imagen_pdt !== 'imagenes/productos/product-defect.webp')
                        <img src="{{ asset('storage/' . $producto->imagen_pdt) }}" alt="Imagen de {{ $producto->nombre_pdt }}"
                            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                    @else
                        <div class="flex flex-col items-center justify-center text-center p-6">
                            <i class="fas fa-image text-5xl text-gray-300 dark:text-gray-600 mb-3"></i>
                            <span class="text-sm font-bold text-gray-400">Sin imagen de producto</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- COLUMNA DERECHA: DATOS Y ESTADÍSTICAS --}}
            <div class="xl:col-span-8 flex flex-col gap-8">
                
                {{-- Bloque de Precios --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Precio Bs --}}
                    <div class="rounded-2xl border p-5 bg-gradient-to-br from-gray-50 to-transparent dark:from-white/5 dark:to-transparent"
                         style="border-color: var(--border-color);">
                        <p class="text-xs font-black uppercase tracking-widest text-gray-500 mb-1">Precio Compra (BS)</p>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                            <span class="text-2xl font-black" style="color: var(--text-main);">
                                {{ number_format($producto->precio_compra_pdt, 2, ',', '.') }} <span class="text-sm font-bold text-gray-400">BS</span>
                            </span>
                        </div>
                    </div>

                    {{-- Precio USD --}}
                    <div class="rounded-2xl border p-5 bg-gradient-to-br from-gray-50 to-transparent dark:from-white/5 dark:to-transparent"
                         style="border-color: var(--border-color);">
                        <p class="text-xs font-black uppercase tracking-widest text-gray-500 mb-1">Precio Compra (USD)</p>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-green-100 dark:bg-green-500/20 text-green-600 dark:text-green-400 flex items-center justify-center text-lg">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                            <span class="text-2xl font-black" style="color: var(--text-main);">
                                {{ optional($producto->precioProducto)->costo_usd_ppr ?? (optional($producto->precioProducto)->precio_usd_ppr ?? '0.00') }} <span class="text-sm font-bold text-gray-400">USD</span>
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Cuadrícula de Inventario y Medidas --}}
                <div>
                    <h3 class="text-sm font-black uppercase tracking-wider text-gray-500 mb-4 flex items-center gap-2">
                        <i class="fas fa-boxes text-gray-400"></i> Inventario y Medidas
                    </h3>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        {{-- Stock Mínimo --}}
                        <div class="rounded-xl border p-4 bg-gray-50 dark:bg-black/20 text-center" style="border-color: var(--border-color);">
                            <i class="fas fa-arrow-down text-rose-500 mb-2 text-lg"></i>
                            <p class="text-[11px] font-black uppercase tracking-wider text-gray-500 mb-1">Stock Mín.</p>
                            <p class="text-lg font-black" style="color: var(--text-main);">{{ $producto->stock_minimo_pdt }}</p>
                        </div>
                        
                        {{-- Stock Máximo --}}
                        <div class="rounded-xl border p-4 bg-gray-50 dark:bg-black/20 text-center" style="border-color: var(--border-color);">
                            <i class="fas fa-arrow-up text-sky-500 mb-2 text-lg"></i>
                            <p class="text-[11px] font-black uppercase tracking-wider text-gray-500 mb-1">Stock Máx.</p>
                            <p class="text-lg font-black" style="color: var(--text-main);">{{ $producto->stock_maximo_pdt }}</p>
                        </div>

                        {{-- Unidad --}}
                        <div class="rounded-xl border p-4 bg-gray-50 dark:bg-black/20 text-center" style="border-color: var(--border-color);">
                            <i class="fas fa-balance-scale text-indigo-500 mb-2 text-lg"></i>
                            <p class="text-[11px] font-black uppercase tracking-wider text-gray-500 mb-1">Unidad</p>
                            <p class="text-lg font-black truncate px-2" style="color: var(--text-main);" title="{{ optional($producto->unidad)->nombre_unm ?? '—' }}">
                                {{ optional($producto->unidad)->nombre_unm ?? '—' }}
                            </p>
                        </div>

                        {{-- Peso --}}
                        <div class="rounded-xl border p-4 bg-gray-50 dark:bg-black/20 text-center" style="border-color: var(--border-color);">
                            <i class="fas fa-weight text-amber-500 mb-2 text-lg"></i>
                            <p class="text-[11px] font-black uppercase tracking-wider text-gray-500 mb-1">Peso</p>
                            <p class="text-lg font-black" style="color: var(--text-main);">{{ round($producto->peso_contenido_pdt, 2) }}</p>
                        </div>
                    </div>
                </div>

                {{-- Bloque de Descripción --}}
                <div>
                    <h3 class="text-sm font-black uppercase tracking-wider text-gray-500 mb-4 flex items-center gap-2">
                        <i class="fas fa-align-left text-gray-400"></i> Descripción
                    </h3>
                    <div class="rounded-xl border p-5 text-sm leading-relaxed"
                        style="border-color: var(--border-color); background-color: rgba(0,0,0,0.02); color: var(--text-main);">
                        @if ($producto->descripcion_pdt)
                            {!! \App\Support\Seguridad\HtmlSeguro::limpiar($producto->descripcion_pdt) !!}
                        @else
                            <div class="flex items-center gap-2 text-gray-400 italic">
                                <i class="fas fa-info-circle"></i>
                                Este producto no tiene una descripción detallada.
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Footer --}}
    <div class="p-6 border-t flex items-center justify-end bg-gray-50 dark:bg-black/10" style="border-color: var(--border-color);">
        <a href="{{ $rutaVolver }}"
            class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl border text-sm font-bold hover:bg-white dark:hover:bg-white/5 transition-all shadow-sm"
            style="border-color: var(--border-color); color: var(--text-main);">
            <i class="fas fa-arrow-left text-xs"></i> Volver al listado
        </a>
    </div>
</div>