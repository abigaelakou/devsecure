@extends('layouts.app')
@section('title', 'Tableau de bord')
@section('page-title', 'Tableau de bord')
@section('page-subtitle', 'Bienvenue, ' . auth()->user()->prenoms . ' !')

@section('content')

{{-- DEVOIR EN COURS — alerte urgente --}}
@if($devoirEnCours)
<div style="background:linear-gradient(135deg,#1E1B4B,#4F46E5);border-radius:14px;padding:1.25rem 1.5rem;margin-bottom:1.5rem;display:flex;align-items:center;gap:1rem">
    <div style="width:44px;height:44px;background:rgba(255,255,255,0.2);border-radius:12px;display:flex;align-items:center;justify-content:center;color:white;font-size:1.2rem;flex-shrink:0;animation:pulse 2s infinite">
        <i class="bi bi-play-circle-fill"></i>
    </div>
    <div style="flex:1">
        <div style="font-size:0.875rem;font-weight:700;color:white">Devoir en cours !</div>
        <div style="font-size:0.8rem;color:rgba(255,255,255,0.7)">{{ $devoirEnCours->devoir?->titre }}</div>
    </div>
    <a href="{{ route('eleve.passage.question', [$devoirEnCours->id, $devoirEnCours->question_courante]) }}"
       style="padding:0.6rem 1.25rem;background:white;color:#4F46E5;border-radius:8px;text-decoration:none;font-size:0.875rem;font-weight:700;flex-shrink:0">
        Reprendre <i class="bi bi-arrow-right ms-1"></i>
    </a>
</div>
@endif

