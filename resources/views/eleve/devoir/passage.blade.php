@extends('layouts.app')

@section('title', 'Tableau de bord')
@section('page-title', 'Tableau de bord')
@section('page-subtitle', 'Bienvenue, ' . auth()->user()->prenoms . ' !')

@section('content')

{{-- Stats rapides --}}
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background:#EEF2FF;color:#4F46E5">
            <i class="bi bi-journals"></i>
        </div>
        <div class="stat-value">{{ $devoirsDisponibles }}</div>
        <div class="stat-label">Devoirs disponibles</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#D1FAE5;color:#059669">
            <i class="bi bi-check2-circle"></i>
        </div>
        <div class="stat-value">{{ $devoirsTermines }}</div>
        <div class="stat-label">Devoirs terminés</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#FEF3C7;color:#D97706">
            <i class="bi bi-star-fill"></i>
        </div>
        <div class="stat-value">{{ $moyenneGenerale ?? '—' }}</div>
        <div class="stat-label">Moyenne générale /20</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#FEE2E2;color:#DC2626">
            <i class="bi bi-clock-history"></i>
        </div>
        <div class="stat-value">{{ $devoirsEnCours }}</div>
        <div class="stat-label">En cours</div>
    </div>
</div>

{{-- Devoirs disponibles --}}
<div class="card-section">
    <div class="card-header-row">
        <h2><i class="bi bi-journals me-2" style="color:#4F46E5"></i>Devoirs disponibles</h2>
        <a href="{{ route('eleve.devoirs') }}" style="font-size:0.8rem;color:#4F46E5;text-decoration:none">
            Voir tous <i class="bi bi-arrow-right"></i>
        </a>
    </div>

    @if($devoirs->isEmpty())
        <div style="padding:2rem;text-align:center;color:#6B7280">
            <i class="bi bi-inbox" style="font-size:2rem;display:block;margin-bottom:0.5rem"></i>
            Aucun devoir disponible pour le moment.
        </div>
    @else
        <div style="padding:1rem 1.5rem;display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:1rem">
            @foreach($devoirs as $devoir)
                <div style="border:1px solid #E5E7EB;border-radius:12px;padding:1.25rem;transition:box-shadow 0.2s"
                     onmouseover="this.style.boxShadow='0 4px 12px rgba(0,0,0,0.08)'"
                     onmouseout="this.style.boxShadow='none'">

                    <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.75rem">
                        <span style="background:{{ $devoir->matiere?->couleur ?? '#4F46E5' }}20;color:{{ $devoir->matiere?->couleur ?? '#4F46E5' }};font-size:0.72rem;font-weight:600;padding:3px 10px;border-radius:20px;text-transform:uppercase">
                            {{ $devoir->matiere?->nom ?? 'Matière' }}
                        </span>
                        @if($devoir->expire_le && $devoir->expire_le->diffInDays() <= 2)
                            <span style="background:#FEF3C7;color:#D97706;font-size:0.72rem;font-weight:600;padding:3px 8px;border-radius:20px">
                                <i class="bi bi-clock"></i> Expire bientôt
                            </span>
                        @endif
                    </div>

                    <h3 style="font-size:0.95rem;font-weight:600;margin-bottom:0.5rem">{{ $devoir->titre }}</h3>

                    <div style="font-size:0.8rem;color:#6B7280;display:flex;gap:1rem;margin-bottom:1rem">
                        <span><i class="bi bi-question-circle me-1"></i>{{ $devoir->questions_count }} questions</span>
                        @if($devoir->duree_totale_minutes)
                            <span><i class="bi bi-clock me-1"></i>{{ $devoir->duree_totale_minutes }} min</span>
                        @endif
                        <span><i class="bi bi-person me-1"></i>{{ $devoir->enseignant?->nom }}</span>
                    </div>

                    @if($devoir->expire_le)
                        <div style="font-size:0.75rem;color:#6B7280;margin-bottom:0.875rem">
                            <i class="bi bi-calendar3 me-1"></i>
                            Expire le {{ $devoir->expire_le->format('d/m/Y à H:i') }}
                        </div>
                    @endif

                    <a href="{{ route('eleve.devoir.show', $devoir->id) }}"
                       style="display:block;text-align:center;background:#4F46E5;color:white;padding:0.6rem;border-radius:8px;text-decoration:none;font-size:0.875rem;font-weight:600;transition:background 0.2s"
                       onmouseover="this.style.background='#3730A3'"
                       onmouseout="this.style.background='#4F46E5'">
                        <i class="bi bi-play-circle me-1"></i>Commencer
                    </a>
                </div>
            @endforeach
        </div>
    @endif
</div>

{{-- Résultats récents --}}
@if($resultats->isNotEmpty())
<div class="card-section">
    <div class="card-header-row">
        <h2><i class="bi bi-trophy me-2" style="color:#D97706"></i>Mes derniers résultats</h2>
    </div>
    <table style="width:100%;border-collapse:collapse">
        <thead>
            <tr style="background:#F9FAFB">
                <th style="padding:0.6rem 1.5rem;font-size:0.72rem;font-weight:600;text-transform:uppercase;color:#6B7280;text-align:left">Devoir</th>
                <th style="padding:0.6rem 1.5rem;font-size:0.72rem;font-weight:600;text-transform:uppercase;color:#6B7280;text-align:left">Note</th>
                <th style="padding:0.6rem 1.5rem;font-size:0.72rem;font-weight:600;text-transform:uppercase;color:#6B7280;text-align:left">Mention</th>
                <th style="padding:0.6rem 1.5rem;font-size:0.72rem;font-weight:600;text-transform:uppercase;color:#6B7280;text-align:left">Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($resultats as $resultat)
            <tr style="border-top:1px solid #E5E7EB">
                <td style="padding:0.875rem 1.5rem;font-size:0.875rem;font-weight:500">
                    {{ $resultat->devoir?->titre }}
                    <div style="font-size:0.75rem;color:#6B7280">{{ $resultat->devoir?->matiere?->nom }}</div>
                </td>
                <td style="padding:0.875rem 1.5rem">
                    <strong style="font-size:1rem">{{ $resultat->note_finale }}</strong>
                    <span style="color:#6B7280;font-size:0.8rem">/{{ $resultat->note_sur }}</span>
                    <div style="font-size:0.72rem;color:#6B7280">{{ $resultat->pourcentage }}%</div>
                </td>
                <td style="padding:0.875rem 1.5rem">
                    @php
                        $couleur = match(true) {
                            $resultat->pourcentage >= 75 => ['bg'=>'#D1FAE5','color'=>'#065F46'],
                            $resultat->pourcentage >= 50 => ['bg'=>'#FEF3C7','color'=>'#92400E'],
                            default                      => ['bg'=>'#FEE2E2','color'=>'#991B1B'],
                        };
                    @endphp
                    <span style="background:{{ $couleur['bg'] }};color:{{ $couleur['color'] }};font-size:0.72rem;font-weight:600;padding:3px 10px;border-radius:20px">
                        {{ $resultat->mention }}
                    </span>
                </td>
                <td style="padding:0.875rem 1.5rem;font-size:0.8rem;color:#6B7280">
                    {{ $resultat->created_at->format('d/m/Y') }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

@endsection