<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Movimiento de Inventario</title>
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
            margin-bottom: 20px;
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

        .data-table td.right {
            text-align: right;
        }

        /* Badges */
        .badge {
            padding: 3px 8px;
            border-radius: 3px;
            color: #fff;
            font-size: 9px;
            text-transform: uppercase;
            font-weight: bold;
            display: inline-block;
        }

        .badge-egreso {
            background-color: #e67e22;
        }

        .badge-ingreso {
            background-color: #27ae60;
        }

        /* Footer */
        .footer {
            position: fixed;
            bottom: -10px;
            left: 0px;
            right: 0px;
            height: 30px;
            text-align: center;
            font-size: 9px;
            color: #777;
            border-top: 1px solid #eee;
            padding-top: 10px;
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
        }
    </style>
</head>

<body>
    <!-- Header Institucional -->
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
        <h3>Movimiento de Inventario</h3>
        <p>Reporte detallado de entradas y salidas de almacén</p>
    </div>

    <!-- Información de Filtros -->
    <div class="info-grid">
        <div class="info-row">
            <div class="info-cell">
                <strong>RANGO DE BÚSQUEDA:</strong>
                Desde: {{ $filtro['fecha_desde'] ?? 'Hoy' }}<br>
                Hasta: {{ $filtro['fecha_hasta'] ?? 'N/A' }}<br>
                Tipo de producto: {{ $filtro['tipo_producto_nombre'] ?? 'Todos' }}
            </div>
            <div class="info-cell">
                <strong>GENERADO EL:</strong> {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}<br>
                <strong>TOTAL MOVIMIENTOS:</strong> {{ $movimiento->count() }}
            </div>
        </div>
    </div>

    <!-- Tabla de Movimientos -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 12%;">FECHA</th>
                <th style="width: 20%;">LOTE</th>
                <th style="width: 20%;">PRODUCTO / ÍTEM</th>
                <th style="width: 15%;">TIPO</th>
                <th style="width: 10%;">CANT. (g)</th>
                <th style="width: 15%;">SEDE</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($movimiento as $index => $item)
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->fecha_movimiento_moi)->format('d/m/Y') }}</td>
                    <td><strong>{{ $item->lote->codigo_lote_lot ?? 'N/A' }}</strong></td>
                    <td><strong>{{ $item->producto->nombre_pdt ?? 'N/A' }}</strong></td>
                    <td>
                        <span
                            class="badge {{ strtoupper($item->tipo_movimiento_moi) == 'ENTRADA' ? 'badge-ingreso' : 'badge-egreso' }}">
                            {{ $item->tipo_movimiento_moi }}
                        </span>
                    </td>
                    <td class="right">{{ number_format($item->cantidad_convertida_moi, 2, ',', '.') }}</td>
                    <td>{{ $item->sede->nombre_sed ?? 'N/A' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p><strong>Comedor Universitario - UPTP Juan Jesús Montilla</strong></p>
        <p>Sistema de Bienestar Estudiantil | Documento generado automáticamente</p>
    </div>

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
                $y = $pdf->get_height() - 35;
 
                $pdf->text($x, $y, $text, $font, $size, $color, $word_space, $char_space, $angle);
            ');
        }
    </script>
</body>

</html>
