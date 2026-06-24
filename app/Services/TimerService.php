<?php

namespace App\Services;

use App\Models\Tenant\TentativeDevoir;

class TimerService
{
    public function getTempsRestant(TentativeDevoir $tentative): int
    {
        if (!$tentative->debut_le) return 0;
        $devoir = $tentative->devoir;
        if (!$devoir->duree_totale_minutes) return 0;
        $expireA = $tentative->debut_le->addMinutes($devoir->duree_totale_minutes);
        return max(0, now()->diffInSeconds($expireA, false));
    }

    public function getTempsRestantQuestion(TentativeDevoir $tentative): int
    {
        $question = $tentative->devoir->questions()
            ->where('ordre', $tentative->question_courante)
            ->first();
        $temps = $question?->temps_secondes ?? $tentative->devoir->temps_par_question_secondes;
        if (!$temps) return 0;
        $debutQuestion = cache()->get("tentative:{$tentative->id}:question:{$tentative->question_courante}:debut");
        if (!$debutQuestion) return $temps;
        $ecoule = now()->diffInSeconds($debutQuestion);
        return max(0, $temps - $ecoule);
    }

    public function demarrerChronometre(TentativeDevoir $tentative): void
    {
        $tentative->update(['debut_le' => now(), 'statut' => 'en_cours']);
        $this->demarrerChronometreQuestion($tentative);
        $devoir = $tentative->devoir;
        if ($devoir->duree_totale_minutes) {
            \App\Jobs\SoumettreDevoirJob::dispatch($tentative)
                ->delay(now()->addMinutes($devoir->duree_totale_minutes));
        }
    }

    public function demarrerChronometreQuestion(TentativeDevoir $tentative): void
    {
        $question = $tentative->devoir->questions()
            ->where('ordre', $tentative->question_courante)
            ->first();
        $temps = $question?->temps_secondes ?? $tentative->devoir->temps_par_question_secondes;
        if ($temps) {
            cache()->put(
                "tentative:{$tentative->id}:question:{$tentative->question_courante}:debut",
                now(),
                $temps + 5
            );
        }
    }
}