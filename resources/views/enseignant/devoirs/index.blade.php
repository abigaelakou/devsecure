{{-- resources/views/enseignant/devoirs/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Mes devoirs')
@section('page-title', 'Mes devoirs')
@section('topbar-actions')
<a href="{{ route('enseignant.devoirs.create') }}" class="btn-primary-custom">
    <i class="bi bi-plus-lg"></i> Nouveau devoir
</a>
@endsection
@section('content')
{{-- Filtres --}}
<div class="card-section" style="margin-bottom:1.5rem">
    <div style="padding:1rem 1.5rem">
        <form method="GET" style="display:flex;gap:1rem;flex-wrap:wrap">
            <select name="statut" style="padding:0.5rem 1rem;border:1.5px solid #E5E7EB;border-radius:8px;font-size:0.875rem">
                <option value="">Tous les statuts</option>
                <option value="brouillon" {{ request('statut')=='brouillon'?'selected':'' }}>Brouillons</option>
                <option value="actif" {{ request('statut')=='actif'?'selected':'' }}>Actifs</option>
                <option value="archive" {{ request('statut')=='archive'?'selected':'' }}>Archivés</option>
            </select>
            <select name="matiere_id" style="padding:0.5rem 1rem;border:1.5px solid #E5E7EB;border-radius:8px;font-size:0.875rem">
                <option value="">Toutes les matières</option>
                @foreach($matieres as $m)
                <option value="{{ $m->id }}" {{ request('matiere_id')==$m->id?'selected':'' }}>{{ $m->nom }}</option>
                @endforeach
            </select>
            <button type="submit" style="padding:0.5rem 1.25rem;background:#4F46E5;color:white;border:none;border-radius:8px;font-size:0.875rem;cursor:pointer">
                Filtrer
            </button>
        </form>
    </div>
</div>

<div class="card-section">
    <table style="width:100%;border-collapse:collapse">
        <thead><tr style="background:#F9FAFB">
            @foreach(['Devoir','Classe','Questions','Progression','Statut','Actions'] as $th)
            <th style="padding:0.6rem 1.5rem;font-size:0.72rem;font-weight:600;text-transform:uppercase;color:#6B7280;text-align:left">{{ $th }}</th>
            @endforeach
        </tr></thead>
        <tbody>
            @forelse($devoirs as $devoir)
            <tr style="border-top:1px solid #E5E7EB">
                <td style="padding:0.875rem 1.5rem">
                    <strong style="font-size:0.875rem">{{ Str::limit($devoir->titre, 40) }}</strong>
                    <div style="font-size:0.75rem;color:#6B7280">
                        <span style="background:{{ $devoir->matiere?->couleur ?? '#4F46E5' }}20;color:{{ $devoir->matiere?->couleur ?? '#4F46E5' }};padding:1px 6px;border-radius:4px">
                            {{ $devoir->matiere?->nom }}
                        </span>
                        · {{ $devoir->duree_totale_minutes ?? '—' }} min
                    </div>
                </td>
                <td style="padding:0.875rem 1.5rem;font-size:0.875rem">{{ $devoir->classe?->nom }}</td>
                <td style="padding:0.875rem 1.5rem;font-size:0.875rem;font-weight:600;color:#4F46E5">{{ $devoir->questions_count }}</td>
                <td style="padding:0.875rem 1.5rem">
                    @php $pct = $devoir->classe?->effectif > 0 ? round($devoir->resultats_count / max($devoir->classe->effectif,1) * 100) : 0; @endphp
                    <div style="width:80px;height:6px;background:#E5E7EB;border-radius:3px;overflow:hidden;display:inline-block;vertical-align:middle;margin-right:4px">
                        <div style="height:100%;background:#4F46E5;border-radius:3px;width:{{ $pct }}%"></div>
                    </div>
                    <span style="font-size:0.78rem">{{ $devoir->resultats_count }}/{{ $devoir->classe?->effectif ?? '?' }}</span>
                </td>
                <td style="padding:0.875rem 1.5rem">
                    <span class="badge-statut badge-{{ $devoir->statut }}">{{ ucfirst($devoir->statut) }}</span>
                </td>
                <td style="padding:0.875rem 1rem">
                    <div style="display:flex;gap:0.4rem">
                        <a href="{{ route('enseignant.devoirs.edit', $devoir->id) }}" style="width:28px;height:28px;border:1px solid #E5E7EB;border-radius:7px;display:flex;align-items:center;justify-content:center;color:#6B7280;text-decoration:none" title="Modifier"><i class="bi bi-pencil"></i></a>
                        <a href="{{ route('enseignant.devoirs.resultats', $devoir->id) }}" style="width:28px;height:28px;border:1px solid #E5E7EB;border-radius:7px;display:flex;align-items:center;justify-content:center;color:#6B7280;text-decoration:none" title="Résultats"><i class="bi bi-bar-chart"></i></a>
                        @if($devoir->statut === 'brouillon')
                        <form method="POST" action="{{ route('enseignant.devoirs.publier', $devoir->id) }}">
                            @csrf
                            <button type="submit" style="width:28px;height:28px;border:1px solid #E5E7EB;border-radius:7px;background:white;color:#059669;font-size:0.85rem;cursor:pointer" title="Publier"><i class="bi bi-send"></i></button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" style="padding:2rem;text-align:center;color:#6B7280">Aucun devoir créé. <a href="{{ route('enseignant.devoirs.create') }}">Créer le premier</a></td></tr>
            @endforelse
        </tbody>
    </table>
    <div style="padding:1rem 1.5rem">{{ $devoirs->links() }}</div>
</div>
@endsection