<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau devoir disponible</title>
    <style>
        body { font-family: 'Inter', Arial, sans-serif; background:#F3F4F6; margin:0; padding:20px; }
        .container { max-width:560px; margin:0 auto; }
        .header {
            background:linear-gradient(135deg, #1E1B4B, #4F46E5);
            border-radius:16px 16px 0 0; padding:32px;
            text-align:center;
        }
        .logo { font-size:24px; font-weight:800; color:white; margin-bottom:4px; }
        .logo-sub { font-size:12px; color:rgba(255,255,255,0.6); letter-spacing:0.1em; text-transform:uppercase; }
        .body { background:white; padding:32px; }
        .greeting { font-size:18px; font-weight:700; color:#111827; margin-bottom:8px; }
        .text { font-size:15px; color:#374151; line-height:1.7; margin-bottom:24px; }
        .devoir-card {
            background:#F9FAFB; border:1.5px solid #E5E7EB;
            border-radius:12px; padding:20px; margin-bottom:24px;
        }
        .matiere-badge {
            display:inline-block; font-size:11px; font-weight:700;
            text-transform:uppercase; letter-spacing:0.06em;
            padding:3px 10px; border-radius:20px;
            background:#EEF2FF; color:#4F46E5; margin-bottom:10px;
        }
        .devoir-titre { font-size:17px; font-weight:700; color:#111827; margin-bottom:12px; }
        .devoir-info { display:flex; gap:16px; flex-wrap:wrap; }
        .info-item { font-size:13px; color:#6B7280; }
        .info-item strong { color:#374151; }
        .btn {
            display:block; text-align:center;
            background:#4F46E5; color:white;
            padding:14px 32px; border-radius:12px;
            text-decoration:none; font-size:15px; font-weight:700;
            margin:0 auto 24px;
        }
        .warning {
            background:#FEF3C7; border:1px solid #FCD34D;
            border-radius:10px; padding:14px 16px;
            font-size:13px; color:#92400E; margin-bottom:24px;
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
        <div class="greeting">Bonjour {{ $eleve->prenoms }} 👋</div>
        <p class="text">
            Un nouveau devoir vient d'être publié par
            <strong>{{ $devoir->enseignant?->nom_complet }}</strong>.
            Il est maintenant disponible sur votre espace élève.
        </p>

        <div class="devoir-card">
            <div class="matiere-badge">{{ $devoir->matiere?->nom }}</div>
            <div class="devoir-titre">{{ $devoir->titre }}</div>
            <div class="devoir-info">
                <div class="info-item">
                    📚 <strong>{{ $devoir->questions()->count() }}</strong> questions
                </div>
                @if($devoir->duree_totale_minutes)
                <div class="info-item">
                    ⏱ <strong>{{ $devoir->duree_totale_minutes }}</strong> minutes
                </div>
                @endif
                @if($devoir->expire_le)
                <div class="info-item">
                    📅 Expire le <strong>{{ $devoir->expire_le->format('d/m/Y à H:i') }}</strong>
                </div>
                @endif
                <div class="info-item">
                    🎯 Note sur <strong>{{ $devoir->note_sur }}</strong>
                </div>
            </div>
        </div>

        @if($devoir->expire_le && $devoir->expire_le->diffInDays() <= 2)
        <div class="warning">
            ⚠️ <strong>Attention !</strong> Ce devoir expire dans moins de 48 heures.
            Pensez à le compléter rapidement.
        </div>
        @endif

        <a href="{{ config('app.url') }}/eleve/devoirs/{{ $devoir->id }}" class="btn">
            📖 Accéder au devoir
        </a>

        <p class="text" style="font-size:13px;color:#6B7280;">
            Si vous avez des difficultés à accéder au devoir, connectez-vous sur
            <a href="{{ config('app.url') }}" style="color:#4F46E5">{{ config('app.url') }}</a>
            et rendez-vous dans la section "Mes devoirs".
        </p>
    </div>

    <div class="footer">
        <p>© {{ date('Y') }} DevSecure — {{ config('app.name') }}</p>
        <p style="margin-top:4px">Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
    </div>
</div>
</body>
</html>