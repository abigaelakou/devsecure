{{-- resources/views/eleve/devoirs.blade.php --}}
@extends('layouts.app')
@section('title', 'Mes devoirs')
@section('page-title', 'Mes devoirs disponibles')
@section('content')
@if($devoirs->isEmpty())
<div class="card-section" style="padding:3rem;text-align:center;color:#6B7280">
    <i class="bi bi-inbox" style="font-size:3rem;display:block;margin-bottom:1rem"></i>
    <div style="font-size:1rem;font-weight:500">Aucun devoir disponible pour le moment.</div>
    <div style="font-size:0.875rem;margin-top:0.5rem">Vos enseignants n'ont pas encore publié de devoirs.</div>
</div>
@else
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:1.25rem">
    @foreach($devoirs as $devoir)
    <div class="card-section" style="margin:0">
        <div style="padding:1.5rem">
            <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.875rem">
                <span style="background:{{ $devoir->matiere?->couleur ?? '#4F46E5' }}20;color:{{ $devoir->matiere?->couleur ?? '#4F46E5' }};font-size:0.72rem;font-weight:600;padding:3px 10px;border-radius:20px;text-transform:uppercase">
                    {{ $devoir->matiere?->nom }}
                </span>
            </div>
            <h3 style="font-size:1rem;font-weight:600;margin-bottom:0.5rem">{{ $devoir->titre }}</h3>
            @if($devoir->description)
            <p style="font-size:0.8rem;color:#6B7280;margin-bottom:0.875rem">{{ Str::limit($devoir->description, 80) }}</p>
            @endif
            <div style="display:flex;gap:1rem;font-size:0.8rem;color:#6B7280;margin-bottom:1rem">
                <span><i class="bi bi-question-circle me-1"></i>{{ $devoir->questions_count }} questions</span>
                @if($devoir->duree_totale_minutes)
                <span><i class="bi bi-clock me-1"></i>{{ $devoir->duree_totale_minutes }} min</span>
                @endif
            </div>
            @if($devoir->expire_le)
            <div style="font-size:0.75rem;color:{{ $devoir->expire_le->diffInDays() <= 1 ? '#DC2626' : '#6B7280' }};margin-bottom:1rem">
                <i class="bi bi-calendar3 me-1"></i>
                Expire {{ $devoir->expire_le->diffForHumans() }}
            </div>
            @endif
            <form method="POST" action="{{ route('eleve.devoir.commencer', $devoir->id) }}">
                @csrf
                <button type="submit" style="width:100%;padding:0.7rem;background:#4F46E5;color:white;border:none;border-radius:10px;font-size:0.9rem;font-weight:600;cursor:pointer;transition:background 0.2s"
                        onmouseover="this.style.background='#3730A3'" onmouseout="this.style.background='#4F46E5'">
                    <i class="bi bi-play-circle me-1"></i> Commencer
                </button>
            </form>
        </div>
    </div>
    @endforeach
</div>
@endif
@endsection
