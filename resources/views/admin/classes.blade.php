{{-- resources/views/admin/classes.blade.php --}}
@extends('layouts.app')
@section('title', 'Classes')
@section('page-title', 'Gestion des classes')
@section('content')
<div style="display:grid;grid-template-columns:1fr 360px;gap:1.5rem">
    <div class="card-section">
        <div class="card-header-row"><h2>Classes — {{ $annee?->libelle }}</h2></div>
        <table style="width:100%;border-collapse:collapse">
            <thead><tr style="background:#F9FAFB">
                @foreach(['Classe','Niveau','Effectif','Actions'] as $th)
                <th style="padding:0.6rem 1.5rem;font-size:0.72rem;font-weight:600;text-transform:uppercase;color:#6B7280;text-align:left">{{ $th }}</th>
                @endforeach
            </tr></thead>
            <tbody>
                @forelse($classes as $classe)
                <tr style="border-top:1px solid #E5E7EB">
                    <td style="padding:0.875rem 1.5rem;font-weight:600;font-size:0.875rem">{{ $classe->nom }}</td>
                    <td style="padding:0.875rem 1.5rem;font-size:0.875rem;color:#6B7280">{{ ucfirst($classe->niveau) }}</td>
                    <td style="padding:0.875rem 1.5rem">
                        <span style="background:#EEF2FF;color:#4F46E5;font-size:0.75rem;font-weight:600;padding:3px 10px;border-radius:20px">
                            {{ $classe->eleves_count }} élèves
                        </span>
                    </td>
                    <td style="padding:0.875rem 1.5rem;font-size:0.8rem;color:#6B7280">—</td>
                </tr>
                @empty
                <tr><td colspan="4" style="padding:2rem;text-align:center;color:#6B7280">Aucune classe créée.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-section" style="align-self:start">
        <div class="card-header-row"><h2>Nouvelle classe</h2></div>
        <div style="padding:1.25rem">
            <form method="POST" action="{{ route('admin.classes') }}">
                @csrf
                <div style="margin-bottom:1rem">
                    <label style="font-size:0.8rem;font-weight:500;display:block;margin-bottom:0.3rem">Nom</label>
                    <input type="text" name="nom" placeholder="3ème A" required style="width:100%;padding:0.6rem;border:1.5px solid #E5E7EB;border-radius:8px;font-size:0.875rem">
                </div>
                <div style="margin-bottom:1.25rem">
                    <label style="font-size:0.8rem;font-weight:500;display:block;margin-bottom:0.3rem">Niveau</label>
                    <select name="niveau" style="width:100%;padding:0.6rem;border:1.5px solid #E5E7EB;border-radius:8px;font-size:0.875rem">
                        @foreach(['6eme','5eme','4eme','3eme','seconde','premiere','terminale'] as $niv)
                        <option value="{{ $niv }}">{{ ucfirst($niv) }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" style="width:100%;padding:0.75rem;background:#4F46E5;color:white;border:none;border-radius:10px;font-weight:600;cursor:pointer">
                    <i class="bi bi-plus-lg me-1"></i> Créer la classe
                </button>
            </form>
        </div>
    </div>
</div>
@endsection