@php
    $moduloActivo = strtolower(session('modulo_activo', 'general'));
    $esPsicologia = in_array($moduloActivo, ['psicologia', 'psicología', 'mental']);

    $themeColor = $esPsicologia ? 'indigo' : 'red';
@endphp

<x-app-layout>
    <div class="pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @include('components.alert')

            {{-- Encabezado Principal --}}
            <div
                class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-1 border-b border-gray-200/50 dark:border-gray-800/50">
                <div>
                    <div class="flex items-center gap-3 flex-wrap">
                        <h1 class="text-2xl sm:text-3xl font-black tracking-tight" style="color: var(--text-main);">
                            Gestionar Horario
                        </h1>
                        <span
                            class="px-2.5 py-1 text-xs font-bold rounded-full bg-{{ $themeColor }}-100 text-{{ $themeColor }}-700 dark:bg-{{ $themeColor }}-950/60 dark:text-{{ $themeColor }}-300">
                            Crear / Editar
                        </span>
                    </div>
                    <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                        Bienvenido <span
                            class="font-bold text-gray-700 dark:text-gray-200">{{ auth()->user()->nombre_completo ?? (auth()->user()->name ?? 'Usuario') }}</span>
                        · {{ \Carbon\Carbon::now()->format('d/m/Y') }}
                        @isset($grupoSeleccionado)
                            · Grupo: <span
                                class="font-bold text-{{ $themeColor }}-600 dark:text-{{ $themeColor }}-400">{{ $grupoSeleccionado->nombre }}</span>
                        @endisset
                    </p>
                </div>

                <a href="{{ route('admin.psicologia.maestros.horarios.index', isset($grupoRetorno) && $grupoRetorno ? ['grupo' => $grupoRetorno] : []) }}"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border text-xs font-bold hover:bg-gray-50 dark:hover:bg-white/5 transition-all shadow-sm self-start sm:self-center"
                    style="border-color: var(--border-color); color: var(--text-main);">
                    <i class="fas fa-arrow-left text-[10px]"></i>
                    <span>Volver</span>
                </a>
            </div>

            @if (session('error'))
                <div
                    class="p-4 text-sm text-rose-800 rounded-2xl bg-rose-50 dark:bg-rose-900/30 dark:text-rose-400 border border-rose-200 dark:border-rose-800 flex items-center gap-3">
                    <i class="fas fa-exclamation-circle text-rose-600 dark:text-rose-400 text-lg"></i>
                    <span><strong
                            class="font-black uppercase tracking-wider text-[10px] block mb-0.5">Error</strong>{{ session('error') }}</span>
                </div>
            @endif

            @include('admin.psicologia.maestros.horarios.form', [
                'horario' => null,
                'dias' => $dias,
                'grupoRetorno' => $grupoRetorno ?? null,
                'grupoSeleccionado' => $grupoSeleccionado ?? null,
                'horariosSeleccionadosIniciales' => $horariosSeleccionadosIniciales ?? [],
                'tieneCitasPendientes' => $tieneCitasPendientes ?? false,
            ])

        </div>
    </div>
</x-app-layout>