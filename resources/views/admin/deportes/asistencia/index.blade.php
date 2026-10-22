@php
    $estiloCampo = 'background-color:var(--input-bg);border-color:var(--input-border);color:var(--text-main);';
    $porDia = $eventos->groupBy(fn ($evento) => $evento->fecha->toDateString());
    $nombreDia = function ($fecha) use ($hoy) {
        $fecha = \Carbon\Carbon::parse($fecha);
        $texto = \App\Models\Deporte\DeporteCalendario::DIAS_LEGIBLES[\App\Models\Deporte\DeporteCalendario::diaDeFecha($fecha)] . ' ' . $fecha->format('d/m/Y');

        if ($fecha->equalTo($hoy)) {
            return 'Hoy, ' . $texto;
        }

        return $fecha->equalTo($hoy->copy()->subDay()) ? 'Ayer, ' . $texto : $texto;
    };
@endphp
<x-app-layout>

    @include('components.alert')

    <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-6">
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color:var(--text-main);">Tomar asistencia</h1>
                <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                    <i class="fas fa-clipboard-check mr-1" style="color:var(--color-primary)"></i>
                    Elige el evento de la lista y marca a los presentes por cédula. Puedes registrarla el mismo día o días después.
                </p>
            </div>

            @include('admin.deportes.partials.errores')

            <form method="GET" action="{{ route('admin.deportes.asistencia.index') }}" class="mb-6 flex flex-col gap-2 rounded-2xl border p-3 shadow-sm sm:flex-row sm:items-end" style="background-color:var(--bg-card);border-color:var(--border-color);">
                <div class="flex-1">
                    <label for="q" class="mb-1 block text-xs font-black uppercase tracking-wider" style="color:var(--text-muted);">Buscar</label>
                    <div class="flex items-center gap-2 rounded-xl border px-3 py-2.5" style="background-color:var(--input-bg);border-color:var(--input-border);">
                        <i class="fas fa-search text-gray-400"></i>
                        <input id="q" type="text" name="q" value="{{ $buscar }}" class="w-full border-0 bg-transparent text-sm outline-none" style="color:var(--text-main);" placeholder="Nombre de la actividad, deporte, cancha o sección">
                    </div>
                </div>
                <div>
                    <label for="fecha" class="mb-1 block text-xs font-black uppercase tracking-wider" style="color:var(--text-muted);">¿Fue en otra fecha?</label>
                    <input id="fecha" type="date" name="fecha" max="{{ $hoy->format('Y-m-d') }}" value="{{ $fecha?->format('Y-m-d') }}" class="w-full rounded-xl border px-3 py-2.5 text-sm sm:w-48" style="{{ $estiloCampo }}">
                </div>
                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-700 px-4 py-2.5 text-sm font-bold text-white hover:bg-red-800">
                    <i class="fas fa-search text-xs"></i> Buscar
                </button>
                @if ($buscar !== '' || $fecha)
                    <a href="{{ route('admin.deportes.asistencia.index') }}" class="inline-flex items-center justify-center rounded-xl border px-4 py-2.5 text-sm font-bold" style="border-color:var(--border-color);color:var(--text-main);">Limpiar</a>
                @endif
            </form>

            <p class="mb-3 text-xs font-bold uppercase tracking-wider" style="color:var(--text-muted);">
                {{ $fecha ? 'Eventos del ' . $fecha->format('d/m/Y') : 'Eventos de hoy y de los últimos ' . $dias . ' días' }}
            </p>

            @forelse ($porDia as $dia => $lista)
                <div class="mb-5 rounded-2xl border shadow-sm overflow-hidden" style="background-color:var(--bg-card);border-color:var(--border-color);">
                    <div class="border-b px-4 py-2.5 text-sm font-extrabold" style="border-color:var(--border-color);color:var(--text-main);">{{ $nombreDia($dia) }}</div>
                    <div class="divide-y" style="border-color:var(--border-color);">
                        @foreach ($lista as $evento)
                            @php $estadoEvento = \App\Support\Deportes\ServicioEventos::estadoDe($evento); @endphp
                            <div class="flex flex-col gap-3 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                                <div class="min-w-0">
                                    <p class="font-bold" style="color:var(--text-main);">
                                        {{ $evento->nombre }}
                                        @if ($evento->torneo)
                                            <span class="ml-1 rounded-md bg-amber-100 px-1.5 py-0.5 text-[10px] font-bold uppercase text-amber-700">Torneo</span>
                                        @endif
                                    </p>
                                    <p class="text-sm" style="color:var(--text-muted);">
                                        <i class="fas fa-futbol text-[11px]"></i> {{ $evento->disciplina }}
                                        · <i class="fas fa-map-marker-alt text-[11px]"></i> {{ $evento->espacio }}
                                        · <i class="far fa-clock text-[11px]"></i> {{ $evento->horas }}
                                        @if ($evento->seccion)
                                            · Sección {{ $evento->seccion }}
                                        @endif
                                    </p>
                                </div>
                                <div class="flex shrink-0 items-center gap-3">
                                    @if ($estadoEvento === 'cerrada')
                                        <span class="rounded-lg bg-emerald-100 px-2 py-1 text-[11px] font-extrabold uppercase text-emerald-700">Cerrada · {{ $evento->sesion->presentes_count }} presentes</span>
                                        @canRoute('admin.deportes.asistencia.reporte')
                                        <a href="{{ route('admin.deportes.asistencia.reporte', $evento->sesion->id_sad) }}" class="inline-flex items-center gap-2 rounded-xl border px-3 py-2 text-xs font-bold" style="border-color:var(--border-color);color:var(--text-main);">
                                            <i class="fas fa-list text-[10px]"></i> Ver reporte
                                        </a>
                                        @endcanRoute
                                    @elseif ($estadoEvento === 'abierta')
                                        <span class="rounded-lg bg-amber-100 px-2 py-1 text-[11px] font-extrabold uppercase text-amber-700">En curso · {{ $evento->sesion->presentes_count }} presentes</span>
                                        @canRoute('admin.deportes.asistencia.show')
                                        <a href="{{ route('admin.deportes.asistencia.show', $evento->sesion->id_sad) }}" class="inline-flex items-center gap-2 rounded-xl bg-red-800 px-3 py-2 text-xs font-extrabold text-white hover:bg-red-900">
                                            <i class="fas fa-pen text-[10px]"></i> Continuar
                                        </a>
                                        @endcanRoute
                                    @else
                                        <span class="rounded-lg bg-gray-100 px-2 py-1 text-[11px] font-extrabold uppercase text-gray-600 dark:bg-gray-800 dark:text-gray-300">Sin tomar</span>
                                        @canRoute('admin.deportes.asistencia.store')
                                        <form method="POST" action="{{ route('admin.deportes.asistencia.store') }}">
                                            @csrf
                                            <input type="hidden" name="hde_id" value="{{ $evento->bloque->id_hde }}">
                                            <input type="hidden" name="fecha" value="{{ $evento->fecha->format('Y-m-d') }}">
                                            <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-red-800 px-3 py-2 text-xs font-extrabold text-white hover:bg-red-900">
                                                <i class="fas fa-clipboard-check text-[10px]"></i> Tomar asistencia
                                            </button>
                                        </form>
                                        @endcanRoute
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-dashed p-10 text-center" style="border-color:var(--border-color);color:var(--text-muted);background-color:var(--bg-card);">
                    <i class="fas fa-calendar-times fa-2x mb-3" style="opacity:.3"></i>
                    <p class="text-sm font-semibold">No hay eventos {{ $fecha ? 'el ' . $fecha->format('d/m/Y') : 'en estos días' }}{{ $buscar !== '' ? ' que coincidan con "' . $buscar . '"' : '' }}.</p>
                    <p class="text-sm">Si el evento fue antes, elige su fecha en "¿Fue en otra fecha?". Si no existe, primero créalo en "Actividades y jornadas" o en "Torneos".</p>
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
