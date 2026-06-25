<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Super Admin — DevSecure</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --purple: #7C3AED; --purple-light: #F5F3FF; --purple-dark: #5B21B6;
            --primary: #4F46E5; --success: #059669; --danger: #DC2626; --warning: #D97706;
            --bg: #0F0A1E; --sidebar: #1A1033; --card: #1E1744; --border: rgba(255,255,255,0.08);
            --text: #F8FAFC; --muted: rgba(255,255,255,0.5);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', system-ui, sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; display: flex; }

        /* SIDEBAR */
        .sidebar {
            width: 240px; background: var(--sidebar); min-height: 100vh;
            position: fixed; top: 0; left: 0; display: flex; flex-direction: column;
            border-right: 1px solid var(--border);
        }
        .sidebar-logo {
            padding: 1.5rem 1.25rem; border-bottom: 1px solid var(--border);
            display: flex; align-items: center; gap: 0.75rem;
        }
        .logo-icon {
            width: 40px; height: 40px;
            background: linear-gradient(135deg, var(--purple), var(--primary));
            border-radius: 12px; display: flex; align-items: center; justify-content: center;
            color: white; font-size: 1.1rem;
        }
        .logo-text { font-size: 1rem; font-weight: 700; color: white; }
        .logo-badge { font-size: 0.65rem; background: rgba(124,58,237,0.4); color: #C4B5FD; padding: 1px 8px; border-radius: 10px; }

        .nav-section { padding: 1rem 0.75rem; flex: 1; }
        .nav-label { font-size: 0.65rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.08em; color: var(--muted); padding: 0.75rem 0.5rem 0.3rem; }
        .nav-item {
            display: flex; align-items: center; gap: 0.7rem;
            padding: 0.6rem 0.75rem; border-radius: 8px;
            color: var(--muted); font-size: 0.875rem; text-decoration: none;
            margin-bottom: 2px; transition: all 0.15s;
        }
        .nav-item:hover  { background: rgba(255,255,255,0.05); color: white; }
        .nav-item.active { background: rgba(124,58,237,0.3); color: #C4B5FD; font-weight: 600; }
        .nav-item i      { font-size: 1rem; }

        /* MAIN */
        .main { margin-left: 240px; flex: 1; }
        .topbar {
            background: var(--card); border-bottom: 1px solid var(--border);
            padding: 0.875rem 2rem; display: flex; align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 50;
        }
        .topbar-title { font-size: 1.1rem; font-weight: 700; }
        .page-content { padding: 2rem; }

        /* CARDS */
        .stat-card {
            background: var(--card); border: 1px solid var(--border);
            border-radius: 14px; padding: 1.25rem;
        }
        .stat-value { font-size: 2rem; font-weight: 700; }
        .stat-label { font-size: 0.78rem; color: var(--muted); margin-top: 0.25rem; }

        /* TABLE */
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th {
            padding: 0.6rem 1.5rem; font-size: 0.72rem; font-weight: 600;
            text-transform: uppercase; letter-spacing: 0.05em; color: var(--muted);
            background: rgba(255,255,255,0.03); text-align: left;
            border-bottom: 1px solid var(--border);
        }
        .data-table td {
            padding: 0.875rem 1.5rem; border-bottom: 1px solid var(--border);
            font-size: 0.875rem;
        }
        .data-table tr:hover td { background: rgba(255,255,255,0.02); }

        /* PLAN BADGES */
        .badge-gratuit  { background: rgba(107,114,128,0.2); color: #9CA3AF; }
        .badge-standard { background: rgba(79,70,229,0.2);   color: #818CF8; }
        .badge-premium  { background: rgba(245,158,11,0.2);  color: #FCD34D; }
        .badge-plan { font-size: 0.72rem; font-weight: 600; padding: 3px 10px; border-radius: 20px; }

        .badge-actif   { background: rgba(5,150,105,0.2); color: #6EE7B7; }
        .badge-inactif { background: rgba(220,38,38,0.2); color: #FCA5A5; }
        .badge-statut  { font-size: 0.72rem; font-weight: 600; padding: 3px 10px; border-radius: 20px; }

        /* MODAL */
        .modal-overlay {
            position: fixed; inset: 0; background: rgba(0,0,0,0.7);
            display: none; align-items: center; justify-content: center; z-index: 300;
            backdrop-filter: blur(4px);
        }
        .modal-overlay.show { display: flex; }
        .modal-box {
            background: #1E1744; border: 1px solid var(--border);
            border-radius: 20px; padding: 2rem; width: 90%; max-width: 580px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.5); max-height: 90vh; overflow-y: auto;
        }
        .modal-title { font-size: 1.1rem; font-weight: 700; margin-bottom: 1.5rem; }

        /* FORM */
        .form-group { margin-bottom: 1rem; }
        .form-label-dark { font-size: 0.8rem; font-weight: 500; color: var(--muted); display: block; margin-bottom: 0.4rem; }
        .form-control-dark {
            width: 100%; padding: 0.75rem; background: rgba(255,255,255,0.05);
            border: 1.5px solid var(--border); border-radius: 10px;
            font-size: 0.875rem; color: white; transition: border-color 0.2s;
        }
        .form-control-dark:focus { outline: none; border-color: var(--purple); background: rgba(255,255,255,0.08); }
        .form-control-dark::placeholder { color: var(--muted); }
        select.form-control-dark option { background: #1E1744; }

        .btn-purple {
            background: linear-gradient(135deg, var(--purple), var(--primary));
            color: white; border: none; border-radius: 10px;
            padding: 0.75rem 1.5rem; font-weight: 600; cursor: pointer;
            transition: all 0.2s; font-size: 0.875rem;
        }
        .btn-purple:hover { opacity: 0.9; transform: translateY(-1px); }
        .btn-ghost {
            background: rgba(255,255,255,0.08); color: var(--muted);
            border: 1px solid var(--border); border-radius: 10px;
            padding: 0.75rem 1.5rem; font-weight: 600; cursor: pointer;
            transition: all 0.2s; font-size: 0.875rem;
        }
        .btn-ghost:hover { background: rgba(255,255,255,0.12); color: white; }

        .section-divider {
            border: none; border-top: 1px solid var(--border);
            margin: 1.25rem 0;
        }
        .section-title {
            font-size: 0.75rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: 0.08em; color: var(--muted); margin-bottom: 1rem;
        }
    </style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
    <div class="sidebar-logo">
        <div class="logo-icon"><i class="bi bi-shield-lock"></i></div>
        <div>
            <div class="logo-text">DevSecure</div>
            <div class="logo-badge">Super Admin</div>
        </div>
    </div>
    <nav class="nav-section">
        <div class="nav-label">Navigation</div>
        <a href="{{ route('superadmin.dashboard') }}" class="nav-item active">
            <i class="bi bi-grid-1x2"></i> Tableau de bord
        </a>
        <div class="nav-label">Compte</div>
        <form method="POST" action="{{ route('superadmin.logout') }}">
            @csrf
            <button type="submit" class="nav-item" style="width:100%;background:none;border:none;cursor:pointer;text-align:left">
                <i class="bi bi-box-arrow-right"></i> Déconnexion
            </button>
        </form>
    </nav>
    <div style="padding:1rem 1.25rem;border-top:1px solid var(--border)">
        <div style="font-size:0.82rem;font-weight:600;color:white">
            {{ Auth::guard('superadmin')->user()?->nom_complet }}
        </div>
        <div style="font-size:0.7rem;color:var(--muted)">Super Administrateur</div>
    </div>
</aside>

<!-- MAIN -->
<div class="main">
    <div class="topbar">
        <div>
            <div class="topbar-title">Tableau de bord global</div>
            <div style="font-size:0.78rem;color:var(--muted)">{{ now()->isoFormat('dddd D MMMM YYYY') }}</div>
        </div>
        <button onclick="document.getElementById('modalNouveauTenant').classList.add('show')"
                class="btn-purple">
            <i class="bi bi-plus-lg me-1"></i> Nouvel établissement
        </button>
    </div>

    <div class="page-content">

        @if(session('success'))
        <div style="background:rgba(5,150,105,0.2);border:1px solid rgba(5,150,105,0.4);color:#6EE7B7;border-radius:10px;padding:0.875rem 1.25rem;margin-bottom:1.5rem;font-size:0.875rem">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div style="background:rgba(220,38,38,0.2);border:1px solid rgba(220,38,38,0.4);color:#FCA5A5;border-radius:10px;padding:0.875rem 1.25rem;margin-bottom:1.5rem;font-size:0.875rem">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
        </div>
        @endif

        {{-- Stats --}}
        <div style="display:grid;grid-template-columns:repeat(6,1fr);gap:1rem;margin-bottom:2rem">
            @foreach([
                ['Total','bi-building',$stats['total'],'#7C3AED'],
                ['Actifs','bi-check-circle',$stats['actifs'],'#059669'],
                ['Inactifs','bi-x-circle',$stats['inactifs'],'#DC2626'],
                ['Gratuits','bi-gift',$stats['gratuits'],'#6B7280'],
                ['Standard','bi-star',$stats['standards'],'#4F46E5'],
                ['Premium','bi-star-fill',$stats['premiums'],'#D97706'],
            ] as [$label,$icon,$val,$color])
            <div class="stat-card" style="text-align:center">
                <div style="font-size:1.1rem;color:{{ $color }};margin-bottom:0.5rem"><i class="bi {{ $icon }}"></i></div>
                <div class="stat-value" style="font-size:1.75rem;color:{{ $color }}">{{ $val }}</div>
                <div class="stat-label">{{ $label }}</div>
            </div>
            @endforeach
        </div>

        {{-- Table établissements --}}
        <div style="background:var(--card);border:1px solid var(--border);border-radius:14px;overflow:hidden">
            <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center">
                <h2 style="font-size:0.95rem;font-weight:700">Établissements</h2>
                <span style="font-size:0.8rem;color:var(--muted)">{{ $tenants->count() }} au total</span>
            </div>

            <table class="data-table">
                <thead>
                    <tr>
                        @foreach(['Établissement','Domaine','Plan','Statut','Créé le','Actions'] as $th)
                        <th>{{ $th }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($tenants as $tenant)
                    <tr>
                        <td>
                            <div style="font-weight:600">{{ $tenant->name }}</div>
                            <div style="font-size:0.75rem;color:var(--muted)">{{ $tenant->email_contact }}</div>
                        </td>
                        <td>
                            <code style="background:rgba(255,255,255,0.05);padding:2px 8px;border-radius:6px;font-size:0.8rem;color:#C4B5FD">
                                {{ $tenant->id }}
                            </code>
                        </td>
                        <td>
                            <span class="badge-plan badge-{{ $tenant->plan }}">
                                {{ ucfirst($tenant->plan) }}
                            </span>
                        </td>
                        <td>
                            <span class="badge-statut {{ $tenant->actif ? 'badge-actif' : 'badge-inactif' }}">
                                {{ $tenant->actif ? '● Actif' : '○ Inactif' }}
                            </span>
                        </td>
                        <td style="color:var(--muted)">{{ $tenant->created_at->format('d/m/Y') }}</td>
                        <td>
                            <div style="display:flex;gap:0.4rem">
                                <a href="{{ route('superadmin.tenants.show', $tenant->id) }}"
                                   style="padding:4px 10px;background:rgba(79,70,229,0.2);color:#818CF8;border-radius:6px;text-decoration:none;font-size:0.75rem;font-weight:600">
                                    Gérer
                                </a>
                                <form method="POST" action="{{ route('superadmin.tenants.toggle', $tenant->id) }}">
                                    @csrf @method('PATCH')
                                    <button type="submit"
                                            style="padding:4px 10px;background:rgba(255,255,255,0.05);color:var(--muted);border:1px solid var(--border);border-radius:6px;font-size:0.75rem;cursor:pointer">
                                        {{ $tenant->actif ? 'Désactiver' : 'Activer' }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align:center;padding:3rem;color:var(--muted)">
                            <i class="bi bi-building" style="font-size:2rem;display:block;margin-bottom:0.75rem"></i>
                            Aucun établissement. Créez le premier !
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL NOUVEL ÉTABLISSEMENT ─────────────────────────────────── --}}
<div id="modalNouveauTenant" class="modal-overlay" onclick="if(event.target===this)this.classList.remove('show')">
    <div class="modal-box">
        <div class="modal-title"><i class="bi bi-building-add me-2" style="color:#7C3AED"></i>Nouvel établissement</div>

        <form method="POST" action="{{ route('superadmin.tenants.store') }}">
            @csrf

            <div class="section-title">Informations de l'établissement</div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
                <div class="form-group" style="grid-column:1/-1">
                    <label class="form-label-dark">Nom de l'établissement <span style="color:#F87171">*</span></label>
                    <input type="text" name="name" class="form-control-dark" required
                           placeholder="Lycée Moderne d'Abidjan" value="{{ old('name') }}">
                </div>
                <div class="form-group">
                    <label class="form-label-dark">
                        Sous-domaine <span style="color:#F87171">*</span>
                        <span style="color:var(--muted);font-weight:400"> (lettres, chiffres, tirets)</span>
                    </label>
                    <div style="display:flex;align-items:center;gap:0.5rem">
                        <input type="text" name="domain" class="form-control-dark" required
                               placeholder="lycee-moderne" value="{{ old('domain') }}"
                               style="flex:1"
                               oninput="this.value=this.value.toLowerCase().replace(/[^a-z0-9\-]/g,'')">
                        <span style="font-size:0.8rem;color:var(--muted);white-space:nowrap">.devsecure.ci</span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label-dark">Email contact <span style="color:#F87171">*</span></label>
                    <input type="email" name="email_contact" class="form-control-dark" required
                           placeholder="admin@lycee.ci" value="{{ old('email_contact') }}">
                </div>
                <div class="form-group">
                    <label class="form-label-dark">Ville</label>
                    <input type="text" name="ville" class="form-control-dark"
                           placeholder="Abidjan" value="{{ old('ville') }}">
                </div>
                <div class="form-group">
                    <label class="form-label-dark">Plan</label>
                    <select name="plan" class="form-control-dark" required>
                        <option value="gratuit"  {{ old('plan')=='gratuit' ?'selected':'' }}>Gratuit (100 élèves)</option>
                        <option value="standard" {{ old('plan')=='standard'?'selected':'' }}>Standard (500 élèves)</option>
                        <option value="premium"  {{ old('plan')=='premium' ?'selected':'' }}>Premium (illimité)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label-dark">Max élèves</label>
                    <input type="number" name="max_eleves" class="form-control-dark"
                           value="{{ old('max_eleves', 200) }}" min="10">
                </div>
            </div>

            <hr class="section-divider">
            <div class="section-title">Compte administrateur de l'école</div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
                <div class="form-group">
                    <label class="form-label-dark">Nom <span style="color:#F87171">*</span></label>
                    <input type="text" name="admin_nom" class="form-control-dark" required
                           placeholder="Kouassi" value="{{ old('admin_nom') }}">
                </div>
                <div class="form-group">
                    <label class="form-label-dark">Prénoms <span style="color:#F87171">*</span></label>
                    <input type="text" name="admin_prenoms" class="form-control-dark" required
                           placeholder="Jean-Baptiste" value="{{ old('admin_prenoms') }}">
                </div>
                <div class="form-group" style="grid-column:1/-1">
                    <label class="form-label-dark">Email admin <span style="color:#F87171">*</span></label>
                    <input type="email" name="admin_email" class="form-control-dark" required
                           placeholder="admin@lycee.ci" value="{{ old('admin_email') }}">
                </div>
                <div class="form-group">
                    <label class="form-label-dark">Mot de passe <span style="color:#F87171">*</span></label>
                    <input type="password" name="admin_password" class="form-control-dark" required
                           placeholder="Min. 8 caractères">
                </div>
                <div class="form-group">
                    <label class="form-label-dark">Confirmer le mot de passe</label>
                    <input type="password" name="admin_password_confirmation" class="form-control-dark" required
                           placeholder="Répéter le mot de passe">
                </div>
            </div>

            <div style="display:flex;gap:0.75rem;margin-top:0.5rem">
                <button type="button"
                        onclick="document.getElementById('modalNouveauTenant').classList.remove('show')"
                        class="btn-ghost" style="flex:1">
                    Annuler
                </button>
                <button type="submit" class="btn-purple" style="flex:2">
                    <i class="bi bi-building-add me-1"></i> Créer l'établissement
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Ouvrir modal si erreurs de validation
@if($errors->any())
document.getElementById('modalNouveauTenant').classList.add('show');
@endif
</script>

</body>
</html>