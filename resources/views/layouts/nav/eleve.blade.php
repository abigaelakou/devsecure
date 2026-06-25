<div class="nav-label">Principal</div>
<a href="{{ route('eleve.dashboard') }}" class="nav-item {{ request()->routeIs('eleve.dashboard') ? 'active' : '' }}">
    <i class="bi bi-grid-1x2"></i> Tableau de bord
</a>
<a href="{{ route('eleve.devoirs') }}" class="nav-item {{ request()->routeIs('eleve.devoirs') ? 'active' : '' }}">
    <i class="bi bi-journals"></i> Mes devoirs
</a>
<a href="{{ route('eleve.resultats') }}" class="nav-item {{ request()->routeIs('eleve.resultats') ? 'active' : '' }}">
    <i class="bi bi-trophy"></i> Mes résultats
</a>

<div class="nav-label">Compte</div>
<a href="{{ route('eleve.profil') }}" class="nav-item {{ request()->routeIs('eleve.profil') ? 'active' : '' }}">
    <i class="bi bi-person"></i> Mon profil
</a>