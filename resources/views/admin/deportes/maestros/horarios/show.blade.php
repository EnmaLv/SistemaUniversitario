@php
    $cupos = $horario->cupo_maximo_hde;
    $libres = $cupos === null ? null : max(0, $cupos - $horario->inscritos_count);
@endphp
<x-app-layout>
    @include('components.alert')

    <div class="pt-6 pb-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <a href="{{ route('admin.deportes.maestros.horarios.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold" style="color:var(--text-muted);">
                <i class="fas fa-arrow-left text-xs"></i> Volver a horarios
            </a>

            <div class="mt-2 mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" style="color:var(--text-main);">
                    {{ $horario->actividad?->nombre_acd ?? 'Horario' }} <span class="text-base font-bold" style="color:var(--text-muted);">#{{ $horario->id_hde }}</span>
                </h1>
                <div class="flex flex-wrap gap-2">
                    @canRoute('admin.deportes.maestros.horarios.cartel_pdf')
                    <a href="{{ route('admin.deportes.maestros.horarios.cartel_pdf', $horario->id_hde) }}" target="_blank" class="inline-flex items-center gap-2 rounded-xl bg-red-800 px-4 py-2.5 text-sm font-extrabold text-white shadow-lg hover:bg-red-900">
                        <i class="fas fa-file-pdf text-xs"></i> Cartel PDF
                    </a>
                    @endcanRoute
                    @canRoute('admin.deportes.inscripciones.index')
                    <a href="{{ route('admin.deportes.inscripciones.index', ['hde_id' => $horario->id_hde]) }}" class="inline-flex items-center gap-2 rounded-xl border px-4 py-2.5 text-sm font-bold" style="border-color:var(--border-color);color:var(--text-main);">
                        <i class="fas fa-user-plus text-xs"></i> Inscritos
                    </a>
                    @endcanRoute
                    @canRoute('admin.deportes.asistencia.index')
                    <a href="{{ route('admin.deportes.asistencia.index', ['hde_id' => $horario->id_hde]) }}" class="inline-flex items-center gap-2 rounded-xl border px-4 py-2.5 text-sm font-bold" style="border-color:var(--border-color);color:var(--text-main);">
                        <i class="fas fa-clipboard-check text-xs"></i> Asistencia
                    </a>
                    @endcanRoute
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 rounded-2xl border p-6 shadow-sm sm:grid-cols-2" style="background-color:var(--bg-card);border-color:var(--border-color);color:var(--text-main);">
                <div><p class="text-xs font-bold uppercase" style="color:var(--text-muted);">Calendario</p><p class="font-semibold">{{ $horario->calendario?->nombre_cld ?? '-' }}</p></div>
                <div><p class="text-xs font-bold uppercase" style="color:var(--text-muted);">Sede</p><p class="font-semibold">{{ $horario->calendario?->sede?->nombre_sed ?? '-' }}</p></div>
                <div><p class="text-xs font-bold uppercase" style="color:var(--text-muted);">Espacio</p><p class="font-semibold">{{ $horario->espacio?->nombre_epd ?? '-' }}</p></div>
                <div><p class="text-xs font-bold uppercase" style="color:var(--text-muted);">Cuándo</p><p class="font-semibold">{{ $horario->descripcionCuando() }}</p></div>
                <div>
                    <p class="text-xs font-bold uppercase" style="color:var(--text-muted);">Vigencia</p>
                    @php $vigencia = $horario->vigencia(); @endphp
                    <p class="font-semibold">{{ $vigencia ? $vigencia[0]->format('d/m/Y') . ' - ' . $vigencia[1]->format('d/m/Y') : '-' }}</p>
                </div>
                <div><p class="text-xs font-bold uppercase" style="color:var(--text-muted);">Estado</p><p class="font-semibold">{{ ucfirst($horario->estado_hde) }}</p></div>
                <div>
                    <p class="text-xs font-bold uppercase" style="color:var(--text-muted);">Cupos</p>
                    <p class="font-semibold">{{ $horario->inscritos_count }} inscritos{{ $cupos !== null ? ' de ' . $cupos . ' (' . $libres . ' libres)' : ' · sin límite' }}</p>
                </div>
                <div><p class="text-xs font-bold uppercase" style="color:var(--text-muted);">Responsable</p><p class="font-semibold">{{ trim(($horario->responsable?->persona?->nombres_per ?? '') . ' ' . ($horario->responsable?->persona?->apellidos_per ?? '')) ?: '-' }}</p></div>
                @if ($horario->torneos->isNotEmpty())
                    <div class="sm:col-span-2">
                        <p class="text-xs font-bold uppercase" style="color:var(--text-muted);">Torneos</p>
                        @foreach ($horario->torneos as $torneo)
                            <a href="{{ route('admin.deportes.torneos.show', $torneo->id_tde) }}" class="mr-2 font-semibold underline" style="color:var(--color-primary);">{{ $torneo->nombre_tde }} ({{ $torneo->estadoLegible() }})</a>
                        @endforeach
                    </div>
                @endif
                <div class="sm:col-span-2"><p class="text-xs font-bold uppercase" style="color:var(--text-muted);">Observaciones</p><p>{{ $horario->observaciones_hde ?: '-' }}</p></div>
                @if ($horario->motivo_cancelacion_hde)
                    <div class="sm:col-span-2"><p class="text-xs font-bold uppercase text-red-700">Motivo de cancelación</p><p>{{ $horario->motivo_cancelacion_hde }}</p></div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
