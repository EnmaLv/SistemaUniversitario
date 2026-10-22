<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MensajesAgrupadosMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $remitente;
    public $cantidadMensajes;

    public function __construct($remitente, $cantidadMensajes)
    {
        $this->remitente = $remitente;
        $this->cantidadMensajes = $cantidadMensajes;
    }

    public function envelope(): Envelope
    {
        $roleName = $this->remitente->role === 'psicologo' ? 'El psicólogo' : 'El paciente';
        $sujet = $this->cantidadMensajes > 1 
            ? "{$roleName} {$this->remitente->persona->nombres_per} te ha enviado {$this->cantidadMensajes} mensajes nuevos"
            : "{$roleName} {$this->remitente->persona->nombres_per} te ha enviado un mensaje nuevo";
            
        return new Envelope(
            subject: $sujet,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.mensajes_agrupados',
            with: [
                'roleName' => $this->remitente->role === 'psicologo' ? 'El psicólogo' : 'El paciente',
                'remitenteName' => $this->remitente->name,
                'cantidad' => $this->cantidadMensajes
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
