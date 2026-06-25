@extends('layouts.app')
@section('title', 'Mes classes')
@section('page-title', 'Mes classes')
@section('content')

@if($classes->isEmpty())
<div class="card-section" style="padding:3rem;text-align:center;color:#6B7280">
    <i class="bi bi-people" style="font-size:2.5rem;display:block;margin-bottom:0.75rem"></i>
    Aucune classe assignée. Contactez l'administrateur.
</div>
@else
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1.25rem">
    @foreach($classes as $classe)
    <div class="card-section" style="margin:0">
        <div style="padding:1.5rem">
            <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:1rem">
                <div style="width:44px;height:44px;background:#EEF2FF;border-radius:12px;display:flex;align-items:center;justify-content:center;color:#4F46E5;font-size:1.2rem">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div>
                    <div style="font-size:1rem;font-weight:700">{{ $classe->nom }}</div>
                    <div style="font-size:0.75rem;color:#6B7280">{{ ucfirst($classe->niveau) }} · {{ $classe->anneeScolaire?->libelle }}</div>
                </div>
            </div>
            <div style="display:flex;justify-content:space-between;font-size:0.875rem">
                <span style="color:#6B7280">Effectif</span>
                <strong>{{ $classe->eleves_count }} élèves</strong>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif

@endsection