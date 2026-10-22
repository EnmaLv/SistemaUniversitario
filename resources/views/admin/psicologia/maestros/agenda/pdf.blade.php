<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Agenda Semanal</title>
    <style>
        body {
            font-family: 'Nunito', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #334155;
        }

        @page {
            margin: 40px 25px 80px 25px;
        }

        header {
            position: fixed;
            top: -60px;
            left: 0px;
            right: 0px;
            height: 50px;
            text-align: center;
        }

        footer {
            position: fixed;
            bottom: -60px;
            left: 0px;
            right: 0px;
            height: 50px;
            text-align: center;
        }

        .header-title {
            text-align: center;
            margin-bottom: 25px;
            margin-top: 10px;
        }

        .header-title h1 {
            margin: 0;
            font-size: 18px;
            font-weight: 800;
            color: #03133dff;
            letter-spacing: 0.5px;
        }

        .header-title p {
            margin: 5px 0 0;
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 10px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            overflow: hidden;
        }

        th,
        td {
            padding: 10px 8px;
            text-align: center;
            vertical-align: middle;
            border-bottom: 1px solid #e2e8f0;
            border-right: 1px solid #e2e8f0;
        }

        th:last-child,
        td:last-child {
            border-right: none;
        }

        tr:last-child td {
            border-bottom: none;
        }

        th {
            font-weight: 700;
            font-size: 10px;
            color: #353536ff;
            text-transform: uppercase;
            background-color: #f8fafc;
        }

        .time-col {
            width: 14%;
            font-weight: 700;
            color: #353536ff;
            background-color: #f8fafc;
        }

        .day-col {
            width: 17.2%;
        }

        .cita-block {
            background-color: #eaebecff;
            color: #3d3d3fff;
            padding: 6px 8px;
            border-radius: 8px;
            border: 1px solid #eaebecff;
            margin-bottom: 4px;
        }

        .cita-paciente {
            font-weight: 800;
            font-size: 10px;
            letter-spacing: 0.3px;
        }

        .cita-estado {
            font-size: 8px;
            font-weight: 600;
            color: #6f7978ff;
            text-transform: uppercase;
            margin-top: 2px;
        }

        .empty-dash {
            color: #bec5ceff;
            font-weight: bold;
        }

        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.15;
            z-index: -10;
            width: 60%;
        }
    </style>
</head>

