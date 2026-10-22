<?php

namespace App\Notifications;

use App\Models\salud\Cita;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ContrapropuestaRechazadaNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $cita;

    public function __construct(Cita $cita)
    {
        $this->cita = $cita;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable)
    {
        return (new \App\Mail\PropuestaRechazadaMail($this->cita, $this->cita->paciente))
                    ->to($notifiable->email);
    }

    public function toArray(object $notifiable): array
    {
        $pacienteName = trim(($this->cita->paciente->persona->nombres_per ?? '') . ' ' . ($this->cita->paciente->persona->apellidos_per ?? '')) ?: 'El paciente';
        
        return [
            'type_id' => 'contrapropuesta_rechazada',
            'cita_id' => $this->cita->id_cps,
            'body' => $this->cita->propuesta_estado_cps === null 
                ? "El paciente $pacienteName ha rechazado la contrapropuesta y ha enviado una nueva sugerencia de horario." 
                : "El paciente $pacienteName ha rechazado tu contrapropuesta de horario. La cita permanece pendiente.",
            'url' => route('admin.psicologia.maestros.agenda.index'),
        ];
    }
}
