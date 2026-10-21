<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte Registro Diario</title>
    <style>
        @page {
            margin: 30px 35px;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
            color: #333333;
        }

        /* Header Institucional con logos */
        .institutional-header {
            background-color: #ffffff;
            padding: 15px 0;
            border-bottom: 4px solid #d41002;
            margin-bottom: 0;
        }

        .header-content {
            display: table;
            width: 100%;
        }

        .header-left,
        .header-right {
            display: table-cell;
            vertical-align: middle;
            width: 15%;
        }

        .header-center {
            display: table-cell;
            vertical-align: middle;
            text-align: center;
            width: 70%;
        }

        .header-left img,
        .header-right img {
            max-height: 70px;
            width: auto;
        }

        .header-center h1 {
            font-size: 16px;
            color: #333;
            margin: 0 0 5px 0;
            font-weight: bold;
            text-transform: uppercase;
        }

        .header-center h2 {
            font-size: 14px;
            color: #d41002;
            margin: 0;
            font-weight: bold;
        }

        .header-center p {
            font-size: 11px;
            color: #666;
            margin: 5px 0 0 0;
        }

        /* Línea decorativa triple */
        .decorative-lines {
            height: 8px;
            background: linear-gradient(to bottom,
                    #d41002 0%, #d41002 33%,
                    #333 33%, #333 66%,
                    #ffc107 66%, #ffc107 100%);
            margin-bottom: 10px;
        }

        /* Título del Documento */
        .document-header {
            background-color: #f5f5f5;
            padding: 15px 20px;
            text-align: center;
            border-bottom: 2px solid #d41002;
            margin-bottom: 15px;
        }

        .document-header h3 {
            font-size: 18px;
            margin: 0 0 5px 0;
            font-weight: bold;
            color: #d41002;
            text-transform: uppercase;
        }

        .document-header p {
            margin: 0;
            font-size: 12px;
            color: #666;
        }

        /* Sección de Filtros/Metadata */
        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
            background-color: #fafafa;
            border: 1px solid #e0e0e0;
        }

        .info-row {
            display: table-row;
        }

        .info-cell {
            display: table-cell;
            padding: 12px 15px;
            font-size: 11px;
            width: 50%;
            vertical-align: top;
            line-height: 1.6;
        }

        .info-cell strong {
            color: #d41002;
            display: block;
            margin-bottom: 5px;
        }

        .info-cell:last-child {
            text-align: right;
        }

        /* Tabla de Datos */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
            font-size: 11px;
        }

        .data-table th {
            background-color: #e8e8e8;
            color: #333;
            padding: 10px 8px;
            font-size: 10px;
            text-transform: uppercase;
            text-align: left;
            font-weight: bold;
            border: 1px solid #ccc;
        }

        .data-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .data-table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .data-table tbody tr:hover {
            background-color: #fff5f5;
        }

        .data-table td.center {
            text-align: center;
        }

        /* Empty State */
        .empty-state {
            border: 2px dashed #d41002;
            padding: 30px;
            text-align: center;
            color: #d41002;
            font-weight: 600;
            margin: 20px 0;
            background-color: #fff5f5;
            border-radius: 4px;
        }

        /* Resumen por PNF */
        .resumen-section {
            margin-top: 30px;
            padding: 20px;
            background-color: #f9f9f9;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
        }

        .resumen-section h3 {
            font-size: 16px;
            color: #333;
            margin: 0 0 15px 0;
            padding-bottom: 10px;
            border-bottom: 2px solid #d41002;
            font-weight: bold;
        }

        .item-pnf {
            padding: 12px 0;
            border-bottom: 1px solid #e5e5e5;
            overflow: hidden;
        }

        .item-pnf:last-child {
            border-bottom: none;
        }

        .item-pnf-info {
            float: left;
            width: 75%;
        }

        .item-pnf-nombre {
            font-size: 13px;
            color: #333;
            font-weight: bold;
            margin-bottom: 3px;
        }

        .item-pnf-subtitle {
            font-size: 10px;
            color: #888;
        }

        .item-pnf-cantidad {
            float: right;
            width: 25%;
            text-align: right;
            font-size: 20px;
            font-weight: bold;
            color: #d41002;
            line-height: 1;
        }

        .clearfix {
            clear: both;
        }

        /* Footer */
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #d41002;
            text-align: center;
            font-size: 10px;
            color: #666;
        }

        .footer p {
            margin: 3px 0;
        }

        .footer strong {
            color: #333;
        }

        /* Para impresión */
        @media print {
            .institutional-header {
                page-break-after: avoid;
            }

            .data-table {
                page-break-inside: auto;
            }

            .data-table tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }

            .resumen-section {
                page-break-inside: avoid;
            }
        }
    </style>
</head>

