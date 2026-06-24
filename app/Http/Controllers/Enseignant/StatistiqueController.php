<?php

namespace App\Http\Controllers\Enseignant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Devoir;
use App\Models\Tenant\Resultat;
use App\Models\Tenant\Question;
use App\Models\Tenant\ReponseEleve;
use App\Models\Tenant\EvenementAntitriche;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class StatistiqueController extends Controller
{
    // ── STATISTIQUES D'UN DEVOIR ──────────────────────────
    public function devoir(Request $request, int $id): JsonResponse
    {
        $devoir = Devoir::where('enseignant_id', $request->user()->id)
            ->with('questions')
            ->findOrFail($id);

        $resultats = Resultat::where('devoir_id', $id)->get();

        if ($resultats->isEmpty()) {
            return response()->json(['message' => 'Aucun résultat disponible.'], 404);
        }

        // Distribution des notes par tranche
        $tranches = [
            '0-4'   => [0, 4],
            '5-7'   => [5, 7],
            '8-9'   => [8, 9],
            '10-12' => [10, 12],
            '13-15' => [13, 15],
            '16-18' => [16, 18],
            '19-20' => [19, 20],
        ];

        $distribution = [];
        foreach ($tranches as $label => [$min, $max]) {
            $noteSur20 = $resultats->filter(
                fn($r) => ($r->note_finale / $r->note_sur * 20) >= $min
                    && ($r->note_finale / $r->note_sur * 20) <= $max
            )->count();
            $distribution[$label] = $noteSur20;
        }

        // Questions les plus ratées / mieux réussies
        $statsQuestions = $devoir->questions->map(function ($question) {
            $reponses    = ReponseEleve::where('question_id', $question->id)->get();
            $nbReponses  = $reponses->count();
            $nbCorrectes = $reponses->where('est_correcte', true)->count();
            $tauxReussite = $nbReponses > 0 ? round($nbCorrectes / $nbReponses * 100, 1) : 0;
            $tempsMoyen   = $reponses->avg('temps_utilise_secondes');

            return [
                'question_id'   => $question->id,
                'enonce_court'  => substr($question->enonce, 0, 60) . '...',
                'ordre'         => $question->ordre,
                'taux_reussite' => $tauxReussite,
                'nb_reponses'   => $nbReponses,
                'temps_moyen'   => $tempsMoyen ? round($tempsMoyen) : null,
            ];
        })->sortBy('taux_reussite')->values();

        // Antitriche global du devoir
        $totalEvenements = EvenementAntitriche::whereHas('tentative', fn($q) => $q->where('devoir_id', $id))
            ->selectRaw('type, COUNT(*) as nb')
            ->groupBy('type')
            ->pluck('nb', 'type');

        return response()->json([
            'devoir' => ['id' => $devoir->id, 'titre' => $devoir->titre],
            'global' => [
                'nb_eleves'       => $resultats->count(),
                'moyenne'         => round($resultats->avg('note_finale'), 2),
                'mediane'         => $this->mediane($resultats->pluck('note_finale')->toArray()),
                'meilleure_note'  => $resultats->max('note_finale'),
                'moins_bonne'     => $resultats->min('note_finale'),
                'taux_reussite'   => round($resultats->where('pourcentage', '>=', 50)->count() / $resultats->count() * 100, 1),
                'temps_moyen_min' => round($resultats->avg(fn($r) => $r->tentative?->duree_reelle_secondes / 60), 1),
                'fraudes'         => $resultats->where('fraude_detectee', true)->count(),
            ],
            'distribution_notes'  => $distribution,
            'stats_questions'     => $statsQuestions,
            'evenements_antitriche' => $totalEvenements,
        ]);
    }

    // ── STATISTIQUES D'UNE CLASSE ─────────────────────────
    public function classe(Request $request, int $classeId): JsonResponse
    {
        $devoirs = Devoir::where('enseignant_id', $request->user()->id)
            ->where('classe_id', $classeId)
            ->whereHas('resultats')
            ->with(['matiere:id,nom,couleur', 'resultats'])
            ->get();

        $statsParDevoir = $devoirs->map(fn($d) => [
            'devoir_id' => $d->id,
            'titre'     => $d->titre,
            'matiere'   => $d->matiere?->nom,
            'couleur'   => $d->matiere?->couleur,
            'moyenne'   => round($d->resultats->avg('note_finale'), 2),
            'note_sur'  => $d->note_sur,
            'nb_eleves' => $d->resultats->count(),
        ]);

        return response()->json([
            'classe_id'       => $classeId,
            'stats_par_devoir' => $statsParDevoir,
        ]);
    }

    // ── HELPER : calcul médiane ────────────────────────────
    private function mediane(array $valeurs): float
    {
        if (empty($valeurs)) return 0;
        sort($valeurs);
        $n = count($valeurs);
        $milieu = (int) floor($n / 2);
        return $n % 2 === 0
            ? ($valeurs[$milieu - 1] + $valeurs[$milieu]) / 2
            : $valeurs[$milieu];
    }
}