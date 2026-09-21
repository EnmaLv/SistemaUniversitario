<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte Estadístico de Salud</title>
    <style>
        @page {
            margin: 30px 40px 60px 40px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', ;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
            color: #1f2937;
            font-size: 9.5px;
            line-height: 1.5;
        }

        /* ============================================================
           COLOR DE ACENTO ÚNICO
           ============================================================ */
        /* --accent: #1f4e3d (verde institucional apagado) */

        /* ===================== HEADER ===================== */
        .institutional-header {
            text-align: center;
            padding-top: 0;
            margin-bottom: 18px;
        }

        .header-image {
            max-height: 95px;
            width: 100%;
            object-fit: contain;
        }

        /* ===================== TÍTULO ===================== */
        .document-title {
            text-align: left;
            margin-bottom: 4px;
        }

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
            letter-spacing: 0.3px;
        }

        .document-header-line {
            border-bottom: 1px solid #1f4e3d;
            margin-top: 8px;
            margin-bottom: 18px;
        }

        /* ===================== METADATA ===================== */
        .metadata {
            margin-bottom: 20px;
        }

        .metadata table {
            width: 100%;
            border-collapse: collapse;
        }

        .metadata td {
            padding: 6px 12px 6px 0;
            font-size: 9px;
            vertical-align: top;
            border-right: 1px solid #e5e7eb;
        }

        .metadata td:last-child {
            border-right: none;
            padding-right: 0;
        }

        .metadata td:first-child {
            padding-left: 0;
        }

        .meta-label {
            display: block;
            font-size: 7.5px;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: #9ca3af;
            margin-bottom: 3px;
        }

        .meta-value {
            display: block;
            font-size: 10px;
            color: #111827;
        }

        /* ===================== FILTROS ===================== */
        .filters {
            margin-bottom: 20px;
            padding: 8px 0;
            border-top: 1px solid #e5e7eb;
            border-bottom: 1px solid #e5e7eb;
        }

        .filters .title {
            font-size: 7.5px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #9ca3af;
            margin-bottom: 5px;
        }

        .filters .tag {
            display: inline-block;
            font-size: 8.5px;
            color: #374151;
            margin-right: 14px;
        }

        .filters .tag::before {
            content: "·";
            color: #1f4e3d;
            font-weight: bold;
            margin-right: 5px;
        }

        /* ===================== SECCIONES ===================== */
        .section {
            margin-bottom: 22px;
            page-break-inside: avoid;
        }

        .section-title {
            font-size: 8px;
            font-weight: bold;
            letter-spacing: 1.8px;
            text-transform: uppercase;
            color: #1f4e3d;
            margin-bottom: 2px;
        }

        .section-divider {
            border-top: 1px solid #1f4e3d;
            margin-bottom: 12px;
        }

        .subsection-title {
            font-size: 8px;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: #6b7280;
            margin-bottom: 8px;
        }

        /* ===================== KPIs ===================== */
        .kpi-table {
            width: 100%;
            border-collapse: collapse;
        }

        .kpi-table td {
            width: 16.66%;
            text-align: left;
            padding: 8px 8px 8px 0;
            vertical-align: top;
        }

        .kpi-label {
            font-size: 7.5px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #9ca3af;
            margin-bottom: 4px;
        }

        .kpi-value {
            font-size: 18px;
            font-weight: normal;
            color: #111827;
            line-height: 1;
            font-family: 'DejaVu Sans', sans-serif;
        }

        .kpi-sub {
            font-size: 7.5px;
            color: #9ca3af;
            margin-top: 4px;
        }

        .kpi-positive {
            color: #1f4e3d;
        }

        .kpi-negative {
            color: #991b1b;
        }

        /* ===================== TABLA PRINCIPAL ===================== */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }

        table.data-table th {
            text-align: left;
            padding: 8px 10px;
            font-size: 7.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #6b7280;
            border-bottom: 1px solid #1f4e3d;
            background-color: transparent;
        }

        table.data-table td {
            padding: 7px 10px;
            border-bottom: 1px solid #f3f4f6;
            color: #374151;
            vertical-align: top;
        }

        table.data-table tr:last-child td {
            border-bottom: 1px solid #e5e7eb;
        }

        table.data-table td.right {
            text-align: right;
        }

        table.data-table td.center {
            text-align: center;
        }

        table.data-table td.strong {
            color: #111827;
        }

        table.data-table tr.total td {
            border-top: 1px solid #1f4e3d;
            border-bottom: 1px solid #1f4e3d;
            font-weight: bold;
            color: #111827;
            padding-top: 9px;
            padding-bottom: 9px;
        }

        /* ===================== TABLA MÉTRICAS ===================== */
        table.metrics {
            width: 100%;
            border-collapse: collapse;
        }

        table.metrics td {
            padding: 6px 0;
            font-size: 9.5px;
            border-bottom: 1px solid #f3f4f6;
            color: #4b5563;
        }

        table.metrics td.value {
            text-align: right;
            color: #111827;
            font-weight: bold;
            white-space: nowrap;
        }

        table.metrics tr.divider td {
            padding-top: 14px;
            padding-bottom: 5px;
            font-size: 7.5px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #1f4e3d;
            font-weight: bold;
            border-bottom: 1px solid #e5e7eb;
        }

        /* ===================== BARRAS ===================== */
        .bar-row {
            margin-bottom: 6px;
        }

        .bar-row table {
            width: 100%;
            border-collapse: collapse;
        }

        .bar-row .label {
            width: 30%;
            font-size: 8.5px;
            color: #4b5563;
            padding-right: 8px;
            vertical-align: middle;
        }

        .bar-row .bar-cell {
            width: 60%;
            padding-right: 8px;
            vertical-align: middle;
        }

        .bar-row .value {
            width: 10%;
            text-align: right;
            font-size: 8.5px;
            color: #111827;
            vertical-align: middle;
        }

        .bar-bg {
            width: 100%;
            height: 6px;
            background-color: #f3f4f6;
        }

        .bar-fill {
            height: 6px;
            background-color: #1f4e3d;
        }

        .bar-fill.light {
            background-color: #6b7280;
        }

        /* ===================== COLUMNAS ===================== */
        .two-cols {
            display: table;
            width: 100%;
            border-spacing: 24px 0;
        }

        .two-cols>.col {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }

        /* ===================== ESTADO BADGES ===================== */
        .estado {
            display: inline-block;
            font-size: 8px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            color: #4b5563;
        }

        .estado::before {
            content: "■";
            color: #1f4e3d;
            margin-right: 4px;
            font-size: 7px;
        }

        .estado.incompleto::before {
            color: #9ca3af;
        }

        .estado.sin::before {
            color: #d1d5db;
        }

        /* ===================== EMPTY ===================== */
        .empty {
            padding: 12px 0;
            color: #9ca3af;
            font-size: 8.5px;
            font-style: italic;
        }

        /* ===================== FIRMA ===================== */
        .signatures {
            margin-top: 50px;
            page-break-inside: avoid;
        }

        .signatures table {
            width: 100%;
            border-collapse: collapse;
        }

        .signatures td {
            width: 50%;
            text-align: center;
            padding: 0 20px;
            vertical-align: bottom;
        }

        .signature-line {
            border-top: 1px solid #9ca3af;
            width: 180px;
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

        /* ===================== FOOTER ===================== */
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
            border-collapse: collapse;
        }

        .footer td {
            padding: 0;
        }

        .footer .left {
            text-align: left;
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

    {{-- ============================================================ --}}
    {{-- ENCABEZADO INSTITUCIONAL --}}
    {{-- ============================================================ --}}
    @php
        $cintilloPath = public_path('img/CintilloUPTP.png');
        $cintilloBase64 = file_exists($cintilloPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($cintilloPath))
            : null;
    @endphp

    @if ($cintilloBase64)
        <div class="institutional-header">
            <img src="{{ $cintilloBase64 }}" class="header-image" alt="Encabezado Institucional">
        </div>
    @else
        <div class="institutional-header" style="padding: 20px; text-align: center; border-bottom: 1px solid #e5e7eb;">
            <p style="color: #9ca3af; font-size: 9px; margin: 0;">[Imagen institucional no disponible]</p>
        </div>
    @endif

    {{-- ============================================================ --}}
    {{-- TÍTULO --}}
    {{-- ============================================================ --}}
    <div class="document-title">
        <h1>Reporte Estadístico</h1>
        <p>Análisis de consultas, recetas y dispensaciones</p>
    </div>
    <div class="document-header-line"></div>

    {{-- ============================================================ --}}
    {{-- METADATA --}}
    {{-- ============================================================ --}}
    <div class="metadata">
        <table>
            <tr>
                <td style="width: 28%;">
                    <span class="meta-label">Período</span>
                    <span class="meta-value">
                        {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }}
                        — {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}
                    </span>
                </td>
                <td style="width: 20%;">
                    <span class="meta-label">Tipo</span>
                    <span class="meta-value"
                        style="text-transform: capitalize;">{{ $periodo ?? 'Personalizado' }}</span>
                </td>
                <td style="width: 22%;">
                    <span class="meta-label">Alcance</span>
                    <span class="meta-value" style="text-transform: capitalize;">{{ $reportType ?? 'Completo' }}</span>
                </td>
                <td style="width: 30%;">
                    <span class="meta-label">Generado</span>
                    <span class="meta-value">{{ now()->format('d/m/Y H:i') }}</span>
                </td>
            </tr>
        </table>
    </div>

    {{-- ============================================================ --}}
    {{-- FILTROS APLICADOS --}}
    {{-- ============================================================ --}}
    @php
        $filtrosAplicados = [];

        if (!empty($estado_receta)) {
            $estadoLabels = [
                'con_receta' => 'Con receta',
                'sin_receta' => 'Sin receta',
                'completado' => 'Completadas',
                'sin_dispensacion' => 'Sin dispensación',
                'con_pendientes' => 'Con pendientes',
            ];
            $filtrosAplicados[] = ['Estado', $estadoLabels[$estado_receta] ?? $estado_receta];
        }

        if (!empty($consultorio_nombre)) {
            $filtrosAplicados[] = ['Consultorio', $consultorio_nombre];
        }

        if (!empty($medico_nombre)) {
            $filtrosAplicados[] = ['Médico', $medico_nombre];
        }

        if (!empty($enfermedades_nombres) && count($enfermedades_nombres) > 0) {
            $filtrosAplicados[] = ['Enfermedades', implode(' · ', $enfermedades_nombres)];
        }

        if (!empty($perfil_academico)) {
            $filtrosAplicados[] = ['Rol', $perfil_academico];
        }

        if (!empty($pnf)) {
            $filtrosAplicados[] = ['PNF', str_replace('_', ' ', $pnf)];
        }
    @endphp


    @if (count($filtrosAplicados) > 0)
        <div class="filters">
            <div class="title">Filtros aplicados</div>
            <div>
               @foreach ($filtrosAplicados as [$label, $valor])
                <span class="tag"><b>{{ $label }}: </b>{{ $valor }}</span>
            @endforeach
            </div>
        </div>
    @endif

    {{-- ============================================================ --}}
    {{-- KPIs --}}
    {{-- ============================================================ --}}
    <div class="section">
        <div class="section-title">Resumen ejecutivo</div>
        <div class="section-divider"></div>

        <table class="kpi-table">
            <tr>
                <td>
                    <div class="kpi-label">Consultas</div>
                    <div class="kpi-value">{{ number_format($resumen['total_consultas'] ?? 0) }}</div>
                </td>
                <td>
                    <div class="kpi-label">Pacientes</div>
                    <div class="kpi-value">{{ number_format($resumen['total_pacientes'] ?? 0) }}</div>
                </td>
                <td>
                    <div class="kpi-label">Recetas</div>
                    <div class="kpi-value">{{ number_format($resumen['total_recetas'] ?? 0) }}</div>
                </td>
                <td>
                    <div class="kpi-label">Medicamentos</div>
                    <div class="kpi-value">{{ number_format($resumen['total_dispensados'] ?? 0, 1) }}</div>
                </td>
                <td>
                    <div class="kpi-label">Hora pico</div>
                    <div class="kpi-value" style="font-size: 12px;">{{ $resumen['hora_pico'] ?? 'N/A' }}</div>
                </td>
                <td>
                    <div class="kpi-label">vs. Anterior</div>
                    @php $comp = $resumen['comparativa_consultas'] ?? 0; @endphp
                    <div class="kpi-value {{ $comp > 0 ? 'kpi-positive' : ($comp < 0 ? 'kpi-negative' : '') }}"
                        style="font-size: 15px;">
                        {{ $comp > 0 ? '+' : '' }}{{ $comp }}%
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- ============================================================ --}}
    {{-- SECCIONES CONDICIONALES --}}
    {{-- ============================================================ --}}
    @php
        $mostrarDemografico = in_array($reportType ?? 'completo', ['completo', 'demografico']);
        $mostrarClinico = in_array($reportType ?? 'completo', ['completo']);
        $mostrarOperativo = in_array($reportType ?? 'completo', ['completo', 'operativo']);
        $mostrarEstadoRecetas = in_array($reportType ?? 'completo', ['completo', 'operativo']);
        $mostrarMetricas = in_array($reportType ?? 'completo', ['completo']);
    @endphp

    {{-- ==================== DEMOGRÁFICO ==================== --}}
    @if ($mostrarDemografico)
        <div class="section">
            <div class="section-title">Perfil demográfico</div>
            <div class="section-divider"></div>

            <div class="two-cols">
                <div class="col">
                    <div class="subsection-title">Distribución por género</div>
                    @php
                        $totalGenero = max(
                            1,
                            ($resumen['genero']['masculino'] ?? 0) +
                                ($resumen['genero']['femenino'] ?? 0) +
                                ($resumen['genero']['otro'] ?? 0),
                        );
                        $generos = [
                            'Masculino' => $resumen['genero']['masculino'] ?? 0,
                            'Femenino' => $resumen['genero']['femenino'] ?? 0,
                            'Otro' => $resumen['genero']['otro'] ?? 0,
                        ];
                    @endphp
                    @foreach ($generos as $label => $valor)
                        <div class="bar-row">
                            <table>
                                <tr>
                                    <td class="label">{{ $label }}</td>
                                    <td class="bar-cell">
                                        <div class="bar-bg">
                                            <div class="bar-fill"
                                                style="width: {{ round(($valor / $totalGenero) * 100, 1) }}%;"></div>
                                        </div>
                                    </td>
                                    <td class="value">{{ $valor }}</td>
                                </tr>
                            </table>
                        </div>
                    @endforeach

                    <div class="subsection-title" style="margin-top: 18px;">Distribución por edad</div>
                    @php
                        $rangos = $resumen['edades']['rangos'] ?? [];
                        $maxEdad = max(1, count($rangos) ? max($rangos) : 1);
                    @endphp
                    @foreach ($rangos as $rango => $cantidad)
                        <div class="bar-row">
                            <table>
                                <tr>
                                    <td class="label">{{ $rango }} años</td>
                                    <td class="bar-cell">
                                        <div class="bar-bg">
                                            <div class="bar-fill"
                                                style="width: {{ round(($cantidad / $maxEdad) * 100, 1) }}%;"></div>
                                        </div>
                                    </td>
                                    <td class="value">{{ $cantidad }}</td>
                                </tr>
                            </table>
                        </div>
                    @endforeach
                    <div style="margin-top: 8px; font-size: 8px; color: #6b7280;">
                        Promedio <span style="color: #111827;">{{ $resumen['edades']['promedio'] ?? 0 }}</span> ·
                        Mediana <span style="color: #111827;">{{ $resumen['edades']['mediana'] ?? 0 }}</span> ·
                        Moda <span style="color: #111827;">{{ $resumen['edades']['moda'] ?? 0 }}</span>
                    </div>
                </div>

                <div class="col">
                    <div class="subsection-title">Rol institucional</div>
                    @php
                        $roles = $resumen['perfil_academico'] ?? [];
                        $maxRol = max(1, count($roles) ? max($roles) : 1);
                    @endphp
                    @foreach ($roles as $rol => $cantidad)
                        @if ($cantidad > 0)
                            <div class="bar-row">
                                <table>
                                    <tr>
                                        <td class="label">{{ $rol }}</td>
                                        <td class="bar-cell">
                                            <div class="bar-bg">
                                                <div class="bar-fill light"
                                                    style="width: {{ round(($cantidad / $maxRol) * 100, 1) }}%;"></div>
                                            </div>
                                        </td>
                                        <td class="value">{{ $cantidad }}</td>
                                    </tr>
                                </table>
                            </div>
                        @endif
                    @endforeach

                    <div class="subsection-title" style="margin-top: 18px;">PNF / Carrera</div>
                    @php
                        $pnfs = $resumen['pnf'] ?? [];
                        $maxPnf = max(1, count($pnfs) ? max($pnfs) : 1);
                    @endphp
                    @foreach ($pnfs as $pnfNombre => $cantidad)
                        @if ($cantidad > 0)
                            <div class="bar-row">
                                <table>
                                    <tr>
                                        <td class="label">{{ $pnfNombre }}</td>
                                        <td class="bar-cell">
                                            <div class="bar-bg">
                                                <div class="bar-fill light"
                                                    style="width: {{ round(($cantidad / $maxPnf) * 100, 1) }}%;"></div>
                                            </div>
                                        </td>
                                        <td class="value">{{ $cantidad }}</td>
                                    </tr>
                                </table>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- ==================== CLÍNICO ==================== --}}
    @if ($mostrarClinico)
        <div class="section">
            <div class="section-title">Perfil clínico</div>
            <div class="section-divider"></div>

            <div class="two-cols">
                <div class="col">
                    <div class="subsection-title">Enfermedades más frecuentes</div>
                    @php
                        $enfermedades = array_slice($resumen['enfermedades'] ?? [], 0, 10, true);
                        $maxEnf = count($enfermedades) ? max($enfermedades) : 1;
                    @endphp
                    @forelse ($enfermedades as $nombre => $cantidad)
                        <div class="bar-row">
                            <table>
                                <tr>
                                    <td class="label">{{ \Illuminate\Support\Str::limit($nombre, 30) }}</td>
                                    <td class="bar-cell">
                                        <div class="bar-bg">
                                            <div class="bar-fill"
                                                style="width: {{ round(($cantidad / $maxEnf) * 100, 1) }}%;"></div>
                                        </div>
                                    </td>
                                    <td class="value">{{ $cantidad }}</td>
                                </tr>
                            </table>
                        </div>
                    @empty
                        <div class="empty">Sin enfermedades registradas en el período.</div>
                    @endforelse
                </div>

                <div class="col">
                    <div class="subsection-title">Productos más dispensados</div>
                    @php
                        $productos = array_slice($resumen['productos_top'] ?? [], 0, 10, true);
                        $maxProd = count($productos) ? max($productos) : 1;
                    @endphp
                    @forelse ($productos as $nombre => $cantidad)
                        <div class="bar-row">
                            <table>
                                <tr>
                                    <td class="label">{{ \Illuminate\Support\Str::limit($nombre, 30) }}</td>
                                    <td class="bar-cell">
                                        <div class="bar-bg">
                                            <div class="bar-fill"
                                                style="width: {{ round(($cantidad / $maxProd) * 100, 1) }}%;"></div>
                                        </div>
                                    </td>
                                    <td class="value">{{ number_format($cantidad, 1) }}</td>
                                </tr>
                            </table>
                        </div>
                    @empty
                        <div class="empty">Sin dispensaciones registradas.</div>
                    @endforelse
                </div>
            </div>
        </div>
    @endif

    {{-- ==================== OPERATIVO ==================== --}}
    @if ($mostrarOperativo)
        <div class="section">
            <div class="section-title">Métricas operativas</div>
            <div class="section-divider"></div>

            <div class="two-cols">
                <div class="col">
                    <div class="subsection-title">Consultas por médico</div>
                    @php
                        $medicos = array_slice($resumen['medicos'] ?? [], 0, 8, true);
                        $maxMed = count($medicos) ? max($medicos) : 1;
                    @endphp
                    @forelse ($medicos as $nombre => $cantidad)
                        <div class="bar-row">
                            <table>
                                <tr>
                                    <td class="label">{{ \Illuminate\Support\Str::limit($nombre, 30) }}</td>
                                    <td class="bar-cell">
                                        <div class="bar-bg">
                                            <div class="bar-fill light"
                                                style="width: {{ round(($cantidad / $maxMed) * 100, 1) }}%;"></div>
                                        </div>
                                    </td>
                                    <td class="value">{{ $cantidad }}</td>
                                </tr>
                            </table>
                        </div>
                    @empty
                        <div class="empty">Sin datos de médicos.</div>
                    @endforelse
                </div>

                <div class="col">
                    <div class="subsection-title">Consultas por consultorio</div>
                    @php
                        $consultoriosData = array_slice($resumen['consultorios'] ?? [], 0, 8, true);
                        $maxCons = count($consultoriosData) ? max($consultoriosData) : 1;
                    @endphp
                    @forelse ($consultoriosData as $nombre => $cantidad)
                        <div class="bar-row">
                            <table>
                                <tr>
                                    <td class="label">{{ \Illuminate\Support\Str::limit($nombre, 30) }}</td>
                                    <td class="bar-cell">
                                        <div class="bar-bg">
                                            <div class="bar-fill light"
                                                style="width: {{ round(($cantidad / $maxCons) * 100, 1) }}%;"></div>
                                        </div>
                                    </td>
                                    <td class="value">{{ $cantidad }}</td>
                                </tr>
                            </table>
                        </div>
                    @empty
                        <div class="empty">Sin datos de consultorios.</div>
                    @endforelse
                </div>
            </div>
        </div>
    @endif

    {{-- ==================== ESTADO DE RECETAS ==================== --}}
    @if ($mostrarEstadoRecetas)
        <div class="section">
            <div class="section-title">Estado de recetas</div>
            <div class="section-divider"></div>

            <div class="two-cols">
                <div class="col">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width: 65%;">Estado</th>
                                <th style="width: 35%; text-align: right;">Cantidad</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $estados = $resumen['estado_recetas'] ?? [];
                                $totalEstados = array_sum($estados) ?: 1;
                            @endphp
                            @foreach ($estados as $nombre => $cantidad)
                                <tr>
                                    <td>
                                        @php
                                            $claseExtra = str_contains(strtolower($nombre), 'complet')
                                                ? ''
                                                : (str_contains(strtolower($nombre), 'sin')
                                                    ? 'sin'
                                                    : 'incompleto');
                                        @endphp
                                        <span class="estado {{ $claseExtra }}">{{ $nombre }}</span>
                                    </td>
                                    <td class="right strong">
                                        {{ $cantidad }}
                                        <span style="color:#9ca3af; font-weight:normal; font-size:8px;">
                                            ({{ round(($cantidad / $totalEstados) * 100, 1) }}%)
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                            <tr class="total">
                                <td>Total</td>
                                <td class="right">{{ array_sum($estados) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="col">
                    <div class="subsection-title">Distribución semanal</div>
                    @php
                        $flujo = $resumen['flujo_semanal'] ?? [];
                        $maxFlujo = count($flujo) ? max($flujo) : 1;
                    @endphp
                    @forelse ($flujo as $semana => $cantidad)
                        <div class="bar-row">
                            <table>
                                <tr>
                                    <td class="label">Semana {{ explode('-', $semana)[0] }}</td>
                                    <td class="bar-cell">
                                        <div class="bar-bg">
                                            <div class="bar-fill"
                                                style="width: {{ round(($cantidad / $maxFlujo) * 100, 1) }}%;"></div>
                                        </div>
                                    </td>
                                    <td class="value">{{ $cantidad }}</td>
                                </tr>
                            </table>
                        </div>
                    @empty
                        <div class="empty">Sin datos semanales.</div>
                    @endforelse
                </div>
            </div>
        </div>
    @endif

    {{-- ==================== HORAS ==================== --}}
    @if ($mostrarOperativo && !empty($resumen['distribucion_horas']))
        <div class="section">
            <div class="section-title">Distribución por hora de atención</div>
            <div class="section-divider"></div>

            @php
                $horas = $resumen['distribucion_horas'];
                $maxHora = max($horas);
            @endphp
            @foreach ($horas as $bloque => $cantidad)
                <div class="bar-row">
                    <table>
                        <tr>
                            <td class="label" style="width: 18%;">{{ $bloque }}</td>
                            <td class="bar-cell" style="width: 72%;">
                                <div class="bar-bg">
                                    <div class="bar-fill light"
                                        style="width: {{ round(($cantidad / $maxHora) * 100, 1) }}%;"></div>
                                </div>
                            </td>
                            <td class="value" style="width: 10%;">{{ $cantidad }}</td>
                        </tr>
                    </table>
                </div>
            @endforeach
        </div>
    @endif

    {{-- ==================== MÉTRICAS ==================== --}}
    @if ($mostrarMetricas)
        <div class="section">
            <div class="section-title">Métricas detalladas</div>
            <div class="section-divider"></div>

            <table class="metrics">
                <tbody>
                    <tr>
                        <td>Total de consultas</td>
                        <td class="value">{{ number_format($resumen['total_consultas'] ?? 0) }}</td>
                    </tr>
                    <tr>
                        <td>Pacientes únicos atendidos</td>
                        <td class="value">{{ number_format($resumen['total_pacientes'] ?? 0) }}</td>
                    </tr>
                    <tr>
                        <td>Recetas emitidas</td>
                        <td class="value">{{ number_format($resumen['total_recetas'] ?? 0) }}</td>
                    </tr>
                    <tr>
                        <td>Medicamentos dispensados</td>
                        <td class="value">{{ number_format($resumen['total_dispensados'] ?? 0, 1) }} und</td>
                    </tr>
                    <tr>
                        <td>Tasa de consultas con receta</td>
                        <td class="value">{{ $resumen['tasa_con_receta'] ?? 0 }}%</td>
                    </tr>
                    <tr>
                        <td>Tasa de recetas dispensadas</td>
                        <td class="value">{{ $resumen['tasa_dispensada'] ?? 0 }}%</td>
                    </tr>
                    <tr>
                        <td>Promedio semanal</td>
                        <td class="value">{{ $resumen['promedio_semanal'] ?? 0 }} consultas</td>
                    </tr>
                    <tr>
                        <td>Hora pico</td>
                        <td class="value">{{ $resumen['hora_pico'] ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td>Promedio de edad</td>
                        <td class="value">{{ $resumen['edades']['promedio'] ?? 0 }} años</td>
                    </tr>
                    <tr>
                        <td>Mediana de edad</td>
                        <td class="value">{{ $resumen['edades']['mediana'] ?? 0 }} años</td>
                    </tr>
                    <tr>
                        <td>Moda de edad</td>
                        <td class="value">{{ $resumen['edades']['moda'] ?? 0 }} años</td>
                    </tr>
                    <tr>
                        <td>Comparativa vs. período anterior</td>
                        @php $comp = $resumen['comparativa_consultas'] ?? 0; @endphp
                        <td class="value {{ $comp > 0 ? 'kpi-positive' : ($comp < 0 ? 'kpi-negative' : '') }}">
                            {{ $comp > 0 ? '+' : '' }}{{ $comp }}%
                        </td>
                    </tr>

                    <tr class="divider">
                        <td colspan="2">Distribución por género</td>
                    </tr>
                    <tr>
                        <td>Masculino</td>
                        <td class="value">{{ $resumen['genero']['masculino'] ?? 0 }}</td>
                    </tr>
                    <tr>
                        <td>Femenino</td>
                        <td class="value">{{ $resumen['genero']['femenino'] ?? 0 }}</td>
                    </tr>
                    <tr>
                        <td>Otro / No especificado</td>
                        <td class="value">{{ $resumen['genero']['otro'] ?? 0 }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    @endif

    {{-- ==================== LISTADO CONSULTAS ==================== --}}
    @if (($reportType ?? '') === 'consultas' && !empty($consultas))
        <div class="section">
            <div class="section-title">Listado de consultas</div>
            <div class="section-divider"></div>

            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 10%;">Fecha</th>
                        <th style="width: 22%;">Paciente</th>
                        <th style="width: 10%;">Cédula</th>
                        <th style="width: 20%;">Médico</th>
                        <th style="width: 13%;">Consultorio</th>
                        <th style="width: 15%;">Enfermedades</th>
                        <th style="width: 10%;">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($consultas as $c)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($c->fecha)->format('d/m/Y') }}</td>
                            <td class="strong">
                                {{ trim(($c->paciente->nombre_persona ?? '') . ' ' . ($c->paciente->apellido_persona ?? '')) ?: '—' }}
                            </td>
                            <td>{{ $c->paciente->cedula_persona ?? '—' }}</td>
                            <td>{{ trim(($c->medico->nombre_persona ?? '') . ' ' . ($c->medico->apellido_persona ?? '')) ?: '—' }}
                            </td>
                            <td>{{ $c->consultorio->nombre ?? '—' }}</td>
                            <td>
                                @foreach ($c->enfermedades as $enf)
                                    <div>{{ $enf->nombre }}</div>
                                @endforeach
                            </td>
                            <td>
                                @if ($c->receta)
                                    <span class="estado {{ $c->receta->estado == 2 ? '' : 'incompleto' }}">
                                        {{ $c->receta->estado == 2 ? 'Completada' : 'En proceso' }}
                                    </span>
                                @else
                                    <span class="estado sin">Sin receta</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- ==================== LISTADO RECETAS ==================== --}}
    @if (($reportType ?? '') === 'recetas' && !empty($consultas))
        <div class="section">
            <div class="section-title">Listado de recetas</div>
            <div class="section-divider"></div>

            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 12%;">Fecha</th>
                        <th style="width: 12%;">N° Receta</th>
                        <th style="width: 26%;">Paciente</th>
                        <th style="width: 22%;">Médico</th>
                        <th style="width: 10%;">Ítems</th>
                        <th style="width: 18%;">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($consultas as $c)
                        @if ($c->receta)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($c->receta->fecha)->format('d/m/Y') }}</td>
                                <td class="strong">#{{ str_pad($c->receta->id, 5, '0', STR_PAD_LEFT) }}</td>
                                <td>{{ trim(($c->paciente->nombre_persona ?? '') . ' ' . ($c->paciente->apellido_persona ?? '')) }}
                                </td>
                                <td>{{ trim(($c->medico->nombre_persona ?? '') . ' ' . ($c->medico->apellido_persona ?? '')) }}
                                </td>
                                <td class="center">{{ $c->receta->detalles->count() }}</td>
                                <td>
                                    <span class="estado {{ $c->receta->estado == 2 ? '' : 'incompleto' }}">
                                        {{ $c->receta->estado == 2 ? 'Completada' : 'En proceso' }}
                                    </span>
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- ==================== LISTADO DISPENSACIONES ==================== --}}
    @if (($reportType ?? '') === 'dispensaciones' && !empty($consultas))
        <div class="section">
            <div class="section-title">Detalle de dispensaciones</div>
            <div class="section-divider"></div>

            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 12%;">Fecha</th>
                        <th style="width: 24%;">Paciente</th>
                        <th style="width: 26%;">Producto</th>
                        <th style="width: 10%;">Cantidad</th>
                        <th style="width: 13%;">Lote</th>
                        <th style="width: 15%;">Dispensado por</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalItems = 0; @endphp
                    @foreach ($consultas as $c)
                        @if ($c->receta)
                            @foreach ($c->receta->detalles as $det)
                                @foreach ($det->dispensaciones as $disp)
                                    @php $totalItems++; @endphp
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($disp->fecha)->format('d/m/Y') }}</td>
                                        <td>{{ trim(($c->paciente->nombre_persona ?? '') . ' ' . ($c->paciente->apellido_persona ?? '')) }}
                                        </td>
                                        <td>{{ $det->producto->nombre ?? '—' }}</td>
                                        <td class="center">{{ number_format($disp->cantidad, 2) }}</td>
                                        <td>{{ $disp->lote->codigo ?? '—' }}</td>
                                        <td>{{ trim(($disp->usuario->persona->nombre_persona ?? '') . ' ' . ($disp->usuario->persona->apellido_persona ?? '')) ?: '—' }}
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        @endif
                    @endforeach
                    <tr class="total">
                        <td colspan="3" style="text-align: right;">Total de dispensaciones</td>
                        <td class="center">{{ $totalItems }}</td>
                        <td colspan="2"></td>
                    </tr>
                </tbody>
            </table>
        </div>
    @endif

    {{-- ============================================================ --}}
    {{-- FIRMAS --}}
    {{-- ============================================================ --}}
    <div class="signatures">
        <table>
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
    </div>

    {{-- ============================================================ --}}
    {{-- FOOTER --}}
    {{-- ============================================================ --}}
    <div class="footer">
        <table>
            <tr>
                <td class="left">
                    UPTP · Reporte Estadístico del Módulo Salud · {{ now()->format('d/m/Y H:i') }}
                </td>
                <td class="right">
                    Página <span class="page-num"></span>
                </td>
            </tr>
        </table>
    </div>

</body>

</html>