<body>
    @php
        $fechaGeneracion = \Carbon\Carbon::now()->format('d/m/Y H:i');
    @endphp

    <div class="institutional-header">
        <div class="header-content">
            <div class="header-left">
                @php
                    $logoPath = public_path('img/ministerioLogo.webp');
                    if (file_exists($logoPath)) {
                        $logoData = base64_encode(file_get_contents($logoPath));
                        $logoSrc = 'data:image/webp;base64,' . $logoData;
                    } else {
                        $logoSrc = '';
                    }
                @endphp
                @if ($logoSrc)
                    <img src="{{ $logoSrc }}" alt="Logo Ministerio">
                @endif
            </div>
            <div class="header-center">
                <h1>Universidad Politécnica Territorial del Estado Portuguesa</h1>
                <h2>Juan Jesús Montilla</h2>
                <p>Comedor Universitario</p>
            </div>
            <div class="header-right">
                @php
                    $logoPath2 = public_path('img/Logo.webp');
                    if (file_exists($logoPath2)) {
                        $logoData2 = base64_encode(file_get_contents($logoPath2));
                        $logoSrc2 = 'data:image/webp;base64,' . $logoData2;
                    } else {
                        $logoSrc2 = '';
                    }
                @endphp
                @if ($logoSrc2)
                    <img src="{{ $logoSrc2 }}" alt="Logo Universidad">
                @endif
            </div>
        </div>
    </div>

    <div class="decorative-lines"></div>

    <!-- Título del Documento -->
    <div class="document-header">
        <h3>Registro Diario del Comedor</h3>
        <p>Reporte de estudiantes registrados</p>
    </div>

    <!-- Información de Filtros -->
    <div class="info-grid">
        <div class="info-row">
            <div class="info-cell">
                <strong>RANGO SOLICITADO:</strong>
                Desde: {{ $fecha_desde ?? '—' }}<br>
                Hasta: {{ $fecha_hasta ?? '—' }}
            </div>
            <div class="info-cell">
                <strong>GENERADO EL:</strong> {{ $fechaGeneracion }}<br>
                <strong>TOTAL DE REGISTROS:</strong> {{ $registros->count() }}
            </div>
        </div>
    </div>

    @if ($registros->isEmpty())
        <div class="empty-state">
            No se encontraron registros para los filtros seleccionados.
        </div>
    @else
        <!-- Tabla de Registros -->
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 20%;">Cedula</th>
                    <th style="width: 20%;">Nombre</th>
                    <th style="width: 20%;">Apellido</th>
                    <th style="width: 20%;">PNF</th>
                    <th style="width: 20%;">Direccion</th>
                    <th style="width: 15%;">Fecha Registro</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($registros as $index => $registro)
                    <tr>
                        <td class="center">{{ $index + 1 }}</td>
                        <td>{{ $registro->cedula_persona ?? '—' }}</td>
                        <td>{{ $registro->nombre_persona ?? '—' }}</td>
                        <td>{{ $registro->apellido_persona ?? '—' }}</td>
                        <td>{{ $registro->nombre_prg ?? '—' }}</td>
                        <td>{{ $registro->sector ?? '—' }}</td>
                        <td class="center">
                            @if (!empty($registro->fecha_regis_diario_c))
                                {{ \Carbon\Carbon::parse($registro->fecha_regis_diario_c)->format('d/m/Y') }}
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Resumen por PNF -->
        <div class="resumen-section">
            <h3>Resumen por Programa Nacional de Formación (PNF)</h3>

            @foreach ($registros_pnf as $registro)
                <div class="item-pnf">
                    <div class="item-pnf-info">
                        <div class="item-pnf-nombre">{{ $registro->nombre_prg }}</div>
                        <div class="item-pnf-subtitle">Estudiantes registrados en este PNF</div>
                    </div>

                    <div class="item-pnf-cantidad">
                        {{ $registro->total_registros }}
                    </div>

                    <div class="clearfix"></div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p><strong>Comedor Universitario - UPTP Juan Jesus Montilla</strong></p>
        <p>Sistema de Bienestar Estudiantil | Documento generado automáticamente</p>
    </div>

    <script type="text/php">
        if (isset($pdf)) {
            $pdf->page_script('
                $text = "Página " . $PAGE_NUM;
                $font = null;
                $size = 9;
                $color = array(0, 0, 0);
                $word_space = 0.0;
                $char_space = 0.0;
                $angle = 0.0;
 
                $textWidth = $fontMetrics->getTextWidth($text, $font, $size);
 
                $x = ($pdf->get_width() - $textWidth) / 2;
                $y = $pdf->get_height() - 35;
 
                $pdf->text($x, $y, $text, $font, $size, $color, $word_space, $char_space, $angle);
            ');
        }
    </script>
</body>

</html>
