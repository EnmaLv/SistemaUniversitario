@props(['estado' => 'bloqueado', 'numero' => 1, 'ultimo' => false])

<div class="flex flex-col items-center">
    <div @class([
        'w-9 h-9 rounded-full flex items-center justify-center font-black text-sm shadow-sm transition-all shrink-0',
        'bg-emerald-500 text-white' => $estado === 'completado',
        'bg-sky-600 text-white ring-4 ring-sky-100 dark:ring-sky-950/50' => $estado === 'activo',
        'bg-gray-200 dark:bg-gray-800 text-gray-400' => $estado === 'bloqueado',
    ])>
        @if ($estado === 'completado')
            <i class="fas fa-check text-xs"></i>
        @elseif ($estado === 'activo')
            <span class="relative flex h-2.5 w-2.5">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-white"></span>
            </span>
        @else
            {{ $numero }}
        @endif
    </div>

    @unless ($ultimo)
        <div class="w-px flex-1 my-1 min-h-[2rem]"
            style="background: {{ $estado === 'completado' ? 'linear-gradient(var(--tw-color-emerald-500,#10b981), var(--border-color))' : 'var(--border-color)' }};">
        </div>
    @endunless
</div>