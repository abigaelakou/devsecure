<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'DevSecure') — {{ config('app.name') }}</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary:       #4F46E5;
            --primary-light: #EEF2FF;
            --primary-dark:  #3730A3;
            --success:       #059669;
            --danger:        #DC2626;
            --warning:       #D97706;
            --sidebar-bg:    #1E1B4B;
            --sidebar-text:  #C7D2FE;
            --bg:            #F3F4F6;
            --card:          #FFFFFF;
            --text:          #111827;
            --muted:         #6B7280;
            --border:        #E5E7EB;
        }

        * { box-sizing: border-box; }
        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
        }

        /* ── SIDEBAR ─────────────────────────────────── */
        .sidebar {
            width: 240px;
            background: var(--sidebar-bg);
            min-height: 100vh;
            position: fixed;
            top: 0; left: 0;
            display: flex;
            flex-direction: column;
            z-index: 100;
            transition: transform 0.3s;
        }
        .sidebar-logo {
            padding: 1.5rem 1.25rem 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            display: flex;
            align-items: center;
            gap: 0.6rem;
            text-decoration: none;
        }
        .logo-icon {
            width: 36px; height: 36px;
            background: var(--primary);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            color: white; font-size: 1rem; flex-shrink: 0;
        }
        .logo-text { font-size: 1.05rem; font-weight: 700; color: white; }
        .logo-sub  { font-size: 0.65rem; color: var(--sidebar-text); letter-spacing: 0.05em; text-transform: uppercase; }

        .sidebar-nav { padding: 1rem 0.75rem; flex: 1; overflow-y: auto; }
        .nav-label {
            font-size: 0.65rem; font-weight: 600;
            text-transform: uppercase; letter-spacing: 0.08em;
            color: rgba(199,210,254,0.4);
            padding: 0.75rem 0.5rem 0.3rem;
        }
        .nav-item {
            display: flex; align-items: center; gap: 0.7rem;
            padding: 0.6rem 0.75rem;
            border-radius: 8px;
            color: var(--sidebar-text);
            font-size: 0.875rem;
            text-decoration: none;
            margin-bottom: 2px;
            transition: all 0.15s;
        }
        .nav-item:hover  { background: rgba(255,255,255,0.06); color: white; }
        .nav-item.active { background: var(--primary); color: white; font-weight: 600; }
        .nav-item i      { font-size: 1rem; flex-shrink: 0; }
        .nav-badge {
            margin-left: auto;
            background: #DC2626; color: white;
            font-size: 0.65rem; font-weight: 700;
            padding: 2px 6px; border-radius: 10px;
        }

        .sidebar-user {
            padding: 1rem 1.25rem;
            border-top: 1px solid rgba(255,255,255,0.08);
            display: flex; align-items: center; gap: 0.75rem;
        }
        .user-avatar {
            width: 36px; height: 36px;
            background: var(--primary); border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.75rem; font-weight: 700; color: white; flex-shrink: 0;
        }
        .user-name { font-size: 0.82rem; font-weight: 600; color: white; }
        .user-role { font-size: 0.7rem; color: var(--sidebar-text); }

        /* ── CONTENU PRINCIPAL ───────────────────────── */
        .main-wrapper {
            margin-left: 240px;
            min-height: 100vh;
            display: flex; flex-direction: column;
        }
        .topbar {
            background: var(--card);
            border-bottom: 1px solid var(--border);
            padding: 0.875rem 2rem;
            display: flex; align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 50;
            box-shadow: 0 1px 4px rgba(0,0,0,0.05);
        }
        .topbar-title { font-size: 1.1rem; font-weight: 700; }
        .topbar-sub   { font-size: 0.78rem; color: var(--muted); margin-top: 1px; }

        .page-content { padding: 2rem; flex: 1; }

        /* ── CARDS ───────────────────────────────────── */
        .card-section {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 14px;
            overflow: hidden;
            margin-bottom: 1.5rem;
        }
        .card-header-row {
            display: flex; align-items: center; justify-content: space-between;
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border);
        }
        .card-header-row h2 { font-size: 0.95rem; font-weight: 700; margin: 0; }

        /* ── STAT CARDS ──────────────────────────────── */
        .stat-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 1.5rem; }
        .stat-card {
            background: var(--card); border: 1px solid var(--border);
            border-radius: 14px; padding: 1.25rem;
            transition: box-shadow 0.2s;
        }
        .stat-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.07); }
        .stat-icon {
            width: 42px; height: 42px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem; margin-bottom: 0.875rem;
        }
        .stat-value { font-size: 1.75rem; font-weight: 700; line-height: 1; }
        .stat-label { font-size: 0.78rem; color: var(--muted); margin-top: 0.3rem; }

        /* ── BADGES ──────────────────────────────────── */
        .badge-actif     { background: #D1FAE5; color: #065F46; }
        .badge-brouillon { background: #F3F4F6; color: var(--muted); }
        .badge-expire    { background: #FEE2E2; color: #991B1B; }
        .badge-statut {
            display: inline-flex; align-items: center; gap: 0.3rem;
            font-size: 0.72rem; font-weight: 600; padding: 3px 10px; border-radius: 20px;
        }

        /* ── BOUTONS ─────────────────────────────────── */
        .btn-primary-custom {
            background: var(--primary); color: white; border: none;
            border-radius: 10px; padding: 0.6rem 1.25rem;
            font-size: 0.875rem; font-weight: 600; cursor: pointer;
            display: inline-flex; align-items: center; gap: 0.4rem;
            text-decoration: none; transition: all 0.2s;
        }
        .btn-primary-custom:hover {
            background: var(--primary-dark); color: white;
            transform: translateY(-1px); box-shadow: 0 4px 12px rgba(79,70,229,0.3);
        }

        /* ── ALERTS ──────────────────────────────────── */
        .alert-success { background: #D1FAE5; border-color: #6EE7B7; color: #065F46; }
        .alert-danger  { background: #FEE2E2; border-color: #FCA5A5; color: #991B1B; }

        /* ── RESPONSIVE ──────────────────────────────── */
        @media (max-width: 992px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .main-wrapper { margin-left: 0; }
            .stat-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 576px) {
            .page-content { padding: 1rem; }
            .stat-grid { grid-template-columns: 1fr; }
        }

        /* ── PAGINATION ──────────────────────────────────────── */
        .pagination {
            display: flex;
            gap: 0.3rem;
            list-style: none;
            padding: 0;
            margin: 0;
            flex-wrap: wrap;
            align-items: center;
        }
        .pagination .page-item .page-link {
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 34px;
            height: 34px;
            padding: 0 0.6rem;
            border-radius: 8px;
            border: 1.5px solid var(--border);
            background: var(--card);
            color: var(--text);
            font-size: 0.8rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.15s;
        }
        .pagination .page-item .page-link:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: var(--primary-light);
        }
        .pagination .page-item.active .page-link {
            background: var(--primary);
            border-color: var(--primary);
            color: white;
        }
        .pagination .page-item.disabled .page-link {
            opacity: 0.4;
            cursor: not-allowed;
            pointer-events: none;
        }
            </style>

    @stack('styles')
</head>
<body>

<!-- ── SIDEBAR ─────────────────────────────────────────── -->
<aside class="sidebar" id="sidebar">
    <a href="{{ route(auth()->user()->role . '.dashboard') }}" class="sidebar-logo">
        <div class="logo-icon"><i class="bi bi-shield-check"></i></div>
        <div>
            <div class="logo-text">DevSecure</div>
            <div class="logo-sub">{{ config('app.name') }}</div>
        </div>
    </a>

    <nav class="sidebar-nav">
        @auth
            @if(auth()->user()->role === 'admin')
                @include('layouts.nav.admin')
            @elseif(auth()->user()->role === 'enseignant')
                @include('layouts.nav.enseignant')
            @else
                @include('layouts.nav.eleve')
            @endif
        @endauth
    </nav>

    <div class="sidebar-user">
        @auth
        <div class="user-avatar">
            {{ strtoupper(substr(auth()->user()->prenoms, 0, 1)) }}{{ strtoupper(substr(auth()->user()->nom, 0, 1)) }}
        </div>
        <div>
            <div class="user-name">{{ auth()->user()->prenoms }} {{ auth()->user()->nom }}</div>
            <div class="user-role">{{ ucfirst(auth()->user()->role) }}</div>
        </div>
        @endauth
    </div>
</aside>

<!-- ── CONTENU ─────────────────────────────────────────── -->
<div class="main-wrapper">

    <!-- Topbar -->
    <div class="topbar">
        <div>
            <div class="topbar-title">@yield('page-title', 'Tableau de bord')</div>
            <div class="topbar-sub">@yield('page-subtitle', now()->isoFormat('dddd D MMMM YYYY'))</div>
        </div>
        <div class="d-flex align-items-center gap-3">
            @yield('topbar-actions')
            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-box-arrow-right"></i>
                </button>
            </form>
        </div>
    </div>

    <!-- Flash messages -->
    <div class="px-4 pt-3">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
    </div>

    <!-- Page Content -->
    <div class="page-content">
        @yield('content')
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Toggle sidebar mobile
    document.getElementById('sidebarToggle')?.addEventListener('click', () => {
        document.getElementById('sidebar').classList.toggle('open');
    });

    // Marquer le nav actif
    document.querySelectorAll('.nav-item').forEach(item => {
        if (item.href === window.location.href) {
            item.classList.add('active');
        }
    });
</script>
@stack('scripts')
</body>
</html>