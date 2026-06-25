@extends('layouts.app')
@section('title', 'Devoir en cours')
@section('page-title', $devoir->titre)
@section('page-subtitle', $devoir->matiere?->nom . ' · Question ' . $questionCourante . '/' . count($questions))

@section('content')

{{-- BARRE DE PROGRESSION + TIMER ─────────────────────────────── --}}
<div style="background:white;border:1.5px solid #E5E7EB;border-radius:14px;padding:1rem 1.5rem;margin-bottom:1.5rem;display:flex;align-items:center;gap:1.5rem">

    {{-- Progression questions --}}
    <div style="flex:1">
        <div style="display:flex;justify-content:space-between;font-size:0.78rem;color:#6B7280;margin-bottom:0.4rem">
            <span>Progression</span>
            <span>{{ $questionCourante }}/{{ count($questions) }}</span>
        </div>
        <div style="height:6px;background:#E5E7EB;border-radius:3px;overflow:hidden">
            <div style="height:100%;background:#4F46E5;border-radius:3px;width:{{ round($questionCourante / count($questions) * 100) }}%;transition:width 0.3s"></div>
        </div>
    </div>

    {{-- Timer global --}}
    @if($devoir->duree_totale_minutes)
    <div style="text-align:center;min-width:80px">
        <div id="timerGlobal" style="font-size:1.5rem;font-weight:800;color:#4F46E5;font-family:monospace">
            {{ sprintf('%02d:%02d', floor($devoir->duree_totale_minutes), 0) }}
        </div>
        <div style="font-size:0.7rem;color:#6B7280">Temps restant</div>
    </div>
    @endif

    {{-- Antitriche warning --}}
    <div id="antitricheStatus" style="font-size:0.78rem;color:#059669;display:flex;align-items:center;gap:0.4rem">
        <i class="bi bi-shield-check"></i> Surveillance active
    </div>
</div>

{{-- NAVIGATION QUESTIONS (dots) ──────────────────────────────── --}}
<div style="display:flex;flex-wrap:wrap;gap:0.4rem;margin-bottom:1.5rem">
    @foreach($questions as $i => $q)
    @php
        $num = $i + 1;
        $repondue = in_array($q->id, $questionsRepondues);
        $courante = $num === $questionCourante;
    @endphp
    <a href="{{ route('eleve.passage.question', [$tentative->id, $num]) }}"
       style="width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:0.78rem;font-weight:600;text-decoration:none;border:2px solid {{ $courante ? '#4F46E5' : ($repondue ? '#059669' : '#E5E7EB') }};background:{{ $courante ? '#4F46E5' : ($repondue ? '#ECFDF5' : 'white') }};color:{{ $courante ? 'white' : ($repondue ? '#059669' : '#6B7280') }}">
        {{ $num }}
    </a>
    @endforeach
</div>

{{-- QUESTION COURANTE ────────────────────────────────────────── --}}
@php $question = $questions[$questionCourante - 1]; @endphp

