<x-app-layout>
    <div class="min-h-[calc(100vh-4rem)] px-4 pb-12 pt-6 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-4xl">
            @include('components.alert')
            <div class="mb-6 flex flex-col justify-between gap-4 md:flex-row md:items-center">
                <div>
                    <h1 class="text-2xl font-extrabold tracking-tight sm:text-3xl" style="color: var(--text-main);">Registrar estudiante</h1>
                    <p class="mt-1 text-xs font-medium text-gray-500 sm:text-sm dark:text-gray-400">
                        Bienvenido <span class="font-bold">{{ auth()->user()->persona->nombre_persona }}</span> · {{ \Carbon\Carbon::now()->format('d/m/Y') }}
                    </p>
                </div>
                <a href="{{ route('admin.configuracion.persona.index') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border px-4 py-2.5 text-sm font-bold hover:border-red-600 hover:text-red-600" style="border-color: var(--border-color); color: var(--text-main);">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
            <div class="overflow-hidden rounded-2xl border shadow-sm" style="background-color: var(--bg-card); border-color: var(--border-color);">
                <div class="border-b px-6 py-5" style="border-color: var(--border-color);">
                    <h2 class="text-lg font-extrabold" style="color: var(--text-main);">Datos del estudiante</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Completa la información requerida.</p>
                </div>
                <div class="p-6">
                    <livewire:registro-persona />
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
