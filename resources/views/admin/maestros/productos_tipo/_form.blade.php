@php
    $estiloCampo = 'background-color:var(--input-bg);border-color:var(--input-border);color:var(--text-main);';
    $claseCampo = 'w-full rounded-xl border px-3 py-2.5 text-sm font-medium outline-none focus:ring-2 focus:ring-red-800/20 focus:border-red-800 transition-all';
    $claseEtiqueta = 'block text-xs font-black uppercase tracking-wider mb-1.5';
    $costo = old('costo_usd_ppr', $producto?->precioProducto?->costo_usd_ppr);
    $envaseElegido = old('presentacion_id', $producto?->enp_id);
    $porCaja = old('unidades_presentacion_pdt', $producto && $producto->enp_id ? (int) $producto->unidades_presentacion_pdt : null);
    $tipoElegido = old('tipo', $tipoInicial ?? null);
    $tipoElegido = is_string($tipoElegido) && isset($tipos[$tipoElegido]) ? $tipoElegido : '';
    $categoriaElegida = old('categoria_id', $producto?->cap_id);
    $categoriasPorTipo = collect($tipos)->map(fn ($tipo) => $categorias->where('tpr_id', $tipo['id'])->values());
    $ejemplos = collect($tipos)->map(fn ($tipo) => $tipo['ejemplo'] ?? '');
@endphp

