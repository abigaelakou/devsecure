<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Landlord\Tenant;
use App\Models\Landlord\SuperAdmin;
use App\Mail\SuperAdmin\BienvenueTenantMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class TenantController extends Controller
{
    // ── DASHBOARD SUPER ADMIN ─────────────────────────────────────────
    public function dashboard()
    {
        $tenants = Tenant::orderByDesc('created_at')->get();

        // Stats globales avec données de chaque tenant
        $tenantStats = $tenants->map(function ($tenant) {
            $stats = $this->getTenantStats($tenant);
            return array_merge(['tenant' => $tenant], $stats);
        });

        $stats = [
            'total'           => $tenants->count(),
            'actifs'          => $tenants->where('actif', true)->count(),
            'inactifs'        => $tenants->where('actif', false)->count(),
            'gratuits'        => $tenants->where('plan', 'gratuit')->count(),
            'standards'       => $tenants->where('plan', 'standard')->count(),
            'premiums'        => $tenants->where('plan', 'premium')->count(),
            'total_eleves'    => $tenantStats->sum('nb_eleves'),
            'total_devoirs'   => $tenantStats->sum('nb_devoirs'),
            'total_tentatives'=> $tenantStats->sum('nb_tentatives'),
        ];

        return view('superadmin.dashboard', compact('tenants', 'tenantStats', 'stats'));
    }

    // ── CRÉER UN NOUVEAU TENANT ───────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'name'                  => 'required|string|max:150',
            'domain'                => 'required|string|max:100|regex:/^[a-z0-9\-]+$/|unique:tenants,id',
            'email_contact'         => 'required|email',
            'ville'                 => 'nullable|string|max:100',
            'pays'                  => 'string|max:5',
            'plan'                  => 'required|in:gratuit,standard,premium',
            'max_eleves'            => 'integer|min:10',
            'admin_nom'             => 'required|string|max:100',
            'admin_prenoms'         => 'required|string|max:100',
            'admin_email'           => 'required|email',
            'admin_password'        => 'required|string|min:8|confirmed',
            'envoyer_email'         => 'boolean',
        ]);

        // Limites selon le plan
        $limites = $this->getLimitesPlan($request->plan);

        try {
            DB::beginTransaction();

            $tenant = Tenant::create([
                'id'             => $request->domain,
                'name'           => $request->name,
                'email_contact'  => $request->email_contact,
                'ville'          => $request->ville,
                'pays'           => $request->pays ?? 'CI',
                'plan'           => $request->plan,
                'max_eleves'     => $request->max_eleves ?? $limites['max_eleves'],
                'max_enseignants'=> $limites['max_enseignants'],
                'actif'          => true,
            ]);

            $tenant->domains()->create([
                'domain' => $request->domain . '.' . config('app.base_domain', 'devsecure.ci'),
            ]);

            DB::commit();

            // Migrations
            Artisan::call('tenants:migrate', [
                '--tenants' => [$tenant->id],
                '--force'   => true,
            ]);

            // Créer admin + année scolaire dans le tenant
            tenancy()->initialize($tenant);

            $adminPassword = $request->admin_password;

            $admin = \App\Models\Tenant\User::create([
                'nom'      => $request->admin_nom,
                'prenoms'  => $request->admin_prenoms,
                'email'    => $request->admin_email,
                'password' => Hash::make($adminPassword),
                'role'     => 'admin',
                'actif'    => true,
            ]);

            \App\Models\Tenant\AnneeScolaire::create([
                'libelle'    => date('Y') . '-' . (date('Y') + 1),
                'date_debut' => date('Y') . '-09-01',
                'date_fin'   => (date('Y') + 1) . '-06-30',
                'active'     => true,
            ]);

            tenancy()->end();

            // Email de bienvenue
            if ($request->boolean('envoyer_email', true)) {
                try {
                    Mail::to($request->admin_email)->send(
                        new BienvenueTenantMail($tenant, $admin, $adminPassword)
                    );
                } catch (\Exception $e) {
                    \Log::warning("Email bienvenue échoué pour {$tenant->id} : " . $e->getMessage());
                }
            }

            return redirect()->route('superadmin.dashboard')
                ->with('success', "✅ Établissement \"{$tenant->name}\" créé avec succès !");

        } catch (\Exception $e) {
            DB::rollBack();
            tenancy()->end();
            return back()->withInput()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    // ── DÉTAIL D'UN TENANT ────────────────────────────────────────────
    public function show(string $tenantId)
    {
        $tenant = Tenant::findOrFail($tenantId);
        $stats  = $this->getTenantStats($tenant);
        $limites = $this->getLimitesPlan($tenant->plan);

        // Vérifier si les limites sont atteintes
        $alertes = [];
        if ($stats['nb_eleves'] >= $limites['max_eleves'] * 0.9) {
            $alertes[] = "⚠️ Limite élèves bientôt atteinte ({$stats['nb_eleves']}/{$limites['max_eleves']})";
        }
        if (!$tenant->actif) {
            $alertes[] = "🔴 Établissement désactivé";
        }

        return view('superadmin.show', compact('tenant', 'stats', 'limites', 'alertes'));
    }

    // ── MODIFIER UN TENANT ────────────────────────────────────────────
    public function update(Request $request, string $tenantId)
    {
        $tenant = Tenant::findOrFail($tenantId);

        $request->validate([
            'name'            => 'required|string|max:150',
            'email_contact'   => 'required|email',
            'plan'            => 'required|in:gratuit,standard,premium',
            'max_eleves'      => 'integer|min:10',
            'max_enseignants' => 'integer|min:1',
            'actif'           => 'boolean',
        ]);

        // Si downgrade de plan, vérifier les limites
        if ($request->plan !== $tenant->plan) {
            $stats   = $this->getTenantStats($tenant);
            $limites = $this->getLimitesPlan($request->plan);

            if ($stats['nb_eleves'] > $limites['max_eleves']) {
                return back()->with('error',
                    "Impossible de passer en plan {$request->plan} : l'établissement a {$stats['nb_eleves']} élèves (max {$limites['max_eleves']})."
                );
            }
        }

        $tenant->update($request->only([
            'name', 'email_contact', 'ville', 'pays',
            'plan', 'max_eleves', 'max_enseignants', 'actif',
        ]));

        return back()->with('success', 'Établissement mis à jour.');
    }

    // ── TOGGLE ACTIF ──────────────────────────────────────────────────
    public function toggleActif(string $tenantId)
    {
        $tenant = Tenant::findOrFail($tenantId);
        $tenant->update(['actif' => !$tenant->actif]);
        $msg = $tenant->actif ? "✅ {$tenant->name} activé." : "⏸ {$tenant->name} désactivé.";
        return back()->with('success', $msg);
    }

    // ── RÉENVOYER EMAIL BIENVENUE ─────────────────────────────────────
    public function renvoyerEmail(Request $request, string $tenantId)
    {
        $tenant = Tenant::findOrFail($tenantId);

        $request->validate([
            'admin_email'    => 'required|email',
            'admin_password' => 'required|string|min:8',
        ]);

        try {
            tenancy()->initialize($tenant);
            $admin = \App\Models\Tenant\User::where('email', $request->admin_email)
                ->where('role', 'admin')
                ->first();
            tenancy()->end();

            if (!$admin) {
                return back()->with('error', 'Admin introuvable.');
            }

            Mail::to($request->admin_email)->send(
                new BienvenueTenantMail($tenant, $admin, $request->admin_password)
            );

            return back()->with('success', 'Email de bienvenue renvoyé.');
        } catch (\Exception $e) {
            tenancy()->end();
            return back()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    // ── RÉINITIALISER MOT DE PASSE ADMIN TENANT ──────────────────────
    public function resetAdminPassword(Request $request, string $tenantId)
    {
        $request->validate([
            'admin_email'    => 'required|email',
            'nouveau_mdp'    => 'required|string|min:8',
        ]);

        $tenant = Tenant::findOrFail($tenantId);

        try {
            tenancy()->initialize($tenant);

            $admin = \App\Models\Tenant\User::where('email', $request->admin_email)
                ->where('role', 'admin')
                ->first();

            if (!$admin) {
                tenancy()->end();
                return back()->with('error', 'Admin introuvable dans cet établissement.');
            }

            $admin->update(['password' => Hash::make($request->nouveau_mdp)]);
            tenancy()->end();

            return back()->with('success', "Mot de passe de {$admin->email} réinitialisé.");
        } catch (\Exception $e) {
            tenancy()->end();
            return back()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    // ── RELANCER MIGRATIONS ───────────────────────────────────────────
    public function migrate(string $tenantId)
    {
        $tenant = Tenant::findOrFail($tenantId);

        try {
            Artisan::call('tenants:migrate', [
                '--tenants' => [$tenant->id],
                '--force'   => true,
            ]);
            return back()->with('success', 'Migrations relancées avec succès.');
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    // ── SUPPRIMER ─────────────────────────────────────────────────────
    public function destroy(string $tenantId)
    {
        $tenant = Tenant::findOrFail($tenantId);

        try {
            tenancy()->initialize($tenant);
            tenancy()->end();

            $tenant->domains()->delete();
            $tenant->delete();

            // Supprimer la DB du tenant
            try {
                DB::statement("DROP DATABASE IF EXISTS `tenant{$tenantId}`");
            } catch (\Exception $e) {
                \Log::warning("Impossible de supprimer la DB tenant{$tenantId} : " . $e->getMessage());
            }

            return redirect()->route('superadmin.dashboard')
                ->with('success', "Établissement supprimé définitivement.");
        } catch (\Exception $e) {
            tenancy()->end();
            return back()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    // ── EXPORT CSV ────────────────────────────────────────────────────
    public function exportCsv()
    {
        $tenants = Tenant::all();

        return response()->streamDownload(function () use ($tenants) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, ['ID', 'Nom', 'Email', 'Ville', 'Plan', 'Max élèves', 'Actif', 'Créé le'], ';');
            foreach ($tenants as $t) {
                fputcsv($handle, [
                    $t->id, $t->name, $t->email_contact, $t->ville,
                    $t->plan, $t->max_eleves,
                    $t->actif ? 'Oui' : 'Non',
                    $t->created_at->format('d/m/Y'),
                ], ';');
            }
            fclose($handle);
        }, 'tenants_' . now()->format('Y-m-d') . '.csv', ['Content-Type' => 'text/csv']);
    }

    // ── HELPERS ───────────────────────────────────────────────────────
    private function getTenantStats(Tenant $tenant): array
    {
        $stats = ['nb_eleves' => 0, 'nb_enseignants' => 0, 'nb_devoirs' => 0, 'nb_tentatives' => 0];

        try {
            tenancy()->initialize($tenant);
            $stats = [
                'nb_eleves'       => \App\Models\Tenant\User::eleves()->count(),
                'nb_enseignants'  => \App\Models\Tenant\User::enseignants()->count(),
                'nb_devoirs'      => \App\Models\Tenant\Devoir::count(),
                'nb_tentatives'   => \App\Models\Tenant\TentativeDevoir::count(),
            ];
            tenancy()->end();
        } catch (\Exception $e) {
            tenancy()->end();
        }

        return $stats;
    }

   private function getLimitesPlan(string $plan): array
    {
        return config("plans.{$plan}", config('plans.gratuit'));
    }

}