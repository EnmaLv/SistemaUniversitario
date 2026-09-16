<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Récipe Médico</title>
    <style>
        @page {
            margin: 25px 35px 65px 35px;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
            color: #1a1a1a;
        }

        /* ===================== HEADER ===================== */
        .institutional-header {
            text-align: center;
            margin-bottom: 5px;
            padding-top: 5px;
        }

        .header-image {
            max-height: 110px;
            object-fit: contain;
        }

        /* ===================== TÍTULO DEL DOCUMENTO ===================== */
        .document-header {
            display: table;
            width: 100%;
            margin-bottom: 16px;
            margin-top: 20px
        }

        .document-header-left {
            display: table-cell;
            vertical-align: bottom;
            text-align: left;
        }

        .document-header-left h3 {
            font-size: 16px;
            margin: 0;
            font-weight: bold;
            color: #111;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .document-header-left p {
            margin: 2px 0 0 0;
            font-size: 9.5px;
            color: #666;
            letter-spacing: 0.3px;
        }

        .document-header-right {
            display: table-cell;
            vertical-align: bottom;
            text-align: right;
        }

        .folio-box {
            display: inline-block;
            padding: 5px 12px;
            font-size: 10.5px;
            font-weight: bold;
        }

        .folio-box span {
            display: block;
            font-size: 7.5px;
            color: #777;
            font-weight: normal;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 2px;
        }

        /* ===================== INFO PACIENTE / CONSULTA ===================== */
        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 24px;
        }

        .info-row {
            display: table-row;
        }

        .info-cell {
            display: table-cell;
            padding: 6px 16px;
            font-size: 11px;
            width: 50%;
            vertical-align: top;
            line-height: 1.7;
            color: #222;
        }

        .info-cell strong.label {
            color: #111;
            display: block;
            margin-bottom: 6px;
            font-size: 9.5px;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 4px;
        }

        .info-cell .field-name {
            color: #777;
        }

        .info-cell .field-value {
            color: #111;
            font-weight: bold;
        }

        /* ===================== BLOQUE Rx ===================== */
        .rx-header {
            display: table;
            width: 100%;
            margin-bottom: 4px;
        }

        .rx-header-right {
            display: table-cell;
            vertical-align: bottom;
            border-bottom: 0.5px solid #5a5a5a;
            padding-bottom: 6px;
        }

        .rx-header-right h2 {
            margin: 0;
            font-size: 12px;
            color: #2f2f2f;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* ===================== MEDICAMENTOS ===================== */
        .medication-list {
            width: 100%;
            margin: 16px 0 120px 0;
        }

        .medication-item {
            display: table;
            width: 100%;
            margin-bottom: 14px;
            padding-bottom: 12px;
            border-bottom: 1px dashed #999;
        }

        .med-body {
            display: table-cell;
            vertical-align: top;
        }

        .med-name {
            font-weight: bold;
            font-size: 12px;
            margin-bottom: 4px;
            color: #111;
        }

        .med-name .med-qty {
            font-weight: normal;
            font-size: 11px;
        }

        .med-instructions {
            color: #333;
            line-height: 1.5;
            font-size: 11px;
        }

        .med-instructions strong {
            color: #111;
        }

        .med-instructions .med-note {
            display: block;
            margin-top: 3px;
            color: #444;
            font-style: italic;
        }

        .empty-rx {
            color: #777;
            font-style: italic;
            text-align: center;
            margin-top: 25px;
            font-size: 11px;
            padding: 20px;
            border: 1px dashed #999;
        }

        /* ===================== FIRMA  ===================== */
        .signature-area {
            position: absolute;
            bottom: 20px;
            left: 0;
            right: 0;
            width: 100%;
            text-align: center;
            page-break-inside: avoid;
        }

        .signature-line {
            width: 250px;
            border-top: 1px solid #111;
            margin: 0 auto 6px auto;
        }

        .signature-text {
            font-size: 9px;
            color: #222;
            line-height: 1.5;
        }

        .signature-text strong {
            color: #111;
            font-size: 10px;
        }


        /* ===================== FOOTER ===================== */
        .footer {
            position: fixed;
            bottom: -40px;
            left: 0px;
            right: 0px;
            height: 30px;
            text-align: center;
            font-size: 8px;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 8px;
        }

        .footer p {
            margin: 2px 0;
        }

        .footer .footer-brand {
            color: #111;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <!-- Header Institucional -->
    <div class="institutional-header">
        <img src="{{ public_path('img/CintilloUPTP.png') }}" class="header-image" width="100%"
            alt="Encabezado Institucional">
    </div>

    <!-- Título del Documento -->
    <div class="document-header">
        <div class="document-header-left">
            <h3>Récipe Médico</h3>
            <p>Indicaciones y tratamiento farmacológico</p>
        </div>
        <div class="document-header-right">
            <div class="folio-box">
                <span>Consulta N°</span>
                #{{ str_pad($consulta->id, 5, '0', STR_PAD_LEFT) }}
            </div>
        </div>
    </div>

    <!-- Información de Paciente y Consulta -->
    <div class="info-grid">
        <div class="info-row">
            <div class="info-cell">
                <strong class="label">Datos del paciente</strong>
                <span class="field-name">Nombre:</span>
                <span class="field-value">{{ $consulta->paciente->nombre_persona }}
                    {{ $consulta->paciente->apellido_persona }}</span><br>
                <span class="field-name">Cédula/ID:</span>
                <span class="field-value">{{ $consulta->paciente->cedula_persona ?? 'S/N' }}</span><br>
                <span class="field-name">Válido hasta:</span>
                <span class="field-value">
                    @if (optional($consulta->receta)->vigencia)
                        {{ \Carbon\Carbon::parse($consulta->receta->vigencia)->format('d/m/Y') }}
                    @else
                        N/A
                    @endif
                </span>
            </div>
            <div class="info-cell">
                <strong class="label">Datos de la consulta</strong>
                <span class="field-name">Médico:</span>
                <span class="field-value">Dr(a). {{ $consulta->medico->nombre_persona }}
                    {{ $consulta->medico->apellido_persona }}</span><br>
                <span class="field-name">Consultorio:</span>
                <span class="field-value">{{ optional($consulta->consultorio)->nombre ?? 'Centro Médico' }}</span><br>
                <span class="field-name">Fecha:</span>
                <span class="field-value">{{ $consulta->fecha->format('d/m/Y') }}</span>
            </div>
        </div>
    </div>

    <div class="rx-header">
        <div class="rx-header-right">
            <h2>Prescripción</h2>
        </div>
    </div>

    <!-- Listado de Medicamentos -->
    <div class="medication-list">
        @if ($consulta->receta && $consulta->receta->detalles->count() > 0)
            @foreach ($consulta->receta->detalles as $detalle)
                <div class="medication-item">
                    <div class="med-body">
                        <div class="med-name">
                            &mdash; {{ optional($detalle->producto)->nombre ?? 'Producto no especificado' }}
                            <span class="med-qty">- {{ floatval($detalle->cantidad) }}
                                {{ optional($detalle->unidad)->nombre ?? 'Und' }}</span>
                        </div>
                        <div class="med-instructions">
                            <strong>Tomar / Aplicar:</strong> {{ $detalle->frecuencia }}
                            @if ($detalle->observaciones)
                                <span class="med-note"><strong>Nota:</strong> {{ $detalle->observaciones }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <p class="empty-rx">
                No hay medicamentos recetados registrados en esta consulta.
            </p>
        @endif
    </div>

    <!-- Firma del Médico -->
    <div class="signature-area">
        <div class="signature-line"></div>
        <div class="signature-text">
            <strong>Dr(a). {{ $consulta->medico->nombre_persona }}
                {{ $consulta->medico->apellido_persona }}</strong><br>
            Firma y sello médico
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p><span class="footer-brand">Servicio Médico Universitario</span> &middot; UPTP Juan Jesús Montilla Sistema de
            Bienestar Estudiantil | Documento generado automáticamente</p>
    </div>

    <!-- Script de Paginación -->
    <script type="text/php">
        if (isset($pdf)) {
            $pdf->page_script('
                $text = "Página " . $PAGE_NUM . " de " . $PAGE_COUNT;
                $font = null;
                $size = 9;
                $color = array(0.4, 0.4, 0.4);
                $word_space = 0.0;
                $char_space = 0.0;
                $angle = 0.0;
 
                $textWidth = $fontMetrics->getTextWidth($text, $font, $size);
 
                $x = ($pdf->get_width() - $textWidth) / 2;
                $y = $pdf->get_height() - 25; 
 
                $pdf->text($x, $y, $text, $font, $size, $color, $word_space, $char_space, $angle);
            ');
        }
    </script>
</body>

</html>
