<?php

namespace App\Http\Controllers\Web\Enseignant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Devoir;
use App\Models\Tenant\Matiere;
use App\Models\Tenant\Classe;
use App\Models\Tenant\AnneeScolaire;
use App\Models\Tenant\Resultat;
use Illuminate\Http\Request;

class DevoirWebController extends Controller
{
    // ── LISTE ─────────────────────────────────────────────
    public function index(Request $request)
    {
        $devoirs = Devoir::where('enseignant_id', $request->user()->id)
            ->with(['matiere', 'classe'])
            ->withCount(['questions', 'tentatives', 'resultats'])
            ->when($request->statut, fn($q) => $q->where('statut', $request->statut))
            ->when($request->matiere_id, fn($q) => $q->where('matiere_id', $request->matiere_id))
            ->latest()
            ->paginate(15);

        $matieres = Matiere::orderBy('nom')->get();

        return view('enseignant.devoirs.index', compact('devoirs', 'matieres'));
    }

    // ── CRÉER ─────────────────────────────────────────────
    public function create()
    {
        $matieres = Matiere::orderBy('nom')->get();
        $annee    = AnneeScolaire::courante();
        $classes  = Classe::where('annee_scolaire_id', $annee?->id)->orderBy('niveau')->orderBy('nom')->get();

        return view('enseignant.devoirs.create', compact('matieres', 'classes', 'annee'));
    }

    // ── SAUVEGARDER ───────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'titre'                       => 'required|string|max:255',
            'matiere_id'                  => 'required|exists:matieres,id',
            'classe_id'                   => 'required|exists:classes,id',
            'duree_totale_minutes'        => 'nullable|integer|min:5|max:300',
            'temps_par_question_secondes' => 'nullable|integer|min:10|max:600',
            'max_changements_onglet'      => 'integer|min:0|max:10',
            'max_tentatives'              => 'integer|min:1|max:5',
            'note_sur'                    => 'numeric|min:1|max:100',
            'disponible_le'               => 'nullable|date',
            'expire_le'                   => 'nullable|date|after:disponible_le',
        ]);

        $annee = AnneeScolaire::courante();

        $devoir = Devoir::create([
            'titre'                       => $request->titre,
            'description'                 => $request->description,
            'enseignant_id'               => $request->user()->id,
            'matiere_id'                  => $request->matiere_id,
            'classe_id'                   => $request->classe_id,
            'annee_scolaire_id'           => $annee->id,
            'disponible_le'               => $request->disponible_le,
            'expire_le'                   => $request->expire_le,
            'duree_totale_minutes'        => $request->duree_totale_minutes,
            'temps_par_question_secondes' => $request->temps_par_question_secondes,
            'max_changements_onglet'      => $request->max_changements_onglet ?? 3,
            'soumettre_auto_sortie'       => $request->boolean('soumettre_auto_sortie', true),
            'questions_aleatoires'        => $request->boolean('questions_aleatoires'),
            'reponses_aleatoires'         => $request->boolean('reponses_aleatoires'),
            'max_tentatives'              => $request->max_tentatives ?? 1,
            'note_sur'                    => $request->note_sur ?? 20,
            'correction_auto'             => $request->boolean('correction_auto', true),
            'statut'                      => 'brouillon',
        ]);

        return redirect()->route('enseignant.devoirs.edit', $devoir->id)
            ->with('success', 'Devoir créé ! Ajoutez maintenant les questions.');
    }

    // ── MODIFIER ──────────────────────────────────────────
    public function edit(int $id)
    {
        $devoir   = Devoir::with('questions.reponsesPossibles')->findOrFail($id);
        $matieres = Matiere::orderBy('nom')->get();
        $annee    = AnneeScolaire::courante();
        $classes  = Classe::where('annee_scolaire_id', $annee?->id)->orderBy('niveau')->get();

        return view('enseignant.devoirs.edit', compact('devoir', 'matieres', 'classes'));
    }

    // ── METTRE À JOUR ─────────────────────────────────────
    public function update(Request $request, int $id)
    {
        $devoir = Devoir::where('enseignant_id', $request->user()->id)->findOrFail($id);

        $devoir->update($request->only([
            'titre', 'description', 'matiere_id', 'classe_id',
            'disponible_le', 'expire_le', 'duree_totale_minutes',
            'temps_par_question_secondes', 'max_changements_onglet',
            'max_tentatives', 'note_sur', 'questions_aleatoires', 'reponses_aleatoires',
        ]));

        return back()->with('success', 'Devoir mis à jour.');
    }

    // ── SUPPRIMER ─────────────────────────────────────────
    public function destroy(Request $request, int $id)
    {
        $devoir = Devoir::where('enseignant_id', $request->user()->id)->findOrFail($id);

        if ($devoir->tentatives()->exists()) {
            return back()->with('error', 'Impossible : des élèves ont déjà composé ce devoir.');
        }

        $devoir->delete();

        return redirect()->route('enseignant.devoirs.index')->with('success', 'Devoir supprimé.');
    }

    // ── PUBLIER ───────────────────────────────────────────
    public function publier(Request $request, int $id)
    {
        $devoir = Devoir::where('enseignant_id', $request->user()->id)->findOrFail($id);

        if ($devoir->questions()->count() === 0) {
            return back()->with('error', 'Ajoutez au moins une question avant de publier.');
        }

        $devoir->publier();

        return back()->with('success', 'Devoir publié avec succès !');
    }

    // ── RÉSULTATS ─────────────────────────────────────────
    public function resultats(int $id)
    {
        $devoir    = Devoir::with(['matiere', 'classe'])->findOrFail($id);
        $resultats = Resultat::where('devoir_id', $id)
            ->with(['eleve:id,nom,prenoms,matricule', 'tentative'])
            ->orderByDesc('note_finale')
            ->get()
            ->map(function ($r, $index) {
                $r->rang = $index + 1;
                return $r;
            });

        $stats = [
            'nb_eleves'     => $resultats->count(),
            'moyenne'       => round($resultats->avg('note_finale'), 2),
            'meilleure'     => $resultats->max('note_finale'),
            'moins_bonne'   => $resultats->min('note_finale'),
            'taux_reussite' => $resultats->count() > 0
                ? round($resultats->where('pourcentage', '>=', 50)->count() / $resultats->count() * 100, 1)
                : 0,
            'fraudes'       => $resultats->where('fraude_detectee', true)->count(),
        ];

        return view('enseignant.devoirs.resultats', compact('devoir', 'resultats', 'stats'));
    }
}