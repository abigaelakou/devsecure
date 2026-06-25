<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant\User;
use App\Models\Tenant\Matiere;
use App\Models\Tenant\Classe;
use App\Models\Tenant\AnneeScolaire;
use App\Models\Tenant\EnseignantMatiereClasse;
use Illuminate\Http\Request;

class AffectationController extends Controller
{
    // ── PAGE PRINCIPALE : liste des enseignants avec leurs affectations ──
    public function index()
    {
        $annee = AnneeScolaire::courante();

        $enseignants = User::enseignants()
            ->actifs()
            ->with(['matieres' => function ($q) use ($annee) {
                $q->withPivot('classe_id', 'annee_scolaire_id')
                  ->where('enseignant_matiere_classe.annee_scolaire_id', $annee?->id);
            }])
            ->orderBy('nom')
            ->get();

        $matieres = Matiere::orderBy('nom')->get();
        $classes  = Classe::where('annee_scolaire_id', $annee?->id)
            ->orderBy('niveau')->orderBy('nom')
            ->get();

        return view('admin.affectations.index', compact(
            'enseignants', 'matieres', 'classes', 'annee'
        ));
    }

    // ── DÉTAIL D'UN ENSEIGNANT : ses affectations ────────────────────────
    public function show(int $enseignantId)
    {
        $annee      = AnneeScolaire::courante();
        $enseignant = User::enseignants()->findOrFail($enseignantId);

        $affectations = EnseignantMatiereClasse::where('enseignant_id', $enseignantId)
            ->where('annee_scolaire_id', $annee?->id)
            ->with(['matiere', 'classe', 'anneeScolaire'])
            ->get();

        $matieres = Matiere::orderBy('nom')->get();
        $classes  = Classe::where('annee_scolaire_id', $annee?->id)
            ->orderBy('niveau')->orderBy('nom')
            ->get();

        return view('admin.affectations.show', compact(
            'enseignant', 'affectations', 'matieres', 'classes', 'annee'
        ));
    }

    // ── AJOUTER UNE AFFECTATION ──────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'enseignant_id' => 'required|integer|exists:users,id',
            'matiere_id'    => 'required|integer|exists:matieres,id',
            'classe_id'     => 'required|integer|exists:classes,id',
        ]);

        $annee = AnneeScolaire::courante();

        if (!$annee) {
            return back()->with('error', 'Aucune année scolaire active. Activez-en une d\'abord.');
        }

        // Vérifier si déjà affecté
        $existe = EnseignantMatiereClasse::where([
            'enseignant_id'     => $request->enseignant_id,
            'matiere_id'        => $request->matiere_id,
            'classe_id'         => $request->classe_id,
            'annee_scolaire_id' => $annee->id,
        ])->exists();

        if ($existe) {
            return back()->with('error', 'Cette affectation existe déjà.');
        }

        EnseignantMatiereClasse::create([
            'enseignant_id'     => $request->enseignant_id,
            'matiere_id'        => $request->matiere_id,
            'classe_id'         => $request->classe_id,
            'annee_scolaire_id' => $annee->id,
        ]);

        return back()->with('success', 'Affectation ajoutée avec succès.');
    }

    // ── AJOUTER PLUSIEURS CLASSES EN UNE FOIS ────────────────────────────
    public function storeMasse(Request $request)
    {
        $request->validate([
            'enseignant_id' => 'required|integer|exists:users,id',
            'matiere_id'    => 'required|integer|exists:matieres,id',
            'classe_ids'    => 'required|array|min:1',
            'classe_ids.*'  => 'integer|exists:classes,id',
        ]);

        $annee = AnneeScolaire::courante();

        if (!$annee) {
            return back()->with('error', 'Aucune année scolaire active.');
        }

        $ajoutes = 0;
        foreach ($request->classe_ids as $classeId) {
            $existe = EnseignantMatiereClasse::where([
                'enseignant_id'     => $request->enseignant_id,
                'matiere_id'        => $request->matiere_id,
                'classe_id'         => $classeId,
                'annee_scolaire_id' => $annee->id,
            ])->exists();

            if (!$existe) {
                EnseignantMatiereClasse::create([
                    'enseignant_id'     => $request->enseignant_id,
                    'matiere_id'        => $request->matiere_id,
                    'classe_id'         => $classeId,
                    'annee_scolaire_id' => $annee->id,
                ]);
                $ajoutes++;
            }
        }

        return back()->with('success', "{$ajoutes} affectation(s) ajoutée(s).");
    }

    // ── SUPPRIMER UNE AFFECTATION ────────────────────────────────────────
    public function destroy(int $id)
    {
        $affectation = EnseignantMatiereClasse::findOrFail($id);

        // Vérifier qu'il n'y a pas de devoirs publiés pour cette combinaison
        $devoirsActifs = \App\Models\Tenant\Devoir::where('enseignant_id', $affectation->enseignant_id)
            ->where('matiere_id', $affectation->matiere_id)
            ->where('classe_id', $affectation->classe_id)
            ->where('statut', 'actif')
            ->count();

        if ($devoirsActifs > 0) {
            return back()->with('error', 'Impossible : il y a des devoirs actifs pour cette affectation.');
        }

        $affectation->delete();

        return back()->with('success', 'Affectation supprimée.');
    }

    // ── COPIER LES AFFECTATIONS D'UNE ANNÉE À L'AUTRE ───────────────────
    public function copierAnnee(Request $request)
    {
        $request->validate([
            'annee_source_id' => 'required|integer|exists:annees_scolaires,id',
        ]);

        $anneeSource = \App\Models\Tenant\AnneeScolaire::findOrFail($request->annee_source_id);
        $annee       = AnneeScolaire::courante();

        if (!$annee) {
            return back()->with('error', 'Aucune année active.');
        }

        $affectations = EnseignantMatiereClasse::where('annee_scolaire_id', $anneeSource->id)->get();
        $copiees = 0;

        foreach ($affectations as $a) {
            $existe = EnseignantMatiereClasse::where([
                'enseignant_id'     => $a->enseignant_id,
                'matiere_id'        => $a->matiere_id,
                'classe_id'         => $a->classe_id,
                'annee_scolaire_id' => $annee->id,
            ])->exists();

            if (!$existe) {
                EnseignantMatiereClasse::create([
                    'enseignant_id'     => $a->enseignant_id,
                    'matiere_id'        => $a->matiere_id,
                    'classe_id'         => $a->classe_id,
                    'annee_scolaire_id' => $annee->id,
                ]);
                $copiees++;
            }
        }

        return back()->with('success', "{$copiees} affectation(s) copiée(s) vers {$annee->libelle}.");
    }
}