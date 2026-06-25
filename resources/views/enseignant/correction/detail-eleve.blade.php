@extends('layouts.app')
@section('title', 'Correction — ' . $tentative->eleve?->nom_complet)
@section('page-title', $tentative->eleve?->nom_complet)
@section('page-subtitle', $devoir->titre . ' · ' . $devoir->matiere?->nom)

@section('topbar-actions')
    <a href="{{ route('enseignant.correction.resultats', $devoir->id) }}"
       style="padding:0.5rem 1rem;background:#F3F4F6;color:#374151;border:1.5px solid #E5E7EB;border-radius:8px;font-size:0.875rem;font-weight:600;text-decoration:none">
        <i class="bi bi-arrow-left me-1"></i> Retour aux résultats
    </a>
@endsection

@section('content')

@if(session('success'))
<div style="background:#D1FAE5;border:1.5px solid #6EE7B7;color:#065F46;border-radius:12px;padding:1rem 1.25rem;margin-bottom:1.5rem;font-size:0.875rem">
    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
</div>
@endif

<div style="display:grid;grid-template-columns:1fr 300px;gap:1.5rem">

    {{-- Réponses + correction --}}
    <div>
        <form method="POST" action="{{ route('enseignant.correction.tout', $tentative->id) }}" id="formCorrection">
            @csrf
            <div class="card-section">
                <div class="card-header-row">
                    <h2>Réponses de l'élève</h2>
                    @if($reponses->where('necessite_correction', true)->count() > 0)
                    <button type="submit" form="formCorrection"
                            style="padding:0.5rem 1.25rem;background:#059669;color:white;border:none;border-radius:8px;font-size:0.875rem;font-weight:600;cursor:pointer">
                        <i class="bi bi-check2-all me-1"></i>
                        Sauvegarder toutes les corrections
                    </button>
                    @endif
                </div>

                @foreach($reponses as $index => $r)
                <div style="padding:1.5rem;border-bottom:1px solid #E5E7EB;{{ $r['necessite_correction'] ? 'background:#FFFBEB' : '' }}">

                    {{-- En-tête question --}}
                    <div style="display:flex;align-items:flex-start;gap:0.75rem;margin-bottom:1rem">
                        <span style="background:{{ $r['necessite_correction'] ? '#FEF3C7' : '#EEF2FF' }};color:{{ $r['necessite_correction'] ? '#D97706' : '#4F46E5' }};font-size:0.75rem;font-weight:700;padding:3px 10px;border-radius:6px;flex-shrink:0">
                            Q{{ $r['ordre'] }}
                        </span>
                        <div style="flex:1">
                            <div style="font-size:0.9rem;font-weight:500;color:#111827">{{ $r['enonce'] }}</div>
                            <div style="font-size:0.75rem;color:#6B7280;margin-top:2px">
                                Type : {{ match($r['type']) {
                                    'qcm'            => 'QCM',
                                    'vrai_faux'      => 'Vrai/Faux',
                                    'reponse_courte' => 'Réponse courte',
                                    'redactionnel'   => 'Rédactionnel',
                                    default          => $r['type']
                                } }}
                                · {{ $r['points_max'] }} pt(s) max
                                @if($r['temps_utilise']) · {{ $r['temps_utilise'] }}s utilisées @endif
                            </div>
                        </div>
                        <span style="font-size:0.875rem;font-weight:600;color:{{ $r['est_correcte'] === true ? '#059669' : ($r['est_correcte'] === false ? '#DC2626' : '#D97706') }}">
                            {{ $r['points_obtenus'] }}/{{ $r['points_max'] }}
                        </span>
                    </div>

                    {{-- Réponse donnée --}}
                    <div style="margin-left:2.5rem">
                        <div style="font-size:0.78rem;color:#6B7280;margin-bottom:0.3rem;font-weight:500">Réponse de l'élève :</div>
                        @if($r['temps_expire'] && !$r['reponse_donnee'])
                        <div style="background:#F3F4F6;color:#6B7280;padding:0.75rem;border-radius:8px;font-size:0.875rem;font-style:italic">
                            <i class="bi bi-clock me-1"></i>Temps écoulé — sans réponse
                        </div>
                        @elseif($r['reponse_donnee'])
                        <div style="background:{{ $r['necessite_correction'] ? '#FFFBEB' : ($r['est_correcte'] ? '#F0FDF4' : '#FFF5F5') }};border:1px solid {{ $r['necessite_correction'] ? '#FCD34D' : ($r['est_correcte'] ? '#BBF7D0' : '#FECACA') }};padding:0.875rem;border-radius:8px;font-size:0.875rem;line-height:1.6">
                            {{ $r['reponse_donnee'] }}
                        </div>
                        @else
                        <div style="background:#F3F4F6;color:#6B7280;padding:0.75rem;border-radius:8px;font-size:0.875rem;font-style:italic">
                            Sans réponse
                        </div>
                        @endif

                        {{-- Réponse correcte pour QCM --}}
                        @if(in_array($r['type'], ['qcm','vrai_faux']) && $r['reponse_correcte'])
                        <div style="margin-top:0.5rem;font-size:0.78rem;color:#059669;background:#F0FDF4;padding:4px 10px;border-radius:6px;display:inline-block">
                            <i class="bi bi-check2 me-1"></i>Bonne réponse : {{ $r['reponse_correcte'] }}
                        </div>
                        @endif

                        {{-- Explication --}}
                        @if($r['explication'])
                        <div style="margin-top:0.5rem;background:#EEF2FF;border:1px solid #C7D2FE;padding:0.6rem 0.875rem;border-radius:8px;font-size:0.78rem;color:#4338CA">
                            <i class="bi bi-lightbulb me-1"></i>{{ $r['explication'] }}
                        </div>
                        @endif

                        {{-- Zone de correction manuelle --}}
                        @if($r['necessite_correction'])
                        <input type="hidden" name="corrections[{{ $index }}][reponse_id]" value="{{ $r['id'] }}">
                        <div style="margin-top:1rem;padding:1rem;background:white;border:1.5px solid #FCD34D;border-radius:10px">
                            <div style="font-size:0.78rem;font-weight:600;color:#D97706;margin-bottom:0.75rem">
                                ✏️ Correction requise
                            </div>
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;margin-bottom:0.75rem">
                                <div>
                                    <label style="font-size:0.75rem;font-weight:500;display:block;margin-bottom:0.3rem">
                                        Points obtenus (max {{ $r['points_max'] }})
                                    </label>
                                    <input type="number"
                                           name="corrections[{{ $index }}][points_obtenus]"
                                           min="0" max="{{ $r['points_max'] }}" step="0.5"
                                           value="{{ $r['points_obtenus'] }}"
                                           style="width:100%;padding:0.5rem;border:1.5px solid #E5E7EB;border-radius:8px;font-size:0.875rem"
                                           required>
                                </div>
                                <div>
                                    <label style="font-size:0.75rem;font-weight:500;display:block;margin-bottom:0.3rem">Évaluation</label>
                                    <select name="corrections[{{ $index }}][est_correcte]"
                                            style="width:100%;padding:0.5rem;border:1.5px solid #E5E7EB;border-radius:8px;font-size:0.875rem"
                                            required>
                                        <option value="">Choisir...</option>
                                        <option value="1" {{ $r['est_correcte'] === true ? 'selected' : '' }}>✅ Correct</option>
                                        <option value="0" {{ $r['est_correcte'] === false ? 'selected' : '' }}>❌ Incorrect</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label style="font-size:0.75rem;font-weight:500;display:block;margin-bottom:0.3rem">
                                    Commentaire pour l'élève <span style="color:#6B7280;font-weight:400">(optionnel)</span>
                                </label>
                                <textarea name="corrections[{{ $index }}][commentaire]"
                                          rows="2"
                                          placeholder="Feedback, conseil, explication..."
                                          style="width:100%;padding:0.5rem;border:1.5px solid #E5E7EB;border-radius:8px;font-size:0.875rem;resize:vertical">{{ $r['commentaire'] }}</textarea>
                            </div>
                        </div>
                        @elseif($r['commentaire'])
                        <div style="margin-top:0.5rem;background:#EEF2FF;padding:0.5rem 0.875rem;border-radius:8px;font-size:0.78rem;color:#4F46E5">
                            <i class="bi bi-chat-left-text me-1"></i>{{ $r['commentaire'] }}
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach

                {{-- Bouton sauvegarder en bas --}}
                @if($reponses->where('necessite_correction', true)->count() > 0)
                <div style="padding:1.25rem;text-align:center">
                    <button type="submit"
                            style="padding:0.875rem 2rem;background:#059669;color:white;border:none;border-radius:12px;font-size:0.95rem;font-weight:600;cursor:pointer">
                        <i class="bi bi-check2-all me-2"></i>
                        Sauvegarder et notifier l'élève
                    </button>
                </div>
                @endif
            </div>
        </form>
    </div>

    {{-- Sidebar : résumé + antitriche --}}
    <div>

        {{-- Note actuelle --}}
        <div class="card-section" style="margin-bottom:1rem">
            <div class="card-header-row"><h2>Note actuelle</h2></div>
            @if($tentative->resultat)
            @php $r = $tentative->resultat; @endphp
            <div style="padding:1.25rem;text-align:center">
                <div style="font-size:3rem;font-weight:800;color:#4F46E5;line-height:1">{{ $r->note_finale }}</div>
                <div style="font-size:1rem;color:#6B7280;margin-bottom:0.75rem">/ {{ $r->note_sur }}</div>
                @php $c = $r->pourcentage >= 75 ? ['#D1FAE5','#065F46'] : ($r->pourcentage >= 50 ? ['#FEF3C7','#92400E'] : ['#FEE2E2','#991B1B']); @endphp
                <span style="background:{{ $c[0] }};color:{{ $c[1] }};font-size:0.875rem;font-weight:700;padding:5px 16px;border-radius:20px">
                    {{ $r->mention }} — {{ $r->pourcentage }}%
                </span>
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:0.5rem;margin-top:1rem">
                    <div style="text-align:center;padding:0.5rem;background:#F0FDF4;border-radius:8px">
                        <div style="font-size:1.1rem;font-weight:700;color:#059669">{{ $r->bonnes_reponses }}</div>
                        <div style="font-size:0.65rem;color:#059669">Bonnes</div>
                    </div>
                    <div style="text-align:center;padding:0.5rem;background:#FEF2F2;border-radius:8px">
                        <div style="font-size:1.1rem;font-weight:700;color:#DC2626">{{ $r->mauvaises_reponses }}</div>
                        <div style="font-size:0.65rem;color:#DC2626">Mauvaises</div>
                    </div>
                    <div style="text-align:center;padding:0.5rem;background:#F3F4F6;border-radius:8px">
                        <div style="font-size:1.1rem;font-weight:700;color:#6B7280">{{ $r->sans_reponse }}</div>
                        <div style="font-size:0.65rem;color:#6B7280">Sans rép.</div>
                    </div>
                </div>
            </div>
            @else
            <div style="padding:1.25rem;text-align:center;color:#6B7280;font-size:0.875rem">
                Note non encore calculée.
            </div>
            @endif
        </div>

        {{-- Antitriche --}}
        @if($evenements->isNotEmpty())
        <div class="card-section">
            <div class="card-header-row">
                <h2><i class="bi bi-shield-exclamation me-1" style="color:#DC2626"></i>Antitriche</h2>
                <span style="background:#FEE2E2;color:#991B1B;font-size:0.72rem;font-weight:600;padding:2px 8px;border-radius:10px">
                    {{ $evenements->count() }} événement(s)
                </span>
            </div>
            <div style="padding:0 1.25rem">
                @foreach($evenements as $e)
                <div style="padding:0.75rem 0;border-bottom:1px solid #F3F4F6;display:flex;align-items:flex-start;gap:0.5rem">
                    <i class="bi bi-{{ $e['suspicieux'] ? 'exclamation-triangle-fill' : 'info-circle' }}"
                       style="color:{{ $e['suspicieux'] ? '#DC2626' : '#6B7280' }};font-size:0.85rem;flex-shrink:0;margin-top:2px"></i>
                    <div>
                        <div style="font-size:0.8rem;font-weight:500">{{ $e['label'] }}</div>
                        <div style="font-size:0.72rem;color:#6B7280">
                            {{ $e['question'] ? 'Q'.$e['question'].' · ' : '' }}{{ $e['survenu_le'] }}
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>

@endsection