<?php

namespace App\Notifications;

use App\Models\salud\Cita;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class CitaCancelledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $cita;
    public $cancelledBy;

    public function __construct(Cita $cita, $cancelledBy)
    {
        $this->cita = $cita;
        $this->cancelledBy = $cancelledBy;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable)
    {
        if ($this->cancelledBy === 'pospuesta') {
            return (new \App\Mail\CitaPospuestaMail($this->cita, $this->cita->psicologo))
                        ->to($notifiable->email);
        }

        return (new \App\Mail\CitaCanceladaMail($this->cita, $this->cita->paciente, $this->cita->psicologo, $this->cancelledBy))
                    ->to($notifiable->email);
    }

    public function toArray(object $notifiable): array
    {
        $senderName = $this->cancelledBy === 'paciente' ? $this->cita->paciente->persona->nombres_per : $this->cita->psicologo->persona->nombres_per;
        $url = $this->cancelledBy === 'paciente' ? route('admin.psicologia.maestros.agenda.index') : route('admin.psicologia.maestros.citas.index');
        
        $body = $senderName . ' ha cancelado una cita.';
        if ($this->cancelledBy === 'pospuesta') {
            $senderName = $this->cita->psicologo->persona->nombres_per;
            $url = route('admin.psicologia.maestros.citas.index');
            $body = $senderName . ' ha pospuesto tu cita confirmada. El psicólogo te asignará un nuevo horario pronto. Recibirás una notificación cuando lo haga.';
        }
        
        return [
            'type_id' => $this->cancelledBy === 'pospuesta' ? 'cita_postponed' : 'cita_cancelled',
            'cita_id' => $this->cita->id_cps,
            'sender_name' => $senderName,
            'body' => $body,
            'url' => $url,
        ];
    }
}
