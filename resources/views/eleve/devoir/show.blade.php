@extends('layouts.app')
@section('title', $devoir->titre)
@section('page-title', $devoir->titre)
@section('page-subtitle', $devoir->matiere?->nom . ' · ' . $devoir->classe?->nom)

@section('content')
<div style="max-width:680px;margin:0 auto">

    {{-- Infos principales --}}
    <div class="card-section" style="margin-bottom:1.25rem">
        <div style="padding:1.5rem">
            <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:1.25rem">
                <div style="width:48px;height:48px;background:{{ $devoir->matiere?->couleur ?? '#4F46E5' }}20;border-radius:14px;display:flex;align-items:center;justify-content:center;color:{{ $devoir->matiere?->couleur ?? '#4F46E5' }};font-size:1.4rem">
                    <i class="bi bi-journals"></i>
                </div>
                <div>
                    <div style="font-size:1.1rem;font-weight:700">{{ $devoir->titre }}</div>
                    <div style="font-size:0.8rem;color:#6B7280">{{ $devoir->enseignant?->nom_complet }}</div>
                </div>
            </div>

            @if($devoir->description)
            <p style="font-size:0.9rem;color:#374151;background:#F9FAFB;padding:1rem;border-radius:10px;margin-bottom:1.25rem;line-height:1.6">
                {{ $devoir->description }}
            </p>
            @endif

            {{-- Détails --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem">
                @foreach([
                    ['bi-question-circle','Questions',$devoir->questions_count . ' questions'],
                    ['bi-clock','Durée',$devoir->duree_totale_minutes ? $devoir->duree_totale_minutes . ' minutes' : 'Pas de limite'],
                    ['bi-stopwatch','Temps/question',$devoir->temps_par_question_secondes ? $devoir->temps_par_question_secondes . ' secondes' : 'Libre'],
                    ['bi-arrow-repeat','Tentatives',$devoir->max_tentatives . ' tentative(s) max'],
                    ['bi-star','Note sur',$devoir->note_sur . ' points'],
                    ['bi-calendar3','Expire le',$devoir->expire_le?->format('d/m/Y H:i') ?? 'Pas de limite'],
                ] as [$icon,$label,$value])
                <div style="display:flex;align-items:center;gap:0.75rem;padding:0.75rem;background:#F9FAFB;border-radius:10px">
                    <i class="bi {{ $icon }}" style="color:#4F46E5;font-size:1rem;flex-shrink:0"></i>
                    <div>
                        <div style="font-size:0.72rem;color:#6B7280;text-transform:uppercase;letter-spacing:0.04em">{{ $label }}</div>
                        <div style="font-size:0.875rem;font-weight:600">{{ $value }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Avertissement antitriche --}}
    <div style="background:#FEF3C7;border:1.5px solid #FCD34D;border-radius:12px;padding:1rem 1.25rem;margin-bottom:1.25rem;display:flex;gap:0.75rem">
        <i class="bi bi-shield-exclamation" style="color:#D97706;font-size:1.25rem;flex-shrink:0;margin-top:2px"></i>
        <div>
            <div style="font-size:0.875rem;font-weight:600;color:#92400E;margin-bottom:0.25rem">Règles importantes</div>
            <ul style="font-size:0.8rem;color:#92400E;margin:0;padding-left:1rem;line-height:1.8">
                <li>Ne changez pas d'onglet pendant le devoir</li>
                <li>Ne réduisez pas la fenêtre du navigateur</li>
                <li>Après {{ $devoir->max_changements_onglet }} sorties, le devoir sera soumis automatiquement</li>
                <li>Le temps est compté dès que vous cliquez sur "Commencer"</li>
            </ul>
        </div>
    </div>

    {{-- Bouton commencer --}}
    <form method="POST" action="{{ route('eleve.devoir.commencer', $devoir->id) }}">
        @csrf
        <button type="submit"
                style="width:100%;padding:1rem;background:#4F46E5;color:white;border:none;border-radius:14px;font-size:1rem;font-weight:700;cursor:pointer;transition:all 0.2s;display:flex;align-items:center;justify-content:center;gap:0.5rem"
                onmouseover="this.style.background='#3730A3';this.style.transform='translateY(-1px)'"
                onmouseout="this.style.background='#4F46E5';this.style.transform='none'">
            <i class="bi bi-play-circle-fill"></i>
            Commencer le devoir maintenant
        </button>
    </form>

    <div style="text-align:center;margin-top:0.75rem">
        <a href="{{ route('eleve.devoirs') }}" style="font-size:0.8rem;color:#6B7280;text-decoration:none">
            <i class="bi bi-arrow-left me-1"></i> Retour à la liste
        </a>
    </div>

</div>
@endsection