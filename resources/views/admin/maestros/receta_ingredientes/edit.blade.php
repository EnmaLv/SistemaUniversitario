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
                        Ajusta la composición de <span class="font-bold">{{ $receta->nombre_rct }}</span>.
                    </p>
                </div>

                <a href="{{ route('admin.maestros.receta_ingredientes.index') }}"
                    class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl border text-sm font-bold hover:bg-gray-50 dark:hover:bg-white/5 transition-all"
                    style="border-color: var(--border-color); color: var(--text-main);">
                    <i class="fas fa-arrow-left text-xs"></i> Volver
                </a>
            </div>

            @include('admin.maestros.receta_ingredientes._form', [
                'action' => route('admin.maestros.receta_ingredientes.update', $receta->id_rct),
                'metodo' => 'PUT',
                'receta' => $receta,
                'recetas' => [],
                'productos' => $productos,
                'unidades' => $unidades,
            ])
        </div>
    </div>
</x-app-layout>