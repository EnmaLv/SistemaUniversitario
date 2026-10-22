<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Horarios de los espacios deportivos</title>
    <style>
        @page { size: a4 landscape; margin: 10mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #111827; }
        h1 { margin: 0; font-size: 16px; text-align: center; color: #7f1d1d; text-transform: uppercase; }
        .sub { text-align: center; margin: 3px 0 10px; color: #374151; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th { background: #7f1d1d; color: #fff; padding: 5px 3px; font-size: 9px; text-transform: uppercase; }
        td { border: 1px solid #d1d5db; padding: 3px; vertical-align: top; }
        .espacio { font-weight: bold; width: 13%; }
        .evento { border-left: 3px solid #7f1d1d; background: #fef2f2; padding: 2px 3px; margin-bottom: 3px; }
        .recurrente { border-left-color: #0284c7; background: #f0f9ff; }
        .torneo { border-left-color: #d97706; background: #fffbeb; }
        .hora { font-weight: bold; }
        .libre { color: #9ca3af; text-align: center; }
        .pie { margin-top: 8px; font-size: 8px; color: #6b7280; }
    </style>
</head>
<body>
    <h1>Horarios de los espacios deportivos</h1>
    <p class="sub">Semana del {{ $lunes->format('d/m/Y') }} al {{ $domingo->format('d/m/Y') }}</p>

    <table>
        <thead>
            <tr>
                <th class="espacio">Espacio</th>
                @foreach ($dias as $dia)
                    <th>{{ $dia['nombre'] }} {{ $dia['fecha']->format('d/m') }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($filas as $espacio)
                <tr>
                    <td class="espacio">{{ $espacio->nombre_epd }}</td>
                    @foreach ($dias as $dia)
                        <td>
                            @forelse ($celdas[$espacio->id_epd . '|' . $dia['clave']] ?? [] as $evento)
                                <div class="evento {{ $evento->torneo ? 'torneo' : ($evento->actividad->esRecurrente() ? 'recurrente' : '') }}">
                                    <span class="hora">{{ $evento->bloque->horaInicio('g:i A') }} - {{ $evento->bloque->horaFin('g:i A') }}</span><br>
                                    {{ $evento->disciplina }}<br>
                                    {{ \Illuminate\Support\Str::limit($evento->nombre, 45) }}
                                </div>
                            @empty
                                <div class="libre">Libre</div>
                            @endforelse
                        </td>
                    @endforeach
                </tr>
            @empty
                <tr><td colspan="8" class="libre">No hay espacios deportivos registrados.</td></tr>
            @endforelse
        </tbody>
    </table>

    <p class="pie">Rojo: una sola vez · Azul: se repite cada semana · Naranja: torneo. Generado el {{ now()->format('d/m/Y h:i A') }}.</p>
</body>
</html>
