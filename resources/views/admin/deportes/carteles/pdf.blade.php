@php
    $sede = $actividad->sede?->nombre_sed;
    $titulo = $torneo?->nombre_tde ?? $actividad->nombre_cld;
    $descripcion = $torneo?->descripcion_tde ?? $actividad->descripcion_cld;
    $esTorneo = $torneo !== null || $actividad->esTorneo();
    $disciplinas = $bloques->map(fn ($bloque) => $bloque->actividad?->nombre_acd)->filter()->unique()->values()->all();
    $subtitulo = $esTorneo
        ? 'Torneo de ' . (\App\Models\Deporte\DeporteCalendario::listaLegible($disciplinas) ?: 'deporte')
        : ($actividad->esRecurrente() ? 'Actividad deportiva semanal' : 'Jornada deportiva');
    $cuando = $actividad->descripcionCuando();
    $variosBloques = $bloques->count() > 1;
@endphp
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $titulo }}</title>
    <style>
        @page { size: letter portrait; margin: 14mm; }
        html, body { margin: 0; padding: 0; font-family: DejaVu Sans, sans-serif; color: #111827; }
        .marco { border: 6px solid #7f1d1d; border-radius: 18px; padding: 24px 28px; height: 238mm; position: relative; }
        .institucion { text-align: center; font-size: 13px; letter-spacing: 3px; text-transform: uppercase; color: #7f1d1d; font-weight: bold; }
        .subtitulo { text-align: center; font-size: 19px; margin-top: 14px; text-transform: uppercase; letter-spacing: 2px; color: #374151; }
        .titulo { text-align: center; font-size: {{ mb_strlen($titulo) > 40 ? 32 : 44 }}px; line-height: 1.1; font-weight: bold; margin: 10px 0 6px; color: #7f1d1d; text-transform: uppercase; }
        .etiqueta-seccion { text-align: center; font-size: 18px; font-weight: bold; color: #111827; margin-bottom: 4px; }
        .descripcion { text-align: center; font-size: 14px; color: #374151; margin: 6px 24px 0; }
        .cuando { margin: 16px auto 0; text-align: center; font-size: 22px; font-weight: bold; background: #fef2f2; border-radius: 12px; padding: 10px 14px; }
        .bloques { width: 100%; border-collapse: separate; border-spacing: 0 8px; margin-top: 14px; }
        .bloques th { background: #7f1d1d; color: #ffffff; font-size: 13px; text-transform: uppercase; padding: 8px 10px; text-align: left; }
        .bloques td { background: #fef2f2; font-size: {{ $variosBloques ? 17 : 22 }}px; font-weight: bold; padding: 10px; }
        .bloques td small { display: block; font-size: 12px; font-weight: normal; color: #4b5563; }
        .inscripcion { margin-top: 18px; border: 3px dashed #7f1d1d; border-radius: 14px; padding: 14px 18px; text-align: center; }
        .inscripcion h3 { margin: 0 0 8px; font-size: 20px; text-transform: uppercase; color: #7f1d1d; }
        .inscripcion p { margin: 4px 0; font-size: 15px; }
        .pie { position: absolute; bottom: 16px; left: 28px; right: 28px; text-align: center; font-size: 11px; color: #6b7280; }
    </style>
</head>
<body>
    <div class="marco">
        <div class="institucion">Coordinación de Deportes · Bienestar Estudiantil</div>
        <div class="subtitulo">{{ $subtitulo }}</div>
        <div class="titulo">{{ $titulo }}</div>
        @if ($torneo?->modalidad_tde)
            <div class="etiqueta-seccion">Modalidad {{ $torneo->modalidad_tde }}</div>
        @endif
        @if ($actividad->seccionLegible())
            <div class="etiqueta-seccion">Sección {{ $actividad->seccionLegible() }}</div>
        @endif
        @if (!empty($descripcion))
            <div class="descripcion">{{ \Illuminate\Support\Str::limit($descripcion, 240) }}</div>
        @endif

        <div class="cuando">{{ $cuando }}</div>

        <table class="bloques">
            <thead>
                <tr>
                    <th>Disciplina</th>
                    <th>Lugar</th>
                    <th>Hora</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($bloques as $bloque)
                    <tr>
                        <td>{{ $bloque->actividad?->nombre_acd ?? 'Por definir' }}</td>
                        <td>{{ $bloque->espacio?->nombre_epd ?? 'Por definir' }}@if ($bloque->espacio?->ubicacion_epd)<small>{{ $bloque->espacio->ubicacion_epd }}</small>@endif</td>
                        <td>{{ $bloque->horas() }}@if (! $esTorneo && $actividad->con_inscripcion_cld && $bloque->cupo_maximo_hde !== null)<small>{{ $bloque->cupo_maximo_hde }} cupos</small>@endif</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="inscripcion">
            @if ($esTorneo)
                <h3>¿Cómo inscribir a tu equipo?</h3>
                <p>Arma tu equipo y acércate a la Coordinación de Deportes{{ $sede ? ' de la sede ' . $sede : '' }}.</p>
                <p>Lleva el nombre del equipo, el PNF que representa (si aplica) y la cédula de cada integrante.</p>
                <p><strong>Cada persona puede jugar en un solo equipo del torneo.</strong></p>
            @elseif ($actividad->con_inscripcion_cld)
                <h3>¿Cómo inscribirte?</h3>
                <p>Acércate a la Coordinación de Deportes{{ $sede ? ' de la sede ' . $sede : '' }} con tu cédula de identidad{{ $variosBloques ? ' e indica en qué disciplina quieres participar' : '' }}.</p>
                <p>Pueden participar estudiantes, docentes, administrativos y obreros de la universidad.</p>
                <p><strong>Inscripción sujeta a la disponibilidad de cupos.</strong></p>
            @else
                <h3>Entrada libre</h3>
                <p>No necesitas inscribirte: llega a la hora indicada con tu cédula de identidad.</p>
                <p>Pueden participar estudiantes, docentes, administrativos y obreros de la universidad.</p>
            @endif
        </div>

        <div class="pie">Generado el {{ now()->format('d/m/Y h:i A') }}{{ $sede ? ' · ' . $sede : '' }}</div>
    </div>
</body>
</html>
