<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Compra;

class CompraProveedorMail extends Mailable
{
    use Queueable, SerializesModels;

    public $compra;

    public function __construct(Compra $compra)
    {
        $this->compra = $compra;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Orden de compra Nro: ' . $this->compra->id_cmp,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.compra_proveedor',
            with: [
                'compra' => $this->compra,
                'detalleCompras' => $this->compra->detalleCompras,
                'proveedor' => $this->compra->proveedor,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
