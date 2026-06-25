@extends('layouts.app')
@section('title', 'Affectations')
@section('page-title', 'Affectations enseignants')
@section('page-subtitle', 'Année scolaire : ' . ($annee?->libelle ?? 'Aucune année active'))

@section('topbar-actions')
    {{-- Copier depuis une autre année --}}
    <button onclick="document.getElementById('modalCopie').classList.add('show')"
            style="padding:0.5rem 1rem;background:#F3F4F6;color:#374151;border:1.5px solid #E5E7EB;border-radius:8px;font-size:0.875rem;font-weight:600;cursor:pointer">
        <i class="bi bi-copy me-1"></i> Copier depuis...
    </button>
    <button onclick="document.getElementById('modalAffectation').classList.add('show')"
            class="btn-primary-custom">
        <i class="bi bi-plus-lg"></i> Nouvelle affectation
    </button>
@endsection

@section('content')

@if(!$annee)
<div style="background:#FEF3C7;border:1.5px solid #FCD34D;border-radius:12px;padding:1rem 1.25rem;margin-bottom:1.5rem;display:flex;gap:0.75rem;align-items:center">
    <i class="bi bi-exclamation-triangle-fill" style="color:#D97706;font-size:1.2rem"></i>
    <div>
        <strong style="font-size:0.875rem">Aucune année scolaire active.</strong>
        <span style="font-size:0.875rem;color:#92400E"> Activez une année scolaire d'abord.</span>
        <a href="{{ route('admin.annees-scolaires') }}" style="font-size:0.875rem;color:#4F46E5;margin-left:0.5rem">→ Gérer les années</a>
    </div>
</div>
@endif

{{-- Résumé stats --}}
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-bottom:1.5rem">
    <div class="stat-card">
        <div class="stat-icon" style="background:#EEF2FF;color:#4F46E5"><i class="bi bi-person-badge"></i></div>
        <div class="stat-value">{{ $enseignants->count() }}</div>
        <div class="stat-label">Enseignants actifs</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#D1FAE5;color:#059669"><i class="bi bi-check2-circle"></i></div>
        <div class="stat-value">{{ $enseignants->filter(fn($e) => $e->matieres->count() > 0)->count() }}</div>
        <div class="stat-label">Enseignants affectés</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#FEE2E2;color:#DC2626"><i class="bi bi-exclamation-circle"></i></div>
        <div class="stat-value">{{ $enseignants->filter(fn($e) => $e->matieres->count() === 0)->count() }}</div>
        <div class="stat-label">Sans affectation</div>
    </div>
</div>

{{-- Liste enseignants --}}
<div class="card-section">
    <div class="card-header-row">
        <h2>Enseignants et leurs affectations</h2>
        <span style="font-size:0.8rem;color:#6B7280">Cliquez sur un enseignant pour gérer ses affectations</span>
    </div>

    @forelse($enseignants as $enseignant)
    @php
        $affectations = $enseignant->matieres->groupBy(fn($m) => $m->id);
    @endphp
    <div style="padding:1.25rem 1.5rem;border-bottom:1px solid #E5E7EB">
        <div style="display:flex;align-items:flex-start;gap:1rem">

            {{-- Avatar + infos --}}
            <div style="display:flex;align-items:center;gap:0.75rem;min-width:200px">
                <div style="width:40px;height:40px;border-radius:50%;background:#EEF2FF;color:#4F46E5;display:flex;align-items:center;justify-content:center;font-size:0.8rem;font-weight:700;flex-shrink:0">
                    {{ strtoupper(substr($enseignant->prenoms,0,1).substr($enseignant->nom,0,1)) }}
                </div>
                <div>
                    <div style="font-size:0.9rem;font-weight:600">{{ $enseignant->nom_complet }}</div>
                    <div style="font-size:0.75rem;color:#6B7280">{{ $enseignant->email }}</div>
                </div>
            </div>

            {{-- Affectations résumées --}}
            <div style="flex:1">
                @if($enseignant->matieres->isEmpty())
                    <span style="font-size:0.8rem;color:#DC2626;background:#FEE2E2;padding:4px 12px;border-radius:20px">
                        <i class="bi bi-exclamation-circle me-1"></i>Aucune affectation
                    </span>
                @else
                    <div style="display:flex;flex-wrap:wrap;gap:0.5rem">
                        @foreach($enseignant->matieres->groupBy('id') as $matiereId => $matiereItems)
                        @php
                            $matiere = $matiereItems->first();
                            $classes = $matiereItems->map(fn($m) =>
                                $classes->find($m->pivot->classe_id)?->nom
                            )->filter()->values();
                        @endphp
                        <div style="background:#F9FAFB;border:1px solid #E5E7EB;border-radius:8px;padding:4px 10px;font-size:0.78rem">
                            <span style="color:{{ $matiere->couleur ?? '#4F46E5' }};font-weight:600">{{ $matiere->nom }}</span>
                            <span style="color:#6B7280;margin-left:4px">
                                @foreach($matiereItems as $m)
                                    {{ $classes->find($m->pivot->classe_id)?->nom ?? '?' }}{{ !$loop->last ? ', ' : '' }}
                                @endforeach
                            </span>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Actions --}}
            <a href="{{ route('admin.affectations.show', $enseignant->id) }}"
               style="padding:0.5rem 1rem;background:#EEF2FF;color:#4F46E5;border-radius:8px;text-decoration:none;font-size:0.8rem;font-weight:600;flex-shrink:0;white-space:nowrap">
                <i class="bi bi-pencil me-1"></i> Gérer
            </a>
        </div>
    </div>
    @empty
    <div style="padding:3rem;text-align:center;color:#6B7280">
        <i class="bi bi-person-badge" style="font-size:2.5rem;display:block;margin-bottom:0.75rem"></i>
        Aucun enseignant. <a href="{{ route('admin.utilisateurs') }}">Créer des enseignants d'abord.</a>
    </div>
    @endforelse
