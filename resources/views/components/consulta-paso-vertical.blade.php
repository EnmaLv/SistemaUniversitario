{{--
    Indicador vertical de un paso (línea de tiempo).
    Reemplaza el contenido de tu componente actual: las props son las mismas.
    - estado: 'completado' | 'activo' | 'bloqueado'
    - numero: número del paso
    - ultimo: true en el último paso (oculta el conector)
--}}
@props(['estado' => 'bloqueado', 'numero' => 1, 'ultimo' => false])

@php
    $etiqueta = match ($estado) {
        'completado' => "Paso {$numero} completado",
        'activo'     => "Paso {$numero} en curso",
        default      => "Paso {$numero} pendiente",
    };
@endphp

<div {{ $attributes->merge(['class' => 'flex flex-col items-center self-stretch']) }}>
    <span title="{{ $etiqueta }}" @class([
        'w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold shrink-0 border-2',
        'bg-emerald-600 border-emerald-600 text-white' => $estado === 'completado',
        'bg-sky-600 border-sky-600 text-white ring-4 ring-sky-100 dark:ring-sky-950' => $estado === 'activo',
        'border-gray-300 dark:border-gray-700 text-gray-400 dark:text-gray-500' => !in_array($estado, ['completado', 'activo']),
    ])>
        @if ($estado === 'completado')
            <i class="fas fa-check text-[11px]" aria-hidden="true"></i>
        @else
            {{ $numero }}
        @endif
        <span class="sr-only">{{ $etiqueta }}</span>
    </span>

    @unless ($ultimo)
        <span aria-hidden="true" @class([
            'w-0.5 flex-1 my-1.5 min-h-[2rem] rounded-full',
            'bg-emerald-500' => $estado === 'completado',
            'bg-gray-200 dark:bg-gray-800' => $estado !== 'completado',
        ])></span>
    @endunless
</div>
