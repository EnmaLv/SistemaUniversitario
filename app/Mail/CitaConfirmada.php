<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;

class CitaConfirmada extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected $cita;
    protected $paciente;
    protected $psicologo;

    public function __construct($cita)
    {
        $this->cita = $cita;
        $this->paciente = $cita->paciente;
        $this->psicologo = $cita->psicologo;
    }

    public function build()
    {
        return $this->subject('Confirmación de cita')
            ->view('emails.cita_confirmada')
            ->with([
                'paciente' => optional($this->paciente->persona)->nombres_per ?: 'Paciente',
                'psicologo' => optional($this->psicologo->persona)->nombres_per ?: 'Tu psicólogo',
                'fecha' => $this->cita->fecha_cps ? Carbon::parse($this->cita->fecha_cps)->translatedFormat('d \d\e F, Y') : now()->translatedFormat('d \d\e F, Y'),
                'hora' => $this->cita->hora_cps ?: 'Pendiente',
                'bloque' => $this->cita->bloque_propuesto_cps ?? ($this->cita->bloques_sugeridos_cps ?? 'No definido'),
            ]);
    }
}
