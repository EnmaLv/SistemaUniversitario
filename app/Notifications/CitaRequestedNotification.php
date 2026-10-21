<?php

namespace App\Notifications;

use App\Models\salud\Cita;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class CitaRequestedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $cita;

    public function __construct(Cita $cita)
    {
        $this->cita = $cita;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type_id' => 'cita_requested',
            'cita_id' => $this->cita->id,
            'paciente_name' => $this->cita->paciente->persona->nombres_per,
            'body' => 'Tienes una nueva solicitud de cita de ' . $this->cita->paciente->persona->nombres_per . '.',
            'url' => route('admin.psicologia.maestros.agenda.index'),
        ];
    }
}
