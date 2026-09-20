<x-app-layout>
    <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            @include('components.alert')

            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color: var(--text-main);">Editar Receta</h1>
                    <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">Actualiza la información del plato seleccionado.</p>
                </div>
                <a href="{{ route('admin.maestros.recetas.index') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-bold text-gray-600 transition hover:border-red-500 hover:text-red-600 dark:border-gray-700 dark:text-gray-300">
                    <i class="fas fa-arrow-left text-xs"></i> Volver
                </a>
            </div>

            <div style="background-color: var(--bg-card); border-color: var(--border-color);" class="rounded-2xl border shadow-sm overflow-hidden">
                <div class="border-b border-gray-100 px-6 py-5 dark:border-gray-800">
                    <h2 class="text-lg font-extrabold" style="color: var(--text-main);">Datos de la receta</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Modifica los campos necesarios y guarda los cambios.</p>
                </div>

                <form action="{{ route('admin.maestros.recetas.update', $receta->id) }}" method="POST" class="space-y-6 p-6">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="nombre" class="mb-2 block text-sm font-bold" style="color: var(--text-main);">Nombre de la receta</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400"><i class="fas fa-utensils"></i></span>
                            <input id="nombre" name="nombre" type="text" value="{{ old('nombre', $receta->nombre) }}" required autofocus maxlength="255" placeholder="Ej. Arepa con pollo"
                                class="w-full rounded-xl border border-gray-200 bg-gray-50 py-3 pl-11 pr-4 text-sm font-medium text-gray-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100">
                        </div>
                        @error('nombre') <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="descripcion" class="mb-2 block text-sm font-bold" style="color: var(--text-main);">Descripción <span class="font-normal text-gray-400">(opcional)</span></label>
                        <textarea id="descripcion" name="descripcion" rows="5" placeholder="Describe brevemente la receta"
                            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm font-medium text-gray-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20 dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-100">{{ old('descripcion', $receta->descripcion) }}</textarea>
                        @error('descripcion') <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex justify-end gap-3 border-t border-gray-100 pt-5 dark:border-gray-800">
                        <a href="{{ route('admin.maestros.recetas.index') }}" class="rounded-xl border border-gray-300 px-5 py-2.5 text-sm font-bold text-gray-600 transition hover:border-red-500 hover:text-red-600 dark:border-gray-700 dark:text-gray-300">Cancelar</a>
                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-red-800 px-5 py-2.5 text-sm font-extrabold text-white shadow-lg transition hover:bg-red-900 active:scale-95">
                            <i class="fas fa-save text-xs"></i> Guardar cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
