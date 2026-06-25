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
        :root {
            --purple: #7C3AED; --primary: #4F46E5;
            --bg: #0F0A1E; --card: #1E1744; --border: rgba(255,255,255,0.08);
            --text: #F8FAFC; --muted: rgba(255,255,255,0.5);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', system-ui, sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; padding: 2rem; }
        .card { background: var(--card); border: 1px solid var(--border); border-radius: 14px; padding: 1.5rem; margin-bottom: 1.5rem; }
        .form-control-dark {
            width: 100%; padding: 0.75rem; background: rgba(255,255,255,0.05);
            border: 1.5px solid var(--border); border-radius: 10px;
            font-size: 0.875rem; color: white;
        }
        .form-control-dark:focus { outline: none; border-color: var(--purple); }
        .form-control-dark::placeholder { color: var(--muted); }
        select.form-control-dark option { background: #1E1744; }
        .btn-purple { background: linear-gradient(135deg,var(--purple),var(--primary)); color:white; border:none; border-radius:10px; padding:0.75rem 1.5rem; font-weight:600; cursor:pointer; }
        .label { font-size:0.8rem; color:var(--muted); display:block; margin-bottom:0.4rem; }
    </style>
</head>
<body>

<div style="max-width:900px;margin:0 auto">
    <div style="display:flex;align-items:center;gap:1rem;margin-bottom:2rem">
        <a href="{{ route('superadmin.dashboard') }}"
           style="color:var(--muted);text-decoration:none;font-size:0.875rem">
            <i class="bi bi-arrow-left me-1"></i> Retour
        </a>
        <h1 style="font-size:1.4rem;font-weight:700">{{ $tenant->name }}</h1>
        <code style="background:rgba(124,58,237,0.2);color:#C4B5FD;padding:4px 12px;border-radius:8px;font-size:0.85rem">
            {{ $tenant->id }}.devsecure.ci
        </code>
    </div>

    @if(session('success'))
    <div style="background:rgba(5,150,105,0.2);border:1px solid rgba(5,150,105,0.4);color:#6EE7B7;border-radius:10px;padding:0.875rem;margin-bottom:1.5rem;font-size:0.875rem">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
    </div>
    @endif

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem">

        {{-- Stats --}}
        <div class="card">
            <h2 style="font-size:0.9rem;font-weight:700;margin-bottom:1.25rem;color:var(--muted)">STATISTIQUES</h2>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
                @foreach([
                    ['Élèves',$stats['nb_eleves'] ?? '—','#4F46E5'],
                    ['Enseignants',$stats['nb_enseignants'] ?? '—','#059669'],
                    ['Devoirs',$stats['nb_devoirs'] ?? '—','#D97706'],
                    ['Passages',$stats['nb_tentatives'] ?? '—','#7C3AED'],
                ] as [$label,$val,$color])
                <div style="text-align:center;padding:1rem;background:rgba(255,255,255,0.03);border-radius:10px">
                    <div style="font-size:1.75rem;font-weight:700;color:{{ $color }}">{{ $val }}</div>
                    <div style="font-size:0.78rem;color:var(--muted)">{{ $label }}</div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Infos + actions rapides --}}
        <div class="card">
            <h2 style="font-size:0.9rem;font-weight:700;margin-bottom:1.25rem;color:var(--muted)">ACTIONS</h2>
            <div style="display:flex;flex-direction:column;gap:0.75rem">
                {{-- Toggle actif --}}
                <form method="POST" action="{{ route('superadmin.tenants.toggle', $tenant->id) }}">
                    @csrf @method('PATCH')
                    <button type="submit" style="width:100%;padding:0.75rem;background:{{ $tenant->actif ? 'rgba(220,38,38,0.2)' : 'rgba(5,150,105,0.2)' }};color:{{ $tenant->actif ? '#FCA5A5' : '#6EE7B7' }};border:1px solid {{ $tenant->actif ? 'rgba(220,38,38,0.3)' : 'rgba(5,150,105,0.3)' }};border-radius:10px;font-weight:600;cursor:pointer;font-size:0.875rem">
                        {{ $tenant->actif ? '⏸ Désactiver l\'établissement' : '▶ Activer l\'établissement' }}
                    </button>
                </form>
                {{-- Relancer migrations --}}
                <form method="POST" action="{{ route('superadmin.tenants.migrate', $tenant->id) }}">
                    @csrf
                    <button type="submit" style="width:100%;padding:0.75rem;background:rgba(79,70,229,0.2);color:#818CF8;border:1px solid rgba(79,70,229,0.3);border-radius:10px;font-weight:600;cursor:pointer;font-size:0.875rem">
                        <i class="bi bi-database me-1"></i> Relancer les migrations
                    </button>
                </form>
                {{-- Supprimer --}}
                <form method="POST" action="{{ route('superadmin.tenants.destroy', $tenant->id) }}"
                      onsubmit="return confirm('⚠️ ATTENTION : Supprimer définitivement {{ $tenant->name }} et toutes ses données ?')">
                    @csrf @method('DELETE')
                    <button type="submit" style="width:100%;padding:0.75rem;background:rgba(220,38,38,0.1);color:#FCA5A5;border:1px solid rgba(220,38,38,0.2);border-radius:10px;font-weight:600;cursor:pointer;font-size:0.875rem">
                        <i class="bi bi-trash me-1"></i> Supprimer définitivement
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Modifier --}}
    <div class="card">
        <h2 style="font-size:0.9rem;font-weight:700;margin-bottom:1.25rem;color:var(--muted)">MODIFIER L'ÉTABLISSEMENT</h2>
        <form method="POST" action="{{ route('superadmin.tenants.update', $tenant->id) }}">
            @csrf @method('PUT')
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem">
                <div>
                    <label class="label">Nom</label>
                    <input type="text" name="name" class="form-control-dark" value="{{ $tenant->name }}" required>
                </div>
                <div>
                    <label class="label">Email contact</label>
                    <input type="email" name="email_contact" class="form-control-dark" value="{{ $tenant->email_contact }}" required>
                </div>
                <div>
                    <label class="label">Plan</label>
                    <select name="plan" class="form-control-dark">
                        @foreach(['gratuit','standard','premium'] as $p)
                        <option value="{{ $p }}" {{ $tenant->plan === $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label">Max élèves</label>
                    <input type="number" name="max_eleves" class="form-control-dark" value="{{ $tenant->max_eleves }}" min="10">
                </div>
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
                <i class="bi bi-check2 me-1"></i> Sauvegarder les modifications
            </button>
        </form>
    </div>
</div>

</body>
</html>