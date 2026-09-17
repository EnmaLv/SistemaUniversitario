@props(['step' => 1])

@php
    $steps = [
        1 => [
            'titulo' => 'Requisición',
            'subtitulo' => 'Datos generales',
            'icono' => 'fa-file-alt',
        ],
        2 => [
            'titulo' => 'Productos',
            'subtitulo' => 'Detalle de la compra',
            'icono' => 'fa-boxes',
        ],
        3 => [
            'titulo' => 'Vencimientos',
            'subtitulo' => 'Fechas y finalización',
            'icono' => 'fa-calendar-check',
        ],
    ];

    $total = count($steps);
@endphp

<div
    class="rounded-2xl border shadow-sm px-4 sm:px-8 py-5 mb-8"
    style="background-color: var(--bg-card); border-color: var(--border-color);">

    <div class="flex items-center">

        @foreach ($steps as $n => $info)

            @php
                $estado = $n < $step
                    ? 'completado'
                    : ($n === $step ? 'activo' : 'pendiente');
            @endphp

            <div class="flex items-center {{ $n < $total ? 'flex-1' : '' }}">

                {{-- Paso --}}
                <div class="flex items-center gap-3 shrink-0">

                    {{-- Círculo --}}
                    <div
                        @class([
                            'w-11 h-11 rounded-full flex items-center justify-center shrink-0 border-2 transition-all duration-300',

                            // Completado
                            'bg-emerald-500 border-emerald-500 text-white shadow-md shadow-emerald-500/25'
                                => $estado === 'completado',

                            // Activo
                            'bg-red-800 border-red-800 text-white shadow-lg shadow-red-800/30 scale-110'
                                => $estado === 'activo',
                        ])
                        style="{{ $estado === 'pendiente'
                            ? 'background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main); opacity:.45;'
                            : '' }}">

                        @if ($estado === 'completado')
                            <i class="fas fa-check text-xs"></i>
                        @else
                            <i class="fas {{ $info['icono'] }} text-sm"></i>
                        @endif
                    </div>

                    {{-- Texto --}}
                    <div class="hidden sm:block leading-tight">

                        <p
                            @class([
                                'text-xs font-extrabold tracking-tight',
                                'text-emerald-600 dark:text-emerald-400'
                                    => $estado === 'completado',
                                'text-red-800 dark:text-red-400'
                                    => $estado === 'activo',
                                'text-gray-400 dark:text-gray-600'
                                    => $estado === 'pendiente',
                            ])>
                            {{ $info['titulo'] }}
                        </p>

                        <p
                            class="text-[11px] font-medium
                            {{ $estado === 'pendiente'
                                ? 'text-gray-400 dark:text-gray-600'
                                : 'text-gray-500 dark:text-gray-400' }}">
                            {{ $info['subtitulo'] }}
                        </p>

                    </div>
                </div>

                {{-- Conector --}}
                @if ($n < $total)
                    <div
                        class="flex-1 h-[2px] mx-3 sm:mx-5 rounded-full transition-colors duration-300
                        {{ $n < $step
                            ? 'bg-emerald-500'
                            : 'bg-gray-200 dark:bg-gray-800' }}">
                    </div>
                @endif

            </div>

        @endforeach

    </div>
</div>