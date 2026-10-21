<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Horario Semanal</title>
    <style>
        
        body {
            font-family: 'Nunito', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #374151;
        }
        
        @page { margin: 40px 25px 80px 25px; }
        header { position: fixed; top: -60px; left: 0px; right: 0px; height: 50px; text-align: center; }
        footer { position: fixed; bottom: -60px; left: 0px; right: 0px; height: 50px; text-align: center; }
        
        .header-title { text-align: center; margin-bottom: 25px; margin-top: 10px; }
        .header-title h1 { margin: 0; font-size: 18px; font-weight: 800; color: #111827; letter-spacing: 1px; }
        .header-title p { margin: 5px 0 0; font-size: 12px; font-weight: 600; color: #6b7280; }
        
        table { 
            width: 100%; 
            border-collapse: separate; 
            border-spacing: 0;
            margin-top: 10px; 
            border: 1.5px solid #d1d5db;
            border-radius: 12px;
            overflow: hidden;
        }
        th, td { 
            padding: 10px 8px; 
            text-align: center; 
            vertical-align: middle; 
            border-bottom: 1px solid #e5e7eb;
            border-right: 1px solid #e5e7eb;
        }
        th:last-child, td:last-child { border-right: none; }
        tr:last-child td { border-bottom: none; }

        th { 
            font-weight: 700; 
            font-size: 10px;
            color: #383636ff; /*Color de la letra de los días  */
            text-transform: uppercase; 
            background-color: #f9fafb;
            border-bottom: 2px solid #d1d5db; /*Color de la raya debajo letra de los días  */
            letter-spacing: 0.5px;
        }
        .time-col { 
            width: 14%; 
            font-weight: 700; 
            font-size: 8.5px; 
            color: #383636ff; /*Color de letras de la columna de las horas */
            background-color: #f9fafb;
        }
        .day-col { width: 17.2%; }
        
        /* Celda que representa el rango laboral completo — SIN div interno */
        .celda-rango {
            background-color: rgba(249, 250, 251, 0.5);
            border: 0.5px solid #d1d5db;
            padding: 10;
            vertical-align: middle;
        }

        .celda-rango .rango-horas {
            font-size: 13px;
            font-weight: 800;
            color: #383636ff;
            display: block;
            margin-bottom: 4px;
        }

        .celda-rango .rango-nombre {
            font-size: 8.5px;
            font-weight: 700;
            color: #4b5563;
            display: block;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .empty-dash {
            color: #d1d5db;
            font-weight: 800;
            font-size: 14px;
        }

        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.08;
            z-index: -10;
            width: 55%;
        }
    </style>
</head>
<body>
    <img src="{{ public_path('img/logo-universidad-watermark.png') }}" class="watermark" alt="Logo de fondo">

    <main>
        <div class="header-title">
            <h1>HORARIO LABORAL</h1>
            <p>{{ mb_strtoupper($psicologo->persona->nombres_per ?? 'PSICÓLOGO NO ASIGNADO') }} {{ mb_strtoupper($psicologo->persona->apellidos_per ?? 'N/A') }}</p>
        </div>

        @php
            $intervalos = collect([
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
            ])->sortBy(fn($i) => \Carbon\Carbon::parse($i['inicio'])->timestamp)->values()->all();

            $secciones = ['Matutino' => [], 'Vespertino' => [], 'Nocturno' => []];
            foreach ($intervalos as $intervalo) {
                $t = \Carbon\Carbon::parse($intervalo['inicio']);
                if ($t->lt(\Carbon\Carbon::parse('12:30'))) {
                    $secciones['Matutino'][] = $intervalo;
                } elseif ($t->lt(\Carbon\Carbon::parse('18:00'))) {
                    $secciones['Vespertino'][] = $intervalo;
                } else {
                    $secciones['Nocturno'][] = $intervalo;
                }
            }
        @endphp

        <table>
            <thead>
                <tr>
                    <th class="time-col">HORA</th>
                    @foreach($dias as $dia)
                        <th class="day-col">{{ mb_strtoupper($dia) }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($secciones as $seccionNombre => $bloques)
                    @if(!empty($bloques))
                        <tr>
                            <td colspan="{{ count($dias) + 1 }}" style="background-color: #e2e8f0; font-size: 8px; font-weight: 900; text-align: center; padding: 4px; text-transform: uppercase; letter-spacing: 1px; color: #475569;">
                                {{ $seccionNombre }}
                            </td>
                        </tr>
                        @foreach($bloques as $intervalo)
                            <tr>
                                <td class="time-col" style="font-size: 8px;">
                                    {{ \Carbon\Carbon::parse($intervalo['inicio'])->format('g:i') }} - {{ \Carbon\Carbon::parse($intervalo['fin'])->format('g:i') }}
                                </td>
                                @foreach($dias as $dia)
                                    @php
                                        $blockStart = \Carbon\Carbon::parse($intervalo['inicio']);
                                        $blockEnd   = \Carbon\Carbon::parse($intervalo['fin']);

                                        $horarioBloque = ($horariosPorDia[$dia] ?? collect())
                                            ->first(function ($h) use ($blockStart, $blockEnd) {
                                                $hInicio = \Carbon\Carbon::parse($h->hora_inicio);
                                                $hFin    = \Carbon\Carbon::parse($h->hora_fin);
                                                return $hInicio->lt($blockEnd) && $hFin->gt($blockStart);
                                            });
                                    @endphp
                                    @if($horarioBloque)
                                        <td style="background-color: #f1f5f9; border: 1px solid #cbd5e1; text-align: center; padding: 5px;">
                                            <span style="font-size: 7px; font-weight: 900; color: #1e293b; display: block;">
                                                {{ \Carbon\Carbon::parse($horarioBloque->hora_inicio)->format('g:i A') }} - {{ \Carbon\Carbon::parse($horarioBloque->hora_fin)->format('g:i A') }}
                                            </span>
                                        </td>
                                    @else
                                        <td style="border: 1px solid #e2e8f0; background-color: #ffffff; text-align: center;">
                                            <span style="color: #e2e8f0; font-size: 8px;">—</span>
                                        </td>
                                    @endif
                                @endforeach
                            </tr>
                        @endforeach
                    @endif
                @endforeach
            </tbody>
        </table>
    </main>
</body>
</html>