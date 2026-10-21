<x-app-layout>
    <div class="pt-8 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @include('components.alert')
            <div x-data="{ search: '', items: {{ json_encode($campos->pluck('titulo_cne')->map(function ($t) {return strtolower($t);})) }} }">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color: var(--text-main);">
                            Campos de Evolución
                        </h1>
                        <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                            Gestiona tus campos personalizados para las notas de evolución.
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <a href="{{ route('admin.psicologia.maestros.campos_evolucion.create') }}"
                            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl {{ $btnClass ?? 'bg-indigo-600 hover:bg-indigo-700' }} text-white font-bold text-sm shadow-md active:scale-95 transition-all">
                            <i class="fas fa-plus text-xs"></i>
                            <span>Nuevo Campo</span>
                        </a>
                    </div>
                </div>

                <div style="background-color: var(--bg-card); border-color: var(--border-color);"
                    class="p-4 rounded-2xl border shadow-sm mb-6 flex items-center justify-between gap-4">

                    <div class="relative w-full">
                        <div
                            class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <i class="fas fa-search text-sm"></i>
                        </div>
                        <input type="text" x-model="search" placeholder="Buscar campo de evolución..."
                            style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                            class="w-full pl-10 pr-4 py-2.5 rounded-xl border text-sm font-medium focus:outline-none focus:ring-2 {{ $focusRingClass ?? 'focus:ring-indigo-500' }} transition-all">
                    </div>
                </div>

                <div style="background-color: var(--bg-card); border-color: var(--border-color); color: var(--text-main);"
                    class="rounded-2xl border shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr
                                    class="bg-gray-50/50 dark:bg-black/20 border-b border-gray-100 dark:border-gray-800 text-[11px] font-black uppercase tracking-wider text-gray-400">
                                    <th class="px-6 py-4">Título / Nombre del Campo</th>
                                    <th class="px-6 py-4 text-center">Tipo</th>
                                    <th class="px-6 py-4 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800/60 text-xs font-medium">
                                @forelse($campos as $campo)
                                    <tr x-show="search === '' || '{{ strtolower($campo->titulo_cne) }}'.includes(search.toLowerCase())"
                                        class="hover:bg-gray-50/60 dark:hover:bg-white/[0.02] transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div
                                                    class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                                                    <i class="fas fa-list-check text-xs"></i>
                                                </div>
                                                <span class="font-bold text-sm" style="color: var(--text-main);">
                                                    {{ $campo->titulo_cne }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            @if (is_null($campo->psicologo_use_id))
                                                <span
                                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 border border-amber-200 dark:border-amber-800/50">
                                                    <i class="fas fa-shield-alt text-[9px]"></i>
                                                    Predefinido
                                                </span>
                                            @else
                                                <span
                                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800/50">
                                                    <i class="fas fa-user-edit text-[9px]"></i>
                                                    Personalizado
                                                </span>
                                            @endif
                                        </td>

                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            <div class="flex items-center justify-end gap-1">
                                                @if (!is_null($campo->psicologo_use_id))
                                                    <a href="{{ route('admin.psicologia.maestros.campos_evolucion.edit', $campo->id_cne) }}"
                                                        class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-950/50 transition-all"
                                                        title="Editar">
                                                        <i class="fas fa-pen-to-square text-sm"></i>
                                                    </a>

                                                    <form
                                                        action="{{ route('admin.psicologia.maestros.campos_evolucion.destroy', $campo->id_cne) }}"
                                                        method="POST" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button"
                                                            class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950/50 transition-all"
                                                            onclick="window.AppModal.confirm('Confirmar eliminación', '¿Estás seguro de eliminar este campo?').then(c => { if(c) this.closest('form').submit(); })"
                                                            title="Eliminar">
                                                            <i class="fas fa-trash-can text-sm"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <span
                                                        class="w-8 h-8 inline-flex items-center justify-center text-gray-300 dark:text-gray-600 cursor-not-allowed"
                                                        title="Campo predefinido del sistema (no modificable)">
                                                        <i class="fas fa-lock text-xs"></i>
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-6 py-12 text-center text-gray-400">
                                            <div class="flex flex-col items-center gap-2">
                                                <i class="fas fa-folder-open text-3xl opacity-40 mb-1"></i>
                                                <p class="text-xs font-semibold">No se encontraron campos de evolución
                                                    registrados.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse

                                <tr x-show="search !== '' && !items.some(t => t.includes(search.toLowerCase()))"
                                    x-cloak>
                                    <td colspan="3" class="px-6 py-12 text-center text-gray-400">
                                        <div class="flex flex-col items-center gap-2">
                                            <i class="fas fa-magnifying-glass text-3xl opacity-40 mb-1"></i>
                                            <p class="text-xs font-semibold">Sin coincidencias para la búsqueda.</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    @if ($campos->hasPages())
                        <div class="p-4 border-t border-gray-100 dark:border-gray-800 flex justify-center">
                            {{ $campos->onEachSide(1)->appends(request()->query())->links('partials.pagination') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
