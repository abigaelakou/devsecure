<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant\User;
use App\Models\Tenant\Classe;
use App\Models\Tenant\AnneeScolaire;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use League\Csv\Reader;

class UtilisateurController extends Controller
{
    // ── LISTE ─────────────────────────────────────────────
    public function index(Request $request): JsonResponse
    {
        $users = User::query()
            ->when($request->role,    fn($q) => $q->where('role', $request->role))
            ->when($request->search,  fn($q) => $q->where(function ($q) use ($request) {
                $q->where('nom', 'like', "%{$request->search}%")
                  ->orWhere('prenoms', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
                  ->orWhere('matricule', 'like', "%{$request->search}%");
            }))
            ->when($request->actif !== null, fn($q) => $q->where('actif', $request->boolean('actif')))
            ->orderBy('nom')
            ->paginate(20);

        return response()->json($users);
    }

    // ── DÉTAIL ────────────────────────────────────────────
    public function show(int $id): JsonResponse
    {
        $user = User::with(['classes.anneeScolaire'])->findOrFail($id);
        return response()->json(['user' => $user]);
    }

    // ── CRÉER ─────────────────────────────────────────────
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'nom'       => 'required|string|max:100',
            'prenoms'   => 'required|string|max:100',
            'email'     => 'required|email|unique:users',
            'role'      => 'required|in:admin,enseignant,eleve',
            'matricule' => 'nullable|string|unique:users',
            'telephone' => 'nullable|string|max:20',
            'classe_id' => 'required_if:role,eleve|nullable|integer|exists:classes,id',
        ]);

        $password = $request->password ?? substr(str_shuffle('abcdefghijklmnopqrstuvwxyz0123456789'), 0, 10);

        $user = User::create([
            'nom'       => $request->nom,
            'prenoms'   => $request->prenoms,
            'email'     => $request->email,
            'password'  => Hash::make($password),
            'role'      => $request->role,
            'matricule' => $request->matricule,
            'telephone' => $request->telephone,
            'actif'     => true,
        ]);

        // Affecter l'élève à sa classe
        if ($request->role === 'eleve' && $request->classe_id) {
            $annee = AnneeScolaire::courante();
            if ($annee) {
                $user->classes()->attach($request->classe_id, [
                    'annee_scolaire_id' => $annee->id,
                ]);
            }
        }

        return response()->json([
            'message'          => 'Utilisateur créé.',
            'user'             => $user,
            'mot_de_passe_tmp' => $password, // À envoyer par email en prod
        ], 201);
    }

    // ── MODIFIER ──────────────────────────────────────────
    public function update(Request $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $request->validate([
            'nom'       => 'sometimes|string|max:100',
            'prenoms'   => 'sometimes|string|max:100',
            'email'     => 'sometimes|email|unique:users,email,' . $id,
            'telephone' => 'nullable|string|max:20',
            'matricule' => 'nullable|string|unique:users,matricule,' . $id,
        ]);

        $user->update($request->only(['nom', 'prenoms', 'email', 'telephone', 'matricule']));

        return response()->json(['message' => 'Utilisateur mis à jour.', 'user' => $user]);
    }

    // ── SUPPRIMER ─────────────────────────────────────────
    public function destroy(int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $user->delete(); // SoftDelete
        return response()->json(['message' => 'Utilisateur supprimé.']);
    }

    // ── ACTIVER / DÉSACTIVER ──────────────────────────────
    public function activer(int $id): JsonResponse
    {
        User::findOrFail($id)->update(['actif' => true]);
        return response()->json(['message' => 'Compte activé.']);
    }

    public function desactiver(int $id): JsonResponse
    {
        User::findOrFail($id)->update(['actif' => false]);
        return response()->json(['message' => 'Compte désactivé.']);
    }

    // ── IMPORT CSV ────────────────────────────────────────
    public function importCsv(Request $request): JsonResponse
    {
        $request->validate([
            'fichier'   => 'required|file|mimes:csv,txt|max:2048',
            'classe_id' => 'required|integer|exists:classes,id',
            'role'      => 'required|in:eleve,enseignant',
        ]);

        $annee   = AnneeScolaire::courante();
        $crees   = 0;
        $erreurs = [];

        // Lire le CSV ligne par ligne
        $lignes = array_map('str_getcsv', file($request->file('fichier')->getRealPath()));
        $entetes = array_shift($lignes); // Première ligne = en-têtes

        foreach ($lignes as $index => $ligne) {
            $data = array_combine($entetes, $ligne);
            $ligneNum = $index + 2;

            try {
                if (User::where('email', $data['email'])->exists()) {
                    $erreurs[] = "Ligne {$ligneNum} : email {$data['email']} déjà utilisé.";
                    continue;
                }

                $password = substr(str_shuffle('abcdefghijklmnopqrstuvwxyz0123456789'), 0, 10);

                $user = User::create([
                    'nom'       => $data['nom'],
                    'prenoms'   => $data['prenoms'],
                    'email'     => $data['email'],
                    'password'  => Hash::make($password),
                    'matricule' => $data['matricule'] ?? null,
                    'role'      => $request->role,
                    'actif'     => true,
                ]);

                if ($request->role === 'eleve' && $annee) {
                    $user->classes()->attach($request->classe_id, [
                        'annee_scolaire_id' => $annee->id,
                    ]);
                }

                $crees++;
            } catch (\Exception $e) {
                $erreurs[] = "Ligne {$ligneNum} : {$e->getMessage()}";
            }
        }

        return response()->json([
            'message' => "{$crees} utilisateur(s) importé(s).",
            'erreurs' => $erreurs,
        ]);
    }
}