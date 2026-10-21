<?php

namespace App\Notifications;

use App\Models\salud\Cita;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Mail\CitaRechazadaMail;

class CitaRechazadaNotification extends Notification implements ShouldQueue
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
        return (new CitaRechazadaMail($this->cita, $this->cita->psicologo))
                    ->to($notifiable->email);
    }

    public function toArray(object $notifiable): array
    {
        $psicologoName = $this->cita->psicologo->persona->nombres_per ?? 'Tu psicólogo';
        return [
            'type_id' => 'cita_rechazada',
            'cita_id' => $this->cita->id,
            'psicologo_name' => $psicologoName,
            'body' => "El psicólogo {$psicologoName} ha rechazado tu cita.",
            'url' => route('admin.psicologia.maestros.citas.index'),
        ];
    }
}
