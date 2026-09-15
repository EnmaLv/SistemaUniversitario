{{-- resources/views/components/consulta-stepper.blade.php --}}
@props(['step' => 1])

@php
    $steps = [
        1 => ['titulo' => 'Consulta', 'subtitulo' => 'Datos clínicos'],
        2 => ['titulo' => 'Recetación', 'subtitulo' => 'Medicamentos'],
        3 => ['titulo' => 'Dispensación', 'subtitulo' => 'Entrega en farmacia'],
    ];
    $total = count($steps);
@endphp

<div class="rounded-2xl border shadow-sm px-6 sm:px-10 py-5 mb-8"
    style="background-color: var(--bg-card); border-color: var(--border-color);">
    <div class="flex items-center">
        @foreach ($steps as $n => $info)
            @php
                $estado = $n < $step ? 'completado' : ($n === $step ? 'activo' : 'pendiente');
            @endphp

            {{-- Paso --}}
            <div class="flex items-center {{ $n < $total ? 'flex-1' : '' }}">
                <div class="flex items-center gap-3 shrink-0">
                    {{-- Círculo numerado --}}
                    <div @class([
                        'w-10 h-10 rounded-full flex items-center justify-center text-xs font-black transition-all duration-300 shrink-0 border-2',
                        'bg-emerald-500 border-emerald-500 text-white shadow-md shadow-emerald-500/25' => $estado === 'completado',
                        'bg-sky-600 border-sky-600 text-white shadow-lg shadow-sky-600/30 scale-110' => $estado === 'activo',
                    ])
                    style="{{ $estado === 'pendiente' ? 'background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main); opacity:.45;' : '' }}">
                        @if ($estado === 'completado')
                            <i class="fas fa-check text-xs"></i>
                        @else
                            {{ $n }}
                        @endif
                    </div>

                    {{-- Texto --}}
                    <div class="hidden sm:block leading-tight">
                        <p @class([
                            'text-xs font-extrabold tracking-tight',
                            'text-emerald-600 dark:text-emerald-400' => $estado === 'completado',
                            'text-sky-700 dark:text-sky-300' => $estado === 'activo',
                            'text-gray-400 dark:text-gray-600' => $estado === 'pendiente',
                        ])>
                            {{ $info['titulo'] }}
                        </p>
                        <p class="text-[11px] font-medium {{ $estado === 'pendiente' ? 'text-gray-400 dark:text-gray-600' : 'text-gray-500 dark:text-gray-400' }}">
                            {{ $info['subtitulo'] }}
                        </p>
                    </div>
                </div>

                {{-- Conector --}}
                @if ($n < $total)
                    <div class="flex-1 h-[2px] mx-4 rounded-full {{ $n < $step ? 'bg-emerald-500' : 'bg-gray-200 dark:bg-gray-800' }} transition-colors duration-300"></div>
                @endif
            </div>
        @endforeach
    </div>
</div>