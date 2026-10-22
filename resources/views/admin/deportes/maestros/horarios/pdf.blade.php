@php
    use Carbon\Carbon;

    $turnos = [
        'MATUTINO' => [
            ['inicio' => '07:30', 'fin' => '08:15'],
            ['inicio' => '08:15', 'fin' => '09:00'],
            ['inicio' => '09:20', 'fin' => '10:05'],
            ['inicio' => '10:05', 'fin' => '10:50'],
            ['inicio' => '10:50', 'fin' => '11:35'],
            ['inicio' => '11:35', 'fin' => '12:20'],
        ],
        'VESPERTINO' => [
            ['inicio' => '13:30', 'fin' => '14:15'],
            ['inicio' => '14:15', 'fin' => '15:00'],
            ['inicio' => '15:00', 'fin' => '15:45'],
            ['inicio' => '15:45', 'fin' => '16:30'],
            ['inicio' => '16:30', 'fin' => '17:15'],
            ['inicio' => '17:15', 'fin' => '18:00'],
        ],
        'NOCTURNO' => [
            ['inicio' => '18:00', 'fin' => '18:40'],
            ['inicio' => '18:40', 'fin' => '19:20'],
            ['inicio' => '19:20', 'fin' => '20:00'],
            ['inicio' => '20:00', 'fin' => '20:40'],
            ['inicio' => '20:40', 'fin' => '21:20'],
            ['inicio' => '21:20', 'fin' => '22:00'],
        ],
    ];

    $actividadPorTurnoDiaBloque = [];

    foreach ($turnos as $turnoNombre => $bloquesTurno) {
        foreach ($diasSemana as $diaInfo) {
            foreach ($bloquesTurno as $bloque) {
                $actividadPorTurnoDiaBloque[$turnoNombre][$diaInfo['fecha']][$bloque['inicio']] = [];
            }
        }
    }

    foreach ($horarios as $horario) {
        foreach ($diasSemana as $diaInfo) {
            if (! $horario->aplicaEnFecha($diaInfo['fecha'])) {
                continue;
            }

            $inicioReal = $horario->horaInicio();

            foreach ($turnos as $turnoNombre => $bloquesTurno) {
                foreach ($bloquesTurno as $bloque) {
                    if ($inicioReal >= $bloque['inicio'] && $inicioReal < $bloque['fin']) {
                        $actividadPorTurnoDiaBloque[$turnoNombre][$diaInfo['fecha']][$bloque['inicio']][] = [
                            'nombre' => $horario->actividad?->nombre_acd ?? 'Sin actividad',
                            'hora_inicio_label' => $horario->horaInicio('g:i A'),
                            'hora_fin_label' => $horario->horaFin('g:i A'),
                            'espacio' => $horario->espacio?->nombre_epd ?? '',
                        ];

                        continue 3;
                    }
                }
            }
        }
    }

    $turnos = array_filter($turnos, function ($bloquesTurno, $turnoNombre) use ($actividadPorTurnoDiaBloque) {
        foreach ($actividadPorTurnoDiaBloque[$turnoNombre] as $porBloque) {
            foreach ($porBloque as $actividades) {
                if (! empty($actividades)) {
                    return true;
                }
            }
        }

        return false;
    }, ARRAY_FILTER_USE_BOTH);
@endphp

<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>HORARIO DE DEPORTES</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm 8mm 10mm 8mm;
        }

        html, body {
            margin: 0;
            padding: 0;
            font-family: DejaVu Sans, sans-serif;
            color: #111827;
            background: #ffffff;
        }

        body {
            padding: 10px;
        }

        h1 {
            text-align: center;
            margin: 0 0 12px;
            font-size: 26px;
            letter-spacing: 0.08em;
        }

        .turno {
            margin-bottom: 14px;
            page-break-inside: avoid;
            -fs-table-paginate: paginate;
        }

        .turno h3 {
            margin: 0 0 6px;
            font-size: 13px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            background: #f3f4f6;
            padding: 6px 8px;
            border: 1px solid #d1d5db;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 8px;
            page-break-inside: auto;
        }

        thead {
            display: table-header-group;
        }

        th, td {
            border: 1px solid #d1d5db;
            padding: 4px;
            vertical-align: top;
            text-align: center;
        }

        thead th {
            background: #f3f4f6;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .bloque-label {
            width: 80px;
            background: #f9fafb;
            color: #374151;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            line-height: 1.5;
        }

        .celda {
            min-height: 48px;
            background: #ffffff;
            padding: 3px;
        }

        .semana {
            text-align: center;
            margin: -6px 0 12px;
            font-size: 11px;
            color: #374151;
        }

        .actividad-card {
            display: block;
            border: 1px solid #d1d5db;
            background: #f9fafb;
            border-radius: 3px;
            padding: 3px;
            margin: 2px 0;
            text-align: left;
            line-height: 1.35;
        }

        .actividad-name {
            display: block;
            font-weight: bold;
            font-size: 7px;
            color: #111827;
        }

        .actividad-meta {
            display: block;
            font-size: 7px;
            color: #374151;
        }

        .actividad-espacio {
            display: block;
            font-size: 7px;
            color: #1f2937;
            margin-top: 2px;
        }
    </style>
</head>
<body>
    <h1>HORARIO DE DEPORTES</h1>
    <p class="semana">Semana del {{ $semanaInicio->format('d/m/Y') }} al {{ $semanaInicio->copy()->addDays(4)->format('d/m/Y') }}</p>

    @if (empty($turnos))
        <p class="semana">No hay actividades programadas en esta semana.</p>
    @endif

    @foreach ($turnos as $turnoNombre => $bloquesTurno)
        <div class="turno">
            <h3>{{ $turnoNombre }}</h3>
            <table>
                <thead>
                    <tr>
                        <th>Bloque</th>
                        @foreach ($diasSemana as $diaInfo)
                            <th>{{ $diaInfo['nombre'] }} {{ Carbon::parse($diaInfo['fecha'])->format('d/m') }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($bloquesTurno as $bloque)
                        <tr>
                            <td class="bloque-label">
                                {{ Carbon::parse($bloque['inicio'])->format('g:i A') }}<br>
                                -<br>
                                {{ Carbon::parse($bloque['fin'])->format('g:i A') }}
                            </td>

                            @foreach ($diasSemana as $diaInfo)
                                <td class="celda">
                                    @foreach (($actividadPorTurnoDiaBloque[$turnoNombre][$diaInfo['fecha']][$bloque['inicio']] ?? []) as $actividad)
                                        <div class="actividad-card">
                                            <span class="actividad-name">{{ $actividad['nombre'] }}</span>
                                            <span class="actividad-meta">{{ $actividad['hora_inicio_label'] }} - {{ $actividad['hora_fin_label'] }}</span>
                                            @if (!empty($actividad['espacio']))
                                                <span class="actividad-espacio">{{ $actividad['espacio'] }}</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endforeach
</body>
</html>
