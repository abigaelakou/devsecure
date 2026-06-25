@extends('layouts.app')
@section('title', $classe->nom)
@section('page-title', $classe->nom)
@section('page-subtitle', ucfirst($classe->niveau) . ' · ' . ($annee?->libelle ?? '') . ' · ' . $eleves->count() . ' élève(s)')

@section('topbar-actions')
    <a href="{{ route('admin.eleve-classes.export', $classe->id) }}"
       style="padding:0.5rem 1rem;background:#F3F4F6;color:#374151;border:1.5px solid #E5E7EB;border-radius:8px;font-size:0.875rem;font-weight:600;text-decoration:none">
        <i class="bi bi-download me-1"></i> Export CSV
    </a>
    <a href="{{ route('admin.eleve-classes.index') }}"
       style="padding:0.5rem 1rem;background:#F3F4F6;color:#374151;border:1.5px solid #E5E7EB;border-radius:8px;font-size:0.875rem;font-weight:600;text-decoration:none">
        <i class="bi bi-arrow-left me-1"></i> Retour
    </a>
    <a href="{{ route('admin.bulletin.eleve', $eleve->id) }}"
        style="width:28px;height:28px;border:1px solid #E5E7EB;border-radius:7px;display:flex;align-items:center;justify-content:center;color:#DC2626;text-decoration:none"
        title="Bulletin PDF">
        <i class="bi bi-file-pdf"></i>
    </a>
@endsection

