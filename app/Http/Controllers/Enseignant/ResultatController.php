<?php

namespace App\Http\Controllers\Enseignant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Devoir;
use App\Models\Tenant\Resultat;
use App\Models\Tenant\ReponseEleve;
use App\Models\Tenant\TentativeDevoir;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ResultatController extends Controller
{
    // ── RÉSULTATS D'UN DEVOIR ─────────────────────────────
    public function index(Request $request, int $id): JsonResponse
    {
        $devoir = Devoir::where('enseignant_id', $request->user()->id)
            ->findOrFail($id);

        $resultats = Resultat::where('devoir_id', $id)
            ->with(['eleve:id,nom,prenoms,matricule', 'tentative'])
            ->orderByDesc('note_finale')
            ->get()
            ->values()
            ->map(function ($resultat, $index) {
                return [
                    'rang'                     => $index + 1,
                    'eleve'                    => $resultat->eleve?->nom_complet,
                    'matricule'                => $resultat->eleve?->matricule,
                    'note_finale'              => $resultat->note_finale,
                    'note_sur'                 => $resultat->note_sur,
                    'pourcentage'              => $resultat->pourcentage,
                    'mention'                  => $resultat->mention,
                    'bonnes_reponses'          => $resultat->bonnes_reponses,
                    'mauvaises_reponses'       => $resultat->mauvaises_reponses,
                    'sans_reponse'             => $resultat->sans_reponse,
                    'fraude_detectee'          => $resultat->fraude_detectee,
                    'nb_evenements_antitriche' => $resultat->nb_evenements_antitriche,
                    'duree_minutes'            => $resultat->tentative
                        ? round($resultat->tentative->duree_reelle_secondes / 60, 1)
                        : null,
                    'tentative_id'             => $resultat->tentative_id,
                ];
            });

        return response()->json([
            'devoir'    => ['id' => $devoir->id, 'titre' => $devoir->titre],
            'resultats' => $resultats,
            'stats'     => [
                'nb_eleves'     => $resultats->count(),
                'moyenne'       => round($resultats->avg('note_finale'), 2),
                'meilleure'     => $resultats->max('note_finale'),
                'moins_bonne'   => $resultats->min('note_finale'),
                'taux_reussite' => $resultats->count() > 0
                    ? round($resultats->where('pourcentage', '>=', 50)->count() / $resultats->count() * 100, 1)
                    : 0,
                'fraudes'       => $resultats->where('fraude_detectee', true)->count(),
            ],
        ]);
    }

    // ── DÉTAIL D'UN ÉLÈVE ─────────────────────────────────
    public function show(Request $request, int $id, int $eleveId): JsonResponse
    {
        $devoir = Devoir::where('enseignant_id', $request->user()->id)->findOrFail($id);

        $tentative = TentativeDevoir::where('devoir_id', $id)
            ->where('eleve_id', $eleveId)
            ->where('statut', 'soumis')
            ->with([
                'eleve:id,nom,prenoms,matricule',
                'reponsesEleves.question.reponsesPossibles',
                'reponsesEleves.reponsePossible',
                'evenementsAntitriche',
                'resultat',
            ])
            ->latest()
            ->firstOrFail();

        $reponses = $tentative->reponsesEleves->map(fn($r) => [
            'question_id'      => $r->question_id,
            'question'         => $r->question?->enonce,
            'type'             => $r->question?->type,
            'reponse_donnee'   => $r->reponsePossible?->texte ?? $r->texte_libre ?? '(Sans réponse)',
            'est_correcte'     => $r->est_correcte,
            'points_obtenus'   => $r->points_obtenus,
            'points_total'     => $r->question?->points,
            'temps_utilise'    => $r->temps_utilise_secondes,
            'temps_expire'     => $r->temps_expire,
            'commentaire'      => $r->commentaire_enseignant,
            'reponse_id'       => $r->id,
            'necessite_correction' => $r->necessiteCorrection(),
        ]);

        $evenements = $tentative->evenementsAntitriche->map(fn($e) => [
            'type'       => $e->type,
            'label'      => $e->label,
            'question'   => $e->numero_question,
            'survenu_le' => $e->survenu_le->toIso8601String(),
        ]);

        return response()->json([
            'eleve'      => $tentative->eleve,
            'resultat'   => $tentative->resultat,
            'reponses'   => $reponses,
            'evenements_antitriche' => $evenements,
            'timing' => [
                'debut_le'              => $tentative->debut_le?->toIso8601String(),
                'fin_le'                => $tentative->fin_le?->toIso8601String(),
                'duree_reelle_secondes' => $tentative->duree_reelle_secondes,
            ],
        ]);
    }

    // ── CORRECTION MANUELLE (rédactionnel) ────────────────
    public function corrigerManuel(Request $request, int $reponseId): JsonResponse
    {
        $request->validate([
            'est_correcte'           => 'required|boolean',
            'points_obtenus'         => 'required|numeric|min:0',
            'commentaire_enseignant' => 'nullable|string|max:1000',
        ]);

        $reponse = ReponseEleve::findOrFail($reponseId);

        $reponse->update([
            'est_correcte'           => $request->est_correcte,
            'points_obtenus'         => $request->points_obtenus,
            'commentaire_enseignant' => $request->commentaire_enseignant,
        ]);

        // Recalculer la note totale de la tentative
        $tentative = $reponse->tentative;
        $totalPoints  = $tentative->devoir->questions->sum('points');
        $pointsObtenus = $tentative->reponsesEleves->sum('points_obtenus');
        $noteFinale = $totalPoints > 0
            ? round(($pointsObtenus / $totalPoints) * $tentative->devoir->note_sur, 2)
            : 0;

        $tentative->resultat?->update([
            'note_finale'        => $noteFinale,
            'pourcentage'        => round(($noteFinale / $tentative->devoir->note_sur) * 100, 1),
            'bonnes_reponses'    => $tentative->reponsesEleves->where('est_correcte', true)->count(),
            'mauvaises_reponses' => $tentative->reponsesEleves->where('est_correcte', false)->count(),
        ]);

        return response()->json([
            'message'     => 'Correction enregistrée.',
            'note_finale' => $noteFinale,
        ]);
    }
}