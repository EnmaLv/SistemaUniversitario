<?php

namespace App\Notifications;

use App\Models\Usuario;
use App\Models\salud\Cita;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class AvisoAtencionPsicologoNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $paciente;
    public $cita;

    public function __construct($paciente, Cita $cita)
    {
        $this->paciente = $paciente;
        $this->cita = $cita;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type_id' => 'aviso_atencion_psicologo',
            'paciente_id' => $this->paciente->id,
            'cita_id' => $this->cita->id_cps,
            'body' => 'Recordatorio: Has rechazado o cancelado múltiples citas con el paciente ' . ltrim($this->paciente->name ?? 'Paciente') . '. Recuerda que puedes ajustar su prioridad de atención.',
            'url' => route('admin.psicologia.maestros.agenda.index', ['avisoAtencionCita' => $this->cita->id_cps]),
        ];
    }
}
