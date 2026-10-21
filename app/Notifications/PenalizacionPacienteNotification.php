<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class PenalizacionPacienteNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct()
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type_id' => 'penalizacion_paciente',
            'body' => 'Usted ha incumplido con las normas de atención. Por lo tanto, ahora su prioridad de atención pasará a ser baja.',
            'url' => route('admin.psicologia.maestros.citas.index'),
        ];
    }
}
