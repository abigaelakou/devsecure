<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AuthWebController;
use App\Http\Controllers\Web\Eleve\DashboardController as EleveDashboard;
use App\Http\Controllers\Web\Eleve\PassageWebController;
use App\Http\Controllers\Web\Enseignant\DashboardController as EnseignantDashboard;
use App\Http\Controllers\Web\Enseignant\DevoirWebController;

// ── AUTH ──────────────────────────────────────────────────
Route::get('/',      [AuthWebController::class, 'showLogin'])->name('login.form');
Route::get('/login', [AuthWebController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthWebController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthWebController::class, 'logout'])->name('logout');

// ── ÉLÈVE ─────────────────────────────────────────────────
Route::middleware(['auth', 'role:eleve'])->prefix('eleve')->name('eleve.')->group(function () {
    Route::get('/dashboard',       [EleveDashboard::class, 'index'])->name('dashboard');
    Route::get('/devoirs',         [EleveDashboard::class, 'devoirs'])->name('devoirs');
    Route::get('/devoirs/{id}',    [EleveDashboard::class, 'show'])->name('devoir.show');
    Route::post('/devoirs/{id}/commencer', [PassageWebController::class, 'commencer'])->name('devoir.commencer');
    Route::get('/resultats',       [EleveDashboard::class, 'resultats'])->name('resultats');
    Route::get('/resultats/{id}',  [EleveDashboard::class, 'detailResultat'])->name('resultat.detail');
    Route::get('/profil',          [EleveDashboard::class, 'profil'])->name('profil');
});

// Routes passage (hors prefix eleve pour URL plus courte)
Route::middleware(['auth', 'role:eleve'])->group(function () {
    Route::get('/devoir/{tentativeId}/question/{numero?}', [PassageWebController::class, 'question'])
         ->name('eleve.passage.question')->defaults('numero', 1);
    Route::post('/devoir/{tentativeId}/repondre',  [PassageWebController::class, 'repondre'])->name('eleve.passage.repondre');
    Route::post('/devoir/{tentativeId}/soumettre', [PassageWebController::class, 'soumettre'])->name('eleve.passage.soumettre');
});

// ── ENSEIGNANT ────────────────────────────────────────────
Route::middleware(['auth', 'role:enseignant,admin'])->prefix('enseignant')->name('enseignant.')->group(function () {
    Route::get('/dashboard',            [EnseignantDashboard::class, 'index'])->name('dashboard');
    Route::get('/devoirs',              [DevoirWebController::class, 'index'])->name('devoirs.index');
    Route::get('/devoirs/create',       [DevoirWebController::class, 'create'])->name('devoirs.create');
    Route::post('/devoirs',             [DevoirWebController::class, 'store'])->name('devoirs.store');
    Route::get('/devoirs/{id}/edit',    [DevoirWebController::class, 'edit'])->name('devoirs.edit');
    Route::put('/devoirs/{id}',         [DevoirWebController::class, 'update'])->name('devoirs.update');
    Route::delete('/devoirs/{id}',      [DevoirWebController::class, 'destroy'])->name('devoirs.destroy');
    Route::post('/devoirs/{id}/publier',[DevoirWebController::class, 'publier'])->name('devoirs.publier');
    Route::get('/devoirs/{id}/resultats',[DevoirWebController::class, 'resultats'])->name('devoirs.resultats');
    Route::get('/classes',              [EnseignantDashboard::class, 'classes'])->name('classes');
    Route::get('/statistiques',         [EnseignantDashboard::class, 'statistiques'])->name('statistiques');
    Route::get('/corrections',          [EnseignantDashboard::class, 'corrections'])->name('corrections');
    Route::post('/corrections/{id}',    [EnseignantDashboard::class, 'corriger'])->name('corrections.corriger');
    Route::get('/antitriche',           [EnseignantDashboard::class, 'antitriche'])->name('antitriche');
    Route::get('/profil',               [EnseignantDashboard::class, 'profil'])->name('profil');
});

// ── ADMIN ─────────────────────────────────────────────────
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard',       [\App\Http\Controllers\Web\Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/utilisateurs',    [\App\Http\Controllers\Web\Admin\DashboardController::class, 'utilisateurs'])->name('utilisateurs');
    Route::get('/classes',         [\App\Http\Controllers\Web\Admin\DashboardController::class, 'classes'])->name('classes');
    Route::get('/matieres',        [\App\Http\Controllers\Web\Admin\DashboardController::class, 'matieres'])->name('matieres');
    Route::get('/rapports',        [\App\Http\Controllers\Web\Admin\DashboardController::class, 'rapports'])->name('rapports');
    Route::get('/antitriche',      [\App\Http\Controllers\Web\Admin\DashboardController::class, 'antitriche'])->name('antitriche');
    Route::get('/annees-scolaires',[\App\Http\Controllers\Web\Admin\DashboardController::class, 'anneesScolaires'])->name('annees-scolaires');
});

// Redirection après login selon le rôle
Route::get('/home', function () {
    return match(auth()->user()?->role) {
        'admin'      => redirect()->route('admin.dashboard'),
        'enseignant' => redirect()->route('enseignant.dashboard'),
        'eleve'      => redirect()->route('eleve.dashboard'),
        default      => redirect()->route('login'),
    };
})->middleware('auth')->name('home');