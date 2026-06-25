<?php

namespace App\Mail;

use App\Models\Tenant\Devoir;
use App\Models\Tenant\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RappelEcheanceMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Devoir $devoir,
        public readonly User   $eleve
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '⏰ Rappel : ' . $this->devoir->titre . ' expire bientôt',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.rappel-echeance',
        );
    }
}