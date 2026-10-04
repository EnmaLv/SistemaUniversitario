{{--
    Botón de reporte PDF para <x-table-actions>.
    - url:     enlace al PDF (o usa onClick para ejecutar JavaScript)
    - title:   texto que aparece al pasar el cursor
    - color:   rose | teal | sky | amber | emerald | violet
--}}
@props([
    'url' => null,
    'onClick' => null,
    'title' => 'Descargar PDF',
    'color' => 'rose',
    'target' => '_blank',
])

@php
    // Clases completas 
    $colores = [
        'rose'    => 'text-rose-500 hover:text-rose-600 hover:bg-rose-100 dark:hover:bg-rose-950/50',
        'teal'    => 'text-teal-600 hover:text-teal-700 hover:bg-teal-100 dark:text-teal-400 dark:hover:bg-teal-950/50',
        'sky'     => 'text-sky-500 hover:text-sky-600 hover:bg-sky-100 dark:hover:bg-sky-950/50',
        'amber'   => 'text-amber-500 hover:text-amber-600 hover:bg-amber-100 dark:hover:bg-amber-950/50',
        'emerald' => 'text-emerald-500 hover:text-emerald-600 hover:bg-emerald-100 dark:hover:bg-emerald-950/50',
        'violet'  => 'text-violet-500 hover:text-violet-600 hover:bg-violet-100 dark:hover:bg-violet-950/50',
    ];

    $clases = 'w-8 h-8 flex items-center justify-center rounded-lg transition-colors shrink-0 '
        . ($colores[$color] ?? $colores['rose']);
@endphp

@if ($onClick)
    <button type="button" onclick='{!! $onClick !!}' title="{{ $title }}" aria-label="{{ $title }}"
        {{ $attributes->merge(['class' => $clases]) }}>
        <i class="fas fa-file-pdf text-base" aria-hidden="true"></i>
    </button>
@elseif ($url)
    <a href="{{ $url }}" target="{{ $target }}" onclick="event.stopPropagation()" title="{{ $title }}"
        aria-label="{{ $title }}" {{ $attributes->merge(['class' => $clases]) }}>
        <i class="fas fa-file-pdf text-base" aria-hidden="true"></i>
    </a>
@endif