<form action="{{ $action }}" method="POST" enctype="multipart/form-data" class="rd-prevent-double-submit"
    x-data="{ tipo: @js($tipoElegido), conCaja: {{ $envaseElegido ? 'true' : 'false' }}, ejemplos: @js($ejemplos) }">
    @csrf
    @if ($producto)
        @method('PUT')
    @endif

    <div class="rounded-2xl border shadow-sm p-5 sm:p-6 mb-4" style="background-color:var(--bg-card);border-color:var(--border-color);">
        <p class="{{ $claseEtiqueta }}" style="color:var(--text-main);">1. ¿Qué vas a registrar?</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            @foreach ($tipos as $clave => $tipo)
                <label class="flex items-start gap-3 rounded-xl border p-4 cursor-pointer transition-all"
                    x-bind:class="tipo === @js($clave) ? 'border-red-800 ring-2 ring-red-800/20' : ''"
                    style="border-color:var(--border-color);">
                    <input type="radio" name="tipo" value="{{ $clave }}" x-model="tipo" class="mt-1 text-red-800 focus:ring-red-800" @checked($tipoElegido === $clave) required>
                    <span>
                        <span class="flex items-center gap-2 text-sm font-extrabold" style="color:var(--text-main);">
                            <i class="fas {{ $tipo['icono'] }}" style="color:var(--color-primary)"></i> {{ $tipo['etiqueta'] }}
                        </span>
                        <span class="block mt-0.5 text-xs" style="color:var(--text-muted);">{{ $tipo['ayuda'] }}</span>
                    </span>
                </label>
            @endforeach
        </div>
        @error('tipo') <p class="mt-2 text-xs font-semibold text-rose-500">{{ $message }}</p> @enderror
        <p class="mt-3 text-xs" style="color:var(--text-muted);" x-show="!tipo">Elige una opción para ver el resto del formulario.</p>
    </div>

    <div class="rounded-2xl border shadow-sm p-5 sm:p-6" style="background-color:var(--bg-card);border-color:var(--border-color);" x-show="tipo" x-cloak>
        <p class="{{ $claseEtiqueta }}" style="color:var(--text-main);">2. Datos del producto</p>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label for="nombre" class="{{ $claseEtiqueta }}" style="color:var(--text-main);">Nombre</label>
                <input type="text" id="nombre" name="nombre" value="{{ old('nombre', $producto?->nombre_pdt) }}" x-bind:placeholder="ejemplos[tipo] || ''"
                    class="{{ $claseCampo }}" style="{{ $estiloCampo }}" required maxlength="255">
                @error('nombre') <p class="mt-1 text-xs font-semibold text-rose-500">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="categoria_{{ $tipoElegido ?: 'tipo' }}" class="{{ $claseEtiqueta }}" style="color:var(--text-main);">Categoría</label>
                @foreach ($tipos as $clave => $tipo)
                    <div x-show="tipo === @js($clave)">
                        <select id="categoria_{{ $clave }}" name="categoria_id" class="{{ $claseCampo }}" style="{{ $estiloCampo }}"
                            x-bind:disabled="tipo !== @js($clave)" @disabled($tipoElegido !== $clave) required>
                            <option value="">Seleccione...</option>
                            @foreach ($categoriasPorTipo[$clave] as $categoria)
                                <option value="{{ $categoria->id_cap }}" @selected($categoriaElegida == $categoria->id_cap)>{{ $categoria->nombre_cap }}</option>
                            @endforeach
                        </select>
                        @if ($categoriasPorTipo[$clave]->isEmpty())
                            <p class="mt-1 text-xs font-semibold text-amber-600">No hay categorías de {{ mb_strtolower($tipo['etiqueta']) }}. Créalas primero en Categorías.</p>
                        @endif
                    </div>
                @endforeach
                @error('categoria_id') <p class="mt-1 text-xs font-semibold text-rose-500">{{ $message }}</p> @enderror
            </div>

            <div>
                <span class="{{ $claseEtiqueta }}" style="color:var(--text-main);">Unidad de medida</span>
                <div class="{{ $claseCampo }} flex items-center gap-2" style="{{ $estiloCampo }}">
                    <i class="fas fa-cube text-xs" style="color:var(--text-muted);"></i>
                    <span>{{ \App\Models\TipoProducto::UNIDAD_DEL_AREA }}</span>
                </div>
                <p class="mt-1 text-[11px]" style="color:var(--text-muted);">Aquí todo se cuenta por unidad, aunque se compre por caja o paquete.</p>
            </div>

            <div>
                <label for="costo_usd_ppr" class="{{ $claseEtiqueta }}" style="color:var(--text-main);">Precio de compra (USD)</label>
                <input type="number" id="costo_usd_ppr" name="costo_usd_ppr" value="{{ $costo }}" min="0" step="0.01" placeholder="Ej: 12.50"
                    class="{{ $claseCampo }}" style="{{ $estiloCampo }}" required>
                <p class="mt-1 text-[11px]" style="color:var(--text-muted);">Lo que cuesta una unidad, o una caja si se compra por caja. Compras lo trae solo.</p>
                @error('costo_usd_ppr') <p class="mt-1 text-xs font-semibold text-rose-500">{{ $message }}</p> @enderror
            </div>

            <div class="md:col-span-2 rounded-xl border p-4" style="border-color:var(--border-color);">
                <label class="flex items-center gap-2 text-sm font-bold cursor-pointer" style="color:var(--text-main);">
                    <input type="checkbox" x-model="conCaja" class="rounded border-gray-300 text-red-800 focus:ring-red-800">
                    Se compra por caja o paquete (por ejemplo, una caja de 12 lápices)
                </label>
                <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-4" x-show="conCaja" x-cloak>
                    <div>
                        <label for="presentacion_{{ $tipoElegido ?: 'tipo' }}" class="{{ $claseEtiqueta }}" style="color:var(--text-main);">Tipo de caja o paquete</label>
                        @foreach ($tipos as $clave => $tipo)
                            <div x-show="tipo === @js($clave)">
                                <select id="presentacion_{{ $clave }}" name="presentacion_id" class="{{ $claseCampo }}" style="{{ $estiloCampo }}"
                                    x-bind:disabled="!conCaja || tipo !== @js($clave)" @disabled(! $envaseElegido || $tipoElegido !== $clave)>
                                    <option value="">Seleccione...</option>
                                    @foreach ($envases[$clave] as $envase)
                                        <option value="{{ $envase->id_enp }}" @selected($envaseElegido == $envase->id_enp)>{{ $envase->nombre_enp }}</option>
                                    @endforeach
                                </select>
                                @if ($envases[$clave]->isEmpty())
                                    <p class="mt-1 text-xs font-semibold text-amber-600">No hay cajas ni paquetes registrados para este tipo. Pide al administrador que los cargue.</p>
                                @endif
                            </div>
                        @endforeach
                        @error('presentacion_id') <p class="mt-1 text-xs font-semibold text-rose-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="unidades_presentacion_pdt" class="{{ $claseEtiqueta }}" style="color:var(--text-main);">Unidades por caja</label>
                        <input type="number" id="unidades_presentacion_pdt" name="unidades_presentacion_pdt" value="{{ $porCaja }}" min="2" step="1" placeholder="Ej: 12"
                            class="{{ $claseCampo }}" style="{{ $estiloCampo }}" x-bind:disabled="!conCaja">
                        @error('unidades_presentacion_pdt') <p class="mt-1 text-xs font-semibold text-rose-500">{{ $message }}</p> @enderror
                    </div>
                    <p class="sm:col-span-2 text-[11px]" style="color:var(--text-muted);">En Compras escribes cuántas cajas llegan; al inventario entran como unidades sueltas (3 cajas de 12 = 36 unidades).</p>
                </div>
                <p class="mt-2 text-[11px]" style="color:var(--text-muted);" x-show="!conCaja">Si no lo marcas, se compra y se cuenta por unidad.</p>
            </div>

            <div>
                <label for="stock_minimo_pdt" class="{{ $claseEtiqueta }}" style="color:var(--text-main);">Stock mínimo (unidades)</label>
                <input type="number" id="stock_minimo_pdt" name="stock_minimo_pdt" value="{{ old('stock_minimo_pdt', $producto ? (int) $producto->stock_minimo_pdt : 0) }}" min="0" step="1"
                    class="{{ $claseCampo }}" style="{{ $estiloCampo }}" required>
                <p class="mt-1 text-[11px]" style="color:var(--text-muted);">Por debajo de esta cantidad se avisa que hay que comprar.</p>
                @error('stock_minimo_pdt') <p class="mt-1 text-xs font-semibold text-rose-500">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="stock_maximo_pdt" class="{{ $claseEtiqueta }}" style="color:var(--text-main);">Stock máximo (unidades)</label>
                <input type="number" id="stock_maximo_pdt" name="stock_maximo_pdt" value="{{ old('stock_maximo_pdt', $producto ? (int) $producto->stock_maximo_pdt : 0) }}" min="0" step="1"
                    class="{{ $claseCampo }}" style="{{ $estiloCampo }}" required>
                <p class="mt-1 text-[11px]" style="color:var(--text-muted);">Escribe 0 si no hay límite.</p>
                @error('stock_maximo_pdt') <p class="mt-1 text-xs font-semibold text-rose-500">{{ $message }}</p> @enderror
            </div>

            <div class="md:col-span-2">
                <label for="descripcion" class="{{ $claseEtiqueta }}" style="color:var(--text-main);">Descripción (opcional)</label>
                <textarea id="descripcion" name="descripcion" rows="3" maxlength="1000" placeholder="Talla, color, marca u otro detalle útil"
                    class="{{ $claseCampo }}" style="{{ $estiloCampo }}">{{ old('descripcion', $producto ? strip_tags((string) $producto->descripcion_pdt) : '') }}</textarea>
                @error('descripcion') <p class="mt-1 text-xs font-semibold text-rose-500">{{ $message }}</p> @enderror
            </div>

            <div class="md:col-span-2">
                <label for="imagen" class="{{ $claseEtiqueta }}" style="color:var(--text-main);">Imagen (opcional)</label>
                <input type="file" id="imagen" name="imagen" accept="image/*" class="block w-full text-sm" style="color:var(--text-main);">
                @error('imagen') <p class="mt-1 text-xs font-semibold text-rose-500">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="mt-6 pt-5 border-t flex items-center justify-end gap-3" style="border-color:var(--border-color);">
            <a href="{{ route($config['ruta'] . '.index') }}"
                class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl border text-sm font-bold hover:bg-gray-50 dark:hover:bg-white/5 transition-all"
                style="border-color:var(--border-color);color:var(--text-main);">
                Cancelar
            </a>
            <button type="submit"
                class="rd-submit-btn inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl bg-red-800 hover:bg-red-900 text-white font-extrabold text-sm shadow-md active:scale-95 transition-all">
                <i class="fas fa-save text-xs"></i> {{ $producto ? 'Guardar cambios' : 'Guardar' }}
            </button>
        </div>
    </div>
</form>
