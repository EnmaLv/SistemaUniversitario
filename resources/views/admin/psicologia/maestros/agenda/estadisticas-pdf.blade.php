<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Estadísticas de Citas</title>
    <style>
        body {
            font-family: 'Nunito', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #334155;
            margin: 30px;
            padding: 0;
        }
        @page { margin: 80px 20px 80px 20px; }
        header { position: fixed; top: -60px; left: 0px; right: 0px; height: 50px; text-align: center; }
        footer { position: fixed; bottom: -60px; left: 0px; right: 0px; height: 50px; text-align: center; }
        
        .page-number:after { content: counter(page); }
        .footer-text { font-size: 10px; color: #94a3b8; }
        
        .content { margin-top: 10px; }
        
        .title {
            color: #0f172a;
            font-size: 20px;
            font-weight: 900;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .subtitle {
            color: #64748b;
            font-size: 12px;
            margin-bottom: 20px;
        }
        
        .filters-container {
            margin-bottom: 25px;
        }
        
        .filter-item {
            font-size: 12px;
            margin-bottom: 5px;
        }
        
        .filter-label {
            font-weight: bold;
            color: #1b1b1bff;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin-bottom: 30px;
        }
        
        th {
            background-color: #f1f5f9;
            color: #334155;
            font-weight: bold;
            text-align: left;
            padding: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #cbd5e1;
        }
        
        td {
            padding: 10px;
            border: 1px solid #e2e8f0;
            color: #475569;
        }
        
        .badge {
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .badge-confirmada { background-color: #dcfce7; color: #166534; }
        .badge-pendiente { background-color: #fef9c3; color: #854d0e; }
        .badge-realizada { background-color: #dbeafe; color: #1e40af; }
        .badge-cancelada { background-color: #ffe4e6; color: #be123c; }
        .badge-rechazada { background-color: #fce7f3; color: #9d174d; }
        .badge-no_asistio { background-color: #ffedd5; color: #c2410c; }
        
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        
        .summary-table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }
        
        .summary-table th {
            background-color: #f8fafc;
            color: #334155;
            text-align: center;
            font-size: 12px;
            padding: 8px;
            border: 1px solid #e2e8f0;
            border-bottom: 2px solid #cbd5e1;
        }
        
        .summary-table td {
            padding: 8px 15px;
            border: 1px solid #e2e8f0;
        }
        
        .summary-table .summary-label {
            font-weight: bold;
            text-align: left;
            width: 80%;
        }
        
        .summary-table .summary-value {
            text-align: center;
            font-weight: bold;
        }
        
        .section-header {
            background-color: #e2e8f0;
            font-size: 12px;
            text-align: left;
            padding-left: 15px;
        }

        .chart-container {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 15px;
            margin: 15px 0;
        }
        
        .chart-title {
            font-weight: 900;
            text-align: center;
            color: #334155;
            font-size: 13px;
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .chart-axis-container {
            width: 100%;
            max-width: 100%;
            margin: 0 auto;
            border-left: 2px solid #cbd5e1;
            border-bottom: 2px solid #cbd5e1;
            border-radius: 0 0 0 8px;
            padding: 15px 10px 5px 10px;
            background-color: #ffffff;
        }

        .clear { clear: both; }

        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.05;
            width: 60%;
            z-index: -1;
        }
    </style>
</head>
<body>
    
    <header>
        <img src="{{ public_path('img/encabezado.png') }}" style="width: 94%; max-height: 50px; object-fit: contain;" alt="Encabezado" onerror="this.style.display='none'">
    </header>

    <footer>
        <img src="{{ public_path('img/pie.png') }}" style="width: 94%; max-height: 50px; object-fit: contain;" alt="Pie de Página" onerror="this.style.display='none'">
    </footer>

    <main class="content">
        <div style="text-align: center; margin-bottom: 20px;">
            <div class="title">
                @if(isset($reportType) && $reportType === 'citas_estados')
                    Reporte de Citas y Estados
                @elseif(isset($reportType) && $reportType === 'demografico')
                    Reporte Demográfico de Pacientes
                @elseif(isset($reportType) && $reportType === 'operativo')
                    Reporte de Métricas Operativas
                @elseif(isset($reportType) && $reportType === 'clinico')
                    Reporte Clínico y de Seguimiento
                @else
                    Reporte Completo de Estadísticas
                @endif
            </div>
            <div class="subtitle">Psicólogo: {{ $psicologo->nombre_completo ?? '' }}</div>
        </div>
        <div class="filters-container">
            <div class="filter-item">
                <span class="filter-label">Período ({{ ucfirst($periodo ?? 'Mensual') }}):</span> 
                {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}
            </div>
            <div class="filter-item">
                <span class="filter-label">Estado filtrado:</span> 
                @if($estado)
                    <span class="badge badge-{{ $estado }}">{{ ucfirst(str_replace('_', ' ', $estado)) }}</span>
                @else
                    Todos los estados
                @endif
            </div>
            @if(isset($avance_nombre))
            <div class="filter-item">
                <span class="filter-label">Avance de Sesión:</span> 
                {{ $avance_nombre }}
            </div>
            @endif
            @if(isset($estado_animo_nombre))
            <div class="filter-item">
                <span class="filter-label">Estado de Ánimo:</span> 
                {{ $estado_animo_nombre }}
            </div>
            @endif
            @if(isset($prioridad) && $prioridad)
            <div class="filter-item">
                <span class="filter-label">Prioridad:</span> 
                {{ ucfirst($prioridad) }}
            </div>
            @endif
        </div>

        @if(!isset($reportType) || $reportType === 'completo' || $reportType === 'citas_estados')
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Paciente</th>
                    <th>F. Solicitud</th>
                    <th>H. Solicitud</th>
                    <th>F. Cita</th>
                    <th>H. Cita</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse($citas as $cita)
                    @php
                        $fechaSolicitada = $cita->created_at_carbon ? $cita->created_at_carbon->format('d/m/Y') : 'N/A';
                        $horaSolicitada = $cita->created_at_carbon ? $cita->created_at_carbon->format('h:i A') : 'N/A';
                        
                        $fechaProgramada = ($cita->fecha_carbon && !in_array($cita->estado_cita_cps, ['pendiente', 'rechazada'])) ? $cita->fecha_carbon->format('d/m/Y') : 'N/A';
                        $horaProgramada = ($cita->hora_cps && !in_array($cita->estado_cita_cps, ['pendiente', 'rechazada'])) ? \Carbon\Carbon::parse($cita->hora_cps)->format('h:i A') : 'N/A';
                    @endphp
                    <tr>
                        <td>#{{ $cita->id_cps }}</td>
                        <td>{{ $cita->paciente_nombre }}</td>
                        <td>{{ $fechaSolicitada }}</td>
                        <td>{{ $horaSolicitada }}</td>
                        <td>{{ $fechaProgramada }}</td>
                        <td>{{ $horaProgramada }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $cita->estado_cita_cps)) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">No se encontraron citas en el período seleccionado.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @endif
        
        @php
            $totales = [];
            foreach($citas as $cita) {
                $estado = $cita->estado_cita_cps;
                if ($estado === 'cancelada') {
                    $estado = $cita->cancelado_por_cps === 'psicologo' ? 'cancelada_por_psicólogo' : 'cancelada_por_paciente';
                }
                if(!isset($totales[$estado])) {
                    $totales[$estado] = 0;
                }
                $totales[$estado]++;
            }
        @endphp

        @if(isset($resumen))
        <table class="summary-table">
            <thead>
                <tr>
                    <th colspan="2" style="font-size: 14px;">
                        @if(!isset($reportType) || $reportType === 'completo')
                            Resumen Detallado de Estadísticas
                        @elseif($reportType === 'citas_estados')
                            Resumen de Estados de Cita
                        @elseif($reportType === 'demografico')
                            Análisis Demográfico
                        @elseif($reportType === 'operativo')
                            Análisis de Métricas Operativas
                        @elseif($reportType === 'clinico')
                            Análisis Clínico y de Seguimiento
                        @endif
                    </th>
                </tr>
            </thead>
            <tbody>
                @if(!isset($reportType) || $reportType === 'completo' || $reportType === 'citas_estados')
                <!-- Citas totales y Estados -->
                @foreach($totales as $estado => $cantidad)
                    <tr>
                        <td class="summary-label">Citas {{ ucfirst(str_replace('_', ' ', $estado)) }}:</td>
                        <td class="summary-value">{{ $cantidad }}</td>
                    </tr>
                @endforeach
                <tr style="background-color: #f1f5f9;">
                    <td class="summary-label" style="font-size: 13px;">TOTAL DE CITAS:</td>
                    <td class="summary-value" style="font-size: 13px;">{{ $resumen['total_citas'] }}</td>
                </tr>
                <tr>
                    <td colspan="2" style="padding: 20px 10px;">
                        <div class="chart-container">
                            <div class="chart-title">Distribución por Estado de Cita</div>
                            <div class="chart-axis-container">
                                @php
                                    $maxCountEstado = empty($totales) ? 1 : max(1, max($totales));
                                    $estadoColors = ['#0ea5e9','#10b981','#f43f5e','#f59e0b','#8b5cf6','#64748b'];
                                    $idxEstado = 0;
                                @endphp
                                <table style="width: 100%; border: none; margin: 0; padding: 0;">
                                    <tr style="border: none;">
                                    @forelse($totales as $estado => $cantidad)
                                        @php
                                            $height = max(4, ($cantidad / $maxCountEstado) * 110);
                                            $shortLabel = ucfirst(str_replace('_', ' ', str_replace('cancelada por ', '', $estado)));
                                            if ($estado === 'cancelada_por_psicólogo') $shortLabel = 'Canc. Psico';
                                            if ($estado === 'cancelada_por_paciente') $shortLabel = 'Canc. Paciente';
                                        @endphp
                                        <td style="vertical-align: bottom; text-align: center; border: none; padding: 0 3px;">
                                            <div style="font-size: 10px; color: #334155; font-weight: 900; margin-bottom: 4px;">{{ $cantidad }}</div>
                                            <div style="background-color: {{ $estadoColors[$idxEstado % 6] }}; width: 24px; height: {{ $height }}px; margin: 0 auto; border-radius: 6px 6px 0 0; opacity: 0.8;"></div>
                                            <div style="font-size: 8px; margin-top: 6px; color: #475569; font-weight: 700; white-space: nowrap;">{{ $shortLabel }}</div>
                                        </td>
                                        @php $idxEstado++; @endphp
                                    @empty
                                        <td style="text-align: center; border: none; padding: 20px; color: #94a3b8; font-size: 11px;">No hay datos suficientes</td>
                                    @endforelse
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </td>
                </tr>
                @endif

                @if(!isset($reportType) || $reportType === 'completo' || $reportType === 'demografico')
                <tr>
                    <td class="summary-label">Total de Pacientes Únicos atendidos:</td>
                    <td class="summary-value">{{ $resumen['total_pacientes'] }}</td>
                </tr>
                
                <!-- Género -->
                <tr><th colspan="2" class="section-header">Distribución por Género</th></tr>
                <tr>
                    <td class="summary-label" style="padding-left: 25px; font-weight: normal;">- Hombres:</td>
                    <td class="summary-value">{{ $resumen['genero']['masculino'] }}</td>
                </tr>
                <tr>
                    <td class="summary-label" style="padding-left: 25px; font-weight: normal;">- Mujeres:</td>
                    <td class="summary-value">{{ $resumen['genero']['femenino'] }}</td>
                </tr>
                
                <!-- Edad -->
                <tr><th colspan="2" class="section-header">Rangos de Edad</th></tr>
                @foreach($resumen['edades']['rangos'] as $rango => $cantidad)
                <tr>
                    <td class="summary-label" style="padding-left: 25px; font-weight: normal;">- {{ $rango }} años:</td>
                    <td class="summary-value">{{ $cantidad }}</td>
                </tr>
                @endforeach
                <tr style="background-color: #f8fafc;">
                    <td class="summary-label" style="padding-left: 15px;">Promedio de Edad:</td>
                    <td class="summary-value">{{ $resumen['edades']['promedio'] }} años</td>
                </tr>
                <tr style="background-color: #f8fafc;">
                    <td class="summary-label" style="padding-left: 15px;">Mediana de Edad:</td>
                    <td class="summary-value">{{ $resumen['edades']['mediana'] }} años</td>
                </tr>
                <tr style="background-color: #f8fafc;">
                    <td class="summary-label" style="padding-left: 15px;">Moda de Edad:</td>
                    <td class="summary-value">{{ $resumen['edades']['moda'] }} años</td>
                </tr>

                <!-- Perfil Institucional / Académico -->
                <tr><th colspan="2" class="section-header">Perfil Institucional / Académico</th></tr>
                @foreach($resumen['perfil_academico'] as $rol => $cantidad)
                <tr>
                    <td class="summary-label" style="padding-left: 25px; font-weight: normal;">- {{ $rol }}:</td>
                    <td class="summary-value">{{ $cantidad }}</td>
                </tr>
                @endforeach

                <!-- Pacientes de acuerdo al PNF -->
                <tr><th colspan="2" class="section-header">Pacientes de acuerdo al PNF</th></tr>
                @php
                    $pnfLabels = [
                        'Administracion' => 'Administración',
                        'Mecanica' => 'Mecánica',
                        'Mantenimiento' => 'Mantenimiento',
                        'Electricidad' => 'Electricidad',
                        'Veterinaria' => 'Veterinaria',
                        'Informatica' => 'Informática',
                        'PDA' => 'PDA',
                        'Distribucion_Logistica' => 'Distribución y Logística',
                        'Agroalimentacion' => 'Agroalimentación',
                        'Seguridad_Alimentaria_Nutricional' => 'Seguridad alimentaria y Cultura Nutricional',
                        'No especificado' => 'No especificado',
                        'No aplica' => 'No aplica'
                    ];
                @endphp
                @foreach($resumen['pnf'] as $pnfKey => $cantidad)
                <tr>
                    <td class="summary-label" style="padding-left: 25px; font-weight: normal;">- {{ $pnfLabels[$pnfKey] ?? $pnfKey }}:</td>
                    <td class="summary-value">{{ $cantidad }}</td>
                </tr>
                @endforeach

                <!-- Histograma de Edad -->
                @if(array_sum($resumen['edades']['rangos']) > 0)
                <tr>
                    <td colspan="2" style="padding: 20px 10px;">
                        <div class="chart-container">
                            <div class="chart-title">Distribución de Edades de Pacientes</div>
                            <div class="chart-axis-container">
                                @php
                                    $maxCount = max(1, max($resumen['edades']['rangos']));
                                    $edadColors = ['#6366f1','#0ea5e9','#10b981','#f59e0b','#f43f5e'];
                                    $idx = 0;
                                @endphp
                                <table style="width: 100%; border: none; margin: 0; padding: 0;">
                                    <tr style="border: none;">
                                    @foreach($resumen['edades']['rangos'] as $rango => $cantidad)
                                        @php $height = max(4, ($cantidad / $maxCount) * 110); @endphp
                                        <td style="vertical-align: bottom; text-align: center; border: none; padding: 0 6px; width: 20%;">
                                            <div style="font-size: 11px; color: #334155; font-weight: 900; margin-bottom: 4px;">{{ $cantidad }}</div>
                                            <div style="background-color: {{ $edadColors[$idx % 5] }}; width: 70%; height: {{ $height }}px; margin: 0 auto; border-radius: 6px 6px 0 0; opacity: 0.85;"></div>
                                            <div style="font-size: 10px; margin-top: 6px; color: #475569; font-weight: 700;">{{ $rango }}</div>
                                        </td>
                                        @php $idx++; @endphp
                                    @endforeach
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </td>
                </tr>
                @endif
                @endif

                @if(!isset($reportType) || $reportType === 'completo' || $reportType === 'operativo')
                <!-- Métricas Avanzadas -->
                <tr><th colspan="2" class="section-header">Métricas Avanzadas</th></tr>
                <tr style="background-color: #f8fafc;">
                    <td class="summary-label" style="padding-left: 15px;">Hora Pico (Moda):</td>
                    <td class="summary-value">{{ $resumen['hora_pico'] }}</td>
                </tr>
                <tr style="background-color: #f8fafc;">
                    <td class="summary-label" style="padding-left: 15px;">Volumen Promedio Semanal:</td>
                    <td class="summary-value">{{ $resumen['promedio_semanal'] }} citas/semana</td>
                </tr>
                <tr style="background-color: #f8fafc;">
                    <td class="summary-label" style="padding-left: 15px;">Tasa de Asistencia:</td>
                    <td class="summary-value">{{ $resumen['tasa_asistencia'] }}%</td>
                </tr>
                <tr style="background-color: #f8fafc;">
                    <td class="summary-label" style="padding-left: 15px;">Tiempo de Espera Promedio:</td>
                    <td class="summary-value">{{ $resumen['tiempo_espera_promedio'] }} días</td>
                </tr>
                <tr style="background-color: #f8fafc;">
                    <td class="summary-label" style="padding-left: 15px;">Comparativa Mensual (Pacientes):</td>
                    <td class="summary-value">
                        @if($resumen['comparativa_pacientes'] > 0)
                            <span style="color: #16a34a;">+{{ $resumen['comparativa_pacientes'] }}%</span>
                        @elseif($resumen['comparativa_pacientes'] < 0)
                            <span style="color: #dc2626;">{{ $resumen['comparativa_pacientes'] }}%</span>
                        @else
                            <span style="color: #64748b;">0% (Sin cambios)</span>
                        @endif
                    </td>
                </tr>

                <!-- Distribución de Horas -->
                @if(!empty($resumen['distribucion_horas']))
                <tr>
                    <td colspan="2" style="padding: 20px 10px;">
                        <div class="chart-container">
                            <div class="chart-title">Distribución por Horas de Atención</div>
                            <div class="chart-axis-container">
                                @php
                                    $maxCountHoras = empty($resumen['distribucion_horas']) ? 1 : max(1, max($resumen['distribucion_horas']));
                                    $horaCount = count($resumen['distribucion_horas']);
                                    $colWidth = $horaCount > 0 ? max(30, floor(480 / $horaCount)) : 60;
                                @endphp
                                <table style="width: 100%; border: none; margin: 0; padding: 0;">
                                    <tr style="border: none;">
                                    @foreach($resumen['distribucion_horas'] as $bloque => $cantidad)
                                        @php
                                            $height = max(4, ($cantidad / $maxCountHoras) * 110);
                                            // Extract just the start hour for compact label
                                            $labelParts = explode(' - ', $bloque);
                                            $shortLabel = $labelParts[0] ?? $bloque;
                                        @endphp
                                        <td style="vertical-align: bottom; text-align: center; border: none; padding: 0 3px;">
                                            <div style="font-size: 10px; color: #334155; font-weight: 900; margin-bottom: 4px;">{{ $cantidad }}</div>
                                            <div style="background-color: #10b981; width: 24px; height: {{ $height }}px; margin: 0 auto; border-radius: 6px 6px 0 0; opacity: 0.8;"></div>
                                            <div style="font-size: 8px; margin-top: 6px; color: #475569; font-weight: 700; white-space: nowrap;">{{ $shortLabel }}</div>
                                        </td>
                                    @endforeach
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </td>
                </tr>
                @endif

                <!-- Flujo Semanal -->
                @if(!empty($resumen['flujo_semanal']))
                <tr>
                    <td colspan="2" style="padding: 20px 10px;">
                        <div class="chart-container">
                            <div class="chart-title">Flujo de Pacientes por Semana</div>
                            <div class="chart-axis-container">
                                @php
                                    $maxCountSemana = empty($resumen['flujo_semanal']) ? 1 : max(1, max($resumen['flujo_semanal']));
                                @endphp
                                <table style="width: 100%; border: none; margin: 0; padding: 0;">
                                    <tr style="border: none;">
                                    @foreach($resumen['flujo_semanal'] as $semana => $cantidad)
                                        @php
                                            $height = max(4, ($cantidad / $maxCountSemana) * 110);
                                            $weekParts = explode('-', $semana);
                                            $weekNum = $weekParts[0];
                                        @endphp
                                        <td style="vertical-align: bottom; text-align: center; border: none; padding: 0 6px;">
                                            <div style="font-size: 11px; color: #334155; font-weight: 900; margin-bottom: 4px;">{{ $cantidad }}</div>
                                            <div style="background-color: #0ea5e9; width: 32px; height: {{ $height }}px; margin: 0 auto; border-radius: 6px 6px 0 0; opacity: 0.8;"></div>
                                            <div style="font-size: 9px; margin-top: 6px; color: #475569; font-weight: 700;">Sem {{ $weekNum }}</div>
                                        </td>
                                    @endforeach
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </td>
                </tr>
                @endif
                @endif

                @if(!isset($reportType) || $reportType === 'completo' || $reportType === 'clinico')
                <!-- Avances -->
                <tr><th colspan="2" class="section-header">Avances Clínicos (basado en la última sesión del periodo)</th></tr>
                @foreach($resumen['avances'] as $avance => $cantidad)
                <tr>
                    <td class="summary-label" style="padding-left: 25px; font-weight: normal;">- {{ $avance }}:</td>
                    <td class="summary-value">{{ $cantidad }}</td>
                </tr>
                @endforeach

                <!-- Prioridades -->
                <tr><th colspan="2" class="section-header">Pacientes por Prioridad de Atención</th></tr>
                @foreach($resumen['prioridades'] as $prioridad => $cantidad)
                <tr>
                    <td class="summary-label" style="padding-left: 25px; font-weight: normal;">- {{ $prioridad }}:</td>
                    <td class="summary-value">{{ $cantidad }}</td>
                </tr>
                @endforeach

                <!-- Prioridades Chart -->
                @if(array_sum($resumen['prioridades']) > 0)
                <tr>
                    <td colspan="2" style="padding: 20px 10px;">
                        <div class="chart-container">
                            <div class="chart-title">Distribución de Pacientes por Prioridad</div>
                            <div class="chart-axis-container">
                                @php
                                    $maxCountPrio = max(1, max($resumen['prioridades']));
                                    $prioColors = ['#f43f5e','#f59e0b','#10b981','#0ea5e9','#6366f1'];
                                    $idx = 0;
                                @endphp
                                <table style="width: 100%; border: none; margin: 0; padding: 0;">
                                    <tr style="border: none;">
                                    @foreach($resumen['prioridades'] as $rango => $cantidad)
                                        @php $height = max(4, ($cantidad / $maxCountPrio) * 110); @endphp
                                        <td style="vertical-align: bottom; text-align: center; border: none; padding: 0 6px;">
                                            <div style="font-size: 11px; color: #334155; font-weight: 900; margin-bottom: 4px;">{{ $cantidad }}</div>
                                            <div style="background-color: {{ $prioColors[$idx % 5] }}; width: 32px; height: {{ $height }}px; margin: 0 auto; border-radius: 6px 6px 0 0; opacity: 0.85;"></div>
                                            <div style="font-size: 9px; margin-top: 6px; color: #475569; font-weight: 700;">{{ $rango }}</div>
                                        </td>
                                        @php $idx++; @endphp
                                    @endforeach
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </td>
                </tr>
                @endif

                <!-- Estados de Animo -->
                <tr><th colspan="2" class="section-header">Pacientes por Estado de Ánimo</th></tr>
                @foreach($resumen['estados_animo'] as $estadoAnimo => $cantidad)
                <tr>
                    <td class="summary-label" style="padding-left: 25px; font-weight: normal;">- {{ $estadoAnimo }}:</td>
                    <td class="summary-value">{{ $cantidad }}</td>
                </tr>
                @endforeach

                <!-- Estados de Animo Chart -->
                @if(array_sum($resumen['estados_animo']) > 0)
                <tr>
                    <td colspan="2" style="padding: 20px 10px;">
                        <div class="chart-container">
                            <div class="chart-title">Distribución de Pacientes por Estado de Ánimo</div>
                            <div class="chart-axis-container">
                                @php
                                    $maxCountAnimo = max(1, max($resumen['estados_animo']));
                                    $animoColors = ['#8b5cf6','#ec4899','#14b8a6','#f97316','#84cc16', '#3b82f6'];
                                    $idx = 0;
                                @endphp
                                <table style="width: 100%; border: none; margin: 0; padding: 0;">
                                    <tr style="border: none;">
                                    @foreach($resumen['estados_animo'] as $rango => $cantidad)
                                        @php $height = max(4, ($cantidad / $maxCountAnimo) * 110); @endphp
                                        <td style="vertical-align: bottom; text-align: center; border: none; padding: 0 2px;">
                                            <div style="font-size: 10px; color: #334155; font-weight: 900; margin-bottom: 4px;">{{ $cantidad }}</div>
                                            <div style="background-color: {{ $animoColors[$idx % 6] }}; width: 18px; height: {{ $height }}px; margin: 0 auto; border-radius: 6px 6px 0 0; opacity: 0.85;"></div>
                                            <div style="font-size: 8px; margin-top: 6px; color: #475569; font-weight: 700; max-width: 42px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $rango }}</div>
                                        </td>
                                        @php $idx++; @endphp
                                    @endforeach
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </td>
                </tr>
                @endif
                @endif
            </tbody>
        </table>
        @else
        <table class="summary-table">
            <thead>
                <tr>
                    <th colspan="2">Resumen de Estadísticas</th>
                </tr>
            </thead>
            <tbody>
                @foreach($totales as $estado => $cantidad)
                    <tr>
                        <td class="summary-label">Citas {{ ucfirst(str_replace('_', ' ', $estado)) }}:</td>
                        <td class="summary-value">{{ $cantidad }}</td>
                    </tr>
                @endforeach
                <tr style="background-color: #f1f5f9;">
                    <td class="summary-label" style="font-size: 13px;">TOTAL DE CITAS:</td>
                    <td class="summary-value" style="font-size: 13px;">{{ count($citas) }}</td>
                </tr>
            </tbody>
        </table>
        @endif
        
        <div class="clear"></div>
    </main>

    <script type="text/php">
        if (isset($pdf)) {
            $x = 750;
            $y = 570;
            $text = "Página {PAGE_NUM} de {PAGE_COUNT} | Generado el {{ \Carbon\Carbon::now()->format('d/m/Y h:i A') }}";
            $font = $fontMetrics->get_font("helvetica", "normal");
            $size = 8;
            $color = array(0,0,0);
            $word_space = 0.0;
            $char_space = 0.0;
            $angle = 0.0;
            $pdf->page_text($x, $y, $text, $font, $size, $color, $word_space, $char_space, $angle);
        }
    </script>

</body>
</html>
