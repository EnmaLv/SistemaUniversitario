{{-- resources/views/components/consulta-encabezado.blade.php --}}
{{--
    Encabezado común del flujo de consulta: enlace de retorno, título y progreso.
--}}
@props([
    'titulo',
    'descripcion' => null,
    'step' => 1,
    'consulta' => null,
    'volverUrl' => null,
    'volverTexto' => 'Volver',
])

<header class="mb-6">
    @if ($volverUrl)
        <a href="{{ $volverUrl }}"
            class="inline-flex items-center gap-2 mb-3 text-sm font-semibold text-gray-500 dark:text-gray-400 hover:text-sky-700 dark:hover:text-sky-300 rounded-md focus-visible:outline focus-visible:outline-2 focus-visible:outline-sky-600 transition-colors">
            <i class="fas fa-arrow-left text-[11px]" aria-hidden="true"></i>
            {{ $volverTexto }}
        </a>
    @endif

    <div class="rounded-2xl border p-5 sm:p-6"
        style="background-color: var(--bg-card); border-color: var(--border-color);">
        <div class="flex flex-col lg:flex-row lg:items-center gap-5 lg:gap-12">
            <div class="min-w-0 lg:w-80 shrink-0">
                <h1 class="text-xl sm:text-2xl font-bold tracking-tight" style="color: var(--text-main);">
                    {{ $titulo }}
                    @if ($consulta && $consulta->exists)
                        <span class="text-base font-semibold text-gray-400 dark:text-gray-500">#{{ $consulta->id }}</span>
                    @endif
                </h1>
                @if ($descripcion)
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $descripcion }}</p>
                @endif
            </div>

            <x-consulta-stepper :step="$step" :consulta="$consulta" class="flex-1 min-w-0" />
        </div>
    </div>
</header>
