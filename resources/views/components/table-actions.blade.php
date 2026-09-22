@props([
    'id',
    'baseUrl' => null,
    'status' => null,
    'show' => true,
    'edit' => true,
    'toggle' => true,
    'onEdit' => null,
    'onShow' => null,
])

@php
    $base = $baseUrl ? rtrim($baseUrl, '/') : null;

    // Detectar si el módulo actual pertenece a Salud o Psicología
    $moduloActual = strtolower(session('modulo_activo') ?? (request()->segment(2) ?? ''));
    $esSaludOPsicologia =
        in_array($moduloActual, ['salud', 'psicologia', 'psicología']) || request()->is('*salud*', '*psicologia*');

    // Clases dinámicas según módulo (Azul para Salud/Psicología, Rojo para el resto)
    $triggerHoverClass = $esSaludOPsicologia
        ? 'hover:bg-sky-50 dark:hover:bg-sky-950/50 hover:text-sky-600'
        : 'hover:bg-rose-50 dark:hover:bg-rose-950/50 hover:text-rose-600';

    $showHoverClass = $esSaludOPsicologia
        ? 'hover:text-sky-600 hover:bg-sky-100 dark:hover:bg-sky-950/50'
        : 'hover:text-rose-600 hover:bg-rose-100 dark:hover:bg-rose-950/50';
@endphp

<td {{ $attributes->merge(['class' => 'px-6 py-4 whitespace-nowrap']) }}>
    <div class="acciones-wrap relative flex items-center justify-center h-8">

        {{-- Indicador de tres puntos dinámico --}}
        <div id="trigger-{{ $id }}"
            class="acciones-trigger w-8 h-8 flex items-center justify-center rounded-xl border dark:border-gray-600/50 text-gray-500 dark:text-gray-400 shadow-sm {{ $triggerHoverClass }} transition-all">
            <i class="fas fa-ellipsis-vertical text-xs"></i>
        </div>

        {{-- Botonera desplegada --}}
        <div id="panel-{{ $id }}" class="acciones-panel">
            @if ($base)
                {{-- Editar --}}
                @if ($edit)
                    @if ($onEdit)
                        <button type="button" onclick='{!! $onEdit !!}'
                            class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-amber-500 hover:bg-amber-100 dark:hover:bg-amber-950/50 transition-colors"
                            title="Editar">
                            <i class="fas fa-edit text-xs"></i>
                        </button>
                    @else
                        <a href="{{ url(($baseUrl ?? '') . '/' . $id . '/edit') }}"
                            class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-amber-500 hover:bg-amber-100 dark:hover:bg-amber-950/50 transition-colors"
                            title="Editar">
                            <i class="fas fa-edit text-xs"></i>
                        </a>
                    @endif
                @endif

                {{-- Inactivar / Activar --}}
                @if ($toggle && !is_null($status))
                    @if ($status)
                        <form id="form-toggle-{{ $id }}" action="{{ url($base . '/' . $id) }}" method="POST"
                            class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="button"
                                onclick="event.stopPropagation(); confirmToggleEstado({{ $id }}, 'inactivar')"
                                class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-rose-500 hover:bg-rose-100 dark:hover:bg-rose-950/50 transition-colors"
                                title="Inactivar">
                                <i class="fas fa-trash-alt text-xs"></i>
                            </button>
                        </form>
                    @else
                        <form id="form-toggle-{{ $id }}" action="{{ url($base . '/' . $id . '/activar') }}"
                            method="POST" class="inline">
                            @csrf
                            @method('PUT')
                            <button type="button"
                                onclick="event.stopPropagation(); confirmToggleEstado({{ $id }}, 'activar')"
                                class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-emerald-500 hover:bg-emerald-100 dark:hover:bg-emerald-950/50 transition-colors"
                                title="Activar">
                                <i class="fas fa-check text-xs"></i>
                            </button>
                        </form>
                    @endif
                @endif
            @endif

            {{-- Botones extra opcionales vía slot --}}
            {{ $slot }}
        </div>
    </div>
</td>
