@extends('layouts.app')

@section('title', 'Dashboard Enseignant')
@section('page-title', 'Tableau de bord')
@section('page-subtitle', 'Bienvenue, ' . auth()->user()->prenoms . ' ' . auth()->user()->nom)

@section('topbar-actions')
    <a href="{{ route('enseignant.devoirs.create') }}" class="btn-primary-custom">
        <i class="bi bi-plus-lg"></i> Nouveau devoir
    </a>
@endsection

@section('content')

{{-- Stats --}}
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background:#EEF2FF;color:#4F46E5"><i class="bi bi-journals"></i></div>
        <div class="stat-value">{{ $nbDevoirs }}</div>
        <div class="stat-label">Devoirs créés</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#D1FAE5;color:#059669"><i class="bi bi-people-fill"></i></div>
        <div class="stat-value">{{ $nbEleves }}</div>
        <div class="stat-label">Élèves ont composé</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#FEF3C7;color:#D97706"><i class="bi bi-star-fill"></i></div>
        <div class="stat-value">{{ $moyenneGenerale ?? '—' }}</div>
        <div class="stat-label">Moyenne générale /20</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#FEE2E2;color:#DC2626"><i class="bi bi-shield-exclamation"></i></div>
        <div class="stat-value">{{ $nbFraudes }}</div>
        <div class="stat-label">Fraudes détectées</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 380px;gap:1.5rem">

    {{-- Devoirs récents --}}
    <div>
        <div class="card-section">
            <div class="card-header-row">
                <h2>Devoirs récents</h2>
                <a href="{{ route('enseignant.devoirs.index') }}" style="font-size:0.8rem;color:#4F46E5;text-decoration:none">
                    Voir tous <i class="bi bi-arrow-right"></i>
                </a>
            </div>
            <table style="width:100%;border-collapse:collapse">
                <thead>
                    <tr style="background:#F9FAFB">
                        <th style="padding:0.6rem 1.5rem;font-size:0.72rem;font-weight:600;text-transform:uppercase;color:#6B7280;text-align:left">Devoir</th>
                        <th style="padding:0.6rem 1.5rem;font-size:0.72rem;font-weight:600;text-transform:uppercase;color:#6B7280;text-align:left">Classe</th>
                        <th style="padding:0.6rem 1.5rem;font-size:0.72rem;font-weight:600;text-transform:uppercase;color:#6B7280;text-align:left">Progression</th>
                        <th style="padding:0.6rem 1.5rem;font-size:0.72rem;font-weight:600;text-transform:uppercase;color:#6B7280;text-align:left">Moyenne</th>
                        <th style="padding:0.6rem 1.5rem;font-size:0.72rem;font-weight:600;text-transform:uppercase;color:#6B7280;text-align:left">Statut</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($devoirs as $devoir)
                    <tr style="border-top:1px solid #E5E7EB">
                        <td style="padding:0.875rem 1.5rem">
                            <strong style="font-size:0.85rem">{{ Str::limit($devoir->titre, 35) }}</strong>
                            <div style="font-size:0.75rem;color:#6B7280">{{ $devoir->questions_count }} questions · {{ $devoir->duree_totale_minutes ?? '—' }} min</div>
                        </td>
                        <td style="padding:0.875rem 1.5rem;font-size:0.875rem">{{ $devoir->classe?->nom }}</td>
                        <td style="padding:0.875rem 1.5rem">
                            @php $pct = $devoir->classe?->effectif > 0 ? round($devoir->resultats_count / $devoir->classe->effectif * 100) : 0; @endphp
                            <div style="width:80px;height:6px;background:#E5E7EB;border-radius:3px;overflow:hidden;display:inline-block;vertical-align:middle;margin-right:6px">
                                <div style="height:100%;background:#4F46E5;border-radius:3px;width:{{ $pct }}%"></div>
                            </div>
                            <span style="font-size:0.78rem">{{ $devoir->resultats_count }}/{{ $devoir->classe?->effectif ?? '?' }}</span>
                        </td>
                        <td style="padding:0.875rem 1.5rem;font-size:0.875rem">
                            @if($devoir->resultats_count > 0)
                                <strong>{{ number_format($devoir->resultats->avg('note_finale'), 1) }}</strong>
                                <span style="color:#6B7280">/20</span>
                            @else
                                <span style="color:#6B7280">—</span>
                            @endif
                        </td>
                        <td style="padding:0.875rem 1.5rem">
                            <span class="badge-statut badge-{{ $devoir->statut }}">
                                <i class="bi bi-circle-fill" style="font-size:0.5rem"></i>
                                {{ ucfirst($devoir->statut) }}
                            </span>
                        </td>
                        <td style="padding:0.875rem 1rem">
                            <div style="display:flex;gap:0.4rem">
                                <a href="{{ route('enseignant.correction.resultats', $devoir->id) }}"
                                   style="width:28px;height:28px;border:1px solid #E5E7EB;border-radius:7px;display:flex;align-items:center;justify-content:center;color:#6B7280;text-decoration:none;font-size:0.85rem"
                                   title="Résultats">
                                    <i class="bi bi-bar-chart"></i>
                                </a>
                                <a href="{{ route('enseignant.devoirs.edit', $devoir->id) }}"
                                   style="width:28px;height:28px;border:1px solid #E5E7EB;border-radius:7px;display:flex;align-items:center;justify-content:center;color:#6B7280;text-decoration:none;font-size:0.85rem"
                                   title="Modifier">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="padding:2rem;text-align:center;color:#6B7280">
                            <i class="bi bi-journals" style="font-size:2rem;display:block;margin-bottom:0.5rem"></i>
                            Aucun devoir créé. <a href="{{ route('enseignant.devoirs.create') }}">Créer le premier</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Sidebar droite --}}
    <div>
        {{-- Activité antitriche --}}
        <div class="card-section" style="margin-bottom:1rem">
            <div class="card-header-row">
                <h2><i class="bi bi-shield-exclamation me-1" style="color:#DC2626"></i> Antitriche — Live</h2>
                <span style="font-size:0.72rem;background:#D1FAE5;color:#065F46;padding:2px 8px;border-radius:10px">● En direct</span>
            </div>
            <div style="padding:0 1.25rem">
                @forelse($evenementsRecents as $evenement)
                <div style="display:flex;align-items:flex-start;gap:0.75rem;padding:0.875rem 0;border-bottom:1px solid #E5E7EB">
                    @php
                        $couleur = in_array($evenement->type, ['changement_onglet','quitter_navigateur','soumission_auto'])
                            ? ['bg'=>'#FEE2E2','c'=>'#DC2626']
                            : ['bg'=>'#FEF3C7','c'=>'#D97706'];
                    @endphp
                    <div style="width:32px;height:32px;border-radius:50%;background:{{ $couleur['bg'] }};color:{{ $couleur['c'] }};display:flex;align-items:center;justify-content:center;font-size:0.8rem;flex-shrink:0">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                    <div style="flex:1">
                        <div style="font-size:0.83rem;font-weight:600">{{ $evenement->eleve?->nom_complet }}</div>
                        <div style="font-size:0.76rem;color:#6B7280">{{ $evenement->label }}</div>
                    </div>
                    <div style="font-size:0.72rem;color:#6B7280;white-space:nowrap">
                        {{ $evenement->survenu_le->diffForHumans() }}
                    </div>
                </div>
                @empty
                <div style="padding:1.5rem;text-align:center;color:#6B7280;font-size:0.875rem">
                    <i class="bi bi-shield-check" style="font-size:1.5rem;display:block;margin-bottom:0.5rem;color:#059669"></i>
                    Aucun événement suspect
                </div>
                @endforelse
            </div>
        </div>

        {{-- Corrections en attente --}}
        @if($nbCorrections > 0)
        <div class="card-section">
            <div class="card-header-row">
                <h2>Corrections en attente</h2>
                <span style="background:#FEE2E2;color:#991B1B;font-size:0.72rem;padding:2px 10px;border-radius:10px;font-weight:600">
                    {{ $nbCorrections }} à corriger
                </span>
            </div>
            <div style="padding:0.75rem 1.25rem">
                <a href="{{ route('enseignant.corrections') }}"
                   style="display:block;text-align:center;padding:0.65rem;background:#EEF2FF;color:#4F46E5;border:1.5px solid #4F46E5;border-radius:10px;font-size:0.875rem;font-weight:600;text-decoration:none">
                    <i class="bi bi-pencil-square me-1"></i> Corriger maintenant
                </a>
            </div>
        </div>
        @endif
    </div>
</div>

@endsection