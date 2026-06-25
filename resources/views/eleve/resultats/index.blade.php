{{-- ════════════════════════════════════════════════════════
     resources/views/eleve/resultats/index.blade.php
════════════════════════════════════════════════════════ --}}
@extends('layouts.app')
@section('title', 'Mes résultats')
@section('page-title', 'Mes résultats')
@section('content')
<div class="card-section">
    <table style="width:100%;border-collapse:collapse">
        <thead><tr style="background:#F9FAFB">
            @foreach(['Devoir','Note','Mention','Bonnes réponses','Date'] as $th)
            <th style="padding:0.6rem 1.5rem;font-size:0.72rem;font-weight:600;text-transform:uppercase;color:#6B7280;text-align:left">{{ $th }}

            </th>
            @endforeach

        </tr></thead>
        <tbody>
            @forelse($resultats as $r)
            <tr style="border-top:1px solid #E5E7EB">
                <td style="padding:0.875rem 1.5rem">
                    <div style="font-size:0.875rem;font-weight:500">{{ $r->devoir?->titre }}</div>
                    <div style="font-size:0.75rem;color:#6B7280">{{ $r->devoir?->matiere?->nom }}</div>
                </td>
                <td style="padding:0.875rem 1.5rem">
                    <strong style="font-size:1.1rem">{{ $r->note_finale }}</strong>
                    <span style="color:#6B7280">/{{ $r->note_sur }}</span>
                </td>
                <td style="padding:0.875rem 1.5rem">
                    @php $c = $r->pourcentage >= 75 ? ['#D1FAE5','#065F46'] : ($r->pourcentage >= 50 ? ['#FEF3C7','#92400E'] : ['#FEE2E2','#991B1B']); @endphp
                    <span style="background:{{ $c[0] }};color:{{ $c[1] }};font-size:0.72rem;font-weight:600;padding:3px 10px;border-radius:20px">
                        {{ $r->mention }}
                    </span>
                </td>
                <td style="padding:0.875rem 1.5rem;font-size:0.875rem">
                    {{ $r->bonnes_reponses }}/{{ $r->total_questions }}
                </td>
                <td style="padding:0.875rem 1.5rem;font-size:0.8rem;color:#6B7280">
                    {{ $r->created_at->format('d/m/Y') }}
                </td>
            </tr>
            @empty
            <tr><td colspan="5" style="padding:2rem;text-align:center;color:#6B7280">Aucun résultat disponible.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div style="padding:1rem 1.5rem">{{ $resultats->links() }}</div>
</div>
@endsection
