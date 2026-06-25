<div class="nav-label">Administration</div>
<a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
    <i class="bi bi-grid-1x2"></i> Tableau de bord
</a>
<a href="{{ route('admin.utilisateurs') }}" class="nav-item {{ request()->routeIs('admin.utilisateurs*') ? 'active' : '' }}">
    <i class="bi bi-people"></i> Utilisateurs
</a>
<a href="{{ route('admin.classes') }}" class="nav-item {{ request()->routeIs('admin.classes*') ? 'active' : '' }}">
    <i class="bi bi-diagram-3"></i> Classes
</a>
<a href="{{ route('admin.matieres') }}" class="nav-item {{ request()->routeIs('admin.matieres*') ? 'active' : '' }}">
    <i class="bi bi-book"></i> Matières
</a>

<div class="nav-label">Rapports</div>
<a href="{{ route('admin.rapports') }}" class="nav-item {{ request()->routeIs('admin.rapports*') ? 'active' : '' }}">
    <i class="bi bi-bar-chart"></i> Rapports globaux
</a>
<a href="{{ route('admin.antitriche') }}" class="nav-item {{ request()->routeIs('admin.antitriche*') ? 'active' : '' }}">
    <i class="bi bi-shield-exclamation"></i> Antitriche
</a>

<div class="nav-label">Paramètres</div>
<a href="{{ route('admin.annees-scolaires') }}" class="nav-item {{ request()->routeIs('admin.annees-scolaires*') ? 'active' : '' }}">
    <i class="bi bi-calendar3"></i> Années scolaires
</a>