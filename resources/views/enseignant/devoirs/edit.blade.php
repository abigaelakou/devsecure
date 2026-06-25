@extends('layouts.app')
@section('title', 'Modifier · ' . $devoir->titre)
@section('page-title', $devoir->titre)
@section('page-subtitle', $devoir->matiere?->nom . ' · ' . $devoir->classe?->nom . ' · ' . $devoir->questions->count() . ' question(s)')

@section('topbar-actions')
    @if($devoir->statut === 'brouillon')
    <form method="POST" action="{{ route('enseignant.devoirs.publier', $devoir->id) }}" style="display:inline">
        @csrf
        <button type="submit"
                style="padding:0.5rem 1.25rem;background:#059669;color:white;border:none;border-radius:8px;font-size:0.875rem;font-weight:600;cursor:pointer"
                {{ $devoir->questions->count() === 0 ? 'disabled title=Ajoutez au moins une question' : '' }}>
            <i class="bi bi-send me-1"></i> Publier le devoir
        </button>
    </form>
    @else
    <span style="background:#D1FAE5;color:#065F46;font-size:0.8rem;font-weight:600;padding:6px 14px;border-radius:8px">
        <i class="bi bi-check-circle me-1"></i> Publié
    </span>
    @endif
    <a href="{{ route('enseignant.devoirs.index') }}"
       style="padding:0.5rem 1rem;background:#F3F4F6;color:#374151;border:1.5px solid #E5E7EB;border-radius:8px;font-size:0.875rem;font-weight:600;text-decoration:none">
        <i class="bi bi-arrow-left me-1"></i> Retour
    </a>
@endsection

@section('content')

@if(session('success'))
<div style="background:#D1FAE5;border:1.5px solid #6EE7B7;color:#065F46;border-radius:12px;padding:1rem 1.25rem;margin-bottom:1.5rem;font-size:0.875rem">
    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
</div>
@endif
@if(session('error'))
<div style="background:#FEE2E2;border:1.5px solid #FCA5A5;color:#991B1B;border-radius:12px;padding:1rem 1.25rem;margin-bottom:1.5rem;font-size:0.875rem">
    <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
</div>
@endif

