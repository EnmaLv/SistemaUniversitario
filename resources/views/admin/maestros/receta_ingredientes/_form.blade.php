@php
    $esEdicion = isset($receta) && $receta;
    $metodo = $metodo ?? 'POST';
    $recetas = $recetas ?? [];
    $productos = $productos ?? [];
    $unidades = $unidades ?? [];
    $rutaVolver = $rutaVolver ?? route('admin.maestros.receta_ingredientes.index');
    $emptyMsgText = $esEdicion ? 'No hay ingredientes en esta receta.' : 'Agregue ingredientes para comenzar la receta.';

    $items = [];
    if (old('producto_id')) {
        $oldProdIds = old('producto_id');
        $oldCants = old('cantidad_porcion_rci');
        $oldUniIds = old('unidad_id');
        foreach ($oldProdIds as $i => $pid) {
            $prod = collect($productos)->firstWhere('id_pdt', $pid);
            $uni = collect($unidades)->firstWhere('id_unm', $oldUniIds[$i] ?? null);
            $items[] = [
                'producto_id' => $pid,
                'cantidad' => $oldCants[$i] ?? '',
                'unidad_id' => $oldUniIds[$i] ?? '',
                'producto_nombre' => $prod?->nombre_pdt ?? 'Producto #' . $pid,
                'unidad_nombre' => $uni?->nombre_unm ?? 'Unidad #' . ($oldUniIds[$i] ?? ''),
            ];
        }
    } elseif ($esEdicion && $receta) {
        foreach ($receta->recetaIngredientes as $ing) {
            $items[] = [
                'producto_id' => $ing->pdt_id,
                'cantidad' => $ing->cantidad_porcion_rci,
                'unidad_id' => $ing->unm_id,
                'producto_nombre' => $ing->producto?->nombre_pdt ?? 'Producto',
                'unidad_nombre' => $ing->unidad?->nombre_unm ?? 'Unidad',
            ];
        }
    }
@endphp

