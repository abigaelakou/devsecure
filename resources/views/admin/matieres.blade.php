@extends('layouts.app')
@section('title', 'Matières')
@section('page-title', 'Gestion des matières')
@section('content')
<div style="display:grid;grid-template-columns:1fr 360px;gap:1.5rem">
    <div class="card-section">
        <div class="card-header-row"><h2>Matières</h2></div>
        <div style="padding:1.25rem;display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:1rem">
            @forelse($matieres as $matiere)
            <div style="border:1px solid #E5E7EB;border-radius:12px;padding:1rem;display:flex;align-items:center;gap:0.75rem">
                <div style="width:40px;height:40px;border-radius:10px;background:{{ $matiere->couleur }}20;color:{{ $matiere->couleur }};display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0">
                    <i class="bi bi-book"></i>
                </div>
                <div>
                    <div style="font-size:0.875rem;font-weight:600">{{ $matiere->nom }}</div>
                    <div style="font-size:0.75rem;color:#6B7280">{{ $matiere->code }} · {{ $matiere->devoirs_count }} devoirs</div>
                </div>
            </div>
            @empty
            <p style="color:#6B7280;grid-column:1/-1;text-align:center;padding:2rem">Aucune matière créée.</p>
            @endforelse
        </div>
    </div>
    <div class="card-section" style="align-self:start">
        <div class="card-header-row"><h2>Nouvelle matière</h2></div>
        <div style="padding:1.25rem">
            <form method="POST" action="{{ route('admin.matieres') }}">
                @csrf
                <div style="margin-bottom:1rem">
                    <label style="font-size:0.8rem;font-weight:500;display:block;margin-bottom:0.3rem">Nom</label>
                    <input type="text" name="nom" placeholder="Mathématiques" required style="width:100%;padding:0.6rem;border:1.5px solid #E5E7EB;border-radius:8px;font-size:0.875rem">
                </div>
                <div style="margin-bottom:1rem">
                    <label style="font-size:0.8rem;font-weight:500;display:block;margin-bottom:0.3rem">Code</label>
                    <input type="text" name="code" placeholder="MATH" maxlength="10" required style="width:100%;padding:0.6rem;border:1.5px solid #E5E7EB;border-radius:8px;font-size:0.875rem">
                </div>
                <div style="margin-bottom:1.25rem">
                    <label style="font-size:0.8rem;font-weight:500;display:block;margin-bottom:0.3rem">Couleur</label>
                    <input type="color" name="couleur" value="#4F46E5" style="width:100%;height:40px;border:1.5px solid #E5E7EB;border-radius:8px;cursor:pointer">
                </div>
                <button type="submit" style="width:100%;padding:0.75rem;background:#4F46E5;color:white;border:none;border-radius:10px;font-weight:600;cursor:pointer">
                    <i class="bi bi-plus-lg me-1"></i> Créer la matière
                </button>
            </form>
        </div>
    </div>
</div>
@endsection