<div style="display:grid;grid-template-columns:1fr 380px;gap:1.5rem">

    {{-- LISTE DES QUESTIONS ──────────────────────────────────── --}}
    <div>
        {{-- Résumé points --}}
        @php $totalPoints = $devoir->questions->sum('points'); @endphp
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem">
            <div style="font-size:0.875rem;color:#6B7280">
                <strong>{{ $devoir->questions->count() }}</strong> question(s) ·
                <strong>{{ $totalPoints }}</strong> pts au total
                @if($totalPoints != $devoir->note_sur)
                <span style="color:#DC2626"> ≠ note sur {{ $devoir->note_sur }}</span>
                @else
                <span style="color:#059669"> ✓ cohérent avec note/{{ $devoir->note_sur }}</span>
                @endif
            </div>
            @if($devoir->questions->count() > 1)
            <span style="font-size:0.78rem;color:#6B7280">
                <i class="bi bi-grip-vertical me-1"></i>Glissez pour réordonner
            </span>
            @endif
        </div>

        @if($devoir->questions->isEmpty())
        <div class="card-section" style="padding:3rem;text-align:center;color:#6B7280">
            <i class="bi bi-question-circle" style="font-size:3rem;display:block;margin-bottom:1rem;color:#E5E7EB"></i>
            <div style="font-size:1rem;font-weight:500;margin-bottom:0.5rem">Aucune question</div>
            <div style="font-size:0.875rem">Utilisez le formulaire à droite pour ajouter la première question.</div>
        </div>
        @else

        {{-- Questions liste --}}
        <div id="questionsList">
            @foreach($devoir->questions->sortBy('ordre') as $question)
            <div class="question-card" data-id="{{ $question->id }}"
                 style="background:white;border:1.5px solid #E5E7EB;border-radius:14px;padding:1.25rem;margin-bottom:0.875rem;transition:all 0.2s">

                {{-- Header question --}}
                <div style="display:flex;align-items:flex-start;gap:0.875rem">
                    {{-- Drag handle --}}
                    <div class="drag-handle" style="cursor:grab;color:#D1D5DB;padding-top:3px;flex-shrink:0">
                        <i class="bi bi-grip-vertical" style="font-size:1.1rem"></i>
                    </div>

                    {{-- Numéro + type --}}
                    <div style="flex-shrink:0">
                        <div style="width:32px;height:32px;background:#EEF2FF;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:0.78rem;font-weight:700;color:#4F46E5">
                            {{ $question->ordre }}
                        </div>
                    </div>

                    {{-- Contenu --}}
                    <div style="flex:1;min-width:0">
                        <div style="font-size:0.9rem;font-weight:500;color:#111827;margin-bottom:0.5rem;line-height:1.5">
                            {{ $question->enonce }}
                        </div>

                        {{-- Badges type + points --}}
                        <div style="display:flex;flex-wrap:wrap;gap:0.5rem;margin-bottom:0.75rem">
                            @php
                            $typeLabels = [
                                'qcm'            => ['QCM','#EEF2FF','#4F46E5'],
                                'vrai_faux'      => ['Vrai/Faux','#F0FDF4','#059669'],
                                'reponse_courte' => ['Réponse courte','#FEF3C7','#D97706'],
                                'redactionnel'   => ['Rédactionnel','#FEE2E2','#DC2626'],
                            ];
                            $tl = $typeLabels[$question->type] ?? [$question->type,'#F3F4F6','#6B7280'];
                            @endphp
                            <span style="background:{{ $tl[1] }};color:{{ $tl[2] }};font-size:0.72rem;font-weight:600;padding:3px 10px;border-radius:20px">
                                {{ $tl[0] }}
                            </span>
                            <span style="background:#F3F4F6;color:#374151;font-size:0.72rem;font-weight:600;padding:3px 10px;border-radius:20px">
                                {{ $question->points }} pt(s)
                            </span>
                        </div>

                        {{-- Réponses possibles --}}
                        @if($question->reponsesPossibles->isNotEmpty())
                        <div style="display:flex;flex-wrap:wrap;gap:0.4rem">
                            @foreach($question->reponsesPossibles->sortBy('ordre') as $r)
                            <span style="background:{{ $r->est_correcte ? '#D1FAE5' : '#F3F4F6' }};color:{{ $r->est_correcte ? '#065F46' : '#374151' }};font-size:0.78rem;padding:3px 10px;border-radius:8px;border:1px solid {{ $r->est_correcte ? '#A7F3D0' : '#E5E7EB' }}">
                                {{ $r->est_correcte ? '✓ ' : '' }}{{ Str::limit($r->texte, 40) }}
                            </span>
                            @endforeach
                        </div>
                        @endif

                        {{-- Explication --}}
                        @if($question->explication)
                        <div style="margin-top:0.5rem;font-size:0.78rem;color:#6B7280;background:#F9FAFB;padding:5px 10px;border-radius:6px;border-left:3px solid #4F46E5">
                            💡 {{ $question->explication }}
                        </div>
                        @endif
                    </div>

                    {{-- Actions --}}
                    <div style="display:flex;gap:0.4rem;flex-shrink:0">
                        <button onclick="ouvrirEdition({{ $question->id }})"
                                style="width:30px;height:30px;border:1px solid #E5E7EB;border-radius:7px;background:white;color:#4F46E5;cursor:pointer;font-size:0.9rem"
                                title="Modifier">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button onclick="dupliquerQuestion({{ $question->id }})"
                                style="width:30px;height:30px;border:1px solid #E5E7EB;border-radius:7px;background:white;color:#6B7280;cursor:pointer;font-size:0.9rem"
                                title="Dupliquer">
                            <i class="bi bi-copy"></i>
                        </button>
                        <form method="POST" action="{{ route('enseignant.questions.destroy', [$devoir->id, $question->id]) }}"
                              onsubmit="return confirm('Supprimer cette question ?')" style="display:inline">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    style="width:30px;height:30px;border:1px solid #FCA5A5;border-radius:7px;background:white;color:#DC2626;cursor:pointer;font-size:0.9rem"
                                    title="Supprimer">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- FORMULAIRE AJOUT QUESTION ────────────────────────────── --}}
    <div id="sidebarQuestion">
        <div class="card-section" style="position:sticky;top:80px">
            <div class="card-header-row">
                <h2 id="formTitre">Nouvelle question</h2>
                <button id="btnAnnulerEdit" onclick="annulerEdition()" style="display:none;font-size:0.78rem;color:#6B7280;background:none;border:none;cursor:pointer">
                    Annuler
                </button>
            </div>
            <div style="padding:1.25rem">
                <form method="POST" id="formQuestion"
                      action="{{ route('enseignant.questions.store', $devoir->id) }}">
                    @csrf
                    <input type="hidden" name="_method" id="formMethod" value="POST">
                    <input type="hidden" name="question_id" id="formQuestionId" value="">

                    {{-- Type --}}
                    <div style="margin-bottom:1rem">
                        <label style="font-size:0.8rem;font-weight:600;display:block;margin-bottom:0.5rem">Type de question</label>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.5rem" id="typeButtons">
                            @foreach([
                                ['qcm','QCM','bi-list-check','#4F46E5','#EEF2FF'],
                                ['vrai_faux','Vrai/Faux','bi-toggle-on','#059669','#ECFDF5'],
                                ['reponse_courte','Réponse courte','bi-cursor-text','#D97706','#FEF3C7'],
                                ['redactionnel','Rédactionnel','bi-pencil-square','#DC2626','#FEF2F2'],
                            ] as [$val,$label,$icon,$color,$bg])
                            <label style="cursor:pointer">
                                <input type="radio" name="type" value="{{ $val }}" class="type-radio" style="display:none"
                                       {{ $val === 'qcm' ? 'checked' : '' }}>
                                <div class="type-btn" data-type="{{ $val }}"
                                     style="padding:0.6rem 0.5rem;border:2px solid {{ $val === 'qcm' ? $color : '#E5E7EB' }};background:{{ $val === 'qcm' ? $bg : 'white' }};border-radius:10px;text-align:center;transition:all 0.15s">
                                    <i class="bi {{ $icon }}" style="color:{{ $val === 'qcm' ? $color : '#6B7280' }};font-size:1rem;display:block;margin-bottom:2px"></i>
                                    <span style="font-size:0.72rem;font-weight:600;color:{{ $val === 'qcm' ? $color : '#6B7280' }}">{{ $label }}</span>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Énoncé --}}
                    <div style="margin-bottom:1rem">
                        <label style="font-size:0.8rem;font-weight:600;display:block;margin-bottom:0.4rem">
                            Énoncé <span style="color:#DC2626">*</span>
                        </label>
                        <textarea name="enonce" id="enonce" rows="3" required
                                  placeholder="Rédigez votre question ici..."
                                  style="width:100%;padding:0.75rem;border:1.5px solid #E5E7EB;border-radius:10px;font-size:0.875rem;resize:vertical"></textarea>
                    </div>

                    {{-- Points --}}
                    <div style="margin-bottom:1rem">
                        <label style="font-size:0.8rem;font-weight:600;display:block;margin-bottom:0.4rem">Points</label>
                        <input type="number" name="points" id="points" value="1" min="0.5" max="100" step="0.5" required
                               style="width:100%;padding:0.75rem;border:1.5px solid #E5E7EB;border-radius:10px;font-size:0.875rem">
                    </div>

                    {{-- Zone QCM --}}
                    <div id="zoneQcm" style="margin-bottom:1rem">
                        <label style="font-size:0.8rem;font-weight:600;display:block;margin-bottom:0.5rem">
                            Réponses <span style="color:#6B7280;font-weight:400">(cochez la/les correcte(s))</span>
                        </label>
                        <div id="reponsesQcm">
                            @for($i = 0; $i < 4; $i++)
                            <div class="reponse-row" style="display:flex;gap:0.5rem;align-items:center;margin-bottom:0.5rem">
                                <input type="checkbox" name="reponses[{{ $i }}][est_correcte]" value="1"
                                       style="width:16px;height:16px;flex-shrink:0;cursor:pointer">
                                <input type="text" name="reponses[{{ $i }}][texte]"
                                       placeholder="Réponse {{ chr(65+$i) }}..."
                                       style="flex:1;padding:0.5rem 0.75rem;border:1.5px solid #E5E7EB;border-radius:8px;font-size:0.8rem">
                                @if($i >= 2)
                                <button type="button" onclick="this.parentElement.remove()"
                                        style="width:28px;height:28px;border:1px solid #FCA5A5;border-radius:6px;background:white;color:#DC2626;cursor:pointer;font-size:0.85rem;flex-shrink:0">
                                    <i class="bi bi-x"></i>
                                </button>
                                @endif
                            </div>
                            @endfor
                        </div>
                        <button type="button" onclick="ajouterReponse()"
                                style="font-size:0.78rem;color:#4F46E5;background:none;border:none;cursor:pointer;padding:0;margin-top:4px">
                            <i class="bi bi-plus-circle me-1"></i>Ajouter une réponse
                        </button>
                    </div>

                    {{-- Zone Vrai/Faux --}}
                    <div id="zoneVraiFaux" style="margin-bottom:1rem;display:none">
                        <label style="font-size:0.8rem;font-weight:600;display:block;margin-bottom:0.5rem">Réponse correcte</label>
                        <div style="display:flex;gap:0.75rem">
                            <label style="flex:1;cursor:pointer">
                                <input type="radio" name="reponse_correcte_vf" value="vrai" style="display:none">
                                <div class="vf-btn" data-val="vrai"
                                     style="padding:0.75rem;border:2px solid #E5E7EB;border-radius:10px;text-align:center;font-size:0.875rem;font-weight:600;color:#6B7280;transition:all 0.15s">
                                    ✓ Vrai
                                </div>
                            </label>
                            <label style="flex:1;cursor:pointer">
                                <input type="radio" name="reponse_correcte_vf" value="faux" style="display:none">
                                <div class="vf-btn" data-val="faux"
                                     style="padding:0.75rem;border:2px solid #E5E7EB;border-radius:10px;text-align:center;font-size:0.875rem;font-weight:600;color:#6B7280;transition:all 0.15s">
                                    ✗ Faux
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- Zone Réponse courte --}}
                    <div id="zoneReponseCorte" style="margin-bottom:1rem;display:none">
                        <label style="font-size:0.8rem;font-weight:600;display:block;margin-bottom:0.4rem">
                            Réponse attendue <span style="color:#6B7280;font-weight:400">(optionnel — pour correction auto)</span>
                        </label>
                        <input type="text" name="reponse_courte_attendue" id="reponseCorte"
                               placeholder="Ex: Paris, 42, Charles de Gaulle..."
                               style="width:100%;padding:0.75rem;border:1.5px solid #E5E7EB;border-radius:10px;font-size:0.875rem">
                    </div>

                    {{-- Zone Rédactionnel --}}
                    <div id="zoneRedactionnel" style="margin-bottom:1rem;display:none">
                        <div style="background:#FEF3C7;border:1px solid #FCD34D;border-radius:10px;padding:0.75rem;font-size:0.8rem;color:#92400E">
                            <i class="bi bi-info-circle me-1"></i>
                            Les questions rédactionnelles nécessitent une <strong>correction manuelle</strong> par l'enseignant.
                        </div>
                    </div>

                    {{-- Explication --}}
                    <div style="margin-bottom:1.25rem">
                        <label style="font-size:0.8rem;font-weight:600;display:block;margin-bottom:0.4rem">
                            Explication <span style="color:#6B7280;font-weight:400">(affichée après correction)</span>
                        </label>
                        <textarea name="explication" id="explication" rows="2"
                                  placeholder="Expliquez pourquoi cette réponse est correcte..."
                                  style="width:100%;padding:0.75rem;border:1.5px solid #E5E7EB;border-radius:10px;font-size:0.875rem;resize:vertical"></textarea>
                    </div>

                    <button type="submit" id="btnSubmit"
                            style="width:100%;padding:0.875rem;background:#4F46E5;color:white;border:none;border-radius:12px;font-size:0.9rem;font-weight:600;cursor:pointer;transition:background 0.2s">
                        <i class="bi bi-plus-lg me-1"></i> Ajouter la question
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// ── GESTION DES TYPES ─────────────────────────────────────
const typeColors = {
    qcm:            ['#4F46E5','#EEF2FF'],
    vrai_faux:      ['#059669','#ECFDF5'],
    reponse_courte: ['#D97706','#FEF3C7'],
    redactionnel:   ['#DC2626','#FEF2F2'],
};

