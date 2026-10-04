{{--
    Listado del panel estadístico de Salud (PDF, DomPDF, A4 horizontal).
    Mismo diseño que pdf/salud/pdf.blade.php.

    Variables:
    - $reporte  Definición que arma ListadoSaludService (columnas, filas, resumen, anexo)
    - $meta     periodo, tipoPeriodo, generado y filtros (pares [etiqueta, valor])
--}}
@php
    $cintilloPath = public_path('img/CintilloUPTP.png');
    $cintilloBase64 = file_exists($cintilloPath)
        ? 'data:image/png;base64,' . base64_encode(file_get_contents($cintilloPath))
        : null;

    // Da formato a una celda según el tipo de su columna
    $formatear = function ($valor, $tipo) {
        if ($valor === null || $valor === '') {
            return '—';
        }
        if ($tipo === 'fecha') {
            return $valor instanceof \DateTimeInterface ? $valor->format('d/m/Y') : (string) $valor;
        }
        if ($tipo === 'numero') {
            return rtrim(rtrim(number_format((float) $valor, 2, ',', '.'), '0'), ',');
        }
        if ($tipo === 'entero') {
            return number_format((int) $valor, 0, ',', '.');
        }
        return (string) $valor;
    };

    $clasesEstado = ['Completada' => '', 'En proceso' => 'incompleto', 'Sin receta' => 'sin'];

    $tablas = [['titulo' => 'Detalle', 'columnas' => $reporte['columnas'], 'filas' => $reporte['filas']]];
    if (!empty($reporte['anexo'])) {
        $tablas[] = $reporte['anexo'];
    }
