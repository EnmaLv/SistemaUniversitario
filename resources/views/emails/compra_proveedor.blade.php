<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Orden de Compra Nro: {{ $compra->id_cmp }}</title>
</head>

<body>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
            color: #333333;
        }

        .container {
            max-width: 650px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 0;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
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
            margin-bottom: 0;
        }

        .document-header {
            background-color: #f5f5f5;
            padding: 20px 30px;
            text-align: left;
            border-bottom: 2px solid #d41002;
        }

        .document-header h3 {
            font-size: 20px;
            margin: 0 0 8px 0;
            font-weight: bold;
            color: #d41002;
        }

        .document-header p {
            margin: 0;
            font-size: 13px;
            color: #666;
        }

        .content-wrapper {
            padding: 30px;
        }

        .info-section {
            background-color: #f9f9f9;
            padding: 18px;
            border-left: 4px solid #d41002;
            margin-bottom: 25px;
            border-radius: 4px;
        }

        .info-section p {
            margin: 0 0 8px 0;
            font-size: 14px;
            line-height: 1.6;
        }

        .info-section p:last-child {
            margin-bottom: 0;
        }

        .info-section strong {
            color: #d41002;
            font-weight: bold;
        }

        .greeting {
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .greeting p {
            margin: 0 0 10px 0;
            font-size: 14px;
        }

        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            margin: 25px 0 15px 0;
            padding-bottom: 8px;
            border-bottom: 2px solid #d41002;
        }

        .product-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
            font-size: 14px;
        }

        .product-table th,
        .product-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }

        .product-table th {
            background-color: #f0f0f0;
            color: #333;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
        }

        .product-table tbody tr:nth-child(even) {
            background-color: #fafafa;
        }

        .product-table tbody tr:hover {
            background-color: #fff5f5;
        }

        .product-table td:first-child {
            text-align: center;
            font-weight: bold;
            color: #666;
            width: 60px;
        }

        .product-table td:last-child {
            text-align: center;
            width: 100px;
        }

        .observaciones-section {
            background-color: #fff8e6;
            padding: 15px;
            border-left: 4px solid #ffc107;
            margin: 20px 0;
            border-radius: 4px;
        }

        .observaciones-section p {
            margin: 0;
            font-size: 13px;
            line-height: 1.5;
        }

        .observaciones-section strong {
            color: #f57c00;
        }

        .closing {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .closing p {
            margin: 0 0 8px 0;
            font-size: 14px;
            line-height: 1.6;
        }

        .signature {
            margin-top: 15px;
            font-weight: bold;
            color: #d41002;
        }

        .footer {
            background-color: #f9f9f9;
            text-align: center;
            font-size: 12px;
            color: #666;
            padding: 20px 30px;
            border-top: 1px solid #eee;
        }

        .footer p {
            margin: 5px 0;
            line-height: 1.5;
        }
    </style>

    <div class="container">
        <div class="institutional-header">
            <div class="header-content">
                <div class="header-left">
                    <a href='https://postimg.cc/F7pD0F56' target='_blank'><img src='https://i.postimg.cc/F7pD0F56/ministerio-Logo.png' border='0' alt='ministerio-Logo'></a>
                </div>
                <div class="header-center">
                    <h1>Universidad Politécnica Territorial del Estado Portuguesa</h1>
                    <h2>Juan Jesus Montilla</h2>
                    <p>Comedor Universitario</p>
                </div>
                <div class="header-right">
                    <a href='https://postimg.cc/QB9mLp9s' target='_blank'><img src='https://i.postimg.cc/QB9mLp9s/uptp-logo.png' border='0' alt='uptp-logo'></a>
                </div>
            </div>
        </div>

        <div class="decorative-lines"></div>

        <div class="document-header">
            <h3>Orden de Compra Nro: {{ $compra->id_cmp }}</h3>
            <p>Fecha de emisión: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>
        </div>

        <div class="content-wrapper">
            <div class="info-section">
                <p><strong>Proveedor:</strong> {{ $proveedor->nombre_contacto_prv }}</p>
                <p><strong>Empresa:</strong> {{ $proveedor->empresa_prv ?? 'N/A' }}</p>
                <p><strong>Fecha de Orden:</strong> {{ \Carbon\Carbon::parse($compra->fecha_compra_cmp)->format('d/m/Y') }}</p>
                <p><strong>Registro en Sistema:</strong> {{ \Carbon\Carbon::parse($compra->created_at)->format('d/m/Y H:i:s') }}</p>
            </div>

            <div class="greeting">
                <p>Estimado/a <strong>{{ $proveedor->nombre_contacto_prv }}</strong>,</p>
                <p>Por medio del presente, le enviamos los detalles de la orden de compra <strong>Nro: {{ $compra->id_cmp }}</strong> para su procesamiento.</p>
            </div>

            <h2 class="section-title">Productos Solicitados</h2>

            <table class="product-table">
                <thead>
                    <tr>
                        <th>Nro</th>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Unidad</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($detalleCompras as $detalle)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $detalle->producto->nombre_pdt }}</td>
                            <td>{{ number_format($detalle->cantidad_dcm, 2, ',', '.') }}</td>
                            <td>{{ $detalle->unidad->abreviatura_unm ?? 'UND' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if($compra->observacion_cmp)
            <div class="observaciones-section">
                <p><strong>Observaciones:</strong> {{ $compra->observacion_cmp }}</p>
            </div>
            @endif

            <div class="closing">
                <p>Agradecemos su atención y pronta respuesta a esta solicitud.</p>
                <p>Quedamos a su disposición para cualquier consulta o aclaración.</p>
            </div>
        </div>

        <div class="footer">
            <p><strong>Comedor Universitario - UPTP Juan Jesus Montilla</strong></p>
            <p>Sistema de Bienestar Estudiantil | Documento generado automáticamente</p>
        </div>
    </div>
</body>

</html>