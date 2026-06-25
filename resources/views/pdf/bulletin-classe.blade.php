<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Relevé — {{ $classe->nom }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: sans-serif; font-size: 9pt; color: #1F2937; background: white; }

        .header {
            background: #1E1B4B; color: white;
            padding: 14px 20px; display: table; width: 100%;
            margin-bottom: 12px;
        }
        .header-left  { display: table-cell; vertical-align: middle; }
        .header-right { display: table-cell; vertical-align: middle; text-align: right; }
        .logo-text { font-size: 16pt; font-weight: bold; }
        .titre     { font-size: 13pt; font-weight: bold; }
        .sous-titre{ font-size: 9pt; opacity: 0.8; margin-top: 2px; }

        .meta {
            padding: 8px 20px;
            background: #F8F7FF;
            border-bottom: 2px solid #4F46E5;
            margin-bottom: 12px;
            display: table; width: 100%;
            font-size: 9pt;
        }
        .meta-item { display: table-cell; }
        .meta-label { color: #6B7280; font-size: 8pt; }
        .meta-val   { font-weight: bold; color: #1E1B4B; }

        table { width: 100%; border-collapse: collapse; font-size: 8.5pt; }
        th {
            background: #1E1B4B; color: white;
            padding: 6px 8px; text-align: center;
            font-size: 7.5pt; font-weight: bold;
            border: 1px solid #374151;
        }
        th.left { text-align: left; }
        td {
            padding: 5px 8px; border: 1px solid #E5E7EB;
            text-align: center; vertical-align: middle;
        }
        td.left { text-align: left; }
        tr:nth-child(even) td { background: #F9FAFB; }
        tr:hover td { background: #EEF2FF; }

        .rang  { font-weight: bold; color: #D97706; }
        .note-ok   { color: #059669; font-weight: bold; }
        .note-warn { color: #D97706; font-weight: bold; }
        .note-fail { color: #DC2626; font-weight: bold; }
        .badge-fraude { color: #DC2626; font-size: 8pt; }

        .footer {
            position: fixed; bottom: 0; left: 0; right: 0;
            padding: 6px 20px; background: #F9FAFB;
            border-top: 1px solid #E5E7EB;
            font-size: 7.5pt; color: #9CA3AF;
            display: table; width: 100%;
        }
        .footer-left  { display: table-cell; }
        .footer-right { display: table-cell; text-align: right; }
    </style>
</head>
<body>

<div class="header">
    <div class="header-left">
        <div class="logo-text">DevSecure</div>
        <div class="sous-titre">Plateforme d'évaluation sécurisée</div>
    </div>
    <div class="header-right">
        <div class="titre">RELEVÉ DE NOTES — {{ strtoupper($classe->nom) }}</div>
        <div class="sous-titre">Année scolaire {{ $annee?->libelle ?? '—' }}</div>
    </div>
</div>

<div class="meta">
    <div class="meta-item">
        <div class="meta-label">Classe</div>
        <div class="meta-val">{{ $classe->nom }} ({{ ucfirst($classe->niveau) }})</div>
    </div>
    <div class="meta-item">
        <div class="meta-label">Effectif évalué</div>
        <div class="meta-val">{{ $donneesEleves->count() }} élève(s)</div>
    </div>
    <div class="meta-item">
        <div class="meta-label">Moyenne de classe</div>
        <div class="meta-val">{{ $moyenneClasse ?? '—' }}/20</div>
    </div>
    <div class="meta-item">
        <div class="meta-label">Généré le</div>
        <div class="meta-val">{{ $genereLe }}</div>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th class="left" style="width:30px">Rang</th>
            <th class="left" style="width:120px">Élève</th>
            <th style="width:60px">Matricule</th>
            @foreach($matieres as $m)
            <th style="min-width:50px">{{ \Str::limit($m, 10) }}</th>
            @endforeach
            <th style="width:55px">Moy. /20</th>
            <th style="width:45px">Devoirs</th>
            <th style="width:40px">Fraude</th>
        </tr>
    </thead>
    <tbody>
        @foreach($donneesEleves as $d)
        <tr>
            <td class="rang">
                @if($d['rang'] == 1) 🥇
                @elseif($d['rang'] == 2) 🥈
                @elseif($d['rang'] == 3) 🥉
                @else {{ $d['rang'] }}
                @endif
            </td>
            <td class="left">
                <strong>{{ $d['eleve']->nom }}</strong> {{ $d['eleve']->prenoms }}
            </td>
            <td style="color:#6B7280;font-size:7.5pt">{{ $d['eleve']->matricule ?? '—' }}</td>

            @foreach($matieres as $m)
            @php $note = $d['par_matiere'][$m] ?? null; @endphp
            <td class="{{ $note === null ? '' : ($note >= 10 ? 'note-ok' : 'note-fail') }}">
                {{ $note ?? '—' }}
            </td>
            @endforeach

            <td class="{{ $d['moyenne'] === null ? '' : ($d['moyenne'] >= 10 ? 'note-ok' : 'note-fail') }}"
                style="font-size:10pt">
                {{ $d['moyenne'] ?? '—' }}
            </td>
            <td style="color:#6B7280">{{ $d['nb_devoirs'] }}</td>
            <td>
                @if($d['fraudes'] > 0)
                <span class="badge-fraude">⚠ {{ $d['fraudes'] }}</span>
                @else
                <span style="color:#059669">✓</span>
                @endif
            </td>
        </tr>
        @endforeach

        {{-- Ligne moyennes de classe --}}
        <tr style="background:#EEF2FF;font-weight:bold;font-size:8.5pt">
            <td colspan="3" class="left" style="color:#4F46E5">Moyenne de la classe</td>
            @foreach($matieres as $m)
            @php
                $moyMat = $donneesEleves->filter(fn($d) => isset($d['par_matiere'][$m]))->avg(fn($d) => $d['par_matiere'][$m]);
            @endphp
            <td style="color:#4F46E5">{{ $moyMat ? round($moyMat, 1) : '—' }}</td>
            @endforeach
            <td style="color:#4F46E5">{{ $moyenneClasse ?? '—' }}</td>
            <td colspan="2"></td>
        </tr>
    </tbody>
</table>

<div class="footer">
    <div class="footer-left">DevSecure · Document confidentiel · Usage interne</div>
    <div class="footer-right">{{ $classe->nom }} · {{ $annee?->libelle }}</div>
</div>

</body>
</html>