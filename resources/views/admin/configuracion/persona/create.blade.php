<x-app-layout>
    <div class="min-h-[calc(100vh-4rem)] px-4 pb-12 pt-6 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-4xl">
            @include('components.alert')
            <div class="mb-6 flex flex-col justify-between gap-4 md:flex-row md:items-center">
                <div>
                    <h1 class="text-2xl font-extrabold tracking-tight sm:text-3xl" style="color: var(--text-main);">Registrar persona</h1>
                    <p class="mt-1 text-xs font-medium text-gray-500 sm:text-sm dark:text-gray-400">
                        Bienvenido <span class="font-bold">{{ auth()->user()->persona->nombres_per }}</span> · {{ \Carbon\Carbon::now()->format('d/m/Y') }}
                    </p>
                </div>
                <a href="{{ route('admin.configuracion.persona.index') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border px-4 py-2.5 text-sm font-bold hover:border-red-600 hover:text-red-600" style="border-color: var(--border-color); color: var(--text-main);">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
            @if (\App\Models\Persona::esContextoSaludPsicologia())
                <div class="mb-5 flex items-center gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-xs font-semibold text-amber-800 dark:border-amber-800/60 dark:bg-amber-950/30 dark:text-amber-300">
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-600 dark:bg-amber-900/50 dark:text-amber-400">
                        <i class="fas fa-info-circle text-sm"></i>
                    </div>
                    <div>
                        <p class="font-bold">Restricción de perfil</p>
                        <p class="text-[11px] opacity-90">Desde este módulo solo está permitido registrar personas con perfil de <strong>Comunidad universitaria</strong> u <strong>Obrero</strong>. El registro de Estudiantes, Docentes y Administrativos está restringido.</p>
                    </div>
                </div>
            @endif
            <div class="overflow-hidden rounded-2xl border shadow-sm" style="background-color: var(--bg-card); border-color: var(--border-color);">
                <div class="border-b px-6 py-5" style="border-color: var(--border-color);">
                    <h2 class="text-lg font-extrabold" style="color: var(--text-main);">Datos de la persona</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Completa la información requerida.</p>
                </div>
                <div class="p-6">
                    <livewire:registro-persona />
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
