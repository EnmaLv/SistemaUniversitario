<?php

namespace App\Notifications;

use App\Models\salud\Cita;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class RecordatorioNotasNotification extends Notification implements ShouldQueue
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
        $pacienteName = $this->cita->paciente->persona->nombres_per ?? 'Paciente';
        return [
            'type_id' => 'recordatorio_notas',
            'cita_id' => $this->cita->id,
            'paciente_name' => $pacienteName,
            'body' => 'Recordatorio: Tienes pendiente registrar las notas de la sesión con ' . $pacienteName . '.',
            'url' => route('admin.psicologia.maestros.historias.show', $this->cita->user_id),
        ];
    }
}
