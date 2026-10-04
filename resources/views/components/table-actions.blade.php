@props([
    'id',
    'baseUrl' => null,
    'status' => null,
    'show' => true,
    'edit' => true,
    'toggle' => true,
    'onEdit' => null,
    'onShow' => null,

    'pendingUrl' => null,
    'onPending' => null,
    'pendingTitle' => 'Pendiente',
    'pdfUrl' => null,
    'onPdf' => null,
    'pdfTitle' => 'Imprimir Récipe',
    'pdfTarget' => '_blank',
    'pdfColor' => 'rose',
])

@php
    $base = $baseUrl ? rtrim($baseUrl, '/') : null;

    $moduloActual = strtolower(session('modulo_activo') ?? (request()->segment(2) ?? ''));
    $esSaludOPsicologia =
        in_array($moduloActual, ['salud', 'psicologia', 'psicología']) || request()->is('*salud*', '*psicologia*');

    $triggerHoverClass = $esSaludOPsicologia
        ? 'hover:bg-sky-50 dark:hover:bg-sky-950/50 hover:text-sky-600'
        : 'hover:bg-rose-50 dark:hover:bg-rose-950/50 hover:text-rose-600';

    $showHoverClass = $esSaludOPsicologia
        ? 'hover:text-sky-600 hover:bg-sky-100 dark:hover:bg-sky-950/50'
        : 'hover:text-rose-600 hover:bg-rose-100 dark:hover:bg-rose-950/50';

    $permite = fn (string $url, string $metodo = 'GET') => \App\Support\Permisos\PermisoUrl::puede($url, $metodo);
    $permiteEdicion = fn () => $base && ($permite($base . '/' . $id, 'PUT') || $permite($base . '/' . $id, 'PATCH'));

    $verVisible = $base && $show && ($onShow ? true : $permite($base . '/' . $id));
    $editarVisible = $base && $edit && ($onEdit ? $permiteEdicion() : $permite($base . '/' . $id . '/edit'));
    $toggleVisible = $base && $toggle && !is_null($status)
        && ($status ? $permite($base . '/' . $id, 'DELETE') : $permite($base . '/' . $id . '/activar', 'PUT'));
    $pendienteVisible = $onPending || ($pendingUrl && $permite($pendingUrl));
    $pdfVisible = $onPdf || ($pdfUrl && $permite($pdfUrl));
    $hayExtras = trim((string) $slot) !== '';
    $hayAcciones = $verVisible || $editarVisible || $toggleVisible || $pendienteVisible || $pdfVisible || $hayExtras;
@endphp

@if (! $hayAcciones)
<td {{ $attributes->merge(['class' => 'px-6 py-4 text-center text-gray-300 dark:text-gray-600']) }} title="Solo lectura">
    <i class="fas fa-lock text-xs"></i>
</td>
@else
<td {{ $attributes->merge(['class' => 'px-6 py-4 whitespace-nowrap']) }}>
    <div class="acciones-wrap relative flex items-center justify-center h-8">

        <div id="trigger-{{ $id }}"
            class="acciones-trigger w-8 h-8 flex items-center justify-center rounded-xl border dark:border-gray-600/50 text-gray-500 dark:text-gray-400 shadow-sm {{ $triggerHoverClass }} transition-all">
            <i class="fas fa-ellipsis-vertical text-xs"></i>
        </div>

        <div id="panel-{{ $id }}" class="acciones-panel">

            @if ($pendienteVisible)
                @if ($onPending)
                    <button type="button" onclick='{!! $onPending !!}'
                        class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-emerald-500 hover:bg-emerald-100 dark:hover:bg-emerald-950/50 transition-colors"
                        title="{{ $pendingTitle }}">
                        <i class="fas fa-arrow-right text-xs"></i>
                    </button>
                @else
                    <a href="{{ $pendingUrl }}" onclick="event.stopPropagation()"
                        class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-emerald-500 hover:bg-emerald-100 dark:hover:bg-emerald-950/50 transition-colors"
                        title="{{ $pendingTitle }}">
                        <i class="fas fa-arrow-right text-xs"></i>
                    </a>
                @endif
            @endif

            @if ($base)
                @if ($verVisible)
                    @if ($onShow)
                        <button type="button" onclick='{!! $onShow !!}'
                            class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-sky-500 hover:bg-sky-100 dark:hover:bg-sky-950/50 transition-colors"
                            title="Ver Más">
                            <i class="fas fa-eye text-xs"></i>
                        </button>
                    @else
                        <a href="{{ url($base . '/' . $id) }}" onclick="event.stopPropagation()"
                            class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-sky-500 hover:bg-sky-100 dark:hover:bg-sky-950/50 transition-colors"
                            title="Ver Más">
                            <i class="fas fa-eye text-xs"></i>
                        </a>
                    @endif
                @endif

                @if ($editarVisible)
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

                @if ($toggleVisible)
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

            {{-- Reporte PDF principal --}}
            <x-table-actions-pdf :url="$pdfUrl" :on-click="$onPdf" :title="$pdfTitle" :color="$pdfColor"
                :target="$pdfTarget" />

            {{-- Botones extra (por ejemplo, más reportes con <x-table-action-pdf>) --}}
            {{ $slot }}
        </div>
    </div>
</td>