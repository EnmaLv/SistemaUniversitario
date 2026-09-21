<x-app-layout>
    <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @include('components.alert')

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color: var(--text-main);">
                        Editar Producto
                    </h1>
                    <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                        Editando: <span class="font-bold">{{ $producto->nombre }}</span>
                    </p>
                </div>
                <a href="{{ route('admin.maestros.productos.index') }}"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl border text-xs font-bold hover:bg-gray-50 dark:hover:bg-white/5 transition-all"
                    style="border-color: var(--border-color); color: var(--text-main);">
                    <i class="fas fa-arrow-left text-[10px]"></i> Volver
                </a>
            </div>

            @include('admin.maestros.productos._form', [
                'titulo'          => 'Actualizar Producto',
                'action'          => route('admin.maestros.productos.update', $producto->id),
                'metodo'          => 'PUT',
                'rutaVolver'      => route('admin.maestros.productos.index'),
                'categorias'      => $categorias,
                'unidades'        => $unidades,
                'envases'         => $envases ?? [],
                'esMedicamento'   => $esMedicamento ?? false,
                'modelo'          => $producto,
            ])

        </div>
    </div>
</x-app-layout>