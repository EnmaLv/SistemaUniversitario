<x-app-layout>
    @include('components.alert')

    <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color:var(--text-main);">
                        {{ $config['nuevo'] }}
                    </h1>
                    <p class="mt-1 text-xs sm:text-sm font-medium" style="color:var(--text-muted);">
                        <i class="fas {{ $config['icono'] }} mr-1" style="color:var(--color-primary)"></i>
                        Primero elige si es un implemento deportivo o material de oficina; luego completa los datos y guarda. Quedará disponible en Compras.
                    </p>
                </div>
                <a href="{{ route($config['ruta'] . '.index') }}"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl border text-xs font-bold hover:bg-gray-50 dark:hover:bg-white/5 transition-all"
                    style="border-color:var(--border-color);color:var(--text-main);">
                    <i class="fas fa-arrow-left text-[10px]"></i> Volver
                </a>
            </div>

            @include('admin.maestros.productos_tipo._form', ['action' => route($config['ruta'] . '.store')])
        </div>
    </div>
</x-app-layout>