<div style="background-color: var(--bg-card); border-color: var(--border-color);"
    class="rounded-2xl border shadow-sm p-4 sm:p-6 mb-8">

    <form action="{{ $action }}" method="POST" class="rd-prevent-double-submit">
        @csrf
        @if ($metodo !== 'POST')
            @method($metodo)
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-6">
            <div class="lg:col-span-4 flex flex-col gap-6">
                <div>
                    <label class="block text-[16px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">
                        Receta
                    </label>

                    @if ($esEdicion)
                        <div class="flex items-stretch rounded-xl border overflow-hidden opacity-70"
                            style="border-color: var(--border-color);">
                            <span class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                style="border-color: var(--border-color);">
                                <i class="fas fa-utensils text-sm"></i>
                            </span>
                            <input type="text" value="{{ $receta->nombre_rct }}" disabled
                                style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none">
                        </div>
                        <input type="hidden" name="rct_id" value="{{ $receta->id_rct }}">
                    @else
                        <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                            style="border-color: var(--border-color);">
                            <span class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                style="border-color: var(--border-color);">
                                <i class="fas fa-utensils text-sm"></i>
                            </span>
                            <select id="rct_id" name="rct_id"
                                style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none transition-all">
                                <option value="" selected disabled>Seleccione...</option>
                                @foreach ($recetas as $r)
                                    <option value="{{ $r->id_rct }}"
                                        {{ old('rct_id', request('rct_id')) == $r->id_rct ? 'selected' : '' }}>
                                        {{ $r->nombre_rct }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    @error('rct_id')
                        <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                    @enderror

                    <div class="mt-3 rounded-xl border p-3 text-xs"
                        style="border-color: var(--border-color); background-color: rgba(0,0,0,0.02); color: var(--text-main);">
                        ¿No encuentras lo que buscas?
                        @canRoute('admin.maestros.recetas.create')
                            <a href="{{ route('admin.maestros.recetas.create', ['from' => url()->current()]) }}"
                                class="font-bold text-red-700 hover:text-red-800">
                                Créala aquí
                            </a>
                        @endcanRoute
                    </div>
                </div>
            </div>

            <div class="lg:col-span-8 flex flex-col gap-6">
                <div>
                    <label class="block text-[16px] font-black uppercase tracking-wider dark:text-gray-400 mb-1.5">
                        Agregar insumos
                    </label>

                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3 mb-3">
                        <div class="md:col-span-6">
                            <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                                style="border-color: var(--border-color);">
                                <span class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                    style="border-color: var(--border-color);">
                                    <i class="fas fa-box text-sm"></i>
                                </span>
                                <select id="producto_select"
                                    style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                    class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none transition-all">
                                    <option value="">Producto...</option>
                                    @foreach ($productos as $producto)
                                        <option value="{{ $producto->id_pdt }}" data-nombre="{{ $producto->nombre_pdt }}">
                                            {{ $producto->nombre_pdt }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="md:col-span-3">
                            <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                                style="border-color: var(--border-color);">
                                <span class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                    style="border-color: var(--border-color);">
                                    <i class="fas fa-hashtag text-sm"></i>
                                </span>
                                <input type="number" step="any" min="0" id="cantidad_input" placeholder="Cant."
                                    style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                    class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none transition-all">
                            </div>
                        </div>

                        <div class="md:col-span-3">
                            <div class="flex items-stretch rounded-xl border overflow-hidden focus-within:ring-2 focus-within:ring-sky-500 transition-all"
                                style="border-color: var(--border-color);">
                                <span class="flex items-center justify-center px-3.5 bg-gray-50 dark:bg-black/20 text-gray-400 border-r"
                                    style="border-color: var(--border-color);">
                                    <i class="fas fa-balance-scale text-sm"></i>
                                </span>
                                <select id="unidad_select"
                                    style="background-color: rgba(0,0,0,0.02); color: var(--text-main);"
                                    class="w-full px-3 py-2.5 text-sm font-medium border-none focus:ring-0 focus:outline-none transition-all">
                                    <option value="">Unidad</option>
                                    @foreach ($unidades as $unidad)
                                        <option value="{{ $unidad->id_unm }}" data-nombre="{{ $unidad->nombre_unm }}">
                                            {{ $unidad->nombre_unm }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <button type="button" id="agregarProducto"
                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-red-800 hover:bg-red-900 text-white font-bold text-sm shadow-md active:scale-95 transition-all mb-4">
                        <i class="fas fa-plus text-xs"></i> Agregar ingrediente
                    </button>

                    <h3 class="text-sm font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">
                        Lista de preparación
                    </h3>
                    <div class="rounded-xl border overflow-hidden"
                        style="border-color: var(--border-color); background-color: var(--bg-card);">
                        <ul id="listaProductos" class="max-h-[330px] overflow-y-auto divide-y"
                            style="border-color: var(--border-color);">
                            @if (count($items) > 0)
                                @foreach ($items as $item)
                                    <li class="flex items-center justify-between gap-3 px-4 py-3"
                                        id="prod_{{ $item['producto_id'] }}">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-10 w-10 items-center justify-center rounded-xl border"
                                                style="border-color: var(--border-color); background-color: rgba(0,0,0,0.02); color: var(--text-main);">
                                                <i class="fas fa-carrot text-xs"></i>
                                            </div>
                                            <div>
                                                <div class="text-sm font-bold" style="color: var(--text-main);">
                                                    {{ $item['producto_nombre'] }}
                                                </div>
                                                <div class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                                    {{ $item['cantidad'] }} {{ $item['unidad_nombre'] }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <input type="hidden" name="producto_id[]" value="{{ $item['producto_id'] }}">
                                            <input type="hidden" name="cantidad_porcion_rci[]" value="{{ $item['cantidad'] }}">
                                            <input type="hidden" name="unidad_id[]" value="{{ $item['unidad_id'] }}">
                                            <button type="button"
                                                class="remove-item flex h-8 w-8 items-center justify-center rounded-lg border border-red-200 bg-red-50 text-red-600 transition hover:bg-red-100">
                                                <i class="fas fa-trash-alt text-xs"></i>
                                            </button>
                                        </div>
                                    </li>
                                @endforeach
                            @else
                                <li class="empty-msg py-8 text-center text-sm font-medium text-slate-400">
                                    <i class="fas fa-layer-group mb-3 block text-3xl text-slate-300"></i>
                                    {{ $emptyMsgText }}
                                </li>
                            @endif
                        </ul>
                    </div>
                    @error('producto_id.*')
                        <p class="mt-1.5 text-xs font-semibold text-rose-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="mt-8 pt-6 border-t flex items-center justify-end gap-3"
            style="border-color: var(--border-color);">
            <a href="{{ $rutaVolver }}"
                class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl border text-sm font-bold hover:bg-gray-50 dark:hover:bg-white/5 transition-all"
                style="border-color: var(--border-color); color: var(--text-main);">
                Cancelar
            </a>
            <button type="submit"
                class="rd-submit-btn inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl text-white font-bold text-sm shadow-md active:scale-95 transition-all bg-red-800 hover:bg-red-900">
                <i class="fas fa-save text-xs"></i> {{ $esEdicion ? 'Guardar cambios' : 'Guardar' }}
            </button>
        </div>
    </form>
</div>

<style>
    html.dark {
        color-scheme: dark;
    }

    html.dark select,
    html.dark select option {
        background-color: #160c0e !important;
        color: #f8fafc !important;
        forced-color-adjust: none;
    }

    html:not(.dark) select {
        color-scheme: light;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectProd = document.getElementById('producto_select');
        const cantidadInput = document.getElementById('cantidad_input');
        const unidadSelect = document.getElementById('unidad_select');
        const btnAgregar = document.getElementById('agregarProducto');
        const lista = document.getElementById('listaProductos');
        const emptyMsgText = @json($emptyMsgText);

        function renderEmptyIfNeeded() {
            if (lista.querySelectorAll('li:not(.empty-msg)').length === 0) {
                lista.innerHTML = `<li class="empty-msg py-8 text-center text-sm font-medium text-slate-400">
                    <i class="fas fa-layer-group mb-3 block text-3xl text-slate-300"></i>
                    ${emptyMsgText}
                </li>`;
            }
        }

        function crearItem(prodId, prodNombre, cantidad, unidadId, unidadNombre) {
            const empty = lista.querySelector('.empty-msg');
            if (empty) empty.remove();

            const li = document.createElement('li');
            li.className = 'flex items-center justify-between gap-3 px-4 py-3';
            li.id = 'prod_' + prodId;
            li.innerHTML = `
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl border"
                        style="border-color: var(--border-color); background-color: rgba(0,0,0,0.02); color: var(--text-main);">
                        <i class="fas fa-carrot text-xs"></i>
                    </div>
                    <div>
                        <div class="text-sm font-bold" style="color: var(--text-main);">${prodNombre}</div>
                        <div class="text-xs font-medium text-gray-500 dark:text-gray-400">${cantidad} ${unidadNombre}</div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <input type="hidden" name="producto_id[]" value="${prodId}">
                    <input type="hidden" name="cantidad_porcion_rci[]" value="${cantidad}">
                    <input type="hidden" name="unidad_id[]" value="${unidadId}">
                    <button type="button" class="remove-item flex h-8 w-8 items-center justify-center rounded-lg border border-red-200 bg-red-50 text-red-600 transition hover:bg-red-100">
                        <i class="fas fa-trash-alt text-xs"></i>
                    </button>
                </div>
            `;

            li.querySelector('.remove-item').addEventListener('click', () => {
                li.remove();
                renderEmptyIfNeeded();
            });

            return li;
        }

        lista.querySelectorAll('.remove-item').forEach((button) => {
            button.addEventListener('click', () => {
                const li = button.closest('li');
                if (li) li.remove();
                renderEmptyIfNeeded();
            });
        });

        btnAgregar.addEventListener('click', function () {
            const prodId = selectProd.value;
            const prodNombre = selectProd.options[selectProd.selectedIndex]?.text || '';
            const cantidad = cantidadInput.value;
            const unidadId = unidadSelect.value;
            const unidadNombre = unidadSelect.options[unidadSelect.selectedIndex]?.text || '';

            if (!prodId) return Swal.fire('Error', 'Seleccione un producto.', 'error');
            if (!cantidad || Number(cantidad) <= 0) return Swal.fire('Error', 'Ingrese una cantidad válida.', 'error');
            if (!unidadId) return Swal.fire('Error', 'Seleccione una unidad.', 'error');
            if (document.getElementById('prod_' + prodId)) return Swal.fire('Aviso', 'Este producto ya está en la lista.', 'info');

            const item = crearItem(prodId, prodNombre, cantidad, unidadId, unidadNombre);
            lista.appendChild(item);

            selectProd.selectedIndex = 0;
            cantidadInput.value = '';
            unidadSelect.selectedIndex = 0;
        });
    });
</script>