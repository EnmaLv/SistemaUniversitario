@php
    $moduloActivo = session('modulo_activo', 'general');
    $esPsicologia = in_array($moduloActivo, ['psicologia', 'salud']);
    $headerGradient = $esPsicologia
        ? 'from-blue-600 via-indigo-700 to-slate-900'
        : 'from-[var(--color-primary,#c52222)] to-[var(--color-tertiary,#800000)]';
@endphp

<x-app-layout>
    
    <x-slot name="header">
        @include('components.alert')
        @if (!auth()->user()->tieneRol('paciente'))
            <div
                class="dashboard-header bg-gradient-to-r {{ $headerGradient }} rounded-3xl p-6 md:p-8 text-white shadow-xl relative overflow-hidden mb-6">
                <div class="absolute -top-1/2 -right-10 w-72 h-72 bg-white/10 rounded-full blur-3xl pointer-events-none">
                </div>

                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 relative z-10">
                    <div>
                        <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight flex items-center gap-2 m-0">
                            🏠 Panel de Control
                        </h1>
                        <p class="mt-2 mb-0 text-base opacity-95">
                            Bienvenido de nuevo,
                            <strong>{{ auth()->user()->persona?->nombre_persona ?? auth()->user()->name }}</strong>
                        </p>
                        <p class="mb-0 text-sm opacity-80">
                            Gestiona tu inventario de manera eficiente
                        </p>
                    </div>

                    <div class="hidden md:flex items-center gap-6">
                        <div class="text-right">
                            <div class="text-xs opacity-90 mb-1">
                                📅 Hoy es
                            </div>
                            <div class="font-bold text-xl">
                                {{ \Carbon\Carbon::now()->translatedFormat('d M Y') }}
                            </div>
                            <div class="text-xs opacity-80 capitalize">
                                {{ \Carbon\Carbon::now()->translatedFormat('l') }}
                            </div>
                        </div>

                        <div
                            class="w-16 h-16 rounded-full overflow-hidden border-4 border-white/30 shadow-lg bg-white flex-shrink-0">
                            <img src="{{ asset('img/usuario-verificado.webp') }}" alt="Usuario"
                                class="w-full h-full object-cover">
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </x-slot>

    <div>
        @switch($moduloActivo)
            @case('comedor')
                @if (auth()->user()->tieneRol('paciente'))
                    @include('components.estudiante.comedor-home')
                @else
                    @include('components.comedor-home')
                @endif
            @break

            @case('salud')
                @if (auth()->user()->tieneRol('paciente'))
                    @include('components.estudiante.salud-home')
                @else
                    @include('components.salud-home')
                @endif
            @break

            @case('psicologia')
                @if (auth()->user()->tieneRol('paciente'))
                    @include('components.estudiante.psicologia-home')
                @else
                    @include('components.psicologia-home')
                @endif
            @break

            @case('beca')
                @if (auth()->user()->tieneRol(['paciente', 'becario', 'estudiante']))
                    @include('components.estudiante.becas-home')
                @else
                    @include('components.becas-home')
                @endif
            @break

            @case('transporte')
                @if (auth()->user()->tieneRol('paciente'))
                    @include('components.estudiante.transporte-home')
                @else
                    @include('components.transporte-home')
                @endif
            @break

            @default
                <div
                    class="p-4 bg-amber-50 dark:bg-amber-900/30 text-amber-800 dark:text-amber-200 border border-amber-200 dark:border-amber-800 rounded-2xl flex items-center gap-3">
                    <div class="text-xl text-amber-600 dark:text-amber-400">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                    <div class="text-sm font-medium">
                        Estadísticas en desarrollo.
                    </div>
                </div>
        @endswitch
    </div>

    @push('css')
        <link rel="stylesheet" href="{{ asset('css/home.css') }}">
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
        @include('components.alert-home')
        @yield('grafica')
    @endpush
</x-app-layout>