document.querySelectorAll('.type-radio').forEach(radio => {
    radio.addEventListener('change', function() {
        switchType(this.value);
    });
});

function switchType(type) {
    // Mise à jour boutons
    document.querySelectorAll('.type-btn').forEach(btn => {
        const t = btn.dataset.type;
        const [color, bg] = typeColors[t] || ['#6B7280','white'];
        const actif = t === type;
        btn.style.borderColor = actif ? color : '#E5E7EB';
        btn.style.background  = actif ? bg    : 'white';
        btn.querySelector('i').style.color   = actif ? color : '#6B7280';
        btn.querySelector('span').style.color= actif ? color : '#6B7280';
    });

    // Affichage zones
    document.getElementById('zoneQcm').style.display        = type === 'qcm'            ? 'block' : 'none';
    document.getElementById('zoneVraiFaux').style.display   = type === 'vrai_faux'      ? 'block' : 'none';
    document.getElementById('zoneReponseCorte').style.display = type === 'reponse_courte' ? 'block' : 'none';
    document.getElementById('zoneRedactionnel').style.display = type === 'redactionnel'   ? 'block' : 'none';
}

// ── VRAI/FAUX TOGGLE ─────────────────────────────────────
document.querySelectorAll('input[name="reponse_correcte_vf"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.querySelectorAll('.vf-btn').forEach(btn => {
            const actif = btn.dataset.val === this.value;
            btn.style.borderColor = actif ? '#059669' : '#E5E7EB';
            btn.style.background  = actif ? '#ECFDF5' : 'white';
            btn.style.color       = actif ? '#059669' : '#6B7280';
        });
    });
});

