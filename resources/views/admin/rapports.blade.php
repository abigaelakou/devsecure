@extends('layouts.app')
@section('title', 'Rapports')
@section('page-title', 'Rapports globaux')
@section('content')

<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background:#EEF2FF;color:#4F46E5"><i class="bi bi-people"></i></div>
        <div class="stat-value">{{ $nbEleves }}</div>
        <div class="stat-label">Élèves inscrits</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#D1FAE5;color:#059669"><i class="bi bi-check2-all"></i></div>
        <div class="stat-value">{{ $nbTentatives }}</div>
        <div class="stat-label">Devoirs passés</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#FEF3C7;color:#D97706"><i class="bi bi-star"></i></div>
        <div class="stat-value">{{ $moyenne ?? '—' }}</div>
        <div class="stat-label">Moyenne générale /20</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#FEE2E2;color:#DC2626"><i class="bi bi-shield-exclamation"></i></div>
        <div class="stat-value">{{ $fraudes }}</div>
        <div class="stat-label">Fraudes détectées</div>
    </div>
</div>

<div class="card-section">
    <div class="card-header-row">
        <h2>Résumé de l'établissement</h2>
        <a href="{{ route('admin.rapports') }}?export=csv"
           style="font-size:0.8rem;color:#4F46E5;text-decoration:none">
            <i class="bi bi-download me-1"></i> Exporter CSV
        </a>
    </div>
    <div style="padding:1.5rem;display:grid;grid-template-columns:repeat(3,1fr);gap:1.5rem">
        @foreach([
            ['label'=>'Taux de réussite','value'=>$tauxReussite.'%','color'=>'#059669'],
            ['label'=>'Enseignants actifs','value'=>$nbEnseignants,'color'=>'#4F46E5'],
            ['label'=>'Devoirs publiés','value'=>$nbDevoirs,'color'=>'#D97706'],
        ] as $stat)
        <div style="text-align:center;padding:1.5rem;background:#F9FAFB;border-radius:12px">
            <div style="font-size:2.5rem;font-weight:700;color:{{ $stat['color'] }}">{{ $stat['value'] }}</div>
            <div style="font-size:0.875rem;color:#6B7280;margin-top:0.25rem">{{ $stat['label'] }}</div>
        </div>
        @endforeach
    </div>
</div>
@endsection