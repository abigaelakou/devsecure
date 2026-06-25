<?php

namespace App\Services;

use App\Models\Tenant\TentativeDevoir;
use App\Models\Tenant\ReponseEleve;
use App\Models\Tenant\Resultat;
use App\Models\Tenant\EvenementAntitriche;

class NotationService
{
    public function calculerNote(TentativeDevoir $tentative): float
    {
        $devoir        = $tentative->devoir;
        $reponses      = $tentative->reponsesEleves;
        $totalPoints   = $devoir->questions->sum('points');
        $pointsObtenus = 0;

        foreach ($reponses as $reponse) {
            if ($reponse->reponse_possible_id && $reponse->reponsePossible?->est_correcte) {
                $pointsObtenus += $reponse->question->points;
                $reponse->update(['est_correcte' => true, 'points_obtenus' => $reponse->question->points]);
            } elseif ($reponse->reponse_possible_id) {
                $reponse->update(['est_correcte' => false, 'points_obtenus' => 0]);
            }
        }

        $noteSur    = $devoir->note_sur;
        $noteFinale = $totalPoints > 0
            ? round(($pointsObtenus / $totalPoints) * $noteSur, 2)
            : 0;

        $antitriche = app(AntitricheService::class)->getScoreAntitriche($tentative);

        Resultat::updateOrCreate(
            ['tentative_id' => $tentative->id],
            [
                'eleve_id'                 => $tentative->eleve_id,
                'devoir_id'                => $tentative->devoir_id,
                'note_finale'              => $noteFinale,
                'note_sur'                 => $noteSur,
                'pourcentage'              => round(($noteFinale / $noteSur) * 100, 1),
                'bonnes_reponses'          => $reponses->where('est_correcte', true)->count(),
                'mauvaises_reponses'       => $reponses->where('est_correcte', false)->count(),
                'sans_reponse'             => $devoir->questions->count() - $reponses->count(),
                'total_questions'          => $devoir->questions->count(),
                'fraude_detectee'          => $antitriche['fraude_probable'],
                'nb_evenements_antitriche' => array_sum($antitriche['evenements']),
            ]
        );

        $tentative->update([
            'note'          => $noteFinale,
            'note_calculee' => true,
            'statut'        => 'soumis',
            'fin_le'        => now(),
        ]);

        return $noteFinale;
    }

    public function soumettreAutomatiquement(TentativeDevoir $tentative, string $raison = 'timeout'): void
    {
        $questionsRepondues = $tentative->reponsesEleves->pluck('question_id');

        $tentative->devoir->questions
            ->whereNotIn('id', $questionsRepondues)
            ->each(function ($q) use ($tentative) {
                ReponseEleve::create([
                    'tentative_id'   => $tentative->id,
                    'question_id'    => $q->id,
                    'temps_expire'   => true,
                    'est_correcte'   => false,
                    'points_obtenus' => 0,
                ]);
            });

        $this->calculerNote($tentative);
    app(\App\Services\NotificationService::class)->notifierResultat($resultat_instance);
    app(\App\Services\NotificationService::class)->notifierCorrectionRequise($tentative->devoir);

        EvenementAntitriche::create([
            'tentative_id' => $tentative->id,
            'eleve_id'     => $tentative->eleve_id,
            'type'         => 'soumission_auto',
            'details'      => json_encode(['raison' => $raison]),
            'survenu_le'   => now(),
        ]);
    }
}