<body>

    @foreach ($semanasInfo as $indexInfo => $semana)
        <img src="{{ public_path('img/logo-universidad-watermark.png') }}" class="watermark" alt="Logo de fondo">
        @php
            $currentDate = $semana['currentDate'];
            $citasCalendario = $semana['citasCalendario'];
        @endphp

        <main>
            <div class="header-title">
                @if ($loop->first)
                    <h1>{{ count($semanasInfo) > 1 ? 'AGENDA DEL MES' : 'AGENDA SEMANAL' }}</h1>
                    <p>{{ mb_strtoupper($psicologo->persona->nombres_per ?? 'PSICÓLOGO NO ASIGNADO') }}
                        {{ mb_strtoupper($psicologo->persona->apellidos_per ?? 'N/A') }}</p>
                @endif
                <p>SEMANA DEL {{ $currentDate->copy()->startOfWeek(\Carbon\Carbon::MONDAY)->format('d/m/Y') }} AL
                    {{ $currentDate->copy()->endOfWeek(\Carbon\Carbon::FRIDAY)->format('d/m/Y') }}</p>
            </div>

            @php
                $weekDays = [
                    'lunes' => $currentDate->copy()->startOfWeek(\Carbon\Carbon::MONDAY),
                    'martes' => $currentDate->copy()->startOfWeek(\Carbon\Carbon::MONDAY)->addDays(1),
                    'miercoles' => $currentDate->copy()->startOfWeek(\Carbon\Carbon::MONDAY)->addDays(2),
                    'jueves' => $currentDate->copy()->startOfWeek(\Carbon\Carbon::MONDAY)->addDays(3),
                    'viernes' => $currentDate->copy()->startOfWeek(\Carbon\Carbon::MONDAY)->addDays(4),
                ];

                $defaultIntervalos = collect([
                    ['inicio' => '07:00', 'fin' => '08:15'],
                    ['inicio' => '08:15', 'fin' => '09:20'],
                    ['inicio' => '09:20', 'fin' => '10:00'],
                    ['inicio' => '10:00', 'fin' => '10:45'],
                    ['inicio' => '10:45', 'fin' => '11:30'],
                    ['inicio' => '11:30', 'fin' => '12:20'],
                    ['inicio' => '12:20', 'fin' => '13:00'],
                    ['inicio' => '13:00', 'fin' => '13:45'],
                    ['inicio' => '13:45', 'fin' => '14:25'],
                    ['inicio' => '14:25', 'fin' => '15:05'],
                    ['inicio' => '15:05', 'fin' => '15:45'],
                    ['inicio' => '16:00', 'fin' => '16:40'],
                    ['inicio' => '16:40', 'fin' => '17:20'],
                    ['inicio' => '17:20', 'fin' => '18:00'],
                    ['inicio' => '18:00', 'fin' => '18:35'],
                    ['inicio' => '18:35', 'fin' => '19:10'],
                    ['inicio' => '19:10', 'fin' => '19:45'],
                    ['inicio' => '19:45', 'fin' => '20:20'],
                    ['inicio' => '20:20', 'fin' => '20:55'],
                    ['inicio' => '20:55', 'fin' => '21:30'],
                ]);

                $timeBlocks = $defaultIntervalos
                    ->sortBy(function ($item) {
                        return \Carbon\Carbon::parse($item['inicio'])->timestamp;
                    })
                    ->values()
                    ->map(function ($item) {
                        return \Carbon\Carbon::parse($item['inicio'])->format('h:i A') .
                            ' - ' .
                            \Carbon\Carbon::parse($item['fin'])->format('h:i A');
                    });

                $normalizeBlock = function ($text) {
                    $value = trim($text ?? '');
                    $value = preg_replace_callback(
                        '/(\d{1,2}):(\d{2})\s*(am|pm)\b/i',
                        function ($matches) {
                            $hours = (int) $matches[1];
                            $ampm = strtolower($matches[3]);
                            if ($ampm === 'pm' && $hours < 12) {
                                $hours += 12;
                            }
                            if ($ampm === 'am' && $hours === 12) {
                                $hours = 0;
                            }
                            return sprintf('%02d:%s', $hours, $matches[2]);
                        },
                        $value,
                    );
                    $value = preg_replace(
                        ['/\s*[-–—]\s*/u', '/(\d{1,2}:\d{2}):\d{2}/', '/\s+/'],
                        ['-', '$1', ' '],
                        $value,
                    );
                    $value = preg_replace('/(^|\s|-)(\d):/', '${1}0$2:', $value);
                    return strtolower($value);
                };

                $skipCells = [];
                foreach ($weekDays as $nombreDia => $fechaObj) {
                    $skipCells[$nombreDia] = 0;
                }
                $timeBlocksArr = $timeBlocks->toArray();
            @endphp

            <table>
                <thead>
                    <tr>
                        <th class="time-col">HORA</th>
                        @foreach ($weekDays as $nombreDia => $fechaObj)
                            <th class="day-col">{{ mb_strtoupper($nombreDia) }}<br>{{ $fechaObj->format('d/m/Y') }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @if ($timeBlocks->isEmpty())
                        <tr>
                            <td colspan="6" style="padding: 20px;">No hay bloques de horario ni citas en esta semana.
                            </td>
                        </tr>
                    @else
                        @foreach ($timeBlocksArr as $index => $time)
                            <tr>
                                <td class="time-col" style="font-size: 8px;">{{ $time }}</td>
                                @foreach ($weekDays as $nombreDia => $fechaObj)
                                    @if ($skipCells[$nombreDia] > 0)
                                        @php $skipCells[$nombreDia]--; @endphp
                                    @else
                                        @php
                                            $matchedCitas = [];
                                            $parts = explode(' - ', $time);
                                            $blockStart = \Carbon\Carbon::parse(trim($parts[0] ?? ''));
                                            $blockEnd = \Carbon\Carbon::parse(trim($parts[1] ?? ''));
                                            $horaInicio = $blockStart->format('H:i');
                                            $horaFin = $blockEnd->format('H:i');

                                            $horarioBloque = $horarios
                                                ->where(
                                                    'dia_hpr',
                                                    ucfirst($nombreDia === 'miercoles' ? 'Miércoles' : $nombreDia),
                                                )
                                                ->first(function ($h) use ($blockStart, $blockEnd) {
                                                    $hInicio = \Carbon\Carbon::parse($h->hora_inicio_hpr);
                                                    $hFin = \Carbon\Carbon::parse($h->hora_fin_hpr);
                                                    return $hInicio->lt($blockEnd) && $hFin->gt($blockStart);
                                                });

                                            if (!$horarioBloque) {
                                                echo '<td style="border: 1px solid #e2e8f0; background-color: #f8f8f8;"><span class="empty-dash">-</span></td>';
                                            } else {
                                                foreach ($citasCalendario as $cita) {
                                                    if (!$cita->fecha_cps->isSameDay($fechaObj)) {
                                                        continue;
                                                    }

                                                    $citaStart = null;
                                                    $citaEnd = null;

                                                    if (!empty($cita->bloque_propuesto_cps)) {
                                                        if (
                                                            preg_match(
                                                                '/(\d{1,2}:\d{2}(?:\s*[aApP][mM])?)\s*[-–]\s*(\d{1,2}:\d{2}(?:\s*[aApP][mM])?)/i',
                                                                $cita->bloque_propuesto_cps,
                                                                $m,
                                                            )
                                                        ) {
                                                            $citaStart = \Carbon\Carbon::parse($m[1]);
                                                            $citaEnd = \Carbon\Carbon::parse($m[2]);
                                                        }
                                                    }

                                                    if (!$citaStart && !empty($cita->hora_cps)) {
                                                        $citaStart = \Carbon\Carbon::parse($cita->hora_cps);
                                                        $citaEnd = $citaStart->copy()->addHour();
                                                    }

                                                    if (!$citaStart) {
                                                        continue;
                                                    }

                                                    if ($citaStart->lt($blockEnd) && $citaEnd->gt($blockStart)) {
                                                        if (!in_array($cita, $matchedCitas, true)) {
                                                            $matchedCitas[] = $cita;
                                                        }
                                                    }
                                                }

                                                if (count($matchedCitas) > 0) {
                                                    echo '<td style="vertical-align: top; border: 1px solid #cbd5e1; background-color: #ffffff;">';
                                                    foreach ($matchedCitas as $mcita) {
                                                        $mcHora = null;
                                                        if (
                                                            !empty($mcita->bloque_propuesto_cps) &&
                                                            preg_match(
                                                                '/(\d{1,2}:\d{2}(?:\s*[aApP][mM])?)/i',
                                                                $mcita->bloque_propuesto_cps,
                                                                $hm,
                                                            )
                                                        ) {
                                                            $mcHora = \Carbon\Carbon::parse($hm[1]);
                                                        } elseif (!empty($mcita->hora_cps)) {
                                                            $mcHora = \Carbon\Carbon::parse($mcita->hora_cps);
                                                        }

                                                        echo '<div style="background-color: #ffffff; border-radius: 4px; text-align: center; margin-bottom: 3px;">';
                                                        if ($mcHora) {
                                                            echo '<span style="font-size: 7px; font-weight: 900; color: #1e293b; display: block;">' .
                                                                $mcHora->format('g:i A') .
                                                                '</span>';
                                                        }
                                                        $nombreCorto =
                                                            $mcita->paciente->persona->nombres_per ?? 'Paciente';
                                                        echo '<span style="font-size: 7px; color: #475569; display: block; margin-top: 2px;">' .
                                                            htmlspecialchars($nombreCorto) .
                                                            '</span>';
                                                        echo '</div>';
                                                    }
                                                    echo '</td>';
                                                } else {
                                                    echo '<td style="border: 1px solid #e2e8f0; background-color: #ffffff; text-align: center;"><span style="color: #e2e8f0; font-size: 8px;">—</span></td>';
                                                }
                                            }
                                        @endphp
                                    @endif
                                @endforeach
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </main>

        @if (!$loop->last)
            <div style="page-break-after: always;"></div>
        @endif
    @endforeach
</body>

</html>
