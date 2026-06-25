<?php

namespace App\Http\Controllers\Web\Enseignant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Devoir;
use App\Models\Tenant\Question;
use App\Models\Tenant\ReponsePossible;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuestionController extends Controller
{
    public function store(Request $request, int $devoirId)
    {
        $devoir = Devoir::where('enseignant_id', $request->user()->id)->findOrFail($devoirId);

        // Validation dynamique selon le type
        $rules = [
            'enonce'      => 'required|string|max:1000',
            'type'        => 'required|in:qcm,vrai_faux,reponse_courte,redactionnel',
            'points'      => 'required|numeric|min:0.5|max:100',
            'explication' => 'nullable|string|max:500',
        ];

        if ($request->type === 'qcm') {
            $rules['reponses']         = 'required|array|min:2|max:6';
            $rules['reponses.*.texte'] = 'required|string|max:500';
            // est_correcte est une checkbox — absent = non coché = false, pas besoin de valider
        }

        if ($request->type === 'vrai_faux') {
            $rules['reponse_correcte_vf'] = 'required|in:vrai,faux';
        }

        $request->validate($rules);

        // Vérifier qu'au moins une réponse QCM est cochée
        if ($request->type === 'qcm') {
            $aUneCorrecte = collect($request->reponses ?? [])->contains(
                fn($r) => !empty($r['est_correcte'])
            );
            if (!$aUneCorrecte) {
                return back()
                    ->withInput()
                    ->with('error', 'Veuillez cocher au moins une bonne réponse.');
            }
        }

        DB::transaction(function () use ($request, $devoir) {
            $ordre    = $devoir->questions()->max('ordre') + 1;
            $question = Question::create([
                'devoir_id'   => $devoir->id,
                'enonce'      => $request->enonce,
                'type'        => $request->type,
                'points'      => $request->points,
                'ordre'       => $ordre,
                'explication' => $request->explication,
                'obligatoire' => true,
            ]);

            match($request->type) {
                'qcm'            => $this->creerReponsesQcm($question, $request->reponses ?? []),
                'vrai_faux'      => $this->creerReponsesVraiFaux($question, $request->reponse_correcte_vf),
                'reponse_courte' => $this->creerReponseCorte($question, $request->reponse_courte_attendue),
                default          => null, // redactionnel : pas de réponses possibles
            };
        });

        return back()->with('success', 'Question ajoutée avec succès.');
    }

    public function update(Request $request, int $devoirId, int $questionId)
    {
        $devoir   = Devoir::where('enseignant_id', $request->user()->id)->findOrFail($devoirId);
        $question = Question::where('devoir_id', $devoir->id)->findOrFail($questionId);

        $request->validate([
            'enonce'      => 'required|string|max:1000',
            'points'      => 'required|numeric|min:0.5|max:100',
            'explication' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($request, $question) {
            $question->update([
                'enonce'      => $request->enonce,
                'points'      => $request->points,
                'explication' => $request->explication,
            ]);

            if ($question->type === 'qcm' && $request->has('reponses')) {
                $question->reponsesPossibles()->delete();
                $this->creerReponsesQcm($question, $request->reponses ?? []);
            }
            if ($question->type === 'vrai_faux' && $request->has('reponse_correcte_vf')) {
                $question->reponsesPossibles()->delete();
                $this->creerReponsesVraiFaux($question, $request->reponse_correcte_vf);
            }
            if ($question->type === 'reponse_courte' && $request->has('reponse_courte_attendue')) {
                $question->reponsesPossibles()->delete();
                $this->creerReponseCorte($question, $request->reponse_courte_attendue);
            }
        });

        return back()->with('success', 'Question mise à jour.');
    }

    public function destroy(Request $request, int $devoirId, int $questionId)
    {
        $devoir   = Devoir::where('enseignant_id', $request->user()->id)->findOrFail($devoirId);
        $question = Question::where('devoir_id', $devoir->id)->findOrFail($questionId);

        if ($question->reponsesEleves()->exists()) {
            return back()->with('error', 'Impossible : des élèves ont déjà répondu à cette question.');
        }

        $question->reponsesPossibles()->delete();
        $question->delete();

        $devoir->questions()->orderBy('ordre')->each(fn($q, $i) => $q->update(['ordre' => $i + 1]));

        return back()->with('success', 'Question supprimée.');
    }

    public function dupliquer(Request $request, int $devoirId, int $questionId)
    {
        $devoir   = Devoir::where('enseignant_id', $request->user()->id)->findOrFail($devoirId);
        $question = Question::where('devoir_id', $devoir->id)
            ->with('reponsesPossibles')
            ->findOrFail($questionId);

        DB::transaction(function () use ($question, $devoir) {
            $nouvelle = Question::create([
                'devoir_id'   => $devoir->id,
                'enonce'      => $question->enonce . ' (copie)',
                'type'        => $question->type,
                'points'      => $question->points,
                'ordre'       => $devoir->questions()->max('ordre') + 1,
                'explication' => $question->explication,
                'obligatoire' => $question->obligatoire,
            ]);
            foreach ($question->reponsesPossibles as $r) {
                ReponsePossible::create([
                    'question_id'  => $nouvelle->id,
                    'texte'        => $r->texte,
                    'est_correcte' => $r->est_correcte,
                    'ordre'        => $r->ordre,
                ]);
            }
        });

        return back()->with('success', 'Question dupliquée.');
    }

    public function reordonner(Request $request, int $devoirId)
    {
        $devoir = Devoir::where('enseignant_id', $request->user()->id)->findOrFail($devoirId);
        foreach ($request->ordres ?? [] as $questionId => $ordre) {
            Question::where('id', $questionId)
                ->where('devoir_id', $devoir->id)
                ->update(['ordre' => $ordre]);
        }
        return response()->json(['success' => true]);
    }

    // ── HELPERS ──────────────────────────────────────────
    private function creerReponsesQcm(Question $question, array $reponses): void
    {
        foreach ($reponses as $i => $r) {
            if (empty(trim($r['texte'] ?? ''))) continue;
            ReponsePossible::create([
                'question_id'  => $question->id,
                'texte'        => trim($r['texte']),
                'est_correcte' => !empty($r['est_correcte']),
                'ordre'        => $i + 1,
            ]);
        }
    }

    private function creerReponsesVraiFaux(Question $question, string $correcte): void
    {
        ReponsePossible::create([
            'question_id'  => $question->id,
            'texte'        => 'Vrai',
            'est_correcte' => $correcte === 'vrai',
            'ordre'        => 1,
        ]);
        ReponsePossible::create([
            'question_id'  => $question->id,
            'texte'        => 'Faux',
            'est_correcte' => $correcte === 'faux',
            'ordre'        => 2,
        ]);
    }

    private function creerReponseCorte(Question $question, ?string $attendue): void
    {
        if (!empty(trim($attendue ?? ''))) {
            ReponsePossible::create([
                'question_id'  => $question->id,
                'texte'        => trim($attendue),
                'est_correcte' => true,
                'ordre'        => 1,
            ]);
        }
    }
}