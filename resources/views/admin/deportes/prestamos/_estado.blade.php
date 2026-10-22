@php
    $clasesEstado = [
        'activo'     => 'bg-sky-50 text-sky-700 dark:bg-sky-950/40 dark:text-sky-300',
        'devuelto'   => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300',
        'vencido'    => 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300',
        'sancionado' => 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300',
        'repuesto'   => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
    ];
@endphp
<span class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-[11px] font-extrabold uppercase tracking-wide {{ $clasesEstado[$estado] ?? 'bg-gray-100 text-gray-700' }}">
    {{ \App\Models\Deporte\PrestamoDeportivo::etiquetaEstado($estado) }}
</span>
