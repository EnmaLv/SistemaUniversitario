@php
    $clasesEstado = [
        'abierto'    => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300',
        'en_curso'   => 'bg-sky-100 text-sky-700 dark:bg-sky-950/50 dark:text-sky-300',
        'finalizado' => 'bg-gray-200 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
        'cancelado'  => 'bg-red-100 text-red-700 dark:bg-red-950/50 dark:text-red-300',
    ];
@endphp
<span class="inline-flex items-center rounded-lg px-2.5 py-1 text-[11px] font-extrabold uppercase tracking-wide {{ $clasesEstado[$torneo->estado_tde] ?? 'bg-gray-100 text-gray-600' }}">
    {{ $torneo->estadoLegible() }}
</span>