{{-- STATS RAPIDES --}}
<div class="stat-grid" style="margin-bottom:1.5rem">
    <div class="stat-card">
        <div class="stat-icon" style="background:#EEF2FF;color:#4F46E5"><i class="bi bi-journals"></i></div>
        <div class="stat-value">{{ $devoirsDisponibles }}</div>
        <div class="stat-label">Devoirs disponibles</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#D1FAE5;color:#059669"><i class="bi bi-check2-circle"></i></div>
        <div class="stat-value">{{ $devoirsTermines }}</div>
        <div class="stat-label">Devoirs terminés</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#FEF3C7;color:#D97706"><i class="bi bi-star-fill"></i></div>
        <div class="stat-value">{{ $moyenneGenerale ?? '—' }}</div>
        <div class="stat-label">Moyenne générale /20</div>
    </div>
    <div class="stat-card">
        @if($meilleurResultat)
        <div class="stat-icon" style="background:#D1FAE5;color:#059669"><i class="bi bi-trophy-fill"></i></div>
        <div class="stat-value" style="color:#059669">{{ round(($meilleurResultat->note_finale / $meilleurResultat->note_sur) * 20, 1) }}</div>
        <div class="stat-label">Meilleure note /20</div>
        @else
        <div class="stat-icon" style="background:#F3F4F6;color:#6B7280"><i class="bi bi-trophy"></i></div>
        <div class="stat-value">—</div>
        <div class="stat-label">Meilleure note</div>
        @endif
    </div>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem;margin-bottom:1.5rem">

    {{-- GRAPHIQUE ÉVOLUTION --}}
    <div class="card-section">
        <div class="card-header-row">
            <h2><i class="bi bi-graph-up me-2" style="color:#4F46E5"></i>Évolution de mes notes</h2>
            <span style="font-size:0.78rem;color:#6B7280">{{ $evolution->count() }} dernier(s) devoir(s)</span>
        </div>
        <div style="padding:1.25rem">
            @if($evolution->isEmpty())
            <div style="text-align:center;padding:2rem;color:#6B7280">
                <i class="bi bi-graph-up" style="font-size:2rem;display:block;margin-bottom:0.5rem"></i>
                Aucune donnée disponible. Commencez vos devoirs !
            </div>
            @else
            {{-- Graphique à barres custom --}}
            <div style="display:flex;align-items:flex-end;gap:8px;height:140px;margin-bottom:0.75rem">
                @foreach($evolution as $item)
                @php $hauteur = max(8, round($item['note'] / 20 * 100)); @endphp
                <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:4px">
                    <div style="font-size:0.65rem;font-weight:700;color:{{ $item['note'] >= 10 ? '#059669' : '#DC2626' }}">
                        {{ $item['note'] }}
                    </div>
                    <div style="width:100%;border-radius:6px 6px 0 0;background:{{ $item['couleur'] }};height:{{ $hauteur }}%;min-height:8px;transition:height 0.5s;position:relative;cursor:pointer"
                         title="{{ $item['label'] }} — {{ $item['note_brute'] }}/{{ $item['note_sur'] }} ({{ $item['date'] }})">
                    </div>
                    <div style="font-size:0.6rem;color:#6B7280;text-align:center;width:100%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"
                         title="{{ $item['label'] }}">
                        {{ $item['date'] }}
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Ligne de référence 10/20 --}}
            <div style="display:flex;align-items:center;gap:0.5rem;font-size:0.75rem;color:#6B7280;border-top:1px dashed #E5E7EB;padding-top:0.75rem">
                <div style="width:16px;height:2px;background:#E5E7EB"></div>
                <span>Seuil de réussite : 10/20</span>
                @if($moyenneGenerale)
                <div style="margin-left:auto;background:#EEF2FF;color:#4F46E5;padding:2px 10px;border-radius:10px;font-weight:600">
                    Moy. : {{ $moyenneGenerale }}/20
                </div>
                @endif
            </div>
            @endif
        </div>
    </div>

    {{-- PROGRESSION PAR MATIÈRE --}}
    <div class="card-section">
        <div class="card-header-row"><h2>Par matière</h2></div>
        <div style="padding:0.75rem 1.25rem">
            @if($progressionMatieres->isEmpty())
            <div style="text-align:center;padding:1.5rem;color:#6B7280;font-size:0.875rem">
                Aucune donnée.
            </div>
            @else
            @foreach($progressionMatieres as $m)
            <div style="padding:0.75rem 0;border-bottom:1px solid #F3F4F6">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.4rem">
                    <div style="display:flex;align-items:center;gap:0.5rem">
                        <div style="width:8px;height:8px;border-radius:50%;background:{{ $m['couleur'] }}"></div>
                        <span style="font-size:0.8rem;font-weight:500">{{ $m['matiere'] }}</span>
                    </div>
                    <span style="font-size:0.85rem;font-weight:700;color:{{ $m['moyenne'] >= 10 ? '#059669' : '#DC2626' }}">
                        {{ $m['moyenne'] }}/20
                    </span>
                </div>
                <div style="height:6px;background:#E5E7EB;border-radius:3px;overflow:hidden">
                    <div style="height:100%;background:{{ $m['couleur'] }};border-radius:3px;width:{{ min(100, round($m['moyenne'] / 20 * 100)) }}%;transition:width 0.5s"></div>
                </div>
                <div style="font-size:0.7rem;color:#6B7280;margin-top:3px">{{ $m['nb_devoirs'] }} devoir(s)</div>
            </div>
            @endforeach
            @endif
        </div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-bottom:1.5rem">

    {{-- DEVOIRS DISPONIBLES --}}
    <div class="card-section">
        <div class="card-header-row">
            <h2><i class="bi bi-journals me-2" style="color:#4F46E5"></i>Devoirs disponibles</h2>
            <a href="{{ route('eleve.devoirs') }}" style="font-size:0.8rem;color:#4F46E5;text-decoration:none">
                Voir tous <i class="bi bi-arrow-right"></i>
            </a>
        </div>

        @if($devoirs->isEmpty())
        <div style="padding:2rem;text-align:center;color:#6B7280">
            <i class="bi bi-inbox" style="font-size:1.75rem;display:block;margin-bottom:0.5rem"></i>
            Aucun devoir disponible.
        </div>
        @else
        <div style="padding:0.75rem 1.25rem">
            @foreach($devoirs as $devoir)
            <div style="padding:0.875rem 0;border-bottom:1px solid #F3F4F6;display:flex;align-items:center;gap:0.875rem">
                <div style="width:40px;height:40px;background:{{ $devoir->matiere?->couleur ?? '#4F46E5' }}20;border-radius:10px;display:flex;align-items:center;justify-content:center;color:{{ $devoir->matiere?->couleur ?? '#4F46E5' }};font-size:1rem;flex-shrink:0">
                    <i class="bi bi-file-earmark-text"></i>
                </div>
                <div style="flex:1;min-width:0">
                    <div style="font-size:0.875rem;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                        {{ $devoir->titre }}
                    </div>
                    <div style="font-size:0.75rem;color:#6B7280">
                        {{ $devoir->questions_count }} questions
                        @if($devoir->duree_totale_minutes) · {{ $devoir->duree_totale_minutes }} min @endif
                        @if($devoir->expire_le) · expire {{ $devoir->expire_le->diffForHumans() }} @endif
                    </div>
                </div>
                <a href="{{ route('eleve.devoir.show', $devoir->id) }}"
                   style="padding:5px 12px;background:#EEF2FF;color:#4F46E5;border-radius:8px;text-decoration:none;font-size:0.78rem;font-weight:600;flex-shrink:0">
                    Commencer
                </a>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- DERNIERS RÉSULTATS --}}
    <div class="card-section">
        <div class="card-header-row">
            <h2><i class="bi bi-trophy me-2" style="color:#D97706"></i>Mes derniers résultats</h2>
            <a href="{{ route('eleve.resultats') }}" style="font-size:0.8rem;color:#4F46E5;text-decoration:none">
                Voir tous <i class="bi bi-arrow-right"></i>
            </a>
        </div>

        @if($resultats->isEmpty())
        <div style="padding:2rem;text-align:center;color:#6B7280">
            <i class="bi bi-trophy" style="font-size:1.75rem;display:block;margin-bottom:0.5rem"></i>
            Aucun résultat pour l'instant.
        </div>
        @else
        <div style="padding:0.75rem 1.25rem">
            @foreach($resultats as $r)
            <div style="padding:0.875rem 0;border-bottom:1px solid #F3F4F6;display:flex;align-items:center;gap:0.875rem">
                {{-- Note en cercle --}}
                @php $pct = $r->pourcentage; $color = $pct >= 75 ? '#059669' : ($pct >= 50 ? '#D97706' : '#DC2626'); @endphp
                <div style="width:44px;height:44px;border-radius:50%;border:3px solid {{ $color }};display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <span style="font-size:0.7rem;font-weight:800;color:{{ $color }}">
                        {{ round(($r->note_finale / $r->note_sur) * 20, 0) }}
                    </span>
                </div>
                <div style="flex:1;min-width:0">
                    <div style="font-size:0.875rem;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                        {{ $r->devoir?->titre }}
                    </div>
                    <div style="font-size:0.75rem;color:#6B7280">
                        {{ $r->devoir?->matiere?->nom }} · {{ $r->created_at->format('d/m/Y') }}
                    </div>
                </div>
                <div style="text-align:right;flex-shrink:0">
                    @php $c = $pct >= 75 ? ['#D1FAE5','#065F46'] : ($pct >= 50 ? ['#FEF3C7','#92400E'] : ['#FEE2E2','#991B1B']); @endphp
                    <span style="background:{{ $c[0] }};color:{{ $c[1] }};font-size:0.7rem;font-weight:600;padding:2px 8px;border-radius:10px">
                        {{ $r->mention }}
                    </span>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>

