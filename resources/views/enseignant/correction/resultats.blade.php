@extends('layouts.app')
@section('title', 'Résultats — ' . $devoir->titre)
@section('page-title', $devoir->titre)
@section('page-subtitle', 'Résultats · ' . $devoir->classe?->nom . ' · ' . $devoir->matiere?->nom)

@section('topbar-actions')
    <a href="{{ route('enseignant.devoirs.index') }}"
       style="padding:0.5rem 1rem;background:#F3F4F6;color:#374151;border:1.5px solid #E5E7EB;border-radius:8px;font-size:0.875rem;font-weight:600;text-decoration:none">
        <i class="bi bi-arrow-left me-1"></i> Retour
    </a>
@endsection

@section('content')

{{-- Stats globales --}}
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background:#EEF2FF;color:#4F46E5"><i class="bi bi-people"></i></div>
        <div class="stat-value">{{ $stats['nb_eleves'] }}</div>
        <div class="stat-label">Élèves ayant composé</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#D1FAE5;color:#059669"><i class="bi bi-star"></i></div>
        <div class="stat-value">{{ $stats['moyenne'] }}</div>
        <div class="stat-label">Moyenne /{{ $devoir->note_sur }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#FEF3C7;color:#D97706"><i class="bi bi-graph-up"></i></div>
        <div class="stat-value">{{ $stats['taux_reussite'] }}%</div>
        <div class="stat-label">Taux de réussite</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#FEE2E2;color:#DC2626"><i class="bi bi-shield-exclamation"></i></div>
        <div class="stat-value">{{ $stats['fraudes'] }}</div>
        <div class="stat-label">Fraudes détectées</div>
    </div>
</div>

{{-- Alerte corrections en attente --}}
@if($stats['a_corriger'] > 0)
<div style="background:#FEF3C7;border:1.5px solid #FCD34D;border-radius:12px;padding:1rem 1.25rem;margin-bottom:1.5rem;display:flex;align-items:center;gap:0.75rem">
    <i class="bi bi-pencil-square" style="color:#D97706;font-size:1.2rem"></i>
    <div style="flex:1;font-size:0.875rem;color:#92400E">
        <strong>{{ $stats['a_corriger'] }} élève(s)</strong> ont des questions rédactionnelles en attente de correction.
    </div>
</div>
@endif

<div style="display:grid;grid-template-columns:1fr 300px;gap:1.5rem">

    {{-- Table des résultats --}}
    <div class="card-section">
        <div class="card-header-row">
            <h2>Classement</h2>
            <div style="font-size:0.8rem;color:#6B7280">
                Médiane : <strong>{{ $stats['mediane'] }}/{{ $devoir->note_sur }}</strong>
                · Meilleure : <strong>{{ $stats['meilleure'] }}</strong>
                · Moins bonne : <strong>{{ $stats['moins_bonne'] }}</strong>
            </div>
        </div>

        <table style="width:100%;border-collapse:collapse">
            <thead>
                <tr style="background:#F9FAFB">
                    <th style="padding:0.6rem 1rem;font-size:0.72rem;font-weight:600;text-transform:uppercase;color:#6B7280;text-align:left;width:50px">Rang</th>
                    <th style="padding:0.6rem 1rem;font-size:0.72rem;font-weight:600;text-transform:uppercase;color:#6B7280;text-align:left">Élève</th>
                    <th style="padding:0.6rem 1rem;font-size:0.72rem;font-weight:600;text-transform:uppercase;color:#6B7280;text-align:left">Note</th>
                    <th style="padding:0.6rem 1rem;font-size:0.72rem;font-weight:600;text-transform:uppercase;color:#6B7280;text-align:left">Mention</th>
                    <th style="padding:0.6rem 1rem;font-size:0.72rem;font-weight:600;text-transform:uppercase;color:#6B7280;text-align:left">Durée</th>
                    <th style="padding:0.6rem 1rem;font-size:0.72rem;font-weight:600;text-transform:uppercase;color:#6B7280;text-align:left">Statut</th>
                    <th style="padding:0.6rem 1rem"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($resultats as $r)
                <tr style="border-top:1px solid #E5E7EB;{{ $r['fraude_detectee'] ? 'background:#FFF7F7' : '' }}">
                    <td style="padding:0.875rem 1rem;font-weight:700;color:{{ $r['rang'] <= 3 ? '#D97706' : '#6B7280' }}">
                        @if($r['rang'] == 1) 🥇
                        @elseif($r['rang'] == 2) 🥈
                        @elseif($r['rang'] == 3) 🥉
                        @else {{ $r['rang'] }}
                        @endif
                    </td>
                    <td style="padding:0.875rem 1rem">
                        <div style="font-size:0.875rem;font-weight:500">{{ $r['eleve'] }}</div>
                        <div style="font-size:0.75rem;color:#6B7280">{{ $r['matricule'] ?? '' }}</div>
                    </td>
                    <td style="padding:0.875rem 1rem">
                        <strong style="font-size:1rem">{{ $r['note_finale'] }}</strong>
                        <span style="color:#6B7280;font-size:0.8rem">/{{ $r['note_sur'] }}</span>
                        <div style="font-size:0.72rem;color:#6B7280">{{ $r['pourcentage'] }}%</div>
                    </td>
                    <td style="padding:0.875rem 1rem">
                        @php $c = $r['pourcentage'] >= 75 ? ['#D1FAE5','#065F46'] : ($r['pourcentage'] >= 50 ? ['#FEF3C7','#92400E'] : ['#FEE2E2','#991B1B']); @endphp
                        <span style="background:{{ $c[0] }};color:{{ $c[1] }};font-size:0.72rem;font-weight:600;padding:3px 10px;border-radius:20px">
                            {{ $r['mention'] }}
                        </span>
                    </td>
                    <td style="padding:0.875rem 1rem;font-size:0.8rem;color:#6B7280">
                        {{ $r['duree_minutes'] ? $r['duree_minutes'] . ' min' : '—' }}
                    </td>
                    <td style="padding:0.875rem 1rem">
                        <div style="display:flex;gap:0.3rem;align-items:center">
                            @if($r['fraude_detectee'])
                            <span title="{{ $r['nb_evenements_antitriche'] }} événements"
                                  style="color:#DC2626;font-size:0.85rem">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                            </span>
                            @endif
                            @if($r['necessite_correction'])
                            <span style="background:#FEF3C7;color:#D97706;font-size:0.7rem;font-weight:600;padding:2px 7px;border-radius:10px">
                                À corriger
                            </span>
                            @endif
                        </div>
                    </td>
                    <td style="padding:0.875rem 1rem">
                        <a href="{{ route('enseignant.correction.detail', [$devoir->id, $r['eleve_id']]) }}"
                           style="padding:5px 12px;background:#EEF2FF;color:#4F46E5;border-radius:6px;text-decoration:none;font-size:0.78rem;font-weight:600">
                            {{ $r['necessite_correction'] ? '✏️ Corriger' : '👁 Voir' }}
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="padding:3rem;text-align:center;color:#6B7280">
                        <i class="bi bi-inbox" style="font-size:2rem;display:block;margin-bottom:0.5rem"></i>
                        Aucun résultat pour l'instant.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Distribution des notes --}}
    <div>
        <div class="card-section">
            <div class="card-header-row"><h2>Distribution</h2></div>
            <div style="padding:1.25rem">
                @php $maxCount = max(array_values($distribution) ?: [1]); @endphp
                @foreach($distribution as $tranche => $count)
                <div style="margin-bottom:0.75rem">
                    <div style="display:flex;justify-content:space-between;font-size:0.78rem;margin-bottom:0.3rem">
                        <span style="color:#374151;font-weight:500">{{ $tranche }}</span>
                        <span style="color:#6B7280">{{ $count }} élève(s)</span>
                    </div>
                    <div style="height:8px;background:#E5E7EB;border-radius:4px;overflow:hidden">
                        <div style="height:100%;background:#4F46E5;border-radius:4px;width:{{ $maxCount > 0 ? round($count / $maxCount * 100) : 0 }}%;transition:width 0.5s"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Résumé antitriche --}}
        @if($stats['fraudes'] > 0)
        <div class="card-section" style="margin-top:1rem">
            <div class="card-header-row">
                <h2><i class="bi bi-shield-exclamation me-1" style="color:#DC2626"></i>Antitriche</h2>
            </div>
            <div style="padding:1.25rem">
                <div style="text-align:center;padding:1rem;background:#FEF2F2;border-radius:10px;margin-bottom:0.75rem">
                    <div style="font-size:2rem;font-weight:700;color:#DC2626">{{ $stats['fraudes'] }}</div>
                    <div style="font-size:0.78rem;color:#991B1B">fraude(s) détectée(s)</div>
                </div>
                <a href="{{ route('enseignant.antitriche') }}"
                   style="display:block;text-align:center;padding:0.6rem;background:#FEE2E2;color:#DC2626;border-radius:8px;text-decoration:none;font-size:0.8rem;font-weight:600">
                    Voir le rapport antitriche
                </a>
            </div>
        </div>
        @endif
    </div>
</div>

@endsection