{{-- resources/views/admin/antitriche.blade.php --}}
@extends('layouts.app')
@section('title', 'Antitriche')
@section('page-title', 'Rapport antitriche global')
@section('content')

<div class="stat-grid" style="grid-template-columns:repeat(3,1fr)">
    <div class="stat-card">
        <div class="stat-icon" style="background:#FEE2E2;color:#DC2626"><i class="bi bi-exclamation-triangle"></i></div>
        <div class="stat-value">{{ $stats['total'] }}</div>
        <div class="stat-label">Total événements</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#FEF3C7;color:#D97706"><i class="bi bi-shield-exclamation"></i></div>
        <div class="stat-value">{{ $stats['fraudes'] }}</div>
        <div class="stat-label">Fraudes avérées</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#EEF2FF;color:#4F46E5"><i class="bi bi-graph-up"></i></div>
        <div class="stat-value">{{ $stats['par_type']->sum() }}</div>
        <div class="stat-label">Événements suspects</div>
    </div>
</div>

<div class="card-section">
    <div class="card-header-row"><h2>Événements récents</h2></div>
    <table style="width:100%;border-collapse:collapse">
        <thead><tr style="background:#F9FAFB">
            @foreach(['Élève','Devoir','Type','Question','Date'] as $th)
            <th style="padding:0.6rem 1.5rem;font-size:0.72rem;font-weight:600;text-transform:uppercase;color:#6B7280;text-align:left">{{ $th }}</th>
            @endforeach
        </tr></thead>
        <tbody>
            @forelse($evenements as $e)
            <tr style="border-top:1px solid #E5E7EB">
                <td style="padding:0.875rem 1.5rem;font-size:0.875rem;font-weight:500">{{ $e->eleve?->nom_complet }}</td>
                <td style="padding:0.875rem 1.5rem;font-size:0.875rem;color:#6B7280">{{ Str::limit($e->tentative?->devoir?->titre, 30) }}</td>
                <td style="padding:0.875rem 1.5rem">
                    <span style="background:#FEE2E2;color:#991B1B;font-size:0.72rem;font-weight:600;padding:3px 10px;border-radius:20px">
                        {{ $e->label }}
                    </span>
                </td>
                <td style="padding:0.875rem 1.5rem;font-size:0.875rem;color:#6B7280">Q{{ $e->numero_question ?? '—' }}</td>
                <td style="padding:0.875rem 1.5rem;font-size:0.8rem;color:#6B7280">{{ $e->survenu_le->format('d/m/Y H:i') }}</td>
            </tr>
            @empty
            <tr><td colspan="5" style="padding:2rem;text-align:center;color:#6B7280">Aucun événement.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div style="padding:1rem 1.5rem">{{ $evenements->links() }}</div>
</div>
@endsection