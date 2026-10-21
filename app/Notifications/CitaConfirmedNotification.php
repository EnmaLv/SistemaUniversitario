<?php

namespace App\Notifications;

use App\Models\salud\Cita;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class CitaConfirmedNotification extends Notification implements ShouldQueue
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
        return (new \App\Mail\CitaConfirmada($this->cita))
                    ->to($notifiable->email);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type_id' => 'cita_confirmed',
            'cita_id' => $this->cita->id,
            'psicologo_name' => $this->cita->psicologo->persona->nombres_per,
            'body' => 'Tienes una nueva cita confirmada con ' . $this->cita->psicologo->persona->nombres_per . '.',
            'url' => route('admin.psicologia.maestros.citas.index'),
        ];
    }
}
