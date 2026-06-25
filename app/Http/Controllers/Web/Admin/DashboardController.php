<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant\User;
use App\Models\Tenant\Devoir;
use App\Models\Tenant\Classe;
use App\Models\Tenant\Matiere;
use App\Models\Tenant\AnneeScolaire;
use App\Models\Tenant\Resultat;
use App\Models\Tenant\EvenementAntitriche;
use App\Models\Tenant\TentativeDevoir;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DashboardController extends Controller
{
    // ── TABLEAU DE BORD ───────────────────────────────────
    public function index()
    {
        return view('admin.dashboard', [
            'nbEleves'      => User::eleves()->count(),
            'nbEnseignants' => User::enseignants()->count(),
            'nbDevoirs'     => Devoir::count(),
            'nbFraudes'     => Resultat::where('fraude_detectee', true)->count(),
            'devoirsActifs' => Devoir::where('statut', 'actif')->count(),
            'tentativesAujourdhui' => TentativeDevoir::whereDate('created_at', today())->count(),
        ]);
    }

    // ── UTILISATEURS ──────────────────────────────────────
    public function utilisateurs(Request $request)
    {
        $users = User::query()
            ->when($request->role, fn($q) => $q->where('role', $request->role))
            ->when($request->search, fn($q) => $q->where(function ($q) use ($request) {
                $q->where('nom', 'like', "%{$request->search}%")
                  ->orWhere('prenoms', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
                  ->orWhere('matricule', 'like', "%{$request->search}%");
            }))
            ->orderBy('nom')
            ->paginate(20);

        return view('admin.utilisateurs', compact('users'));
    }

    public function storeUtilisateur(Request $request)
    {
        $request->validate([
            'nom'       => 'required|string|max:100',
            'prenoms'   => 'required|string|max:100',
            'email'     => 'required|email|unique:users',
            'role'      => 'required|in:admin,enseignant,eleve',
            'matricule' => 'nullable|string|unique:users',
        ]);

        User::create([
            ...$request->only(['nom', 'prenoms', 'email', 'role', 'matricule', 'telephone']),
            'password' => Hash::make('password'),
            'actif'    => true,
        ]);

        return back()->with('success', 'Utilisateur créé (mot de passe : password).');
    }

    public function toggleActif(int $id)
    {
        $user = User::findOrFail($id);
        $user->update(['actif' => !$user->actif]);
        return back()->with('success', 'Statut mis à jour.');
    }

    // ── CLASSES ───────────────────────────────────────────
    public function classes(Request $request)
    {
        $annee   = AnneeScolaire::courante();
        $classes = Classe::where('annee_scolaire_id', $annee?->id)
            ->withCount('eleves')
            ->orderBy('niveau')
            ->orderBy('nom')
            ->get();

        return view('admin.classes', compact('classes', 'annee'));
    }

    public function storeClasse(Request $request)
    {
        $request->validate([
            'nom'    => 'required|string|max:50',
            'niveau' => 'required|string|max:20',
        ]);

        $annee = AnneeScolaire::courante();
        Classe::create([...$request->only(['nom', 'niveau']), 'annee_scolaire_id' => $annee->id]);

        return back()->with('success', 'Classe créée.');
    }

    // ── MATIÈRES ──────────────────────────────────────────
    public function matieres()
    {
        $matieres = Matiere::withCount('devoirs')->orderBy('nom')->get();
        return view('admin.matieres', compact('matieres'));
    }

    public function storeMatiere(Request $request)
    {
        $request->validate([
            'nom'     => 'required|string|max:100',
            'code'    => 'required|string|max:10|unique:matieres',
            'couleur' => 'nullable|string',
        ]);

        Matiere::create($request->only(['nom', 'code', 'couleur', 'icone']));

        return back()->with('success', 'Matière créée.');
    }

    // ── RAPPORTS ──────────────────────────────────────────
    public function rapports()
    {
        $resultats = Resultat::all();

        return view('admin.rapports', [
            'nbEleves'      => User::eleves()->count(),
            'nbEnseignants' => User::enseignants()->count(),
            'nbDevoirs'     => Devoir::count(),
            'nbTentatives'  => TentativeDevoir::count(),
            'moyenne'       => round($resultats->avg('note_finale'), 2),
            'tauxReussite'  => $resultats->count() > 0
                ? round($resultats->where('pourcentage', '>=', 50)->count() / $resultats->count() * 100, 1)
                : 0,
            'fraudes'       => $resultats->where('fraude_detectee', true)->count(),
        ]);
    }

    // ── ANTITRICHE ────────────────────────────────────────
    public function antitriche()
    {
        $evenements = EvenementAntitriche::with(['eleve:id,nom,prenoms', 'tentative.devoir:id,titre'])
            ->suspicieux()
            ->latest('survenu_le')
            ->paginate(30);

        $stats = [
            'total'    => EvenementAntitriche::count(),
            'fraudes'  => Resultat::where('fraude_detectee', true)->count(),
            'par_type' => EvenementAntitriche::selectRaw('type, COUNT(*) as nb')->groupBy('type')->pluck('nb', 'type'),
        ];

        return view('admin.antitriche', compact('evenements', 'stats'));
    }

    // ── ANNÉES SCOLAIRES ──────────────────────────────────
    public function anneesScolaires()
    {
        $annees = AnneeScolaire::orderByDesc('date_debut')->get();
        return view('admin.annees-scolaires', compact('annees'));
    }

    public function storeAnnee(Request $request)
    {
        $request->validate([
            'libelle'    => 'required|string|max:20',
            'date_debut' => 'required|date',
            'date_fin'   => 'required|date|after:date_debut',
        ]);

        $annee = AnneeScolaire::create($request->only(['libelle', 'date_debut', 'date_fin']));

        if ($request->boolean('active')) {
            $annee->activer();
        }

        return back()->with('success', 'Année scolaire créée.');
    }

    public function activerAnnee(int $id)
    {
        AnneeScolaire::findOrFail($id)->activer();
        return back()->with('success', 'Année scolaire activée.');
    }
}