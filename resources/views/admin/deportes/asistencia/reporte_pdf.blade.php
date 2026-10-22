@php
    $actividad = $bloque->calendario;
    $torneo = $bloque->torneos->sortByDesc('id_tde')->first();
    $fecha = $sesion->fecha_sad;
    $listas = [['Presentes', $reporte['presentes']]];

    if ($reporte['usa_inscritos']) {
        $listas[] = [$reporte['cerrada'] ? 'Inasistentes' : 'Pendientes (asistencia sin cerrar)', $reporte['inasistentes']];
        $listas[] = [$actividad?->esTorneo() ? 'Asistieron sin estar en un equipo' : 'Asistieron sin estar inscritos', $reporte['no_inscritos']];
    }
@endphp
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte de asistencia</title>
    <style>
        @page { size: letter portrait; margin: 14mm 12mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111827; }
        h1 { margin: 0; font-size: 18px; text-align: center; color: #7f1d1d; text-transform: uppercase; }
        h2 { font-size: 12px; margin: 14px 0 4px; color: #7f1d1d; text-transform: uppercase; }
        .sub { text-align: center; margin: 4px 0 14px; color: #374151; }
        .resumen { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .resumen td { border: 1px solid #d1d5db; padding: 6px; text-align: center; }
        .resumen strong { display: block; font-size: 16px; }
        table.detalle { width: 100%; border-collapse: collapse; }
        table.detalle th { background: #7f1d1d; color: #fff; padding: 5px; font-size: 10px; text-transform: uppercase; text-align: left; }
        table.detalle td { border-bottom: 1px solid #e5e7eb; padding: 4px 5px; }
        .c { text-align: center; }
        .nota { margin-top: 12px; font-size: 10px; color: #6b7280; }
    </style>
</head>
<body>
    <h1>Reporte de asistencia</h1>
    <p class="sub">
        <strong>{{ $torneo?->nombre_tde ?? $actividad?->nombre_cld }}</strong><br>
        {{ $bloque->actividad?->nombre_acd }} · {{ $bloque->espacio?->nombre_epd }} · {{ \App\Models\Deporte\DeporteCalendario::DIAS_LEGIBLES[\App\Models\Deporte\DeporteCalendario::diaDeFecha($fecha)] }} {{ $fecha->format('d/m/Y') }} · {{ $bloque->horas() }}
        @if ($actividad?->seccionLegible())
            <br>Sección {{ $actividad->seccionLegible() }}
        @endif
    </p>

    <table class="resumen">
        <tr>
            @foreach ($listas as [$titulo, $filas])
                <td>{{ $titulo }}<strong>{{ $filas->count() }}</strong></td>
            @endforeach
        </tr>
    </table>

    @foreach ($listas as [$titulo, $filas])
        <h2>{{ $titulo }}</h2>
        <table class="detalle">
            <thead>
                <tr><th class="c" style="width:8%;">#</th><th style="width:25%;">Cédula</th><th>Nombre</th></tr>
            </thead>
            <tbody>
                @forelse ($filas as $fila)
                    <tr>
                        <td class="c">{{ $loop->iteration }}</td>
                        <td>{{ $fila->persona?->cedula_per }}</td>
                        <td>{{ $fila->persona ? trim($fila->persona->nombres_per . ' ' . $fila->persona->apellidos_per) : 'Persona eliminada' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="c">Nadie en esta lista.</td></tr>
                @endforelse
            </tbody>
        </table>
    @endforeach

    <p class="nota">
        {{ $reporte['cerrada'] ? 'Asistencia cerrada el ' . $sesion->cerrada_en_sad?->format('d/m/Y h:i A') . '.' : 'Asistencia sin cerrar.' }}
        Generado el {{ now()->format('d/m/Y h:i A') }}.
    </p>
</body>
</html>
