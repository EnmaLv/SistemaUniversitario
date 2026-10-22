<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Constancia de Asistencia - {{ $paciente->persona->nombres_per }}</title>
    <style>
        @page { margin: 100px 60px 65px 60px; }
        header { position: fixed; top: -60px; left: 0px; right: 0px; height: 50px; text-align: center; }
        footer { position: fixed; bottom: -60px; left: 0px; right: 0px; height: 50px; text-align: center; }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #111827;
            margin: 0;
            padding: 10px 20px;
            font-size: 12px;
            line-height: 1.6;
        }
        .header {
            width: 100%;
            border-bottom: 3px solid #374151;
            padding-bottom: 15px;
            margin-bottom: 45px;
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
            font-size: 15px;
            font-weight: bold;
            color: #111827;
            text-transform: uppercase;
        }
        .doc-subtitle {
            font-size: 9px;
            color: #6b7280;
            text-transform: uppercase;
            margin-top: 5px;
        }
        .content-body {
            margin-top: 40px;
            font-size: 13px;
            color: #374151;
            text-align: justify;
        }
        .salutation {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 30px;
            color: #111827;
        }
        .text-paragraph {
            text-indent: 30px;
            margin-bottom: 25px;
        }
        .confidentiality-notice {
            background-color: #f9fafb;
            border-left: 3px solid #6b7280;
            padding: 12px 18px;
            font-size: 11px;
            color: #4b5563;
            margin-top: 30px;
            margin-bottom: 50px;
            font-style: italic;
        }
        .footer-sign {
            margin-top: 80px;
            text-align: center;
        }
        .signature-line {
            width: 250px;
            border-top: 1px solid #374151;
            margin: 0 auto 10px auto;
        }
        .signature-text {
            font-size: 12px;
            font-weight: bold;
            color: #111827;
            text-transform: uppercase;
        }
        .date-text {
            font-size: 10px;
            color: #6b7280;
            margin-top: 5px;
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
                    <div class="doc-title">Constancia de Asistencia</div>
                    <div class="doc-subtitle">Psic. {{ strtoupper($psicologo->persona->nombres_per . ' ' . $psicologo->persona->apellidos_per) }}</div>
                </td>
            </tr>
        </table>

        <div class="content-body">
            <div class="salutation">A QUIEN PUEDA INTERESAR:</div>

            <div class="text-paragraph">
                Por medio de la presente se hace constar formalmente que el/la ciudadano(a) 
                <strong>{{ $paciente->persona->nombres_per }} {{ $paciente->persona->apellidos_per }}</strong>, 
                titular de la cédula de identidad 
                <strong>{{ $paciente->persona->cedula_per ?? 'No registrada' }}</strong>, 
                asistió a la consulta psicológica en el día 
                <strong>{{ $cita->fecha_cps ? $cita->fecha_cps->translatedFormat('d \d\e F \d\e Y') : 'No registrada' }}</strong> 
                a las 
                <strong>{{ $cita->hora_cps ? \Carbon\Carbon::parse($cita->hora_cps)->format('g:i A') : 'No registrada' }}</strong>, 
                siendo atendido(a) en el área correspondiente por el/la profesional de la psicología 
                <strong>Psic. {{ $psicologo->persona->nombres_per }} {{ $psicologo->persona->apellidos_per }}</strong>.
            </div>

            <div class="text-paragraph">
                Se expide esta constancia a petición de la parte interesada, en la Ciudad de Acarigua, estado Portuguesa, a partir de los 
                <strong>{{ date('d') }} días del mes de {{ \Carbon\Carbon::now()->translatedFormat('F') }} de {{ date('Y') }}</strong>.
            </div>

            <div class="confidentiality-notice">
                <strong>Nota de Confidencialidad:</strong> De conformidad con el artículo 15 del Código de Ética Profesional del Psicólogo, 
                la información clínica compartida durante la sesión es de carácter estrictamente confidencial. Por lo tanto, esta constancia 
                se limita a certificar únicamente la asistencia de la persona a su cita, sin revelar diagnósticos, tratamientos o detalles clínicos.
            </div>
        </div>

        <div class="footer-sign">
            <div class="signature-line"></div>
            <div class="signature-text">Psic. {{ $psicologo->persona->nombres_per }} {{ $psicologo->persona->nombres_per }}</div>
            <div class="date-text">Firma y Sello del Psicólogo</div>
        </div>
    </main>
</body>
</html>
