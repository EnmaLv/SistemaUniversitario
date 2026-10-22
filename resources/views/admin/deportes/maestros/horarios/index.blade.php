@php
    $estiloCampo = 'background-color:var(--input-bg);border-color:var(--input-border);color:var(--text-main);';
    $anterior = $lunes->copy()->subWeek()->format('Y-m-d');
    $siguiente = $lunes->copy()->addWeek()->format('Y-m-d');
    $filtros = array_filter(['epd_id' => $espacioId]);
@endphp
<x-app-layout>

    @include('components.alert')

    <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color:var(--text-main);">Horarios de los espacios</h1>
                    <p class="mt-1 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                        <i class="fas fa-calendar-week mr-1" style="color:var(--color-primary)"></i>
                        Muestra qué espacio está ocupado cada día de la semana. Para ocupar un espacio, crea una actividad o un torneo.
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    @canRoute('admin.deportes.calendarios.create')
                    <a href="{{ route('admin.deportes.calendarios.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-800 px-5 py-2.5 text-sm font-extrabold text-white shadow-lg hover:bg-red-900">
                        <i class="fas fa-plus text-xs"></i> Crear actividad
                    </a>
                    @endcanRoute
                    @canRoute('admin.deportes.maestros.horarios.exportarPdf')
                    <a href="{{ route('admin.deportes.maestros.horarios.exportarPdf', array_merge(['semana' => $lunes->format('Y-m-d')], $filtros)) }}" target="_blank" class="inline-flex items-center justify-center gap-2 rounded-xl border px-4 py-2.5 text-sm font-bold" style="border-color:var(--border-color);color:var(--text-main);">
                        <i class="fas fa-file-pdf text-xs"></i> Imprimir semana
                    </a>
                    @endcanRoute
                </div>
            </div>

            <div class="mb-4 flex flex-col gap-3 rounded-2xl border p-3 shadow-sm lg:flex-row lg:items-center lg:justify-between" style="background-color:var(--bg-card);border-color:var(--border-color);">
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.deportes.maestros.horarios.index', array_merge(['semana' => $anterior], $filtros)) }}" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border" style="border-color:var(--border-color);color:var(--text-main);" title="Semana anterior"><i class="fas fa-chevron-left text-xs"></i></a>
                    <div class="text-sm font-extrabold" style="color:var(--text-main);">Semana del {{ $lunes->format('d/m/Y') }} al {{ $domingo->format('d/m/Y') }}</div>
                    <a href="{{ route('admin.deportes.maestros.horarios.index', array_merge(['semana' => $siguiente], $filtros)) }}" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border" style="border-color:var(--border-color);color:var(--text-main);" title="Semana siguiente"><i class="fas fa-chevron-right text-xs"></i></a>
                    <a href="{{ route('admin.deportes.maestros.horarios.index', $filtros) }}" class="ml-1 text-xs font-bold" style="color:var(--color-primary);">Ir a esta semana</a>
                </div>
                <form method="GET" action="{{ route('admin.deportes.maestros.horarios.index') }}" class="flex flex-col gap-2 sm:flex-row">
                    <input type="hidden" name="semana" value="{{ $lunes->format('Y-m-d') }}">
                    <select name="epd_id" onchange="this.form.submit()" class="rounded-xl border px-3 py-2 text-sm" style="{{ $estiloCampo }}">
                        <option value="">Todos los espacios</option>
                        @foreach ($espacios as $espacio)
                            <option value="{{ $espacio->id_epd }}" @selected((int) $espacioId === (int) $espacio->id_epd)>{{ $espacio->nombre_epd }}</option>
                        @endforeach
                    </select>
                    <noscript><button type="submit" class="rounded-xl bg-red-700 px-4 py-2 text-sm font-bold text-white">Ver</button></noscript>
                </form>
            </div>

            @if ($filas->isEmpty())
                <div class="rounded-2xl border p-10 text-center shadow-sm" style="background-color:var(--bg-card);border-color:var(--border-color);">
                    <i class="fas fa-map-marked-alt fa-3x mb-3" style="opacity:0.1;"></i>
                    <p class="text-sm font-bold" style="color:var(--text-main);">No hay espacios deportivos registrados.</p>
                    <p class="text-sm" style="color:var(--text-muted);">Registra primero las canchas o espacios en "Espacios deportivos".</p>
                </div>
            @else
                <div class="hidden overflow-x-auto rounded-2xl border shadow-sm lg:block" style="background-color:var(--bg-card);border-color:var(--border-color);">
                    <table class="w-full table-fixed border-collapse text-left">
                        <thead>
                            <tr class="border-b text-[12px] font-black uppercase tracking-wider" style="border-color:var(--border-color);color:var(--text-main);">
                                <th class="w-44 px-3 py-3">Espacio</th>
                                @foreach ($dias as $dia)
                                    <th class="px-2 py-3 text-center {{ $dia['hoy'] ? 'bg-red-50 dark:bg-red-950/30' : '' }}">
                                        {{ $dia['nombre'] }}<br><span class="text-[11px] font-semibold normal-case" style="color:var(--text-muted);">{{ $dia['fecha']->format('d/m') }}</span>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($filas as $espacio)
                                <tr class="border-b align-top" style="border-color:var(--border-color);">
                                    <td class="px-3 py-3">
                                        <p class="text-sm font-bold" style="color:var(--text-main);">{{ $espacio->nombre_epd }}</p>
                                        @if ($espacio->ubicacion_epd)
                                            <p class="text-[11px]" style="color:var(--text-muted);">{{ $espacio->ubicacion_epd }}</p>
                                        @endif
                                    </td>
                                    @foreach ($dias as $dia)
                                        <td class="px-1.5 py-2 {{ $dia['hoy'] ? 'bg-red-50/60 dark:bg-red-950/20' : '' }}">
                                            @forelse ($celdas[$espacio->id_epd . '|' . $dia['clave']] ?? [] as $evento)
                                                <a href="{{ $evento->torneo ? route('admin.deportes.torneos.show', $evento->torneo->id_tde) : route('admin.deportes.calendarios.show', $evento->actividad->id_cld) }}"
                                                    class="mb-1.5 block rounded-lg border-l-4 px-2 py-1.5 text-[11px] leading-tight hover:opacity-80 {{ $evento->torneo ? 'border-amber-500 bg-amber-50 dark:bg-amber-950/30' : ($evento->actividad->esRecurrente() ? 'border-sky-500 bg-sky-50 dark:bg-sky-950/30' : 'border-red-700 bg-red-50 dark:bg-red-950/30') }}">
                                                    <span class="block font-extrabold" style="color:var(--text-main);">{{ $evento->bloque->horaInicio('g:i A') }} - {{ $evento->bloque->horaFin('g:i A') }}</span>
                                                    <span class="block font-semibold" style="color:var(--text-main);">{{ $evento->disciplina }}</span>
                                                    <span class="block" style="color:var(--text-muted);">{{ \Illuminate\Support\Str::limit($evento->nombre, 40) }}</span>
                                                </a>
                                            @empty
                                                <span class="block py-2 text-center text-[11px]" style="color:var(--text-muted);">Libre</span>
                                            @endforelse
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="space-y-4 lg:hidden">
                    @foreach ($dias as $dia)
                        @php $delDia = $eventos->filter(fn ($evento) => $evento->fecha->toDateString() === $dia['clave'])->sortBy('inicio'); @endphp
                        <div class="rounded-2xl border shadow-sm" style="background-color:var(--bg-card);border-color:var(--border-color);">
                            <div class="flex items-center justify-between border-b px-4 py-2" style="border-color:var(--border-color);">
                                <p class="text-sm font-extrabold" style="color:var(--text-main);">{{ $dia['nombre'] }} {{ $dia['fecha']->format('d/m') }}</p>
                                @if ($dia['hoy'])
                                    <span class="rounded-md bg-red-100 px-1.5 py-0.5 text-[10px] font-bold uppercase text-red-700">Hoy</span>
                                @endif
                            </div>
                            @forelse ($delDia as $evento)
                                <a href="{{ $evento->torneo ? route('admin.deportes.torneos.show', $evento->torneo->id_tde) : route('admin.deportes.calendarios.show', $evento->actividad->id_cld) }}" class="block border-b px-4 py-2 last:border-b-0" style="border-color:var(--border-color);">
                                    <p class="text-sm font-bold" style="color:var(--text-main);">{{ $evento->horas }} · {{ $evento->espacio }}</p>
                                    <p class="text-xs" style="color:var(--text-muted);">{{ $evento->disciplina }} · {{ $evento->nombre }}</p>
                                </a>
                            @empty
                                <p class="px-4 py-3 text-xs" style="color:var(--text-muted);">Todos los espacios libres.</p>
                            @endforelse
                        </div>
                    @endforeach
                </div>

                <div class="mt-3 flex flex-wrap gap-4 text-xs" style="color:var(--text-muted);">
                    <span><span class="mr-1 inline-block h-3 w-3 rounded-sm bg-red-700 align-middle"></span> Una sola vez</span>
                    <span><span class="mr-1 inline-block h-3 w-3 rounded-sm bg-sky-500 align-middle"></span> Se repite cada semana</span>
                    <span><span class="mr-1 inline-block h-3 w-3 rounded-sm bg-amber-500 align-middle"></span> Torneo</span>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
