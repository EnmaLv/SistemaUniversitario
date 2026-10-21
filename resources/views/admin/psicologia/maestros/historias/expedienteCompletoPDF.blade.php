<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Expediente Completo - {{ $paciente->persona->nombres_per }}</title>
    <style>
        @page { margin: 80px 18mm 80px 18mm; }
        header { position: fixed; top: -60px; left: 0px; right: 0px; height: 50px; text-align: center; }
        footer { position: fixed; bottom: -60px; left: 0px; right: 0px; height: 50px; text-align: center; }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #111827;
            margin: 0;
            padding: 0;
            font-size: 11px;
        }

        .membrete {
            width: 100%;
            border-bottom: 3px solid #374151;
            padding-bottom: 12px;
            margin-bottom: 22px;
        }
        .membrete td { vertical-align: middle; }
        .logo-text {
            font-size: 20px;
            font-weight: bold;
            color: #111827;
            letter-spacing: 1px;
        }
        .logo-sub {
            font-size: 9px;
            color: #6b7280;
            text-transform: uppercase;
            margin-top: 2px;
        }
        .doc-info { text-align: right; }
        .doc-title {
            font-size: 15px;
            font-weight: bold;
            color: #111827;
            text-transform: uppercase;
        }
        .doc-subtitle {
            font-size: 9px;
            color: #6b7280;
            text-transform: uppercase;
            margin-top: 4px;
        }

        .patient-card {
            border: none;
            border-radius: 10px;
            padding: 16px 20px;
            margin-bottom: 22px;
            background-color: transparent;
        }
        .patient-name {
            font-size: 17px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 8px;
            color: #111827;
        }
        .patient-meta {
            font-size: 10px;
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
            font-size: 13px;
            font-weight: bold;
            color: #111827;
            margin-top: 22px;
            margin-bottom: 10px;
            text-transform: uppercase;
            border-bottom: 2px solid #9ca3af;
            padding-bottom: 4px;
        }
        .section-title.main {
            font-size: 16px;
            text-align: center;
            border-bottom: 3px solid #374151;
            margin-bottom: 16px;
        }

        .info-grid {
            width: 100%;
            margin-bottom: 18px;
            border-collapse: collapse;
        }
        .info-grid td {
            width: 50%;
            vertical-align: top;
            padding-right: 12px;
            padding-bottom: 8px;
        }
        .info-label {
            font-size: 9px;
            font-weight: bold;
            color: #374151;
            text-transform: uppercase;
            margin-bottom: 3px;
        }
        .info-value {
            font-size: 10px;
            color: #111827;
            font-weight: normal;
        }

        .segment-title {
            font-size: 10px;
            font-weight: bold;
            color: #374151;
            text-transform: uppercase;
            margin-bottom: 4px;
            margin-top: 12px;
        }
        .segment-content {
            font-size: 10px;
            color: #4b5563;
            line-height: 1.5;
            background-color: #ffffff;
            border: none;
            border-radius: 6px;
            padding: 8px 10px;
            margin-bottom: 10px;
        }
        .disease-tag {
            background-color: transparent;
            color: #374151;
            border-radius: 4px;
            padding: 2px 7px;
            font-size: 9px;
            font-weight: bold;
            white-space: nowrap;
            line-height: 1.2;
            display: inline-block;
            margin: 2px 2px 2px 0;
        }
        .disease-list {
            display: block;
            margin-top: 6px;
        }

        .timeline-item {
            margin-bottom: 16px;
            page-break-inside: avoid;
        }
        .timeline-header {
            width: 100%;
            margin-bottom: 5px;
        }
        .timeline-date {
            display: inline-block;
            background-color: transparent;
            color: #374151;
            padding: 3px 10px;
            border-radius: 6px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .timeline-title {
            font-size: 11px;
            font-weight: bold;
            color: #111827;
            margin-top: 4px;
            margin-bottom: 4px;
        }
        .timeline-body {
            border-left: 3px solid #9ca3af;
            padding-left: 10px;
            font-size: 10px;
            color: #374151;
            line-height: 1.6;
        }

        .footer {
            margin-top: 45px;
            text-align: center;
        }
        .signature-line {
            width: 250px;
            border-top: 1px solid #374151;
            margin: 0 auto 8px auto;
        }
        .signature-text {
            font-size: 10px;
            font-weight: bold;
            color: #111827;
        }
        .date-text {
            font-size: 9px;
            color: #6b7280;
            margin-top: 4px;
        }

        .page-break { page-break-after: always; }
        .divider { border: none; border-top: 1px solid #e5e7eb; margin: 18px 0; }
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
    <table class="membrete">
        <tr>
            <td style="width: 50%;">
                <div class="logo-text">Psico-Guía UPTP</div>
                <div class="logo-sub">Sistema de Gestión Psicológica</div>
            </td>
            <td style="width: 50%;" class="doc-info">
                <div class="doc-title">Expediente Clínico General</div>
                <div class="doc-subtitle">DR. {{ strtoupper(Auth::user()->persona->nombres_per . ' ' . Auth::user()->persona->apellidos_per) }} | PSICÓLOGO</div>
                <div class="doc-subtitle">Fecha: {{ date('d/m/Y') }}</div>
            </td>
        </tr>
    </table>

    <div class="patient-card">
        <div class="patient-name">{{ $paciente->persona->nombres_per }} {{ $paciente->persona->apellidos_per }}</div>
        <div class="patient-meta">
            <span>Sesiones Totales: {{ $stats['realizadas'] }}</span>
            <span>N° Expediente: {{ $historia->id }}</span>
        </div>
    </div>

    <div class="section-title">Información Personal</div>
    <table class="info-grid">
        <tr>
            <td>
                <div class="info-label">Cédula: {{ $paciente->persona->cedula_per ?? 'No registrada' }}</div>
            </td>
            <td>
                <div class="info-label">Teléfono: {{ $paciente->persona->telefono_per ?? 'No registrado' }}</div>
            </td>
        </tr>
        <tr>
            <td>
                @php
                    $edad = 'No registrada';
                    if(!empty($paciente->fecha_nacimiento)) {
                        $edad = \Carbon\Carbon::parse($paciente->persona->fecha_nacimiento_per)->age . ' años';
                    }
                @endphp
                <div class="info-label">Edad y Nacimiento: {{ $edad }} ({{ !empty($paciente->persona->fecha_nacimiento_per) ? \Carbon\Carbon::parse($paciente->persona->fecha_nacimiento_per)->format('d/m/Y') : 'N/A' }})</div>
            </td>
            <td>
                <div class="info-label">Perfil Académico / PNF: {{ $paciente->perfil_academico ?? 'No registrado' }}
                    @if(($paciente->perfil_academico ?? '') === 'Estudiante')
                        — {{ $paciente->pnf ?? 'N/A' }} (Sem: {{ $paciente->semestre ?? 'N/A' }})
                    @endif
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="info-label">Género / Estado Civil: {{ $paciente->persona->genero_per ?? 'N/A' }} / {{ $paciente->estado_civil ?? 'N/A' }}</div>
            </td>
            <td>
                <div class="info-label">Discapacidad: {{ ($paciente->discapacidad ?? 'No') == 'Si' ? 'Sí — ' . ($paciente->tipo_discapacidad ?? '') : 'Ninguna' }}</div>
            </td>
        </tr>
    </table>

    @foreach($seccionesPersonalizadas as $seccion)
        <div class="section-title">{{ $seccion->titulo }}</div>
        @if($seccion->descripcion_general)
            <div style="font-size: 9px; color: #6b7280; margin-bottom: 8px;">{{ $seccion->descripcion_general }}</div>
        @endif
        @foreach($seccion->segmentos as $segmento)
            <div class="segment-title">{{ $segmento->titulo }}</div>
            <div class="segment-content">
                {!! nl2br(e(strip_tags($segmento->contenido ?? 'Sin información registrada.'))) !!}
                @php
                    $contextoSeg = 'seg_' . $segmento->id;
                    $enfermedadesSeg = $enfermedadesVinculadas[$contextoSeg] ?? [];
                @endphp
                @if(count($enfermedadesSeg) > 0)
                    <div style="margin-top: 8px; padding-top: 8px; border-top: 1px dashed #e5e7eb;">
                        <strong>Trastornos o Condiciones Presentes:</strong>
                        <div class="disease-list">
                            @foreach($enfermedadesSeg as $enf)
                                <span class="disease-tag">{{ $enf->nombre_efm }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @endforeach
    @endforeach

    <div class="page-break"></div>
    <table class="membrete">
        <tr>
            <td style="width: 50%;">
                <div class="logo-text">Psico-Guía UPTP</div>
                <div class="logo-sub">Sistema de Gestión Psicológica</div>
            </td>
            <td style="width: 50%;" class="doc-info">
                <div class="doc-title">Notas de Evolución</div>
                <div class="doc-subtitle">DR. {{ strtoupper(Auth::user()->persona->nombres_per . ' ' . Auth::user()->persona->apellidos_per) }} | PSICÓLOGO</div>
                <div class="doc-subtitle">Fecha: {{ date('d/m/Y') }}</div>
            </td>
        </tr>
    </table>

    <div class="section-title">Cronología de Sesiones</div>

    @if($citasSeleccionadas->isEmpty())
        <p style="color: #6b7280; font-style: italic;">No hay sesiones registradas para este paciente.</p>
    @else
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
                <div class="timeline-title">Sesión No. {{ $sesionNum }} — {{ $motivo }}</div>
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
                            @if(!empty($notasData['avance_detalle'])) — {{ $notasData['avance_detalle'] }}@endif<br>
                        @endif
                        @if(!empty($notasData['diagnosticos']))
                            <strong>Diagnósticos (CIE-10):</strong><br>
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
    @endif

    <div class="footer">
        <div class="signature-line"></div>
        <div class="signature-text">FIRMA Y SELLO DEL PSICÓLOGO</div>
        <div class="date-text">Fecha de Emisión: {{ date('d/m/Y') }}</div>
    </div>
    </main>

</body>
</html>
