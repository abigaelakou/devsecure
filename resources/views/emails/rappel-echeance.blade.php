{{-- resources/views/emails/rappel-echeance.blade.php --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rappel d'échéance</title>
    <style>
        body { font-family: Arial, sans-serif; background:#F3F4F6; margin:0; padding:20px; }
        .container { max-width:560px; margin:0 auto; }
        .header { background:linear-gradient(135deg,#1E1B4B,#D97706); border-radius:16px 16px 0 0; padding:32px; text-align:center; }
        .logo { font-size:24px; font-weight:800; color:white; }
        .body { background:white; padding:32px; }
        .greeting { font-size:18px; font-weight:700; color:#111827; margin-bottom:8px; }
        .text { font-size:15px; color:#374151; line-height:1.7; margin-bottom:20px; }
        .warning-card { background:#FEF3C7; border:2px solid #FCD34D; border-radius:12px; padding:20px; margin-bottom:24px; }
        .devoir-titre { font-size:17px; font-weight:700; color:#92400E; margin-bottom:8px; }
        .expire { font-size:14px; color:#B45309; }
        .btn { display:block; text-align:center; background:#D97706; color:white; padding:14px 32px; border-radius:12px; text-decoration:none; font-size:15px; font-weight:700; margin:0 auto 24px; }
        .footer { background:#F9FAFB; border-radius:0 0 16px 16px; padding:20px 32px; text-align:center; font-size:12px; color:#9CA3AF; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <div class="logo">⏰ DevSecure</div>
    </div>
    <div class="body">
        <div class="greeting">Bonjour {{ $eleve->prenoms }},</div>
        <p class="text">
            N'oubliez pas ! Vous avez un devoir qui expire très bientôt et que vous n'avez pas encore complété.
        </p>
        <div class="warning-card">
            <div class="devoir-titre">{{ $devoir->titre }}</div>
            <div class="expire">
                📅 Expire le <strong>{{ $devoir->expire_le?->format('d/m/Y à H:i') }}</strong>
                ({{ $devoir->expire_le?->diffForHumans() }})
            </div>
            <div style="font-size:13px;color:#92400E;margin-top:8px">
                ⏱ Durée : {{ $devoir->duree_totale_minutes ?? '—' }} minutes
                · {{ $devoir->questions()->count() }} questions
            </div>
        </div>
        <a href="{{ config('app.url') }}/eleve/devoirs/{{ $devoir->id }}" class="btn">
            🚀 Commencer le devoir maintenant
        </a>
    </div>
    <div class="footer">
        <p>© {{ date('Y') }} DevSecure — {{ config('app.name') }}</p>
    </div>
</div>
</body>
</html>
 