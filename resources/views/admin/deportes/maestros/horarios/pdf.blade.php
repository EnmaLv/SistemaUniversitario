@php
    use Carbon\Carbon;

    $normalizarDia = function ($valor) {
        $map = ['Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'Ñ' => 'N', 'ñ' => 'n'];
        $texto = strtr((string) $valor, $map);
        return strtolower(trim($texto));
    };

    $diasSemana = [
        'lunes' => 'Lunes',
        'martes' => 'Martes',
        'miercoles' => 'Miércoles',
        'jueves' => 'Jueves',
        'viernes' => 'Viernes',
    ];

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

    $slotMap = [];
    foreach ($turnos as $turnoNombre => $bloquesTurno) {
        foreach ($bloquesTurno as $bloque) {
            $slotKey = Carbon::parse($bloque['inicio'])->format('H:i:s') . '|' . Carbon::parse($bloque['fin'])->format('H:i:s');
            $slotMap[$turnoNombre][$slotKey] = $bloque;
        }
    }

    $actividadPorTurnoDiaBloque = [];
    foreach ($turnos as $turnoNombre => $bloquesTurno) {
        foreach ($diasSemana as $diaClave => $diaLabel) {
            $actividadPorTurnoDiaBloque[$turnoNombre][$diaClave] = [];
            foreach ($bloquesTurno as $bloque) {
                $slotKey = Carbon::parse($bloque['inicio'])->format('H:i:s') . '|' . Carbon::parse($bloque['fin'])->format('H:i:s');
                $actividadPorTurnoDiaBloque[$turnoNombre][$diaClave][$slotKey] = [];
            }
        }
    }

    $horariosSinBloqueExacto = [];

    foreach ($horarios as $horario) {
        if (($horario->estado ?? null) !== 'activo') {
            continue;
        }

        $dia = null;
        if (!empty($horario->fecha_especifica)) {
            $fecha = Carbon::parse($horario->fecha_especifica);
            $dia = $normalizarDia($fecha->locale('es')->translatedFormat('l'));
        } elseif (!empty($horario->dia_semana)) {
            $dia = $normalizarDia($horario->dia_semana);
        }

        if (!$dia || !isset($diasSemana[$dia])) {
            continue;
        }

        $inicioReal = Carbon::parse($horario->hora_inicio)->format('H:i:s');
        $finReal = Carbon::parse($horario->hora_fin)->format('H:i:s');
        $actividad = [
            'nombre' => $horario->actividad->nombre ?? 'Sin actividad',
            'inicio' => $inicioReal,
            'fin' => $finReal,
            'hora_inicio_label' => Carbon::parse($horario->hora_inicio)->format('g:i A'),
            'hora_fin_label' => Carbon::parse($horario->hora_fin)->format('g:i A'),
            'espacio' => $horario->espacio->nombre ?? '',
        ];

        $asignada = false;
        foreach ($turnos as $turnoNombre => $bloquesTurno) {
            foreach ($bloquesTurno as $bloque) {
                $slotKey = Carbon::parse($bloque['inicio'])->format('H:i:s') . '|' . Carbon::parse($bloque['fin'])->format('H:i:s');
                $bloqueInicio = Carbon::parse($bloque['inicio'])->format('H:i:s');
                $bloqueFin = Carbon::parse($bloque['fin'])->format('H:i:s');

                if ($inicioReal === $bloqueInicio && $finReal === $bloqueFin) {
                    $actividadPorTurnoDiaBloque[$turnoNombre][$dia][$slotKey][] = $actividad;
                    $asignada = true;
                    break 2;
                }
            }
        }

        if (!$asignada) {
            $horariosSinBloqueExacto[] = [
                'actividad' => $actividad['nombre'],
                'dia' => $dia,
                'inicio' => $inicioReal,
                'fin' => $finReal,
                'espacio' => $actividad['espacio'],
            ];
        }
    }
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

        .ocultar-fin-de-semana {
            display: none;
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

    @foreach ($turnos as $turnoNombre => $bloquesTurno)
        <div class="turno">
            <h3>{{ $turnoNombre }}</h3>
            <table>
                <thead>
                    <tr>
                        <th>Bloque</th>
                        @foreach ($diasSemana as $diaClave => $diaLabel)
                            <th>{{ $diaLabel }}</th>
                        @endforeach
                        <th class="ocultar-fin-de-semana">Sábado</th>
                        <th class="ocultar-fin-de-semana">Domingo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($bloquesTurno as $bloque)
                        @php
                            $bloqueKey = Carbon::parse($bloque['inicio'])->format('H:i:s') . '|' . Carbon::parse($bloque['fin'])->format('H:i:s');
                        @endphp
                        <tr>
                            <td class="bloque-label">
                                {{ Carbon::parse($bloque['inicio'])->format('g:i A') }}<br>
                                -<br>
                                {{ Carbon::parse($bloque['fin'])->format('g:i A') }}
                            </td>

                            @foreach ($diasSemana as $diaClave => $diaLabel)
                                <td class="celda">
                                    @foreach (($actividadPorTurnoDiaBloque[$turnoNombre][$diaClave][$bloqueKey] ?? []) as $actividad)
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
                            <td class="celda ocultar-fin-de-semana"></td>
                            <td class="celda ocultar-fin-de-semana"></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endforeach
</body>
</html>
