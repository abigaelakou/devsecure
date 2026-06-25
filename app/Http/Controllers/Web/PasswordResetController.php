<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Tenant\User;
use App\Mail\ReinitialisationMotDePasseMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    // ── FORMULAIRE DEMANDE DE RÉINITIALISATION ────────────────────────
    public function showForgotForm()
    {
        return view('auth.forgot-password');
    }

    // ── ENVOYER LE LIEN DE RÉINITIALISATION ───────────────────────────
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        // On retourne toujours un succès pour ne pas révéler les emails existants
        if (!$user) {
            return back()->with('success',
                'Si cet email existe, un lien de réinitialisation vous a été envoyé.'
            );
        }

        if (!$user->actif) {
            return back()->with('success',
                'Si cet email existe, un lien de réinitialisation vous a été envoyé.'
            );
        }

        // Supprimer l'ancien token s'il existe
        DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->delete();

        // Créer un nouveau token
        $token = Str::random(64);

        DB::table('password_reset_tokens')->insert([
            'email'      => $request->email,
            'token'      => Hash::make($token),
            'created_at' => now(),
        ]);

        // Envoyer l'email
        try {
            Mail::to($user->email)->send(
                new ReinitialisationMotDePasseMail($user, $token)
            );
        } catch (\Exception $e) {
            \Log::error('Erreur envoi email reset password : ' . $e->getMessage());
        }

        return back()->with('success',
            'Si cet email existe, un lien de réinitialisation vous a été envoyé.'
        );
    }

    // ── FORMULAIRE NOUVEAU MOT DE PASSE ───────────────────────────────
    public function showResetForm(Request $request, string $token)
    {
        $email = $request->query('email');

        if (!$email) {
            return redirect()->route('login')
                ->with('error', 'Lien invalide.');
        }

        return view('auth.reset-password', compact('token', 'email'));
    }

    // ── ENREGISTRER LE NOUVEAU MOT DE PASSE ───────────────────────────
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email'                 => 'required|email',
            'token'                 => 'required|string',
            'password'              => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required',
        ]);

        // Récupérer le token en base
        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$record) {
            return back()->withErrors([
                'email' => 'Aucune demande de réinitialisation trouvée pour cet email.',
            ]);
        }

        // Vérifier que le token n'a pas expiré (60 minutes)
        if (now()->diffInMinutes($record->created_at) > 60) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return back()->withErrors([
                'email' => 'Ce lien a expiré. Faites une nouvelle demande.',
            ]);
        }

        // Vérifier le token
        if (!Hash::check($request->token, $record->token)) {
            return back()->withErrors([
                'email' => 'Lien de réinitialisation invalide.',
            ]);
        }

        // Mettre à jour le mot de passe
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Utilisateur introuvable.']);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Supprimer le token utilisé
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        // Révoquer tous les tokens Sanctum (déconnexion de tous les appareils)
        $user->tokens()->delete();

        return redirect()->route('login')
            ->with('success', 'Mot de passe réinitialisé avec succès. Vous pouvez maintenant vous connecter.');
    }

    // ── CHANGER SON MOT DE PASSE (utilisateur connecté) ───────────────
    public function showChangeForm()
    {
        return view('auth.change-password');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'mot_de_passe_actuel' => 'required|string',
            'password'            => 'required|string|min:8|confirmed|different:mot_de_passe_actuel',
            'password_confirmation'=> 'required',
        ]);

        $user = $request->user();

        // Vérifier l'ancien mot de passe
        if (!Hash::check($request->mot_de_passe_actuel, $user->password)) {
            return back()->withErrors([
                'mot_de_passe_actuel' => 'Le mot de passe actuel est incorrect.',
            ]);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Révoquer les autres sessions
        $user->tokens()->where('id', '!=', $request->user()->currentAccessToken()->id)->delete();

        return back()->with('success', 'Mot de passe modifié avec succès.');
    }
}