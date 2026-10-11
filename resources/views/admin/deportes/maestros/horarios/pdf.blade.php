<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Horarios - Deportes</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; }
    </style>
</head>
<body>
    <h2>Horarios - Deportes</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Calendario</th>
                <th>Actividad</th>
                <th>Espacio</th>
                <th>Día/Fecha</th>
                <th>Hora</th>
            </tr>
        </thead>
        <tbody>
            @foreach($horarios as $h)
                <tr>
                    <td>{{ $h->id }}</td>
                    <td>{{ $h->calendario->nombre ?? '' }}</td>
                    <td>{{ $h->actividad->nombre ?? '' }}</td>
                    <td>{{ $h->espacio->nombre ?? '' }}</td>
                    <td>{{ $h->fecha_especifica ? $h->fecha_especifica->format('d/m/Y') : $h->dia_semana }}</td>
                    <td>{{ \Carbon\Carbon::parse($h->hora_inicio)->format('g:i A') }} - {{ \Carbon\Carbon::parse($h->hora_fin)->format('g:i A') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
