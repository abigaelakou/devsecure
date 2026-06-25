@extends('layouts.app')
@section('title', 'Années scolaires')
@section('page-title', 'Années scolaires')
@section('content')
<div style="display:grid;grid-template-columns:1fr 360px;gap:1.5rem">
    <div class="card-section">
        <div class="card-header-row"><h2>Années scolaires</h2></div>
        <table style="width:100%;border-collapse:collapse">
            <thead><tr style="background:#F9FAFB">
                @foreach(['Libellé','Début','Fin','Statut','Actions'] as $th)
                <th style="padding:0.6rem 1.5rem;font-size:0.72rem;font-weight:600;text-transform:uppercase;color:#6B7280;text-align:left">{{ $th }}</th>
                @endforeach
            </tr></thead>
            <tbody>
                @forelse($annees as $annee)
                <tr style="border-top:1px solid #E5E7EB">
                    <td style="padding:0.875rem 1.5rem;font-weight:600;font-size:0.875rem">{{ $annee->libelle }}</td>
                    <td style="padding:0.875rem 1.5rem;font-size:0.875rem;color:#6B7280">{{ $annee->date_debut->format('d/m/Y') }}</td>
                    <td style="padding:0.875rem 1.5rem;font-size:0.875rem;color:#6B7280">{{ $annee->date_fin->format('d/m/Y') }}</td>
                    <td style="padding:0.875rem 1.5rem">
                        @if($annee->active)
                            <span style="background:#D1FAE5;color:#065F46;font-size:0.72rem;font-weight:600;padding:3px 10px;border-radius:20px">● Active</span>
                        @else
                            <span style="background:#F3F4F6;color:#6B7280;font-size:0.72rem;font-weight:600;padding:3px 10px;border-radius:20px">Inactive</span>
                        @endif
                    </td>
                    <td style="padding:0.875rem 1.5rem">
                        @if(!$annee->active)
                        <form method="POST" action="{{ route('admin.annees-scolaires') }}/{{ $annee->id }}/activer" style="display:inline">
                            @csrf @method('PATCH')
                            <button type="submit" style="padding:4px 10px;border:1px solid #4F46E5;border-radius:6px;background:white;font-size:0.75rem;cursor:pointer;color:#4F46E5">
                                Activer
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" style="padding:2rem;text-align:center;color:#6B7280">Aucune année scolaire.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-section" style="align-self:start">
        <div class="card-header-row"><h2>Nouvelle année</h2></div>
        <div style="padding:1.25rem">
            <form method="POST" action="{{ route('admin.annees-scolaires') }}">
                @csrf
                <div style="margin-bottom:1rem">
                    <label style="font-size:0.8rem;font-weight:500;display:block;margin-bottom:0.3rem">Libellé</label>
                    <input type="text" name="libelle" placeholder="2026-2027" required style="width:100%;padding:0.6rem;border:1.5px solid #E5E7EB;border-radius:8px;font-size:0.875rem">
                </div>
                <div style="margin-bottom:1rem">
                    <label style="font-size:0.8rem;font-weight:500;display:block;margin-bottom:0.3rem">Date début</label>
                    <input type="date" name="date_debut" required style="width:100%;padding:0.6rem;border:1.5px solid #E5E7EB;border-radius:8px;font-size:0.875rem">
                </div>
                <div style="margin-bottom:1rem">
                    <label style="font-size:0.8rem;font-weight:500;display:block;margin-bottom:0.3rem">Date fin</label>
                    <input type="date" name="date_fin" required style="width:100%;padding:0.6rem;border:1.5px solid #E5E7EB;border-radius:8px;font-size:0.875rem">
                </div>
                <div style="margin-bottom:1.25rem;display:flex;align-items:center;gap:0.5rem">
                    <input type="checkbox" name="active" id="active" style="width:16px;height:16px">
                    <label for="active" style="font-size:0.875rem">Définir comme année active</label>
                </div>
                <button type="submit" style="width:100%;padding:0.75rem;background:#4F46E5;color:white;border:none;border-radius:10px;font-weight:600;cursor:pointer">
                    Créer
                </button>
            </form>
        </div>
    </div>
</div>
@endsection