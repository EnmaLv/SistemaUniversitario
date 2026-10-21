<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Informe de Evolución - {{ $paciente->persona->nombres_per }}</title>
    <style>
        @page { margin: 100px 60px 65px 60px;  }
        header { position: fixed; top: -60px; left: 0px; right: 0px; height: 50px; text-align: center; }
        footer { position: fixed; bottom: -60px; left: 0px; right: 0px; height: 50px; text-align: center; }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #111827;
            margin: 0;
            padding: 0;
            font-size: 12px;
        }
        .disease-list {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;           /* espacio entre etiquetas horizontal y vertical */
            margin-top: 8px;
        }
        .header {
            width: 100%;
            border-bottom: 3px solid #374151;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .header td {
            vertical-align: middle;
        }
        .logo-text {
            font-size: 22px;
            font-weight: bold;
            color: #111827;
        }
        .doc-info {
            text-align: right;
        }
        .doc-title {
            font-size: 16px;
            font-weight: bold;
            color: #111827;
        }
        .doc-subtitle {
            font-size: 10px;
            color: #6b7280;
            text-transform: uppercase;
            margin-top: 5px;
        }

        .patient-card {
            border: none;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
            background-color: transparent;
        }
        .patient-name {
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 10px;
            color: #111827;
        }
        .patient-meta {
            font-size: 11px;
            color: #374151;
            font-weight: bold;
        }
        .patient-meta span {
            background-color: transparent;
            color: #374151;
            padding: 4px 12px;
            border-radius: 10px;
            margin-right: 8px;
            display: inline-block;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #111827;
            margin-top: 25px;
            margin-bottom: 12px;
            text-transform: uppercase;
            border-bottom: 2px solid #9ca3af;
            padding-bottom: 5px;
        }

        .info-grid {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        .info-grid td {
            width: 50%;
            vertical-align: top;
            padding-right: 15px;
            padding-bottom: 10px;
        }
        .info-label {
            font-size: 9px;
            font-weight: bold;
            color: #111827;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .info-value {
            border: none;
            border-radius: 8px;
            padding: 8px 12px;
            background-color: #ffffff;
            font-size: 11px;
            color: #374151;
            min-height: 20px;
        }

        /* Timeline */
        .timeline-item {
            margin-bottom: 18px;
            page-break-inside: avoid;
        }
        .timeline-header {
            width: 100%;
            margin-bottom: 8px;
        }
        .timeline-date {
            display: inline-block;
            background-color: transparent;
            color: #374151;
            padding: 4px 12px;
            border-radius: 6px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .timeline-title {
            font-size: 12px;
            font-weight: bold;
            color: #111827;
            margin-top: 5px;
            margin-bottom: 5px;
        }
        .timeline-body {
            border-left: 3px solid #9ca3af;
            padding-left: 12px;
            font-size: 11px;
            color: #374151;
            line-height: 1.6;
        }

        /* Summary boxes */
        .summary-box {
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 10px;
            background-color: #ffffff;
        }
        .summary-label {
            font-size: 10px;
            font-weight: bold;
            color: #374151;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .summary-content {
            font-size: 11px;
            color: #374151;
            line-height: 1.5;
        }

        .footer {
            margin-top: 50px;
            text-align: center;
        }
        .signature-line {
            width: 250px;
            border-top: 1px solid #000;
            margin: 0 auto 10px auto;
        }
        .signature-text {
            font-size: 11px;
            font-weight: bold;
            color: #111827;
        }
        .date-text {
            font-size: 10px;
            color: #6b7280;
            margin-top: 5px;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <header>
        <img src="{{ public_path('img/encabezado.png') }}" style="width: 100%; max-height: 50px; object-fit: contain;" alt="Encabezado" onerror="this.style.display='none'">
    </header>
    <footer>
        <img src="{{ public_path('img/pie.png') }}" style="width: 100%; max-height: 50px; object-fit: contain;" alt="Pie de Página" onerror="this.style.display='none'">
    </footer>

    <main>
        <table class="header">
        <tr>
            <td style="width: 50%;">
                <span class="logo-text">Psico-Guia</span>
            </td>
            <td style="width: 50%;" class="doc-info">
                <div class="doc-title">INFORME CLINICO DE EVOLUCION</div>
                <div class="doc-subtitle">DR. {{ strtoupper(Auth::user()->persona->nombres_per . ' ' . Auth::user()->persona->apellidos_per) }} | PSICOLOGO</div>
            </td>
        </tr>
    </table>

    <div class="patient-card">
        <div class="patient-name">{{ $paciente->persona->nombres_per }} {{ $paciente->persona->apellidos_per }}</div>
        <div class="patient-meta">
            <span>Sesiones: {{ $stats['realizadas'] }}</span>
            <span>N Expediente: {{ $historia->id }}</span>
        </div>
    </div>



    <div class="section-title">NOTAS DE SESION - CRONOLOGIA</div>

    @php $sesionNum = 0; @endphp
    @foreach($citasSeleccionadas as $cita)
        @php
            $sesionNum++;
            $fechaStr = $cita->fecha ? $cita->fecha->translatedFormat('d M Y') : 'Sin fecha';
            $motivo = $cita->motivo ?? 'Consulta General';
            $notasData = null;
            if ($cita->notas) {
                $notasData = json_decode($cita->notas, true);
                if (json_last_error() !== JSON_ERROR_NONE) $notasData = null;
            }
        @endphp
        <div class="timeline-item">
            <div class="timeline-header">
                <span class="timeline-date">{{ $fechaStr }}</span>
            </div>
            <div class="timeline-title">Sesion No. {{ $sesionNum }} - {{ $motivo }} ({{ $fechaStr }})</div>
            <div class="timeline-body">
                @if($notasData && is_array($notasData))
                    @php
                        $camposDinamicos = \App\Models\salud\CitaNotaEvolucion::obtenerPorCita($cita->id);
                    @endphp
                    
                    @foreach($camposDinamicos as $campo)
                        @if(!empty(trim($campo->contenido)))
                            <strong>{{ $campo->titulo }}:</strong> {{ trim($campo->contenido) }}<br>
                        @endif
                    @endforeach

                    @if(!empty($notasData['avance_estado']))
                        <strong>Avance:</strong> {{ ucfirst(str_replace('_', ' ', $notasData['avance_estado'])) }}
                        @if(!empty($notasData['avance_detalle']))
                            - {{ $notasData['avance_detalle'] }}
                        @endif
                        <br>
                    @endif
                    @if(!empty($notasData['diagnosticos']))
                        <strong>Diagnosticos (CIE-10):</strong><br>
                        @foreach($notasData['diagnosticos'] as $diag)
                            &nbsp;&nbsp;- {{ $diag['codigo'] ?? '' }} {{ $diag['nombre'] ?? '' }}<br>
                        @endforeach
                    @endif
                @else
                    <em>{{ $cita->notas_limpias ?? 'Sin notas registradas.' }}</em>
                @endif
            </div>
        </div>
    @endforeach



    <div class="footer">
        <div class="signature-line"></div>
        <div class="signature-text">FIRMA Y SELLO DEL PSICOLOGO</div>
        <div class="date-text">Fecha de Emision: {{ date('d/m/Y') }}</div>
    </div>
    </div>
    </main>
</body>
</html>
