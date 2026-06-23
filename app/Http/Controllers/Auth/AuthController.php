<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // ── LOGIN ─────────────────────────────────────────────
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Les identifiants sont incorrects.'],
            ]);
        }

        if (!$user->actif) {
            return response()->json([
                'message' => 'Votre compte est désactivé. Contactez l\'administrateur.',
            ], 403);
        }

        // Mettre à jour la dernière connexion
        $user->update(['derniere_connexion' => now()]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Connexion réussie.',
            'token'   => $token,
            'user'    => $this->formatUser($user),
        ]);
    }

    // ── LOGOUT ────────────────────────────────────────────
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Déconnexion réussie.']);
    }

    // ── ME ────────────────────────────────────────────────
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $this->formatUser($request->user()),
        ]);
    }

    // ── REGISTER (admin seulement) ────────────────────────
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'nom'       => 'required|string|max:100',
            'prenoms'   => 'required|string|max:100',
            'email'     => 'required|email|unique:users',
            'password'  => 'required|string|min:8|confirmed',
            'role'      => 'required|in:admin,enseignant,eleve',
            'matricule' => 'nullable|string|unique:users',
            'telephone' => 'nullable|string|max:20',
        ]);

        $user = User::create([
            'nom'       => $request->nom,
            'prenoms'   => $request->prenoms,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'role'      => $request->role,
            'matricule' => $request->matricule,
            'telephone' => $request->telephone,
            'actif'     => true,
        ]);

        return response()->json([
            'message' => 'Compte créé avec succès.',
            'user'    => $this->formatUser($user),
        ], 201);
    }

    // ── MOT DE PASSE OUBLIÉ ───────────────────────────────
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            // On retourne quand même 200 pour ne pas révéler les emails existants
            return response()->json([
                'message' => 'Si cet email existe, un lien de réinitialisation a été envoyé.',
            ]);
        }

        // TODO: envoyer l'email de réinitialisation
        // Password::sendResetLink($request->only('email'));

        return response()->json([
            'message' => 'Si cet email existe, un lien de réinitialisation a été envoyé.',
        ]);
    }

    // ── RESET PASSWORD ────────────────────────────────────
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // TODO: implémenter avec Password::reset()

        return response()->json(['message' => 'Mot de passe réinitialisé avec succès.']);
    }

    // ── HELPER ────────────────────────────────────────────
    private function formatUser(User $user): array
    {
        return [
            'id'          => $user->id,
            'nom'         => $user->nom,
            'prenoms'     => $user->prenoms,
            'nom_complet' => $user->nom_complet,
            'email'       => $user->email,
            'role'        => $user->role,
            'matricule'   => $user->matricule,
            'avatar'      => $user->avatar,
            'actif'       => $user->actif,
        ];
    }
}