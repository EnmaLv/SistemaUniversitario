<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class PropuestaRechazadaMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected $cita;
    protected $paciente;

    public function __construct($cita, $paciente)
    {
        $this->cita = $cita;
        $this->paciente = $paciente;
    }

    public function build()
    {
        return $this->subject('Contrapropuesta Rechazada')
            ->view('emails.propuesta_rechazada')
            ->with([
                'paciente' => optional($this->paciente->persona)->nombres_per ?: 'Paciente',
                'bloques_rechazados' => $this->cita->bloque_propuesto_cps ?: 'los bloques sugeridos'
            ]);
    }
}
