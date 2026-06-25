@extends('layouts.app')
@section('title', 'Résultat détaillé')
@section('page-title', 'Résultat du devoir')
@section('page-subtitle', $tentative->devoir?->titre)

@section('content')
<div style="max-width:760px;margin:0 auto">

    {{-- Résultat principal --}}
    @php $r = $tentative->resultat; @endphp
    @if($r)
    <div style="background:linear-gradient(135deg,#1E1B4B,#4F46E5);border-radius:20px;padding:2rem;text-align:center;margin-bottom:1.5rem;color:white">
        <div style="font-size:0.875rem;opacity:0.8;margin-bottom:0.5rem">Votre note</div>
        <div style="font-size:4rem;font-weight:800;line-height:1">{{ $r->note_finale }}</div>
        <div style="font-size:1.1rem;opacity:0.8;margin-bottom:1rem">/ {{ $r->note_sur }}</div>
        <div style="display:inline-block;background:rgba(255,255,255,0.2);padding:6px 20px;border-radius:20px;font-size:0.9rem;font-weight:600">
            {{ $r->mention }} — {{ $r->pourcentage }}%
        </div>

        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-top:1.5rem">
            <div style="background:rgba(255,255,255,0.1);border-radius:12px;padding:0.875rem">
                <div style="font-size:1.5rem;font-weight:700">{{ $r->bonnes_reponses }}</div>
                <div style="font-size:0.75rem;opacity:0.8">Bonnes réponses</div>
            </div>
            <div style="background:rgba(255,255,255,0.1);border-radius:12px;padding:0.875rem">
                <div style="font-size:1.5rem;font-weight:700">{{ $r->mauvaises_reponses }}</div>
                <div style="font-size:0.75rem;opacity:0.8">Mauvaises réponses</div>
            </div>
            <div style="background:rgba(255,255,255,0.1);border-radius:12px;padding:0.875rem">
                <div style="font-size:1.5rem;font-weight:700">{{ $r->sans_reponse }}</div>
                <div style="font-size:0.75rem;opacity:0.8">Sans réponse</div>
            </div>
        </div>

        @if($r->fraude_detectee)
        <div style="margin-top:1rem;background:rgba(220,38,38,0.3);border:1px solid rgba(220,38,38,0.5);border-radius:10px;padding:0.75rem;font-size:0.8rem">
            <i class="bi bi-exclamation-triangle-fill me-1"></i>
            Activité suspecte détectée ({{ $r->nb_evenements_antitriche }} événements)
        </div>
        @endif
    </div>
    @endif

    {{-- Détail des réponses --}}
    <div class="card-section">
        <div class="card-header-row"><h2>Détail des réponses</h2></div>
        <div style="padding:0 1.5rem">
            @foreach($tentative->reponsesEleves->sortBy('question.ordre') as $reponse)
            @php $q = $reponse->question; @endphp
            <div style="padding:1.25rem 0;border-bottom:1px solid #F3F4F6">
                <div style="display:flex;align-items:flex-start;gap:0.75rem;margin-bottom:0.75rem">
                    <span style="background:#EEF2FF;color:#4F46E5;font-size:0.75rem;font-weight:700;padding:3px 10px;border-radius:6px;flex-shrink:0">
                        Q{{ $q?->ordre }}
                    </span>
                    <span style="font-size:0.9rem;font-weight:500;flex:1">{{ $q?->enonce }}</span>
                    <span style="flex-shrink:0">
                        @if($reponse->est_correcte === true)
                            <span style="color:#059669;font-size:1.1rem"><i class="bi bi-check-circle-fill"></i></span>
                        @elseif($reponse->est_correcte === false)
                            <span style="color:#DC2626;font-size:1.1rem"><i class="bi bi-x-circle-fill"></i></span>
                        @else
                            <span style="color:#D97706;font-size:1.1rem"><i class="bi bi-clock-fill"></i></span>
                        @endif
                    </span>
                </div>

                {{-- Réponse donnée --}}
                <div style="margin-left:2.5rem">
                    <div style="font-size:0.78rem;color:#6B7280;margin-bottom:0.3rem">Votre réponse :</div>
                    @if($reponse->reponsePossible)
                        <div style="background:{{ $reponse->est_correcte ? '#D1FAE5' : '#FEE2E2' }};color:{{ $reponse->est_correcte ? '#065F46' : '#991B1B' }};padding:0.5rem 0.875rem;border-radius:8px;font-size:0.875rem;display:inline-block">
                            {{ $reponse->reponsePossible->texte }}
                        </div>
                    @elseif($reponse->texte_libre)
                        <div style="background:#F9FAFB;border:1px solid #E5E7EB;padding:0.75rem;border-radius:8px;font-size:0.875rem">
                            {{ $reponse->texte_libre }}
                        </div>
                        @if($reponse->commentaire_enseignant)
                        <div style="margin-top:0.5rem;background:#EEF2FF;padding:0.5rem 0.875rem;border-radius:8px;font-size:0.8rem;color:#4F46E5">
                            <i class="bi bi-chat-left-text me-1"></i>{{ $reponse->commentaire_enseignant }}
                        </div>
                        @endif
                    @elseif($reponse->temps_expire)
                        <div style="background:#F3F4F6;color:#6B7280;padding:0.5rem 0.875rem;border-radius:8px;font-size:0.875rem">
                            <i class="bi bi-clock me-1"></i> Temps écoulé — sans réponse
                        </div>
                    @endif

                    {{-- Points --}}
                    <div style="margin-top:0.5rem;font-size:0.78rem;color:#6B7280">
                        {{ $reponse->points_obtenus }} / {{ $q?->points }} point(s)
                        @if($reponse->temps_utilise_secondes)
                            · {{ $reponse->temps_utilise_secondes }}s utilisées
                        @endif
                    </div>

                    {{-- Explication --}}
                    @if($q?->explication)
                    <div style="margin-top:0.75rem;background:#FFFBEB;border:1px solid #FCD34D;padding:0.75rem;border-radius:8px;font-size:0.8rem;color:#92400E">
                        <i class="bi bi-lightbulb me-1"></i><strong>Explication :</strong> {{ $q->explication }}
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <div style="margin-top:1.25rem;display:flex;gap:1rem">
        <a href="{{ route('eleve.resultats') }}"
           style="flex:1;text-align:center;padding:0.75rem;background:#F3F4F6;color:#374151;border-radius:10px;text-decoration:none;font-weight:600;font-size:0.9rem">
            <i class="bi bi-arrow-left me-1"></i> Mes résultats
        </a>
        <a href="{{ route('eleve.devoirs') }}"
           style="flex:1;text-align:center;padding:0.75rem;background:#4F46E5;color:white;border-radius:10px;text-decoration:none;font-weight:600;font-size:0.9rem">
            <i class="bi bi-journals me-1"></i> Mes devoirs
        </a>
    </div>

</div>
@endsection