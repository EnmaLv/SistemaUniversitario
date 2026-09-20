<x-app-layout>
    <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            @include('components.alert')

            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color: var(--text-main);">
                        Editar Ingredientes de Receta
                    </h1>
                    <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                        Ajusta la composición de <span class="font-bold">{{ $receta->nombre }}</span>.
                    </p>
                </div>

                <a href="{{ route('admin.maestros.receta_ingredientes.index') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-bold text-gray-600 transition hover:border-red-500 hover:text-red-600 dark:border-gray-700 dark:text-gray-300">
                    <i class="fas fa-arrow-left text-xs"></i> Volver
                </a>
            </div>

            <div style="background-color: var(--bg-card); border-color: var(--border-color);" class="rounded-2xl border shadow-sm overflow-hidden">
                <div class="border-b border-gray-100 px-6 py-5 dark:border-gray-800">
                    <h2 class="text-lg font-extrabold" style="color: var(--text-main);">Configuración de la receta</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Agrega o elimina ingredientes de la preparación.</p>
                </div>

                <form action="{{ route('admin.maestros.receta_ingredientes.update', $receta->id) }}" method="POST" class="space-y-6 p-6">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">
                        <div class="lg:col-span-1">
                            <label class="mb-2 block text-sm font-bold" style="color: var(--text-main);">Receta</label>
                            <div class="rounded-xl border border-gray-200 bg-gray-50 px-3 py-3 text-sm font-medium text-slate-700 dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100">
                                {{ $receta->nombre }}
                            </div>
                            <input type="hidden" name="recetas_id" value="{{ $receta->id }}">
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Estás editando la composición técnica de esta preparación.</p>
                        </div>

                        <div class="lg:col-span-3">
                            <label class="mb-2 block text-sm font-bold" style="color: var(--text-main);">Agregar nuevos ingredientes</label>
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start mb-4">
                                <div class="flex-1">
                                    <select id="producto_select"
                                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-3 text-sm font-medium text-gray-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100">
                                        <option value="">Producto...</option>
                                        @foreach ($productos as $producto)
                                            <option value="{{ $producto->id }}" data-nombre="{{ $producto->nombre }}">
                                                {{ $producto->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="w-full sm:w-28">
                                    <input type="number" step="any" min="0" id="cantidad_input" placeholder="Cant."
                                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-3 text-sm font-medium text-gray-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100">
                                </div>

                                <div class="w-full sm:w-40">
                                    <select id="unidad_select"
                                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-3 text-sm font-medium text-gray-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100">
                                        <option value="">Unidad</option>
                                        @foreach ($unidades as $unidad)
                                            <option value="{{ $unidad->id }}" data-nombre="{{ $unidad->nombre }}">
                                                {{ $unidad->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <button type="button" id="agregarProducto"
                                    class="inline-flex h-[48px] w-[48px] items-center justify-center rounded-xl bg-red-800 text-white shadow-lg transition hover:bg-red-900 active:scale-95">
                                    <i class="fas fa-plus text-sm"></i>
                                </button>
                            </div>

                            <h3 class="mb-3 text-sm font-extrabold uppercase tracking-wide text-slate-600">Ingredientes actuales</h3>
                            <div class="receta-ingredientes-panel rounded-xl border overflow-hidden">
                                <ul id="listaProductos" class="receta-ingredientes-list max-h-[330px] overflow-y-auto">
                                    @forelse ($receta->recetaIngredientes as $ing)
                                        <li class="receta-ingrediente-item flex items-center justify-between gap-3 border-b px-4 py-3 last:border-b-0" id="prod_{{ $ing->producto_id }}">
                                            <div class="flex items-center gap-3">
                                                <div class="receta-ingrediente-icon flex h-10 w-10 items-center justify-center rounded-xl border text-slate-500">
                                                    <i class="fas fa-carrot text-xs"></i>
                                                </div>
                                                <div>
                                                    <div class="receta-ingrediente-name text-sm font-bold">{{ $ing->producto->nombre }}</div>
                                                    <div class="receta-ingrediente-detail text-xs font-medium">{{ $ing->cantidad_porcion }} {{ $ing->unidad->nombre }}</div>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <input type="hidden" name="producto_id[]" value="{{ $ing->producto_id }}">
                                                <input type="hidden" name="cantidad_porcion[]" value="{{ $ing->cantidad_porcion }}">
                                                <input type="hidden" name="unidad_id[]" value="{{ $ing->unidad_id }}">
                                                <button type="button" class="remove-item flex h-8 w-8 items-center justify-center rounded-lg border border-red-200 bg-red-50 text-red-600 transition hover:bg-red-100" title="Eliminar">
                                                    <i class="fas fa-times text-xs"></i>
                                                </button>
                                            </div>
                                        </li>
                                    @empty
                                        <li class="empty-msg py-8 text-center text-sm font-medium text-slate-400">
                                            <i class="fas fa-layer-group mb-3 block text-3xl text-slate-300"></i>
                                            No hay ingredientes en esta receta.
                                        </li>
                                    @endforelse
                                </ul>
                            </div>
                            @error('producto_id.*')
                                <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 border-t border-gray-100 pt-5 dark:border-gray-800">
                        <a href="{{ route('admin.maestros.receta_ingredientes.index') }}"
                            class="rounded-xl border border-gray-300 px-5 py-2.5 text-sm font-bold text-gray-600 transition hover:border-red-500 hover:text-red-600 dark:border-gray-700 dark:text-gray-300">
                            Cancelar
                        </a>
                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-red-800 px-5 py-2.5 text-sm font-extrabold text-white shadow-lg transition hover:bg-red-900 active:scale-95">
                            <i class="fas fa-save text-xs"></i> Guardar cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const selectProd = document.getElementById('producto_select');
            const cantidadInput = document.getElementById('cantidad_input');
            const unidadSelect = document.getElementById('unidad_select');
            const btnAgregar = document.getElementById('agregarProducto');
            const lista = document.getElementById('listaProductos');

            function crearItem(prodId, prodNombre, cantidad, unidadId, unidadNombre) {
                const empty = lista.querySelector('.empty-msg');
                if (empty) empty.remove();

                const li = document.createElement('li');
                li.className = 'receta-ingrediente-item flex items-center justify-between gap-3 border-b px-4 py-3 last:border-b-0';
                li.id = 'prod_' + prodId;

                li.innerHTML = `
                    <div class="flex items-center gap-3">
                        <div class="receta-ingrediente-icon flex h-10 w-10 items-center justify-center rounded-xl border border-emerald-100 bg-emerald-50 text-emerald-600">
                            <i class="fas fa-plus text-xs"></i>
                        </div>
                        <div>
                            <div class="receta-ingrediente-name text-sm font-bold">${prodNombre}</div>
                            <div class="receta-ingrediente-detail text-xs font-medium">${cantidad} ${unidadNombre}</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="hidden" name="producto_id[]" value="${prodId}">
                        <input type="hidden" name="cantidad_porcion[]" value="${cantidad}">
                        <input type="hidden" name="unidad_id[]" value="${unidadId}">
                        <button type="button" class="remove-item flex h-8 w-8 items-center justify-center rounded-lg border border-red-200 bg-red-50 text-red-600 transition hover:bg-red-100">
                            <i class="fas fa-trash-alt text-xs"></i>
                        </button>
                    </div>
                `;

                li.querySelector('.remove-item').addEventListener('click', () => {
                    li.remove();
                    if (lista.children.length === 0) {
                        lista.innerHTML = '<li class="empty-msg py-8 text-center text-sm font-medium text-slate-400"><i class="fas fa-layer-group mb-3 block text-3xl text-slate-300"></i>No hay ingredientes en esta receta.</li>';
                    }
                });

                return li;
            }

            document.querySelectorAll('.remove-item').forEach((button) => {
                button.addEventListener('click', () => {
                    const li = button.closest('li');
                    if (li) li.remove();
                    if (lista.children.length === 0) {
                        lista.innerHTML = '<li class="empty-msg py-8 text-center text-sm font-medium text-slate-400"><i class="fas fa-layer-group mb-3 block text-3xl text-slate-300"></i>No hay ingredientes en esta receta.</li>';
                    }
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
</x-app-layout>

@push('styles')
    <style>
        .receta-ingredientes-panel {
            background: var(--input-bg) !important;
            border-color: var(--border-color) !important;
        }

        .receta-ingredientes-list {
            background: var(--input-bg) !important;
        }

        .receta-ingrediente-item {
            background: var(--bg-card) !important;
            border-color: var(--border-color) !important;
        }

        .receta-ingrediente-icon {
            background: var(--input-bg) !important;
            border-color: var(--border-color) !important;
        }

        .receta-ingrediente-name {
            color: var(--text-main) !important;
        }

        .receta-ingrediente-detail {
            color: color-mix(in srgb, var(--text-main) 65%, transparent) !important;
        }

        .receta-ingredientes-panel select,
        .receta-ingredientes-panel select option,
        #producto_select,
        #producto_select option,
        #unidad_select,
        #unidad_select option {
            background-color: var(--input-bg) !important;
            color: var(--text-main) !important;
        }

        html.dark .receta-ingredientes-panel,
        html.dark .receta-ingredientes-list,
        html.dark .receta-ingrediente-item {
            background-color: #160c0e !important;
        }

        html.dark .receta-ingredientes-panel select,
        html.dark .receta-ingredientes-panel select option,
        html.dark #producto_select,
        html.dark #producto_select option,
        html.dark #unidad_select,
        html.dark #unidad_select option {
            background-color: #160c0e !important;
            color: #f8fafc !important;
            forced-color-adjust: none;
        }

        html.dark {
            color-scheme: dark;
        }

        html.dark .receta-ingredientes-panel select,
        html.dark #producto_select,
        html.dark #unidad_select {
            color-scheme: dark;
        }

        html.dark .receta-ingredientes-panel select option,
        html.dark #producto_select option,
        html.dark #unidad_select option {
            background: #160c0e !important;
            color: #f8fafc !important;
        }

        html:not(.dark) .receta-ingredientes-panel select,
        html:not(.dark) #producto_select,
        html:not(.dark) #unidad_select {
            color-scheme: light;
        }
    </style>
@endpush
