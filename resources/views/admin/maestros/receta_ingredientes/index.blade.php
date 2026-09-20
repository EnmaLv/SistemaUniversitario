<x-app-layout>
    <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @include('components.alert')

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color: var(--text-main);">
                        Ingredientes de Recetas
                    </h1>
                    <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                        Bienvenido <span class="font-bold">{{ auth()->user()->persona->nombre_persona }}</span> ·
                        {{ \Carbon\Carbon::now()->format('d/m/Y') }}
                    </p>
                </div>

                <a href="{{ route('admin.maestros.receta_ingredientes.create') }}"
                    class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-red-800 hover:bg-red-900 text-white font-extrabold text-sm shadow-lg active:scale-95 transition-all">
                    <i class="fas fa-plus text-xs"></i>
                    <span>Asignar Insumos</span>
                </a>
            </div>

            <div style="background-color: var(--bg-card); border-color: var(--border-color);"
                class="p-2.5 rounded-2xl border shadow-sm mb-3 flex flex-col lg:flex-row lg:items-center gap-4">
                <form action="{{ route('admin.maestros.receta_ingredientes.index') }}" method="GET" class="relative w-full">
                    <input type="hidden" name="estado" value="{{ request('estado', 1) }}">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                        <i class="fas fa-search text-sm"></i>
                    </div>
                    <input type="text" name="buscar" value="{{ request('buscar') }}"
                        placeholder="Buscar receta..."
                        style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                        class="w-full pl-10 pr-4 py-2.5 rounded-xl border text-sm font-medium focus:outline-none focus:ring-2 focus:ring-red-500 transition-all">
                </form>

                <div class="flex items-center gap-3 shrink-0">
                    <div class="flex items-center gap-2 px-3 py-2 rounded-xl border" style="border-color: var(--border-color);">
                        <span class="text-[11px] font-black uppercase tracking-wider text-gray-500 dark:text-gray-400">Activas</span>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="estadoToggle" class="sr-only peer" {{ request('estado', 1) == 1 ? 'checked' : '' }}>
                            <div class="w-10 h-6 bg-gray-300 dark:bg-gray-700 rounded-full peer peer-checked:bg-red-700 transition-colors relative after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-4"></div>
                        </label>
                    </div>
                </div>
            </div>

            <div style="background-color: var(--bg-card); border-color: var(--border-color);" class="rounded-2xl border shadow-sm overflow-hidden">
                <div class="overflow-x-auto" id="printArea">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/50 dark:bg-black/20 border-b border-gray-100 dark:border-gray-800 text-[13px] font-black uppercase tracking-wider">
                                <th class="px-6 py-4 text-center" style="width: 80px;">#</th>
                                <th class="px-6 py-4 text-center">Receta</th>
                                <th class="px-6 py-4 text-center">Ingredientes</th>
                                <th class="px-6 py-4 text-center" style="width: 140px;">Estado</th>
                                <th class="px-6 py-4 text-center" style="width: 120px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800/60 text-xs font-medium">
                            @forelse($recetas as $receta)
                                <x-table-row :id="$receta->id">
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center px-3 py-1 text-[12px] font-black rounded-lg text-gray-600 dark:text-gray-300 border border-gray-300 dark:border-gray-800">
                                            {{ ($recetas->currentPage() - 1) * $recetas->perPage() + $loop->iteration }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap font-bold" style="color: var(--text-main);">
                                        <div class="leading-5">{{ $receta->nombre }}</div>
                                        @if (!empty($receta->descripcion))
                                            <div class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">
                                                {{ Str::limit($receta->descripcion, 90) }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if ($receta->recetaIngredientes->isEmpty())
                                            <span class="inline-flex items-center rounded-full border border-dashed border-gray-300 px-2.5 py-1 text-[11px] font-semibold text-gray-400">
                                                Sin ingredientes
                                            </span>
                                        @else
                                            @php
                                                $ingredientes = $receta->recetaIngredientes;
                                                $totalIngredientes = count($ingredientes);
                                                $showMore = $totalIngredientes > 3;
                                            @endphp
                                            <ul id="receta-{{ $receta->id }}" class="space-y-1 text-left inline-block">
                                                @foreach ($ingredientes as $index => $ing)
                                                    <li class="ingrediente-item text-sm text-gray-600 dark:text-gray-300 {{ $showMore && $index >= 3 ? 'hidden' : '' }}">
                                                        <span class="font-semibold text-gray-700 dark:text-gray-200">{{ optional($ing->producto)->nombre ?? 'Producto eliminado' }}</span>
                                                        <span class="text-gray-500">- {{ round($ing->cantidad_porcion) }} {{ optional($ing->unidad)->nombre ?? '' }}</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                            @if ($showMore)
                                                <div class="mt-2">
                                                    <button type="button" class="text-[11px] font-bold text-red-700 hover:text-red-800 ver-mas-btn" data-recipiente-id="{{ $receta->id }}">
                                                        Ver más...
                                                    </button>
                                                    <button type="button" class="hidden text-[11px] font-bold text-red-700 hover:text-red-800 ver-menos-btn" data-recipiente-id="{{ $receta->id }}">
                                                        Ver menos
                                                    </button>
                                                </div>
                                            @endif
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        @if ($receta->estado)
                                            <span class="inline-flex items-center gap-1 px-3 py-1 text-[10px] font-black rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900">
                                                <i class="fas fa-check-circle"></i> Activo
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-3 py-1 text-[10px] font-black rounded-lg bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-900">
                                                <i class="fas fa-times-circle"></i> Inactivo
                                            </span>
                                        @endif
                                    </td>
                                    <x-table-actions
                                        :id="$receta->id"
                                        baseUrl="admin/maestros/receta_ingredientes"
                                        :status="$receta->estado"
                                        :show="false" />
                                </x-table-row>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-400 dark:text-gray-500 font-bold text-xs uppercase tracking-wider">
                                        <i class="fas fa-utensils text-3xl mb-3 block text-gray-300 dark:text-gray-700"></i>
                                        No hay ingredientes de recetas registradas
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($recetas->hasPages())
                    <div class="p-4 border-t border-gray-100 dark:border-gray-800 flex justify-center">
                        {{ $recetas->onEachSide(1)->appends(request()->query())->links('partials.pagination') }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        document.getElementById('estadoToggle')?.addEventListener('change', function () {
            const params = new URLSearchParams(window.location.search);
            params.set('estado', this.checked ? '1' : '0');
            window.location.href = `${window.location.pathname}?${params.toString()}`;
        });

        document.querySelectorAll('.ver-mas-btn').forEach((button) => {
            const recipeId = button.dataset.recipienteId;
            const items = document.querySelectorAll(`#receta-${recipeId} .ingrediente-item`);
            const verMenosBtn = document.querySelector(`.ver-menos-btn[data-recipiente-id="${recipeId}"]`);

            button.addEventListener('click', function () {
                items.forEach((item, index) => {
                    if (index >= 3) {
                        item.classList.remove('hidden');
                    }
                });
                button.classList.add('hidden');
                if (verMenosBtn) verMenosBtn.classList.remove('hidden');
            });
        });

        document.querySelectorAll('.ver-menos-btn').forEach((button) => {
            const recipeId = button.dataset.recipienteId;
            const items = document.querySelectorAll(`#receta-${recipeId} .ingrediente-item`);
            const verMasBtn = document.querySelector(`.ver-mas-btn[data-recipiente-id="${recipeId}"]`);

            button.addEventListener('click', function () {
                items.forEach((item, index) => {
                    if (index >= 3) {
                        item.classList.add('hidden');
                    }
                });
                if (verMasBtn) verMasBtn.classList.remove('hidden');
                button.classList.add('hidden');
            });
        });

        window.confirmDelete = function (button) {
            const text = '¿Desea eliminar los ingredientes asociados a esta receta?';
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '¿Estás seguro?',
                    text,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#b91c1c',
                    cancelButtonColor: '#475569',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        button.closest('form').submit();
                    }
                });
                return;
            }

            if (confirm(text)) {
                button.closest('form').submit();
            }
        };
    </script>
</x-app-layout>
