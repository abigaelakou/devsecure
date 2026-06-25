<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AuthWebController;
use App\Http\Controllers\Web\Eleve\DashboardController as EleveDashboard;
use App\Http\Controllers\Web\Eleve\PassageWebController;
use App\Http\Controllers\Web\Enseignant\DashboardController as EnseignantDashboard;
use App\Http\Controllers\Web\Enseignant\DevoirWebController;
use App\Http\Controllers\SuperAdmin\AuthController as SuperAdminAuth;
use App\Http\Controllers\SuperAdmin\TenantController;
use App\Http\Controllers\Web\PasswordResetController;
use App\Http\Controllers\Web\BulletinController;

 

// Super Admin — accessible sur /superadmin
Route::prefix('superadmin')->name('superadmin.')->group(function () {
 // Auth (public)
    Route::get('login',  [SuperAdminAuth::class, 'showLogin'])->name('login');
    Route::post('login', [SuperAdminAuth::class, 'login'])->name('login.post');
    Route::post('logout',[SuperAdminAuth::class, 'logout'])->name('logout');
 
    // Routes protégées
    Route::middleware('superadmin.auth')->group(function () {
        Route::get('/',                                    [TenantController::class, 'dashboard'])->name('dashboard');
        Route::get('/export-csv',                          [TenantController::class, 'exportCsv'])->name('export-csv');
 
        Route::post('/tenants',                            [TenantController::class, 'store'])->name('tenants.store');
        Route::get('/tenants/{id}',                        [TenantController::class, 'show'])->name('tenants.show');
        Route::put('/tenants/{id}',                        [TenantController::class, 'update'])->name('tenants.update');
        Route::patch('/tenants/{id}/toggle',               [TenantController::class, 'toggleActif'])->name('tenants.toggle');
        Route::post('/tenants/{id}/migrate',               [TenantController::class, 'migrate'])->name('tenants.migrate');
        Route::delete('/tenants/{id}',                     [TenantController::class, 'destroy'])->name('tenants.destroy');
        Route::post('/tenants/{id}/reset-password',        [TenantController::class, 'resetAdminPassword'])->name('tenants.reset-password');
        Route::post('/tenants/{id}/renvoyer-email',        [TenantController::class, 'renvoyerEmail'])->name('tenants.renvoyer-email');
    });
});
 


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
    Route::get('/eleve/bulletin',  [BulletinController::class, 'bulletinEleve'])->name('eleve.bulletin');
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

    Route::prefix('correction')->name('correction.')->group(function () {
    Route::get('/devoirs/{devoirId}/resultats',             [\App\Http\Controllers\Web\Enseignant\CorrectionController::class, 'resultats'])->name('resultats');
    Route::get('/devoirs/{devoirId}/eleves/{eleveId}',      [\App\Http\Controllers\Web\Enseignant\CorrectionController::class, 'detailEleve'])->name('detail');
    Route::post('/reponses/{reponseId}',                    [\App\Http\Controllers\Web\Enseignant\CorrectionController::class, 'corriger'])->name('corriger');
    Route::post('/tentatives/{tentativeId}/tout',           [\App\Http\Controllers\Web\Enseignant\CorrectionController::class, 'corrigerTout'])->name('tout');
    });
 

    Route::get('/enseignant/eleves/{eleveId}/bulletin', [BulletinController::class, 'bulletinEleve'])->name('enseignant.bulletin.eleve');
    Route::get('/enseignant/classes/{classeId}/releve', [BulletinController::class, 'bulletinClasse'])->name('enseignant.bulletin.classe');
    
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

    Route::prefix('affectations')->name('affectations.')->group(function () {
    Route::get('/',              [AffectationController::class, 'index'])->name('index');
    Route::get('/{id}',         [AffectationController::class, 'show'])->name('show');
    Route::post('/',             [AffectationController::class, 'store'])->name('store');
    Route::post('/masse',        [AffectationController::class, 'storeMasse'])->name('store-masse');
    Route::delete('/{id}',       [AffectationController::class, 'destroy'])->name('destroy');
    Route::post('/copier-annee', [AffectationController::class, 'copierAnnee'])->name('copier');
     });

    Route::prefix('eleve-classes')->name('eleve-classes.')->group(function () {
    Route::get('/',                          [\App\Http\Controllers\Web\Admin\EleveClasseController::class, 'index'])->name('index');
    Route::get('/{classeId}',                [\App\Http\Controllers\Web\Admin\EleveClasseController::class, 'show'])->name('show');
    Route::post('/',                         [\App\Http\Controllers\Web\Admin\EleveClasseController::class, 'store'])->name('store');
    Route::post('/masse',                    [\App\Http\Controllers\Web\Admin\EleveClasseController::class, 'storeMasse'])->name('store-masse');
    Route::delete('/{classeId}/{eleveId}',   [\App\Http\Controllers\Web\Admin\EleveClasseController::class, 'destroy'])->name('destroy');
    Route::post('/deplacer',                 [\App\Http\Controllers\Web\Admin\EleveClasseController::class, 'deplacer'])->name('deplacer');
    Route::get('/{classeId}/export',         [\App\Http\Controllers\Web\Admin\EleveClasseController::class, 'exportCsv'])->name('export');
    });

    Route::prefix('import-csv')->name('import-csv.')->group(function () {
    Route::get('/',                              [\App\Http\Controllers\Web\Admin\ImportCsvController::class, 'index'])->name('index');
    Route::get('/modele/{type}',                 [\App\Http\Controllers\Web\Admin\ImportCsvController::class, 'telechargerModele'])->name('modele');
    Route::post('/previsualiser',                [\App\Http\Controllers\Web\Admin\ImportCsvController::class, 'previsualiser'])->name('previsualiser');
    Route::post('/importer',                     [\App\Http\Controllers\Web\Admin\ImportCsvController::class, 'importer'])->name('importer');
    });
    Route::get('/admin/eleves/{eleveId}/bulletin', [BulletinController::class, 'bulletinEleve'])->name('admin.bulletin.eleve');
    Route::get('/admin/classes/{classeId}/releve', [BulletinController::class, 'bulletinClasse'])->name('admin.bulletin.classe');

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


// Mot de passe oublié
Route::get('/forgot-password',  [PasswordResetController::class, 'showForgotForm'])->name('password.forgot');
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->name('password.send');
 
// Réinitialisation (lien depuis email)
Route::get('/reset-password/{token}',  [PasswordResetController::class, 'showResetForm'])->name('password.reset.form');
Route::post('/reset-password',         [PasswordResetController::class, 'resetPassword'])->name('password.reset');
 
// Changer son mot de passe (utilisateur connecté)
Route::middleware('auth')->group(function () {
    Route::get('/change-password',  [PasswordResetController::class, 'showChangeForm'])->name('password.change');
    Route::post('/change-password', [PasswordResetController::class, 'changePassword'])->name('password.change.update');
});
 