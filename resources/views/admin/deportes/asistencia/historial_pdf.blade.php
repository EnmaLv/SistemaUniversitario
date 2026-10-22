<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Asistencia por persona</title>
    <style>
        @page { size: letter portrait; margin: 14mm 12mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111827; }
        h1 { margin: 0; font-size: 18px; text-align: center; color: #7f1d1d; text-transform: uppercase; }
        .sub { text-align: center; margin: 4px 0 14px; color: #374151; }
        .resumen { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        .resumen td { border: 1px solid #d1d5db; padding: 6px; text-align: center; }
        .resumen strong { display: block; font-size: 16px; }
        table.detalle { width: 100%; border-collapse: collapse; }
        table.detalle th { background: #7f1d1d; color: #fff; padding: 6px; font-size: 10px; text-transform: uppercase; }
        table.detalle td { border-bottom: 1px solid #e5e7eb; padding: 5px 6px; }
        .c { text-align: center; }
        .nota { margin-top: 10px; font-size: 10px; color: #6b7280; }
    </style>
</head>
<body>
    <h1>Asistencia por persona</h1>
    <p class="sub">
        <strong>{{ $actividad->nombre_cld }}</strong><br>
        {{ $actividad->descripcionCuando() }}{{ $actividad->sede ? ' · ' . $actividad->sede->nombre_sed : '' }}
    </p>

    <table class="resumen">
        <tr>
            <td>Sesiones cerradas<strong>{{ $historial['totalSesiones'] }}</strong></td>
            <td>Personas<strong>{{ $historial['filas']->count() }}</strong></td>
            <td>Asistencias<strong>{{ $historial['filas']->sum('presentes') }}</strong></td>
        </tr>
    </table>

    <table class="detalle">
        <thead>
            <tr>
                <th>#</th>
                <th>Cédula</th>
                <th>Nombre</th>
                <th>Asistió</th>
                <th>Faltó</th>
                <th>Sesiones</th>
                <th>%</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($historial['filas'] as $fila)
                <tr>
                    <td class="c">{{ $loop->iteration }}</td>
                    <td>{{ $fila->cedula_per }}</td>
                    <td>{{ $fila->nombres_per }} {{ $fila->apellidos_per }}</td>
                    <td class="c">{{ $fila->presentes }}</td>
                    <td class="c">{{ $fila->inasistentes }}</td>
                    <td class="c">{{ $fila->sesiones }}</td>
                    <td class="c">{{ $fila->porcentaje }}%</td>
                </tr>
            @empty
                <tr><td colspan="7" class="c">No hay sesiones con la asistencia cerrada.</td></tr>
            @endforelse
        </tbody>
    </table>

    <p class="nota">Solo cuentan las sesiones con la asistencia cerrada. Generado el {{ now()->format('d/m/Y h:i A') }}.</p>
</body>
</html>
