<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class CitaRechazadaMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected $cita;
    protected $psicologo;

    public function __construct($cita, $psicologo)
    {
        $this->cita = $cita;
        $this->psicologo = $psicologo;
    }

    public function build()
    {
        return $this->subject('Aviso de Cita Rechazada')
            ->view('emails.cita_rechazada')
            ->with([
                'psicologo' => optional($this->psicologo->persona)->nombres_per ?: 'Psicólogo'
            ]);
    }
}
