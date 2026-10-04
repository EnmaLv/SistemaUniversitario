@php
    $moduloActivo = session('modulo_activo', 'general');
    $esPsicologia = in_array($moduloActivo, ['psicologia', 'salud']);
    $esSalud      = strtolower($moduloActivo) === 'salud';
    $headerGradient = $esPsicologia
        ? 'from-blue-600 via-indigo-700 to-slate-900'
        : 'from-[var(--color-primary,#c52222)] to-[var(--color-tertiary,#800000)]';
@endphp

<x-app-layout>
    @include('components.alert')

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

            @case('administracion')
                @if (auth()->user()->tieneRol('paciente'))
                    @include('components.estudiante.salud-home')
                @else
                    @include('components.administracion-home')
                @endif
            @break

            @case('psicologia')
                @if (auth()->user()->tieneRol(['administrador', 'secretaria de bienestar']))
                    @include('components.psicologia-admin-home')
                @elseif (auth()->user()->tieneRol('paciente'))
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
                @if (!empty($resumenGeneral))
                    @include('components.resumen-general')
                @else
                    <div
                        class="p-4 bg-amber-50 dark:bg-amber-900/30 text-amber-800 dark:text-amber-200 border border-amber-200 dark:border-amber-800 rounded-2xl flex items-center gap-3">
                        <div class="text-xl text-amber-600 dark:text-amber-400">
                            <i class="fas fa-chart-pie"></i>
                        </div>
                        <div class="text-sm font-medium">
                            Estadísticas en desarrollo.
                        </div>
                    </div>
                @endif
        @endswitch
    </div>

    @push('css')
        <link rel="stylesheet" href="{{ asset('css/home.css') }}">
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
        @yield('grafica')
    @endpush
</x-app-layout>