@section('content')
<div style="display:grid;grid-template-columns:1fr 380px;gap:1.5rem">

    {{-- Liste élèves de la classe --}}
    <div>
        <div class="card-section">
            <div class="card-header-row">
                <h2>Élèves de {{ $classe->nom }}</h2>
                <span style="background:#EEF2FF;color:#4F46E5;font-size:0.75rem;font-weight:600;padding:3px 12px;border-radius:20px">
                    {{ $eleves->count() }} élève(s)
                </span>
            </div>

            @if($eleves->isEmpty())
            <div style="padding:3rem;text-align:center;color:#6B7280">
                <i class="bi bi-people" style="font-size:2.5rem;display:block;margin-bottom:0.75rem"></i>
                Aucun élève dans cette classe.<br>
                <span style="font-size:0.875rem">Utilisez le formulaire à droite pour en ajouter.</span>
            </div>
            @else
            <table style="width:100%;border-collapse:collapse">
                <thead>
                    <tr style="background:#F9FAFB">
                        <th style="padding:0.6rem 1.5rem;font-size:0.72rem;font-weight:600;text-transform:uppercase;color:#6B7280;text-align:left">Élève</th>
                        <th style="padding:0.6rem 1.5rem;font-size:0.72rem;font-weight:600;text-transform:uppercase;color:#6B7280;text-align:left">Matricule</th>
                        <th style="padding:0.6rem 1.5rem;font-size:0.72rem;font-weight:600;text-transform:uppercase;color:#6B7280;text-align:left">Email</th>
                        <th style="padding:0.6rem 1.5rem;font-size:0.72rem;font-weight:600;text-transform:uppercase;color:#6B7280;text-align:left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($eleves as $eleve)
                    <tr style="border-top:1px solid #E5E7EB">
                        <td style="padding:0.875rem 1.5rem">
                            <div style="display:flex;align-items:center;gap:0.75rem">
                                <div style="width:34px;height:34px;border-radius:50%;background:#EEF2FF;color:#4F46E5;display:flex;align-items:center;justify-content:center;font-size:0.72rem;font-weight:700;flex-shrink:0">
                                    {{ strtoupper(substr($eleve->prenoms,0,1).substr($eleve->nom,0,1)) }}
                                </div>
                                <span style="font-size:0.875rem;font-weight:500">{{ $eleve->nom_complet }}</span>
                            </div>
                        </td>
                        <td style="padding:0.875rem 1.5rem;font-size:0.875rem;color:#6B7280">
                            {{ $eleve->matricule ?? '—' }}
                        </td>
                        <td style="padding:0.875rem 1.5rem;font-size:0.8rem;color:#6B7280">
                            {{ $eleve->email }}
                        </td>
                        <td style="padding:0.875rem 1rem">
                            <div style="display:flex;gap:0.4rem">
                                {{-- Déplacer --}}
                                <button onclick="ouvrirModalDeplacement({{ $eleve->id }}, '{{ $eleve->nom_complet }}')"
                                        style="padding:4px 10px;border:1px solid #E5E7EB;border-radius:6px;background:white;font-size:0.75rem;cursor:pointer;color:#4F46E5"
                                        title="Déplacer vers une autre classe">
                                    <i class="bi bi-arrow-left-right"></i>
                                </button>
                                {{-- Retirer --}}
                                <form method="POST"
                                      action="{{ route('admin.eleve-classes.destroy', [$classe->id, $eleve->id]) }}"
                                      onsubmit="return confirm('Retirer {{ $eleve->nom_complet }} de {{ $classe->nom }} ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            style="padding:4px 10px;border:1px solid #FCA5A5;border-radius:6px;background:white;font-size:0.75rem;cursor:pointer;color:#DC2626"
                                            title="Retirer de la classe">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </div>

    {{-- Formulaires ajout --}}
    <div>

        {{-- Ajouter un élève --}}
        <div class="card-section" style="margin-bottom:1rem">
            <div class="card-header-row"><h2>Ajouter un élève</h2></div>
            <div style="padding:1.25rem">
                @if($elevesDisponibles->isEmpty())
                <div style="padding:1rem;text-align:center;color:#6B7280;font-size:0.875rem;background:#F9FAFB;border-radius:10px">
                    <i class="bi bi-check2-circle" style="color:#059669;display:block;font-size:1.5rem;margin-bottom:0.5rem"></i>
                    Tous les élèves sont déjà affectés à une classe.
                </div>
                @else
                <form method="POST" action="{{ route('admin.eleve-classes.store') }}">
                    @csrf
                    <input type="hidden" name="classe_id" value="{{ $classe->id }}">
                    <div style="margin-bottom:1rem">
                        <label style="font-size:0.8rem;font-weight:600;display:block;margin-bottom:0.4rem">
                            Élève <span style="color:#6B7280;font-weight:400">({{ $elevesDisponibles->count() }} disponibles)</span>
                        </label>
                        <select name="eleve_id" required
                                style="width:100%;padding:0.75rem;border:1.5px solid #E5E7EB;border-radius:10px;font-size:0.875rem">
                            <option value="">Choisir un élève...</option>
                            @foreach($elevesDisponibles as $e)
                            <option value="{{ $e->id }}">{{ $e->nom_complet }} {{ $e->matricule ? '('.$e->matricule.')' : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit"
                            style="width:100%;padding:0.75rem;background:#4F46E5;color:white;border:none;border-radius:10px;font-weight:600;cursor:pointer;font-size:0.875rem">
                        <i class="bi bi-plus-lg me-1"></i> Ajouter à {{ $classe->nom }}
                    </button>
                </form>
                @endif
            </div>
        </div>

        {{-- Ajouter plusieurs élèves en masse --}}
        @if($elevesDisponibles->count() > 1)
        <div class="card-section">
            <div class="card-header-row"><h2>Ajout en masse</h2></div>
            <div style="padding:1.25rem">
                <form method="POST" action="{{ route('admin.eleve-classes.store-masse') }}">
                    @csrf
                    <input type="hidden" name="classe_id" value="{{ $classe->id }}">
                    <div style="margin-bottom:1rem">
                        <label style="font-size:0.8rem;font-weight:600;display:block;margin-bottom:0.4rem">Sélectionner les élèves</label>
                        <div style="border:1.5px solid #E5E7EB;border-radius:10px;overflow:hidden;max-height:220px;overflow-y:auto">
                            @foreach($elevesDisponibles as $e)
                            <label style="display:flex;align-items:center;gap:0.75rem;padding:0.6rem 0.875rem;cursor:pointer;border-bottom:1px solid #F3F4F6;transition:background 0.15s"
                                   onmouseover="this.style.background='#F9FAFB'" onmouseout="this.style.background='white'">
                                <input type="checkbox" name="eleve_ids[]" value="{{ $e->id }}"
                                       style="width:16px;height:16px;cursor:pointer">
                                <div>
                                    <div style="font-size:0.875rem;font-weight:500">{{ $e->nom_complet }}</div>
                                    @if($e->matricule)
                                    <div style="font-size:0.72rem;color:#6B7280">{{ $e->matricule }}</div>
                                    @endif
                                </div>
                            </label>
                            @endforeach
                        </div>
                        <div style="margin-top:0.5rem;display:flex;gap:0.5rem">
                            <button type="button"
                                    onclick="document.querySelectorAll('input[name=\'eleve_ids[]\']').forEach(cb => cb.checked = true)"
                                    style="font-size:0.75rem;color:#4F46E5;background:none;border:none;cursor:pointer;padding:0">
                                Tout sélectionner
                            </button>
                            <span style="color:#E5E7EB">·</span>
                            <button type="button"
                                    onclick="document.querySelectorAll('input[name=\'eleve_ids[]\']').forEach(cb => cb.checked = false)"
                                    style="font-size:0.75rem;color:#6B7280;background:none;border:none;cursor:pointer;padding:0">
                                Tout désélectionner
                            </button>
                        </div>
                    </div>
                    <button type="submit"
                            style="width:100%;padding:0.75rem;background:#059669;color:white;border:none;border-radius:10px;font-weight:600;cursor:pointer;font-size:0.875rem">
                        <i class="bi bi-check2-all me-1"></i> Affecter la sélection
                    </button>
                </form>
            </div>
        </div>
        @endif
    </div>
</div>

{{-- MODAL DÉPLACEMENT ────────────────────────────────────────────── --}}
<div id="modalDeplacement"
     style="position:fixed;inset:0;background:rgba(0,0,0,0.5);display:none;align-items:center;justify-content:center;z-index:300">
    <div style="background:white;border-radius:20px;padding:2rem;width:90%;max-width:420px;box-shadow:0 20px 40px rgba(0,0,0,0.2)">
        <h3 style="font-size:1.1rem;font-weight:700;margin-bottom:0.5rem">Déplacer un élève</h3>
        <p id="modalDeplacementDesc" style="font-size:0.875rem;color:#6B7280;margin-bottom:1.5rem"></p>

        <form method="POST" action="{{ route('admin.eleve-classes.deplacer') }}">
            @csrf
            <input type="hidden" name="eleve_id" id="modalEleveId">
            <input type="hidden" name="classe_source_id" value="{{ $classe->id }}">

            <div style="margin-bottom:1.5rem">
                <label style="font-size:0.8rem;font-weight:600;display:block;margin-bottom:0.4rem">Nouvelle classe</label>
                <select name="classe_cible_id" required
                        style="width:100%;padding:0.75rem;border:1.5px solid #E5E7EB;border-radius:10px;font-size:0.875rem">
                    <option value="">Choisir une classe...</option>
                    @foreach(\App\Models\Tenant\Classe::where('annee_scolaire_id', $annee?->id)->where('id', '!=', $classe->id)->orderBy('niveau')->orderBy('nom')->get() as $c)
                    <option value="{{ $c->id }}">{{ $c->nom }}</option>
                    @endforeach
                </select>
            </div>

            <div style="display:flex;gap:0.75rem">
                <button type="button"
                        onclick="document.getElementById('modalDeplacement').style.display='none'"
                        style="flex:1;padding:0.75rem;background:#F3F4F6;border:none;border-radius:10px;font-weight:600;cursor:pointer">
                    Annuler
                </button>
                <button type="submit"
                        style="flex:1;padding:0.75rem;background:#4F46E5;color:white;border:none;border-radius:10px;font-weight:600;cursor:pointer">
                    <i class="bi bi-arrow-left-right me-1"></i> Déplacer
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function ouvrirModalDeplacement(eleveId, eleveNom) {
    document.getElementById('modalEleveId').value = eleveId;
    document.getElementById('modalDeplacementDesc').textContent =
        'Déplacer ' + eleveNom + ' de {{ $classe->nom }} vers une autre classe.';
    document.getElementById('modalDeplacement').style.display = 'flex';
}
document.getElementById('modalDeplacement').addEventListener('click', function(e) {
    if (e.target === this) this.style.display = 'none';
});
</script>

@endsection