@extends('layouts.app')
@section('title', 'Élèves par classe')
@section('page-title', 'Affectation élèves → classes')
@section('page-subtitle', 'Année scolaire : ' . ($annee?->libelle ?? 'Aucune année active'))

@section('topbar-actions')
    <a href="{{ route('admin.eleve-classes.index') }}?export=1"
       style="padding:0.5rem 1rem;background:#F3F4F6;color:#374151;border:1.5px solid #E5E7EB;border-radius:8px;font-size:0.875rem;font-weight:600;text-decoration:none">
        <i class="bi bi-download me-1"></i> Export global
    </a>
@endsection

@section('content')

@if(!$annee)
<div style="background:#FEF3C7;border:1.5px solid #FCD34D;border-radius:12px;padding:1rem 1.25rem;margin-bottom:1.5rem;display:flex;gap:0.75rem;align-items:center">
    <i class="bi bi-exclamation-triangle-fill" style="color:#D97706"></i>
    <strong style="font-size:0.875rem">Aucune année scolaire active.</strong>
    <a href="{{ route('admin.annees-scolaires') }}" style="font-size:0.875rem;color:#4F46E5;margin-left:0.5rem">→ Activer une année</a>
</div>
@endif

{{-- Alerte élèves sans classe --}}
@if($elevesNonAffectes->isNotEmpty())
<div style="background:#FEF3C7;border:1.5px solid #FCD34D;border-radius:12px;padding:1rem 1.25rem;margin-bottom:1.5rem">
    <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:0.75rem">
        <i class="bi bi-exclamation-triangle-fill" style="color:#D97706;font-size:1.1rem"></i>
        <strong style="font-size:0.875rem;color:#92400E">
            {{ $elevesNonAffectes->count() }} élève(s) sans classe
        </strong>
    </div>
    <div style="display:flex;flex-wrap:wrap;gap:0.5rem">
        @foreach($elevesNonAffectes->take(8) as $e)
        <span style="background:white;border:1px solid #FCD34D;border-radius:20px;padding:3px 10px;font-size:0.78rem;color:#92400E">
            {{ $e->nom_complet }}
        </span>
        @endforeach
        @if($elevesNonAffectes->count() > 8)
        <span style="font-size:0.78rem;color:#92400E;padding:3px 0">
            + {{ $elevesNonAffectes->count() - 8 }} autres...
        </span>
        @endif
    </div>
</div>
@endif

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1.25rem">
    @forelse($classes as $classe)
    <div class="card-section" style="margin:0">
        <div style="padding:1.25rem">

            {{-- Header classe --}}
            <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:1rem">
                <div style="width:44px;height:44px;background:#EEF2FF;border-radius:12px;display:flex;align-items:center;justify-content:center;color:#4F46E5;font-size:1.2rem">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div style="flex:1">
                    <div style="font-size:1rem;font-weight:700">{{ $classe->nom }}</div>
                    <div style="font-size:0.75rem;color:#6B7280">{{ ucfirst($classe->niveau) }}</div>
                </div>
                <div style="text-align:right">
                    <div style="font-size:1.5rem;font-weight:700;color:#4F46E5">{{ $classe->eleves_count }}</div>
                    <div style="font-size:0.7rem;color:#6B7280">élèves</div>
                </div>
            </div>

            {{-- Barre remplissage --}}
            @php $pct = min(100, round($classe->eleves_count / 40 * 100)); @endphp
            <div style="height:5px;background:#E5E7EB;border-radius:3px;overflow:hidden;margin-bottom:1rem">
                <div style="height:100%;background:{{ $pct > 80 ? '#DC2626' : ($pct > 60 ? '#D97706' : '#4F46E5') }};border-radius:3px;width:{{ $pct }}%"></div>
            </div>

            {{-- Actions --}}
            <div style="display:flex;gap:0.5rem">
                <a href="{{ route('admin.eleve-classes.show', $classe->id) }}"
                   style="flex:1;text-align:center;padding:0.6rem;background:#EEF2FF;color:#4F46E5;border-radius:8px;text-decoration:none;font-size:0.8rem;font-weight:600">
                    <i class="bi bi-pencil me-1"></i> Gérer
                </a>
                <a href="{{ route('admin.eleve-classes.export', $classe->id) }}"
                   style="padding:0.6rem 0.75rem;background:#F3F4F6;color:#6B7280;border-radius:8px;text-decoration:none;font-size:0.8rem"
                   title="Exporter CSV">
                    <i class="bi bi-download"></i>
                </a>
            </div>
        </div>
    </div>
    @empty
    <div style="grid-column:1/-1;padding:3rem;text-align:center;color:#6B7280;background:white;border-radius:14px;border:1px solid #E5E7EB">
        <i class="bi bi-diagram-3" style="font-size:2.5rem;display:block;margin-bottom:0.75rem"></i>
        Aucune classe créée pour cette année.
        <a href="{{ route('admin.classes') }}" style="color:#4F46E5;display:block;margin-top:0.5rem">→ Créer des classes</a>
    </div>
    @endforelse
</div>

@endsection