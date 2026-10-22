<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $resultado['solvente'] ? 'Constancia de solvencia' : 'Constancia de no solvencia' }}</title>
    <style>
        @page { margin: 100px 60px 65px 60px; }
        header { position: fixed; top: -60px; left: 0; right: 0; height: 50px; text-align: center; }
        footer { position: fixed; bottom: -60px; left: 0; right: 0; height: 50px; text-align: center; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #111827; font-size: 12px; line-height: 1.6; margin: 0; padding: 10px 20px; }
        .titulo { font-size: 16px; font-weight: bold; text-transform: uppercase; text-align: center; margin: 10px 0 25px; }
        .estado { text-align: center; font-size: 20px; font-weight: bold; padding: 10px; margin: 15px 0; border-radius: 6px; color: #fff; }
        .solvente { background-color: #059669; }
        .no-solvente { background-color: #b91c1c; }
        .alerta { border: 2px solid #b91c1c; background-color: #fef2f2; color: #7f1d1d; padding: 10px 14px; font-weight: bold; margin: 15px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #d1d5db; padding: 6px 8px; text-align: left; vertical-align: top; }
        th { background-color: #f3f4f6; font-size: 10px; text-transform: uppercase; }
        .meta td { border: none; padding: 2px 0; }
        .firma { margin-top: 70px; text-align: center; }
        .linea { width: 250px; border-top: 1px solid #374151; margin: 0 auto 6px; }
        .pequeno { font-size: 10px; color: #6b7280; }
    </style>
</head>
<body>
    <header>
        <img src="{{ public_path('img/encabezado.png') }}" style="width: 100%; max-height: 50px;" alt="">
    </header>
    <footer>
        <img src="{{ public_path('img/pie.png') }}" style="width: 100%; max-height: 50px;" alt="">
    </footer>

    @php $persona = $resultado['persona']; @endphp

    <main>
        <div class="titulo">{{ $resultado['solvente'] ? 'Constancia de solvencia' : 'Constancia de no solvencia' }} · Deportes</div>

        <table class="meta">
            <tr><td><strong>Fecha de emisión:</strong> {{ $resultado['fecha']->format('d/m/Y H:i') }}</td></tr>
            <tr><td><strong>Sede:</strong> {{ $sede ?? '—' }}</td></tr>
            <tr><td><strong>Emitida por:</strong> {{ $emisor ?: '—' }}</td></tr>
        </table>

        <p style="margin-top: 20px; text-align: justify;">
            Se hace constar que el/la ciudadano(a) <strong>{{ mb_strtoupper(trim($persona->nombres_per . ' ' . $persona->apellidos_per)) }}</strong>,
            titular de la cédula de identidad <strong>{{ $persona->cedula_per }}</strong>,
            @if ($resultado['solvente'])
                no posee préstamos pendientes de implementos deportivos ni de material de oficina, ni sanciones activas en el área de deportes, por lo que se encuentra
            @else
                registra los compromisos pendientes que se detallan a continuación en el área de deportes, por lo que se encuentra
            @endif
        </p>

        <div class="estado {{ $resultado['solvente'] ? 'solvente' : 'no-solvente' }}">
            {{ $resultado['solvente'] ? 'SOLVENTE' : 'NO SOLVENTE' }}
        </div>

        @if ($resultado['bloquea_titulo'])
            <div class="alerta">
                Tiene una sanción activa: NO se puede firmar la solvencia para el título hasta que la sanción se levante por devolución o reposición de lo prestado.
            </div>
        @endif

        @foreach ($resultado['modulos'] as $modulo)
            @if ($modulo['pendientes'] !== [])
                <p><strong>{{ $modulo['etiqueta'] }}</strong></p>
                <table>
                    <thead>
                        <tr><th>Compromiso</th><th>Detalle</th><th>Desde</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($modulo['pendientes'] as $pendiente)
                            <tr>
                                <td>{{ $pendiente['titulo'] }}</td>
                                <td>{{ $pendiente['detalle'] }}</td>
                                <td>{{ $pendiente['fecha'] ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        @endforeach

        <div class="firma">
            <div class="linea"></div>
            <div><strong>Coordinación de Deportes</strong></div>
            <div class="pequeno">Constancia generada por el Sistema de Bienestar Estudiantil · válida para la fecha de emisión</div>
        </div>
    </main>
</body>
</html>
