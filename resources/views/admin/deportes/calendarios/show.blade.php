@php
    $hoy = \Carbon\Carbon::today();
    $etiquetaFecha = fn ($fecha) => \App\Models\Deporte\DeporteCalendario::DIAS_LEGIBLES[\App\Models\Deporte\DeporteCalendario::diaDeFecha($fecha)] . ' ' . \Carbon\Carbon::parse($fecha)->format('d/m/Y');
@endphp
<x-app-layout>

    @include('components.alert')

    <div class="pt-6 pb-12 min-h-[calc(100vh-4rem)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <a href="{{ route('admin.deportes.calendarios.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold" style="color:var(--text-muted);">
                <i class="fas fa-arrow-left text-xs"></i> Volver a actividades
            </a>

            <div class="mt-2 mb-6 flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color:var(--text-main);">{{ $actividad->nombre_cld }}</h1>
                    <p class="mt-1 text-sm" style="color:var(--text-muted);">Aquí ves los bloques de la actividad, sus fechas y el estado de la asistencia de cada evento.</p>
                    <div class="mt-2 flex flex-wrap items-center gap-2">
                        <span class="rounded-lg px-2.5 py-1 text-[11px] font-extrabold uppercase {{ $actividad->esRecurrente() ? 'bg-sky-100 text-sky-700 dark:bg-sky-950/50 dark:text-sky-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300' }}">{{ $actividad->tipoLegible() }}</span>
                        @if (! $actividad->estaActiva())
                            <span class="rounded-lg bg-gray-200 px-2.5 py-1 text-[11px] font-extrabold uppercase text-gray-600 dark:bg-gray-800 dark:text-gray-400">Cancelada</span>
                        @endif
                        @if ($actividad->seccionLegible())
                            <span class="rounded-lg border px-2.5 py-1 text-[11px] font-bold uppercase" style="border-color:var(--border-color);color:var(--text-main);">Sección {{ $actividad->seccionLegible() }}</span>
                        @endif
                        <span class="rounded-lg border px-2.5 py-1 text-[11px] font-bold uppercase" style="border-color:var(--border-color);color:var(--text-main);">{{ $actividad->con_inscripcion_cld ? 'Con inscripción' : 'Abierta, sin inscripción' }}</span>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    @canRoute('admin.deportes.calendarios.cartel_pdf')
                    <a href="{{ route('admin.deportes.calendarios.cartel_pdf', $actividad->id_cld) }}" target="_blank" class="inline-flex items-center gap-2 rounded-xl bg-red-800 px-4 py-2.5 text-sm font-extrabold text-white shadow-lg hover:bg-red-900">
                        <i class="fas fa-file-pdf text-xs"></i> Cartel PDF
                    </a>
                    @endcanRoute
                    @if ($actividad->esRecurrente())
                        @canRoute('admin.deportes.asistencia.historial')
                        <a href="{{ route('admin.deportes.asistencia.historial', $actividad->id_cld) }}" class="inline-flex items-center gap-2 rounded-xl border px-4 py-2.5 text-sm font-bold" style="border-color:var(--border-color);color:var(--text-main);">
                            <i class="fas fa-chart-bar text-xs"></i> Asistencia por persona
                        </a>
                        @endcanRoute
                    @endif
                    @if ($actividad->estaActiva())
                        @canRoute('admin.deportes.calendarios.edit')
                        <a href="{{ route('admin.deportes.calendarios.edit', $actividad->id_cld) }}" class="inline-flex items-center gap-2 rounded-xl border px-4 py-2.5 text-sm font-bold" style="border-color:var(--border-color);color:var(--text-main);">
                            <i class="fas fa-edit text-xs"></i> Editar
                        </a>
                        @endcanRoute
                        @canRoute('admin.deportes.calendarios.destroy')
                        <form method="POST" action="{{ route('admin.deportes.calendarios.destroy', $actividad->id_cld) }}"
                            data-confirmar="¿Cancelar esta actividad?" data-confirmar-texto="Los espacios quedarán libres. El historial de asistencia se conserva." data-confirmar-boton="Sí, cancelar">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center gap-2 rounded-xl border px-4 py-2.5 text-sm font-bold text-red-700" style="border-color:var(--border-color);">
                                <i class="fas fa-ban text-xs"></i> Cancelar actividad
                            </button>
                        </form>
                        @endcanRoute
                    @else
                        @canRoute('admin.deportes.calendarios.activar')
                        <form method="POST" action="{{ route('admin.deportes.calendarios.activar', $actividad->id_cld) }}">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="inline-flex items-center gap-2 rounded-xl border px-4 py-2.5 text-sm font-bold" style="border-color:var(--border-color);color:var(--text-main);">
                                <i class="fas fa-undo text-xs"></i> Reactivar
                            </button>
                        </form>
                        @endcanRoute
                    @endif
                </div>
            </div>

            @include('admin.deportes.partials.errores')

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div class="space-y-6 lg:col-span-1">
                    <div class="rounded-2xl border p-5 shadow-sm space-y-3" style="background-color:var(--bg-card);border-color:var(--border-color);color:var(--text-main);">
                        <h3 class="text-sm font-black uppercase tracking-wider" style="color:var(--text-muted);">Datos</h3>
                        <div><p class="text-xs font-bold uppercase" style="color:var(--text-muted);">Cuándo</p><p class="font-semibold">{{ $actividad->descripcionCuando() }}</p></div>
                        <div><p class="text-xs font-bold uppercase" style="color:var(--text-muted);">Eventos en total</p><p class="font-semibold">{{ $totalFechas }}</p></div>
                        <div><p class="text-xs font-bold uppercase" style="color:var(--text-muted);">Sede</p><p class="font-semibold">{{ $actividad->sede?->nombre_sed ?? '-' }}</p></div>
                        <div><p class="text-xs font-bold uppercase" style="color:var(--text-muted);">Registrada por</p><p class="font-semibold">{{ trim(($actividad->coordinador?->persona?->nombres_per ?? '') . ' ' . ($actividad->coordinador?->persona?->apellidos_per ?? '')) ?: '-' }}</p></div>
                        @if ($actividad->descripcion_cld)
                            <div><p class="text-xs font-bold uppercase" style="color:var(--text-muted);">Descripción</p><p class="text-sm">{{ $actividad->descripcion_cld }}</p></div>
                        @endif
                    </div>
                </div>

                <div class="space-y-6 lg:col-span-2">
                    <div class="rounded-2xl border shadow-sm" style="background-color:var(--bg-card);border-color:var(--border-color);">
                        <div class="border-b p-4" style="border-color:var(--border-color);">
                            <h3 class="text-lg font-extrabold" style="color:var(--text-main);"><i class="fas fa-layer-group mr-1" style="color:var(--color-primary);"></i> Bloques</h3>
                            <p class="text-xs" style="color:var(--text-muted);">Cada bloque es un deporte en un espacio a una hora. Si hay inscripción, la gente se inscribe en un bloque.</p>
                        </div>
                        <div class="divide-y" style="border-color:var(--border-color);">
                            @forelse ($bloques as $bloque)
                                <div class="flex flex-col gap-2 p-4 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <p class="font-bold" style="color:var(--text-main);">{{ $bloque->actividad?->nombre_acd ?? 'Disciplina' }} · {{ $bloque->espacio?->nombre_epd ?? 'Espacio' }}</p>
                                        <p class="text-xs" style="color:var(--text-muted);">
                                            {{ $bloque->horas() }}
                                            @if ($actividad->con_inscripcion_cld)
                                                · {{ $bloque->inscritos_count }} inscritos{{ $bloque->cupo_maximo_hde !== null ? ' de ' . $bloque->cupo_maximo_hde . ' cupos' : ' (sin límite)' }}
                                            @endif
                                            @if ($bloque->estado_hde === \App\Models\Deporte\DeporteHorario::ESTADO_CANCELADO)
                                                · <span class="font-bold text-red-700">Cancelado</span>
                                            @endif
                                        </p>
                                    </div>
                                    @if ($actividad->con_inscripcion_cld && $bloque->estaActivo())
                                        @canRoute('admin.deportes.inscripciones.index')
                                        <a href="{{ route('admin.deportes.inscripciones.index', ['hde_id' => $bloque->id_hde]) }}" class="inline-flex items-center gap-2 rounded-xl border px-3 py-1.5 text-xs font-bold" style="border-color:var(--border-color);color:var(--text-main);">
                                            <i class="fas fa-user-plus text-[10px]"></i> Ver e inscribir
                                        </a>
                                        @endcanRoute
                                    @endif
                                </div>
                            @empty
                                <p class="p-6 text-center text-sm" style="color:var(--text-muted);">Esta actividad no tiene bloques. Edítala para agregar al menos uno.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="rounded-2xl border shadow-sm" style="background-color:var(--bg-card);border-color:var(--border-color);">
                        <div class="border-b p-4" style="border-color:var(--border-color);">
                            <h3 class="text-lg font-extrabold" style="color:var(--text-main);"><i class="fas fa-clipboard-check mr-1" style="color:var(--color-primary);"></i> Eventos y asistencia</h3>
                            <p class="text-xs" style="color:var(--text-muted);">La asistencia se toma por evento. Puedes registrarla el mismo día o después.</p>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm" style="color:var(--text-main);">
                                <tbody>
                                    @forelse ($pasadas as $fecha)
                                        @foreach ($bloques->where('estado_hde', '!=', \App\Models\Deporte\DeporteHorario::ESTADO_CANCELADO) as $bloque)
                                            @php $sesion = $sesiones[$bloque->id_hde . '|' . $fecha] ?? null; @endphp
                                            <tr class="border-b" style="border-color:var(--border-color);">
                                                <td class="px-4 py-2 whitespace-nowrap font-semibold">{{ $etiquetaFecha($fecha) }}</td>
                                                <td class="px-4 py-2">{{ $bloque->actividad?->nombre_acd }} · {{ $bloque->horas() }}</td>
                                                <td class="px-4 py-2 whitespace-nowrap">
                                                    @if (! $sesion)
                                                        <span class="text-xs font-bold" style="color:var(--text-muted);">Sin tomar</span>
                                                    @elseif ($sesion->cerrada_sad)
                                                        <span class="text-xs font-bold text-emerald-700">Cerrada · {{ $sesion->presentes_count }} presentes</span>
                                                    @else
                                                        <span class="text-xs font-bold text-amber-700">Abierta · {{ $sesion->presentes_count }} presentes</span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-2 text-right whitespace-nowrap">
                                                    @if ($sesion)
                                                        @canRoute('admin.deportes.asistencia.show')
                                                        <a href="{{ route($sesion->cerrada_sad ? 'admin.deportes.asistencia.reporte' : 'admin.deportes.asistencia.show', $sesion->id_sad) }}" class="text-xs font-bold" style="color:var(--color-primary);">{{ $sesion->cerrada_sad ? 'Ver reporte' : 'Continuar' }}</a>
                                                        @endcanRoute
                                                    @elseif ($actividad->estaActiva())
                                                        @canRoute('admin.deportes.asistencia.store')
                                                        <form method="POST" action="{{ route('admin.deportes.asistencia.store') }}" class="inline">
                                                            @csrf
                                                            <input type="hidden" name="hde_id" value="{{ $bloque->id_hde }}">
                                                            <input type="hidden" name="fecha" value="{{ $fecha }}">
                                                            <button type="submit" class="text-xs font-bold" style="color:var(--color-primary);">Tomar asistencia</button>
                                                        </form>
                                                        @endcanRoute
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    @empty
                                        <tr><td class="px-4 py-6 text-center text-sm" style="color:var(--text-muted);">Todavía no ha ocurrido ningún evento de esta actividad. El día del evento aparecerá aquí para tomar asistencia.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if ($proximas !== [])
                            <div class="border-t p-4 text-sm" style="border-color:var(--border-color);color:var(--text-main);">
                                <p class="mb-1 text-xs font-bold uppercase" style="color:var(--text-muted);">Próximas fechas</p>
                                {{ collect($proximas)->map($etiquetaFecha)->implode(' · ') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('admin.deportes.partials.confirmar')
</x-app-layout>
