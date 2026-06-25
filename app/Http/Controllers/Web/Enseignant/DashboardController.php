<?php

namespace App\Http\Controllers\Web\Enseignant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Devoir;
use App\Models\Tenant\EvenementAntitriche;
use App\Models\Tenant\ReponseEleve;
use App\Models\Tenant\Resultat;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $enseignant = $request->user();

        $devoirs = Devoir::where('enseignant_id', $enseignant->id)
            ->with(['matiere', 'classe', 'resultats'])
            ->withCount(['questions', 'resultats'])
            ->latest()
            ->take(5)
            ->get();

        $evenementsRecents = EvenementAntitriche::whereHas('tentative.devoir', fn($q) =>
                $q->where('enseignant_id', $enseignant->id))
            ->with(['eleve:id,nom,prenoms'])
            ->suspicieux()
            ->latest('survenu_le')
            ->take(8)
            ->get();

        $nbCorrections = ReponseEleve::whereNull('est_correcte')
            ->whereHas('question', fn($q) => $q->whereIn('type', ['reponse_courte', 'redactionnel']))
            ->whereHas('tentative.devoir', fn($q) => $q->where('enseignant_id', $enseignant->id))
            ->count();

        $allResultats = Resultat::whereHas('devoir', fn($q) =>
            $q->where('enseignant_id', $enseignant->id))->get();

        return view('enseignant.dashboard', [
            'devoirs'           => $devoirs,
            'evenementsRecents' => $evenementsRecents,
            'nbCorrections'     => $nbCorrections,
            'nbDevoirs'         => Devoir::where('enseignant_id', $enseignant->id)->count(),
            'nbEleves'          => $allResultats->count(),
            'moyenneGenerale'   => $allResultats->isNotEmpty() ? round($allResultats->avg('note_finale'), 1) : null,
            'nbFraudes'         => $allResultats->where('fraude_detectee', true)->count(),
        ]);
    }

    


    public function classes(Request $request)
    {
        $enseignant = $request->user();

        // Récupérer les classes via la table pivot directement
        $classeIds = \App\Models\Tenant\EnseignantMatiereClasse::where('enseignant_id', $enseignant->id)
            ->pluck('classe_id')
            ->unique();

        $classes = \App\Models\Tenant\Classe::whereIn('id', $classeIds)
            ->with(['eleves', 'anneeScolaire'])
            ->withCount('eleves')
            ->orderBy('niveau')
            ->get();

        return view('enseignant.classes', compact('classes'));
    }


    public function statistiques(Request $request)
    {
        $devoirs = Devoir::where('enseignant_id', $request->user()->id)
            ->whereHas('resultats')
            ->with(['matiere', 'classe', 'resultats'])
            ->get();

        return view('enseignant.statistiques', compact('devoirs'));
    }

    public function corrections(Request $request)
    {
        $corrections = ReponseEleve::whereNull('est_correcte')
            ->whereHas('question', fn($q) => $q->whereIn('type', ['reponse_courte', 'redactionnel']))
            ->whereHas('tentative.devoir', fn($q) => $q->where('enseignant_id', $request->user()->id))
            ->with(['question', 'tentative.eleve', 'tentative.devoir'])
            ->paginate(20);

        return view('enseignant.corrections', compact('corrections'));
    }

    public function corriger(Request $request, int $id)
    {
        $request->validate([
            'points_obtenus'         => 'required|numeric|min:0',
            'est_correcte'           => 'required|boolean',
            'commentaire_enseignant' => 'nullable|string|max:500',
        ]);

        ReponseEleve::findOrFail($id)->update([
            'points_obtenus'         => $request->points_obtenus,
            'est_correcte'           => $request->est_correcte,
            'commentaire_enseignant' => $request->commentaire_enseignant,
        ]);

        return back()->with('success', 'Correction enregistrée.');
    }

    public function antitriche(Request $request)
    {
        $evenements = EvenementAntitriche::whereHas('tentative.devoir', fn($q) =>
                $q->where('enseignant_id', $request->user()->id))
            ->with(['eleve:id,nom,prenoms', 'tentative.devoir:id,titre'])
            ->suspicieux()
            ->latest('survenu_le')
            ->paginate(30);

        return view('enseignant.antitriche', compact('evenements'));
    }

    public function profil(Request $request)
    {
        return view('enseignant.profil', ['user' => $request->user()]);
    }
}