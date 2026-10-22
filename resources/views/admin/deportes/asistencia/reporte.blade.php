@php
    $estiloCampo = 'background-color:var(--input-bg);border-color:var(--input-border);color:var(--text-main);';
@endphp
<x-app-layout>

    @include('components.alert')

    <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color:var(--text-main);">Reporte de asistencia</h1>
                    <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                        <i class="fas fa-chart-bar mr-1" style="color:var(--color-primary)"></i>
                        Presentes e inasistencias por persona en sesiones cerradas
                    </p>
                </div>
                <div class="flex gap-2">
                    @canRoute('admin.deportes.asistencia.index')
                    <a href="{{ route('admin.deportes.asistencia.index', $horario ? ['hde_id' => $horario->id_hde] : []) }}" class="inline-flex items-center gap-2 rounded-xl border px-4 py-2.5 text-sm font-bold" style="border-color:var(--border-color);color:var(--text-main);">
                        <i class="fas fa-arrow-left text-xs"></i> Tomar asistencia
                    </a>
                    @endcanRoute
                    @if ($horario)
                        @canRoute('admin.deportes.asistencia.reporte_pdf')
                        <a href="{{ route('admin.deportes.asistencia.reporte_pdf', ['hde_id' => $horario->id_hde, 'desde' => $desde->format('Y-m-d'), 'hasta' => $hasta->format('Y-m-d')]) }}" target="_blank"
                            class="inline-flex items-center gap-2 rounded-xl bg-red-800 px-4 py-2.5 text-sm font-extrabold text-white shadow-lg hover:bg-red-900">
                            <i class="fas fa-file-pdf text-xs"></i> Exportar PDF
                        </a>
                        @endcanRoute
                    @endif
                </div>
            </div>

            <form method="GET" action="{{ route('admin.deportes.asistencia.reporte') }}" class="mb-6 grid grid-cols-1 gap-3 rounded-2xl border p-4 shadow-sm md:grid-cols-[1fr_170px_170px_auto]" style="background-color:var(--bg-card);border-color:var(--border-color);">
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
                    <label for="desde" class="mb-1 block text-xs font-black uppercase tracking-wider" style="color:var(--text-muted);">Desde</label>
                    <input id="desde" type="date" name="desde" value="{{ $desde->format('Y-m-d') }}" class="w-full rounded-xl border px-3 py-2.5 text-sm" style="{{ $estiloCampo }}">
                </div>
                <div>
                    <label for="hasta" class="mb-1 block text-xs font-black uppercase tracking-wider" style="color:var(--text-muted);">Hasta</label>
                    <input id="hasta" type="date" name="hasta" value="{{ $hasta->format('Y-m-d') }}" class="w-full rounded-xl border px-3 py-2.5 text-sm" style="{{ $estiloCampo }}">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-red-700 px-4 py-2.5 text-sm font-bold text-white hover:bg-red-800">
                        <i class="fas fa-search text-xs"></i> Generar
                    </button>
                </div>
            </form>

            @if (! $horario)
                <div class="rounded-2xl border border-dashed p-10 text-center" style="border-color:var(--border-color);color:var(--text-muted);background-color:var(--bg-card);">
                    <p class="text-sm font-semibold">Selecciona un horario y un rango de fechas.</p>
                </div>
            @else
                <div class="mb-6 grid grid-cols-2 gap-4 md:grid-cols-4">
                    @foreach ([
                        ['Sesiones cerradas', $reporte['sesionesCerradas'], 'fa-lock'],
                        ['Sesiones abiertas', $reporte['sesionesAbiertas'], 'fa-door-open'],
                        ['Presentes', $reporte['totalPresentes'], 'fa-check-circle'],
                        ['Inasistencias', $reporte['totalInasistentes'], 'fa-times-circle'],
                    ] as [$etiqueta, $valor, $icono])
                        <div class="rounded-2xl border p-4 shadow-sm" style="background-color:var(--bg-card);border-color:var(--border-color);">
                            <p class="text-xs font-bold uppercase" style="color:var(--text-muted);"><i class="fas {{ $icono }} mr-1" style="color:var(--color-primary);"></i>{{ $etiqueta }}</p>
                            <p class="mt-1 text-2xl font-black" style="color:var(--text-main);">{{ $valor }}</p>
                        </div>
                    @endforeach
                </div>

                @if ($reporte['sesionesAbiertas'] > 0)
                    <p class="mb-4 rounded-xl bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-700">Hay {{ $reporte['sesionesAbiertas'] }} sesión(es) abierta(s) en el rango; no se cuentan hasta que se cierren.</p>
                @endif

                <div class="rounded-2xl border shadow-sm overflow-hidden" style="background-color:var(--bg-card);border-color:var(--border-color);">
                    <div class="border-b p-4" style="border-color:var(--border-color);">
                        <h3 class="text-lg font-extrabold" style="color:var(--text-main);">{{ $horario->etiqueta() }}</h3>
                        <p class="text-xs" style="color:var(--text-muted);">Del {{ $desde->format('d/m/Y') }} al {{ $hasta->format('d/m/Y') }}</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b text-[12px] font-black uppercase tracking-wider" style="border-color:var(--border-color);color:var(--text-main);">
                                    <th class="px-4 py-3">Cédula</th>
                                    <th class="px-4 py-3">Nombre</th>
                                    <th class="px-4 py-3 text-center">Presentes</th>
                                    <th class="px-4 py-3 text-center">Inasistencias</th>
                                    <th class="px-4 py-3 text-center">% asistencia</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($reporte['filas'] as $fila)
                                    <tr class="border-b text-sm" style="border-color:var(--border-color);color:var(--text-main);">
                                        <td class="px-4 py-3 font-mono text-xs">{{ $fila->cedula_per }}</td>
                                        <td class="px-4 py-3 font-semibold">{{ $fila->nombres_per }} {{ $fila->apellidos_per }}</td>
                                        <td class="px-4 py-3 text-center font-bold text-emerald-700">{{ $fila->presentes }}</td>
                                        <td class="px-4 py-3 text-center font-bold text-red-700">{{ $fila->inasistentes }}</td>
                                        <td class="px-4 py-3 text-center">{{ $fila->porcentaje }}%</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-10 text-center text-xs font-bold uppercase tracking-wider text-gray-400">No hay sesiones cerradas en el rango.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
