<?php

namespace App\Jobs;

use App\Models\Tenant\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EnvoyerNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 30;

    public function __construct(
        public readonly int    $userId,
        public readonly string $type,
        public readonly array  $data = []
    ) {}

    public function handle(): void
    {
        $user = User::find($this->userId);

        if (!$user) {
            Log::warning("EnvoyerNotificationJob: user {$this->userId} introuvable.");
            return;
        }

        match($this->type) {
            'nouveau_devoir'      => $this->notifierNouveauDevoir($user),
            'devoir_termine'      => $this->notifierDevoirTermine($user),
            'correction_requise'  => $this->notifierCorrectionRequise($user),
            'rappel_echeance'     => $this->notifierRappelEcheance($user),
            default               => Log::warning("EnvoyerNotificationJob: type inconnu {$this->type}"),
        };
    }

    private function notifierNouveauDevoir(User $user): void
    {
        Log::info("Notification nouveau devoir → {$user->email} : {$this->data['titre']}");
        // TODO: Mail::to($user->email)->send(new NouveauDevoirMail($this->data));
    }

    private function notifierDevoirTermine(User $user): void
    {
        Log::info("Notification devoir terminé → {$user->email}");
        // TODO: Mail::to($user->email)->send(new DevoirTermineMail($this->data));
    }

    private function notifierCorrectionRequise(User $user): void
    {
        Log::info("Notification correction requise → {$user->email}");
        // TODO: Mail::to($user->email)->send(new CorrectionRequise($this->data));
    }

    private function notifierRappelEcheance(User $user): void
    {
        Log::info("Rappel échéance → {$user->email} : {$this->data['titre']}");
        // TODO: Mail::to($user->email)->send(new RappelEcheanceMail($this->data));
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("EnvoyerNotificationJob échoué pour user {$this->userId}: {$exception->getMessage()}");
    }
}