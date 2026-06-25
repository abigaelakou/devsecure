{{-- ════════════════════════════════════════════════════════
     resources/views/enseignant/statistiques.blade.php
════════════════════════════════════════════════════════ --}}
@extends('layouts.app')
@section('title', 'Statistiques')
@section('page-title', 'Statistiques')
@section('content')
@forelse($devoirs as $devoir)
<div class="card-section" style="margin-bottom:1.25rem">
    <div class="card-header-row">
        <h2>{{ $devoir->titre }}</h2>
        <span style="font-size:0.8rem;color:#6B7280">{{ $devoir->classe?->nom }}</span>
    </div>
    <div style="padding:1.25rem;display:grid;grid-template-columns:repeat(4,1fr);gap:1rem">
        @php $avg = round($devoir->resultats->avg('note_finale'), 1); @endphp
        <div style="text-align:center;padding:1rem;background:#F9FAFB;border-radius:10px">
            <div style="font-size:1.75rem;font-weight:700;color:#4F46E5">{{ $devoir->resultats->count() }}</div>
            <div style="font-size:0.78rem;color:#6B7280">Élèves</div>
        </div>
        <div style="text-align:center;padding:1rem;background:#F9FAFB;border-radius:10px">
            <div style="font-size:1.75rem;font-weight:700;color:#D97706">{{ $avg ?: '—' }}</div>
            <div style="font-size:0.78rem;color:#6B7280">Moyenne /{{ $devoir->note_sur }}</div>
        </div>
        <div style="text-align:center;padding:1rem;background:#F9FAFB;border-radius:10px">
            <div style="font-size:1.75rem;font-weight:700;color:#059669">{{ $devoir->resultats->max('note_finale') ?: '—' }}</div>
            <div style="font-size:0.78rem;color:#6B7280">Meilleure note</div>
        </div>
        <div style="text-align:center;padding:1rem;background:#F9FAFB;border-radius:10px">
            <div style="font-size:1.75rem;font-weight:700;color:#DC2626">{{ $devoir->resultats->where('fraude_detectee',true)->count() }}</div>
            <div style="font-size:0.78rem;color:#6B7280">Fraudes</div>
        </div>
    </div>
</div>
@empty
<div class="card-section" style="padding:3rem;text-align:center;color:#6B7280">
    <i class="bi bi-graph-up" style="font-size:2.5rem;display:block;margin-bottom:0.75rem"></i>
    Aucune statistique disponible. Publiez des devoirs et attendez que les élèves composent.
</div>
@endforelse
@endsection