</div>

{{-- MODAL NOUVELLE AFFECTATION RAPIDE ─────────────────────────── --}}
<div id="modalAffectation"
     style="position:fixed;inset:0;background:rgba(0,0,0,0.5);display:none;align-items:center;justify-content:center;z-index:300">
    <div style="background:white;border-radius:20px;padding:2rem;width:90%;max-width:500px;box-shadow:0 20px 40px rgba(0,0,0,0.2)">
        <h3 style="font-size:1.1rem;font-weight:700;margin-bottom:1.5rem">
            <i class="bi bi-plus-circle me-2" style="color:#4F46E5"></i>Nouvelle affectation
        </h3>

        <form method="POST" action="{{ route('admin.affectations.store') }}">
            @csrf

            {{-- Enseignant --}}
            <div style="margin-bottom:1rem">
                <label style="font-size:0.8rem;font-weight:600;display:block;margin-bottom:0.4rem">Enseignant</label>
                <select name="enseignant_id" required
                        style="width:100%;padding:0.75rem;border:1.5px solid #E5E7EB;border-radius:10px;font-size:0.875rem">
                    <option value="">Choisir un enseignant...</option>
                    @foreach($enseignants as $e)
                    <option value="{{ $e->id }}">{{ $e->nom_complet }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Matière --}}
            <div style="margin-bottom:1rem">
                <label style="font-size:0.8rem;font-weight:600;display:block;margin-bottom:0.4rem">Matière</label>
                <select name="matiere_id" required
                        style="width:100%;padding:0.75rem;border:1.5px solid #E5E7EB;border-radius:10px;font-size:0.875rem">
                    <option value="">Choisir une matière...</option>
                    @foreach($matieres as $m)
                    <option value="{{ $m->id }}">{{ $m->nom }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Classe --}}
            <div style="margin-bottom:1.5rem">
                <label style="font-size:0.8rem;font-weight:600;display:block;margin-bottom:0.4rem">Classe</label>
                <select name="classe_id" required
                        style="width:100%;padding:0.75rem;border:1.5px solid #E5E7EB;border-radius:10px;font-size:0.875rem">
                    <option value="">Choisir une classe...</option>
                    @foreach($classes as $c)
                    <option value="{{ $c->id }}">{{ $c->nom }}</option>
                    @endforeach
                </select>
            </div>

            <div style="display:flex;gap:0.75rem">
                <button type="button"
                        onclick="document.getElementById('modalAffectation').classList.remove('show')"
                        style="flex:1;padding:0.75rem;background:#F3F4F6;border:none;border-radius:10px;font-weight:600;cursor:pointer">
                    Annuler
                </button>
                <button type="submit"
                        style="flex:1;padding:0.75rem;background:#4F46E5;color:white;border:none;border-radius:10px;font-weight:600;cursor:pointer">
                    <i class="bi bi-plus-lg me-1"></i>Affecter
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL COPIER DEPUIS UNE AUTRE ANNÉE ────────────────────────── --}}
<div id="modalCopie"
     style="position:fixed;inset:0;background:rgba(0,0,0,0.5);display:none;align-items:center;justify-content:center;z-index:300">
    <div style="background:white;border-radius:20px;padding:2rem;width:90%;max-width:420px;box-shadow:0 20px 40px rgba(0,0,0,0.2)">
        <h3 style="font-size:1.1rem;font-weight:700;margin-bottom:0.5rem">Copier les affectations</h3>
        <p style="font-size:0.875rem;color:#6B7280;margin-bottom:1.5rem">
            Copie toutes les affectations d'une année passée vers l'année active ({{ $annee?->libelle }}).
        </p>
        <form method="POST" action="{{ route('admin.affectations.copier') }}">
            @csrf
            <div style="margin-bottom:1.5rem">
                <label style="font-size:0.8rem;font-weight:600;display:block;margin-bottom:0.4rem">Année source</label>
                <select name="annee_source_id" required
                        style="width:100%;padding:0.75rem;border:1.5px solid #E5E7EB;border-radius:10px;font-size:0.875rem">
                    <option value="">Choisir une année...</option>
                    @foreach(\App\Models\Tenant\AnneeScolaire::where('active', false)->orderByDesc('date_debut')->get() as $a)
                    <option value="{{ $a->id }}">{{ $a->libelle }}</option>
                    @endforeach
                </select>
            </div>
            <div style="display:flex;gap:0.75rem">
                <button type="button"
                        onclick="document.getElementById('modalCopie').classList.remove('show')"
                        style="flex:1;padding:0.75rem;background:#F3F4F6;border:none;border-radius:10px;font-weight:600;cursor:pointer">
                    Annuler
                </button>
                <button type="submit"
                        style="flex:1;padding:0.75rem;background:#059669;color:white;border:none;border-radius:10px;font-weight:600;cursor:pointer">
                    <i class="bi bi-copy me-1"></i>Copier
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Toggle modals
['modalAffectation','modalCopie'].forEach(id => {
    const modal = document.getElementById(id);
    new MutationObserver(() => {
        modal.style.display = modal.classList.contains('show') ? 'flex' : 'none';
    }).observe(modal, { attributes: true, attributeFilter: ['class'] });
    modal.addEventListener('click', e => {
        if (e.target === modal) modal.classList.remove('show');
    });
});
</script>

@endsection