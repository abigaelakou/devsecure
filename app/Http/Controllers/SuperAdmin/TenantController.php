<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Landlord\Tenant;
use App\Models\Landlord\SuperAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TenantController extends Controller
{
    // ── DASHBOARD SUPER ADMIN ─────────────────────────────────────────
    public function dashboard()
    {
        $tenants = Tenant::orderByDesc('created_at')->get();

        $stats = [
            'total'     => $tenants->count(),
            'actifs'    => $tenants->where('actif', true)->count(),
            'inactifs'  => $tenants->where('actif', false)->count(),
            'gratuits'  => $tenants->where('plan', 'gratuit')->count(),
            'standards' => $tenants->where('plan', 'standard')->count(),
            'premiums'  => $tenants->where('plan', 'premium')->count(),
        ];

        return view('superadmin.dashboard', compact('tenants', 'stats'));
    }

    // ── CRÉER UN NOUVEAU TENANT (ÉTABLISSEMENT) ───────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:150',
            'domain'        => 'required|string|max:100|unique:tenants,id|regex:/^[a-z0-9\-]+$/',
            'email_contact' => 'required|email',
            'ville'         => 'nullable|string|max:100',
            'pays'          => 'string|max:5',
            'plan'          => 'required|in:gratuit,standard,premium',
            'max_eleves'    => 'integer|min:10',
            'admin_nom'     => 'required|string|max:100',
            'admin_prenoms' => 'required|string|max:100',
            'admin_email'   => 'required|email',
            'admin_password'=> 'required|string|min:8|confirmed',
        ]);

        // Vérifier que le domaine n'existe pas déjà
        if (Tenant::find($request->domain)) {
            return back()->withErrors(['domain' => 'Ce sous-domaine est déjà utilisé.'])->withInput();
        }

        try {
            DB::beginTransaction();

            // 1. Créer le tenant
            $tenant = Tenant::create([
                'id'            => $request->domain,
                'name'          => $request->name,
                'email_contact' => $request->email_contact,
                'ville'         => $request->ville,
                'pays'          => $request->pays ?? 'CI',
                'plan'          => $request->plan,
                'max_eleves'    => $request->max_eleves ?? 200,
                'max_enseignants'=> $request->max_enseignants ?? 20,
                'actif'         => true,
            ]);

            // 2. Créer le domaine associé
            $tenant->domains()->create([
                'domain' => $request->domain . '.' . config('app.base_domain', 'devsecure.ci'),
            ]);

            DB::commit();

            // 3. Jouer les migrations pour ce tenant
            Artisan::call('tenants:migrate', [
                '--tenants' => [$tenant->id],
                '--force'   => true,
            ]);

            // 4. Créer l'admin de l'établissement via tinker-like
            tenancy()->initialize($tenant);

            \App\Models\Tenant\User::create([
                'nom'      => $request->admin_nom,
                'prenoms'  => $request->admin_prenoms,
                'email'    => $request->admin_email,
                'password' => Hash::make($request->admin_password),
                'role'     => 'admin',
                'actif'    => true,
            ]);

            // 5. Créer l'année scolaire courante
            \App\Models\Tenant\AnneeScolaire::create([
                'libelle'    => date('Y') . '-' . (date('Y') + 1),
                'date_debut' => date('Y') . '-09-01',
                'date_fin'   => (date('Y') + 1) . '-06-30',
                'active'     => true,
            ]);

            tenancy()->end();

            return redirect()->route('superadmin.dashboard')
                ->with('success', "Établissement \"{$tenant->name}\" créé avec succès !");

        } catch (\Exception $e) {
            DB::rollBack();
            tenancy()->end();

            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la création : ' . $e->getMessage());
        }
    }

    // ── DÉTAIL D'UN TENANT ────────────────────────────────────────────
    public function show(string $tenantId)
    {
        $tenant = Tenant::findOrFail($tenantId);

        // Stats depuis la DB du tenant
        $stats = [];
        try {
            tenancy()->initialize($tenant);
            $stats = [
                'nb_eleves'      => \App\Models\Tenant\User::eleves()->count(),
                'nb_enseignants' => \App\Models\Tenant\User::enseignants()->count(),
                'nb_devoirs'     => \App\Models\Tenant\Devoir::count(),
                'nb_tentatives'  => \App\Models\Tenant\TentativeDevoir::count(),
            ];
            tenancy()->end();
        } catch (\Exception $e) {
            tenancy()->end();
        }

        return view('superadmin.show', compact('tenant', 'stats'));
    }

    // ── MODIFIER UN TENANT ────────────────────────────────────────────
    public function update(Request $request, string $tenantId)
    {
        $tenant = Tenant::findOrFail($tenantId);

        $request->validate([
            'name'           => 'required|string|max:150',
            'email_contact'  => 'required|email',
            'plan'           => 'required|in:gratuit,standard,premium',
            'max_eleves'     => 'integer|min:10',
            'max_enseignants'=> 'integer|min:1',
            'actif'          => 'boolean',
        ]);

        $tenant->update($request->only([
            'name', 'email_contact', 'ville', 'pays',
            'plan', 'max_eleves', 'max_enseignants', 'actif',
        ]));

        return back()->with('success', 'Établissement mis à jour.');
    }

    // ── ACTIVER / DÉSACTIVER ──────────────────────────────────────────
    public function toggleActif(string $tenantId)
    {
        $tenant = Tenant::findOrFail($tenantId);
        $tenant->update(['actif' => !$tenant->actif]);

        $msg = $tenant->actif ? 'Établissement activé.' : 'Établissement désactivé.';
        return back()->with('success', $msg);
    }

    // ── RELANCER LES MIGRATIONS D'UN TENANT ──────────────────────────
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

    // ── SUPPRIMER UN TENANT ───────────────────────────────────────────
    public function destroy(string $tenantId)
    {
        $tenant = Tenant::findOrFail($tenantId);

        try {
            // Supprimer la DB du tenant
            tenancy()->initialize($tenant);
            tenancy()->end();

            Artisan::call('tenants:delete', ['--tenants' => [$tenant->id]]);

            $tenant->domains()->delete();
            $tenant->delete();

            return redirect()->route('superadmin.dashboard')
                ->with('success', "Établissement supprimé définitivement.");
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }
}