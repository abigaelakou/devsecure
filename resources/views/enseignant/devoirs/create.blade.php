@extends('layouts.app')
@section('title', 'Nouveau devoir')
@section('page-title', 'Créer un devoir')
@section('page-subtitle', 'Étape 1 : Paramètres du devoir')

@section('content')
<div style="max-width:760px;margin:0 auto">
    <div class="card-section">
        <div class="card-header-row"><h2>Informations générales</h2></div>
        <div style="padding:1.5rem">
            <form method="POST" action="{{ route('enseignant.devoirs.store') }}">
                @csrf

                {{-- Titre --}}
                <div style="margin-bottom:1.25rem">
                    <label style="font-size:0.875rem;font-weight:500;display:block;margin-bottom:0.4rem">Titre du devoir <span style="color:#DC2626">*</span></label>
                    <input type="text" name="titre" value="{{ old('titre') }}" required
                           placeholder="Ex: Devoir N°1 — Algèbre"
                           style="width:100%;padding:0.75rem;border:1.5px solid #E5E7EB;border-radius:10px;font-size:0.9rem">
                </div>

                {{-- Description --}}
                <div style="margin-bottom:1.25rem">
                    <label style="font-size:0.875rem;font-weight:500;display:block;margin-bottom:0.4rem">Description <span style="color:#6B7280;font-weight:400">(optionnel)</span></label>
                    <textarea name="description" rows="2" placeholder="Brève description du devoir..."
                              style="width:100%;padding:0.75rem;border:1.5px solid #E5E7EB;border-radius:10px;font-size:0.9rem;resize:vertical">{{ old('description') }}</textarea>
                </div>

                {{-- Matière + Classe --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.25rem">
                    <div>
                        <label style="font-size:0.875rem;font-weight:500;display:block;margin-bottom:0.4rem">Matière <span style="color:#DC2626">*</span></label>
                        <select name="matiere_id" required style="width:100%;padding:0.75rem;border:1.5px solid #E5E7EB;border-radius:10px;font-size:0.9rem">
                            <option value="">Choisir...</option>
                            @foreach($matieres as $m)
                            <option value="{{ $m->id }}" {{ old('matiere_id')==$m->id?'selected':'' }}>{{ $m->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="font-size:0.875rem;font-weight:500;display:block;margin-bottom:0.4rem">Classe <span style="color:#DC2626">*</span></label>
                        <select name="classe_id" required style="width:100%;padding:0.75rem;border:1.5px solid #E5E7EB;border-radius:10px;font-size:0.9rem">
                            <option value="">Choisir...</option>
                            @foreach($classes as $c)
                            <option value="{{ $c->id }}" {{ old('classe_id')==$c->id?'selected':'' }}>{{ $c->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Dates --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.25rem">
                    <div>
                        <label style="font-size:0.875rem;font-weight:500;display:block;margin-bottom:0.4rem">Disponible le</label>
                        <input type="datetime-local" name="disponible_le" value="{{ old('disponible_le') }}"
                               style="width:100%;padding:0.75rem;border:1.5px solid #E5E7EB;border-radius:10px;font-size:0.9rem">
                    </div>
                    <div>
                        <label style="font-size:0.875rem;font-weight:500;display:block;margin-bottom:0.4rem">Expire le</label>
                        <input type="datetime-local" name="expire_le" value="{{ old('expire_le') }}"
                               style="width:100%;padding:0.75rem;border:1.5px solid #E5E7EB;border-radius:10px;font-size:0.9rem">
                    </div>
                </div>

                <hr style="margin:1.5rem 0;border-color:#F3F4F6">
                <h3 style="font-size:0.95rem;font-weight:700;margin-bottom:1.25rem">⏱ Paramètres du timer</h3>

                {{-- Timer --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.25rem">
                    <div>
                        <label style="font-size:0.875rem;font-weight:500;display:block;margin-bottom:0.4rem">Durée totale (minutes)</label>
                        <input type="number" name="duree_totale_minutes" value="{{ old('duree_totale_minutes', 30) }}" min="5" max="300"
                               style="width:100%;padding:0.75rem;border:1.5px solid #E5E7EB;border-radius:10px;font-size:0.9rem">
                    </div>
                    <div>
                        <label style="font-size:0.875rem;font-weight:500;display:block;margin-bottom:0.4rem">Temps par question (secondes)</label>
                        <input type="number" name="temps_par_question_secondes" value="{{ old('temps_par_question_secondes', 60) }}" min="10" max="600"
                               style="width:100%;padding:0.75rem;border:1.5px solid #E5E7EB;border-radius:10px;font-size:0.9rem">
                    </div>
                </div>

                <hr style="margin:1.5rem 0;border-color:#F3F4F6">
                <h3 style="font-size:0.95rem;font-weight:700;margin-bottom:1.25rem">🛡 Paramètres antitriche</h3>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.25rem">
                    <div>
                        <label style="font-size:0.875rem;font-weight:500;display:block;margin-bottom:0.4rem">Max changements d'onglet</label>
                        <input type="number" name="max_changements_onglet" value="{{ old('max_changements_onglet', 3) }}" min="0" max="10"
                               style="width:100%;padding:0.75rem;border:1.5px solid #E5E7EB;border-radius:10px;font-size:0.9rem">
                    </div>
                    <div>
                        <label style="font-size:0.875rem;font-weight:500;display:block;margin-bottom:0.4rem">Tentatives autorisées</label>
                        <input type="number" name="max_tentatives" value="{{ old('max_tentatives', 1) }}" min="1" max="5"
                               style="width:100%;padding:0.75rem;border:1.5px solid #E5E7EB;border-radius:10px;font-size:0.9rem">
                    </div>
                </div>

                {{-- Options --}}
                <div style="display:flex;flex-wrap:wrap;gap:1.5rem;margin-bottom:1.5rem">
                    @foreach([
                        ['name'=>'soumettre_auto_sortie','label'=>'Soumission auto si trop de sorties'],
                        ['name'=>'questions_aleatoires','label'=>'Ordre aléatoire des questions'],
                        ['name'=>'reponses_aleatoires','label'=>'Ordre aléatoire des réponses'],
                        ['name'=>'correction_auto','label'=>'Correction automatique (QCM)'],
                    ] as $opt)
                    <label style="display:flex;align-items:center;gap:0.5rem;font-size:0.875rem;cursor:pointer">
                        <input type="checkbox" name="{{ $opt['name'] }}" value="1"
                               {{ old($opt['name'], $opt['name'] === 'soumettre_auto_sortie' || $opt['name'] === 'correction_auto' ? '1' : '') ? 'checked' : '' }}
                               style="width:16px;height:16px">
                        {{ $opt['label'] }}
                    </label>
                    @endforeach
                </div>

                <div style="margin-bottom:1.5rem">
                    <label style="font-size:0.875rem;font-weight:500;display:block;margin-bottom:0.4rem">Note sur</label>
                    <input type="number" name="note_sur" value="{{ old('note_sur', 20) }}" min="1" max="100" step="0.5"
                           style="width:200px;padding:0.75rem;border:1.5px solid #E5E7EB;border-radius:10px;font-size:0.9rem">
                </div>

                <div style="display:flex;gap:1rem">
                    <a href="{{ route('enseignant.devoirs.index') }}"
                       style="padding:0.75rem 1.5rem;background:#F3F4F6;color:#374151;border-radius:10px;text-decoration:none;font-weight:600;font-size:0.9rem">
                        Annuler
                    </a>
                    <button type="submit"
                            style="padding:0.75rem 2rem;background:#4F46E5;color:white;border:none;border-radius:10px;font-weight:600;font-size:0.9rem;cursor:pointer">
                        <i class="bi bi-check2 me-1"></i> Créer le devoir
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection