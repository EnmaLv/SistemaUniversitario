<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Pacientes</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #333;
        }
        @page { margin: 80px 20px 80px 20px; }
        header { position: fixed; top: -60px; left: 0px; right: 0px; height: 50px; text-align: center; }
        footer { position: fixed; bottom: -60px; left: 0px; right: 0px; height: 50px; text-align: center; }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h2 {
            margin: 0;
            color: #111827;
        }
        .header p {
            margin: 5px 0 0;
            color: #666;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }
        th {
            background-color: #374151;
            color: white;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9fafb;
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
    <div class="header">
        <h2>Reporte de Pacientes - Historial Clínico</h2>
        @if(!empty($search))
            <p>Pacientes filtrados por: <strong>"{{ $search }}"</strong></p>
        @else
            <p>Listado general de pacientes atendidos</p>
        @endif
        
        @php
            $filtros = [];
            if (!empty($filterNames['fecha_desde']) && !empty($filterNames['fecha_hasta'])) {
                $filtros[] = 'Fechas: ' . \Carbon\Carbon::parse($filterNames['fecha_desde'])->format('d/m/Y') . ' al ' . \Carbon\Carbon::parse($filterNames['fecha_hasta'])->format('d/m/Y');
            }
            if (!empty($filterNames['pnf'])) $filtros[] = 'PNF: ' . $filterNames['pnf'];
            if (!empty($filterNames['edad'])) $filtros[] = 'Edad: ' . $filterNames['edad'];
            if (!empty($filterNames['enfermedad'])) $filtros[] = 'Enfermedad: ' . $filterNames['enfermedad'];
            if (!empty($filterNames['prioridad'])) $filtros[] = 'Prioridad: ' . $filterNames['prioridad'];
            if (!empty($filterNames['avance'])) $filtros[] = 'Avance: ' . $filterNames['avance'];
            if (!empty($filterNames['estado_animo'])) $filtros[] = 'Ánimo: ' . $filterNames['estado_animo'];
        @endphp

        @if(count($filtros) > 0)
            <p style="font-style: italic; color: #4B5563; font-size: 11px;">Filtros: {{ implode(' | ', $filtros) }}</p>
        @endif
        
        <p>Fecha de emisión: {{ date('d/m/Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Nombres</th>
                <th>Apellidos</th>
                <th>Cédula</th>
                <th>PNF / Carrera</th>
                <th>Edad</th>
                <th>F. Nacimiento</th>
                <th>Teléfono</th>
                <th>Sesiones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($historias as $historia)
                @php $paciente = $historia['paciente']; @endphp
                <tr>
                    <td>{{ $paciente->persona->nombres_per ?? '' }}</td>
                    <td>{{ $paciente->persona->persona_persona ?? '' }}</td>
                    <td>{{ $paciente->persona->cedula_per ?? '' }}</td>
                    <td>{{ $paciente->pnf ?? 'N/A' }}</td>
                    <td>{{ $paciente->persona->fecha_nacimiento_per ? \Carbon\Carbon::parse($paciente->fecha_nacimiento)->age : 'N/A' }}</td>
                    <td>{{ $paciente->persona->fecha_nacimiento_per ? \Carbon\Carbon::parse($paciente->fecha_nacimiento)->format('d/m/Y') : 'N/A' }}</td>
                    <td>{{ $paciente->persona->telefono_per ?? 'N/A' }}</td>
                    <td style="text-align: center;">{{ $historia['citas_realizadas'] ?? 0 }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    </main>
    <script type="text/php">
        if (isset($pdf)) {
            $x = 750;
            $y = 570;
            $text = "Página {PAGE_NUM} de {PAGE_COUNT}";
            $font = $fontMetrics->get_font("helvetica", "normal");
            $size = 9;
            $color = array(0,0,0);
            $word_space = 0.0;  //  default
            $char_space = 0.0;  //  default
            $angle = 0.0;   //  default
            $pdf->page_text($x, $y, $text, $font, $size, $color, $word_space, $char_space, $angle);
        }
    </script>
</body>
</html>
