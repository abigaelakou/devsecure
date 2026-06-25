<?php

namespace App\Http\Controllers\Web\Enseignant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Devoir;
use App\Models\Tenant\TentativeDevoir;
use App\Models\Tenant\ReponseEleve;
use App\Models\Tenant\Resultat;
use App\Models\Tenant\EvenementAntitriche;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class CorrectionController extends Controller
{
    // ── LISTE DES RÉSULTATS D'UN DEVOIR ──────────────────────────────
    public function resultats(Request $request, int $devoirId)
    {
        $devoir = Devoir::where('enseignant_id', $request->user()->id)
            ->with(['matiere', 'classe'])
            ->withCount('questions')
            ->findOrFail($devoirId);

        $resultats = Resultat::where('devoir_id', $devoirId)
            ->with(['eleve:id,nom,prenoms,matricule', 'tentative'])
            ->orderByDesc('note_finale')
            ->get()
            ->values()
            ->map(fn($r, $i) => [
                'rang'                     => $i + 1,
                'resultat_id'              => $r->id,
                'tentative_id'             => $r->tentative_id,
                'eleve_id'                 => $r->eleve_id,
                'eleve'                    => $r->eleve?->nom_complet,
                'matricule'                => $r->eleve?->matricule,
                'note_finale'              => $r->note_finale,
                'note_sur'                 => $r->note_sur,
                'pourcentage'              => $r->pourcentage,
                'mention'                  => $r->mention,
                'bonnes_reponses'          => $r->bonnes_reponses,
                'mauvaises_reponses'       => $r->mauvaises_reponses,
                'sans_reponse'             => $r->sans_reponse,
                'total_questions'          => $r->total_questions,
                'fraude_detectee'          => $r->fraude_detectee,
                'nb_evenements_antitriche' => $r->nb_evenements_antitriche,
                'necessite_correction'     => $this->necessiteCorrection($r->tentative_id),
                'duree_minutes'            => $r->tentative
                    ? round($r->tentative->duree_reelle_secondes / 60, 1)
                    : null,
            ]);

        // Stats globales
        $stats = [
            'nb_eleves'     => $resultats->count(),
            'moyenne'       => round($resultats->avg('note_finale'), 2),
            'mediane'       => $this->mediane($resultats->pluck('note_finale')->toArray()),
            'meilleure'     => $resultats->max('note_finale'),
            'moins_bonne'   => $resultats->min('note_finale'),
            'taux_reussite' => $resultats->count() > 0
                ? round($resultats->where('pourcentage', '>=', 50)->count() / $resultats->count() * 100, 1)
                : 0,
            'fraudes'       => $resultats->where('fraude_detectee', true)->count(),
            'a_corriger'    => $resultats->where('necessite_correction', true)->count(),
        ];

        // Distribution des notes
        $distribution = $this->calculerDistribution($resultats->pluck('note_finale')->toArray(), $devoir->note_sur);

        return view('enseignant.correction.resultats', compact(
            'devoir', 'resultats', 'stats', 'distribution'
        ));
    }

    // ── DÉTAIL D'UN ÉLÈVE ─────────────────────────────────────────────
    public function detailEleve(Request $request, int $devoirId, int $eleveId)
    {
        $devoir = Devoir::where('enseignant_id', $request->user()->id)
            ->with('questions.reponsesPossibles')
            ->findOrFail($devoirId);

        $tentative = TentativeDevoir::where('devoir_id', $devoirId)
            ->where('eleve_id', $eleveId)
            ->where('statut', 'soumis')
            ->with([
                'eleve:id,nom,prenoms,matricule,email',
                'reponsesEleves.question.reponsesPossibles',
                'reponsesEleves.reponsePossible',
                'evenementsAntitriche',
                'resultat',
            ])
            ->latest()
            ->firstOrFail();

        $reponses = $tentative->reponsesEleves
            ->sortBy('question.ordre')
            ->map(fn($r) => [
                'id'               => $r->id,
                'question_id'      => $r->question_id,
                'ordre'            => $r->question?->ordre,
                'enonce'           => $r->question?->enonce,
                'type'             => $r->question?->type,
                'points_max'       => $r->question?->points,
                'reponse_donnee'   => $r->reponsePossible?->texte ?? $r->texte_libre,
                'est_correcte'     => $r->est_correcte,
                'points_obtenus'   => $r->points_obtenus,
                'temps_utilise'    => $r->temps_utilise_secondes,
                'temps_expire'     => $r->temps_expire,
                'commentaire'      => $r->commentaire_enseignant,
                'explication'      => $r->question?->explication,
                'necessite_correction' => $r->necessiteCorrection(),
                'reponse_correcte' => $r->question?->reponsesPossibles
                    ->where('est_correcte', true)->first()?->texte,
            ]);

        $evenements = $tentative->evenementsAntitriche->map(fn($e) => [
            'type'       => $e->type,
            'label'      => $e->label,
            'question'   => $e->numero_question,
            'survenu_le' => $e->survenu_le->format('d/m/Y H:i:s'),
            'suspicieux' => $e->estSuspicieux(),
        ]);

        return view('enseignant.correction.detail-eleve', compact(
            'devoir', 'tentative', 'reponses', 'evenements'
        ));
    }

    // ── CORRIGER UNE RÉPONSE RÉDACTIONNELLE ───────────────────────────
    public function corriger(Request $request, int $reponseId)
    {
        $request->validate([
            'points_obtenus'         => 'required|numeric|min:0',
            'est_correcte'           => 'required|boolean',
            'commentaire_enseignant' => 'nullable|string|max:500',
        ]);

        $reponse = ReponseEleve::findOrFail($reponseId);

        // Vérifier que l'enseignant a accès à ce devoir
        $devoir = $reponse->tentative?->devoir;
        if ($devoir?->enseignant_id !== $request->user()->id) {
            abort(403);
        }

        // Vérifier que les points ne dépassent pas le max
        $pointsMax = $reponse->question?->points ?? 0;
        if ($request->points_obtenus > $pointsMax) {
            return back()->withErrors([
                'points_obtenus' => "Les points ne peuvent pas dépasser {$pointsMax}.",
            ]);
        }

        $reponse->update([
            'points_obtenus'         => $request->points_obtenus,
            'est_correcte'           => $request->est_correcte,
            'commentaire_enseignant' => $request->commentaire_enseignant,
        ]);

        // Recalculer la note totale
        $this->recalculerNote($reponse->tentative);

        return back()->with('success', 'Correction enregistrée.');
    }

    // ── CORRIGER TOUTES LES RÉPONSES D'UN ÉLÈVE EN UNE FOIS ──────────
    public function corrigerTout(Request $request, int $tentativeId)
    {
        $request->validate([
            'corrections'                   => 'required|array',
            'corrections.*.reponse_id'      => 'required|integer',
            'corrections.*.points_obtenus'  => 'required|numeric|min:0',
            'corrections.*.est_correcte'    => 'required|boolean',
            'corrections.*.commentaire'     => 'nullable|string|max:500',
        ]);

        $tentative = TentativeDevoir::findOrFail($tentativeId);

        if ($tentative->devoir?->enseignant_id !== $request->user()->id) {
            abort(403);
        }

        foreach ($request->corrections as $correction) {
            $reponse = ReponseEleve::find($correction['reponse_id']);
            if (!$reponse || $reponse->tentative_id !== $tentativeId) continue;

            $pointsMax = $reponse->question?->points ?? 0;
            $points    = min($correction['points_obtenus'], $pointsMax);

            $reponse->update([
                'points_obtenus'         => $points,
                'est_correcte'           => $correction['est_correcte'],
                'commentaire_enseignant' => $correction['commentaire'] ?? null,
            ]);
        }

        $this->recalculerNote($tentative);

        // Notifier l'élève
        if ($tentative->resultat) {
            app(NotificationService::class)->notifierResultat($tentative->resultat);
        }

        return back()->with('success', 'Toutes les corrections ont été enregistrées. L\'élève a été notifié.');
    }

    // ── RECALCULER LA NOTE APRÈS CORRECTION ───────────────────────────
    private function recalculerNote(TentativeDevoir $tentative): void
    {
        $totalPoints   = $tentative->devoir->questions->sum('points');
        $pointsObtenus = $tentative->reponsesEleves->sum('points_obtenus');

        $noteFinale = $totalPoints > 0
            ? round(($pointsObtenus / $totalPoints) * $tentative->devoir->note_sur, 2)
            : 0;

        $tentative->update([
            'note'          => $noteFinale,
            'note_calculee' => true,
        ]);

        $tentative->resultat?->update([
            'note_finale'        => $noteFinale,
            'pourcentage'        => round(($noteFinale / $tentative->devoir->note_sur) * 100, 1),
            'bonnes_reponses'    => $tentative->reponsesEleves->where('est_correcte', true)->count(),
            'mauvaises_reponses' => $tentative->reponsesEleves->where('est_correcte', false)->count(),
        ]);
    }

    // ── HELPERS ───────────────────────────────────────────────────────
    private function necessiteCorrection(int $tentativeId): bool
    {
        return ReponseEleve::where('tentative_id', $tentativeId)
            ->whereNull('est_correcte')
            ->whereHas('question', fn($q) => $q->whereIn('type', ['reponse_courte', 'redactionnel']))
            ->exists();
    }

    private function mediane(array $valeurs): float
    {
        if (empty($valeurs)) return 0;
        sort($valeurs);
        $n = count($valeurs);
        $m = (int) floor($n / 2);
        return $n % 2 === 0 ? ($valeurs[$m - 1] + $valeurs[$m]) / 2 : $valeurs[$m];
    }

    private function calculerDistribution(array $notes, float $noteSur): array
    {
        $tranches = [];
        $nb = 5;
        $pas = $noteSur / $nb;

        for ($i = 0; $i < $nb; $i++) {
            $min = $i * $pas;
            $max = ($i + 1) * $pas;
            $label = number_format($min, 0) . '-' . number_format($max, 0);
            $count = count(array_filter($notes, fn($n) => $n >= $min && $n <= $max));
            $tranches[$label] = $count;
        }

        return $tranches;
    }
}