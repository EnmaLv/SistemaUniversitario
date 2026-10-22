<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class PenalizacionPsicologoNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $paciente;

    public function __construct($paciente)
    {
        $this->paciente = $paciente;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type_id' => 'penalizacion_psicologo',
            'paciente_id' => $this->paciente->id,
            'body' => 'El paciente ' . ltrim($this->paciente->persona->nombres_per ?? 'Paciente') . ' ha incumplido con las normas. Su prioridad ha sido cambiada a baja.',
            'url' => route('admin.psicologia.maestros.historias.show', $this->paciente->id),
        ];
    }
}
