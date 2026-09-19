<x-app-layout>
    <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @include('components.alert')
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color: var(--text-main);">
                        Importación de estudiantes
                    </h1>
                    <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                        Bienvenido <span class="font-bold">{{ auth()->user()->persona->nombre_persona ?? auth()->user()->name }}</span> ·
                        {{ \Carbon\Carbon::now()->format('d/m/Y') }}
                    </p>
                </div>
            </div>
            <livewire:archivos />
        </div>
    </div>
</x-app-layout>
