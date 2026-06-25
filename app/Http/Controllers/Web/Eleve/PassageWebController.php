<?php

namespace App\Http\Controllers\Web\Eleve;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Devoir;
use App\Models\Tenant\TentativeDevoir;
use App\Models\Tenant\ReponseEleve;
use App\Services\TimerService;
use App\Services\NotationService;
use Illuminate\Http\Request;

class PassageWebController extends Controller
{
    public function __construct(
        private TimerService    $timerService,
        private NotationService $notationService
    ) {}

    // ── COMMENCER UN DEVOIR ───────────────────────────────
    public function commencer(Request $request, int $id)
    {
        $eleve  = $request->user();
        $devoir = Devoir::findOrFail($id);

        if (!$devoir->estDisponible()) {
            return back()->with('error', 'Ce devoir n\'est pas disponible.');
        }

        // Tentative déjà en cours → reprendre
        $tentativeEnCours = TentativeDevoir::where('devoir_id', $id)
            ->where('eleve_id', $eleve->id)
            ->where('statut', 'en_cours')
            ->first();

        if ($tentativeEnCours) {
            return redirect()->route('eleve.passage.question', [
                $tentativeEnCours->id,
                $tentativeEnCours->question_courante,
            ]);
        }

        // Vérifier le nombre de tentatives
        $nbTentatives = TentativeDevoir::where('devoir_id', $id)
            ->where('eleve_id', $eleve->id)
            ->count();

        if ($nbTentatives >= $devoir->max_tentatives) {
            return back()->with('error', 'Vous avez atteint le nombre maximum de tentatives.');
        }

        // Créer la tentative
        $tentative = TentativeDevoir::create([
            'devoir_id'  => $devoir->id,
            'eleve_id'   => $eleve->id,
            'adresse_ip' => $request->ip(),
            'navigateur' => $request->userAgent(),
            'statut'     => 'en_cours',
        ]);

        $this->timerService->demarrerChronometre($tentative);

        return redirect()->route('eleve.passage.question', [$tentative->id, 1]);
    }

    // ── AFFICHER UNE QUESTION ─────────────────────────────
    public function question(Request $request, int $tentativeId, int $numero = 1)
    {
        $tentative = TentativeDevoir::where('id', $tentativeId)
            ->where('eleve_id', $request->user()->id)
            ->where('statut', 'en_cours')
            ->with('devoir.questions.reponsesPossibles')
            ->firstOrFail();

        $devoir    = $tentative->devoir;
        $questions = $devoir->questions_aleatoires
            ? $devoir->questions->shuffle()
            : $devoir->questions;

        // Sécuriser le numéro
        if ($numero < 1 || $numero > $questions->count()) {
            $numero = 1;
        }

        // Mettre à jour la progression
        $tentative->update(['question_courante' => $numero]);
        $this->timerService->demarrerChronometreQuestion($tentative);

        // Questions déjà répondues (pour les dots de navigation)
        $questionsRepondues = $tentative->reponsesEleves()->pluck('question_id')->toArray();

        // Réponse existante pour pré-remplir
        $reponseExistante = ReponseEleve::where('tentative_id', $tentativeId)
            ->where('question_id', $questions[$numero - 1]->id)
            ->first();

        return view('eleve.devoir.passage', [
            'devoir'             => $devoir,
            'tentative'          => $tentative->fresh(),
            'questions'          => $questions,
            'questionCourante'   => $numero,
            'questionsRepondues' => $questionsRepondues,
            'reponseExistante'   => $reponseExistante,
        ]);
    }

    // ── ENREGISTRER UNE RÉPONSE ───────────────────────────
    public function repondre(Request $request, int $tentativeId)
    {
        $tentative = TentativeDevoir::where('id', $tentativeId)
            ->where('eleve_id', $request->user()->id)
            ->where('statut', 'en_cours')
            ->firstOrFail();

        // Enregistrer la réponse
        ReponseEleve::updateOrCreate(
            [
                'tentative_id' => $tentativeId,
                'question_id'  => $request->question_id,
            ],
            [
                'reponse_possible_id'    => $request->reponse_possible_id,
                'texte_libre'            => $request->texte_libre,
                'temps_utilise_secondes' => $request->temps_utilise ?? null,
            ]
        );

        $nbQuestions = $tentative->devoir->questions()->count();
        $prochainNum = $tentative->question_courante + 1;

        // Dernière question → soumettre
        if ($prochainNum > $nbQuestions) {
            $this->notationService->calculerNote($tentative);
            return redirect()->route('eleve.resultat.detail', $tentativeId)
                ->with('success', 'Devoir soumis avec succès !');
        }

        // Passer à la question suivante
        return redirect()->route('eleve.passage.question', [$tentativeId, $prochainNum]);
    }

    // ── SOUMISSION MANUELLE (AJAX) ────────────────────────
    public function soumettre(Request $request, int $tentativeId)
    {
        $tentative = TentativeDevoir::where('id', $tentativeId)
            ->where('eleve_id', $request->user()->id)
            ->where('statut', 'en_cours')
            ->firstOrFail();

        $this->notationService->soumettreAutomatiquement($tentative, 'manuel');

        return response()->json(['success' => true, 'redirect' => route('eleve.resultat.detail', $tentativeId)]);
    }
}