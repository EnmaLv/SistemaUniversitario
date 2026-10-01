{{-- resources/views/components/consulta-stepper.blade.php --}}
{{--
    Progreso del flujo de consulta.
    - step:     paso activo (1, 2 o 3)
    - consulta: opcional; si se pasa, los pasos completados se vuelven enlaces
--}}
@props(['step' => 1, 'consulta' => null])

@php
    $steps = [
        1 => ['titulo' => 'Consulta',     'subtitulo' => 'Datos clínicos',      'ruta' => 'create'],
        2 => ['titulo' => 'Recetación',   'subtitulo' => 'Medicamentos',        'ruta' => 'recetacion'],
        3 => ['titulo' => 'Dispensación', 'subtitulo' => 'Entrega en farmacia', 'ruta' => 'dispensacion'],
    ];
    $total = count($steps);
    $step = max(1, min((int) $step, $total));
    $base = 'admin.salud.movimientos.consultas.';
    $puedeEnlazar = $consulta && $consulta->exists;
@endphp

<nav aria-label="Progreso de la consulta" {{ $attributes }}>

    {{-- Móvil: texto + barra de progreso --}}
    <div class="sm:hidden">
        <div class="flex items-baseline justify-between gap-3 text-xs">
            <span class="font-bold" style="color: var(--text-main);">
                Paso {{ $step }} de {{ $total }}: {{ $steps[$step]['titulo'] }}
            </span>
            @if ($step < $total)
                <span class="text-gray-500 dark:text-gray-400">Luego: {{ $steps[$step + 1]['titulo'] }}</span>
            @endif
        </div>
        <div class="mt-2 h-1.5 rounded-full bg-gray-200 dark:bg-gray-800 overflow-hidden" role="progressbar"
            aria-valuemin="1" aria-valuemax="{{ $total }}" aria-valuenow="{{ $step }}">
            <div class="h-full rounded-full bg-sky-600" style="width: {{ ($step / $total) * 100 }}%"></div>
        </div>
    </div>

    {{-- Escritorio: pasos con conectores --}}
    <ol class="hidden sm:flex items-center">
        @foreach ($steps as $n => $info)
            @php
                $estado = $n < $step ? 'completado' : ($n === $step ? 'activo' : 'pendiente');
                $href = $estado === 'completado' && $puedeEnlazar ? route($base . $info['ruta'], $consulta) : null;
            @endphp

            <li class="flex items-center {{ $n < $total ? 'flex-1' : '' }}">
                @if ($href)
                    <a href="{{ $href }}" title="Volver a {{ $info['titulo'] }}"
                        class="group flex items-center gap-3 shrink-0 rounded-xl -m-1.5 p-1.5 hover:bg-gray-100 dark:hover:bg-white/5 focus-visible:outline focus-visible:outline-2 focus-visible:outline-sky-600 transition-colors">
                @else
                    <div class="flex items-center gap-3 shrink-0" @if ($estado === 'activo') aria-current="step" @endif>
                @endif

                    <span @class([
                        'w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold shrink-0 border-2',
                        'bg-emerald-600 border-emerald-600 text-white' => $estado === 'completado',
                        'bg-sky-600 border-sky-600 text-white ring-4 ring-sky-100 dark:ring-sky-950' => $estado === 'activo',
                        'border-gray-300 dark:border-gray-700 text-gray-400 dark:text-gray-500' => $estado === 'pendiente',
                    ])>
                        @if ($estado === 'completado')
                            <i class="fas fa-check text-[11px]" aria-hidden="true"></i>
                            <span class="sr-only">Completado:</span>
                        @else
                            {{ $n }}
                        @endif
                    </span>

                    <span class="leading-tight">
                        <span @class([
                            'block text-sm font-semibold',
                            'text-emerald-700 dark:text-emerald-400' => $estado === 'completado',
                            'text-sky-700 dark:text-sky-300' => $estado === 'activo',
                            'text-gray-400 dark:text-gray-500' => $estado === 'pendiente',
                        ])>
                            {{ $info['titulo'] }}
                        </span>
                        <span class="block text-xs {{ $estado === 'pendiente' ? 'text-gray-400 dark:text-gray-600' : 'text-gray-500 dark:text-gray-400' }}">
                            @if ($href)
                                <span class="group-hover:hidden">{{ $info['subtitulo'] }}</span>
                                <span class="hidden group-hover:inline">Editar</span>
                            @else
                                {{ $info['subtitulo'] }}
                            @endif
                        </span>
                    </span>

                @if ($href)
                    </a>
                @else
                    </div>
                @endif

                @if ($n < $total)
                    <span aria-hidden="true"
                        class="flex-1 h-0.5 mx-4 rounded-full {{ $n < $step ? 'bg-emerald-500' : 'bg-gray-200 dark:bg-gray-800' }}"></span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
