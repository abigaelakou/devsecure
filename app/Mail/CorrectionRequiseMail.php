<?php

namespace App\Mail;

use App\Models\Tenant\Devoir;
use App\Models\Tenant\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CorrectionRequiseMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Devoir $devoir,
        public readonly User   $enseignant,
        public readonly int    $nbCorrections
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '✏️ ' . $this->nbCorrections . ' correction(s) en attente : ' . $this->devoir->titre,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.correction-requise',
        );
    }
}