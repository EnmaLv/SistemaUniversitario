<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Horario de Consultorio - {{ $consultorio->nombre_csr }}</title>
    <style>
        body {
            font-family: 'Nunito', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #334155;
            margin: 0;
            padding: 0;
        }

        @page {
            margin: 35px 25px 35px 25px;
        }

        .header-title {
            text-align: center;
            margin-bottom: 12px;
            margin-top: 5px;
        }

        .header-title h1 {
            margin: 0;
            padding-bottom: 3px;
            font-size: 17px;
            font-weight: 800;
            color: #03133d;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .header-title p {
            margin: 0;
            font-size: 14px;
            font-weight: 800;
            color: #000000;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .jornada-header {
            background-color: #f1f5f9;
            color: #0f172a;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            padding: 4px 10px;
            text-align: center;
            letter-spacing: 0.5px;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 5px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            overflow: hidden;
        }

        th,
        td {
            padding: 5px 4px;
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
            font-weight: 800;
            font-size: 11px;
            color: #1e293b;
            text-transform: uppercase;
            background-color: #f1f5f9;
        }

        .time-col {
            width: 14%;
            font-weight: 700;
            font-size: 10px;
            color: #334155;
            background-color: #f8fafc;
            white-space: nowrap;
        }

        .bloque-asignado {
            color: #000000;
            font-weight: 800;
            font-size: 10px;
            padding: 3px 6px;
            border-radius: 4px;
            display: inline-block;
            text-transform: uppercase;
        }

        .empty-dash {
            color: #cbd5e1;
            font-weight: bold;
            font-size: 10px;
        }

        .watermark {
            position: absolute;
            top: 40%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.08;
            z-index: -10;
            width: 55%;
        }

        /* Salto de página para el detalle */
        .page-break {
            page-break-before: always;
        }

        /* Tabla de detalle de personal */
        .table-detalle {
            margin-top: 15px;
        }

        .table-detalle th {
            text-align: left;
            padding: 7px 10px;
        }

        .table-detalle td {
            text-align: left;
            padding: 7px 10px;
            font-size: 11px;
        }

        .badge-rol {
            color: #475569;
            font-size: 9px;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 4px;
            text-transform: uppercase;
        }

        .tag-horario {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            color: #334155;
            font-size: 9.5px;
            font-weight: 700;
            padding: 2px 5px;
            border-radius: 4px;
            margin-right: 3px;
            margin-bottom: 3px;
            display: inline-block;
        }
    </style>
</head>

<body>

    @if (file_exists(public_path('img/logo-universidad-watermark.png')))
        <img src="{{ public_path('img/logo-universidad-watermark.png') }}" class="watermark" alt="Logo de fondo">
    @endif

    {{-- PÁGINA 1: RESUMEN DE HORARIOS --}}
    <main>
        <div class="header-title">
            <h1>PROGRAMACIÓN DE HORARIOS DE CONSULTORIO</h1>
            <p>{{ mb_strtoupper($consultorio->nombre_csr) }}</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th class="time-col">HORA</th>
                    @foreach ($dias as $diaKey => $diaLabel)
                        <th>{{ mb_strtoupper($diaLabel) }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($bloquesJornadas as $jornada => $bloques)
                    <tr>
                        <td colspan="{{ count($dias) + 1 }}" class="jornada-header">
                            JORNADA {{ mb_strtoupper($jornada) }}
                        </td>
                    </tr>
                    @foreach ($bloques as $bloque)
                        @php
                            $inicioFormatted = \Carbon\Carbon::parse($bloque['inicio'])->format('g:i A');
                            $finFormatted = \Carbon\Carbon::parse($bloque['fin'])->format('g:i A');
                            $inicioKey = \Carbon\Carbon::parse($bloque['inicio'])->format('H:i');
                            $finKey = \Carbon\Carbon::parse($bloque['fin'])->format('H:i');
                        @endphp
                        <tr>
                            <td class="time-col">{{ $inicioFormatted }} - {{ $finFormatted }}</td>
                            @foreach ($dias as $diaKey => $diaLabel)
                                @php
                                    $key = "{$diaKey}|{$inicioKey}|{$finKey}";
                                    $asignaciones = $activosMap[$key] ?? [];
                                    $count = count($asignaciones);
                                @endphp
                                <td>
                                    @if ($count > 0)
                                        <div class="bloque-asignado">
                                            Espacio Abierto
                                        </div>
                                    @else
                                        <span class="empty-dash">-</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </main>

    {{-- PÁGINA 2: DETALLE DEL PERSONAL ASIGNADO --}}
    @if ($personalAsignado->isNotEmpty())
        <div class="page-break"></div>

        <main>
            <div class="header-title">
                <h1>DETALLE DE PERSONAL Y ASIGNACIONES</h1>
                <p>{{ mb_strtoupper($consultorio->nombre_csr) }}</p>
            </div>

            <table class="table-detalle">
                <thead>
                    <tr>
                        <th style="width: 35%;">PERSONAL / PROFESIONAL</th>
                        <th style="width: 20%;">ROL / CARGO</th>
                        <th style="width: 45%;">DÍAS Y HORARIOS ASIGNADOS</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($personalAsignado as $idRolUsuario => $horariosPersona)
                        @php
                            $primerHorario = $horariosPersona->first();
                            $nombrePersona = $primerHorario->nombre_personal ?? 'Sin nombre';
                            $rolPersona = $primerHorario->nombre_rol_asignado ?? 'Sin rol';
                        @endphp
                        <tr>
                            <td style="font-weight: 800; color: #0f172a;">
                                {{ $nombrePersona }}
                            </td>
                            <td>
                                <span class="badge-rol">{{ $rolPersona }}</span>
                            </td>
                            <td>
                                @foreach ($horariosPersona as $h)
                                    @php
                                        $diaLabel = ucfirst($dias[strtolower(trim($h->dia_hrc))] ?? $h->dia_hrc);
                                        $inicioStr = \Carbon\Carbon::parse($h->hora_inicio_hrc)->format('g:i A');
                                        $finStr = \Carbon\Carbon::parse($h->hora_fin_hrc)->format('g:i A');
                                    @endphp
                                    <div class="tag-horario">
                                        <strong>{{ $diaLabel }}:</strong> {{ $inicioStr }} - {{ $finStr }}
                                    </div>
                                @endforeach
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </main>
    @endif

</body>

</html>