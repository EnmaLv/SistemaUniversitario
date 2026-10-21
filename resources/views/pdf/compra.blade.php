<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte de Compras Detallado</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
            color: #333333;
        }

        .institutional-header {
            background-color: #ffffff;
            padding: 15px 30px;
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
            width: 20%;
        }

        .header-center {
            display: table-cell;
            vertical-align: middle;
            text-align: center;
            width: 60%;
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

        .decorative-lines {
            height: 8px;
            background: linear-gradient(to bottom,
                    #d41002 0%, #d41002 33%,
                    #333 33%, #333 66%,
                    #ffc107 66%, #ffc107 100%);
            margin-bottom: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 20px 40px;
        }

        .document-info {
            text-align: right;
            margin-bottom: 25px;
            padding: 10px;
            background-color: #f9f9f9;
            border-left: 3px solid #d41002;
            font-size: 12px;
        }

        .document-info p {
            margin: 3px 0;
            color: #666;
        }

        .document-info strong {
            color: #d41002;
        }

        .compra-item {
            margin-bottom: 40px;
            padding-bottom: 30px;
            border-bottom: 2px solid #eee;
        }

        .compra-item:last-child {
            border-bottom: none;
        }

        .compra-header {
            background-color: #f5f5f5;
            padding: 15px 20px;
            border-left: 5px solid #d41002;
            margin-bottom: 20px;
        }

        .compra-header h2 {
            font-size: 16px;
            margin: 0 0 8px 0;
            color: #d41002;
            font-weight: bold;
        }

        .compra-header p {
            font-size: 13px;
            margin: 0;
            color: #666;
        }

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

        .info-label,
        .info-value {
            display: table-cell;
            padding: 10px 15px;
            font-size: 13px;
            border-bottom: 1px solid #e0e0e0;
        }

        .info-label {
            font-weight: bold;
            color: #333;
            width: 35%;
            background-color: #f0f0f0;
        }

        .info-value {
            color: #666;
        }

        .section-title {
            font-size: 15px;
            font-weight: bold;
            color: #333;
            margin: 25px 0 15px 0;
            padding: 8px 15px;
            background-color: #f0f0f0;
            border-left: 4px solid #d41002;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .product-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 13px;
        }

        .product-table th,
        .product-table td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }

        .product-table th {
            background-color: #e8e8e8;
            color: #333;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
            text-align: center;
        }

        .product-table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .product-table td.center {
            text-align: center;
        }

        .product-table td.right {
            text-align: right;
        }

        .observaciones-section {
            background-color: #fffbf0;
            padding: 12px 15px;
            border-left: 4px solid #ffc107;
            margin: 15px 0;
            border: 1px solid #ffe082;
        }

        .observaciones-section p {
            margin: 0;
            font-size: 12px;
            line-height: 1.5;
            color: #666;
        }

        .observaciones-section strong {
            color: #f57c00;
        }

        .total-section {
            margin-top: 20px;
            padding: 15px 20px;
            background-color: #f5f5f5;
            border: 2px solid #d41002;
            text-align: right;
        }

        .total-section .total-label {
            font-size: 15px;
            font-weight: bold;
            color: #333;
            margin-right: 15px;
        }

        .total-section .total-valor {
            font-size: 18px;
            font-weight: bold;
            color: #d41002;
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 3px solid #d41002;
            text-align: center;
            font-size: 11px;
            color: #666;
        }

        .footer p {
            margin: 5px 0;
            line-height: 1.5;
        }

        .footer strong {
            color: #333;
            font-size: 12px;
        }

        .empty-state {
            text-align: center;
            padding: 60px 30px;
        }

        .empty-state p {
            font-size: 15px;
            color: #666;
            margin: 0;
        }

        @media print {
            body {
                padding: 0;
            }

            .container {
                max-width: 100%;
                padding: 10px 20px;
            }

            .compra-item {
                page-break-inside: avoid;
            }

            .institutional-header {
                page-break-after: avoid;
            }
        }
    </style>
</head>

<body>
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

    <div class="container">
        <div class="document-info">
            <p><strong>REPORTE DE COMPRAS DETALLADO</strong></p>
            <p>Fecha de generación: {{ now()->format('d/m/Y') }}</p>
            <p>Hora: {{ now()->format('H:i:s') }}</p>
        </div>

        @forelse ($compras as $compra)
            <div class="compra-item">
                <div class="compra-header">
                    <h2>COMPRA N° {{ $compra->id_cmp ?? 'N/A' }}</h2>
                    <p>Proveedor: {{ $compra->proveedor_empresa }}</p>
                </div>

                <div class="info-grid">
                    <div class="info-row">
                        <div class="info-label">Fecha de Compra:</div>
                        <div class="info-value">{{ \Carbon\Carbon::parse($compra->fecha_compra_cmp)->format('d/m/Y H:i') }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Registro en Sistema:</div>
                        <div class="info-value">{{ \Carbon\Carbon::parse($compra->created_at)->format('d/m/Y H:i:s') }}
                        </div>
                    </div>
                </div>

                <h3 class="section-title">Detalle de Productos</h3>

                <table class="product-table">
                    <thead>
                        <tr>
                            <th style="width: 40%;">Producto</th>
                            <th style="width: 12%;">Cantidad</th>
                            <th style="width: 12%;">Unidad</th>
                            <th style="width: 18%;">Precio Unit.</th>
                            <th style="width: 18%;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($compra->detalles as $detalle)
                            <tr>
                                <td>{{ $detalle->producto_nombre }}</td>
                                <td class="right">{{ number_format($detalle->cantidad_dcm, 2, ',', '.') }}</td>
                                <td class="center">{{ $detalle->unidad_abreviatura }}</td>
                                <td class="right">{{ number_format($detalle->precio_unitario_dcm, 2, ',', '.') }} Bs</td>
                                <td class="right">{{ number_format($detalle->subtotal_dcm, 2, ',', '.') }} Bs</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                @if ($compra->observacion_cmp)
                    <div class="observaciones-section">
                        <p><strong>Observaciones:</strong> {{ $compra->observacion_cmp }}</p>
                    </div>
                @endif

                <div class="total-section">
                    <span class="total-label">TOTAL DE LA COMPRA:</span>
                    <span class="total-valor">{{ number_format($compra->total_cmp, 2, ',', '.') }} Bs</span>
                </div>
            </div>
        @empty
            <div class="empty-state">
                <p>No se encontraron registros de compras con los criterios especificados.</p>
            </div>
        @endforelse

        <div class="footer">
            <p><strong>Comedor Universitario - UPTP Juan Jesus Montilla</strong></p>
            <p>Sistema de Bienestar Estudiantil | Documento generado automáticamente</p>
        </div>
    </div>

    <script type="text/php">
        if (isset($pdf)) {
            $pdf->page_script('
                $text = "Página " . $PAGE_NUM;
                $font = null;
                $size = 9;
                $color = array(0,0,0);
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
