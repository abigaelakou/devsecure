{{-- ════════════════════════════════════════════════════════
     resources/views/enseignant/devoirs/edit.blade.php
════════════════════════════════════════════════════════ --}}
@extends('layouts.app')
@section('title', 'Modifier le devoir')
@section('page-title', 'Modifier · ' . $devoir->titre)
@section('content')
<div style="max-width:760px;margin:0 auto">
    <div class="card-section" style="margin-bottom:1.25rem">
        <div class="card-header-row">
            <h2>Paramètres du devoir</h2>
            @if($devoir->statut === 'brouillon')
            <form method="POST" action="{{ route('enseignant.devoirs.publier', $devoir->id) }}">
                @csrf
                <button type="submit" style="padding:0.5rem 1.25rem;background:#059669;color:white;border:none;border-radius:8px;font-size:0.875rem;font-weight:600;cursor:pointer">
                    <i class="bi bi-send me-1"></i> Publier
                </button>
            </form>
            @endif
        </div>
        <div style="padding:1.5rem">
            <form method="POST" action="{{ route('enseignant.devoirs.update', $devoir->id) }}">
                @csrf @method('PUT')
                <div style="margin-bottom:1rem">
                    <label style="font-size:0.875rem;font-weight:500;display:block;margin-bottom:0.4rem">Titre</label>
                    <input type="text" name="titre" value="{{ $devoir->titre }}" required style="width:100%;padding:0.75rem;border:1.5px solid #E5E7EB;border-radius:10px;font-size:0.9rem">
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem">
                    <div>
                        <label style="font-size:0.875rem;font-weight:500;display:block;margin-bottom:0.4rem">Durée totale (min)</label>
                        <input type="number" name="duree_totale_minutes" value="{{ $devoir->duree_totale_minutes }}" style="width:100%;padding:0.75rem;border:1.5px solid #E5E7EB;border-radius:10px;font-size:0.9rem">
                    </div>
                    <div>
                        <label style="font-size:0.875rem;font-weight:500;display:block;margin-bottom:0.4rem">Temps/question (sec)</label>
                        <input type="number" name="temps_par_question_secondes" value="{{ $devoir->temps_par_question_secondes }}" style="width:100%;padding:0.75rem;border:1.5px solid #E5E7EB;border-radius:10px;font-size:0.9rem">
                    </div>
                </div>
                <button type="submit" style="padding:0.75rem 2rem;background:#4F46E5;color:white;border:none;border-radius:10px;font-weight:600;cursor:pointer">
                    Sauvegarder
                </button>
            </form>
        </div>
    </div>

    {{-- Questions --}}
    <div class="card-section">
        <div class="card-header-row">
            <h2>Questions ({{ $devoir->questions->count() }})</h2>
        </div>
        @forelse($devoir->questions as $q)
        <div style="padding:1rem 1.5rem;border-bottom:1px solid #E5E7EB;display:flex;align-items:center;gap:1rem">
            <span style="background:#EEF2FF;color:#4F46E5;font-size:0.75rem;font-weight:700;padding:3px 10px;border-radius:6px;flex-shrink:0">Q{{ $q->ordre }}</span>
            <div style="flex:1;font-size:0.875rem">{{ Str::limit($q->enonce, 80) }}</div>
            <span style="font-size:0.75rem;color:#6B7280">{{ $q->points }} pt · {{ $q->type }}</span>
        </div>
        @empty
        <div style="padding:2rem;text-align:center;color:#6B7280;font-size:0.875rem">
            Aucune question. Utilisez l'API pour ajouter des questions.
        </div>
        @endforelse
    </div>
</div>
@endsection