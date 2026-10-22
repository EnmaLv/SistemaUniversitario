<x-mail::message>
# Nueva solicitud de cita

Hola {{ $cita->paciente->nombre_completo ?? 'Paciente' }},

El psicólogo **{{ $psicologo->nombre_completo ?? 'Psicólogo' }}** ha abierto una nueva solicitud de cita para ti en la plataforma. 
Esta solicitud se encuentra actualmente en estado pendiente, lo que significa que el psicólogo estará buscando el mejor bloque de horario para atenderte pronto.

Recibirás una notificación y un correo cuando tu cita sea finalmente confirmada con fecha y hora.

Si tienes dudas, puedes consultar tu perfil en la plataforma.

<x-mail::button :url="route('dashboard')">
Ir a la plataforma
</x-mail::button>

Gracias,<br>
{{ config('app.name') }}
</x-mail::message>