<div style="display:grid;grid-template-columns:1fr 280px;gap:1.5rem">

    {{-- Carte question --}}
    <div class="card-section">
        <div style="padding:1.5rem">

            {{-- Numéro + type --}}
            <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:1.25rem">
                <div style="width:40px;height:40px;background:#EEF2FF;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:0.875rem;font-weight:800;color:#4F46E5;flex-shrink:0">
                    {{ $questionCourante }}
                </div>
                <div>
                    @php
                    $typeLabels = ['qcm'=>'QCM','vrai_faux'=>'Vrai / Faux','reponse_courte'=>'Réponse courte','redactionnel'=>'Rédactionnel'];
                    $typeColors = ['qcm'=>['#EEF2FF','#4F46E5'],'vrai_faux'=>['#ECFDF5','#059669'],'reponse_courte'=>['#FEF3C7','#D97706'],'redactionnel'=>['#FEE2E2','#DC2626']];
                    $tc = $typeColors[$question->type] ?? ['#F3F4F6','#6B7280'];
                    @endphp
                    <span style="background:{{ $tc[0] }};color:{{ $tc[1] }};font-size:0.72rem;font-weight:600;padding:3px 10px;border-radius:20px">
                        {{ $typeLabels[$question->type] ?? $question->type }}
                    </span>
                    <span style="font-size:0.75rem;color:#6B7280;margin-left:0.5rem">{{ $question->points }} pt(s)</span>
                </div>
                {{-- Timer par question --}}
                @if($devoir->temps_par_question_secondes)
                <div id="timerQuestion" style="margin-left:auto;font-size:1rem;font-weight:700;color:#D97706;font-family:monospace">
                    {{ sprintf('%02d:%02d', floor($devoir->temps_par_question_secondes / 60), $devoir->temps_par_question_secondes % 60) }}
                </div>
                @endif
            </div>

            {{-- Énoncé --}}
            <div style="font-size:1rem;font-weight:500;color:#111827;line-height:1.7;margin-bottom:1.5rem">
                {{ $question->enonce }}
            </div>

            {{-- Formulaire réponse --}}
            <form method="POST" action="{{ route('eleve.passage.repondre', $tentative->id) }}" id="formReponse">
                @csrf
                <input type="hidden" name="question_id" value="{{ $question->id }}">
                <input type="hidden" name="temps_utilise" id="tempsUtilise" value="0">

                {{-- QCM --}}
                @if($question->type === 'qcm')
                <div style="display:flex;flex-direction:column;gap:0.75rem;margin-bottom:1.5rem">
                    @foreach($question->reponsesPossibles->sortBy($devoir->reponses_aleatoires ? fn() => rand() : 'ordre') as $reponse)
                    <label style="cursor:pointer">
                        <input type="radio" name="reponse_possible_id" value="{{ $reponse->id }}"
                               {{ $reponseExistante?->reponse_possible_id === $reponse->id ? 'checked' : '' }}
                               style="display:none" class="reponse-radio">
                        <div class="reponse-option"
                             style="padding:0.875rem 1.25rem;border:2px solid #E5E7EB;border-radius:12px;font-size:0.9rem;color:#374151;transition:all 0.15s;{{ $reponseExistante?->reponse_possible_id === $reponse->id ? 'border-color:#4F46E5;background:#EEF2FF;color:#4F46E5;' : '' }}">
                            {{ $reponse->texte }}
                        </div>
                    </label>
                    @endforeach
                </div>

                {{-- Vrai/Faux --}}
                @elseif($question->type === 'vrai_faux')
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.5rem">
                    @foreach($question->reponsesPossibles->sortBy('ordre') as $reponse)
                    <label style="cursor:pointer">
                        <input type="radio" name="reponse_possible_id" value="{{ $reponse->id }}"
                               {{ $reponseExistante?->reponse_possible_id === $reponse->id ? 'checked' : '' }}
                               style="display:none" class="reponse-radio">
                        <div class="reponse-option"
                             style="padding:1.25rem;border:2px solid #E5E7EB;border-radius:12px;text-align:center;font-size:1rem;font-weight:700;color:#374151;transition:all 0.15s;{{ $reponseExistante?->reponse_possible_id === $reponse->id ? 'border-color:#4F46E5;background:#EEF2FF;color:#4F46E5;' : '' }}">
                            {{ $reponse->texte === 'Vrai' ? '✓ Vrai' : '✗ Faux' }}
                        </div>
                    </label>
                    @endforeach
                </div>

                {{-- Réponse courte --}}
                @elseif($question->type === 'reponse_courte')
                <div style="margin-bottom:1.5rem">
                    <input type="text" name="texte_libre"
                           value="{{ $reponseExistante?->texte_libre }}"
                           placeholder="Votre réponse..."
                           style="width:100%;padding:0.875rem;border:2px solid #E5E7EB;border-radius:12px;font-size:0.9rem;transition:border-color 0.2s"
                           onfocus="this.style.borderColor='#4F46E5'"
                           onblur="this.style.borderColor='#E5E7EB'">
                </div>

                {{-- Rédactionnel --}}
                @elseif($question->type === 'redactionnel')
                <div style="margin-bottom:1.5rem">
                    <textarea name="texte_libre" rows="6"
                              placeholder="Rédigez votre réponse ici..."
                              style="width:100%;padding:0.875rem;border:2px solid #E5E7EB;border-radius:12px;font-size:0.9rem;resize:vertical;transition:border-color 0.2s"
                              onfocus="this.style.borderColor='#4F46E5'"
                              onblur="this.style.borderColor='#E5E7EB'">{{ $reponseExistante?->texte_libre }}</textarea>
                </div>
                @endif

                {{-- Boutons navigation --}}
                <div style="display:flex;gap:1rem;justify-content:space-between">
                    @if($questionCourante > 1)
                    <a href="{{ route('eleve.passage.question', [$tentative->id, $questionCourante - 1]) }}"
                       style="padding:0.75rem 1.5rem;background:#F3F4F6;color:#374151;border-radius:10px;text-decoration:none;font-size:0.9rem;font-weight:600">
                        <i class="bi bi-arrow-left me-1"></i> Précédent
                    </a>
                    @else
                    <div></div>
                    @endif

                    @if($questionCourante < count($questions))
                    <button type="submit"
                            style="padding:0.75rem 2rem;background:#4F46E5;color:white;border:none;border-radius:10px;font-size:0.9rem;font-weight:600;cursor:pointer">
                        Suivant <i class="bi bi-arrow-right ms-1"></i>
                    </button>
                    @else
                    <button type="submit"
                            style="padding:0.75rem 2rem;background:#059669;color:white;border:none;border-radius:10px;font-size:0.9rem;font-weight:600;cursor:pointer">
                        <i class="bi bi-check2-circle me-1"></i> Terminer le devoir
                    </button>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Sidebar droite --}}
    <div>
        {{-- Résumé devoir --}}
        <div class="card-section" style="margin-bottom:1rem">
            <div style="padding:1.25rem">
                <div style="font-size:0.8rem;font-weight:600;color:#374151;margin-bottom:0.875rem">Résumé</div>
                @foreach([
                    ['bi-journals','Devoir',$devoir->titre],
                    ['bi-book','Matière',$devoir->matiere?->nom ?? '—'],
                    ['bi-question-circle','Questions',count($questions).' questions'],
                    ['bi-star','Note sur',$devoir->note_sur.' points'],
                ] as [$icon,$label,$value])
                <div style="display:flex;align-items:center;gap:0.5rem;padding:0.4rem 0;font-size:0.8rem">
                    <i class="bi {{ $icon }}" style="color:#6B7280;width:16px"></i>
                    <span style="color:#6B7280;flex:1">{{ $label }}</span>
                    <span style="font-weight:500;color:#374151">{{ Str::limit($value, 20) }}</span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Soumettre manuellement --}}
        <div class="card-section">
            <div style="padding:1.25rem">
                <div style="font-size:0.8rem;color:#6B7280;margin-bottom:0.75rem;line-height:1.5">
                    Vous pouvez soumettre le devoir à tout moment, même sans répondre à toutes les questions.
                </div>
                <button onclick="soumettreMaintenant()"
                        style="width:100%;padding:0.75rem;background:#FEE2E2;color:#DC2626;border:1.5px solid #FCA5A5;border-radius:10px;font-size:0.875rem;font-weight:600;cursor:pointer">
                    <i class="bi bi-stop-circle me-1"></i> Soumettre maintenant
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// ── ANTITRICHE ────────────────────────────────────────────
let nbSorties = 0;
const maxSorties = {{ $devoir->max_changements_onglet ?? 3 }};
const tentativeId = {{ $tentative->id }};
const csrfToken = '{{ csrf_token() }}';

