@extends('layouts.app')
@section('title', 'Affectations — ' . $enseignant->nom_complet)
@section('page-title', $enseignant->nom_complet)
@section('page-subtitle', 'Gestion des affectations · ' . ($annee?->libelle ?? ''))

@section('topbar-actions')
    <a href="{{ route('admin.affectations.index') }}"
       style="padding:0.5rem 1rem;background:#F3F4F6;color:#374151;border:1.5px solid #E5E7EB;border-radius:8px;font-size:0.875rem;font-weight:600;text-decoration:none">
        <i class="bi bi-arrow-left me-1"></i> Retour
    </a>
@endsection

@section('content')
<div style="display:grid;grid-template-columns:1fr 380px;gap:1.5rem">

    {{-- Affectations actuelles --}}
    <div>
        <div class="card-section">
            <div class="card-header-row">
                <h2>Affectations actuelles</h2>
                <span style="background:#EEF2FF;color:#4F46E5;font-size:0.75rem;font-weight:600;padding:3px 12px;border-radius:20px">
                    {{ $affectations->count() }} affectation(s)
                </span>
            </div>

            @if($affectations->isEmpty())
            <div style="padding:3rem;text-align:center;color:#6B7280">
                <i class="bi bi-inbox" style="font-size:2.5rem;display:block;margin-bottom:0.75rem"></i>
                Aucune affectation pour {{ $enseignant->prenoms }}.<br>
                <span style="font-size:0.875rem">Utilisez le formulaire à droite pour en ajouter.</span>
            </div>
            @else

            {{-- Grouper par matière --}}
            @foreach($affectations->groupBy('matiere_id') as $matiereId => $items)
            @php $matiere = $items->first()->matiere; @endphp
            <div style="padding:1.25rem 1.5rem;border-bottom:1px solid #E5E7EB">
                <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:0.875rem">
                    <div style="width:10px;height:10px;border-radius:50%;background:{{ $matiere?->couleur ?? '#4F46E5' }}"></div>
                    <span style="font-size:0.95rem;font-weight:700;color:{{ $matiere?->couleur ?? '#4F46E5' }}">
                        {{ $matiere?->nom }}
                    </span>
                    <span style="font-size:0.75rem;color:#6B7280;background:#F3F4F6;padding:2px 8px;border-radius:10px">
                        {{ $items->count() }} classe(s)
                    </span>
                </div>

                <div style="display:flex;flex-wrap:wrap;gap:0.5rem">
                    @foreach($items as $affectation)
                    <div style="display:flex;align-items:center;gap:0.5rem;background:#F9FAFB;border:1px solid #E5E7EB;border-radius:8px;padding:6px 12px">
                        <i class="bi bi-people" style="color:#6B7280;font-size:0.85rem"></i>
                        <span style="font-size:0.875rem;font-weight:500">{{ $affectation->classe?->nom }}</span>
                        <form method="POST" action="{{ route('admin.affectations.destroy', $affectation->id) }}" style="display:inline"
                              onsubmit="return confirm('Supprimer cette affectation ?')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    style="background:none;border:none;color:#DC2626;cursor:pointer;font-size:0.9rem;padding:0;margin-left:4px;line-height:1"
                                    title="Supprimer">
                                <i class="bi bi-x-circle"></i>
                            </button>
                        </form>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
            @endif
        </div>

        {{-- Résumé devoirs liés --}}
        @php
            $nbDevoirs = \App\Models\Tenant\Devoir::where('enseignant_id', $enseignant->id)->count();
            $nbDevoirsActifs = \App\Models\Tenant\Devoir::where('enseignant_id', $enseignant->id)->where('statut','actif')->count();
        @endphp
        @if($nbDevoirs > 0)
        <div class="card-section" style="margin-top:1rem">
            <div class="card-header-row"><h2>Activité de cet enseignant</h2></div>
            <div style="padding:1.25rem;display:grid;grid-template-columns:1fr 1fr;gap:1rem">
                <div style="text-align:center;padding:1rem;background:#F9FAFB;border-radius:10px">
                    <div style="font-size:1.75rem;font-weight:700;color:#4F46E5">{{ $nbDevoirs }}</div>
                    <div style="font-size:0.78rem;color:#6B7280">Devoirs créés</div>
                </div>
                <div style="text-align:center;padding:1rem;background:#F9FAFB;border-radius:10px">
                    <div style="font-size:1.75rem;font-weight:700;color:#059669">{{ $nbDevoirsActifs }}</div>
                    <div style="font-size:0.78rem;color:#6B7280">Devoirs actifs</div>
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- Formulaire ajout --}}
    <div>

        {{-- Ajout simple (une classe) --}}
        <div class="card-section" style="margin-bottom:1rem">
            <div class="card-header-row"><h2>Ajouter une affectation</h2></div>
            <div style="padding:1.25rem">
                <form method="POST" action="{{ route('admin.affectations.store') }}">
                    @csrf
                    <input type="hidden" name="enseignant_id" value="{{ $enseignant->id }}">

                    <div style="margin-bottom:1rem">
                        <label style="font-size:0.8rem;font-weight:600;display:block;margin-bottom:0.4rem">Matière</label>
                        <select name="matiere_id" required
                                style="width:100%;padding:0.75rem;border:1.5px solid #E5E7EB;border-radius:10px;font-size:0.875rem">
                            <option value="">Choisir...</option>
                            @foreach($matieres as $m)
                            <option value="{{ $m->id }}">{{ $m->nom }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div style="margin-bottom:1.25rem">
                        <label style="font-size:0.8rem;font-weight:600;display:block;margin-bottom:0.4rem">Classe</label>
                        <select name="classe_id" required
                                style="width:100%;padding:0.75rem;border:1.5px solid #E5E7EB;border-radius:10px;font-size:0.875rem">
                            <option value="">Choisir...</option>
                            @foreach($classes as $c)
                            <option value="{{ $c->id }}">{{ $c->nom }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit"
                            style="width:100%;padding:0.75rem;background:#4F46E5;color:white;border:none;border-radius:10px;font-weight:600;cursor:pointer;font-size:0.875rem">
                        <i class="bi bi-plus-lg me-1"></i> Ajouter
                    </button>
                </form>
            </div>
        </div>

        {{-- Ajout en masse (plusieurs classes) --}}
        <div class="card-section">
            <div class="card-header-row">
                <h2>Affecter plusieurs classes</h2>
            </div>
            <div style="padding:1.25rem">
                <form method="POST" action="{{ route('admin.affectations.store-masse') }}">
                    @csrf
                    <input type="hidden" name="enseignant_id" value="{{ $enseignant->id }}">

                    <div style="margin-bottom:1rem">
                        <label style="font-size:0.8rem;font-weight:600;display:block;margin-bottom:0.4rem">Matière</label>
                        <select name="matiere_id" required
                                style="width:100%;padding:0.75rem;border:1.5px solid #E5E7EB;border-radius:10px;font-size:0.875rem">
                            <option value="">Choisir...</option>
                            @foreach($matieres as $m)
                            <option value="{{ $m->id }}">{{ $m->nom }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div style="margin-bottom:1.25rem">
                        <label style="font-size:0.8rem;font-weight:600;display:block;margin-bottom:0.4rem">
                            Classes <span style="color:#6B7280;font-weight:400">(cochez toutes)</span>
                        </label>
                        <div style="border:1.5px solid #E5E7EB;border-radius:10px;overflow:hidden;max-height:200px;overflow-y:auto">
                            @foreach($classes as $c)
                            <label style="display:flex;align-items:center;gap:0.75rem;padding:0.6rem 0.875rem;cursor:pointer;border-bottom:1px solid #F3F4F6;transition:background 0.15s"
                                   onmouseover="this.style.background='#F9FAFB'" onmouseout="this.style.background='white'">
                                <input type="checkbox" name="classe_ids[]" value="{{ $c->id }}"
                                       style="width:16px;height:16px;cursor:pointer">
                                <div>
                                    <span style="font-size:0.875rem;font-weight:500">{{ $c->nom }}</span>
                                    <span style="font-size:0.75rem;color:#6B7280;margin-left:0.4rem">{{ ucfirst($c->niveau) }}</span>
                                </div>
                            </label>
                            @endforeach
                        </div>
                        <div style="margin-top:0.5rem;display:flex;gap:0.5rem">
                            <button type="button" onclick="document.querySelectorAll('input[name=\'classe_ids[]\']').forEach(cb => cb.checked = true)"
                                    style="font-size:0.75rem;color:#4F46E5;background:none;border:none;cursor:pointer;padding:0">
                                Tout sélectionner
                            </button>
                            <span style="color:#E5E7EB">·</span>
                            <button type="button" onclick="document.querySelectorAll('input[name=\'classe_ids[]\']').forEach(cb => cb.checked = false)"
                                    style="font-size:0.75rem;color:#6B7280;background:none;border:none;cursor:pointer;padding:0">
                                Tout désélectionner
                            </button>
                        </div>
                    </div>

                    <button type="submit"
                            style="width:100%;padding:0.75rem;background:#059669;color:white;border:none;border-radius:10px;font-weight:600;cursor:pointer;font-size:0.875rem">
                        <i class="bi bi-check2-all me-1"></i> Affecter les classes sélectionnées
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection