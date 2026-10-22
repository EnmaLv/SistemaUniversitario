@php
    $activo = (int) ($status ?? 1) === 1;
@endphp
<span class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-[11px] font-extrabold uppercase tracking-wide {{ $activo ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300' : 'bg-gray-200 text-gray-600 dark:bg-gray-800 dark:text-gray-400' }}">
    <i class="fas {{ $activo ? 'fa-check-circle' : 'fa-ban' }} text-[10px]"></i>
    {{ $activo ? 'Activo' : 'Inactivo' }}
</span>