// ── AJOUTER RÉPONSE QCM ───────────────────────────────────
let nbReponses = 4;
function ajouterReponse() {
    if (nbReponses >= 6) { alert('Maximum 6 réponses.'); return; }
    const container = document.getElementById('reponsesQcm');
    const div = document.createElement('div');
    div.className = 'reponse-row';
    div.style.cssText = 'display:flex;gap:0.5rem;align-items:center;margin-bottom:0.5rem';
    div.innerHTML = `
        <input type="checkbox" name="reponses[${nbReponses}][est_correcte]" value="1"
               style="width:16px;height:16px;flex-shrink:0;cursor:pointer">
        <input type="text" name="reponses[${nbReponses}][texte]"
               placeholder="Réponse ${String.fromCharCode(65+nbReponses)}..."
               style="flex:1;padding:0.5rem 0.75rem;border:1.5px solid #E5E7EB;border-radius:8px;font-size:0.8rem">
        <button type="button" onclick="this.parentElement.remove()"
                style="width:28px;height:28px;border:1px solid #FCA5A5;border-radius:6px;background:white;color:#DC2626;cursor:pointer;font-size:0.85rem;flex-shrink:0">
            <i class="bi bi-x"></i>
        </button>`;
    container.appendChild(div);
    nbReponses++;
}

