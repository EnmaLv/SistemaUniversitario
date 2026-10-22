@props(['h'])
@php
    $themeColor = 'green';
    $espacioNombre = $h->espacio->nombre ?? 'Sin espacio';
    $actividadNombre = $h->actividad->nombre ?? 'Actividad';
    $horaInicio = \Carbon\Carbon::parse($h->hora_inicio)->format('g:i A');
    $horaFin = \Carbon\Carbon::parse($h->hora_fin)->format('g:i A');
    $diaSemana = null;
    if (!empty($h->dia_semana)) {
        $diaSemana = ucfirst(strtolower((string) $h->dia_semana));
    }
    $fechaEspecifica = null;
    if (!empty($h->fecha_especifica)) {
        $fechaEspecifica = \Carbon\Carbon::parse($h->fecha_especifica)->format('d/m/Y');
    }
    $tipoHorario = !empty($h->fecha_especifica) ? 'Fecha específica' : 'Recurrente';
    $estado = isset($h->estado) && $h->estado !== null ? ucfirst((string) $h->estado) : 'Activo';
@endphp

<button
    type="button"
    class="horario-card w-full max-w-full overflow-hidden rounded-xl border border-{{ $themeColor }}-200 bg-{{ $themeColor }}-300/20 p-2 text-left shadow-sm transition-all hover:bg-{{ $themeColor }}-300/30"
    data-id="{{ $h->id }}"
    data-actividad="{{ $actividadNombre }}"
    data-calendario="{{ optional($h->calendario)->nombre ?? '-' }}"
    data-espacio="{{ $espacioNombre }}"
    data-dia="{{ $diaSemana ?? 'Sin día' }}"
    data-fecha="{{ $fechaEspecifica ?? '' }}"
    data-hora-inicio="{{ \Carbon\Carbon::parse($h->hora_inicio)->format('H:i') }}"
    data-hora-fin="{{ \Carbon\Carbon::parse($h->hora_fin)->format('H:i') }}"
    data-cupo="{{ $h->cupo_maximo ?? '-' }}"
    data-tipo="{{ $tipoHorario }}"
    data-estado="{{ $estado }}"
    data-observaciones="{{ e($h->observaciones ?? '') }}"
    data-edit-url="{{ route('admin.deportes.maestros.horarios.edit', ['id' => $h->id]) }}"
    data-delete-url="{{ route('admin.deportes.maestros.horarios.destroy', ['id' => $h->id]) }}"
>
    <div class="flex items-start gap-2">
        <div class="mt-0.5 flex-shrink-0">
            <i class="fas fa-futbol text-[11px] text-{{ $themeColor }}-600"></i>
        </div>

        <div class="min-w-0 flex-1">
            <div class="truncate text-[11px] font-bold text-{{ $themeColor }}-900 leading-snug">
                {{ $actividadNombre }}
            </div>
            <div class="mt-1 text-[10px] font-medium text-{{ $themeColor }}-700 leading-snug">
                {{ $horaInicio }} - {{ $horaFin }}
            </div>
            <div class="mt-1 text-[10px] font-medium text-gray-700 leading-snug">
                {{ $espacioNombre }}
            </div>
        </div>
    </div>
</button>
