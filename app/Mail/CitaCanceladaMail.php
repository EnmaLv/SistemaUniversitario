<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;

class CitaCanceladaMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected $cita;
    protected $paciente;
    protected $psicologo;
    protected $cancelledBy;

    public function __construct($cita, $paciente, $psicologo, $cancelledBy)
    {
        $this->cita = $cita;
        $this->paciente = $paciente;
        $this->psicologo = $psicologo;
        $this->cancelledBy = $cancelledBy;
    }

    public function build()
    {
        return $this->subject('Aviso de Cita Cancelada')
            ->view('emails.cita_cancelada')
            ->with([
                'paciente' => optional($this->paciente->persona)->nombres_per ?: 'Paciente',
                'psicologo' => optional($this->psicologo->persona)->nombres_per ?: 'Psicólogo',
                'fecha' => $this->cita->fecha_cps ? Carbon::parse($this->cita->fecha_cps)->translatedFormat('d \d\e F, Y') : 'una fecha por definir',
                'hora' => $this->cita->hora_cps ? Carbon::parse($this->cita->hora_cps)->format('g:i A') : 'una hora por definir',
                'cancelledBy' => $this->cancelledBy
            ]);
    }
}
