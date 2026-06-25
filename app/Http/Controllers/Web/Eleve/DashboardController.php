<?php

namespace App\Http\Controllers\Web\Eleve;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Devoir;
use App\Models\Tenant\Resultat;
use App\Models\Tenant\TentativeDevoir;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $eleve    = $request->user();
        $classeId = $eleve->classes()->latest('pivot_created_at')->first()?->id;

        $devoirs = Devoir::actifs()
            ->where('classe_id', $classeId)
            ->with(['matiere', 'enseignant:id,nom,prenoms'])
            ->withCount('questions')
            ->latest()
            ->take(4)
            ->get();

        $resultats = Resultat::where('eleve_id', $eleve->id)
            ->with(['devoir.matiere'])
            ->latest()
            ->take(5)
            ->get();

        return view('eleve.dashboard', [
            'devoirs'            => $devoirs,
            'resultats'          => $resultats,
            'devoirsDisponibles' => $devoirs->count(),
            'devoirsTermines'    => TentativeDevoir::where('eleve_id', $eleve->id)->where('statut', 'soumis')->count(),
            'devoirsEnCours'     => TentativeDevoir::where('eleve_id', $eleve->id)->where('statut', 'en_cours')->count(),
            'moyenneGenerale'    => $resultats->isNotEmpty() ? round($resultats->avg('note_finale'), 1) : null,
        ]);
    }

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
        $devoir = Devoir::with(['matiere', 'enseignant', 'classe'])
            ->withCount('questions')
            ->findOrFail($id);

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
            ->with([
                'devoir',
                'reponsesEleves.question.reponsesPossibles',
                'reponsesEleves.reponsePossible',
                'resultat',
            ])
            ->firstOrFail();

        return view('eleve.resultats.detail', compact('tentative'));
    }

    public function profil(Request $request)
    {
        return view('eleve.profil', ['user' => $request->user()]);
    }
}