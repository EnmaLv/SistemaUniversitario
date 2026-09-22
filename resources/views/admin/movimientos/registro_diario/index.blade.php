<x-app-layout>
    <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @include('components.alert')

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color: var(--text-main);">
                        Registro Diario del Comedor
                    </h1>
                    <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                        Bienvenido <span class="font-bold">{{ auth()->user()->persona->nombre_persona ?? auth()->user()->name }}</span> ·
                        {{ \Carbon\Carbon::now()->format('d/m/Y') }}
                    </p>
                </div>
            </div>

            <div class="rd-section-wrapper rounded-2xl">
                @if (!$receta_diario)
                    <div class="rd-blur">
                        <div class="rd-blur-content">
                            <h2 class="rd-blur-title">Debes seleccionar una comida</h2>
                            <p class="rd-blur-text">
                                Selecciona primero la comida del día y la cantidad servida para poder registrar a los estudiantes.
                            </p>
                            <a href="{{ route('admin.movimientos.registro_comida.index') }}"
                                class="rd-btn rd-btn-primary rd-blur-btn">
                                Ir a la sección de comida
                            </a>
                        </div>
                    </div>
                @endif

                <livewire:register-noti />
            </div>
        </div>
    </div>

    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/register-noti.css') }}">
    @endpush
</x-app-layout>
