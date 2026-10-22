@php
    $estiloCampo = 'background-color:var(--input-bg);border-color:var(--input-border);color:var(--text-main);';
    $cerrada = $sesion?->cerrada_sad ?? false;
    $presentesActuales = $marcas->filter(fn ($estado) => $estado === 'presente')->count();
@endphp
<x-app-layout>

    @include('components.alert')

    <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color:var(--text-main);">Asistencia deportiva</h1>
                    <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                        <i class="fas fa-clipboard-check mr-1" style="color:var(--color-primary)"></i>
                        Abre la sesión del día, marca a los presentes y ciérrala
                    </p>
                </div>
                @canRoute('admin.deportes.asistencia.reporte')
                <a href="{{ route('admin.deportes.asistencia.reporte', $horario ? ['hde_id' => $horario->id_hde] : []) }}" class="inline-flex items-center justify-center gap-2 rounded-xl border px-4 py-2.5 text-sm font-bold" style="border-color:var(--border-color);color:var(--text-main);">
                    <i class="fas fa-chart-bar text-xs"></i> Reporte de asistencia
                </a>
                @endcanRoute
            </div>

            @include('admin.deportes.partials.errores')

            <form method="GET" action="{{ route('admin.deportes.asistencia.index') }}" class="mb-6 grid grid-cols-1 gap-3 rounded-2xl border p-4 shadow-sm md:grid-cols-[1fr_200px_auto]" style="background-color:var(--bg-card);border-color:var(--border-color);">
                <div>
                    <label for="hde_id" class="mb-1 block text-xs font-black uppercase tracking-wider" style="color:var(--text-muted);">Horario</label>
                    <select id="hde_id" name="hde_id" class="w-full rounded-xl border px-3 py-2.5 text-sm" style="{{ $estiloCampo }}">
                        <option value="">Selecciona un horario</option>
                        @foreach ($horarios as $opcion)
                            <option value="{{ $opcion->id_hde }}" @selected($horario && (int) $horario->id_hde === (int) $opcion->id_hde)>{{ $opcion->etiqueta() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="fecha" class="mb-1 block text-xs font-black uppercase tracking-wider" style="color:var(--text-muted);">Fecha</label>
                    <input id="fecha" type="date" name="fecha" value="{{ $fecha->format('Y-m-d') }}" max="{{ now()->format('Y-m-d') }}" class="w-full rounded-xl border px-3 py-2.5 text-sm" style="{{ $estiloCampo }}">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-red-700 px-4 py-2.5 text-sm font-bold text-white hover:bg-red-800">
                        <i class="fas fa-search text-xs"></i> Consultar
                    </button>
                </div>
            </form>

            @if (! $horario)
                <div class="rounded-2xl border border-dashed p-10 text-center" style="border-color:var(--border-color);color:var(--text-muted);background-color:var(--bg-card);">
                    <i class="fas fa-hand-pointer fa-2x mb-3" style="opacity:.3"></i>
                    <p class="text-sm font-semibold">Selecciona un horario y una fecha.</p>
                </div>
            @else
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <div class="space-y-6">
                        <div class="rounded-2xl border p-5 shadow-sm space-y-3" style="background-color:var(--bg-card);border-color:var(--border-color);color:var(--text-main);">
                            <p class="text-lg font-extrabold">{{ $horario->actividad?->nombre_acd }}</p>
                            <p class="text-sm" style="color:var(--text-muted);">{{ $horario->espacio?->nombre_epd }} · {{ $horario->descripcionCuando() }}</p>
                            <div>
                                <p class="text-xs font-bold uppercase" style="color:var(--text-muted);">Sesión del {{ $fecha->format('d/m/Y') }}</p>
                                @if (! $sesion)
                                    <p class="font-semibold">Sin abrir</p>
                                @elseif ($cerrada)
                                    <p class="font-semibold text-gray-600">Cerrada el {{ $sesion->cerrada_en_sad?->format('d/m/Y h:i A') }}</p>
                                @else
                                    <p class="font-semibold text-emerald-700">Abierta · {{ $presentesActuales }} presentes marcados</p>
                                @endif
                            </div>
                            @if ($fechasSugeridas)
                                <div>
                                    <p class="mb-1 text-xs font-bold uppercase" style="color:var(--text-muted);">Fechas recientes del horario</p>
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach ($fechasSugeridas as $sugerida)
                                            <a href="{{ route('admin.deportes.asistencia.index', ['hde_id' => $horario->id_hde, 'fecha' => $sugerida->format('Y-m-d')]) }}"
                                                class="rounded-lg border px-2 py-1 text-xs font-bold {{ $sugerida->isSameDay($fecha) ? 'bg-red-800 text-white border-red-800' : '' }}"
                                                style="{{ $sugerida->isSameDay($fecha) ? '' : 'border-color:var(--border-color);color:var(--text-main);' }}">
                                                {{ $sugerida->format('d/m') }}
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        @if (! $sesion)
                            @if ($horario->aplicaEnFecha($fecha) && ! $fecha->isFuture())
                                @canRoute('admin.deportes.asistencia.store')
                                <form method="POST" action="{{ route('admin.deportes.asistencia.store') }}" class="rounded-2xl border p-5 shadow-sm" style="background-color:var(--bg-card);border-color:var(--border-color);">
                                    @csrf
                                    <input type="hidden" name="hde_id" value="{{ $horario->id_hde }}">
                                    <input type="hidden" name="fecha" value="{{ $fecha->format('Y-m-d') }}">
                                    <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-red-800 px-4 py-2.5 text-sm font-extrabold text-white shadow-lg hover:bg-red-900">
                                        <i class="fas fa-door-open text-xs"></i> Abrir sesión del {{ $fecha->format('d/m/Y') }}
                                    </button>
                                </form>
                                @endcanRoute
                            @else
                                <p class="rounded-2xl border p-4 text-sm font-semibold text-amber-700" style="border-color:var(--border-color);background-color:var(--bg-card);">
                                    El horario no tiene sesión el {{ $fecha->format('d/m/Y') }}. Elige una de las fechas recientes.
                                </p>
                            @endif
                        @endif

                        @if ($sesionesRecientes->isNotEmpty())
                            <div class="rounded-2xl border p-5 shadow-sm" style="background-color:var(--bg-card);border-color:var(--border-color);color:var(--text-main);">
                                <p class="mb-2 text-xs font-black uppercase tracking-wider" style="color:var(--text-muted);">Sesiones recientes</p>
                                <ul class="space-y-1 text-sm">
                                    @foreach ($sesionesRecientes as $reciente)
                                        <li class="flex items-center justify-between">
                                            <a href="{{ route('admin.deportes.asistencia.index', ['hde_id' => $horario->id_hde, 'fecha' => $reciente->fecha_sad->format('Y-m-d')]) }}" class="font-semibold underline">{{ $reciente->fecha_sad->format('d/m/Y') }}</a>
                                            <span class="text-xs" style="color:var(--text-muted);">
                                                {{ $reciente->presentes_count }} P · {{ $reciente->inasistentes_count }} I · {{ $reciente->cerrada_sad ? 'cerrada' : 'abierta' }}
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>

                    <div class="lg:col-span-2 rounded-2xl border shadow-sm overflow-hidden" style="background-color:var(--bg-card);border-color:var(--border-color);">
                        @if ($sesion && ! $cerrada)
                            <form id="formAsistencia" method="POST" action="{{ route('admin.deportes.asistencia.update', $sesion->id_sad) }}">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="marcas_enviadas" value="1">
                        @endif

                        <div class="flex flex-col gap-2 border-b p-4 sm:flex-row sm:items-center sm:justify-between" style="border-color:var(--border-color);">
                            <h3 class="text-lg font-extrabold" style="color:var(--text-main);">Inscritos ({{ $inscritos->count() }})</h3>
                            @if ($sesion && ! $cerrada && $inscritos->isNotEmpty())
                                <div class="flex gap-2 text-xs font-bold">
                                    <button type="button" onclick="document.querySelectorAll('.marca-presente').forEach(c => c.checked = true)" class="rounded-lg border px-2.5 py-1" style="border-color:var(--border-color);color:var(--text-main);">Marcar todos</button>
                                    <button type="button" onclick="document.querySelectorAll('.marca-presente').forEach(c => c.checked = false)" class="rounded-lg border px-2.5 py-1" style="border-color:var(--border-color);color:var(--text-main);">Desmarcar</button>
                                </div>
                            @endif
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="border-b text-[12px] font-black uppercase tracking-wider" style="border-color:var(--border-color);color:var(--text-main);">
                                        <th class="px-4 py-3 text-center w-24">{{ $sesion ? 'Presente' : '' }}</th>
                                        <th class="px-4 py-3">Cédula</th>
                                        <th class="px-4 py-3">Nombre</th>
                                        @if ($cerrada)
                                            <th class="px-4 py-3 text-center">Resultado</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($inscritos as $inscripcion)
                                        @php $marca = $marcas[$inscripcion->id_ide] ?? null; @endphp
                                        <tr class="border-b text-sm" style="border-color:var(--border-color);color:var(--text-main);">
                                            <td class="px-4 py-3 text-center">
                                                @if ($sesion && ! $cerrada)
                                                    <input type="checkbox" name="presentes[]" value="{{ $inscripcion->id_ide }}" class="marca-presente h-5 w-5 rounded accent-red-800" @checked($marca === 'presente')>
                                                @elseif ($cerrada)
                                                    <i class="fas {{ $marca === 'presente' ? 'fa-check-circle text-emerald-600' : 'fa-times-circle text-red-600' }}"></i>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 font-mono text-xs">{{ $inscripcion->persona?->cedula_per }}</td>
                                            <td class="px-4 py-3 font-semibold">{{ trim(($inscripcion->persona?->nombres_per ?? '') . ' ' . ($inscripcion->persona?->apellidos_per ?? '')) }}</td>
                                            @if ($cerrada)
                                                <td class="px-4 py-3 text-center">
                                                    <span class="rounded-lg px-2 py-1 text-[11px] font-bold uppercase {{ $marca === 'presente' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                                        {{ $marca === 'presente' ? 'Presente' : 'Inasistente' }}
                                                    </span>
                                                </td>
                                            @endif
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-6 py-10 text-center text-xs font-bold uppercase tracking-wider text-gray-400">No hay inscritos para esta fecha.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if ($sesion && ! $cerrada)
                                <div class="flex flex-col gap-2 border-t p-4 sm:flex-row sm:justify-end" style="border-color:var(--border-color);">
                                    @canRoute('admin.deportes.asistencia.update')
                                    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl border px-4 py-2.5 text-sm font-bold" style="border-color:var(--border-color);color:var(--text-main);">
                                        <i class="fas fa-save text-xs"></i> Guardar marcas
                                    </button>
                                    @endcanRoute
                                    @canRoute('admin.deportes.asistencia.cerrar')
                                    <button type="button" id="btnCerrarSesion" class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-800 px-5 py-2.5 text-sm font-extrabold text-white shadow-lg hover:bg-red-900">
                                        <i class="fas fa-lock text-xs"></i> Guardar y cerrar sesión
                                    </button>
                                    @endcanRoute
                                </div>
                            </form>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if ($sesion && ! $cerrada)
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const boton = document.getElementById('btnCerrarSesion');
                const formulario = document.getElementById('formAsistencia');
                const urlCerrar = @json(route('admin.deportes.asistencia.cerrar', $sesion->id_sad));

                if (!boton || !formulario) {
                    return;
                }

                function enviarCierre() {
                    formulario.action = urlCerrar;
                    const metodo = formulario.querySelector('input[name="_method"]');
                    if (metodo) {
                        metodo.remove();
                    }
                    formulario.submit();
                }

                boton.addEventListener('click', function () {
                    const sinMarcar = formulario.querySelectorAll('.marca-presente:not(:checked)').length;
                    const titulo = '¿Cerrar la sesión?';
                    const texto = sinMarcar + ' inscrito(s) sin marcar quedarán como inasistentes. Una sesión cerrada no se puede editar.';

                    if (typeof Swal === 'undefined') {
                        if (confirm(titulo + '\n\n' + texto)) {
                            enviarCierre();
                        }
                        return;
                    }

                    Swal.fire({
                        title: titulo,
                        text: texto,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#991b1b',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: 'Sí, cerrar',
                        cancelButtonText: 'Cancelar'
                    }).then(function (resultado) {
                        if (resultado.isConfirmed) {
                            enviarCierre();
                        }
                    });
                });
            });
        </script>
    @endif
</x-app-layout>
