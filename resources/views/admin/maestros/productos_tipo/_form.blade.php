@php
    $estiloCampo = 'background-color:var(--input-bg);border-color:var(--input-border);color:var(--text-main);';
    $claseCampo = 'w-full rounded-xl border px-3 py-2.5 text-sm font-medium outline-none focus:ring-2 focus:ring-red-800/20 focus:border-red-800 transition-all';
    $claseEtiqueta = 'block text-xs font-black uppercase tracking-wider mb-1.5';
    $costo = old('costo_usd_ppr', $producto?->precioProducto?->costo_usd_ppr);
    $envaseElegido = old('presentacion_id', $producto?->enp_id);
    $porCaja = old('unidades_presentacion_pdt', $producto && $producto->enp_id ? (int) $producto->unidades_presentacion_pdt : null);
@endphp

<form action="{{ $action }}" method="POST" enctype="multipart/form-data" class="rd-prevent-double-submit"
    x-data="{ conCaja: {{ $envaseElegido ? 'true' : 'false' }} }">
    @csrf
    @if ($producto)
        @method('PUT')
    @endif

    <div class="rounded-2xl border shadow-sm p-5 sm:p-6" style="background-color:var(--bg-card);border-color:var(--border-color);">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label for="nombre" class="{{ $claseEtiqueta }}" style="color:var(--text-main);">Nombre</label>
                <input type="text" id="nombre" name="nombre" value="{{ old('nombre', $producto?->nombre_pdt) }}" placeholder="{{ $config['ejemplo'] }}"
                    class="{{ $claseCampo }}" style="{{ $estiloCampo }}" required maxlength="255">
                @error('nombre') <p class="mt-1 text-xs font-semibold text-rose-500">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="categoria_id" class="{{ $claseEtiqueta }}" style="color:var(--text-main);">Categoría</label>
                <select id="categoria_id" name="categoria_id" class="{{ $claseCampo }}" style="{{ $estiloCampo }}" required>
                    <option value="">Seleccione...</option>
                    @foreach ($categorias as $categoria)
                        <option value="{{ $categoria->id_cap }}" @selected(old('categoria_id', $producto?->cap_id) == $categoria->id_cap)>{{ $categoria->nombre_cap }}</option>
                    @endforeach
                </select>
                @if ($categorias->isEmpty())
                    <p class="mt-1 text-xs font-semibold text-amber-600">No hay categorías para este tipo. Créalas primero en Categorías.</p>
                @endif
                @error('categoria_id') <p class="mt-1 text-xs font-semibold text-rose-500">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="unidad_id" class="{{ $claseEtiqueta }}" style="color:var(--text-main);">Unidad de medida</label>
                <select id="unidad_id" name="unidad_id" class="{{ $claseCampo }}" style="{{ $estiloCampo }}" required>
                    <option value="">Seleccione...</option>
                    @foreach ($unidades as $unidad)
                        <option value="{{ $unidad->id_unm }}" @selected(old('unidad_id', $producto?->unm_id) == $unidad->id_unm)>{{ $unidad->nombre_unm }}</option>
                    @endforeach
                </select>
                <p class="mt-1 text-[11px]" style="color:var(--text-muted);">Normalmente "Unidad": se cuentan por piezas.</p>
                @error('unidad_id') <p class="mt-1 text-xs font-semibold text-rose-500">{{ $message }}</p> @enderror
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
                        <label for="presentacion_id" class="{{ $claseEtiqueta }}" style="color:var(--text-main);">Tipo de caja o paquete</label>
                        <select id="presentacion_id" name="presentacion_id" class="{{ $claseCampo }}" style="{{ $estiloCampo }}" x-bind:disabled="!conCaja">
                            <option value="">Seleccione...</option>
                            @foreach ($envases as $envase)
                                <option value="{{ $envase->id_enp }}" @selected($envaseElegido == $envase->id_enp)>{{ $envase->nombre_enp }}</option>
                            @endforeach
                        </select>
                        @error('presentacion_id') <p class="mt-1 text-xs font-semibold text-rose-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="unidades_presentacion_pdt" class="{{ $claseEtiqueta }}" style="color:var(--text-main);">Unidades por caja</label>
                        <input type="number" id="unidades_presentacion_pdt" name="unidades_presentacion_pdt" value="{{ $porCaja }}" min="1" step="1" placeholder="Ej: 12"
                            class="{{ $claseCampo }}" style="{{ $estiloCampo }}" x-bind:disabled="!conCaja">
                        @error('unidades_presentacion_pdt') <p class="mt-1 text-xs font-semibold text-rose-500">{{ $message }}</p> @enderror
                    </div>
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
