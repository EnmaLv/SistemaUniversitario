@php
    $actividad = $bloque->calendario;
    $torneo = $bloque->torneos->sortByDesc('id_tde')->first();
    $fecha = $sesion->fecha_sad;
    $nombrePersona = fn ($fila) => $fila->persona ? trim($fila->persona->nombres_per . ' ' . $fila->persona->apellidos_per) : 'Persona eliminada';
    $secciones = [
        ['titulo' => $actividad?->esTorneo() ? 'Integrantes presentes' : ($reporte['usa_inscritos'] ? 'Inscritos presentes' : 'Presentes'), 'filas' => $reporte['presentes'], 'color' => 'text-emerald-700', 'icono' => 'fa-check-circle', 'vacio' => 'Nadie en esta lista.'],
    ];

    if ($reporte['usa_inscritos']) {
        $secciones[] = ['titulo' => $reporte['cerrada'] ? 'Inasistentes' : 'Pendientes (aún sin marcar)', 'filas' => $reporte['inasistentes'], 'color' => 'text-red-700', 'icono' => 'fa-times-circle', 'vacio' => 'No faltó nadie.'];
        $secciones[] = ['titulo' => $actividad?->esTorneo() ? 'Asistieron sin estar en un equipo' : 'Asistieron sin estar inscritos', 'filas' => $reporte['no_inscritos'], 'color' => 'text-amber-700', 'icono' => 'fa-user-plus', 'vacio' => 'Nadie en esta lista.'];
    }
@endphp
<x-app-layout>

    @include('components.alert')

    <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <a href="{{ route('admin.deportes.asistencia.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold" style="color:var(--text-muted);">
                <i class="fas fa-arrow-left text-xs"></i> Volver a la lista de eventos
            </a>

            <div class="mt-2 mb-6 flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color:var(--text-main);">Reporte de asistencia</h1>
                    <p class="mt-1 text-sm font-semibold" style="color:var(--text-main);">
                        {{ $torneo?->nombre_tde ?? $actividad?->nombre_cld }} · {{ $bloque->actividad?->nombre_acd }} · {{ $bloque->espacio?->nombre_epd }} ·
                        {{ \App\Models\Deporte\DeporteCalendario::DIAS_LEGIBLES[\App\Models\Deporte\DeporteCalendario::diaDeFecha($fecha)] }} {{ $fecha->format('d/m/Y') }} · {{ $bloque->horas() }}
                    </p>
                    <p class="mt-1 text-sm" style="color:var(--text-muted);">
                        {{ $reporte['cerrada'] ? 'Asistencia cerrada: este es el resultado final del evento.' : 'La asistencia sigue abierta: los pendientes todavía pueden marcarse.' }}
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    @canRoute('admin.deportes.asistencia.reporte_pdf')
                    <a href="{{ route('admin.deportes.asistencia.reporte_pdf', $sesion->id_sad) }}" target="_blank" class="inline-flex items-center gap-2 rounded-xl bg-red-800 px-4 py-2.5 text-sm font-extrabold text-white shadow-lg hover:bg-red-900">
                        <i class="fas fa-file-pdf text-xs"></i> Descargar PDF
                    </a>
                    @endcanRoute
                    @canRoute('admin.deportes.asistencia.show')
                    <a href="{{ route('admin.deportes.asistencia.show', $sesion->id_sad) }}" class="inline-flex items-center gap-2 rounded-xl border px-4 py-2.5 text-sm font-bold" style="border-color:var(--border-color);color:var(--text-main);">
                        <i class="fas fa-clipboard-check text-xs"></i> {{ $reporte['cerrada'] ? 'Ver asistencia' : 'Seguir marcando' }}
                    </a>
                    @endcanRoute
                    @if ($actividad?->esRecurrente())
                        @canRoute('admin.deportes.asistencia.historial')
                        <a href="{{ route('admin.deportes.asistencia.historial', $actividad->id_cld) }}" class="inline-flex items-center gap-2 rounded-xl border px-4 py-2.5 text-sm font-bold" style="border-color:var(--border-color);color:var(--text-main);">
                            <i class="fas fa-chart-bar text-xs"></i> Asistencia por persona
                        </a>
                        @endcanRoute
                    @endif
                </div>
            </div>

            <div class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-3">
                @foreach ($secciones as $seccion)
                    <div class="rounded-2xl border p-4 text-center shadow-sm" style="background-color:var(--bg-card);border-color:var(--border-color);">
                        <p class="text-[10px] font-black uppercase tracking-wider" style="color:var(--text-muted);">{{ $seccion['titulo'] }}</p>
                        <p class="text-3xl font-black {{ $seccion['color'] }}">{{ $seccion['filas']->count() }}</p>
                    </div>
                @endforeach
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                @foreach ($secciones as $seccion)
                    <div class="rounded-2xl border shadow-sm overflow-hidden" style="background-color:var(--bg-card);border-color:var(--border-color);">
                        <div class="border-b px-4 py-3 text-sm font-extrabold {{ $seccion['color'] }}" style="border-color:var(--border-color);">
                            <i class="fas {{ $seccion['icono'] }} mr-1"></i> {{ $seccion['titulo'] }} ({{ $seccion['filas']->count() }})
                        </div>
                        <ul class="divide-y" style="border-color:var(--border-color);">
                            @forelse ($seccion['filas'] as $fila)
                                <li class="px-4 py-2">
                                    <p class="text-sm font-semibold" style="color:var(--text-main);">{{ $nombrePersona($fila) }}</p>
                                    <p class="font-mono text-xs" style="color:var(--text-muted);">{{ $fila->persona?->cedula_per }}</p>
                                </li>
                            @empty
                                <li class="px-4 py-6 text-center text-sm" style="color:var(--text-muted);">{{ $seccion['vacio'] }}</li>
                            @endforelse
                        </ul>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
