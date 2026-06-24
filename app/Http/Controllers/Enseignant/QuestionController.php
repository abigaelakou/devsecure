<?php

namespace App\Http\Controllers\Enseignant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Devoir;
use App\Models\Tenant\Question;
use App\Models\Tenant\ReponsePossible;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class QuestionController extends Controller
{
    // ── LISTE DES QUESTIONS D'UN DEVOIR ───────────────────
    public function index(int $devoirId): JsonResponse
    {
        $questions = Question::where('devoir_id', $devoirId)
            ->with('reponsesPossibles')
            ->orderBy('ordre')
            ->get();

        return response()->json(['questions' => $questions]);
    }

    // ── CRÉER UNE QUESTION ────────────────────────────────
    public function store(Request $request, int $devoirId): JsonResponse
    {
        $devoir = Devoir::findOrFail($devoirId);

        $request->validate([
            'enonce'          => 'required|string',
            'type'            => 'required|in:qcm,vrai_faux,reponse_courte,redactionnel',
            'points'          => 'numeric|min:0.5|max:100',
            'temps_secondes'  => 'nullable|integer|min:10|max:600',
            'explication'     => 'nullable|string',
            'image'           => 'nullable|string',
            'reponses'        => 'required_if:type,qcm,vrai_faux|array|min:2',
            'reponses.*.texte'       => 'required|string',
            'reponses.*.est_correcte' => 'required|boolean',
        ]);

        // Valider qu'il y a au moins une bonne réponse pour QCM/Vrai-Faux
        if (in_array($request->type, ['qcm', 'vrai_faux'])) {
            $bonnesReponses = collect($request->reponses)->where('est_correcte', true)->count();
            if ($bonnesReponses === 0) {
                return response()->json([
                    'message' => 'Il doit y avoir au moins une bonne réponse.',
                ], 422);
            }
        }

        // Ordre automatique
        $ordre = Question::where('devoir_id', $devoirId)->max('ordre') + 1;

        $question = Question::create([
            'devoir_id'      => $devoirId,
            'enonce'         => $request->enonce,
            'type'           => $request->type,
            'points'         => $request->points ?? 1,
            'temps_secondes' => $request->temps_secondes,
            'explication'    => $request->explication,
            'image'          => $request->image,
            'ordre'          => $ordre,
        ]);

        // Créer les réponses possibles
        if ($request->filled('reponses')) {
            foreach ($request->reponses as $index => $reponseData) {
                ReponsePossible::create([
                    'question_id'  => $question->id,
                    'texte'        => $reponseData['texte'],
                    'est_correcte' => $reponseData['est_correcte'],
                    'ordre'        => $index + 1,
                ]);
            }
        }

        return response()->json([
            'message'  => 'Question créée.',
            'question' => $question->load('reponsesPossibles'),
        ], 201);
    }

    // ── MODIFIER UNE QUESTION ─────────────────────────────
    public function update(Request $request, int $devoirId, int $id): JsonResponse
    {
        $question = Question::where('devoir_id', $devoirId)->findOrFail($id);

        $request->validate([
            'enonce'         => 'sometimes|string',
            'points'         => 'numeric|min:0.5',
            'temps_secondes' => 'nullable|integer|min:10',
            'explication'    => 'nullable|string',
            'reponses'       => 'sometimes|array|min:2',
            'reponses.*.id'           => 'nullable|integer',
            'reponses.*.texte'        => 'required|string',
            'reponses.*.est_correcte' => 'required|boolean',
        ]);

        $question->update($request->only(['enonce', 'points', 'temps_secondes', 'explication', 'image']));

        // Mettre à jour les réponses si fournies
        if ($request->filled('reponses')) {
            $question->reponsesPossibles()->delete();
            foreach ($request->reponses as $index => $reponseData) {
                ReponsePossible::create([
                    'question_id'  => $question->id,
                    'texte'        => $reponseData['texte'],
                    'est_correcte' => $reponseData['est_correcte'],
                    'ordre'        => $index + 1,
                ]);
            }
        }

        return response()->json([
            'message'  => 'Question mise à jour.',
            'question' => $question->fresh('reponsesPossibles'),
        ]);
    }

    // ── SUPPRIMER UNE QUESTION ────────────────────────────
    public function destroy(int $devoirId, int $id): JsonResponse
    {
        $question = Question::where('devoir_id', $devoirId)->findOrFail($id);
        $question->delete();

        // Réordonner les questions restantes
        Question::where('devoir_id', $devoirId)
            ->orderBy('ordre')
            ->get()
            ->each(fn($q, $i) => $q->update(['ordre' => $i + 1]));

        return response()->json(['message' => 'Question supprimée.']);
    }

    // ── RÉORDONNER LES QUESTIONS ──────────────────────────
    public function reordonner(Request $request, int $devoirId): JsonResponse
    {
        $request->validate([
            'ordre'    => 'required|array',
            'ordre.*'  => 'integer|exists:questions,id',
        ]);

        foreach ($request->ordre as $position => $questionId) {
            Question::where('id', $questionId)
                ->where('devoir_id', $devoirId)
                ->update(['ordre' => $position + 1]);
        }

        return response()->json(['message' => 'Questions réordonnées.']);
    }
}