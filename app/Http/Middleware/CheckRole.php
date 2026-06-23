<?php

// ============================================================
// app/Http/Middleware/CheckRole.php
// ============================================================
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): mixed
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Non authentifié.'], 401);
        }

        if (!in_array($user->role, $roles)) {
            return response()->json([
                'message' => 'Accès refusé. Rôle requis : ' . implode(' ou ', $roles) . '.',
            ], 403);
        }

        return $next($request);
    }
}

// ============================================================
// app/Http/Middleware/DevoirEnCours.php
// ============================================================
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Tenant\TentativeDevoir;

class DevoirEnCours
{
    public function handle(Request $request, Closure $next): mixed
    {
        $tentativeId = $request->route('tentativeId');
        $tentative   = TentativeDevoir::find($tentativeId);

        if (!$tentative) {
            return response()->json(['message' => 'Tentative introuvable.'], 404);
        }

        // Vérifier que la tentative appartient à l'élève connecté
        if ($tentative->eleve_id !== $request->user()->id) {
            return response()->json(['message' => 'Accès refusé.'], 403);
        }

        // Vérifier que la tentative est bien en cours
        if (!$tentative->estEnCours()) {
            return response()->json([
                'message' => 'Ce devoir n\'est plus en cours.',
                'statut'  => $tentative->statut,
            ], 403);
        }

        // Vérifier si le temps global est expiré
        if ($tentative->temps_restant <= 0 && $tentative->devoir->duree_totale_minutes) {
            app(\App\Services\NotationService::class)
                ->soumettreAutomatiquement($tentative, 'timeout');

            return response()->json([
                'message' => 'Le temps est écoulé. Votre devoir a été soumis automatiquement.',
                'statut'  => 'expire',
            ], 403);
        }

        // Injecter la tentative dans la requête pour éviter un double appel DB
        $request->merge(['_tentative' => $tentative]);

        return $next($request);
    }
}

// ============================================================
// Enregistrement dans bootstrap/app.php (Laravel 11)
// ============================================================
// Dans bootstrap/app.php, ajouter dans ->withMiddleware() :
//
// ->withMiddleware(function (Middleware $middleware) {
//     $middleware->alias([
//         'role'          => \App\Http\Middleware\CheckRole::class,
//         'devoir_en_cours' => \App\Http\Middleware\DevoirEnCours::class,
//     ]);
// })