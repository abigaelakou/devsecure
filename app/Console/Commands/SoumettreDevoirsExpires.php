<?php

namespace App\Console\Commands;

use App\Models\Tenant\TentativeDevoir;
use App\Services\NotationService;
use Illuminate\Console\Command;

class SoumettreDevoirsExpires extends Command
{
    protected $signature   = 'devoirs:soumettre-expires';
    protected $description = 'Soumet automatiquement les devoirs dont le temps est écoulé';

    public function handle(NotationService $notationService): void
    {
        $this->info('🔍 Recherche des devoirs expirés...');

        // Récupérer toutes les tentatives en cours
        $tentatives = TentativeDevoir::enCours()
            ->whereNotNull('debut_le')
            ->with('devoir')
            ->get();

        $count = 0;

        foreach ($tentatives as $tentative) {
            $devoir = $tentative->devoir;

            // Vérifier si la durée globale est dépassée
            if ($devoir->duree_totale_minutes && $tentative->temps_restant <= 0) {
                $this->line("  → Soumission tentative #{$tentative->id} (élève: {$tentative->eleve_id})");
                $notationService->soumettreAutomatiquement($tentative, 'timeout_cron');
                $count++;
                continue;
            }

            // Vérifier si la date d'expiration du devoir est dépassée
            if ($devoir->expire_le && $devoir->expire_le->isPast()) {
                $this->line("  → Soumission tentative #{$tentative->id} (devoir expiré)");
                $notationService->soumettreAutomatiquement($tentative, 'devoir_expire');
                $count++;
            }
        }

        $this->info("✅ {$count} devoir(s) soumis automatiquement.");
    }
}