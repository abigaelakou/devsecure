<?php

namespace App\Http\Controllers\Enseignant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Devoir;
use App\Models\Tenant\Classe;
use App\Models\Tenant\Matiere;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DevoirController extends Controller
{
    // ── LISTE DES DEVOIRS ─────────────────────────────────
    public function index(Request $request): JsonResponse
    {
        $devoirs = Devoir::where('enseignant_id', $request->user()->id)
            ->with(['matiere:id,nom,couleur', 'classe:id,nom'])
            ->withCount(['questions', 'tentatives', 'resultats'])
            ->latest()
            ->paginate(15);

        return response()->json($devoirs);
    }

    // ── DÉTAIL ────────────────────────────────────────────
    public function show(int $id): JsonResponse
    {
        $devoir = Devoir::with([
            'matiere', 'classe', 'anneeScolaire',
            'questions.reponsesPossibles',
        ])->findOrFail($id);

        return response()->json(['devoir' => $devoir]);
    }

    // ── CRÉER ─────────────────────────────────────────────
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'titre'                       => 'required|string|max:255',
            'description'                 => 'nullable|string',
            'matiere_id'                  => 'required|integer|exists:matieres,id',
            'classe_id'                   => 'required|integer|exists:classes,id',
            'annee_scolaire_id'           => 'required|integer|exists:annees_scolaires,id',
            'disponible_le'               => 'nullable|date',
            'expire_le'                   => 'nullable|date|after:disponible_le',
            'duree_totale_minutes'        => 'nullable|integer|min:1|max:300',
            'temps_par_question_secondes' => 'nullable|integer|min:10|max:600',
            'max_changements_onglet'      => 'integer|min:0|max:10',
            'soumettre_auto_sortie'       => 'boolean',
            'questions_aleatoires'        => 'boolean',
            'reponses_aleatoires'         => 'boolean',
            'max_tentatives'              => 'integer|min:1|max:5',
            'note_sur'                    => 'numeric|min:1|max:100',
            'correction_auto'             => 'boolean',
        ]);

        $devoir = Devoir::create([
            ...$request->validated(),
            'enseignant_id' => $request->user()->id,
            'statut'        => 'brouillon',
        ]);

        return response()->json([
            'message' => 'Devoir créé avec succès.',
            'devoir'  => $devoir->load(['matiere', 'classe']),
        ], 201);
    }

    // ── MODIFIER ──────────────────────────────────────────
    public function update(Request $request, int $id): JsonResponse
    {
        $devoir = Devoir::where('enseignant_id', $request->user()->id)
            ->findOrFail($id);

        if ($devoir->statut === 'archive') {
            return response()->json(['message' => 'Un devoir archivé ne peut pas être modifié.'], 403);
        }

        $request->validate([
            'titre'                       => 'sometimes|string|max:255',
            'description'                 => 'nullable|string',
            'matiere_id'                  => 'sometimes|integer|exists:matieres,id',
            'classe_id'                   => 'sometimes|integer|exists:classes,id',
            'disponible_le'               => 'nullable|date',
            'expire_le'                   => 'nullable|date',
            'duree_totale_minutes'        => 'nullable|integer|min:1|max:300',
            'temps_par_question_secondes' => 'nullable|integer|min:10',
            'max_changements_onglet'      => 'integer|min:0|max:10',
            'questions_aleatoires'        => 'boolean',
            'reponses_aleatoires'         => 'boolean',
            'max_tentatives'              => 'integer|min:1|max:5',
            'note_sur'                    => 'numeric|min:1',
        ]);

        $devoir->update($request->validated());

        return response()->json([
            'message' => 'Devoir mis à jour.',
            'devoir'  => $devoir->fresh(['matiere', 'classe']),
        ]);
    }

    // ── SUPPRIMER ─────────────────────────────────────────
    public function destroy(Request $request, int $id): JsonResponse
    {
        $devoir = Devoir::where('enseignant_id', $request->user()->id)
            ->findOrFail($id);

        if ($devoir->tentatives()->exists()) {
            return response()->json([
                'message' => 'Impossible de supprimer : des élèves ont déjà composé ce devoir.',
            ], 403);
        }

        $devoir->delete();

        return response()->json(['message' => 'Devoir supprimé.']);
    }

    // ── PUBLIER ───────────────────────────────────────────
    public function publier(Request $request, int $id): JsonResponse
    {
        $devoir = Devoir::where('enseignant_id', $request->user()->id)
            ->findOrFail($id);

        if ($devoir->questions()->count() === 0) {
            return response()->json([
                'message' => 'Impossible de publier un devoir sans questions.',
            ], 422);
        }

        $devoir->publier();

        return response()->json(['message' => 'Devoir publié avec succès.', 'devoir' => $devoir]);
    }

    // ── ARCHIVER ──────────────────────────────────────────
    public function archiver(Request $request, int $id): JsonResponse
    {
        $devoir = Devoir::where('enseignant_id', $request->user()->id)
            ->findOrFail($id);

        $devoir->archiver();

        return response()->json(['message' => 'Devoir archivé.']);
    }

    // ── DUPLIQUER ─────────────────────────────────────────
    public function dupliquer(Request $request, int $id): JsonResponse
    {
        $devoir = Devoir::with('questions.reponsesPossibles')
            ->where('enseignant_id', $request->user()->id)
            ->findOrFail($id);

        $nouveau = $devoir->replicate();
        $nouveau->titre  = $devoir->titre . ' (copie)';
        $nouveau->statut = 'brouillon';
        $nouveau->disponible_le = null;
        $nouveau->expire_le     = null;
        $nouveau->save();

        // Dupliquer les questions et réponses
        foreach ($devoir->questions as $question) {
            $nouvelleQuestion = $question->replicate();
            $nouvelleQuestion->devoir_id = $nouveau->id;
            $nouvelleQuestion->save();

            foreach ($question->reponsesPossibles as $reponse) {
                $nouvelleReponse = $reponse->replicate();
                $nouvelleReponse->question_id = $nouvelleQuestion->id;
                $nouvelleReponse->save();
            }
        }

        return response()->json([
            'message' => 'Devoir dupliqué avec succès.',
            'devoir'  => $nouveau->load(['matiere', 'classe']),
        ], 201);
    }
}