{{-- RÉSUMÉ MENSUEL --}}
@if($parMois->isNotEmpty())
<div class="card-section">
    <div class="card-header-row">
        <h2><i class="bi bi-calendar3 me-2" style="color:#7C3AED"></i>Progression mensuelle</h2>
    </div>
    <div style="padding:1.25rem;display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:1rem">
        @foreach($parMois as $mois)
        <div style="background:#F9FAFB;border:1px solid #E5E7EB;border-radius:12px;padding:1rem;text-align:center">
            <div style="font-size:0.75rem;color:#6B7280;margin-bottom:0.5rem;font-weight:600;text-transform:uppercase">
                {{ $mois['mois'] }}
            </div>
            <div style="font-size:1.5rem;font-weight:800;color:{{ $mois['moyenne'] >= 10 ? '#4F46E5' : '#DC2626' }}">
                {{ $mois['moyenne'] }}
            </div>
            <div style="font-size:0.7rem;color:#6B7280;margin-bottom:0.5rem">/20 · {{ $mois['nb'] }} devoir(s)</div>
            {{-- Mini barre taux réussite --}}
            <div style="height:4px;background:#E5E7EB;border-radius:2px;overflow:hidden">
                <div style="height:100%;background:{{ $mois['taux_reussite'] >= 50 ? '#059669' : '#DC2626' }};border-radius:2px;width:{{ $mois['taux_reussite'] }}%"></div>
            </div>
            <div style="font-size:0.68rem;color:#6B7280;margin-top:3px">{{ $mois['taux_reussite'] }}% réussite</div>
        </div>
        @endforeach
    </div>
</div>
@endif

@endsection