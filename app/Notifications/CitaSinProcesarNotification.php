<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Mail\CitaSinProcesarMail;

class CitaSinProcesarNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $cita;

    public function __construct($cita)
    {
        $this->cita = $cita;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }
    
    public function toMail(object $notifiable)
    {
        return (new CitaSinProcesarMail($this->cita))
                    ->to($notifiable->email);
    }

    public function toArray(object $notifiable): array
    {
        $fechaStr = $this->cita->fecha_cps ? \Carbon\Carbon::parse($this->cita->fecha_cps)->format('d/m/Y') : '';
        $horaStr = $this->cita->hora_cps ? \Carbon\Carbon::parse($this->cita->hora_cps)->format('g:i A') : '';
        $pacienteName = $this->cita->paciente->persona->nombres_per ?? ($this->cita->paciente->persona->nombres_per ?? 'Desconocido');
        
        return [
            'type_id' => 'cita_sin_procesar',
            'cita_id' => $this->cita->id_cps,
            'paciente_name' => $pacienteName,
            'body' => "Hay citas pendientes por gestionar, del paciente {$pacienteName} por el cual se ha pasado la semana de la gestión de esa precisa cita.",
            'url' => route('admin.psicologia.maestros.historias.index'), 
        ];
    }
}
