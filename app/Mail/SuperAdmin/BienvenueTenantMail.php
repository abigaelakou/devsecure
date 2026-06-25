<?php

namespace App\Mail\SuperAdmin;

use App\Models\Landlord\Tenant;
use App\Models\Tenant\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BienvenueTenantMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $urlEtablissement;

    public function __construct(
        public readonly Tenant $tenant,
        public readonly User   $admin,
        public readonly string $motDePasse
    ) {
        $this->urlEtablissement = 'https://' . $tenant->id . '.' . config('app.base_domain', 'devsecure.ci');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🎉 Bienvenue sur DevSecure — ' . $this->tenant->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.superadmin.bienvenue-tenant',
        );
    }
}