<x-app-layout>
    <div class="pt-8 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @include('components.alert')
            <div x-data="{ search: '', items: {{ json_encode($plantillas->pluck('titulo_sph')->map(function($t) { return strtolower($t); })) }} }">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color: var(--text-main);">
                            Anexos Clínicos
                        </h1>
                        <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                            Campos adicionales para evaluaciones específicas según las necesidades del paciente.
                        </p>
                    </div>

                    <div class="flex flex-col sm:flex-row items-center gap-3 w-full md:w-auto">
                        <div class="relative w-full sm:w-64">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                <i class="fas fa-search text-xs"></i>
                            </div>
                            <input type="text" x-model="search"
                                style="background-color: var(--bg-card); border-color: var(--border-color); color: var(--text-main);"
                                class="w-full pl-9 pr-4 py-2.5 rounded-xl border text-xs font-medium focus:outline-none focus:ring-2 {{ $focusRingClass ?? 'focus:ring-indigo-500' }} transition-all"
                                placeholder="Buscar por título...">
                        </div>

                        <a href="{{ route('admin.psicologia.maestros.plantillas.create') }}"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl {{ $btnClass ?? 'bg-indigo-600 hover:bg-indigo-700' }} text-white font-bold text-xs shadow-md transition-all active:scale-95 whitespace-nowrap">
                            <i class="fas fa-plus text-xs"></i>
                            <span>Nuevo Anexo</span>
                        </a>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @forelse($plantillas as $plantilla)
                        <div x-show="search === '' || '{{ strtolower($plantilla->titulo_sph) }}'.includes(search.toLowerCase())"
                            style="background-color: var(--bg-card); border-color: var(--border-color);"
                            class="rounded-2xl p-6 border shadow-sm hover:shadow-md transition-all duration-300 flex flex-col h-full relative group">
                            
                            <div class="flex items-start gap-3.5 mb-3">
                                <div class="w-10 h-10 bg-{{ $themeColor ?? 'indigo' }}-50 dark:bg-{{ $themeColor ?? 'indigo' }}-950/50 text-{{ $themeColor ?? 'indigo' }}-600 dark:text-{{ $themeColor ?? 'indigo' }}-400 rounded-xl flex items-center justify-center shrink-0">
                                    <i class="fas fa-file-medical text-base"></i>
                                </div>
                                <div>
                                    <h3 class="text-base font-extrabold tracking-tight leading-snug" style="color: var(--text-main);">
                                        {{ $plantilla->titulo_sph }}
                                    </h3>
                                </div>
                            </div>

                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 flex-grow mb-6 leading-relaxed line-clamp-3">
                                {{ $plantilla->descripcion_general_sph ?? 'Sin descripción adicional.' }}
                            </p>

                            <div class="flex items-center justify-between pt-4 border-t border-gray-100 dark:border-gray-800/80 mt-auto">
                                <div>
                                    @if($plantilla->esta_en_uso)
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 border border-amber-200 dark:border-amber-800/50"
                                              title="Esta plantilla ya está siendo utilizada por un paciente y no puede ser modificada.">
                                            <i class="fas fa-lock text-[9px]"></i>
                                            En uso
                                        </span>
                                    @endif
                                </div>

                                <div class="flex items-center gap-2">
                                    @if(!$plantilla->esta_en_uso)
                                        <a href="{{ route('admin.psicologia.maestros.plantillas.edit', $plantilla->id_sph) }}"
                                            class="w-8 h-8 flex items-center justify-center rounded-lg bg-{{ $themeColor ?? 'indigo' }}-50 dark:bg-{{ $themeColor ?? 'indigo' }}-950/50 text-{{ $themeColor ?? 'indigo' }}-600 dark:text-{{ $themeColor ?? 'indigo' }}-400 hover:bg-{{ $themeColor ?? 'indigo' }}-600 hover:text-white transition-all text-xs"
                                            title="Editar Plantilla">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                        <form action="{{ route('admin.psicologia.maestros.plantillas.destroy', $plantilla->id_sph) }}" method="POST" class="inline-block"
                                              onsubmit="event.preventDefault(); window.AppModal.confirm('Confirmar eliminación', '¿Estás seguro de eliminar esta plantilla?').then(c => { if(c) this.submit(); });">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="w-8 h-8 flex items-center justify-center rounded-lg bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 hover:bg-rose-600 hover:text-white transition-all text-xs"
                                                title="Eliminar Plantilla">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full">
                            <div style="background-color: var(--bg-card); border-color: var(--border-color);"
                                class="rounded-2xl border-2 border-dashed p-12 text-center shadow-sm">
                                <div class="w-16 h-16 bg-gray-50 dark:bg-gray-800 text-gray-400 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-folder-open text-2xl"></i>
                                </div>
                                <h3 class="text-base font-extrabold tracking-tight mb-1" style="color: var(--text-main);">
                                    No tienes plantillas creadas
                                </h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 max-w-sm mx-auto">
                                    Crea plantillas de anexos para agilizar la redacción de los historiales clínicos de tus pacientes.
                                </p>
                            </div>
                        </div>
                    @endforelse

                    <div class="col-span-full" x-show="search !== '' && !items.some(t => t.includes(search.toLowerCase()))" x-cloak>
                        <div style="background-color: var(--bg-card); border-color: var(--border-color);"
                            class="rounded-2xl border p-12 text-center shadow-sm">
                            <div class="w-12 h-12 bg-gray-50 dark:bg-gray-800 text-gray-400 rounded-xl flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-search text-lg"></i>
                            </div>
                            <p class="text-sm font-bold" style="color: var(--text-main);">Sin anexos clínicos encontrados</p>
                        </div>
                    </div>
                </div>

                @if($plantillas->hasPages())
                    <div class="p-4 border-t border-gray-100 dark:border-gray-800 flex justify-center">
                        {{ $plantillas->onEachSide(1)->appends(request()->query())->links('partials.pagination') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>