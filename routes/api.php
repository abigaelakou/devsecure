<?php
// routes/api.php — API REST pour Flutter mobile
// Toutes les routes sont préfixées /api et répondent en JSON
// Auth: Laravel Sanctum (tokens)

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Eleve\DevoirController as EleveDevoirController;
use App\Http\Controllers\Eleve\PassageController;
use App\Http\Controllers\Eleve\AntitricheController;
use App\Http\Controllers\Enseignant\DevoirController as EnseignantDevoirController;
use App\Http\Controllers\Enseignant\QuestionController;
use App\Http\Controllers\Enseignant\ResultatController;
use App\Http\Controllers\Enseignant\StatistiqueController;
use App\Http\Controllers\Admin\EtablissementController;
use App\Http\Controllers\Admin\UtilisateurController;

// ── AUTH (public) ──────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('login',     [AuthController::class, 'login']);
    Route::post('register',  [AuthController::class, 'register']);   // Admin seulement
    Route::post('forgot',    [AuthController::class, 'forgotPassword']);
    Route::post('reset',     [AuthController::class, 'resetPassword']);
});

// ── ROUTES AUTHENTIFIÉES ───────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/me',      [AuthController::class, 'me']);

    // ── ÉLÈVE ──────────────────────────────────────────────
    Route::middleware('role:eleve')->prefix('eleve')->group(function () {

        // Devoirs disponibles
        Route::get('devoirs',            [EleveDevoirController::class, 'index']);
        Route::get('devoirs/{id}',       [EleveDevoirController::class, 'show']);
        Route::post('devoirs/{id}/commencer', [EleveDevoirController::class, 'commencer']);

        // Passage en cours
        Route::prefix('passage/{tentativeId}')->middleware('devoir_en_cours')->group(function () {
            Route::get('question/{numero}',   [PassageController::class, 'getQuestion']);
            Route::post('repondre',           [PassageController::class, 'repondre']);
            Route::post('soumettre',          [PassageController::class, 'soumettre']);
            Route::get('progression',         [PassageController::class, 'progression']);
        });

        // Antitriche — enregistre les événements
        Route::post('antitriche/evenement',  [AntitricheController::class, 'enregistrer']);

        // Résultats
        Route::get('resultats',             [EleveDevoirController::class, 'mesResultats']);
        Route::get('resultats/{tentativeId}', [EleveDevoirController::class, 'detailResultat']);
    });

    // ── ENSEIGNANT ────────────────────────────────────────
    Route::middleware('role:enseignant,admin')->prefix('enseignant')->group(function () {

        // Devoirs
        Route::apiResource('devoirs', EnseignantDevoirController::class);
        Route::post('devoirs/{id}/publier',   [EnseignantDevoirController::class, 'publier']);
        Route::post('devoirs/{id}/archiver',  [EnseignantDevoirController::class, 'archiver']);
        Route::post('devoirs/{id}/dupliquer', [EnseignantDevoirController::class, 'dupliquer']);

        // Questions
        Route::apiResource('devoirs/{devoirId}/questions', QuestionController::class);
        Route::post('devoirs/{devoirId}/questions/ordre',  [QuestionController::class, 'reordonner']);

        // Résultats & corrections
        Route::get('devoirs/{id}/resultats',             [ResultatController::class, 'index']);
        Route::get('devoirs/{id}/resultats/{eleveId}',   [ResultatController::class, 'show']);
        Route::post('reponses/{id}/corriger',            [ResultatController::class, 'corrigerManuel']);

        // Statistiques
        Route::get('devoirs/{id}/statistiques',          [StatistiqueController::class, 'devoir']);
        Route::get('classes/{id}/statistiques',          [StatistiqueController::class, 'classe']);
    });

    // ── ADMIN ─────────────────────────────────────────────
    Route::middleware('role:admin')->prefix('admin')->group(function () {

        // Utilisateurs
        Route::apiResource('utilisateurs', UtilisateurController::class);
        Route::post('utilisateurs/{id}/activer',    [UtilisateurController::class, 'activer']);
        Route::post('utilisateurs/{id}/desactiver', [UtilisateurController::class, 'desactiver']);
        Route::post('utilisateurs/import-csv',      [UtilisateurController::class, 'importCsv']);

        // Classes & matières
        Route::apiResource('classes', \App\Http\Controllers\Admin\ClasseController::class);
        Route::apiResource('matieres', \App\Http\Controllers\Admin\MatiereController::class);
        Route::apiResource('annees-scolaires', \App\Http\Controllers\Admin\AnneeScolaireController::class);

        // Rapports globaux
        Route::get('rapports/global',      [\App\Http\Controllers\Admin\RapportController::class, 'global']);
        Route::get('rapports/antitriche',  [\App\Http\Controllers\Admin\RapportController::class, 'antitriche']);
        Route::get('rapports/export',      [\App\Http\Controllers\Admin\RapportController::class, 'export']);
    });
});

// ── WEBSOCKET BROADCAST ────────────────────────────────────
// Dans routes/channels.php :
// Broadcast::channel('devoir.{tentativeId}', function ($user, $tentativeId) {
//     return $user->tentatives()->find($tentativeId) !== null;
// });
