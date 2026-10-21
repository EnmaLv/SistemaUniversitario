<?php

namespace App\Notifications;

use App\Models\salud\Cita;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ContrapropuestaCitaNotification extends Notification implements ShouldQueue
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
        return (new \App\Mail\ContrapropuestaCitaMail($this->cita, $this->cita->psicologo))
                    ->to($notifiable->email);
    }

    public function toArray(object $notifiable): array
    {
        $pacienteName = trim(($this->cita->paciente->persona->nombres_per ?? '') . ' ' . ($this->cita->paciente->persona->apellidos_per ?? '')) ?: 'paciente';

        return [
            'type_id' => 'contrapropuesta_cita',
            'cita_id' => $this->cita->id_cps,
            'psicologo_name' => $this->cita->psicologo->persona->nombres_per ?? 'Tu psicólogo',
            'body' => "Hola $pacienteName, tu psicólogo te ha enviado una contrapropuesta de horario para tu cita. Revísala y responde como corresponde.",
            'url' => route('admin.psicologia.maestros.citas.index'),
        ];
    }
}
