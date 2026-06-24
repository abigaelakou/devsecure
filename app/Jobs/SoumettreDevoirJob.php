<?php

namespace App\Jobs;

use App\Models\Tenant\TentativeDevoir;
use App\Services\NotationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SoumettreDevoirJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Nombre de tentatives si le job échoue
    public int $tries = 3;

    // Timeout du job en secondes
    public int $timeout = 60;

    public function __construct(
        public readonly TentativeDevoir $tentative
    ) {}

    public function handle(NotationService $notationService): void
    {
        // Recharger la tentative depuis la DB (peut avoir changé)
        $tentative = TentativeDevoir::find($this->tentative->id);

        if (!$tentative) {
            Log::warning("SoumettreDevoirJob: tentative {$this->tentative->id} introuvable.");
            return;
        }

        // Ne soumettre que si toujours en cours
        if (!$tentative->estEnCours()) {
            Log::info("SoumettreDevoirJob: tentative {$tentative->id} déjà soumise (statut: {$tentative->statut}).");
            return;
        }

        // Vérifier que le temps est vraiment écoulé
        if ($tentative->temps_restant > 10) {
            Log::info("SoumettreDevoirJob: tentative {$tentative->id} — temps restant {$tentative->temps_restant}s, pas encore soumis.");
            return;
        }

        Log::info("SoumettreDevoirJob: soumission automatique tentative {$tentative->id}.");

        $notationService->soumettreAutomatiquement($tentative, 'timeout');
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("SoumettreDevoirJob échoué pour tentative {$this->tentative->id}: {$exception->getMessage()}");
    }
}