@extends('layouts.app')
@section('title', 'Prévisualisation CSV')
@section('page-title', 'Prévisualisation de l\'import')
@section('page-subtitle', 'Vérifiez les données avant de confirmer l\'import')

@section('content')

{{-- Résumé --}}
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-bottom:1.5rem">
    <div class="stat-card">
        <div class="stat-icon" style="background:#EEF2FF;color:#4F46E5"><i class="bi bi-file-earmark-text"></i></div>
        <div class="stat-value">{{ count($lignes) }}</div>
        <div class="stat-label">Lignes détectées</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#D1FAE5;color:#059669"><i class="bi bi-check-circle"></i></div>
        <div class="stat-value" style="color:#059669">{{ $nb_valides }}</div>
        <div class="stat-label">Lignes valides</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#FEE2E2;color:#DC2626"><i class="bi bi-exclamation-circle"></i></div>
        <div class="stat-value" style="color:#DC2626">{{ $nb_erreurs }}</div>
        <div class="stat-label">Lignes avec erreurs</div>
    </div>
</div>

@if($nb_erreurs > 0)
<div style="background:#FEF3C7;border:1.5px solid #FCD34D;border-radius:12px;padding:1rem 1.25rem;margin-bottom:1.5rem;display:flex;gap:0.75rem">
    <i class="bi bi-exclamation-triangle-fill" style="color:#D97706;font-size:1.1rem;flex-shrink:0;margin-top:1px"></i>
    <div style="font-size:0.875rem;color:#92400E">
        <strong>{{ $nb_erreurs }} ligne(s) contiennent des erreurs</strong> et seront ignorées lors de l'import.
        Seules les <strong>{{ $nb_valides }} lignes valides</strong> seront importées.
    </div>
</div>
@endif

{{-- Table prévisualisation --}}
<div class="card-section" style="margin-bottom:1.5rem">
    <div class="card-header-row">
        <h2>Aperçu des données</h2>
        <span style="font-size:0.8rem;color:#6B7280">
            Type : <strong>{{ ucfirst($type) }}</strong>
        </span>
    </div>
    <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse;min-width:600px">
            <thead>
                <tr style="background:#F9FAFB">
                    <th style="padding:0.6rem 1rem;font-size:0.72rem;font-weight:600;text-transform:uppercase;color:#6B7280;text-align:left;width:60px">#</th>
                    <th style="padding:0.6rem 1rem;font-size:0.72rem;font-weight:600;text-transform:uppercase;color:#6B7280;text-align:left">Nom</th>
                    <th style="padding:0.6rem 1rem;font-size:0.72rem;font-weight:600;text-transform:uppercase;color:#6B7280;text-align:left">Prénoms</th>
                    <th style="padding:0.6rem 1rem;font-size:0.72rem;font-weight:600;text-transform:uppercase;color:#6B7280;text-align:left">Email</th>
                    @if($type === 'eleves')
                    <th style="padding:0.6rem 1rem;font-size:0.72rem;font-weight:600;text-transform:uppercase;color:#6B7280;text-align:left">Matricule</th>
                    @endif
                    <th style="padding:0.6rem 1rem;font-size:0.72rem;font-weight:600;text-transform:uppercase;color:#6B7280;text-align:left">Statut</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lignes as $ligne)
                <tr style="border-top:1px solid #E5E7EB;background:{{ $ligne['valide'] ? 'white' : '#FFF7F7' }}">
                    <td style="padding:0.75rem 1rem;font-size:0.8rem;color:#6B7280">{{ $ligne['num'] }}</td>
                    <td style="padding:0.75rem 1rem;font-size:0.875rem">{{ $ligne['data']['nom'] ?? '—' }}</td>
                    <td style="padding:0.75rem 1rem;font-size:0.875rem">{{ $ligne['data']['prenoms'] ?? '—' }}</td>
                    <td style="padding:0.75rem 1rem;font-size:0.875rem;color:#6B7280">{{ $ligne['data']['email'] ?? '—' }}</td>
                    @if($type === 'eleves')
                    <td style="padding:0.75rem 1rem;font-size:0.875rem;color:#6B7280">{{ $ligne['data']['matricule'] ?? '—' }}</td>
                    @endif
                    <td style="padding:0.75rem 1rem">
                        @if($ligne['valide'])
                            <span style="background:#D1FAE5;color:#065F46;font-size:0.72rem;font-weight:600;padding:3px 10px;border-radius:20px">
                                <i class="bi bi-check2 me-1"></i>Valide
                            </span>
                        @else
                            <div>
                                @foreach($ligne['erreurs'] as $err)
                                <span style="background:#FEE2E2;color:#991B1B;font-size:0.72rem;font-weight:600;padding:2px 8px;border-radius:20px;display:inline-block;margin-bottom:2px">
                                    {{ $err }}
                                </span>
                                @endforeach
                            </div>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Actions --}}
<div style="display:flex;gap:1rem;align-items:center">
    <a href="{{ route('admin.import-csv.index') }}"
       style="padding:0.875rem 1.5rem;background:#F3F4F6;color:#374151;border-radius:12px;text-decoration:none;font-weight:600;font-size:0.9rem">
        <i class="bi bi-arrow-left me-1"></i> Recommencer
    </a>

    @if($nb_valides > 0)
    <form method="POST" action="{{ route('admin.import-csv.importer') }}" style="flex:1">
        @csrf
        <input type="hidden" name="chemin_temp"   value="{{ $chemin_temp }}">
        <input type="hidden" name="type"           value="{{ $type }}">
        <input type="hidden" name="separateur"     value="{{ $separateur }}">
        <input type="hidden" name="classe_id"      value="{{ $classe_id }}">

        <button type="submit"
                style="width:100%;padding:0.875rem;background:#4F46E5;color:white;border:none;border-radius:12px;font-size:0.95rem;font-weight:600;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:0.5rem">
            <i class="bi bi-cloud-upload"></i>
            Confirmer l'import de {{ $nb_valides }} {{ $type }}
            @if($nb_erreurs > 0)
            <span style="background:rgba(255,255,255,0.2);padding:2px 8px;border-radius:10px;font-size:0.8rem">
                ({{ $nb_erreurs }} ignoré(s))
            </span>
            @endif
        </button>
    </form>
    @else
    <div style="flex:1;padding:0.875rem;background:#FEE2E2;color:#991B1B;border-radius:12px;text-align:center;font-size:0.875rem;font-weight:600">
        <i class="bi bi-x-circle me-1"></i>
        Aucune ligne valide à importer. Corrigez votre fichier et recommencez.
    </div>
    @endif
</div>

@endsection