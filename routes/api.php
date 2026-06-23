<?php

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Eleve\DevoirController as EleveDevoirController;
use App\Http\Controllers\Eleve\PassageController;
use App\Http\Controllers\Eleve\AntitricheController;
use App\Http\Controllers\Enseignant\DevoirController as EnseignantDevoirController;
use App\Http\Controllers\Enseignant\QuestionController;
use App\Http\Controllers\Enseignant\ResultatController;
use App\Http\Controllers\Enseignant\StatistiqueController;
use App\Http\Controllers\Admin\UtilisateurController;
use App\Http\Controllers\Admin\ClasseController;
use App\Http\Controllers\Admin\MatiereController;
use App\Http\Controllers\Admin\AnneeScolaireController;
use App\Http\Controllers\Admin\RapportController;

// ── TOUTES LES ROUTES PASSENT PAR LE MIDDLEWARE TENANCY ───
// Route::middleware([
//     InitializeTenancyByDomain::class,
//     // PreventAccessFromCentralDomains::class,
// ])->group(function () {
// });
    // ── AUTH (public) ──────────────────────────────────────
    Route::prefix('auth')->group(function () {
        Route::post('login',    [AuthController::class, 'login']);
        Route::post('register', [AuthController::class, 'register']);
        Route::post('forgot',   [AuthController::class, 'forgotPassword']);
        Route::post('reset',    [AuthController::class, 'resetPassword']);
    });

    // ── ROUTES AUTHENTIFIÉES ───────────────────────────────
    Route::middleware('auth:sanctum')->group(function () {

        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('auth/me',      [AuthController::class, 'me']);

        // ── ÉLÈVE ──────────────────────────────────────────
        Route::middleware('role:eleve')->prefix('eleve')->group(function () {
            Route::get('devoirs',                 [EleveDevoirController::class, 'index']);
            Route::get('devoirs/{id}',            [EleveDevoirController::class, 'show']);
            Route::post('devoirs/{id}/commencer', [EleveDevoirController::class, 'commencer']);
            Route::post('antitriche/evenement',   [AntitricheController::class, 'enregistrer']);
            Route::get('resultats',               [EleveDevoirController::class, 'mesResultats']);
            Route::get('resultats/{tentativeId}', [EleveDevoirController::class, 'detailResultat']);

            Route::prefix('passage/{tentativeId}')
                ->middleware('devoir_en_cours')
                ->group(function () {
                    Route::get('question/{numero}', [PassageController::class, 'getQuestion']);
                    Route::post('repondre',         [PassageController::class, 'repondre']);
                    Route::post('soumettre',        [PassageController::class, 'soumettre']);
                    Route::get('progression',       [PassageController::class, 'progression']);
                });
        });

        // ── ENSEIGNANT ────────────────────────────────────
        Route::middleware('role:enseignant,admin')->prefix('enseignant')->group(function () {
            Route::apiResource('devoirs', EnseignantDevoirController::class);
            Route::post('devoirs/{id}/publier',   [EnseignantDevoirController::class, 'publier']);
            Route::post('devoirs/{id}/archiver',  [EnseignantDevoirController::class, 'archiver']);
            Route::post('devoirs/{id}/dupliquer', [EnseignantDevoirController::class, 'dupliquer']);

            Route::apiResource('devoirs/{devoirId}/questions', QuestionController::class);
            Route::post('devoirs/{devoirId}/questions/ordre', [QuestionController::class, 'reordonner']);

            Route::get('devoirs/{id}/resultats',           [ResultatController::class, 'index']);
            Route::get('devoirs/{id}/resultats/{eleveId}', [ResultatController::class, 'show']);
            Route::post('reponses/{id}/corriger',          [ResultatController::class, 'corrigerManuel']);

            Route::get('devoirs/{id}/statistiques',  [StatistiqueController::class, 'devoir']);
            Route::get('classes/{id}/statistiques',  [StatistiqueController::class, 'classe']);
        });

        // ── ADMIN ─────────────────────────────────────────
        Route::middleware('role:admin')->prefix('admin')->group(function () {
            Route::apiResource('utilisateurs', UtilisateurController::class);
            Route::post('utilisateurs/{id}/activer',    [UtilisateurController::class, 'activer']);
            Route::post('utilisateurs/{id}/desactiver', [UtilisateurController::class, 'desactiver']);
            Route::post('utilisateurs/import-csv',      [UtilisateurController::class, 'importCsv']);

            Route::apiResource('classes',          ClasseController::class);
            Route::apiResource('matieres',         MatiereController::class);
            Route::apiResource('annees-scolaires', AnneeScolaireController::class);

            Route::get('rapports/global',     [RapportController::class, 'global']);
            Route::get('rapports/antitriche', [RapportController::class, 'antitriche']);
            Route::get('rapports/export',     [RapportController::class, 'export']);
        });
    });
