@props(['total' => 0, 'minimo' => null, 'maximo' => null])

@php
    $estadoStock = \App\Models\InventarioTotal::clasificar((float) $total, $minimo, $maximo);
    $estilosStock = [
        'agotado'      => ['bg-red-50 text-red-700 border-red-200 dark:bg-red-950/40 dark:text-red-400 dark:border-red-900', 'fa-circle-xmark', 'Agotado'],
        'bajo_minimo'  => ['bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-400 dark:border-amber-900', 'fa-triangle-exclamation', 'Bajo el mínimo'],
        'sobre_maximo' => ['bg-violet-50 text-violet-700 border-violet-200 dark:bg-violet-950/40 dark:text-violet-400 dark:border-violet-900', 'fa-arrow-up', 'Sobre el máximo'],
        'normal'       => ['bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-400 dark:border-emerald-900', 'fa-check', 'Normal'],
    ][$estadoStock];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 px-2.5 py-1 text-[10px] font-black rounded-lg border ' . $estilosStock[0]]) }}
    title="Mínimo {{ $minimo ?? '—' }} · Máximo {{ $maximo ?? '—' }}">
    <i class="fas {{ $estilosStock[1] }}"></i>
    {{ rtrim(rtrim(number_format((float) $total, 2, ',', '.'), '0'), ',') }} · {{ $estilosStock[2] }}
</span>
