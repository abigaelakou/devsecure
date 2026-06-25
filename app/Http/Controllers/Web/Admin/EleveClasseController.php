<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant\User;
use App\Models\Tenant\Classe;
use App\Models\Tenant\AnneeScolaire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EleveClasseController extends Controller
{
    // ── PAGE PRINCIPALE : classes avec leurs élèves ──────────────────────
    public function index(Request $request)
    {
        $annee = AnneeScolaire::courante();

        $classes = Classe::where('annee_scolaire_id', $annee?->id)
            ->withCount(['eleves as eleves_count'])
            ->orderBy('niveau')
            ->orderBy('nom')
            ->get();

        // Élèves sans classe pour l'année courante
        $elevesNonAffectes = User::eleves()
            ->actifs()
            ->whereDoesntHave('classes', function ($q) use ($annee) {
                $q->where('annee_scolaire_id', $annee?->id);
            })
            ->orderBy('nom')
            ->get();

        return view('admin.eleve-classes.index', compact(
            'classes', 'elevesNonAffectes', 'annee'
        ));
    }

    // ── DÉTAIL D'UNE CLASSE : ses élèves ────────────────────────────────
    public function show(int $classeId)
    {
        $annee  = AnneeScolaire::courante();
        $classe = Classe::findOrFail($classeId);

        $eleves = $classe->eleves()
            ->where('eleve_classe.annee_scolaire_id', $annee?->id)
            ->orderBy('nom')
            ->get();

        // Élèves non affectés à cette classe (pour pouvoir en ajouter)
        $elevesDisponibles = User::eleves()
            ->actifs()
            ->whereDoesntHave('classes', function ($q) use ($classeId, $annee) {
                $q->where('classes.id', $classeId)
                  ->where('annee_scolaire_id', $annee?->id);
            })
            ->orderBy('nom')
            ->get();

        return view('admin.eleve-classes.show', compact(
            'classe', 'eleves', 'elevesDisponibles', 'annee'
        ));
    }

    // ── AFFECTER UN ÉLÈVE À UNE CLASSE ──────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'eleve_id'  => 'required|integer|exists:users,id',
            'classe_id' => 'required|integer|exists:classes,id',
        ]);

        $annee = AnneeScolaire::courante();

        if (!$annee) {
            return back()->with('error', 'Aucune année scolaire active.');
        }

        $eleve  = User::findOrFail($request->eleve_id);
        $classe = Classe::findOrFail($request->classe_id);

        // Vérifier si déjà dans cette classe
        $dejaAffecte = $eleve->classes()
            ->where('classes.id', $request->classe_id)
            ->where('annee_scolaire_id', $annee->id)
            ->exists();

        if ($dejaAffecte) {
            return back()->with('error', "{$eleve->nom_complet} est déjà dans {$classe->nom}.");
        }

        // Vérifier si déjà dans une autre classe cette année
        $autreClasse = $eleve->classes()
            ->where('annee_scolaire_id', $annee->id)
            ->first();

        if ($autreClasse) {
            return back()->with('error', "{$eleve->nom_complet} est déjà affecté à {$autreClasse->nom}. Retirez-le d'abord.");
        }

        $eleve->classes()->attach($request->classe_id, [
            'annee_scolaire_id' => $annee->id,
        ]);

        // Mettre à jour l'effectif
        $classe->majEffectif();

        return back()->with('success', "{$eleve->nom_complet} affecté à {$classe->nom}.");
    }

    // ── AFFECTER PLUSIEURS ÉLÈVES EN UNE FOIS ───────────────────────────
    public function storeMasse(Request $request)
    {
        $request->validate([
            'classe_id'  => 'required|integer|exists:classes,id',
            'eleve_ids'  => 'required|array|min:1',
            'eleve_ids.*'=> 'integer|exists:users,id',
        ]);

        $annee  = AnneeScolaire::courante();
        $classe = Classe::findOrFail($request->classe_id);

        if (!$annee) {
            return back()->with('error', 'Aucune année scolaire active.');
        }

        $ajoutes = 0;
        $ignores = 0;

        foreach ($request->eleve_ids as $eleveId) {
            $eleve = User::find($eleveId);
            if (!$eleve) continue;

            // Déjà dans une classe cette année ?
            $dejaAffecte = $eleve->classes()
                ->where('annee_scolaire_id', $annee->id)
                ->exists();

            if ($dejaAffecte) {
                $ignores++;
                continue;
            }

            $eleve->classes()->attach($request->classe_id, [
                'annee_scolaire_id' => $annee->id,
            ]);
            $ajoutes++;
        }

        $classe->majEffectif();

        $msg = "{$ajoutes} élève(s) affecté(s) à {$classe->nom}.";
        if ($ignores > 0) {
            $msg .= " {$ignores} ignoré(s) (déjà dans une classe).";
        }

        return back()->with('success', $msg);
    }

    // ── RETIRER UN ÉLÈVE D'UNE CLASSE ───────────────────────────────────
    public function destroy(Request $request, int $classeId, int $eleveId)
    {
        $annee  = AnneeScolaire::courante();
        $classe = Classe::findOrFail($classeId);
        $eleve  = User::findOrFail($eleveId);

        // Vérifier qu'il n'a pas de tentatives en cours pour cette classe
        $tentativesEnCours = \App\Models\Tenant\TentativeDevoir::where('eleve_id', $eleveId)
            ->where('statut', 'en_cours')
            ->whereHas('devoir', fn($q) => $q->where('classe_id', $classeId))
            ->count();

        if ($tentativesEnCours > 0) {
            return back()->with('error', "{$eleve->nom_complet} a un devoir en cours. Impossible de le retirer.");
        }

        $eleve->classes()->wherePivot('annee_scolaire_id', $annee?->id)->detach($classeId);
        $classe->majEffectif();

        return back()->with('success', "{$eleve->nom_complet} retiré de {$classe->nom}.");
    }

    // ── DÉPLACER UN ÉLÈVE VERS UNE AUTRE CLASSE ─────────────────────────
    public function deplacer(Request $request)
    {
        $request->validate([
            'eleve_id'          => 'required|integer|exists:users,id',
            'classe_source_id'  => 'required|integer|exists:classes,id',
            'classe_cible_id'   => 'required|integer|exists:classes,id',
        ]);

        $annee        = AnneeScolaire::courante();
        $eleve        = User::findOrFail($request->eleve_id);
        $classeSource = Classe::findOrFail($request->classe_source_id);
        $classeCible  = Classe::findOrFail($request->classe_cible_id);

        DB::transaction(function () use ($eleve, $classeSource, $classeCible, $annee) {
            // Retirer de la classe source
            $eleve->classes()->wherePivot('annee_scolaire_id', $annee?->id)->detach($classeSource->id);

            // Affecter à la classe cible
            $eleve->classes()->attach($classeCible->id, [
                'annee_scolaire_id' => $annee->id,
            ]);

            // Mettre à jour les effectifs
            $classeSource->majEffectif();
            $classeCible->majEffectif();
        });

        return back()->with('success', "{$eleve->nom_complet} déplacé de {$classeSource->nom} vers {$classeCible->nom}.");
    }

    // ── EXPORT CSV DES ÉLÈVES D'UNE CLASSE ──────────────────────────────
    public function exportCsv(int $classeId)
    {
        $annee  = AnneeScolaire::courante();
        $classe = Classe::findOrFail($classeId);

        $eleves = $classe->eleves()
            ->where('eleve_classe.annee_scolaire_id', $annee?->id)
            ->orderBy('nom')
            ->get();

        $filename = 'eleves_' . str_replace(' ', '_', $classe->nom) . '_' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($eleves) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Matricule', 'Nom', 'Prénoms', 'Email']);
            foreach ($eleves as $e) {
                fputcsv($handle, [$e->matricule, $e->nom, $e->prenoms, $e->email]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}