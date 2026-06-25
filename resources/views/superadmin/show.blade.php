<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $tenant->name }} — Super Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --purple:#7C3AED; --primary:#4F46E5; --bg:#0F0A1E; --card:#1E1744; --border:rgba(255,255,255,0.08); --text:#F8FAFC; --muted:rgba(255,255,255,0.5); }
        * { box-sizing:border-box; margin:0; padding:0; }
        body { font-family:'Inter',system-ui,sans-serif; background:var(--bg); color:var(--text); min-height:100vh; padding:2rem; }
        .card-dark { background:var(--card); border:1px solid var(--border); border-radius:14px; padding:1.5rem; margin-bottom:1.5rem; }
        .card-title { font-size:0.78rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--muted); margin-bottom:1.25rem; }
        .form-control-dark { width:100%; padding:0.75rem; background:rgba(255,255,255,0.05); border:1.5px solid var(--border); border-radius:10px; font-size:0.875rem; color:white; margin-bottom:1rem; }
        .form-control-dark:focus { outline:none; border-color:var(--purple); }
        .form-control-dark::placeholder { color:var(--muted); }
        select.form-control-dark option { background:#1E1744; }
        .label { font-size:0.78rem; color:var(--muted); display:block; margin-bottom:0.3rem; }
        .btn-purple { background:linear-gradient(135deg,var(--purple),var(--primary)); color:white; border:none; border-radius:10px; padding:0.75rem 1.25rem; font-weight:600; cursor:pointer; font-size:0.875rem; width:100%; }
        .btn-danger  { background:rgba(220,38,38,0.2); color:#FCA5A5; border:1px solid rgba(220,38,38,0.3); border-radius:10px; padding:0.75rem 1.25rem; font-weight:600; cursor:pointer; font-size:0.875rem; width:100%; }
        .btn-success { background:rgba(5,150,105,0.2); color:#6EE7B7; border:1px solid rgba(5,150,105,0.3); border-radius:10px; padding:0.75rem 1.25rem; font-weight:600; cursor:pointer; font-size:0.875rem; width:100%; }
        .btn-ghost   { background:rgba(255,255,255,0.08); color:var(--muted); border:1px solid var(--border); border-radius:10px; padding:0.5rem 1rem; font-weight:600; cursor:pointer; font-size:0.875rem; text-decoration:none; }
        .alert-success { background:rgba(5,150,105,0.2); border:1px solid rgba(5,150,105,0.4); color:#6EE7B7; border-radius:10px; padding:0.875rem; margin-bottom:1rem; font-size:0.875rem; }
        .alert-error   { background:rgba(220,38,38,0.2);  border:1px solid rgba(220,38,38,0.4);  color:#FCA5A5; border-radius:10px; padding:0.875rem; margin-bottom:1rem; font-size:0.875rem; }
        .stat-mini { text-align:center; padding:0.875rem; background:rgba(255,255,255,0.03); border-radius:10px; }
        .stat-mini-val { font-size:1.5rem; font-weight:700; }
        .stat-mini-lbl { font-size:0.72rem; color:var(--muted); margin-top:2px; }
        .progress-wrap { background:rgba(255,255,255,0.08); border-radius:4px; height:8px; overflow:hidden; margin:6px 0; }
        .progress-fill { height:100%; border-radius:4px; }
        .badge-plan { font-size:0.72rem; font-weight:600; padding:3px 10px; border-radius:20px; }
        .plan-gratuit  { background:rgba(107,114,128,0.2); color:#9CA3AF; }
        .plan-standard { background:rgba(79,70,229,0.2);   color:#818CF8; }
        .plan-premium  { background:rgba(245,158,11,0.2);  color:#FCD34D; }
        .divider { border:none; border-top:1px solid var(--border); margin:1rem 0; }
    </style>
</head>
<body>
<div style="max-width:1000px;margin:0 auto">

    {{-- Header --}}
    <div style="display:flex;align-items:center;gap:1rem;margin-bottom:2rem">
        <a href="{{ route('superadmin.dashboard') }}" class="btn-ghost">
            <i class="bi bi-arrow-left me-1"></i> Retour
        </a>
        <h1 style="font-size:1.4rem;font-weight:700">{{ $tenant->name }}</h1>
        <code style="background:rgba(124,58,237,0.2);color:#C4B5FD;padding:4px 12px;border-radius:8px;font-size:0.85rem">
            {{ $tenant->id }}.devsecure.ci
        </code>
        <span class="badge-plan plan-{{ $tenant->plan }}">{{ ucfirst($tenant->plan) }}</span>
    </div>

    @if(session('success'))
    <div class="alert-success"><i class="bi bi-check-circle me-2"></i>{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="alert-error"><i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}</div>
    @endif

    {{-- Alertes --}}
    @foreach($alertes as $alerte)
    <div style="background:rgba(220,38,38,0.15);border:1px solid rgba(220,38,38,0.3);color:#FCA5A5;border-radius:10px;padding:0.875rem;margin-bottom:1rem;font-size:0.875rem">
        {{ $alerte }}
    </div>
    @endforeach

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem">

        {{-- Colonne gauche --}}
        <div>
            {{-- Stats --}}
            <div class="card-dark">
                <div class="card-title">Statistiques</div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;margin-bottom:1rem">
                    @foreach([
                        ['nb_eleves','Élèves','#4F46E5'],
                        ['nb_enseignants','Enseignants','#059669'],
                        ['nb_devoirs','Devoirs','#D97706'],
                        ['nb_tentatives','Passages','#7C3AED'],
                    ] as [$key,$lbl,$color])
                    <div class="stat-mini">
                        <div class="stat-mini-val" style="color:{{ $color }}">{{ $stats[$key] }}</div>
                        <div class="stat-mini-lbl">{{ $lbl }}</div>
                    </div>
                    @endforeach
                </div>

                {{-- Barre utilisation élèves --}}
                @php $pctEleves = min(100, round($stats['nb_eleves'] / max($tenant->max_eleves, 1) * 100)); @endphp
                <div style="font-size:0.78rem;color:var(--muted);margin-bottom:4px">
                    Utilisation élèves : {{ $stats['nb_eleves'] }}/{{ $tenant->max_eleves }}
                    ({{ $pctEleves }}%)
                </div>
                <div class="progress-wrap">
                    <div class="progress-fill" style="width:{{ $pctEleves }}%;background:{{ $pctEleves >= 90 ? '#DC2626' : ($pctEleves >= 70 ? '#D97706' : '#4F46E5') }}"></div>
                </div>
            </div>

            {{-- Modifier --}}
            <div class="card-dark">
                <div class="card-title">Modifier l'établissement</div>
                <form method="POST" action="{{ route('superadmin.tenants.update', $tenant->id) }}">
                    @csrf @method('PUT')
                    <label class="label">Nom</label>
                    <input type="text" name="name" class="form-control-dark" value="{{ $tenant->name }}" required>
                    <label class="label">Email contact</label>
                    <input type="email" name="email_contact" class="form-control-dark" value="{{ $tenant->email_contact }}" required>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem">
                        <div>
                            <label class="label">Plan</label>
                            <select name="plan" class="form-control-dark" required>
                                @foreach(['gratuit','standard','premium'] as $p)
                                <option value="{{ $p }}" {{ $tenant->plan === $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="label">Max élèves</label>
                            <input type="number" name="max_eleves" class="form-control-dark" value="{{ $tenant->max_eleves }}" min="10">
                        </div>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem">
                        <div>
                            <label class="label">Ville</label>
                            <input type="text" name="ville" class="form-control-dark" value="{{ $tenant->ville }}">
                        </div>
                        <div>
                            <label class="label">Statut</label>
                            <select name="actif" class="form-control-dark">
                                <option value="1" {{ $tenant->actif ? 'selected' : '' }}>Actif</option>
                                <option value="0" {{ !$tenant->actif ? 'selected' : '' }}>Inactif</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn-purple">
                        <i class="bi bi-check2 me-1"></i> Sauvegarder
                    </button>
                </form>
            </div>
        </div>

        {{-- Colonne droite --}}
        <div>
            {{-- Actions rapides --}}
            <div class="card-dark">
                <div class="card-title">Actions</div>
                <div style="display:flex;flex-direction:column;gap:0.75rem">

                    {{-- Toggle actif --}}
                    <form method="POST" action="{{ route('superadmin.tenants.toggle', $tenant->id) }}">
                        @csrf @method('PATCH')
                        <button type="submit" class="{{ $tenant->actif ? 'btn-danger' : 'btn-success' }}">
                            {{ $tenant->actif ? '⏸ Désactiver l\'établissement' : '▶ Activer l\'établissement' }}
                        </button>
                    </form>

                    {{-- Migrations --}}
                    <form method="POST" action="{{ route('superadmin.tenants.migrate', $tenant->id) }}">
                        @csrf
                        <button type="submit" style="width:100%;padding:0.75rem;background:rgba(79,70,229,0.2);color:#818CF8;border:1px solid rgba(79,70,229,0.3);border-radius:10px;font-weight:600;cursor:pointer;font-size:0.875rem">
                            <i class="bi bi-database me-1"></i> Relancer les migrations
                        </button>
                    </form>

                    {{-- Supprimer --}}
                    <form method="POST" action="{{ route('superadmin.tenants.destroy', $tenant->id) }}"
                          onsubmit="return confirm('⚠️ SUPPRIMER définitivement {{ $tenant->name }} et TOUTES ses données ?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-danger">
                            <i class="bi bi-trash me-1"></i> Supprimer définitivement
                        </button>
                    </form>
                </div>
            </div>

            {{-- Réinitialiser mot de passe admin --}}
            <div class="card-dark">
                <div class="card-title">Réinitialiser mot de passe admin</div>
                <form method="POST" action="{{ route('superadmin.tenants.reset-password', $tenant->id) }}">
                    @csrf
                    <label class="label">Email de l'admin</label>
                    <input type="email" name="admin_email" class="form-control-dark" required placeholder="admin@etablissement.ci">
                    <label class="label">Nouveau mot de passe</label>
                    <input type="text" name="nouveau_mdp" class="form-control-dark" required placeholder="Min. 8 caractères">
                    <button type="submit" style="width:100%;padding:0.75rem;background:rgba(124,58,237,0.2);color:#C4B5FD;border:1px solid rgba(124,58,237,0.3);border-radius:10px;font-weight:600;cursor:pointer;font-size:0.875rem">
                        <i class="bi bi-key me-1"></i> Réinitialiser
                    </button>
                </form>
            </div>

            {{-- Renvoyer email bienvenue --}}
            <div class="card-dark">
                <div class="card-title">Renvoyer email de bienvenue</div>
                <form method="POST" action="{{ route('superadmin.tenants.renvoyer-email', $tenant->id) }}">
                    @csrf
                    <label class="label">Email de l'admin</label>
                    <input type="email" name="admin_email" class="form-control-dark" required placeholder="admin@etablissement.ci">
                    <label class="label">Mot de passe à envoyer</label>
                    <input type="text" name="admin_password" class="form-control-dark" required placeholder="Mot de passe actuel ou nouveau">
                    <button type="submit" style="width:100%;padding:0.75rem;background:rgba(5,150,105,0.2);color:#6EE7B7;border:1px solid rgba(5,150,105,0.3);border-radius:10px;font-weight:600;cursor:pointer;font-size:0.875rem">
                        <i class="bi bi-envelope me-1"></i> Renvoyer l'email
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>