@endphp
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>{{ $reporte['titulo'] }}</title>
    <style>
        @page {
            margin: 30px 40px 60px 40px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
            color: #1f2937;
            font-size: 9px;
            line-height: 1.45;
        }

        /* ── Encabezado institucional ── */
        .institutional-header {
            text-align: center;
            margin-bottom: 16px;
        }

        .header-image {
            max-height: 85px;
            max-width: 100%;
        }

        /* ── Título ── */
        .document-title h1 {
            font-size: 15px;
            margin: 0;
            font-weight: normal;
            color: #111827;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .document-title p {
            margin: 2px 0 0 0;
            font-size: 9px;
            color: #6b7280;
        }

        .document-header-line {
            border-bottom: 1px solid #1f4e3d;
            margin-top: 8px;
            margin-bottom: 14px;
        }

        /* ── Metadatos ── */
        table {
            border-collapse: collapse;
        }

        .metadata {
            width: 100%;
            margin-bottom: 14px;
        }

        .metadata td {
            padding: 4px 14px;
            vertical-align: top;
            border-right: 1px solid #e5e7eb;
        }

        .metadata td.primero {
            padding-left: 0;
        }

        .metadata td.ultimo {
            border-right: none;
        }

        .meta-label {
            display: block;
            font-size: 7.5px;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: #9ca3af;
            margin-bottom: 2px;
        }

        .meta-value {
            display: block;
            font-size: 10px;
            color: #111827;
        }

        .meta-value.numero {
            font-size: 14px;
            line-height: 1.1;
        }

        /* ── Filtros ── */
        .filters {
            margin-bottom: 16px;
            padding: 7px 0;
            border-top: 1px solid #e5e7eb;
            border-bottom: 1px solid #e5e7eb;
        }

        .filters .title {
            font-size: 7.5px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #9ca3af;
            margin-bottom: 4px;
        }

        .filters .tag {
            display: inline-block;
            font-size: 8.5px;
            color: #374151;
            margin-right: 16px;
        }

        /* ── Secciones ── */
        .section-title {
            font-size: 8px;
            font-weight: bold;
            letter-spacing: 1.8px;
            text-transform: uppercase;
            color: #1f4e3d;
            margin: 0 0 2px 0;
        }

        .section-divider {
            border-top: 1px solid #1f4e3d;
            margin-bottom: 4px;
        }

        /* ── Tabla ── */
        table.data-table {
            width: 100%;
            font-size: 8.5px;
        }

        table.data-table th {
            text-align: left;
            padding: 7px 8px;
            font-size: 7.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #6b7280;
            border-bottom: 1px solid #1f4e3d;
        }

        table.data-table td {
            padding: 6px 8px;
            border-bottom: 1px solid #f3f4f6;
            color: #374151;
            vertical-align: top;
        }

        table.data-table tr {
            page-break-inside: avoid;
        }

        table.data-table .right {
            text-align: right;
        }

        table.data-table td.strong {
            color: #111827;
            font-weight: bold;
        }

        table.data-table td.num {
            color: #9ca3af;
        }

        table.data-table tr.total td {
            border-top: 1px solid #1f4e3d;
            border-bottom: 1px solid #1f4e3d;
            font-weight: bold;
            color: #111827;
            padding-top: 8px;
            padding-bottom: 8px;
        }

        /* ── Estado ── */
        .estado {
            font-size: 8px;
            color: #4b5563;
            white-space: nowrap;
        }

        .estado .marca {
            color: #1f4e3d;
            font-size: 7px;
        }

        .estado.incompleto .marca {
            color: #9ca3af;
        }

        .estado.sin .marca {
            color: #d1d5db;
        }

        .empty {
            padding: 16px 0;
            color: #9ca3af;
            font-size: 9px;
            font-style: italic;
        }

        /* ── Firmas ── */
        .signatures {
            width: 100%;
            margin-top: 40px;
            page-break-inside: avoid;
        }

        .signatures td {
            width: 50%;
            text-align: center;
            padding: 0 20px;
            vertical-align: bottom;
        }

        .signature-line {
            border-top: 1px solid #9ca3af;
            width: 190px;
            margin: 40px auto 6px auto;
        }

        .signature-name {
            font-size: 8.5px;
            color: #111827;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .signature-role {
            font-size: 7.5px;
            color: #9ca3af;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        /* ── Pie ── */
        .footer {
            position: fixed;
            bottom: -40px;
            left: 0;
            right: 0;
            padding-top: 6px;
            border-top: 1px solid #e5e7eb;
            font-size: 7.5px;
            color: #9ca3af;
        }

        .footer table {
            width: 100%;
        }

        .footer td {
            padding: 0;
        }

        .footer .right {
            text-align: right;
        }

        .footer .page-num:after {
            content: counter(page);
        }
    </style>
</head>

<body>

    {{-- Pie fijo (se declara primero para que DomPDF lo repita en todas las páginas) --}}
    <div class="footer">
        <table>
            <tr>
                <td>UPTP · {{ $reporte['titulo'] }} · Módulo Salud · {{ $meta['generado'] }}</td>
                <td class="right">Página <span class="page-num"></span></td>
            </tr>
        </table>
    </div>

    {{-- Encabezado institucional --}}
    @if ($cintilloBase64)
        <div class="institutional-header">
            <img src="{{ $cintilloBase64 }}" class="header-image" alt="Encabezado institucional">
        </div>
    @else
        <div class="institutional-header" style="padding: 16px; border-bottom: 1px solid #e5e7eb;">
            <span style="color: #9ca3af;">[Imagen institucional no disponible]</span>
        </div>
    @endif

    {{-- Título --}}
    <div class="document-title">
        <h1>{{ $reporte['titulo'] }}</h1>
        <p>{{ $reporte['descripcion'] }}</p>
    </div>
    <div class="document-header-line"></div>

    {{-- Metadatos y resumen --}}
    <table class="metadata">
        <tr>
            <td class="primero">
                <span class="meta-label">Período</span>
                <span class="meta-value">{{ $meta['periodo'] }}</span>
            </td>
            <td>
                <span class="meta-label">Tipo</span>
                <span class="meta-value">{{ $meta['tipoPeriodo'] }}</span>
            </td>
            @foreach ($reporte['resumen'] as $par)
                <td>
                    <span class="meta-label">{{ $par[0] }}</span>
                    <span class="meta-value numero">{{ $formatear($par[1], 'entero') }}</span>
                </td>
            @endforeach
            <td class="ultimo">
                <span class="meta-label">Generado</span>
                <span class="meta-value">{{ $meta['generado'] }}</span>
            </td>
        </tr>
    </table>

    {{-- Filtros aplicados --}}
    @if (count($meta['filtros']) > 0)
        <div class="filters">
            <div class="title">Filtros aplicados</div>
            <div>
                @foreach ($meta['filtros'] as $par)
                    <span class="tag"><b>{{ $par[0] }}:</b> {{ $par[1] }}</span>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Tablas --}}
    @if (count($reporte['filas']) === 0)
        <div class="section-title">Detalle</div>
        <div class="section-divider"></div>
        <div class="empty">{{ $reporte['vacio'] }}</div>
    @else
        @foreach ($tablas as $indice => $tabla)
            @php
                // La tabla anexa va más angosta y no se separa de su título:
                // si es corta se mantiene junta; si es larga empieza en página nueva.
                $estiloBloque = '';
                if ($indice > 0) {
                    $estiloBloque = 'width: 60%; ' . (count($tabla['filas']) <= 20
                        ? 'page-break-inside: avoid; margin-top: 22px;'
                        : 'page-break-before: always;');
                }
            @endphp
            <div style="{{ $estiloBloque }}">
            <div class="section-title">{{ $tabla['titulo'] }}</div>
            <div class="section-divider"></div>

            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: {{ $indice > 0 ? 6 : 3 }}%;">N.º</th>
                        @foreach ($tabla['columnas'] as $columna)
                            <th style="width: {{ $columna['ancho'] * ($indice > 0 ? 0.94 : 0.97) }}%;"
                                class="{{ in_array($columna['tipo'], ['numero', 'entero']) ? 'right' : '' }}">
                                {{ $columna['titulo'] }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tabla['filas'] as $numero => $fila)
                        <tr>
                            <td class="num">{{ $numero + 1 }}</td>
                            @foreach ($tabla['columnas'] as $columna)
                                @php
                                    $valor = $fila[$columna['clave']] ?? null;
                                    $clases = trim(
                                        (in_array($columna['tipo'], ['numero', 'entero']) ? 'right ' : '') .
                                        ($columna['fuerte'] ? 'strong' : '')
                                    );
                                @endphp
                                <td class="{{ $clases }}">
                                    @if ($columna['tipo'] === 'estado')
                                        <span class="estado {{ $clasesEstado[$valor] ?? 'incompleto' }}">
                                            <span class="marca">■</span> {{ $valor }}
                                        </span>
                                    @else
                                        {{ $formatear($valor, $columna['tipo']) }}
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                    <tr class="total">
                        <td colspan="{{ count($tabla['columnas']) + 1 }}">
                            Total de registros: {{ $formatear(count($tabla['filas']), 'entero') }}
                        </td>
                    </tr>
                </tbody>
            </table>
            </div>
        @endforeach
    @endif

    {{-- Firmas --}}
    <table class="signatures">
        <tr>
            <td>
                <div class="signature-line"></div>
                <div class="signature-name">Coordinación de Salud</div>
                <div class="signature-role">Sello y firma</div>
            </td>
            <td>
                <div class="signature-line"></div>
                <div class="signature-name">Dirección de Bienestar Estudiantil</div>
                <div class="signature-role">Sello y firma</div>
            </td>
        </tr>
    </table>

</body>

</html>
