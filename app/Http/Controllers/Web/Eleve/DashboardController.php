<?php

namespace App\Http\Controllers\Web\Eleve;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Devoir;
use App\Models\Tenant\Resultat;
use App\Models\Tenant\TentativeDevoir;
use App\Models\Tenant\EvenementAntitriche;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $eleve    = $request->user();
        $classeId = $eleve->classes()->latest('pivot_created_at')->first()?->id;

        // Devoirs disponibles
        $devoirs = Devoir::actifs()
            ->where('classe_id', $classeId)
            ->with(['matiere', 'enseignant:id,nom,prenoms'])
            ->withCount('questions')
            ->latest()
            ->take(4)
            ->get();

        // Tous les résultats de l'élève
        $tousResultats = Resultat::where('eleve_id', $eleve->id)
            ->with(['devoir.matiere'])
            ->latest()
            ->get();

        // Derniers résultats pour l'affichage
        $resultats = $tousResultats->take(5);

        // ── STATISTIQUES GLOBALES ─────────────────────────────────────
        $moyenneGenerale  = $tousResultats->isNotEmpty()
            ? round($tousResultats->avg('note_finale'), 1)
            : null;

        $meilleurResultat = $tousResultats->sortByDesc('note_finale')->first();
        $dernierResultat  = $tousResultats->first();

        // ── PROGRESSION PAR MATIÈRE ───────────────────────────────────
        $progressionMatieres = $tousResultats
            ->groupBy('devoir.matiere.nom')
            ->map(fn($groupe, $matiere) => [
                'matiere'   => $matiere ?? 'Inconnue',
                'couleur'   => $groupe->first()->devoir?->matiere?->couleur ?? '#4F46E5',
                'moyenne'   => round($groupe->avg(fn($r) => ($r->note_finale / $r->note_sur) * 20), 1),
                'nb_devoirs'=> $groupe->count(),
                'derniere'  => $groupe->first()?->note_finale,
                'note_sur'  => $groupe->first()?->note_sur,
            ])
            ->values();

        // ── ÉVOLUTION DE LA MOYENNE (derniers 10 devoirs) ─────────────
        $evolution = $tousResultats
            ->take(10)
            ->reverse()
            ->map(fn($r) => [
                'label'      => \Str::limit($r->devoir?->titre ?? '', 20),
                'note'       => round(($r->note_finale / $r->note_sur) * 20, 1),
                'note_brute' => $r->note_finale,
                'note_sur'   => $r->note_sur,
                'date'       => $r->created_at->format('d/m'),
                'matiere'    => $r->devoir?->matiere?->nom,
                'couleur'    => $r->devoir?->matiere?->couleur ?? '#4F46E5',
            ])
            ->values();

        // ── TAUX DE RÉUSSITE PAR MOIS ─────────────────────────────────
        $parMois = $tousResultats
            ->groupBy(fn($r) => $r->created_at->format('M Y'))
            ->map(fn($groupe, $mois) => [
                'mois'          => $mois,
                'moyenne'       => round($groupe->avg(fn($r) => ($r->note_finale / $r->note_sur) * 20), 1),
                'taux_reussite' => round($groupe->where('pourcentage', '>=', 50)->count() / $groupe->count() * 100),
                'nb'            => $groupe->count(),
            ])
            ->values()
            ->take(6);

        // ── ACTIVITÉ ANTITRICHE ───────────────────────────────────────
        $nbEvenements = EvenementAntitriche::where('eleve_id', $eleve->id)
            ->whereIn('type', ['changement_onglet', 'fenetre_reduite', 'quitter_navigateur'])
            ->count();

        // ── DEVOIR EN COURS ───────────────────────────────────────────
        $devoirEnCours = TentativeDevoir::where('eleve_id', $eleve->id)
            ->where('statut', 'en_cours')
            ->with('devoir.matiere')
            ->first();

        return view('eleve.dashboard', [
            'devoirs'             => $devoirs,
            'resultats'           => $resultats,
            'tousResultats'       => $tousResultats,
            'devoirsDisponibles'  => $devoirs->count(),
            'devoirsTermines'     => TentativeDevoir::where('eleve_id', $eleve->id)->where('statut', 'soumis')->count(),
            'devoirsEnCours'      => TentativeDevoir::where('eleve_id', $eleve->id)->where('statut', 'en_cours')->count(),
            'moyenneGenerale'     => $moyenneGenerale,
            'meilleurResultat'    => $meilleurResultat,
            'dernierResultat'     => $dernierResultat,
            'progressionMatieres' => $progressionMatieres,
            'evolution'           => $evolution,
            'parMois'             => $parMois,
            'nbEvenements'        => $nbEvenements,
            'devoirEnCours'       => $devoirEnCours,
        ]);
    }

    // Les autres méthodes restent identiques...
    public function devoirs(Request $request)
    {
        $eleve    = $request->user();
        $classeId = $eleve->classes()->latest('pivot_created_at')->first()?->id;

        $devoirs = Devoir::actifs()
            ->where('classe_id', $classeId)
            ->with(['matiere', 'enseignant:id,nom,prenoms'])
            ->withCount('questions')
            ->get();

        return view('eleve.devoirs', compact('devoirs'));
    }

    public function show(int $id)
    {
        $devoir = Devoir::with(['matiere', 'enseignant', 'classe'])->withCount('questions')->findOrFail($id);
        return view('eleve.devoir.show', compact('devoir'));
    }

    public function resultats(Request $request)
    {
        $resultats = Resultat::where('eleve_id', $request->user()->id)
            ->with(['devoir.matiere'])
            ->latest()
            ->paginate(15);
        return view('eleve.resultats.index', compact('resultats'));
    }

    public function detailResultat(Request $request, int $id)
    {
        $tentative = TentativeDevoir::where('id', $id)
            ->where('eleve_id', $request->user()->id)
            ->with(['devoir', 'reponsesEleves.question.reponsesPossibles', 'reponsesEleves.reponsePossible', 'resultat'])
            ->firstOrFail();
        return view('eleve.resultats.detail', compact('tentative'));
    }

    public function profil(Request $request)
    {
        return view('eleve.profil', ['user' => $request->user()]);
    }
}