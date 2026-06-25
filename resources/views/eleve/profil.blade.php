
@extends('layouts.app')
@section('title', 'Mon profil')
@section('page-title', 'Mon profil')
@section('content')
<div style="max-width:560px;margin:0 auto">
    <div class="card-section">
        <div class="card-header-row"><h2>Informations personnelles</h2></div>
        <div style="padding:1.5rem">
            <div style="width:64px;height:64px;background:#EEF2FF;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.5rem;font-weight:700;color:#4F46E5;margin-bottom:1.5rem">
                {{ strtoupper(substr($user->prenoms,0,1).substr($user->nom,0,1)) }}
            </div>
            @foreach([
                ['Nom complet',$user->nom_complet],
                ['Email',$user->email],
                ['Matricule',$user->matricule ?? '—'],
                ['Rôle',ucfirst($user->role)],
                ['Dernière connexion',$user->derniere_connexion?->format('d/m/Y H:i') ?? 'Première connexion'],
            ] as [$label,$value])
            <div style="display:flex;justify-content:space-between;padding:0.75rem 0;border-bottom:1px solid #F3F4F6;font-size:0.875rem">
                <span style="color:#6B7280">{{ $label }}</span>
                <span style="font-weight:500">{{ $value }}</span>
            </div>
            @endforeach
        </div>
    </div>
    <a href="{{ route('eleve.bulletin') }}"
        style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.75rem 1.25rem;background:#EEF2FF;color:#4F46E5;border-radius:10px;text-decoration:none;font-size:0.875rem;font-weight:600;margin-top:1rem">
        <i class="bi bi-file-earmark-pdf"></i> Télécharger mon bulletin PDF
    </a>
</div>
@endsection