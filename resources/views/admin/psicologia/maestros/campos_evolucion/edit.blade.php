<x-app-layout>
    <div class="pt-8 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <a href="{{ route('admin.psicologia.maestros.campos_evolucion.index') }}"
                class="inline-flex items-center gap-2 text-xs font-bold text-gray-500 hover:text-indigo-600 mb-6 transition-colors group">
                <i class="fas fa-arrow-left text-xs transition-transform group-hover:-translate-x-1"></i>
                <span>Volver a los campos</span>
            </a>
            <div class="mb-8">
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color: var(--text-main);">
                    Editar Campo de Evolución
                </h1>
                <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                    Modificar la información del campo <strong class="text-indigo-600 dark:text-indigo-400">{{ $campo->titulo_cne }}</strong>.
                </p>
            </div>
            <div style="background-color: var(--bg-card); border-color: var(--border-color); color: var(--text-main);"
                class="rounded-2xl border shadow-sm overflow-hidden p-6 sm:p-8">
                <form action="{{ route('admin.psicologia.maestros.campos_evolucion.update', $campo->id_cne) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="space-y-6">
                        <div>
                            <label for="titulo"
                                class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-2">
                                Título del Campo <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="titulo" id="titulo" value="{{ old('titulo', $campo->titulo_cne) }}"
                                style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);"
                                class="w-full h-11 px-4 border rounded-xl focus:outline-none focus:ring-2 {{ $focusRingClass ?? 'focus:ring-indigo-500' }} text-sm font-medium transition-all"
                                placeholder="Ej: Hábitos de sueño, Estado afectivo..." required>
                            @error('titulo')
                                <p class="mt-2 text-xs text-red-500 font-bold flex items-center gap-1">
                                    <i class="fas fa-circle-exclamation"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-800 flex items-center justify-end gap-3">
                        <a href="{{ route('admin.psicologia.maestros.campos_evolucion.index') }}"
                            class="px-5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 text-xs font-bold text-gray-600 dark:text-gray-300 transition-all">
                            Cancelar
                        </a>
                        <button type="submit"
                            class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl {{ $btnClass ?? 'bg-indigo-600 hover:bg-indigo-700' }} text-white text-xs font-bold shadow-md active:scale-95 transition-all">
                            <i class="fas fa-floppy-disk text-xs"></i>
                            <span>Guardar Cambios</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>