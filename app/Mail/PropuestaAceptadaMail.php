<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;

class PropuestaAceptadaMail extends Mailable implements ShouldQueue
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
        return $this->subject('Contrapropuesta Aceptada')
            ->view('emails.propuesta_aceptada')
            ->with([
                'paciente' => optional($this->paciente->persona)->nombres_per ?: 'Paciente',
                'fecha' => $this->cita->fecha_cps ? Carbon::parse($this->cita->fecha_cps)->translatedFormat('l d \d\e F \d\e Y') : 'una fecha por definir',
                'hora' => $this->cita->hora_cps ? Carbon::parse($this->cita->hora_cps)->format('g:i A') : 'una hora por definir'
            ]);
    }
}
