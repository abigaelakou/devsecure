@extends('layouts.app')
@section('title', 'Résultats')
@section('page-title', $devoir->titre)
@section('page-subtitle', 'Résultats · ' . $devoir->classe?->nom)

@section('content')

{{-- Stats --}}
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

<div class="card-section">
    <div class="card-header-row">
        <h2>Classement</h2>
        <div style="display:flex;gap:0.5rem;font-size:0.8rem;color:#6B7280">
            Meilleure: <strong>{{ $stats['meilleure'] }}/{{ $devoir->note_sur }}</strong>
            · Moins bonne: <strong>{{ $stats['moins_bonne'] }}/{{ $devoir->note_sur }}</strong>
        </div>
    </div>
    <table style="width:100%;border-collapse:collapse">
        <thead><tr style="background:#F9FAFB">
            @foreach(['Rang','Élève','Note','%','Mention','Fraude','Durée','Détail'] as $th)
            <th style="padding:0.6rem 1.5rem;font-size:0.72rem;font-weight:600;text-transform:uppercase;color:#6B7280;text-align:left">{{ $th }}</th>
            @endforeach
        </tr></thead>
        <tbody>
            @forelse($resultats as $r)
            <tr style="border-top:1px solid #E5E7EB">
                <td style="padding:0.875rem 1.5rem">
                    <span style="font-weight:700;color:{{ $r->rang <= 3 ? '#D97706' : '#6B7280' }}">
                        @if($r->rang == 1) 🥇 @elseif($r->rang == 2) 🥈 @elseif($r->rang == 3) 🥉 @else {{ $r->rang }} @endif
                    </span>
                </td>
                <td style="padding:0.875rem 1.5rem">
                    <div style="font-size:0.875rem;font-weight:500">{{ $r->eleve?->nom_complet }}</div>
                    <div style="font-size:0.75rem;color:#6B7280">{{ $r->eleve?->matricule }}</div>
                </td>
                <td style="padding:0.875rem 1.5rem">
                    <strong>{{ $r->note_finale }}</strong><span style="color:#6B7280;font-size:0.8rem">/{{ $r->note_sur }}</span>
                </td>
                <td style="padding:0.875rem 1.5rem;font-size:0.875rem">{{ $r->pourcentage }}%</td>
                <td style="padding:0.875rem 1.5rem">
                    @php $c = $r->pourcentage >= 75 ? ['#D1FAE5','#065F46'] : ($r->pourcentage >= 50 ? ['#FEF3C7','#92400E'] : ['#FEE2E2','#991B1B']); @endphp
                    <span style="background:{{ $c[0] }};color:{{ $c[1] }};font-size:0.72rem;font-weight:600;padding:3px 10px;border-radius:20px">
                        {{ $r->mention }}
                    </span>
                </td>
                <td style="padding:0.875rem 1.5rem">
                    @if($r->fraude_detectee)
                        <span style="color:#DC2626;font-size:0.875rem" title="{{ $r->nb_evenements_antitriche }} événements">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                        </span>
                    @else
                        <span style="color:#059669"><i class="bi bi-check2"></i></span>
                    @endif
                </td>
                <td style="padding:0.875rem 1.5rem;font-size:0.8rem;color:#6B7280">
                    {{ $r->tentative ? round($r->tentative->duree_reelle_secondes / 60, 1) . ' min' : '—' }}
                </td>
                <td style="padding:0.875rem 1rem">
                    <a href="{{ route('enseignant.devoirs.resultats', $devoir->id) }}/{{ $r->eleve_id }}"
                       style="padding:4px 10px;border:1px solid #E5E7EB;border-radius:6px;font-size:0.75rem;text-decoration:none;color:#4F46E5">
                        Voir
                    </a>
                </td>
            </tr>
            @empty
            <tr><td colspan="8" style="padding:2rem;text-align:center;color:#6B7280">Aucun résultat pour l'instant.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection