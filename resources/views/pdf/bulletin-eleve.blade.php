<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Bulletin — {{ $eleve->nom_complet }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: sans-serif;
            font-size: 11pt;
            color: #1F2937;
            background: white;
            padding: 0;
        }

        /* ── EN-TÊTE ── */
        .header {
            background: #1E1B4B;
            color: white;
            padding: 20px 30px;
            display: table;
            width: 100%;
        }
        .header-left  { display: table-cell; vertical-align: middle; width: 60%; }
        .header-right { display: table-cell; vertical-align: middle; text-align: right; }
        .logo-text { font-size: 22pt; font-weight: bold; letter-spacing: -0.5px; }
        .logo-sub  { font-size: 8pt; opacity: 0.7; text-transform: uppercase; letter-spacing: 2px; }
        .header-right .titre { font-size: 14pt; font-weight: bold; }
        .header-right .annee { font-size: 10pt; opacity: 0.8; margin-top: 3px; }

        /* ── INFOS ÉLÈVE ── */
        .info-section {
            background: #F8F7FF;
            border-bottom: 3px solid #4F46E5;
            padding: 14px 30px;
            display: table;
            width: 100%;
        }
        .info-col { display: table-cell; vertical-align: top; width: 33%; }
        .info-label { font-size: 8pt; color: #6B7280; text-transform: uppercase; letter-spacing: 0.5px; }
        .info-value { font-size: 11pt; font-weight: bold; color: #1E1B4B; margin-top: 2px; }

        /* ── RÉSUMÉ NOTE ── */
        .resume-section {
            padding: 16px 30px;
            display: table;
            width: 100%;
            background: white;
            border-bottom: 1px solid #E5E7EB;
        }
        .resume-box {
            display: table-cell;
            text-align: center;
            padding: 10px 15px;
            border-right: 1px solid #E5E7EB;
        }
        .resume-box:last-child { border-right: none; }
        .resume-val  { font-size: 20pt; font-weight: bold; }
        .resume-lbl  { font-size: 8pt; color: #6B7280; margin-top: 2px; }

        /* ── SECTION MATIÈRE ── */
        .matiere-section { margin: 0 30px 16px; }
        .matiere-header {
            padding: 8px 12px;
            border-left: 4px solid #4F46E5;
            background: #F9FAFB;
            margin-bottom: 0;
            display: table;
            width: 100%;
        }
        .matiere-nom { display: table-cell; font-size: 11pt; font-weight: bold; color: #1E1B4B; }
        .matiere-moy { display: table-cell; text-align: right; font-size: 11pt; font-weight: bold; }
        .matiere-ens { font-size: 8.5pt; color: #6B7280; margin-top: 1px; }

        /* ── TABLE DEVOIRS ── */
        table { width: 100%; border-collapse: collapse; }
        th {
            background: #F3F4F6;
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            padding: 6px 10px;
            text-align: left;
            color: #6B7280;
            border-bottom: 1px solid #E5E7EB;
        }
        td {
            padding: 7px 10px;
            font-size: 9.5pt;
            border-bottom: 1px solid #F3F4F6;
        }
        tr:last-child td { border-bottom: none; }

        /* ── BADGES ── */
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 8pt;
            font-weight: bold;
        }
        .badge-success { background: #D1FAE5; color: #065F46; }
        .badge-warning { background: #FEF3C7; color: #92400E; }
        .badge-danger  { background: #FEE2E2; color: #991B1B; }

        /* ── PIED DE PAGE ── */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 8px 30px;
            background: #F9FAFB;
            border-top: 1px solid #E5E7EB;
            font-size: 8pt;
            color: #9CA3AF;
            display: table;
            width: 100%;
        }
        .footer-left  { display: table-cell; }
        .footer-right { display: table-cell; text-align: right; }

        .page-break { page-break-after: always; }
        .no-break   { page-break-inside: avoid; }
    </style>
</head>
<body>

{{-- EN-TÊTE --}}
<div class="header">
    <div class="header-left">
        <div class="logo-text">🛡 DevSecure</div>
        <div class="logo-sub">Plateforme d'évaluation sécurisée</div>
    </div>
    <div class="header-right">
        <div class="titre">BULLETIN DE NOTES</div>
        <div class="annee">Année scolaire {{ $annee?->libelle ?? '—' }}</div>
    </div>
</div>

{{-- INFOS ÉLÈVE --}}
<div class="info-section">
    <div class="info-col">
        <div class="info-label">Élève</div>
        <div class="info-value">{{ $eleve->nom_complet }}</div>
        @if($eleve->matricule)
        <div style="font-size:9pt;color:#6B7280;margin-top:2px">Matricule : {{ $eleve->matricule }}</div>
        @endif
    </div>
    <div class="info-col">
        <div class="info-label">Classe</div>
        <div class="info-value">{{ $classe?->nom ?? '—' }}</div>
        <div style="font-size:9pt;color:#6B7280;margin-top:2px">{{ $classe ? ucfirst($classe->niveau) : '' }}</div>
    </div>
    <div class="info-col" style="text-align:right">
        <div class="info-label">Généré le</div>
        <div class="info-value" style="font-size:10pt">{{ $genereLe }}</div>
    </div>
</div>

{{-- RÉSUMÉ --}}
<div class="resume-section">
    <div class="resume-box">
        <div class="resume-val" style="color:#4F46E5">{{ $moyenneGenerale ?? '—' }}</div>
        <div class="resume-lbl">Moyenne générale /20</div>
    </div>
    <div class="resume-box">
        <div class="resume-val" style="color:#059669">{{ $nbDevoirs }}</div>
        <div class="resume-lbl">Devoirs passés</div>
    </div>
    <div class="resume-box">
        <div class="resume-val" style="color:#D97706">{{ $parMatiere->count() }}</div>
        <div class="resume-lbl">Matières évaluées</div>
    </div>
    <div class="resume-box">
        @php
            $mention = match(true) {
                $moyenneGenerale >= 16 => 'Très bien',
                $moyenneGenerale >= 14 => 'Bien',
                $moyenneGenerale >= 12 => 'Assez bien',
                $moyenneGenerale >= 10 => 'Passable',
                $moyenneGenerale !== null => 'Insuffisant',
                default => '—',
            };
            $mentionColor = match($mention) {
                'Très bien','Bien' => '#059669',
                'Assez bien','Passable' => '#D97706',
                default => '#DC2626',
            };
        @endphp
        <div class="resume-val" style="color:{{ $mentionColor }};font-size:14pt">{{ $mention }}</div>
        <div class="resume-lbl">Appréciation</div>
    </div>
</div>

<div style="height:16px"></div>

{{-- RÉSULTATS PAR MATIÈRE --}}
@foreach($parMatiere as $matiere)
<div class="matiere-section no-break">
    <div class="matiere-header" style="border-left-color:{{ $matiere['couleur'] }}">
        <div class="matiere-nom">
            {{ $matiere['matiere'] }}
            <div class="matiere-ens">Enseignant : {{ $matiere['enseignant'] ?? '—' }}</div>
        </div>
        <div class="matiere-moy" style="color:{{ $matiere['couleur'] }}">
            Moy. : {{ $matiere['moyenne_20'] }}/20
            <div style="font-size:8pt;color:#6B7280;font-weight:normal">{{ $matiere['nb_devoirs'] }} devoir(s)</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:40%">Devoir</th>
                <th>Note</th>
                <th>Note /20</th>
                <th>%</th>
                <th>Mention</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($matiere['devoirs'] as $devoir)
            <tr>
                <td>
                    {{ $devoir['titre'] }}
                    @if($devoir['fraude'])
                    <span style="color:#DC2626;font-size:8pt"> ⚠</span>
                    @endif
                </td>
                <td><strong>{{ $devoir['note'] }}</strong>/{{ $devoir['note_sur'] }}</td>
                <td><strong>{{ $devoir['note_20'] }}</strong>/20</td>
                <td>{{ $devoir['pourcentage'] }}%</td>
                <td>
                    @php $c = $devoir['pourcentage'] >= 75 ? 'success' : ($devoir['pourcentage'] >= 50 ? 'warning' : 'danger'); @endphp
                    <span class="badge badge-{{ $c }}">{{ $devoir['mention'] }}</span>
                </td>
                <td style="color:#6B7280">{{ $devoir['date'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div style="height:10px"></div>
@endforeach

{{-- PIED DE PAGE --}}
<div class="footer">
    <div class="footer-left">
        DevSecure — {{ config('app.name') }} · Document généré automatiquement
    </div>
    <div class="footer-right">
        Bulletin de {{ $eleve->nom_complet }} · {{ $annee?->libelle }}
    </div>
</div>

</body>
</html>