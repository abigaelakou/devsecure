<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Votre résultat est disponible</title>
    <style>
        body { font-family: 'Inter', Arial, sans-serif; background:#F3F4F6; margin:0; padding:20px; }
        .container { max-width:560px; margin:0 auto; }
        .header {
            background:linear-gradient(135deg, #1E1B4B, #4F46E5);
            border-radius:16px 16px 0 0; padding:32px; text-align:center;
        }
        .logo { font-size:24px; font-weight:800; color:white; margin-bottom:4px; }
        .logo-sub { font-size:12px; color:rgba(255,255,255,0.6); letter-spacing:0.1em; text-transform:uppercase; }
        .body { background:white; padding:32px; }
        .greeting { font-size:18px; font-weight:700; color:#111827; margin-bottom:8px; }
        .text { font-size:15px; color:#374151; line-height:1.7; margin-bottom:24px; }
        .note-card {
            background:linear-gradient(135deg,#1E1B4B,#4F46E5);
            border-radius:16px; padding:28px; text-align:center; margin-bottom:24px;
        }
        .note-label { font-size:13px; color:rgba(255,255,255,0.7); margin-bottom:6px; }
        .note-valeur { font-size:52px; font-weight:800; color:white; line-height:1; }
        .note-sur { font-size:18px; color:rgba(255,255,255,0.7); margin-bottom:12px; }
        .mention-badge {
            display:inline-block; background:rgba(255,255,255,0.2);
            color:white; padding:6px 20px; border-radius:20px;
            font-size:14px; font-weight:600;
        }
        .stats-grid { display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px; margin-bottom:24px; }
        .stat-box {
            background:#F9FAFB; border:1px solid #E5E7EB;
            border-radius:10px; padding:14px; text-align:center;
        }
        .stat-val { font-size:22px; font-weight:700; }
        .stat-lbl { font-size:12px; color:#6B7280; margin-top:2px; }
        .btn {
            display:block; text-align:center;
            background:#4F46E5; color:white;
            padding:14px 32px; border-radius:12px;
            text-decoration:none; font-size:15px; font-weight:700;
            margin:0 auto 24px;
        }
        .fraude-warning {
            background:#FEE2E2; border:1px solid #FCA5A5;
            border-radius:10px; padding:14px 16px;
            font-size:13px; color:#991B1B; margin-bottom:24px;
        }
        .footer {
            background:#F9FAFB; border-radius:0 0 16px 16px;
            padding:20px 32px; text-align:center;
            font-size:12px; color:#9CA3AF;
        }
    </style>
</head>
<body>
<div class="container">

    <div class="header">
        <div class="logo">DevSecure</div>
        <div class="logo-sub">Plateforme d'évaluation sécurisée</div>
    </div>

    <div class="body">
        <div class="greeting">Bonjour {{ $eleve->prenoms }} 🎉</div>
        <p class="text">
            Votre devoir <strong>"{{ $resultat->devoir?->titre }}"</strong>
            a été corrigé. Voici votre résultat :
        </p>

        {{-- Note principale --}}
        <div class="note-card">
            <div class="note-label">Votre note</div>
            <div class="note-valeur">{{ $resultat->note_finale }}</div>
            <div class="note-sur">/ {{ $resultat->note_sur }}</div>
            <div class="mention-badge">{{ $resultat->mention }} — {{ $resultat->pourcentage }}%</div>
        </div>

        {{-- Stats --}}
        <div class="stats-grid">
            <div class="stat-box">
                <div class="stat-val" style="color:#059669">{{ $resultat->bonnes_reponses }}</div>
                <div class="stat-lbl">Bonnes réponses</div>
            </div>
            <div class="stat-box">
                <div class="stat-val" style="color:#DC2626">{{ $resultat->mauvaises_reponses }}</div>
                <div class="stat-lbl">Mauvaises réponses</div>
            </div>
            <div class="stat-box">
                <div class="stat-val" style="color:#6B7280">{{ $resultat->sans_reponse }}</div>
                <div class="stat-lbl">Sans réponse</div>
            </div>
        </div>

        @if($resultat->fraude_detectee)
        <div class="fraude-warning">
            ⚠️ <strong>Activité suspecte détectée</strong> pendant ce devoir
            ({{ $resultat->nb_evenements_antitriche }} événement(s)).
            Ce résultat est signalé à votre enseignant.
        </div>
        @endif

        <a href="{{ config('app.url') }}/eleve/resultats/{{ $resultat->tentative_id }}" class="btn">
            📊 Voir le détail de mes réponses
        </a>

        <p class="text" style="font-size:13px;color:#6B7280;">
            Connectez-vous sur votre espace élève pour consulter le détail de vos réponses
            et les corrections éventuelles de votre enseignant.
        </p>
    </div>

    <div class="footer">
        <p>© {{ date('Y') }} DevSecure — {{ config('app.name') }}</p>
        <p style="margin-top:4px">Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
    </div>
</div>
</body>
</html>