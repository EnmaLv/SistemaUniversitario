<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Expediente Clínico - {{ $paciente->persona->nombres_per }}</title>
    <style>
        @page { margin: 80px 18mm 80px 18mm; }
        header { position: fixed; top: -60px; left: 0px; right: 0px; height: 50px; text-align: center; }
        footer { position: fixed; bottom: -60px; left: 0px; right: 0px; height: 50px; text-align: center; }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #111827;
            margin: 0;
            padding: 10px 20px;
            font-size: 12px;
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

        .segment-title {
            font-size: 11px;
            font-weight: bold;
            color: #374151;
            text-transform: uppercase;
            margin-bottom: 5px;
            margin-top: 15px;
        }
        .segment-content {
            font-size: 11px;
            color: #374151;
            line-height: 1.5;
            background-color: #ffffff;
            border: none;
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 15px;
        }
        .disease-list {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 8px;
        }

        .disease-tag {
            background-color: transparent;
            color: #374151;
            border-radius: 4px;
            padding: 3px 8px;
            font-size: 9px;
            font-weight: bold;
            white-space: nowrap;
            line-height: 1.2;
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
                <span class="logo-text">Psico-Guía</span>
            </td>
            <td style="width: 50%;" class="doc-info">
                <div class="doc-title">EXPEDIENTE CLÍNICO GENERAL</div>
                <div class="doc-subtitle">{{ strtoupper(Auth::user()->persona->nombres_per . ' ' . Auth::user()->persona->apellidos_per) }} | PSICÓLOGO</div>
            </td>
        </tr>
    </table>

    <div class="patient-card">
        <div class="patient-name">{{ $paciente->persona->nombres_per }} {{ $paciente->persona->apellidos_per }}</div>
        <div class="patient-meta">
            <span>Sesiones Totales: {{ $stats['realizadas'] }}</span>
            <span>N° Expediente: {{ $historia->id_hcl }}</span>
        </div>
    </div>

    <div class="section-title">INFORMACIÓN PERSONAL</div>
    <table class="info-grid">
        <tr>
            <td>
                <div class="info-label">Cédula: {{ $paciente->persona->cedula_per ?? 'No registrada' }} </div>
            </td>
            <td>
                <div class="info-label">Teléfono: {{ $paciente->persona->telefono_per ?? 'No registrado' }}</div>
                
            </td>
        </tr>
        <tr>
            <td>
                @php
                    $edad = 'No registrada';
                    if(!empty($paciente->persona->fecha_nacimiento_per)) {
                        $edad = \Carbon\Carbon::parse($paciente->persona->fecha_nacimiento_per)->age . ' años';
                    }
                @endphp
                <div class="info-label">Edad y Nacimiento: {{ $edad }} ({{ !empty($paciente->persona->fecha_nacimiento_per) ? \Carbon\Carbon::parse($paciente->persona->fecha_nacimiento_per)->format('d/m/Y') : 'N/A' }})</div>
            </td>
            <td>
                <div class="info-label">Perfil Académico: {{ $paciente->perfil_academico ?? 'No registrado' }}
                    @if(($paciente->perfil_academico ?? '') === 'Estudiante')
                        - {{ $paciente->pnf ?? 'N/A' }} (Semestre: {{ $paciente->persona->semestre_per ?? 'N/A' }})
                    @endif
                </div>
            </td>
        </tr>
    </table>

    @foreach($seccionesPersonalizadas as $seccion)
        <div class="section-title">{{ $seccion->titulo_hsp }}</div>
        @if($seccion->descripcion_general_hsp)
            <div style="font-size: 10px; color: #6b7280; margin-bottom: 10px;">{{ $seccion->descripcion_general_hsp }}</div>
        @endif
        
        @foreach($seccion->segmentos as $segmento)
            <div class="segment-title">{{ $segmento->titulo_hsm }}</div>
            <div class="segment-content">
                {!! nl2br(e(strip_tags($segmento->contenido_hsm ?? 'Sin información registrada.'))) !!}
                
                @php
                    $contextoSeg = 'seg_' . $segmento->id_hsm;
                    $enfermedadesSeg = $enfermedadesVinculadas[$contextoSeg] ?? [];
                @endphp
                @if(count($enfermedadesSeg) > 0)
                    <div style="margin-top: 10px; padding-top: 10px; border-top: 1px dashed #e5e7eb;">
                        <strong>Trastornos o Condiciones Presentes:</strong><br>
                        @foreach($enfermedadesSeg as $enf)
                            <span class="disease-tag">{{ $enf->nombre_efm }}</span>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach
    @endforeach

    <div class="footer">
        <div class="signature-line"></div>
        <div class="signature-text">FIRMA Y SELLO DEL PSICÓLOGO</div>
        <div class="date-text">Fecha de Emisión: {{ date('d/m/Y') }}</div>
    </div>

    </main>
</body>
</html>