// ── ÉDITION D'UNE QUESTION EXISTANTE ─────────────────────
function ouvrirEdition(questionId) {
    // Scroll vers le formulaire
    document.getElementById('sidebarQuestion').scrollIntoView({ behavior:'smooth' });
    document.getElementById('formTitre').textContent = 'Modifier la question';
    document.getElementById('btnSubmit').innerHTML   = '<i class="bi bi-check2 me-1"></i> Sauvegarder';
    document.getElementById('btnAnnulerEdit').style.display = 'inline';

    // Changer l'action du formulaire
    const form = document.getElementById('formQuestion');
    form.action = `/enseignant/devoirs/{{ $devoir->id }}/questions/${questionId}`;
    document.getElementById('formMethod').value    = 'PUT';
    document.getElementById('formQuestionId').value = questionId;

    // Trouver les données de la question dans le DOM
    const card = document.querySelector(`.question-card[data-id="${questionId}"]`);
    if (!card) return;

    const enonce = card.querySelector('[style*="font-weight:500"]')?.textContent?.trim();
    if (enonce) document.getElementById('enonce').value = enonce;
}

function annulerEdition() {
    const form = document.getElementById('formQuestion');
    form.action = '{{ route("enseignant.questions.store", $devoir->id) }}';
    document.getElementById('formMethod').value      = 'POST';
    document.getElementById('formQuestionId').value  = '';
    document.getElementById('formTitre').textContent = 'Nouvelle question';
    document.getElementById('btnSubmit').innerHTML   = '<i class="bi bi-plus-lg me-1"></i> Ajouter la question';
    document.getElementById('btnAnnulerEdit').style.display = 'none';
    document.getElementById('enonce').value = '';
    document.getElementById('explication').value = '';
}

// ── DUPLIQUER ─────────────────────────────────────────────
function dupliquerQuestion(questionId) {
    if (!confirm('Dupliquer cette question ?')) return;
    fetch(`/enseignant/devoirs/{{ $devoir->id }}/questions/${questionId}/dupliquer`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type':'application/json' }
    }).then(r => r.ok ? location.reload() : alert('Erreur'));
}
</script>

@endsection