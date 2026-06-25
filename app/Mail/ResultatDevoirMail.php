<?php

namespace App\Mail;

use App\Models\Tenant\Resultat;
use App\Models\Tenant\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResultatDevoirMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Resultat $resultat,
        public readonly User     $eleve
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '✅ Résultat disponible : ' . $this->resultat->devoir?->titre,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.resultat-devoir',
        );
    }
}