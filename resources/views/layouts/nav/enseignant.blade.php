<div class="nav-label">Principal</div>
<a href="{{ route('enseignant.dashboard') }}" class="nav-item {{ request()->routeIs('enseignant.dashboard') ? 'active' : '' }}">
    <i class="bi bi-grid-1x2"></i> Tableau de bord
</a>
<a href="{{ route('enseignant.devoirs.index') }}" class="nav-item {{ request()->routeIs('enseignant.devoirs.*') ? 'active' : '' }}">
    <i class="bi bi-journals"></i> Mes devoirs
</a>
<a href="{{ route('enseignant.classes') }}" class="nav-item {{ request()->routeIs('enseignant.classes') ? 'active' : '' }}">
    <i class="bi bi-people"></i> Mes classes
</a>
<a href="{{ route('enseignant.statistiques') }}" class="nav-item {{ request()->routeIs('enseignant.statistiques') ? 'active' : '' }}">
    <i class="bi bi-graph-up"></i> Statistiques
</a>

<div class="nav-label">Résultats</div>
<a href="{{ route('enseignant.corrections') }}" class="nav-item {{ request()->routeIs('enseignant.corrections') ? 'active' : '' }}">
    <i class="bi bi-check2-all"></i> Corrections
    @php $nbCorrections = \App\Models\Tenant\ReponseEleve::whereNull('est_correcte')->whereHas('question', fn($q) => $q->whereIn('type', ['reponse_courte','redactionnel']))->count(); @endphp
    @if($nbCorrections > 0)
        <span class="nav-badge">{{ $nbCorrections }}</span>
    @endif
</a>
<a href="{{ route('enseignant.antitriche') }}" class="nav-item {{ request()->routeIs('enseignant.antitriche') ? 'active' : '' }}">
    <i class="bi bi-shield-exclamation"></i> Antitriche
</a>

<div class="nav-label">Compte</div>
<a href="{{ route('enseignant.profil') }}" class="nav-item {{ request()->routeIs('enseignant.profil') ? 'active' : '' }}">
    <i class="bi bi-person"></i> Mon profil
</a>