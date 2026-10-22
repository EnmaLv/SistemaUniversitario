@php
    $espacioNombre = $h->espacio?->nombre_epd ?? 'Sin espacio';
    $actividadNombre = $h->actividad?->nombre_acd ?? 'Actividad';
    $vigencia = $h->vigencia();
    $inscritos = $h->inscritos_count;
@endphp

<button
    type="button"
    class="horario-card w-full max-w-full overflow-hidden rounded-xl border border-green-200 bg-green-300/20 p-2 text-left shadow-sm transition-all hover:bg-green-300/30"
    data-id="{{ $h->id_hde }}"
    data-actividad="{{ $actividadNombre }}"
    data-calendario="{{ $h->calendario?->nombre_cld ?? '-' }}"
    data-espacio="{{ $espacioNombre }}"
    data-dia="{{ $h->esFechaEspecifica() ? $h->diaLegible() . ' ' . $h->fecha_especifica_hde->format('d/m/Y') : 'Todos los ' . mb_strtolower($h->diaLegible()) }}"
    data-hora-inicio="{{ $h->horaInicio() }}"
    data-hora-fin="{{ $h->horaFin() }}"
    data-cupo="{{ $h->cupo_maximo_hde ?? 'Sin límite' }}"
    data-inscritos="{{ $inscritos }}"
    data-tipo="{{ $h->esFechaEspecifica() ? 'Fecha específica' : 'Semanal' }}"
    data-estado="{{ ucfirst((string) $h->estado_hde) }}"
    data-vigencia="{{ $vigencia ? $vigencia[0]->format('d/m/Y') . ' - ' . $vigencia[1]->format('d/m/Y') : '' }}"
    data-observaciones="{{ $h->observaciones_hde ?? '' }}"
    data-show-url="{{ route('admin.deportes.maestros.horarios.show', ['id' => $h->id_hde]) }}"
    data-edit-url="{{ route('admin.deportes.maestros.horarios.edit', ['id' => $h->id_hde]) }}"
    data-delete-url="{{ route('admin.deportes.maestros.horarios.destroy', ['id' => $h->id_hde]) }}"
    data-cartel-url="{{ route('admin.deportes.maestros.horarios.cartel_pdf', ['id' => $h->id_hde]) }}"
    data-inscritos-url="{{ route('admin.deportes.inscripciones.index', ['hde_id' => $h->id_hde]) }}"
>
    <div class="flex items-start gap-2">
        <div class="mt-0.5 flex-shrink-0">
            <i class="fas fa-futbol text-[11px] text-green-600"></i>
        </div>

        <div class="min-w-0 flex-1">
            <div class="truncate text-[11px] font-bold text-green-900 dark:text-green-200 leading-snug">
                {{ $actividadNombre }}
            </div>
            <div class="mt-1 text-[10px] font-medium text-green-700 dark:text-green-300 leading-snug">
                {{ $h->horaInicio('g:i A') }} - {{ $h->horaFin('g:i A') }}
            </div>
            <div class="mt-1 text-[10px] font-medium text-gray-700 dark:text-gray-300 leading-snug">
                {{ $espacioNombre }}
            </div>
        </div>
    </div>
</button>