function signalerEvenement(type) {
    fetch('/api/eleve/passage/' + tentativeId + '/antitriche', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ type, numero_question: {{ $questionCourante }} })
    });
}

document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
        nbSorties++;
        signalerEvenement('changement_onglet');
        document.getElementById('antitricheStatus').innerHTML =
            '<i class="bi bi-shield-exclamation" style="color:#DC2626"></i> <span style="color:#DC2626">Sortie détectée (' + nbSorties + '/' + maxSorties + ')</span>';

        if (nbSorties >= maxSorties && {{ $devoir->soumettre_auto_sortie ? 'true' : 'false' }}) {
            soumettreMaintenant();
        }
    }
});

// ── TIMER GLOBAL ──────────────────────────────────────────
@if($devoir->duree_totale_minutes)
let secondesRestantes = {{ $tentative->secondes_restantes ?? ($devoir->duree_totale_minutes * 60) }};
const timerGlobal = document.getElementById('timerGlobal');

const intervalGlobal = setInterval(function() {
    secondesRestantes--;
    if (secondesRestantes <= 0) {
        clearInterval(intervalGlobal);
        soumettreMaintenant();
        return;
    }
    const m = Math.floor(secondesRestantes / 60);
    const s = secondesRestantes % 60;
    timerGlobal.textContent = String(m).padStart(2,'0') + ':' + String(s).padStart(2,'0');
    if (secondesRestantes <= 60) timerGlobal.style.color = '#DC2626';
}, 1000);
@endif

// ── TIMER PAR QUESTION ────────────────────────────────────
@if($devoir->temps_par_question_secondes)
let secsQuestion = {{ $devoir->temps_par_question_secondes }};
const timerQ = document.getElementById('timerQuestion');
let secsUtilises = 0;

const intervalQuestion = setInterval(function() {
    secsQuestion--;
    secsUtilises++;
    document.getElementById('tempsUtilise').value = secsUtilises;
    if (secsQuestion <= 0) {
        clearInterval(intervalQuestion);
        signalerEvenement('temps_question_expire');
        document.getElementById('formReponse').submit();
        return;
    }
    const m = Math.floor(secsQuestion / 60);
    const s = secsQuestion % 60;
    timerQ.textContent = String(m).padStart(2,'0') + ':' + String(s).padStart(2,'0');
    if (secsQuestion <= 10) timerQ.style.color = '#DC2626';
}, 1000);
@endif

// ── SÉLECTION RÉPONSE ─────────────────────────────────────
document.querySelectorAll('.reponse-radio').forEach(radio => {
    radio.addEventListener('change', function() {
        document.querySelectorAll('.reponse-option').forEach(opt => {
            opt.style.borderColor = '#E5E7EB';
            opt.style.background  = 'white';
            opt.style.color       = '#374151';
        });
        this.nextElementSibling.style.borderColor = '#4F46E5';
        this.nextElementSibling.style.background  = '#EEF2FF';
        this.nextElementSibling.style.color        = '#4F46E5';
    });
});

// ── SOUMETTRE ─────────────────────────────────────────────
function soumettreMaintenant() {
    if (!confirm('Soumettre le devoir maintenant ?')) return;
    fetch('/devoir/' + tentativeId + '/soumettre', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
    })
    .then(r => r.json())
    .then(data => { if (data.redirect) window.location.href = data.redirect; });
}
</script>

@endsection