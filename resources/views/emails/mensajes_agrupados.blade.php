<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevos Mensajes</title>
</head>
<body>
    <p>Hola,</p>
    
    <p><strong>{{ $roleName }} {{ $remitenteName }}</strong> te ha enviado 
    @if($cantidad > 1)
        <strong>{{ $cantidad }} mensajes nuevos</strong>.
    @else
        <strong>un mensaje nuevo</strong>.
    @endif
    </p>
    
    <p><a href="{{ route('chat.index') }}" style="padding: 10px 15px; background-color: #0284c7; color: white; text-decoration: none; border-radius: 5px;">Ir a mis Mensajes</a></p>
    
    <p>Gracias,</p>
    <p>Equipo de Psicoguía</p>
</body>
</html>
