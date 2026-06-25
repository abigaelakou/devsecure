@extends('layouts.app')

@section('title', 'Administration')
@section('page-title', 'Tableau de bord')
@section('page-subtitle', 'Administration — ' . config('app.name'))

@section('content')

<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background:#EEF2FF;color:#4F46E5"><i class="bi bi-people-fill"></i></div>
        <div class="stat-value">{{ $nbEleves }}</div>
        <div class="stat-label">Élèves inscrits</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#D1FAE5;color:#059669"><i class="bi bi-person-badge"></i></div>
        <div class="stat-value">{{ $nbEnseignants }}</div>
        <div class="stat-label">Enseignants</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#FEF3C7;color:#D97706"><i class="bi bi-journals"></i></div>
        <div class="stat-value">{{ $nbDevoirs }}</div>
        <div class="stat-label">Devoirs créés</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#FEE2E2;color:#DC2626"><i class="bi bi-shield-exclamation"></i></div>
        <div class="stat-value">{{ $nbFraudes }}</div>
        <div class="stat-label">Fraudes détectées</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-bottom:1.5rem">
    <div class="stat-card" style="text-align:center">
        <div style="font-size:2rem;font-weight:700;color:#4F46E5">{{ $devoirsActifs }}</div>
        <div style="font-size:0.8rem;color:#6B7280;margin-top:0.25rem">Devoirs actifs en ce moment</div>
    </div>
    <div class="stat-card" style="text-align:center">
        <div style="font-size:2rem;font-weight:700;color:#059669">{{ $tentativesAujourdhui }}</div>
        <div style="font-size:0.8rem;color:#6B7280;margin-top:0.25rem">Passages aujourd'hui</div>
    </div>
    <div class="stat-card" style="text-align:center">
        <div style="font-size:2rem;font-weight:700;color:#D97706">{{ $nbEleves + $nbEnseignants }}</div>
        <div style="font-size:0.8rem;color:#6B7280;margin-top:0.25rem">Total utilisateurs</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:repeat(2,1fr);gap:1rem">
    <div class="card-section">
        <div class="card-header-row"><h2>Accès rapides</h2></div>
        <div style="padding:1.25rem;display:grid;grid-template-columns:1fr 1fr;gap:0.75rem">
            @foreach([
                ['route'=>'admin.utilisateurs','icon'=>'people','label'=>'Utilisateurs','color'=>'#4F46E5'],
                ['route'=>'admin.classes','icon'=>'diagram-3','label'=>'Classes','color'=>'#059669'],
                ['route'=>'admin.matieres','icon'=>'book','label'=>'Matières','color'=>'#D97706'],
                ['route'=>'admin.rapports','icon'=>'bar-chart','label'=>'Rapports','color'=>'#7C3AED'],
                ['route'=>'admin.antitriche','icon'=>'shield-exclamation','label'=>'Antitriche','color'=>'#DC2626'],
                ['route'=>'admin.annees-scolaires','icon'=>'calendar3','label'=>'Années scolaires','color'=>'#0891B2'],
            ] as $item)
            <a href="{{ route($item['route']) }}"
               style="display:flex;align-items:center;gap:0.75rem;padding:0.875rem;background:#F9FAFB;border-radius:10px;text-decoration:none;color:#111827;transition:background 0.15s"
               onmouseover="this.style.background='#EEF2FF'" onmouseout="this.style.background='#F9FAFB'">
                <div style="width:36px;height:36px;background:{{ $item['color'] }}20;border-radius:8px;display:flex;align-items:center;justify-content:center;color:{{ $item['color'] }}">
                    <i class="bi bi-{{ $item['icon'] }}"></i>
                </div>
                <span style="font-size:0.875rem;font-weight:500">{{ $item['label'] }}</span>
            </a>
            @endforeach
        </div>
    </div>

    <div class="card-section">
        <div class="card-header-row"><h2>Informations système</h2></div>
        <div style="padding:1.25rem">
            @foreach([
                ['label'=>'Version Laravel','value'=>app()->version()],
                ['label'=>'PHP','value'=>PHP_VERSION],
                ['label'=>'Environnement','value'=>app()->environment()],
                ['label'=>'Base de données','value'=>config('database.default')],
                ['label'=>'Cache','value'=>config('cache.default')],
            ] as $info)
            <div style="display:flex;justify-content:space-between;padding:0.5rem 0;border-bottom:1px solid #F3F4F6;font-size:0.875rem">
                <span style="color:#6B7280">{{ $info['label'] }}</span>
                <span style="font-weight:500">{{ $info['value'] }}</span>
            </div>
            @endforeach
        </div>
    </div>
</div>

@endsection