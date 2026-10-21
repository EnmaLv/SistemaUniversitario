<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recordatorio de cita sin procesar</title>
</head>
<body>
    @php
        $fechaStr = $cita->fecha_cps ? \Carbon\Carbon::parse($cita->fecha_cps)->format('d/m/Y') : '';
        $horaStr = $cita->hora_cps ? \Carbon\Carbon::parse($cita->hora_cps)->format('g:i A') : '';
        $pacienteName = $cita->paciente_nombre ?? ($cita->paciente->nombre_completo ?? 'Desconocido');
    @endphp
    
    <p>Hola,</p>

    <p>La cita del día <strong>{{ $fechaStr }}</strong> y hora <strong>{{ $horaStr }}</strong> del paciente <strong>{{ $pacienteName }}</strong>, no ha sido procesada totalmente.</p>

    <p>Rediríjase a ese apartado y proceda, de lo contrario, el paciente no podrá solicitar una cita nuevamente hasta que esta acción sea realizada.</p>

    <p>Gracias,</p>
    <p>Equipo de Psicoguía</p>
</body>
</html>
