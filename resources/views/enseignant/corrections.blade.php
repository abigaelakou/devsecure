{{-- resources/views/enseignant/corrections.blade.php --}}
@extends('layouts.app')
@section('title', 'Corrections')
@section('page-title', 'Questions à corriger')
@section('content')
<div class="card-section">
    <div class="card-header-row">
        <h2>Questions rédactionnelles en attente</h2>
        <span style="background:#FEE2E2;color:#991B1B;font-size:0.72rem;font-weight:600;padding:3px 10px;border-radius:10px">
            {{ $corrections->total() }} à corriger
        </span>
    </div>
    @forelse($corrections as $r)
    <div style="padding:1.5rem;border-bottom:1px solid #E5E7EB">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:0.75rem">
            <div>
                <div style="font-size:0.875rem;font-weight:600">{{ $r->tentative?->eleve?->nom_complet }}</div>
                <div style="font-size:0.75rem;color:#6B7280">{{ $r->tentative?->devoir?->titre }}</div>
            </div>
            <span style="background:#EEF2FF;color:#4F46E5;font-size:0.72rem;padding:3px 10px;border-radius:20px">
                {{ $r->question?->points }} pts max
            </span>
        </div>
        <div style="background:#F9FAFB;padding:0.875rem;border-radius:10px;font-size:0.875rem;margin-bottom:0.875rem">
            <div style="font-size:0.75rem;color:#6B7280;margin-bottom:0.3rem">Question :</div>
            {{ $r->question?->enonce }}
        </div>
        <div style="background:white;border:1.5px solid #E5E7EB;padding:0.875rem;border-radius:10px;font-size:0.875rem;margin-bottom:1rem">
            <div style="font-size:0.75rem;color:#6B7280;margin-bottom:0.3rem">Réponse de l'élève :</div>
            {{ $r->texte_libre ?? '(Sans réponse)' }}
        </div>
        <form method="POST" action="{{ route('enseignant.corrections.corriger', $r->id) }}" style="display:flex;gap:1rem;align-items:flex-end">
            @csrf
            <div style="flex:1">
                <label style="font-size:0.78rem;font-weight:500;display:block;margin-bottom:0.3rem">Points obtenus (max {{ $r->question?->points }})</label>
                <input type="number" name="points_obtenus" min="0" max="{{ $r->question?->points }}" step="0.5" required
                       style="width:100%;padding:0.6rem;border:1.5px solid #E5E7EB;border-radius:8px;font-size:0.875rem">
            </div>
            <div style="flex:2">
                <label style="font-size:0.78rem;font-weight:500;display:block;margin-bottom:0.3rem">Commentaire (optionnel)</label>
                <input type="text" name="commentaire_enseignant" placeholder="Feedback pour l'élève..."
                       style="width:100%;padding:0.6rem;border:1.5px solid #E5E7EB;border-radius:8px;font-size:0.875rem">
            </div>
            <input type="hidden" name="est_correcte" id="est_correcte_{{ $r->id }}" value="1">
            <div style="display:flex;gap:0.5rem">
                <button type="submit" onclick="document.getElementById('est_correcte_{{ $r->id }}').value=1"
                        style="padding:0.6rem 1rem;background:#059669;color:white;border:none;border-radius:8px;font-size:0.875rem;font-weight:600;cursor:pointer">
                    Valider
                </button>
            </div>
        </form>
    </div>
    @empty
    <div style="padding:3rem;text-align:center;color:#6B7280">
        <i class="bi bi-check2-all" style="font-size:2.5rem;display:block;margin-bottom:0.75rem;color:#059669"></i>
        Toutes les corrections sont à jour !
    </div>
    @endforelse
    <div style="padding:1rem 1.5rem">{{ $corrections->links() }}</div>
</div>
@endsection
