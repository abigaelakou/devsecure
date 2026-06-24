<?php

namespace App\Http\Controllers\Eleve;

use App\Http\Controllers\Controller;
use App\Models\Tenant\TentativeDevoir;
use App\Models\Tenant\Question;
use App\Models\Tenant\ReponseEleve;
use App\Services\TimerService;
use App\Services\NotationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PassageController extends Controller
{
    public function __construct(
        private TimerService    $timerService,
        private NotationService $notationService
    ) {}

    // ── RÉCUPÉRER UNE QUESTION ────────────────────────────
    public function getQuestion(Request $request, int $tentativeId, int $numero): JsonResponse
    {
        $tentative = TentativeDevoir::where('id', $tentativeId)
            ->where('eleve_id', $request->user()->id)
            ->where('statut', 'en_cours')
            ->with('devoir.questions')
            ->firstOrFail();

        $devoir    = $tentative->devoir;
        $questions = $devoir->questions_aleatoires
            ? $devoir->questions->shuffle()
            : $devoir->questions;

        $question = $questions->get($numero - 1);

        if (!$question) {
            return response()->json(['message' => 'Question introuvable.'], 404);
        }

        // Mettre à jour la question courante
        $tentative->update(['question_courante' => $numero]);

        // Démarrer le chrono de cette question
        $this->timerService->demarrerChronometreQuestion($tentative);

        // Récupérer les réponses possibles (mélangées si option activée)
        $reponses = $question->reponsesPossibles;
        if ($devoir->reponses_aleatoires) {
            $reponses = $reponses->shuffle();
        }

        return response()->json([
            'question' => [
                'id'              => $question->id,
                'numero'          => $numero,
                'total'           => $questions->count(),
                'enonce'          => $question->enonce,
                'image'           => $question->image,
                'type'            => $question->type,
                'points'          => $question->points,
                'temps_secondes'  => $question->temps_effectif,
                'reponses'        => $reponses->map(fn($r) => [
                    'id'    => $r->id,
                    'texte' => $r->texte,
                ]),
            ],
            'timer' => [
                'temps_restant_question' => $this->timerService->getTempsRestantQuestion($tentative),
                'temps_restant_global'   => $this->timerService->getTempsRestant($tentative),
            ],
            'progression' => [
                'courante'  => $numero,
                'total'     => $questions->count(),
                'repondues' => $tentative->reponsesEleves()->count(),
            ],
        ]);
    }

    // ── ENREGISTRER UNE RÉPONSE ───────────────────────────
    public function repondre(Request $request, int $tentativeId): JsonResponse
    {
        $request->validate([
            'question_id'         => 'required|integer|exists:questions,id',
            'reponse_possible_id' => 'nullable|integer|exists:reponses_possibles,id',
            'texte_libre'         => 'nullable|string|max:5000',
        ]);

        $tentative = TentativeDevoir::where('id', $tentativeId)
            ->where('eleve_id', $request->user()->id)
            ->where('statut', 'en_cours')
            ->firstOrFail();

        $question = Question::findOrFail($request->question_id);

        // Calculer le temps utilisé
        $tempsUtilise = $question->temps_effectif
            ? ($question->temps_effectif - $this->timerService->getTempsRestantQuestion($tentative))
            : null;

        // Créer ou mettre à jour la réponse
        $reponse = ReponseEleve::updateOrCreate(
            [
                'tentative_id' => $tentativeId,
                'question_id'  => $request->question_id,
            ],
            [
                'reponse_possible_id'    => $request->reponse_possible_id,
                'texte_libre'            => $request->texte_libre,
                'temps_utilise_secondes' => $tempsUtilise,
                'temps_expire'           => false,
            ]
        );

        // Correction automatique QCM / Vrai-Faux
        if ($request->reponse_possible_id && $question->estAutomatique()) {
            $reponsePossible = $question->reponsesPossibles()
                ->find($request->reponse_possible_id);

            $reponse->update([
                'est_correcte'   => $reponsePossible?->est_correcte,
                'points_obtenus' => $reponsePossible?->est_correcte ? $question->points : 0,
            ]);
        }

        // Passer à la question suivante automatiquement
        $nbQuestions  = $tentative->devoir->questions()->count();
        $prochainNumero = $tentative->question_courante + 1;

        if ($prochainNumero > $nbQuestions) {
            // C'était la dernière question → soumettre
            $this->notationService->calculerNote($tentative);

            return response()->json([
                'message'     => 'Dernière question répondue. Devoir soumis automatiquement.',
                'action'      => 'soumettre',
                'resultat_id' => $tentative->fresh()->resultat?->id,
            ]);
        }

        return response()->json([
            'message'         => 'Réponse enregistrée.',
            'action'          => 'question_suivante',
            'prochain_numero' => $prochainNumero,
        ]);
    }

    // ── SOUMETTRE LE DEVOIR ───────────────────────────────
    public function soumettre(Request $request, int $tentativeId): JsonResponse
    {
        $tentative = TentativeDevoir::where('id', $tentativeId)
            ->where('eleve_id', $request->user()->id)
            ->where('statut', 'en_cours')
            ->firstOrFail();

        $this->notationService->soumettreAutomatiquement($tentative, 'manuel');

        $resultat = $tentative->fresh()->resultat;

        return response()->json([
            'message'  => 'Devoir soumis avec succès.',
            'resultat' => [
                'note_finale'     => $resultat?->note_finale,
                'note_sur'        => $resultat?->note_sur,
                'pourcentage'     => $resultat?->pourcentage,
                'mention'         => $resultat?->mention,
                'bonnes_reponses' => $resultat?->bonnes_reponses,
                'total_questions' => $resultat?->total_questions,
            ],
        ]);
    }

    // ── PROGRESSION ───────────────────────────────────────
    public function progression(Request $request, int $tentativeId): JsonResponse
    {
        $tentative = TentativeDevoir::where('id', $tentativeId)
            ->where('eleve_id', $request->user()->id)
            ->withCount('reponsesEleves')
            ->firstOrFail();

        return response()->json([
            'question_courante'  => $tentative->question_courante,
            'total_questions'    => $tentative->devoir->questions()->count(),
            'reponses_count'     => $tentative->reponses_eleves_count,
            'temps_restant'      => $this->timerService->getTempsRestant($tentative),
            'statut'             => $tentative->statut,
        ]);
    }
}